<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProformaInvoice;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show the vendor dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $vendor = $user->vendor;

        if (!$vendor) {
            return redirect()->route('vendor.login')->with('error', 'Vendor profile not found.');
        }

        // Basic counts for this vendor
        $productCount = Product::where('vendor_id', $vendor->id)->count();
        $categoryCount = Category::where('vendor_id', $vendor->id)->count();

        // Get orders that contain this vendor's products
        $vendorOrders = $this->getVendorOrders($vendor->id);
        
        // Revenue statistics
        $totalRevenue = $vendorOrders['delivered']->sum('vendor_total');
        $monthlyRevenue = $vendorOrders['delivered']
            ->filter(function($order) {
                return Carbon::parse($order['created_at'])->isCurrentMonth();
            })
            ->sum('vendor_total');
        
        $lastMonthRevenue = $vendorOrders['delivered']
            ->filter(function($order) {
                $date = Carbon::parse($order['created_at']);
                return $date->month === Carbon::now()->subMonth()->month 
                    && $date->year === Carbon::now()->subMonth()->year;
            })
            ->sum('vendor_total');
        
        $revenueGrowth = $lastMonthRevenue > 0 
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) 
            : ($monthlyRevenue > 0 ? 100 : 0);

        // Order statistics
        $totalOrders = $vendorOrders['all']->count();
        $pendingOrders = $vendorOrders['pending']->count();
        $deliveredOrders = $vendorOrders['delivered']->count();
        
        // Today's statistics
        $todayOrders = $vendorOrders['all']
            ->filter(function($order) {
                return Carbon::parse($order['created_at'])->isToday();
            })
            ->count();
        $todayRevenue = $vendorOrders['delivered']
            ->filter(function($order) {
                return Carbon::parse($order['created_at'])->isToday();
            })
            ->sum('vendor_total');

        // Monthly revenue chart data (last 12 months)
        $monthlyRevenueData = $this->getMonthlyRevenueData($vendorOrders['delivered']);
        
        // Weekly revenue chart data (last 7 days)
        $weeklyRevenueData = $this->getWeeklyRevenueData($vendorOrders['delivered']);

        // Order status distribution
        $orderStatusData = $this->getOrderStatusDistribution($vendorOrders);

        // Recent orders
        $recentOrders = $vendorOrders['all']->take(5);

        // Low stock products
        $lowStockProducts = Product::where('vendor_id', $vendor->id)
            ->where('in_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_quantity_threshold')
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get();
        
        // Out of stock products count
        $outOfStockCount = Product::where('vendor_id', $vendor->id)
            ->where(function($query) {
                $query->where('in_stock', false)
                      ->orWhere('stock_quantity', '<=', 0);
            })->count();

        // Top selling products
        $topProducts = $this->getTopSellingProducts($vendorOrders['delivered']);

        // Commission info
        $commissionRate = $vendor->commission_rate;
        $totalCommission = $totalRevenue * ($commissionRate / 100);
        $netEarnings = $totalRevenue - $totalCommission;

        return view('vendor.dashboard.index', compact(
            'vendor',
            'productCount', 
            'categoryCount',
            'totalRevenue',
            'monthlyRevenue',
            'revenueGrowth',
            'totalOrders',
            'pendingOrders',
            'deliveredOrders',
            'todayOrders',
            'todayRevenue',
            'monthlyRevenueData',
            'weeklyRevenueData',
            'orderStatusData',
            'recentOrders',
            'lowStockProducts',
            'outOfStockCount',
            'topProducts',
            'commissionRate',
            'totalCommission',
            'netEarnings'
        ));
    }

    /**
     * Get orders containing vendor's products
     */
    private function getVendorOrders($vendorId)
    {
        $allInvoices = ProformaInvoice::whereNotNull('invoice_data')->get();
        
        $vendorOrders = collect();
        
        foreach ($allInvoices as $invoice) {
            $invoiceData = $invoice->invoice_data;
            $vendorTotal = 0;
            $hasVendorProducts = false;
            
            if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
                foreach ($invoiceData['cart_items'] as $item) {
                    $productId = $item['product_id'] ?? $item['id'] ?? null;
                    if ($productId) {
                        $product = Product::find($productId);
                        if ($product && $product->vendor_id == $vendorId) {
                            $hasVendorProducts = true;
                            $vendorTotal += $item['total'] ?? (($item['price'] ?? 0) * ($item['quantity'] ?? 1));
                        }
                    }
                }
            }
            
            if ($hasVendorProducts) {
                $vendorOrders->push([
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number ?? 'INV-' . $invoice->id,
                    'user' => $invoice->user,
                    'status' => $invoice->status,
                    'vendor_total' => $vendorTotal,
                    'created_at' => $invoice->created_at,
                ]);
            }
        }
        
        return [
            'all' => $vendorOrders->sortByDesc('created_at'),
            'delivered' => $vendorOrders->where('status', ProformaInvoice::STATUS_DELIVERED),
            'pending' => $vendorOrders->whereIn('status', [
                ProformaInvoice::STATUS_DRAFT,
                ProformaInvoice::STATUS_APPROVED,
                ProformaInvoice::STATUS_DISPATCH,
                ProformaInvoice::STATUS_OUT_FOR_DELIVERY
            ]),
        ];
    }

    /**
     * Get monthly revenue data for the last 12 months
     */
    private function getMonthlyRevenueData($deliveredOrders)
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = $deliveredOrders
                ->filter(function($order) use ($date) {
                    $orderDate = Carbon::parse($order['created_at']);
                    return $orderDate->month === $date->month && $orderDate->year === $date->year;
                })
                ->sum('vendor_total');
            
            $data[] = [
                'month' => $date->format('M Y'),
                'short_month' => $date->format('M'),
                'revenue' => round($revenue, 2)
            ];
        }
        return $data;
    }

    /**
     * Get weekly revenue data for the last 7 days
     */
    private function getWeeklyRevenueData($deliveredOrders)
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = $deliveredOrders
                ->filter(function($order) use ($date) {
                    return Carbon::parse($order['created_at'])->isSameDay($date);
                })
                ->sum('vendor_total');
            
            $orders = $deliveredOrders
                ->filter(function($order) use ($date) {
                    return Carbon::parse($order['created_at'])->isSameDay($date);
                })
                ->count();
            
            $data[] = [
                'day' => $date->format('D'),
                'date' => $date->format('M d'),
                'revenue' => round($revenue, 2),
                'orders' => $orders
            ];
        }
        return $data;
    }

    /**
     * Get order status distribution
     */
    private function getOrderStatusDistribution($vendorOrders)
    {
        return [
            ['status' => 'Draft', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_DRAFT)->count(), 'color' => '#6c757d'],
            ['status' => 'Approved', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_APPROVED)->count(), 'color' => '#0d6efd'],
            ['status' => 'Dispatch', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_DISPATCH)->count(), 'color' => '#0dcaf0'],
            ['status' => 'Out for Delivery', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_OUT_FOR_DELIVERY)->count(), 'color' => '#ffc107'],
            ['status' => 'Delivered', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_DELIVERED)->count(), 'color' => '#198754'],
            ['status' => 'Return', 'count' => $vendorOrders['all']->where('status', ProformaInvoice::STATUS_RETURN)->count(), 'color' => '#dc3545'],
        ];
    }

    /**
     * Get top selling products
     */
    private function getTopSellingProducts($deliveredOrders)
    {
        $productSales = [];
        $vendor = Auth::user()->vendor;
        
        foreach ($deliveredOrders as $order) {
            $invoice = ProformaInvoice::find($order['id']);
            if (!$invoice) continue;
            
            $invoiceData = $invoice->invoice_data;
            if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
                foreach ($invoiceData['cart_items'] as $item) {
                    $productId = $item['product_id'] ?? $item['id'] ?? null;
                    if ($productId) {
                        $product = Product::find($productId);
                        if ($product && $product->vendor_id == $vendor->id) {
                            $productName = $item['name'] ?? $item['product_name'] ?? 'Unknown Product';
                            $quantity = $item['quantity'] ?? 1;
                            $total = $item['total'] ?? ($item['price'] ?? 0) * $quantity;
                            
                            if (!isset($productSales[$productId])) {
                                $productSales[$productId] = [
                                    'id' => $productId,
                                    'name' => $productName,
                                    'quantity' => 0,
                                    'revenue' => 0
                                ];
                            }
                            $productSales[$productId]['quantity'] += $quantity;
                            $productSales[$productId]['revenue'] += $total;
                        }
                    }
                }
            }
        }
        
        usort($productSales, function($a, $b) {
            return $b['quantity'] - $a['quantity'];
        });
        
        return array_slice($productSales, 0, 5);
    }

    /**
     * Show pending approval page
     */
    public function pending()
    {
        $user = Auth::user();
        $vendor = $user->vendor;
        
        if ($vendor && $vendor->isApproved()) {
            return redirect()->route('vendor.dashboard');
        }
        
        return view('vendor.auth.pending', compact('vendor'));
    }

    /**
     * Show rejected page
     */
    public function rejected()
    {
        $user = Auth::user();
        $vendor = $user->vendor;
        
        if ($vendor && $vendor->isApproved()) {
            return redirect()->route('vendor.dashboard');
        }
        
        return view('vendor.auth.rejected', compact('vendor'));
    }

    /**
     * Show suspended page
     */
    public function suspended()
    {
        $user = Auth::user();
        $vendor = $user->vendor;
        
        if ($vendor && $vendor->isApproved()) {
            return redirect()->route('vendor.dashboard');
        }
        
        return view('vendor.auth.suspended', compact('vendor'));
    }

    /**
     * Get dashboard data via AJAX for chart updates
     */
    public function getChartData(Request $request)
    {
        $period = $request->get('period', 'weekly');
        $vendor = Auth::user()->vendor;
        $vendorOrders = $this->getVendorOrders($vendor->id);
        
        if ($period === 'monthly') {
            $data = $this->getMonthlyRevenueData($vendorOrders['delivered']);
        } else {
            $data = $this->getWeeklyRevenueData($vendorOrders['delivered']);
        }
        
        return response()->json($data);
    }
}
