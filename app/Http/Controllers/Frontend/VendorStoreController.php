<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Category;

class VendorStoreController extends Controller
{
    /**
     * Display the vendor store page
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        $vendor = Vendor::where('store_slug', $slug)
            ->where('status', Vendor::STATUS_APPROVED)
            ->firstOrFail();
        
        // Get featured products (limit to 8)
        $featuredProducts = Product::where('vendor_id', $vendor->id)
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
        
        // Get vendor categories
        $categories = Category::where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->with('image')
            ->get();
        
        return view('frontend.store.index', compact('vendor', 'featuredProducts', 'categories'));
    }
    
    /**
     * Display all products from a vendor
     *
     * @param string $slug
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function products($slug, Request $request)
    {
        $vendor = Vendor::where('store_slug', $slug)
            ->where('status', Vendor::STATUS_APPROVED)
            ->firstOrFail();
        
        $query = Product::where('vendor_id', $vendor->id)
            ->where('status', 'published');
        
        // Filter by category if provided
        if ($request->has('category')) {
            $categoryId = $request->get('category');
            // Since product_categories is a JSON field, we need to filter differently
            $query->where(function ($q) use ($categoryId) {
                $q->whereJsonContains('product_categories', ['category_id' => (int)$categoryId])
                  ->orWhereJsonContains('product_categories', ['category_id' => (string)$categoryId]);
            });
        }
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }
        
        // Sort
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('selling_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('selling_price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
        
        $products = $query->paginate(12);
        
        // Get vendor categories for filter
        $categories = Category::where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->with('image')
            ->get();
        
        return view('frontend.store.products', compact('vendor', 'products', 'categories'));
    }
    
    /**
     * Display a single product from a vendor
     *
     * @param string $slug
     * @param string $productSlug
     * @return \Illuminate\View\View
     */
    public function productDetail($slug, $productSlug)
    {
        $vendor = Vendor::where('store_slug', $slug)
            ->where('status', Vendor::STATUS_APPROVED)
            ->firstOrFail();
        
        $product = Product::where('vendor_id', $vendor->id)
            ->where('slug', $productSlug)
            ->where('status', 'published')
            ->with(['mainPhoto', 'variations'])
            ->firstOrFail();
        
        // Get related products
        $relatedProducts = Product::where('vendor_id', $vendor->id)
            ->where('status', 'published')
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();
        
        return view('frontend.store.product-detail', compact('vendor', 'product', 'relatedProducts'));
    }
}
