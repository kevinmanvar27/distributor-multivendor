<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VendorController extends Controller
{
    /**
     * Display a listing of the vendors.
     */
    public function index(Request $request)
    {
        $query = Vendor::with('user');
        
        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                  ->orWhere('business_email', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $vendors = $query->latest()->paginate(10);
        
        // Get counts for tabs
        $counts = [
            'all' => Vendor::count(),
            'pending' => Vendor::where('status', Vendor::STATUS_PENDING)->count(),
            'approved' => Vendor::where('status', Vendor::STATUS_APPROVED)->count(),
            'rejected' => Vendor::where('status', Vendor::STATUS_REJECTED)->count(),
            'suspended' => Vendor::where('status', Vendor::STATUS_SUSPENDED)->count(),
        ];
        
        return view('admin.vendors.index', compact('vendors', 'counts'));
    }

    /**
     * Show the form for creating a new vendor.
     */
    public function create()
    {
        return view('admin.vendors.create');
    }

    /**
     * Store a newly created vendor in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'mobile_number' => 'required|string|max:20',
            'store_name' => 'required|string|max:255',
            'store_description' => 'nullable|string|max:1000',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'nullable|string|max:20',
            'business_address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:20',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:pending,approved,rejected,suspended',
        ]);

        DB::beginTransaction();

        try {
            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobile_number' => $request->mobile_number,
                'user_role' => 'vendor',
                'is_approved' => $request->status === 'approved',
            ]);

            // Create the vendor profile
            $vendor = Vendor::create([
                'user_id' => $user->id,
                'store_name' => $request->store_name,
                'store_slug' => Str::slug($request->store_name),
                'store_description' => $request->store_description,
                'business_email' => $request->business_email ?? $request->email,
                'business_phone' => $request->business_phone ?? $request->mobile_number,
                'business_address' => $request->business_address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
                'gst_number' => $request->gst_number,
                'pan_number' => $request->pan_number,
                'commission_rate' => $request->commission_rate ?? 0,
                'status' => $request->status,
                'approved_at' => $request->status === 'approved' ? now() : null,
                'approved_by' => $request->status === 'approved' ? Auth::id() : null,
            ]);

            DB::commit();

            return redirect()->route('admin.vendors.index')->with('success', 'Vendor created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create vendor: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified vendor.
     */
    public function show(Vendor $vendor)
    {
        $vendor->load(['user', 'products', 'approvedByUser']);
        
        // Get vendor statistics
        $stats = [
            'total_products' => $vendor->products()->count(),
            'active_products' => $vendor->products()->where('status', 'published')->count(),
            'total_categories' => $vendor->categories()->count(),
        ];
        
        return view('admin.vendors.show', compact('vendor', 'stats'));
    }

    /**
     * Show the form for editing the specified vendor.
     */
    public function edit(Vendor $vendor)
    {
        $vendor->load('user');
        
        return view('admin.vendors.edit', compact('vendor'));
    }

    /**
     * Update the specified vendor in storage.
     */
    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $vendor->user_id,
            'mobile_number' => 'nullable|string|max:20',
            'store_name' => 'required|string|max:255',
            'store_description' => 'nullable|string|max:1000',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'nullable|string|max:20',
            'business_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:20',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'is_featured' => 'boolean',
            'priority' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Update user info
            $vendor->user->update([
                'name' => $request->name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
            ]);

            // Update vendor info
            $vendor->update([
                'store_name' => $request->store_name,
                'store_description' => $request->store_description,
                'business_email' => $request->business_email,
                'business_phone' => $request->business_phone,
                'business_address' => $request->business_address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
                'gst_number' => $request->gst_number,
                'pan_number' => $request->pan_number,
                'commission_rate' => $request->commission_rate ?? $vendor->commission_rate,
                'is_featured' => $request->has('is_featured'),
                'priority' => $request->priority ?? 0,
            ]);

            DB::commit();

            return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update vendor: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified vendor from storage.
     */
    public function destroy(Vendor $vendor)
    {
        DB::beginTransaction();

        try {
            // Delete vendor's products
            $vendor->products()->delete();
            
            // Delete vendor's categories
            $vendor->categories()->delete();
            
            // Delete vendor staff
            $vendor->staff()->delete();
            
            // Delete vendor
            $vendor->delete();
            
            // Update user role back to 'user'
            $vendor->user->update(['user_role' => 'user']);

            DB::commit();

            return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete vendor: ' . $e->getMessage()]);
        }
    }

    /**
     * Approve a vendor.
     */
    public function approve(Vendor $vendor)
    {
        $vendor->update([
            'status' => Vendor::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'rejection_reason' => null,
        ]);

        $vendor->user->update(['is_approved' => true]);

        return redirect()->back()->with('success', 'Vendor approved successfully.');
    }

    /**
     * Reject a vendor.
     */
    public function reject(Request $request, Vendor $vendor)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $vendor->update([
            'status' => Vendor::STATUS_REJECTED,
            'rejection_reason' => $request->rejection_reason,
        ]);

        $vendor->user->update(['is_approved' => false]);

        return redirect()->back()->with('success', 'Vendor rejected successfully.');
    }

    /**
     * Suspend a vendor.
     */
    public function suspend(Request $request, Vendor $vendor)
    {
        $request->validate([
            'suspension_reason' => 'nullable|string|max:500',
        ]);

        $vendor->update([
            'status' => Vendor::STATUS_SUSPENDED,
            'rejection_reason' => $request->suspension_reason,
        ]);

        $vendor->user->update(['is_approved' => false]);

        return redirect()->back()->with('success', 'Vendor suspended successfully.');
    }

    /**
     * Reactivate a suspended vendor.
     */
    public function reactivate(Vendor $vendor)
    {
        $vendor->update([
            'status' => Vendor::STATUS_APPROVED,
            'rejection_reason' => null,
        ]);

        $vendor->user->update(['is_approved' => true]);

        return redirect()->back()->with('success', 'Vendor reactivated successfully.');
    }

    /**
     * Update vendor commission rate.
     */
    public function updateCommission(Request $request, Vendor $vendor)
    {
        $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        $vendor->update([
            'commission_rate' => $request->commission_rate,
        ]);

        return redirect()->back()->with('success', 'Commission rate updated successfully.');
    }

    /**
     * Manage vendor permissions.
     */
    public function permissions(Vendor $vendor)
    {
        $permissions = Permission::all();
        $vendorPermissions = $vendor->permissions->pluck('id')->toArray();
        
        return view('admin.vendors.permissions', compact('vendor', 'permissions', 'vendorPermissions'));
    }

    /**
     * Update vendor permissions.
     */
    public function updatePermissions(Request $request, Vendor $vendor)
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $vendor->permissions()->sync($request->permissions ?? []);

        return redirect()->route('admin.vendors.show', $vendor)->with('success', 'Permissions updated successfully.');
    }
}
