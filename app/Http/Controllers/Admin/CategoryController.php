<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        $this->authorize('viewAny', Category::class);
        
        $categories = Category::with('image')->latest()->paginate(10);
        
        // Append URL to each category's image
        $categories->getCollection()->each(function ($category) {
            if ($category->image) {
                $category->image->append('url');
            }
        });
        
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Category::class);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_id' => 'nullable|exists:media,id',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        $data = $request->only(['name', 'description', 'image_id', 'is_active']);
        $data['slug'] = Str::slug($request->name);
        
        $category = Category::create($data);
        
        // Load the image relationship
        $category->load('image');
        
        // Append URL to the category's image
        if ($category->image) {
            $category->image->append('url');
        }
        
        return response()->json(['success' => true, 'category' => $category]);
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        $this->authorize('view', $category);
        
        $category->load('image');
        
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
        $this->authorize('update', $category);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_id' => 'nullable|exists:media,id',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        $data = $request->only(['name', 'description', 'image_id', 'is_active']);
        $data['slug'] = Str::slug($request->name);
        
        $category->update($data);
        
        // Load the image relationship
        $category->load('image');
        
        // Append URL to the category's image
        if ($category->image) {
            $category->image->append('url');
        }
        
        return response()->json(['success' => true, 'category' => $category]);
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);
        
        $category->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Get subcategories for a specific category.
     */
    public function getSubCategories(Category $category)
    {
        $this->authorize('viewAny', SubCategory::class);
        
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
     * Store a newly created subcategory in storage.
     */
    public function storeSubCategory(Request $request)
    {
        $this->authorize('create', SubCategory::class);
        
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_id' => 'nullable|exists:media,id',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        $data = $request->only(['category_id', 'name', 'description', 'image_id', 'is_active']);
        $data['slug'] = Str::slug($request->name);
        
        $subCategory = SubCategory::create($data);
        
        // Load the image relationship
        $subCategory->load('image');
        
        // Append URL to the subcategory's image
        if ($subCategory->image) {
            $subCategory->image->append('url');
        }
        
        return response()->json(['success' => true, 'subcategory' => $subCategory]);
    }

    /**
     * Display the specified subcategory.
     */
    public function showSubCategory(SubCategory $subCategory)
    {
        $this->authorize('view', $subCategory);
        
        $subCategory->load(['image', 'category']);
        
        // Append URL to the subcategory's image
        if ($subCategory->image) {
            $subCategory->image->append('url');
        }
        
        return response()->json($subCategory);
    }

    /**
     * Update the specified subcategory in storage.
     */
    public function updateSubCategory(Request $request, SubCategory $subCategory)
    {
        $this->authorize('update', $subCategory);
        
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_id' => 'nullable|exists:media,id',
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        $data = $request->only(['category_id', 'name', 'description', 'image_id', 'is_active']);
        $data['slug'] = Str::slug($request->name);
        
        $subCategory->update($data);
        
        // Load the image relationship
        $subCategory->load('image');
        
        // Append URL to the subcategory's image
        if ($subCategory->image) {
            $subCategory->image->append('url');
        }
        
        return response()->json(['success' => true, 'subcategory' => $subCategory]);
    }

    /**
     * Remove the specified subcategory from storage.
     */
    public function destroySubCategory(SubCategory $subCategory)
    {
        $this->authorize('delete', $subCategory);
        
        $subCategory->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Get all categories for product management.
     */
    public function getAllCategories()
    {
        $this->authorize('viewAny', Category::class);
        
        $categories = Category::with('subCategories')->where('is_active', true)->get();
        
        return response()->json($categories);
    }

    /**
     * Create a new category via AJAX.
     */
    public function createCategory(Request $request)
    {
        $this->authorize('create', Category::class);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        $data = $request->only(['name', 'description']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = true;
        
        $category = Category::create($data);
        
        return response()->json(['success' => true, 'category' => $category]);
    }

    /**
     * Create a new subcategory via AJAX.
     */
    public function createSubCategory(Request $request)
    {
        $this->authorize('create', SubCategory::class);
        
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        $data = $request->only(['category_id', 'name', 'description']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = true;
        
        $subCategory = SubCategory::create($data);
        
        // Load the parent category relationship
        $subCategory->load('category');
        
        return response()->json(['success' => true, 'subcategory' => $subCategory]);
    }
}