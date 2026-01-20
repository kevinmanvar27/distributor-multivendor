<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProformaInvoice;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PendingBillController extends Controller
{
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Display a listing of pending bills for the vendor.
     */
    public function index(Request $request)
    {
        $vendor = $this->getVendor();
        
        // Get filter parameters
        $userId = $request->get('user_id');
        $paymentStatus = $request->get('payment_status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Build query - only for this vendor
        $query = ProformaInvoice::with('user')
            ->where('vendor_id', $vendor->id)
            ->where('status', '!=', 'Return');

        // Apply filters
        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $invoices = $query->orderBy('created_at', 'desc')->get();

        // Calculate summary statistics
        $totalBills = $invoices->count();
        $totalAmount = $invoices->sum('total_amount');
        $totalPaid = $invoices->sum('paid_amount');
        $totalPending = $totalAmount - $totalPaid;

        // Get unpaid and partial bills count
        $unpaidBills = $invoices->where('payment_status', 'unpaid')->count();
        $partialBills = $invoices->where('payment_status', 'partial')->count();
        $paidBills = $invoices->where('payment_status', 'paid')->count();

        // Get users who have invoices with this vendor
        $userIds = ProformaInvoice::where('vendor_id', $vendor->id)
            ->distinct()
            ->pluck('user_id');
        $users = User::whereIn('id', $userIds)->orderBy('name')->get();

        return view('vendor.pending-bills.index', compact(
            'invoices',
            'totalBills',
            'totalAmount',
            'totalPaid',
            'totalPending',
            'unpaidBills',
            'partialBills',
            'paidBills',
            'users',
            'userId',
            'paymentStatus',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Show pending bills for a specific user.
     */
    public function userBills($userId)
    {
        $vendor = $this->getVendor();
        $user = User::findOrFail($userId);
        
        $invoices = ProformaInvoice::with('user')
            ->where('vendor_id', $vendor->id)
            ->where('user_id', $userId)
            ->where('status', '!=', 'Return')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate user-specific statistics
        $totalAmount = $invoices->sum('total_amount');
        $totalPaid = $invoices->sum('paid_amount');
        $totalPending = $totalAmount - $totalPaid;

        return view('vendor.pending-bills.user-bills', compact(
            'user',
            'invoices',
            'totalAmount',
            'totalPaid',
            'totalPending'
        ));
    }

    /**
     * Show details of a specific invoice - redirects to invoices.show
     */
    public function show(ProformaInvoice $invoice)
    {
        $vendor = $this->getVendor();
        
        // Ensure invoice belongs to vendor
        if ($invoice->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }
        
        // Redirect to the invoice show page
        return redirect()->route('vendor.invoices.show', $invoice->id);
    }

    /**
     * Record a payment for an invoice.
     */
    public function recordPayment(Request $request, ProformaInvoice $invoice)
    {
        $vendor = $this->getVendor();
        
        // Ensure invoice belongs to vendor
        if ($invoice->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->pending_amount,
            'payment_method' => 'required|string',
            'notes' => 'nullable|string|max:500',
        ]);

        $newPaidAmount = $invoice->paid_amount + $request->amount;
        
        // Determine new payment status
        if ($newPaidAmount >= $invoice->total_amount) {
            $paymentStatus = 'paid';
        } elseif ($newPaidAmount > 0) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'unpaid';
        }

        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'payment_status' => $paymentStatus,
        ]);

        return redirect()->back()->with('success', 'Payment of ₹' . number_format($request->amount, 2) . ' recorded successfully.');
    }

    /**
     * Add a payment for an invoice (from invoice show page modal).
     */
    public function addPayment(Request $request, ProformaInvoice $invoice)
    {
        $vendor = $this->getVendor();
        
        // Ensure invoice belongs to vendor
        if ($invoice->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }
        
        $pendingAmount = $invoice->total_amount - $invoice->paid_amount;
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $pendingAmount,
        ]);

        $newPaidAmount = $invoice->paid_amount + $request->amount;
        
        // Determine new payment status
        if ($newPaidAmount >= $invoice->total_amount) {
            $paymentStatus = 'paid';
        } elseif ($newPaidAmount > 0) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'unpaid';
        }

        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'payment_status' => $paymentStatus,
        ]);

        return redirect()->back()->with('success', 'Payment of ₹' . number_format($request->amount, 2) . ' added successfully.');
    }

    /**
     * Get summary statistics for the dashboard widget.
     */
    public function summary()
    {
        $vendor = $this->getVendor();
        
        $stats = ProformaInvoice::where('vendor_id', $vendor->id)
            ->where('status', '!=', 'Return')
            ->selectRaw('
                COUNT(*) as total_bills,
                SUM(total_amount) as total_amount,
                SUM(paid_amount) as total_paid,
                SUM(total_amount - paid_amount) as total_pending,
                SUM(CASE WHEN payment_status = "unpaid" THEN 1 ELSE 0 END) as unpaid_count,
                SUM(CASE WHEN payment_status = "partial" THEN 1 ELSE 0 END) as partial_count
            ')
            ->first();

        return response()->json($stats);
    }
}