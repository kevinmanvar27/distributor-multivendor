<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ProformaInvoice;
use App\Models\VendorCustomer;
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
     * Shows customers who have sent invoices to this vendor.
     */
    public function index(Request $request)
    {
        $vendor = $this->getVendor();
        
        // Get customers from vendor_customers table (users who have sent invoices)
        $query = User::whereHas('customerOfVendors', function($q) use ($vendor) {
            $q->where('vendors.id', $vendor->id);
        })->where('user_role', 'user');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }
        
        $customers = $query->withCount(['proformaInvoices as orders_count' => function($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id);
        }])
        ->with(['customerOfVendors' => function($q) use ($vendor) {
            $q->where('vendors.id', $vendor->id);
        }])
        ->orderBy('created_at', 'desc')
        ->paginate(20);
        
        // Calculate statistics
        $totalCustomers = VendorCustomer::where('vendor_id', $vendor->id)->count();
            
        $newCustomersThisMonth = VendorCustomer::where('vendor_id', $vendor->id)
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
        
        // Check if user is a customer of this vendor (has sent invoices)
        $customer = User::where('id', $id)
            ->where('user_role', 'user')
            ->whereHas('customerOfVendors', function($q) use ($vendor) {
                $q->where('vendors.id', $vendor->id);
            })
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
        
        // Get when this customer first ordered from this vendor
        $vendorCustomer = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('user_id', $customer->id)
            ->first();
        $customerSince = $vendorCustomer ? $vendorCustomer->created_at : null;
        
        return view('vendor.customers.show', compact(
            'customer',
            'orders',
            'totalOrders',
            'totalSpent',
            'customerSince'
        ));
    }
}
