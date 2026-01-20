<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProformaInvoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Display a listing of invoices for the vendor.
     */
    public function index(Request $request)
    {
        $vendor = $this->getVendor();
        
        // Get filter parameters
        $status = $request->get('status');
        $paymentStatus = $request->get('payment_status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Build query - only for this vendor
        $query = ProformaInvoice::with('user')
            ->where('vendor_id', $vendor->id);

        // Apply filters
        if ($status) {
            $query->where('status', $status);
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

        $invoices = $query->orderBy('created_at', 'desc')->paginate(25);

        // Calculate statistics
        $totalInvoices = ProformaInvoice::where('vendor_id', $vendor->id)->count();
        $totalAmount = ProformaInvoice::where('vendor_id', $vendor->id)->sum('total_amount');
        $deliveredCount = ProformaInvoice::where('vendor_id', $vendor->id)->where('status', 'Delivered')->count();
        $pendingCount = ProformaInvoice::where('vendor_id', $vendor->id)->whereNotIn('status', ['Delivered', 'Return'])->count();

        return view('vendor.invoices.index', compact(
            'invoices',
            'totalInvoices',
            'totalAmount',
            'deliveredCount',
            'pendingCount',
            'status',
            'paymentStatus',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create()
    {
        $vendor = $this->getVendor();
        
        // Get vendor's products
        $products = Product::where('vendor_id', $vendor->id)
            ->where('status', 'published')
            ->orderBy('name')
            ->get();
        
        // Get customers - only those registered with this vendor
        $customers = User::where('user_role', 'user')
            ->where('vendor_id', $vendor->id)
            ->orderBy('name')
            ->get();

        return view('vendor.invoices.create', compact('products', 'customers'));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        $vendor = $this->getVendor();
        
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        // Generate invoice number
        $lastInvoice = ProformaInvoice::where('vendor_id', $vendor->id)
            ->orderBy('id', 'desc')
            ->first();
        $invoiceNumber = 'INV-' . $vendor->id . '-' . str_pad(($lastInvoice ? $lastInvoice->id + 1 : 1), 6, '0', STR_PAD_LEFT);

        // Build cart items
        $cartItems = [];
        $total = 0;
        
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if ($product && $product->vendor_id === $vendor->id) {
                $itemTotal = $item['price'] * $item['quantity'];
                $cartItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total' => $itemTotal,
                ];
                $total += $itemTotal;
            }
        }

        // Create invoice
        $invoice = ProformaInvoice::create([
            'invoice_number' => $invoiceNumber,
            'user_id' => $request->user_id,
            'vendor_id' => $vendor->id,
            'total_amount' => $total,
            'paid_amount' => 0,
            'payment_status' => 'unpaid',
            'status' => 'Draft',
            'invoice_data' => [
                'cart_items' => $cartItems,
                'total' => $total,
                'invoice_date' => Carbon::now()->format('Y-m-d'),
                'customer' => $request->user_id ? User::find($request->user_id)->toArray() : null,
            ],
        ]);

        return redirect()->route('vendor.invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified invoice.
     */
    public function show(ProformaInvoice $invoice)
    {
        $vendor = $this->getVendor();
        
        // Ensure invoice belongs to vendor
        if ($invoice->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }
        
        $invoice->load('user');
        
        // Get invoice data
        $invoiceData = $invoice->invoice_data;
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
        }
        
        $cartItems = $invoiceData['cart_items'] ?? [];
        $total = $invoiceData['total'] ?? $invoice->total_amount;
        $invoiceDate = $invoiceData['invoice_date'] ?? $invoice->created_at->format('Y-m-d');
        $customer = $invoiceData['customer'] ?? null;

        return view('vendor.invoices.show', compact(
            'invoice',
            'cartItems',
            'total',
            'invoiceDate',
            'customer',
            'invoiceData'
        ));
    }

    /**
     * Print the invoice.
     */
    public function print(ProformaInvoice $invoice)
    {
        $vendor = $this->getVendor();
        
        // Ensure invoice belongs to vendor
        if ($invoice->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }
        
        $invoice->load('user');
        
        // Get invoice data
        $invoiceData = $invoice->invoice_data;
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
        }
        
        $cartItems = $invoiceData['cart_items'] ?? [];
        $total = $invoiceData['total'] ?? $invoice->total_amount;
        $invoiceDate = $invoiceData['invoice_date'] ?? $invoice->created_at->format('Y-m-d');
        $customer = $invoiceData['customer'] ?? null;

        return view('vendor.invoices.print', compact(
            'invoice',
            'cartItems',
            'total',
            'invoiceDate',
            'customer',
            'vendor'
        ));
    }

    /**
     * Download the invoice as PDF.
     */
    public function download(ProformaInvoice $invoice)
    {
        $vendor = $this->getVendor();
        
        // Ensure invoice belongs to vendor
        if ($invoice->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }
        
        $invoice->load('user');
        
        // Get invoice data
        $invoiceData = $invoice->invoice_data;
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            // Handle double-encoded JSON
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        $cartItems = $invoiceData['cart_items'] ?? [];
        $total = $invoiceData['total'] ?? $invoice->total_amount;
        $invoiceDate = $invoiceData['invoice_date'] ?? $invoice->created_at->format('Y-m-d');
        $customer = $invoiceData['customer'] ?? null;
        $invoiceNumber = $invoice->invoice_number;

        $pdf = Pdf::loadView('vendor.invoices.pdf', compact(
            'invoice',
            'invoiceData',
            'cartItems',
            'total',
            'invoiceDate',
            'invoiceNumber',
            'customer',
            'vendor'
        ));

        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Update invoice status.
     */
    public function updateStatus(Request $request, ProformaInvoice $invoice)
    {
        $vendor = $this->getVendor();
        
        // Ensure invoice belongs to vendor
        if ($invoice->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }
        
        $request->validate([
            'status' => 'required|in:' . implode(',', ProformaInvoice::STATUS_OPTIONS),
        ]);

        $invoice->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Invoice status updated successfully.');
    }

    /**
     * Show the form for editing an invoice.
     */
    public function edit(ProformaInvoice $invoice)
    {
        $vendor = $this->getVendor();
        
        // Ensure invoice belongs to vendor
        if ($invoice->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }
        
        // Get vendor's products
        $products = Product::where('vendor_id', $vendor->id)
            ->where('status', 'published')
            ->orderBy('name')
            ->get();
        
        // Get customers
        $customers = User::where('user_role', 'user')
            ->where('vendor_id', $vendor->id)
            ->orderBy('name')
            ->get();
        
        // Get invoice data
        $invoiceData = $invoice->invoice_data;
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
        }
        
        $cartItems = $invoiceData['cart_items'] ?? [];

        return view('vendor.invoices.edit', compact('invoice', 'products', 'customers', 'cartItems'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, ProformaInvoice $invoice)
    {
        $vendor = $this->getVendor();
        
        // Ensure invoice belongs to vendor
        if ($invoice->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }
        
        // Get existing invoice data
        $existingInvoiceData = $invoice->invoice_data;
        if (is_string($existingInvoiceData)) {
            $existingInvoiceData = json_decode($existingInvoiceData, true);
        }
        
        // Build updated cart items from request
        $cartItems = [];
        $subtotal = 0;
        
        if ($request->has('items')) {
            foreach ($request->items as $index => $item) {
                $existingItem = $existingInvoiceData['cart_items'][$index] ?? [];
                $price = floatval($item['price'] ?? $existingItem['price'] ?? 0);
                $quantity = intval($item['quantity'] ?? $existingItem['quantity'] ?? 1);
                $itemTotal = $price * $quantity;
                
                $cartItems[] = array_merge($existingItem, [
                    'price' => $price,
                    'quantity' => $quantity,
                    'total' => $itemTotal,
                ]);
                $subtotal += $itemTotal;
            }
        } else {
            $cartItems = $existingInvoiceData['cart_items'] ?? [];
            foreach ($cartItems as $item) {
                $subtotal += floatval($item['total'] ?? 0);
            }
        }
        
        // Get GST type and calculate accordingly
        $gstType = $request->gst_type ?? $existingInvoiceData['gst_type'] ?? 'with_gst';
        $taxPercentage = $gstType === 'without_gst' ? 0 : floatval($request->tax_percentage ?? $existingInvoiceData['tax_percentage'] ?? 18);
        $taxAmount = $gstType === 'without_gst' ? 0 : ($subtotal * $taxPercentage / 100);
        $shipping = floatval($request->shipping ?? $existingInvoiceData['shipping'] ?? 0);
        $discountAmount = floatval($request->discount_amount ?? $existingInvoiceData['discount_amount'] ?? 0);
        $couponDiscount = floatval($existingInvoiceData['coupon_discount'] ?? 0);
        
        // Calculate total
        $total = ($subtotal + $shipping + $taxAmount) - $discountAmount - $couponDiscount;
        
        // Build updated invoice data
        $invoiceData = array_merge($existingInvoiceData, [
            'cart_items' => $cartItems,
            'subtotal' => $subtotal,
            'total' => $total,
            'gst_type' => $gstType,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'shipping' => $shipping,
            'discount_amount' => $discountAmount,
            'notes' => $request->notes ?? $existingInvoiceData['notes'] ?? '',
        ]);
        
        // Update status if provided
        $status = $request->status ?? $invoice->status;

        // Update invoice
        $invoice->update([
            'status' => $status,
            'total_amount' => $total,
            'invoice_data' => $invoiceData,
        ]);

        return redirect()->route('vendor.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Remove an item from the invoice.
     */
    public function removeItem(Request $request, ProformaInvoice $invoice)
    {
        $vendor = $this->getVendor();
        
        // Ensure invoice belongs to vendor
        if ($invoice->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }
        
        $itemIndex = $request->item_index;
        
        // Get existing invoice data
        $invoiceData = $invoice->invoice_data;
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
        }
        
        $cartItems = $invoiceData['cart_items'] ?? [];
        
        // Remove the item at the specified index
        if (isset($cartItems[$itemIndex])) {
            array_splice($cartItems, $itemIndex, 1);
        }
        
        // Recalculate totals
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += floatval($item['total'] ?? 0);
        }
        
        $gstType = $invoiceData['gst_type'] ?? 'with_gst';
        $taxPercentage = $gstType === 'without_gst' ? 0 : floatval($invoiceData['tax_percentage'] ?? 18);
        $taxAmount = $gstType === 'without_gst' ? 0 : ($subtotal * $taxPercentage / 100);
        $shipping = floatval($invoiceData['shipping'] ?? 0);
        $discountAmount = floatval($invoiceData['discount_amount'] ?? 0);
        $couponDiscount = floatval($invoiceData['coupon_discount'] ?? 0);
        
        $total = ($subtotal + $shipping + $taxAmount) - $discountAmount - $couponDiscount;
        
        // Update invoice data
        $invoiceData['cart_items'] = $cartItems;
        $invoiceData['subtotal'] = $subtotal;
        $invoiceData['total'] = $total;
        $invoiceData['tax_amount'] = $taxAmount;
        
        // Update invoice
        $invoice->update([
            'total_amount' => $total,
            'invoice_data' => $invoiceData,
        ]);

        return redirect()->route('vendor.invoices.show', $invoice)
            ->with('success', 'Item removed from invoice successfully.');
    }

    /**
     * Delete the specified invoice.
     */
    public function destroy(ProformaInvoice $invoice)
    {
        $vendor = $this->getVendor();
        
        // Ensure invoice belongs to vendor
        if ($invoice->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }
        
        // Only allow deletion of draft invoices
        if ($invoice->status !== 'Draft') {
            return redirect()->back()->with('error', 'Only draft invoices can be deleted.');
        }
        
        $invoice->delete();

        return redirect()->route('vendor.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }
}