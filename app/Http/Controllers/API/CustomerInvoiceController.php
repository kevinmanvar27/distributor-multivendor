<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProformaInvoice;
use App\Models\ShoppingCartItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\VendorCustomer;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * @OA\Tag(
 *     name="Customer Invoices",
 *     description="API Endpoints for Vendor Customer Invoices"
 * )
 */
class CustomerInvoiceController extends Controller
{
    /**
     * Get the authenticated customer
     */
    private function getCustomer(Request $request): VendorCustomer
    {
        return $request->user();
    }

    /**
     * Decode invoice data
     */
    private function decodeInvoiceData($invoiceData): ?array
    {
        if (is_array($invoiceData)) {
            return $invoiceData;
        }
        
        if (is_string($invoiceData)) {
            return json_decode($invoiceData, true);
        }
        
        return null;
    }

    /**
     * Get customer's invoices
     */
    public function index(Request $request)
    {
        $customer = $this->getCustomer($request);
        
        $query = ProformaInvoice::where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->orderBy('created_at', 'desc');
        
        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $perPage = min($request->get('per_page', 15), 50);
        $invoices = $query->paginate($perPage);
        
        // Transform invoices
        $invoices->getCollection()->transform(function ($invoice) {
            $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total_amount' => $invoice->total_amount,
                'paid_amount' => $invoice->paid_amount,
                'pending_amount' => $invoice->pending_amount,
                'payment_status' => $invoice->payment_status,
                'status' => $invoice->status,
                'items_count' => isset($invoiceData['cart_items']) ? count($invoiceData['cart_items']) : 0,
                'created_at' => $invoice->created_at,
                'updated_at' => $invoice->updated_at,
            ];
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Invoices retrieved successfully',
            'data' => $invoices
        ]);
    }

    /**
     * Get invoice details
     */
    public function show(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'data' => null
            ], 404);
        }

        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
        $vendor = $customer->vendor;

        // Ensure store details are in invoice data
        if (!isset($invoiceData['store']) && $vendor) {
            $invoiceData['store'] = [
                'id' => $vendor->id,
                'store_name' => $vendor->store_name,
                'store_slug' => $vendor->store_slug,
                'store_logo_url' => $vendor->store_logo_url,
                'business_name' => $vendor->business_name,
                'business_email' => $vendor->business_email,
                'business_phone' => $vendor->business_phone,
                'business_address' => $vendor->business_address,
                'city' => $vendor->city,
                'state' => $vendor->state,
                'postal_code' => $vendor->postal_code,
                'gst_number' => $vendor->gst_number ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoice retrieved successfully',
            'data' => [
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount,
                    'pending_amount' => $invoice->pending_amount,
                    'payment_status' => $invoice->payment_status,
                    'status' => $invoice->status,
                    'created_at' => $invoice->created_at,
                    'updated_at' => $invoice->updated_at,
                ],
                'invoice_data' => $invoiceData,
            ]
        ]);
    }

    /**
     * Download invoice as PDF
     */
    public function downloadPdf(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'data' => null
            ], 404);
        }

        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
        $vendor = $customer->vendor;

        // Prepare data for PDF
        $data = [
            'invoice' => $invoice,
            'invoiceData' => $invoiceData,
            'siteTitle' => $vendor->store_name ?? 'Store',
            'companyAddress' => $vendor->business_address ?? '',
            'companyEmail' => $vendor->business_email ?? '',
            'companyPhone' => $vendor->business_phone ?? '',
            'companyLogo' => $vendor->store_logo_url ?? null,
        ];

        // Load PDF view
        $pdf = Pdf::loadView('frontend.proforma-invoice-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('proforma-invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Add invoice items back to cart
     */
    public function addToCart(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'data' => null
            ], 404);
        }

        // Check if invoice is in draft status
        if ($invoice->status !== ProformaInvoice::STATUS_DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft invoices can be added to cart',
                'data' => null
            ], 400);
        }

        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
        $addedItems = [];
        $skippedItems = [];

        if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
            foreach ($invoiceData['cart_items'] as $item) {
                $product = Product::find($item['product_id']);
                
                if (!$product || $product->vendor_id != $customer->vendor_id) {
                    $skippedItems[] = $item['product_name'] ?? 'Unknown Product';
                    continue;
                }

                // Check if item already exists in cart
                $existingCartItem = ShoppingCartItem::where('vendor_customer_id', $customer->id)
                    ->where('product_id', $item['product_id'])
                    ->where('product_variation_id', $item['product_variation_id'] ?? null)
                    ->first();

                if ($existingCartItem) {
                    $existingCartItem->update([
                        'quantity' => $existingCartItem->quantity + $item['quantity'],
                        'price' => $item['price']
                    ]);
                } else {
                    ShoppingCartItem::create([
                        'vendor_customer_id' => $customer->id,
                        'product_id' => $item['product_id'],
                        'product_variation_id' => $item['product_variation_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }
                
                $addedItems[] = $item['product_name'];
            }
        }

        // Delete the invoice
        $invoice->delete();

        $message = 'Products from invoice added to cart successfully.';
        if (!empty($skippedItems)) {
            $message .= ' Some items were skipped: ' . implode(', ', $skippedItems);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'added_items' => $addedItems,
                'skipped_items' => $skippedItems,
                'cart_count' => ShoppingCartItem::where('vendor_customer_id', $customer->id)->count(),
            ]
        ]);
    }

    /**
     * Delete an invoice
     */
    public function destroy(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'data' => null
            ], 404);
        }

        // Restore stock for all items
        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
        
        if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
            foreach ($invoiceData['cart_items'] as $item) {
                if (!empty($item['product_variation_id'])) {
                    $variation = ProductVariation::find($item['product_variation_id']);
                    if ($variation) {
                        $variation->increment('stock_quantity', $item['quantity']);
                        if ($variation->fresh()->stock_quantity > 0 && !$variation->in_stock) {
                            $variation->update(['in_stock' => true]);
                        }
                    }
                } else {
                    $product = Product::find($item['product_id'] ?? null);
                    if ($product) {
                        $product->increment('stock_quantity', $item['quantity']);
                        if ($product->fresh()->stock_quantity > 0 && !$product->in_stock) {
                            $product->update(['in_stock' => true]);
                        }
                    }
                }
            }
        }

        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted and stock restored successfully',
            'data' => null
        ]);
    }

    /**
     * Remove a specific item from invoice
     */
    public function removeItem(Request $request, $id, $productId)
    {
        $customer = $this->getCustomer($request);
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'data' => null
            ], 404);
        }

        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);

        if (!isset($invoiceData['cart_items']) || !is_array($invoiceData['cart_items'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice has no items',
                'data' => null
            ], 400);
        }

        $itemFound = false;
        $removedItem = null;
        $newCartItems = [];

        foreach ($invoiceData['cart_items'] as $item) {
            if (($item['product_id'] ?? null) == $productId) {
                $itemFound = true;
                $removedItem = $item;
                
                // Restore stock
                if (!empty($item['product_variation_id'])) {
                    $variation = ProductVariation::find($item['product_variation_id']);
                    if ($variation) {
                        $variation->increment('stock_quantity', $item['quantity']);
                        if ($variation->fresh()->stock_quantity > 0 && !$variation->in_stock) {
                            $variation->update(['in_stock' => true]);
                        }
                    }
                } else {
                    $product = Product::find($productId);
                    if ($product) {
                        $product->increment('stock_quantity', $item['quantity']);
                        if ($product->fresh()->stock_quantity > 0 && !$product->in_stock) {
                            $product->update(['in_stock' => true]);
                        }
                    }
                }
            } else {
                $newCartItems[] = $item;
            }
        }

        if (!$itemFound) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in invoice',
                'data' => null
            ], 404);
        }

        // If invoice is empty, delete it
        if (empty($newCartItems)) {
            $invoice->delete();
            return response()->json([
                'success' => true,
                'message' => 'Last item removed. Invoice has been deleted.',
                'data' => [
                    'invoice_deleted' => true,
                    'removed_item' => $removedItem,
                ]
            ]);
        }

        // Update invoice
        $invoiceData['cart_items'] = $newCartItems;
        $newTotal = array_reduce($newCartItems, function ($carry, $item) {
            return $carry + (($item['price'] ?? 0) * ($item['quantity'] ?? 0));
        }, 0);
        
        $invoiceData['total'] = $newTotal;

        $invoice->update([
            'invoice_data' => $invoiceData,
            'total_amount' => $newTotal,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from invoice successfully',
            'data' => [
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $newTotal,
                ],
                'removed_item' => $removedItem,
                'invoice_deleted' => false,
            ]
        ]);
    }
}
