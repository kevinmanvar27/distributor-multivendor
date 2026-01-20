<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProductAttributeController extends Controller
{
    /**
     * Display a listing of the attributes.
     */
    public function index()
    {
        // Check if user has permission to view products (attributes are part of product management)
        if (!auth()->user()->hasPermission('viewAny_product')) {
            abort(403, 'Unauthorized action.');
        }
        
        $attributes = ProductAttribute::with('values')->orderBy('sort_order')->paginate(20);
        
        return view('admin.attributes.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new attribute.
     */
    public function create()
    {
        // Check if user has permission to create products
        if (!auth()->user()->hasPermission('create_product')) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('admin.attributes.create');
    }

    /**
     * Store a newly created attribute in storage.
     */
    public function store(Request $request)
    {
        // Check if user has permission to create products
        if (!auth()->user()->hasPermission('create_product')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }
        
        // Filter out empty values before validation
        $values = array_filter($request->values ?? [], function($value) {
            return !empty(trim($value));
        });
        
        $validator = Validator::make(array_merge($request->all(), ['values' => $values]), [
            'name' => 'required|string|max:255|unique:product_attributes,name',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'values' => 'required|array|min:1',
            'values.*' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            // Create attribute
            $attribute = ProductAttribute::create([
                'name' => $request->name,
                'description' => $request->description,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => true,
            ]);
            
            // Create attribute values
            $sortOrder = 0;
            foreach ($values as $value) {
                $attribute->values()->create([
                    'value' => trim($value),
                    'sort_order' => $sortOrder++,
                ]);
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attribute created successfully.',
                    'attribute' => $attribute->load('values')
                ]);
            }
            
            return redirect()->route('admin.attributes.index')->with('success', 'Attribute created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating attribute: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating attribute: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error creating attribute.')->withInput();
        }
    }

    /**
     * Show the form for editing the specified attribute.
     */
    public function edit(Request $request, ProductAttribute $attribute)
    {
        // Check if user has permission to update products
        if (!auth()->user()->hasPermission('update_product')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }
        
        $attribute->load('values');
        
        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json($attribute);
        }
        
        return view('admin.attributes.edit', compact('attribute'));
    }

    /**
     * Update the specified attribute in storage.
     */
    public function update(Request $request, ProductAttribute $attribute)
    {
        // Check if user has permission to update products
        if (!auth()->user()->hasPermission('update_product')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }
        
        // Filter out empty values before validation
        $values = array_filter($request->values ?? [], function($value) {
            return !empty(trim($value));
        });
        
        $validator = Validator::make(array_merge($request->all(), ['values' => $values]), [
            'name' => 'required|string|max:255|unique:product_attributes,name,' . $attribute->id,
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'values' => 'required|array|min:1',
            'values.*' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            // Update attribute
            $attribute->update([
                'name' => $request->name,
                'description' => $request->description,
                'sort_order' => $request->sort_order ?? 0,
            ]);
            
            // Delete existing values
            $attribute->values()->delete();
            
            // Create new values
            $sortOrder = 0;
            foreach ($values as $value) {
                $attribute->values()->create([
                    'value' => trim($value),
                    'sort_order' => $sortOrder++,
                ]);
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attribute updated successfully.',
                    'attribute' => $attribute->load('values')
                ]);
            }
            
            return redirect()->route('admin.attributes.index')->with('success', 'Attribute updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating attribute: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating attribute: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error updating attribute.')->withInput();
        }
    }

    /**
     * Remove the specified attribute from storage.
     */
    public function destroy(ProductAttribute $attribute)
    {
        // Check if user has permission to delete products
        if (!auth()->user()->hasPermission('delete_product')) {
            abort(403, 'Unauthorized action.');
        }
        
        $attribute->delete();
        
        return redirect()->route('admin.attributes.index')->with('success', 'Attribute deleted successfully.');
    }

    /**
     * Store a new attribute value.
     */
    public function storeValue(Request $request, ProductAttribute $attribute)
    {
        // Check if user has permission to update products
        if (!auth()->user()->hasPermission('update_product')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $attributeValue = $attribute->values()->create([
                'value' => $request->value,
                'color_code' => $request->color_code,
                'sort_order' => $request->sort_order ?? 0,
            ]);

            return response()->json(['success' => true, 'data' => $attributeValue]);
        } catch (\Exception $e) {
            Log::error('Failed to create attribute value: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create attribute value.'], 500);
        }
    }

    /**
     * Update an attribute value.
     */
    public function updateValue(Request $request, ProductAttribute $attribute, ProductAttributeValue $value)
    {
        // Check if user has permission to update products
        if (!auth()->user()->hasPermission('update_product')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $value->update([
                'value' => $request->value,
                'color_code' => $request->color_code,
                'sort_order' => $request->sort_order ?? $value->sort_order,
            ]);

            return response()->json(['success' => true, 'data' => $value]);
        } catch (\Exception $e) {
            Log::error('Failed to update attribute value: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update attribute value.'], 500);
        }
    }

    /**
     * Delete an attribute value.
     */
    public function destroyValue(ProductAttribute $attribute, ProductAttributeValue $value)
    {
        // Check if user has permission to delete products
        if (!auth()->user()->hasPermission('delete_product')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        
        try {
            $value->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to delete attribute value: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete attribute value.'], 500);
        }
    }

    /**
     * Get all attributes with their values (for AJAX)
     */
    public function getAttributes()
    {
        // Check if user has permission to view products
        if (!auth()->user()->hasPermission('viewAny_product')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        
        $attributes = ProductAttribute::with('values')->active()->orderBy('sort_order')->get();
        
        return response()->json(['success' => true, 'data' => $attributes]);
    }
}
