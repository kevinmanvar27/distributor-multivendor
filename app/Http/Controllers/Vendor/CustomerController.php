<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ProformaInvoice;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Display a listing of customers for the vendor.
     */
    public function index(Request $request)
    {
        $vendor = $this->getVendor();
        
        $query = User::where('user_role', 'user')
            ->where('vendor_id', $vendor->id);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $customers = $query->withCount(['proformaInvoices as orders_count' => function($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id);
        }])
        ->orderBy('created_at', 'desc')
        ->paginate(20);
        
        // Calculate statistics
        $totalCustomers = User::where('user_role', 'user')
            ->where('vendor_id', $vendor->id)
            ->count();
            
        $newCustomersThisMonth = User::where('user_role', 'user')
            ->where('vendor_id', $vendor->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        return view('vendor.customers.index', compact(
            'customers',
            'totalCustomers',
            'newCustomersThisMonth'
        ));
    }

    /**
     * Display the specified customer.
     */
    public function show($id)
    {
        $vendor = $this->getVendor();
        
        $customer = User::where('id', $id)
            ->where('user_role', 'user')
            ->where('vendor_id', $vendor->id)
            ->firstOrFail();
        
        // Get customer's orders from this vendor
        $orders = ProformaInvoice::where('user_id', $customer->id)
            ->where('vendor_id', $vendor->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Calculate customer statistics
        $totalOrders = ProformaInvoice::where('user_id', $customer->id)
            ->where('vendor_id', $vendor->id)
            ->count();
            
        $totalSpent = ProformaInvoice::where('user_id', $customer->id)
            ->where('vendor_id', $vendor->id)
            ->sum('total_amount');
        
        return view('vendor.customers.show', compact(
            'customer',
            'orders',
            'totalOrders',
            'totalSpent'
        ));
    }
}
