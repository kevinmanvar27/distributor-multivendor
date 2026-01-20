<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProformaInvoice;
use App\Models\Product;
use App\Models\Vendor;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        $user = Auth::user();
        return $user->vendor ?? $user->vendorStaff?->vendor;
    }

    /**
     * Display a listing of vendor orders
     */
    public function index(Request $request)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.login')->with('error', 'Vendor profile not found.');
        }

        // Get filters
        $status = $request->get('status');
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Get all orders containing vendor's products
        $vendorOrders = $this->getVendorOrders($vendor->id, $status, $search, $dateFrom, $dateTo);

        // Calculate statistics
        $stats = [
            'total' => $vendorOrders->count(),
            'pending' => $vendorOrders->whereIn('status', [
                ProformaInvoice::STATUS_DRAFT,
                ProformaInvoice::STATUS_APPROVED,
                ProformaInvoice::STATUS_DISPATCH,
                ProformaInvoice::STATUS_OUT_FOR_DELIVERY
            ])->count(),
            'delivered' => $vendorOrders->where('status', ProformaInvoice::STATUS_DELIVERED)->count(),
            'returned' => $vendorOrders->where('status', ProformaInvoice::STATUS_RETURN)->count(),
            'total_revenue' => $vendorOrders->where('status', ProformaInvoice::STATUS_DELIVERED)->sum('vendor_total'),
        ];

        // Paginate the results
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);
        $orders = $vendorOrders->forPage($page, $perPage);
        
        // Create a simple paginator
        $pagination = new \Illuminate\Pagination\LengthAwarePaginator(
            $orders,
            $vendorOrders->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('vendor.orders.index', compact('pagination', 'stats', 'vendor', 'status', 'search', 'dateFrom', 'dateTo'));
    }

    /**
     * Display the specified order
     */
    public function show($id)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.login')->with('error', 'Vendor profile not found.');
        }

        $invoice = ProformaInvoice::with('user')->findOrFail($id);
        
        // Get vendor-specific items from this order
        $vendorItems = $this->getVendorItemsFromInvoice($invoice, $vendor->id);
        
        if (empty($vendorItems)) {
            return redirect()->route('vendor.orders.index')
                ->with('error', 'This order does not contain any of your products.');
        }

        $vendorTotal = collect($vendorItems)->sum('total');
        $commissionRate = $vendor->commission_rate ?? 0;
        $commission = $vendorTotal * ($commissionRate / 100);
        $netEarnings = $vendorTotal - $commission;

        return view('vendor.orders.show', compact('invoice', 'vendorItems', 'vendorTotal', 'vendor', 'commissionRate', 'commission', 'netEarnings'));
    }

    /**
     * Get orders containing vendor's products
     */
    private function getVendorOrders($vendorId, $status = null, $search = null, $dateFrom = null, $dateTo = null)
    {
        $query = ProformaInvoice::with('user')->whereNotNull('invoice_data');
        
        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }
        
        // Apply date filters
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        
        $allInvoices = $query->orderBy('created_at', 'desc')->get();
        
        $vendorOrders = collect();
        
        foreach ($allInvoices as $invoice) {
            $invoiceData = $invoice->invoice_data;
            $vendorTotal = 0;
            $hasVendorProducts = false;
            $vendorProductCount = 0;
            
            if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
                foreach ($invoiceData['cart_items'] as $item) {
                    $productId = $item['product_id'] ?? $item['id'] ?? null;
                    if ($productId) {
                        $product = Product::find($productId);
                        if ($product && $product->vendor_id == $vendorId) {
                            $hasVendorProducts = true;
                            $vendorProductCount++;
                            $vendorTotal += $item['total'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 1));
                        }
                    }
                }
            }
            
            if ($hasVendorProducts) {
                // Apply search filter
                if ($search) {
                    $searchLower = strtolower($search);
                    $invoiceNumber = strtolower($invoice->invoice_number ?? 'INV-' . $invoice->id);
                    $customerName = strtolower($invoice->user->name ?? '');
                    $customerEmail = strtolower($invoice->user->email ?? '');
                    
                    if (strpos($invoiceNumber, $searchLower) === false && 
                        strpos($customerName, $searchLower) === false &&
                        strpos($customerEmail, $searchLower) === false) {
                        continue;
                    }
                }
                
                $vendorOrders->push([
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number ?? 'INV-' . $invoice->id,
                    'user' => $invoice->user,
                    'status' => $invoice->status,
                    'payment_status' => $invoice->payment_status,
                    'total_amount' => $invoice->total_amount,
                    'vendor_total' => $vendorTotal,
                    'vendor_product_count' => $vendorProductCount,
                    'created_at' => $invoice->created_at,
                ]);
            }
        }
        
        return $vendorOrders;
    }

    /**
     * Get vendor-specific items from an invoice
     */
    private function getVendorItemsFromInvoice($invoice, $vendorId)
    {
        $vendorItems = [];
        $invoiceData = $invoice->invoice_data;
        
        if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
            foreach ($invoiceData['cart_items'] as $item) {
                $productId = $item['product_id'] ?? $item['id'] ?? null;
                if ($productId) {
                    $product = Product::find($productId);
                    if ($product && $product->vendor_id == $vendorId) {
                        $vendorItems[] = [
                            'product_id' => $productId,
                            'product' => $product,
                            'name' => $item['name'] ?? $item['product_name'] ?? $product->name ?? 'Unknown Product',
                            'sku' => $item['sku'] ?? $product->sku ?? '',
                            'quantity' => $item['quantity'] ?? 1,
                            'price' => $item['price'] ?? 0,
                            'total' => $item['total'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 1)),
                            'image' => $product->primary_image ?? null,
                        ];
                    }
                }
            }
        }
        
        return $vendorItems;
    }

    /**
     * Export orders to CSV
     */
    public function export(Request $request)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.login')->with('error', 'Vendor profile not found.');
        }

        $status = $request->get('status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        $vendorOrders = $this->getVendorOrders($vendor->id, $status, null, $dateFrom, $dateTo);
        
        $filename = 'vendor_orders_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($vendorOrders, $vendor) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Order ID',
                'Invoice Number',
                'Customer Name',
                'Customer Email',
                'Status',
                'Payment Status',
                'Order Total',
                'Your Revenue',
                'Commission (' . ($vendor->commission_rate ?? 0) . '%)',
                'Net Earnings',
                'Date'
            ]);
            
            foreach ($vendorOrders as $order) {
                $commission = $order['vendor_total'] * (($vendor->commission_rate ?? 0) / 100);
                $netEarnings = $order['vendor_total'] - $commission;
                
                fputcsv($file, [
                    $order['id'],
                    $order['invoice_number'],
                    $order['user']->name ?? 'N/A',
                    $order['user']->email ?? 'N/A',
                    $order['status'],
                    $order['payment_status'] ?? 'N/A',
                    number_format($order['total_amount'], 2),
                    number_format($order['vendor_total'], 2),
                    number_format($commission, 2),
                    number_format($netEarnings, 2),
                    Carbon::parse($order['created_at'])->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
