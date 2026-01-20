<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Media;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ProductAttribute;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor;
    }

    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $vendor = $this->getVendor();
        
        $products = Product::with('mainPhoto')
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->paginate(10);
        
        // Get low stock products count for alert badge
        $lowStockCount = Product::where('vendor_id', $vendor->id)
            ->where('in_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_quantity_threshold')
            ->count();
        
        return view('vendor.products.index', compact('products', 'lowStockCount'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $vendor = $this->getVendor();
        
        // Get vendor's categories or all active categories
        $categories = Category::with('subCategories')
            ->where(function($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id)
                      ->orWhereNull('vendor_id');
            })
            ->where('is_active', true)
            ->get();
        
        // Get all active attributes with their values
        $attributes = ProductAttribute::with('values')->active()->orderBy('sort_order')->get();
        
        return view('vendor.products.create', compact('categories', 'attributes'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $vendor = $this->getVendor();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'product_type' => 'required|in:simple,variable',
            'description' => 'nullable|string',
            'mrp' => 'required_if:product_type,simple|nullable|numeric|min:0.01',
            'selling_price' => 'nullable|numeric|lt:mrp',
            'in_stock' => 'required_if:product_type,simple|boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_quantity_threshold' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
            'main_photo_id' => 'nullable|integer|exists:media,id',
            'product_gallery' => 'nullable',
            'product_categories' => 'nullable|array',
            'product_categories.*.category_id' => 'required|exists:categories,id',
            'product_categories.*.subcategory_ids' => 'nullable|array',
            'product_categories.*.subcategory_ids.*' => 'nullable|exists:sub_categories,id',
            'product_attributes' => 'required_if:product_type,variable|nullable|array',
            'variations' => 'required_if:product_type,variable|nullable|array|min:1',
            'variations.*.id' => 'nullable|exists:product_variations,id',
            'variations.*.sku' => 'nullable|string',
            'variations.*.mrp' => 'nullable|numeric|min:0',
            'variations.*.selling_price' => 'nullable|numeric',
            'variations.*.stock_quantity' => 'required_with:variations|integer|min:0',
            'variations.*.low_quantity_threshold' => 'nullable|integer|min:0',
            'variations.*.attribute_values' => 'required_with:variations|array|min:1',
            'variations.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'variations.*.image_id' => 'nullable|integer|exists:media,id',
            'variations.*.remove_image' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            $data = $request->only([
                'name', 'product_type', 'description', 'mrp', 'selling_price', 'in_stock', 
                'status', 'main_photo_id', 'meta_title', 
                'meta_description', 'meta_keywords'
            ]);
            
            // Assign vendor_id
            $data['vendor_id'] = $vendor->id;
            
            // Handle product type - default to simple if not provided
            $data['product_type'] = $request->product_type ?? 'simple';
            
            // For simple products, handle stock quantity
            if ($data['product_type'] === 'simple') {
                $data['stock_quantity'] = $request->in_stock ? ($request->stock_quantity ?? 0) : 0;
                
                if (!isset($data['stock_quantity']) || is_null($data['stock_quantity'])) {
                    $data['stock_quantity'] = 0;
                }
            } else {
                $data['stock_quantity'] = 0;
                $data['in_stock'] = true;
                $data['mrp'] = $data['mrp'] ?? 0;
            }
            
            $data['low_quantity_threshold'] = $request->low_quantity_threshold ?? 10;
            
            // Handle product gallery
            $productGallery = $request->product_gallery;
            if (is_string($productGallery)) {
                $productGallery = json_decode($productGallery, true);
            }
            $data['product_gallery'] = is_array($productGallery) ? $productGallery : [];
            
            // Handle product categories
            $productCategories = $request->product_categories;
            if (is_string($productCategories)) {
                $productCategories = json_decode($productCategories, true);
            }
            $data['product_categories'] = is_array($productCategories) ? $productCategories : [];
            
            // Handle product attributes for variable products
            $productAttributes = $request->product_attributes;
            if (is_string($productAttributes)) {
                $productAttributes = json_decode($productAttributes, true);
            }
            $data['product_attributes'] = is_array($productAttributes) ? $productAttributes : [];
            
            $product = Product::create($data);
            
            // Handle variations for variable products
            if ($data['product_type'] === 'variable' && $request->has('variations')) {
                $variations = $request->variations;
                if (is_string($variations)) {
                    $variations = json_decode($variations, true);
                }
                
                if (is_array($variations) && !empty($variations)) {
                    $seenCombinations = [];
                    
                    foreach ($variations as $index => $variationData) {
                        if (isset($variationData['attribute_values']) && is_string($variationData['attribute_values'])) {
                            $variationData['attribute_values'] = json_decode($variationData['attribute_values'], true);
                        }
                        
                        if (isset($variationData['attribute_values'])) {
                            ksort($variationData['attribute_values']);
                            $combinationKey = json_encode($variationData['attribute_values']);
                            
                            if (in_array($combinationKey, $seenCombinations)) {
                                continue;
                            }
                            
                            $seenCombinations[] = $combinationKey;
                        }
                        
                        if (!isset($variationData['stock_quantity']) || $variationData['stock_quantity'] === null || $variationData['stock_quantity'] === '') {
                            $variationData['stock_quantity'] = 0;
                        }
                        
                        $variationData['in_stock'] = isset($variationData['stock_quantity']) && $variationData['stock_quantity'] > 0;
                        
                        // Handle variation image upload
                        $imageId = null;
                        if ($request->hasFile("variations.{$index}.image")) {
                            $imageFile = $request->file("variations.{$index}.image");
                            $filename = time() . '_' . $imageFile->getClientOriginalName();
                            $path = $imageFile->storeAs('vendor_' . $vendor->id . '/products', $filename, 'public');
                            
                            $media = Media::create([
                                'name' => $imageFile->getClientOriginalName(),
                                'file_name' => $filename,
                                'path' => $path,
                                'mime_type' => $imageFile->getMimeType(),
                                'size' => $imageFile->getSize(),
                                'vendor_id' => $vendor->id,
                            ]);
                            $imageId = $media->id;
                        } elseif (isset($variationData['image_id'])) {
                            $imageId = $variationData['image_id'];
                        }
                        
                        ProductVariation::create([
                            'product_id' => $product->id,
                            'sku' => $variationData['sku'] ?? null,
                            'mrp' => $variationData['mrp'] ?? 0,
                            'selling_price' => $variationData['selling_price'] ?? null,
                            'stock_quantity' => $variationData['stock_quantity'],
                            'low_quantity_threshold' => $variationData['low_quantity_threshold'] ?? 10,
                            'in_stock' => $variationData['in_stock'],
                            'attribute_values' => $variationData['attribute_values'],
                            'image_id' => $imageId,
                            'is_default' => $index === 0,
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('vendor.products.index')->with('success', 'Product created successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating product: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $vendor = $this->getVendor();
        
        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this product.');
        }
        
        $product->load(['mainPhoto', 'variations']);
        
        return view('vendor.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $vendor = $this->getVendor();
        
        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this product.');
        }
        
        $product->load(['mainPhoto', 'variations']);
        
        $categories = Category::with('subCategories')
            ->where(function($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id)
                      ->orWhereNull('vendor_id');
            })
            ->where('is_active', true)
            ->get();
        
        $attributes = ProductAttribute::with('values')->active()->orderBy('sort_order')->get();
        
        return view('vendor.products.edit', compact('product', 'categories', 'attributes'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $vendor = $this->getVendor();
        
        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this product.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'product_type' => 'required|in:simple,variable',
            'description' => 'nullable|string',
            'mrp' => 'required_if:product_type,simple|nullable|numeric|min:0.01',
            'selling_price' => 'nullable|numeric|lt:mrp',
            'in_stock' => 'required_if:product_type,simple|boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_quantity_threshold' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
            'main_photo_id' => 'nullable|integer|exists:media,id',
            'product_gallery' => 'nullable',
            'product_categories' => 'nullable|array',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            $data = $request->only([
                'name', 'product_type', 'description', 'mrp', 'selling_price', 'in_stock', 
                'status', 'main_photo_id', 'meta_title', 
                'meta_description', 'meta_keywords'
            ]);
            
            $data['product_type'] = $request->product_type ?? 'simple';
            
            if ($data['product_type'] === 'simple') {
                $data['stock_quantity'] = $request->in_stock ? ($request->stock_quantity ?? 0) : 0;
            } else {
                $data['stock_quantity'] = 0;
                $data['in_stock'] = true;
            }
            
            $data['low_quantity_threshold'] = $request->low_quantity_threshold ?? 10;
            
            // Handle product gallery
            $productGallery = $request->product_gallery;
            if (is_string($productGallery)) {
                $productGallery = json_decode($productGallery, true);
            }
            $data['product_gallery'] = is_array($productGallery) ? $productGallery : [];
            
            // Handle product categories
            $productCategories = $request->product_categories;
            if (is_string($productCategories)) {
                $productCategories = json_decode($productCategories, true);
            }
            $data['product_categories'] = is_array($productCategories) ? $productCategories : [];
            
            // Handle product attributes
            $productAttributes = $request->product_attributes;
            if (is_string($productAttributes)) {
                $productAttributes = json_decode($productAttributes, true);
            }
            $data['product_attributes'] = is_array($productAttributes) ? $productAttributes : [];
            
            $product->update($data);
            
            // Handle variations for variable products
            if ($data['product_type'] === 'variable' && $request->has('variations')) {
                $variations = $request->variations;
                if (is_string($variations)) {
                    $variations = json_decode($variations, true);
                }
                
                $existingVariationIds = $product->variations->pluck('id')->toArray();
                $updatedVariationIds = [];
                
                if (is_array($variations) && !empty($variations)) {
                    foreach ($variations as $index => $variationData) {
                        if (isset($variationData['attribute_values']) && is_string($variationData['attribute_values'])) {
                            $variationData['attribute_values'] = json_decode($variationData['attribute_values'], true);
                        }
                        
                        if (!isset($variationData['stock_quantity']) || $variationData['stock_quantity'] === null) {
                            $variationData['stock_quantity'] = 0;
                        }
                        
                        $variationData['in_stock'] = $variationData['stock_quantity'] > 0;
                        
                        // Handle variation image
                        $imageId = null;
                        if ($request->hasFile("variations.{$index}.image")) {
                            $imageFile = $request->file("variations.{$index}.image");
                            $filename = time() . '_' . $imageFile->getClientOriginalName();
                            $path = $imageFile->storeAs('vendor_' . $vendor->id . '/products', $filename, 'public');
                            
                            $media = Media::create([
                                'name' => $imageFile->getClientOriginalName(),
                                'file_name' => $filename,
                                'path' => $path,
                                'mime_type' => $imageFile->getMimeType(),
                                'size' => $imageFile->getSize(),
                                'vendor_id' => $vendor->id,
                            ]);
                            $imageId = $media->id;
                        } elseif (isset($variationData['image_id'])) {
                            $imageId = $variationData['image_id'];
                        }
                        
                        if (isset($variationData['id']) && $variationData['id']) {
                            $variation = ProductVariation::find($variationData['id']);
                            if ($variation && $variation->product_id === $product->id) {
                                $variation->update([
                                    'sku' => $variationData['sku'] ?? null,
                                    'mrp' => $variationData['mrp'] ?? 0,
                                    'selling_price' => $variationData['selling_price'] ?? null,
                                    'stock_quantity' => $variationData['stock_quantity'],
                                    'low_quantity_threshold' => $variationData['low_quantity_threshold'] ?? 10,
                                    'in_stock' => $variationData['in_stock'],
                                    'attribute_values' => $variationData['attribute_values'],
                                    'image_id' => $imageId ?? $variation->image_id,
                                    'is_default' => $index === 0,
                                ]);
                                $updatedVariationIds[] = $variation->id;
                            }
                        } else {
                            $newVariation = ProductVariation::create([
                                'product_id' => $product->id,
                                'sku' => $variationData['sku'] ?? null,
                                'mrp' => $variationData['mrp'] ?? 0,
                                'selling_price' => $variationData['selling_price'] ?? null,
                                'stock_quantity' => $variationData['stock_quantity'],
                                'low_quantity_threshold' => $variationData['low_quantity_threshold'] ?? 10,
                                'in_stock' => $variationData['in_stock'],
                                'attribute_values' => $variationData['attribute_values'],
                                'image_id' => $imageId,
                                'is_default' => $index === 0,
                            ]);
                            $updatedVariationIds[] = $newVariation->id;
                        }
                    }
                }
                
                // Delete removed variations
                $variationsToDelete = array_diff($existingVariationIds, $updatedVariationIds);
                ProductVariation::whereIn('id', $variationsToDelete)->delete();
            }
            
            DB::commit();
            
            return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating product: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $vendor = $this->getVendor();
        
        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this product.');
        }
        
        try {
            $product->variations()->delete();
            $product->delete();
            
            return redirect()->route('vendor.products.index')->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to delete product: ' . $e->getMessage()]);
        }
    }

    /**
     * Display low stock products.
     */
    public function lowStock()
    {
        $vendor = $this->getVendor();
        
        $products = Product::with('mainPhoto')
            ->where('vendor_id', $vendor->id)
            ->where('in_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_quantity_threshold')
            ->orderBy('stock_quantity', 'asc')
            ->paginate(10);
        
        return view('vendor.products.low-stock', compact('products'));
    }
}
