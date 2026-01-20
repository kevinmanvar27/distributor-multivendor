<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.login')->with('error', 'Vendor profile not found.');
        }
        
        $categories = Category::with(['subCategories', 'image'])
            ->where('vendor_id', $vendor->id)
            ->orderBy('name')
            ->paginate(10);
        
        // Append URL to each category's image
        $categories->getCollection()->each(function ($category) {
            if ($category->image) {
                $category->image->append('url');
            }
        });
        
        return view('vendor.categories.index', compact('categories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return response()->json(['success' => false, 'error' => 'Vendor not found'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_id' => 'nullable|exists:media,id',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $category = Category::create([
            'vendor_id' => $vendor->id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image_id' => $request->image_id,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        // Load the image relationship
        $category->load('image');
        
        // Append URL to the category's image
        if ($category->image) {
            $category->image->append('url');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'category' => $category
            ]);
        }

        return redirect()->route('vendor.categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        $vendor = $this->getVendor();
        
        if ($category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this category.');
        }
        
        $category->load(['subCategories', 'image']);
        
        // Append URL to the category's image
        if ($category->image) {
            $category->image->append('url');
        }
        
        return response()->json($category);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        $vendor = $this->getVendor();
        
        if ($category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this category.');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_id' => 'nullable|exists:media,id',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image_id' => $request->image_id,
            'is_active' => $request->has('is_active') ? $request->is_active : $category->is_active,
        ]);

        // Load the image relationship
        $category->load('image');
        
        // Append URL to the category's image
        if ($category->image) {
            $category->image->append('url');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'category' => $category
            ]);
        }

        return redirect()->route('vendor.categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        $vendor = $this->getVendor();
        
        if ($category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this category.');
        }
        
        // Delete all subcategories first
        $category->subCategories()->delete();
        $category->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ]);
        }

        return redirect()->route('vendor.categories.index')->with('success', 'Category deleted successfully.');
    }

    /**
     * Get all categories for AJAX requests.
     */
    public function getAllCategories()
    {
        $vendor = $this->getVendor();
        
        $categories = Category::with('subCategories')
            ->where(function($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id)
                      ->orWhereNull('vendor_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return response()->json($categories);
    }

    /**
     * Get subcategories for a category.
     */
    public function getSubCategories(Category $category)
    {
        $vendor = $this->getVendor();
        
        // Allow access to vendor's own categories or global categories
        if ($category->vendor_id !== null && $category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this category.');
        }
        
        $subCategories = $category->subCategories()->with(['image', 'category'])->latest()->paginate(10);
        
        // Append URL to each subcategory's image
        $subCategories->getCollection()->each(function ($subCategory) {
            if ($subCategory->image) {
                $subCategory->image->append('url');
            }
        });
        
        return response()->json($subCategories);
    }

    /**
     * Store a new subcategory.
     */
    public function storeSubCategory(Request $request)
    {
        $vendor = $this->getVendor();
        
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_id' => 'nullable|exists:media,id',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $category = Category::find($request->category_id);
        
        // Only allow adding subcategories to vendor's own categories
        if ($category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this category.');
        }

        $subCategory = SubCategory::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image_id' => $request->image_id,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        // Load the image relationship
        $subCategory->load('image');
        
        // Append URL to the subcategory's image
        if ($subCategory->image) {
            $subCategory->image->append('url');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subcategory created successfully.',
                'subcategory' => $subCategory
            ]);
        }

        return redirect()->route('vendor.categories.index')->with('success', 'Subcategory created successfully.');
    }

    /**
     * Show a subcategory.
     */
    public function showSubCategory(SubCategory $subCategory)
    {
        $vendor = $this->getVendor();
        $category = $subCategory->category;
        
        if ($category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this subcategory.');
        }
        
        $subCategory->load(['image', 'category']);
        
        // Append URL to the subcategory's image
        if ($subCategory->image) {
            $subCategory->image->append('url');
        }
        
        return response()->json($subCategory);
    }

    /**
     * Update a subcategory.
     */
    public function updateSubCategory(Request $request, SubCategory $subCategory)
    {
        $vendor = $this->getVendor();
        $category = $subCategory->category;
        
        if ($category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this subcategory.');
        }
        
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_id' => 'nullable|exists:media,id',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $subCategory->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image_id' => $request->image_id,
            'is_active' => $request->has('is_active') ? $request->is_active : $subCategory->is_active,
        ]);

        // Load the image relationship
        $subCategory->load('image');
        
        // Append URL to the subcategory's image
        if ($subCategory->image) {
            $subCategory->image->append('url');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subcategory updated successfully.',
                'subcategory' => $subCategory
            ]);
        }

        return redirect()->route('vendor.categories.index')->with('success', 'Subcategory updated successfully.');
    }

    /**
     * Delete a subcategory.
     */
    public function destroySubCategory(SubCategory $subCategory)
    {
        $vendor = $this->getVendor();
        $category = $subCategory->category;
        
        if ($category->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this subcategory.');
        }
        
        $subCategory->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subcategory deleted successfully.'
            ]);
        }

        return redirect()->route('vendor.categories.index')->with('success', 'Subcategory deleted successfully.');
    }

    /**
     * Create category via AJAX.
     */
    public function createCategory(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Create subcategory via AJAX.
     */
    public function createSubCategory(Request $request)
    {
        return $this->storeSubCategory($request);
    }
}
