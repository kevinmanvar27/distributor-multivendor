<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="API Endpoints for Category Management"
 * )
 */
class CategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/api/v1/categories",
     *      operationId="getCategoriesList",
     *      tags={"Categories"},
     *      summary="Get list of categories",
     *      description="Returns list of active categories with product counts (same as web flow)",
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function index()
    {
        // Get active categories with images and subcategories (same as web flow)
        $categories = Category::where('is_active', true)
            ->with(['image', 'subCategories' => function ($query) {
                $query->where('is_active', true)->with('image');
            }])
            ->get()
            ->filter(function ($category) {
                // Check if category has any subcategories with products
                foreach ($category->subCategories as $subCategory) {
                    // Check if this subcategory has any products
                    $products = Product::where('status', 'published')
                        ->get()
                        ->filter(function ($product) use ($category, $subCategory) {
                            if (!$product->product_categories) {
                                return false;
                            }
                            
                            // Check if product belongs to both the main category and this specific subcategory
                            foreach ($product->product_categories as $catData) {
                                if (isset($catData['category_id']) && $catData['category_id'] == $category->id &&
                                    isset($catData['subcategory_ids']) && in_array($subCategory->id, $catData['subcategory_ids'])) {
                                    return true;
                                }
                            }
                            
                            return false;
                        });
                    
                    // If we found products in this subcategory, the category should be displayed
                    if ($products->count() > 0) {
                        return true;
                    }
                }
                
                // Check if the parent category itself has products (not in subcategories)
                $directCategoryProducts = Product::where('status', 'published')
                    ->get()
                    ->filter(function ($product) use ($category) {
                        if (!$product->product_categories) {
                            return false;
                        }
                        
                        // Check if product belongs directly to this category (without subcategories)
                        foreach ($product->product_categories as $catData) {
                            if (isset($catData['category_id']) && $catData['category_id'] == $category->id) {
                                // Check if subcategory_ids is empty or not set (meaning product is directly in category)
                                if (!isset($catData['subcategory_ids']) || empty($catData['subcategory_ids'])) {
                                    return true;
                                }
                            }
                        }
                        
                        return false;
                    });
                
                // If we found direct products in this category, display it
                if ($directCategoryProducts->count() > 0) {
                    return true;
                }
                
                // No subcategories with products or direct products found
                return false;
            })
            ->values()
            ->map(function ($category) {
                // Count products for this category (including both direct and subcategory products)
                $productCount = Product::where('status', 'published')
                    ->get()
                    ->filter(function ($product) use ($category) {
                        if (!$product->product_categories) {
                            return false;
                        }
                        
                        // Check if product belongs to this category (either directly or through subcategories)
                        foreach ($product->product_categories as $catData) {
                            if (isset($catData['category_id']) && $catData['category_id'] == $category->id) {
                                return true;
                            }
                        }
                        
                        return false;
                    })
                    ->count();
                
                // Add product count to category
                $category->product_count = $productCount;
                
                // Add product count to subcategories
                $category->subCategories->transform(function ($subCategory) use ($category) {
                    $subProductCount = Product::where('status', 'published')
                        ->get()
                        ->filter(function ($product) use ($category, $subCategory) {
                            if (!$product->product_categories) {
                                return false;
                            }
                            
                            foreach ($product->product_categories as $catData) {
                                if (isset($catData['category_id']) && $catData['category_id'] == $category->id &&
                                    isset($catData['subcategory_ids']) && in_array($subCategory->id, $catData['subcategory_ids'])) {
                                    return true;
                                }
                            }
                            
                            return false;
                        })
                        ->count();
                    
                    $subCategory->product_count = $subProductCount;
                    return $subCategory;
                });
                
                return $category;
            });

        return $this->sendResponse($categories, 'Categories retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/api/v1/categories",
     *      operationId="storeCategory",
     *      tags={"Categories"},
     *      summary="Store new category",
     *      description="Returns category data",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","is_active"},
     *              @OA\Property(property="name", type="string", example="Electronics"),
     *              @OA\Property(property="description", type="string", example="Electronic products"),
     *              @OA\Property(property="image_id", type="integer", example=1),
     *              @OA\Property(property="is_active", type="boolean", example=true),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_id' => 'nullable|integer|exists:media,id',
            'is_active' => 'required|boolean',
        ]);

        $category = Category::create($request->all());

        return $this->sendResponse($category, 'Category created successfully.', 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/api/v1/categories/{id}",
     *      operationId="getCategoryById",
     *      tags={"Categories"},
     *      summary="Get category information",
     *      description="Returns category data with products (same as web flow)",
     *      @OA\Parameter(
     *          name="id",
     *          description="Category id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function show($id)
    {
        $category = Category::with(['subCategories' => function ($query) {
            $query->where('is_active', true)->with('image');
        }, 'image'])->find($id);

        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }

        // Check if category is active (same as web flow)
        if (!$category->is_active) {
            return $this->sendError('Category is not active.');
        }

        // Fetch products that belong to this category (same as web flow)
        $products = Product::with(['mainPhoto'])
            ->where('status', 'published')
            ->get()
            ->filter(function ($product) use ($id) {
                if (empty($product->product_categories)) {
                    return false;
                }
                
                foreach ($product->product_categories as $catData) {
                    if (isset($catData['category_id']) && (int)$catData['category_id'] === (int)$id) {
                        return true;
                    }
                }
                return false;
            })
            ->values();

        // Filter subcategories to only show those with products (same as web flow)
        $subCategories = $category->subCategories->filter(function ($subCategory) use ($category) {
            $products = Product::where('status', 'published')
                ->get()
                ->filter(function ($product) use ($category, $subCategory) {
                    if (!$product->product_categories) {
                        return false;
                    }
                    
                    foreach ($product->product_categories as $catData) {
                        if (isset($catData['category_id']) && $catData['category_id'] == $category->id &&
                            isset($catData['subcategory_ids']) && in_array($subCategory->id, $catData['subcategory_ids'])) {
                            return true;
                        }
                    }
                    
                    return false;
                });
            
            return $products->count() > 0;
        })->values();

        // Convert to array and add products
        $categoryData = $category->toArray();
        $categoryData['sub_categories'] = $subCategories;
        $categoryData['products'] = $products;
        $categoryData['product_count'] = $products->count();

        return $this->sendResponse($categoryData, 'Category retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *      path="/api/v1/categories/{id}",
     *      operationId="updateCategory",
     *      tags={"Categories"},
     *      summary="Update existing category",
     *      description="Returns updated category data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Category id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","is_active"},
     *              @OA\Property(property="name", type="string", example="Electronics"),
     *              @OA\Property(property="description", type="string", example="Electronic products"),
     *              @OA\Property(property="image_id", type="integer", example=1),
     *              @OA\Property(property="is_active", type="boolean", example=true),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_id' => 'nullable|integer|exists:media,id',
            'is_active' => 'required|boolean',
        ]);

        $category->update($request->all());

        return $this->sendResponse($category, 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *      path="/api/v1/categories/{id}",
     *      operationId="deleteCategory",
     *      tags={"Categories"},
     *      summary="Delete category",
     *      description="Deletes a category",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Category id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function destroy($id)
    {
        $category = Category::find($id);

        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }

        $category->delete();

        return $this->sendResponse(null, 'Category deleted successfully.');
    }
}