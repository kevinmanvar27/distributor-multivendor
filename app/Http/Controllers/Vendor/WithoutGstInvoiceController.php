<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WithoutGstInvoice;
use App\Models\Notification;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class WithoutGstInvoiceController extends Controller
{
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Display a listing of without GST invoices for the vendor.
     */
    public function index()
    {
        $vendor = $this->getVendor();
        
        $withoutGstInvoices = WithoutGstInvoice::with('user')
            ->where('vendor_id', $vendor->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Remove all without_gst_invoice notifications when visiting this page
        Notification::where('user_id', Auth::id())
            ->where('type', 'without_gst_invoice')
            ->delete();
        
        return view('vendor.invoices-black.index', compact('withoutGstInvoices'));
    }
    
    /**
     * Display the specified without GST invoice.
     */
    public function show($id)
    {
        $vendor = $this->getVendor();
        
        $invoice = WithoutGstInvoice::with('user')
            ->where('vendor_id', $vendor->id)
            ->findOrFail($id);
        
        $invoiceData = $invoice->invoice_data;
        
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        $cartItems = $invoiceData['cart_items'] ?? [];
        $total = $invoiceData['total'] ?? 0;
        $invoiceDate = $invoiceData['invoice_date'] ?? $invoice->created_at->format('Y-m-d');
        $customer = $invoiceData['customer'] ?? null;
        $invoiceNumber = $invoice->invoice_number;
        
        // Remove notifications for this invoice
        Notification::where('user_id', Auth::id())
            ->where('type', 'without_gst_invoice')
            ->where('data', 'like', '%"invoice_id":' . $id . '%')
            ->delete();
        
        return view('vendor.invoices-black.show', compact('invoice', 'cartItems', 'total', 'invoiceNumber', 'invoiceDate', 'customer', 'invoiceData'));
    }
    
    /**
     * Update the without GST invoice.
     */
    public function update(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        $invoice = WithoutGstInvoice::where('vendor_id', $vendor->id)->findOrFail($id);
        
        $invoiceData = $invoice->invoice_data;
        
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        // Update status if provided
        if ($request->has('status')) {
            $request->validate([
                'status' => 'required|in:' . implode(',', WithoutGstInvoice::STATUS_OPTIONS)
            ]);
            $invoice->status = $request->input('status');
        }
        
        // Update cart items if provided
        if ($request->has('items')) {
            $items = $request->input('items');
            $cartItems = [];
            
            foreach ($items as $index => $item) {
                $originalItem = $invoiceData['cart_items'][$index] ?? [];
                
                // Build updated item with price/quantity from request
                $updatedItem = [
                    'product_name' => $originalItem['product_name'] ?? 'Product',
                    'product_description' => $originalItem['product_description'] ?? '',
                    'price' => (float) $item['price'],
                    'quantity' => (int) $item['quantity'],
                    'total' => (float) $item['total'],
                ];
                
                // Preserve variation data if it exists in original item
                if (isset($originalItem['product_variation_id'])) {
                    $updatedItem['product_variation_id'] = $originalItem['product_variation_id'];
                }
                if (isset($originalItem['variation_display_name'])) {
                    $updatedItem['variation_display_name'] = $originalItem['variation_display_name'];
                }
                if (isset($originalItem['variation_attributes'])) {
                    $updatedItem['variation_attributes'] = $originalItem['variation_attributes'];
                }
                if (isset($originalItem['variation_sku'])) {
                    $updatedItem['variation_sku'] = $originalItem['variation_sku'];
                }
                
                $cartItems[] = $updatedItem;
            }
            
            $invoiceData['cart_items'] = $cartItems;
        }
        
        // Update invoice details (without GST - tax is always 0)
        $invoiceData['subtotal'] = (float) $request->input('subtotal', $invoiceData['subtotal'] ?? 0);
        $invoiceData['discount_percentage'] = (float) $request->input('discount_percentage', $invoiceData['discount_percentage'] ?? 0);
        $invoiceData['discount_amount'] = (float) $request->input('discount_amount', $invoiceData['discount_amount'] ?? 0);
        $invoiceData['shipping'] = (float) $request->input('shipping', $invoiceData['shipping'] ?? 0);
        $invoiceData['gst_type'] = 'without_gst';
        $invoiceData['tax_percentage'] = 0;
        $invoiceData['tax_amount'] = 0;
        $invoiceData['total'] = (float) $request->input('total', $invoiceData['total'] ?? 0);
        $invoiceData['notes'] = $request->input('notes', $invoiceData['notes'] ?? 'This is a proforma invoice without GST.');
        
        $invoice->total_amount = (float) $request->input('total', $invoice->total_amount);
        $invoice->invoice_data = $invoiceData;
        $invoice->save();
        
        return redirect()->back()->with('success', 'Without GST invoice updated successfully.');
    }
    
    /**
     * Update the status of the without GST invoice.
     */
    public function updateStatus(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        $invoice = WithoutGstInvoice::where('vendor_id', $vendor->id)->findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:' . implode(',', WithoutGstInvoice::STATUS_OPTIONS)
        ]);
        
        $invoice->status = $request->input('status');
        $invoice->save();
        
        return redirect()->back()->with('success', "Without GST invoice status updated to {$invoice->status} successfully.");
    }
    
    /**
     * Remove an item from the without GST invoice.
     * When an item is removed, the entire invoice is deleted.
     */
    public function removeItem(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        $invoice = WithoutGstInvoice::where('vendor_id', $vendor->id)->findOrFail($id);
        
        // Store invoice number for success message
        $invoiceNumber = $invoice->invoice_number;
        
        // Delete the entire invoice when removing an item
        $invoice->delete();
        
        return redirect()->route('vendor.invoices-black.index')
            ->with('success', "Invoice #{$invoiceNumber} has been deleted.");
    }
    
    /**
     * Delete the without GST invoice.
     */
    public function destroy($id)
    {
        $vendor = $this->getVendor();
        
        $invoice = WithoutGstInvoice::where('vendor_id', $vendor->id)->findOrFail($id);
        
        $invoice->delete();
        
        return redirect()->route('vendor.invoices-black.index')->with('success', 'Without GST invoice deleted successfully.');
    }
    
    /**
     * Download the invoice as PDF.
     */
    public function downloadPDF($id)
    {
        $vendor = $this->getVendor();
        
        $invoice = WithoutGstInvoice::where('vendor_id', $vendor->id)->findOrFail($id);
        
        $invoiceData = $invoice->invoice_data;
        
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        $cartItems = $invoiceData['cart_items'] ?? [];
        $total = $invoiceData['total'] ?? 0;
        $invoiceDate = $invoiceData['invoice_date'] ?? $invoice->created_at->format('Y-m-d');
        $customer = $invoiceData['customer'] ?? null;
        $invoiceNumber = $invoice->invoice_number;
        
        $pdf = Pdf::loadView('vendor.invoices-black.pdf', compact(
            'invoice',
            'cartItems',
            'total',
            'invoiceNumber',
            'invoiceDate',
            'customer',
            'invoiceData',
            'vendor'
        ));
        
        return $pdf->download('without-gst-invoice-' . $invoice->invoice_number . '.pdf');
    }
    
    /**
     * Add payment to the invoice.
     */
    public function addPayment(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        $invoice = WithoutGstInvoice::where('vendor_id', $vendor->id)->findOrFail($id);
        
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
}