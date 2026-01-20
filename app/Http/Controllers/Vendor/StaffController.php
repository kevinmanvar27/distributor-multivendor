<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Display a listing of the staff.
     */
    public function index()
    {
        $vendor = $this->getVendor();
        
        $staff = VendorStaff::with('user')
            ->where('vendor_id', $vendor->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('vendor.staff.index', compact('staff'));
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create()
    {
        return view('vendor.staff.create');
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        $vendor = $this->getVendor();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'mobile_number' => 'nullable|string|max:20',
            'role' => 'required|string|max:50',
            'permissions' => 'nullable|array',
        ]);

        DB::beginTransaction();
        
        try {
            // Create user account
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobile_number' => $request->mobile_number,
                'user_type' => 'vendor_staff',
            ]);

            // Create vendor staff record
            VendorStaff::create([
                'vendor_id' => $vendor->id,
                'user_id' => $user->id,
                'role' => $request->role,
                'permissions' => $request->permissions ?? [],
                'is_active' => true,
            ]);

            DB::commit();

            return redirect()->route('vendor.staff.index')
                ->with('success', 'Staff member added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error creating staff member: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified staff member.
     */
    public function show($id)
    {
        $vendor = $this->getVendor();
        
        $staff = VendorStaff::with('user')
            ->where('vendor_id', $vendor->id)
            ->findOrFail($id);
        
        return view('vendor.staff.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit($id)
    {
        $vendor = $this->getVendor();
        
        $staff = VendorStaff::with('user')
            ->where('vendor_id', $vendor->id)
            ->findOrFail($id);
        
        return view('vendor.staff.edit', compact('staff'));
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        $staff = VendorStaff::with('user')
            ->where('vendor_id', $vendor->id)
            ->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->user_id,
            'password' => 'nullable|string|min:8|confirmed',
            'mobile_number' => 'nullable|string|max:20',
            'role' => 'required|string|max:50',
            'permissions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        
        try {
            // Update user account
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
            ];
            
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            
            $staff->user->update($userData);

            // Update vendor staff record
            $staff->update([
                'role' => $request->role,
                'permissions' => $request->permissions ?? [],
                'is_active' => $request->has('is_active'),
            ]);

            DB::commit();

            return redirect()->route('vendor.staff.index')
                ->with('success', 'Staff member updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error updating staff member: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy($id)
    {
        $vendor = $this->getVendor();
        
        $staff = VendorStaff::where('vendor_id', $vendor->id)
            ->findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            // Delete the user account
            $staff->user->delete();
            
            // Delete the vendor staff record
            $staff->delete();

            DB::commit();

            return redirect()->route('vendor.staff.index')
                ->with('success', 'Staff member removed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error removing staff member: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle staff active status.
     */
    public function toggleStatus($id)
    {
        $vendor = $this->getVendor();
        
        $staff = VendorStaff::where('vendor_id', $vendor->id)
            ->findOrFail($id);
        
        $staff->update(['is_active' => !$staff->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $staff->is_active,
            'message' => $staff->is_active ? 'Staff member activated.' : 'Staff member deactivated.'
        ]);
    }
}
