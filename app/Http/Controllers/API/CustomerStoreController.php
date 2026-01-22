<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\VendorCustomer;

/**
 * @OA\Tag(
 *     name="Customer Store",
 *     description="API Endpoints for Vendor Customers to browse their vendor's products"
 * )
 */
class CustomerStoreController extends Controller
{
    /**
     * Get the authenticated customer
     */
    private function getCustomer(Request $request): VendorCustomer
    {
        return $request->user();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/products",
     *     summary="Get vendor products",
     *     description="Get products from the customer's vendor only",
     *     operationId="getCustomerVendorProducts",
     *     tags={"Customer Store"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", description="Search by product name", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="category_id", in="query", description="Filter by category", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="subcategory_id", in="query", description="Filter by subcategory", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="in_stock", in="query", description="Filter by stock status", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="sort_by", in="query", description="Sort by field (name, price, created_at)", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_order", in="query", description="Sort order (asc, desc)", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function products(Request $request)
    {
        $customer = $this->getCustomer($request);
        
        // Only get products from the customer's vendor
        $query = Product::where('vendor_id', $customer->vendor_id)
            ->whereIn('status', ['active', 'published'])
            ->with(['mainPhoto', 'vendor:id,store_name,store_slug', 'variations']);

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $categoryId = $request->category_id;
            $query->where(function($q) use ($categoryId) {
                $q->whereJsonContains('product_categories', ['category_id' => (int)$categoryId])
                  ->orWhereJsonContains('product_categories', ['category_id' => (string)$categoryId]);
            });
        }

        // Filter by subcategory
        if ($request->filled('subcategory_id')) {
            $subcategoryId = $request->subcategory_id;
            $query->where(function($q) use ($subcategoryId) {
                $q->whereRaw("JSON_CONTAINS(product_categories, '\"$subcategoryId\"', '$[*].subcategory_ids')")
                  ->orWhereRaw("JSON_CONTAINS(product_categories, '$subcategoryId', '$[*].subcategory_ids')");
            });
        }

        // Filter by stock status
        if ($request->has('in_stock')) {
            $query->where('in_stock', filter_var($request->in_stock, FILTER_VALIDATE_BOOLEAN));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['name', 'selling_price', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min($request->get('per_page', 20), 50);
        $products = $query->paginate($perPage);

        // Transform products with customer discount
        $products->getCollection()->transform(function($product) use ($customer) {
            $priceRange = $product->price_range;
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'product_type' => $product->product_type,
                'mrp' => $product->mrp,
                'selling_price' => $product->selling_price,
                'discounted_price' => $customer->getDiscountedPrice($product->selling_price ?? $product->mrp),
                'customer_discount' => $customer->discount_percentage,
                'price_range' => [
                    'min' => $customer->getDiscountedPrice($priceRange['min']),
                    'max' => $customer->getDiscountedPrice($priceRange['max']),
                ],
                'in_stock' => $product->in_stock,
                'stock_quantity' => $product->stock_quantity,
                'main_photo_url' => $product->mainPhoto?->url,
                'has_variations' => $product->isVariable(),
                'variations_count' => $product->variations->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/products/{id}",
     *     summary="Get product details",
     *     description="Get detailed information about a specific product from the customer's vendor",
     *     operationId="getCustomerProductDetails",
     *     tags={"Customer Store"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", description="Product ID or slug", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function productDetails(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        // Only get product from the customer's vendor
        $product = Product::where('vendor_id', $customer->vendor_id)
            ->whereIn('status', ['active', 'published'])
            ->where(function($q) use ($id) {
                $q->where('id', $id)->orWhere('slug', $id);
            })
            ->with(['mainPhoto', 'variations', 'vendor:id,store_name,store_slug'])
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'data' => null
            ], 404);
        }

        $priceRange = $product->price_range;

        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'product_type' => $product->product_type,
            'mrp' => $product->mrp,
            'selling_price' => $product->selling_price,
            'discounted_price' => $customer->getDiscountedPrice($product->selling_price ?? $product->mrp),
            'customer_discount' => $customer->discount_percentage,
            'price_range' => [
                'min' => $customer->getDiscountedPrice($priceRange['min']),
                'max' => $customer->getDiscountedPrice($priceRange['max']),
            ],
            'in_stock' => $product->in_stock,
            'stock_quantity' => $product->stock_quantity,
            'main_photo_url' => $product->mainPhoto?->url,
            'gallery_photos' => $product->gallery_photos->map(fn($photo) => $photo->url),
            'categories' => $product->categories->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
            ]),
            'meta_title' => $product->meta_title,
            'meta_description' => $product->meta_description,
        ];

        // Add variations if variable product
        if ($product->isVariable()) {
            $productData['variations'] = $product->variations->map(function($variation) use ($customer) {
                return [
                    'id' => $variation->id,
                    'sku' => $variation->sku,
                    'attributes' => $variation->attributes,
                    'mrp' => $variation->mrp,
                    'selling_price' => $variation->selling_price,
                    'discounted_price' => $customer->getDiscountedPrice($variation->selling_price ?? $variation->mrp),
                    'in_stock' => $variation->in_stock,
                    'stock_quantity' => $variation->stock_quantity,
                    'is_default' => $variation->is_default,
                    'image_url' => $variation->image_url,
                ];
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data' => $productData
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/categories",
     *     summary="Get vendor categories",
     *     description="Get categories from the customer's vendor only",
     *     operationId="getCustomerVendorCategories",
     *     tags={"Customer Store"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function categories(Request $request)
    {
        $customer = $this->getCustomer($request);
        
        // Get categories from the customer's vendor
        $categories = Category::where('vendor_id', $customer->vendor_id)
            ->withCount(['subcategories'])
            ->orderBy('name')
            ->get()
            ->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image_url' => $category->image_url,
                    'subcategories_count' => $category->subcategories_count,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $categories
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/categories/{id}/subcategories",
     *     summary="Get subcategories",
     *     description="Get subcategories for a specific category from the customer's vendor",
     *     operationId="getCustomerCategorySubcategories",
     *     tags={"Customer Store"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", description="Category ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function subcategories(Request $request, $categoryId)
    {
        $customer = $this->getCustomer($request);
        
        // Verify category belongs to customer's vendor
        $category = Category::where('vendor_id', $customer->vendor_id)
            ->where('id', $categoryId)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'data' => null
            ], 404);
        }

        $subcategories = SubCategory::where('category_id', $categoryId)
            ->orderBy('name')
            ->get()
            ->map(function($subcategory) {
                return [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'slug' => $subcategory->slug,
                    'description' => $subcategory->description,
                    'image_url' => $subcategory->image_url,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Subcategories retrieved successfully',
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                ],
                'subcategories' => $subcategories
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/home",
     *     summary="Get customer home page data",
     *     description="Get home page data including featured products, categories from the customer's vendor",
     *     operationId="getCustomerHome",
     *     tags={"Customer Store"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function home(Request $request)
    {
        $customer = $this->getCustomer($request);
        $vendor = $customer->vendor;

        // Get featured/latest products
        $featuredProducts = Product::where('vendor_id', $customer->vendor_id)
            ->whereIn('status', ['active', 'published'])
            ->where('in_stock', true)
            ->with(['mainPhoto'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($product) use ($customer) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'mrp' => $product->mrp,
                    'selling_price' => $product->selling_price,
                    'discounted_price' => $customer->getDiscountedPrice($product->selling_price ?? $product->mrp),
                    'main_photo_url' => $product->mainPhoto?->url,
                    'in_stock' => $product->in_stock,
                ];
            });

        // Get categories
        $categories = Category::where('vendor_id', $customer->vendor_id)
            ->limit(10)
            ->get()
            ->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image_url' => $category->image_url,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Home data retrieved successfully',
            'data' => [
                'vendor' => [
                    'id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'store_slug' => $vendor->store_slug,
                    'store_logo_url' => $vendor->store_logo_url,
                    'store_banner_url' => $vendor->store_banner_url,
                    'store_description' => $vendor->store_description,
                ],
                'customer' => [
                    'name' => $customer->name,
                    'discount_percentage' => $customer->discount_percentage,
                ],
                'featured_products' => $featuredProducts,
                'categories' => $categories,
                'total_products' => Product::where('vendor_id', $customer->vendor_id)->whereIn('status', ['active', 'published'])->count(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/search",
     *     summary="Search products",
     *     description="Search products from the customer's vendor only",
     *     operationId="searchCustomerProducts",
     *     tags={"Customer Store"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="q", in="query", description="Search query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="limit", in="query", description="Number of results", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function search(Request $request)
    {
        $customer = $this->getCustomer($request);
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 20), 50);

        if (empty($query)) {
            return response()->json([
                'success' => true,
                'message' => 'No search query provided',
                'data' => []
            ]);
        }

        $products = Product::where('vendor_id', $customer->vendor_id)
            ->whereIn('status', ['active', 'published'])
            ->where(function($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%');
            })
            ->with(['mainPhoto'])
            ->limit($limit)
            ->get()
            ->map(function($product) use ($customer) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'mrp' => $product->mrp,
                    'selling_price' => $product->selling_price,
                    'discounted_price' => $customer->getDiscountedPrice($product->selling_price ?? $product->mrp),
                    'main_photo_url' => $product->mainPhoto?->url,
                    'in_stock' => $product->in_stock,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Search results retrieved successfully',
            'data' => $products
        ]);
    }
}
