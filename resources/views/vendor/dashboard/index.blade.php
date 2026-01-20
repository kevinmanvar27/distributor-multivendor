@extends('vendor.layouts.app')

@section('title', 'Dashboard')

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    .progress-thin {
        height: 6px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Dashboard'])
            
            @section('page-title', 'Dashboard')
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Vendor Status Alert -->
                @if($vendor->status === 'pending')
                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-clock me-3 fa-lg"></i>
                        <div class="flex-grow-1">
                            <strong>Account Pending Approval</strong>
                            <p class="mb-0 small">Your vendor account is currently under review. You'll be notified once it's approved.</p>
                        </div>
                    </div>
                @elseif($vendor->status === 'suspended')
                    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-ban me-3 fa-lg"></i>
                        <div class="flex-grow-1">
                            <strong>Account Suspended</strong>
                            <p class="mb-0 small">Your vendor account has been suspended. Please contact support for assistance.</p>
                        </div>
                    </div>
                @endif
                
                <!-- Welcome Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg-6">
                                <h2 class="card-title mb-2 h4">Welcome back, {{ Auth::user()->name }}!</h2>
                                <p class="text-secondary mb-3 small">Here's what's happening with your store today.</p>
                                <div class="d-flex flex-wrap gap-3 gap-md-4">
                                    <div class="text-center text-md-start">
                                        <span class="text-secondary small d-block">Today's Orders</span>
                                        <h4 class="mb-0 text-primary h5">{{ $todayOrders ?? 0 }}</h4>
                                    </div>
                                    <div class="text-center text-md-start">
                                        <span class="text-secondary small d-block">Today's Revenue</span>
                                        <h4 class="mb-0 text-success h5">₹{{ number_format($todayRevenue ?? 0, 2) }}</h4>
                                    </div>
                                    <div class="text-center text-md-start">
                                        <span class="text-secondary small d-block">Pending Orders</span>
                                        <h4 class="mb-0 text-warning h5">{{ $pendingOrders ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6 text-lg-end mt-3 mt-lg-0">
                                <div class="d-flex flex-wrap gap-2 justify-content-start justify-content-lg-end">
                                    <a href="{{ route('vendor.products.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                                        <i class="fas fa-box me-1"></i><span class="d-none d-sm-inline">View Products</span>
                                    </a>
                                    <a href="{{ route('vendor.products.create') }}" class="btn btn-sm btn-theme rounded-pill">
                                        <i class="fas fa-plus me-1"></i><span class="d-none d-sm-inline">Add Product</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row g-3 g-md-4 mb-4">
                    <div class="col-6 col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100 stat-card">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="stat-icon bg-success rounded-circle" style="width: 40px; height: 40px; font-size: 1rem;">
                                        <i class="fas fa-indian-rupee-sign text-white"></i>
                                    </div>
                                </div>
                                <h3 class="h6 text-secondary mb-1">Total Revenue</h3>
                                <p class="h5 mb-0 fw-bold">₹{{ number_format($stats['totalRevenue'] ?? 0, 2) }}</p>
                                <small class="text-muted">This month: ₹{{ number_format($stats['monthlyRevenue'] ?? 0, 2) }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6 col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100 stat-card">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="stat-icon bg-primary rounded-circle" style="width: 40px; height: 40px; font-size: 1rem;">
                                        <i class="fas fa-shopping-cart text-white"></i>
                                    </div>
                                    <span class="badge bg-primary text-white" style="font-size: 0.65rem;">
                                        {{ $stats['pendingOrders'] ?? 0 }} pending
                                    </span>
                                </div>
                                <h3 class="h6 text-secondary mb-1">Total Orders</h3>
                                <p class="h5 mb-0 fw-bold">{{ $stats['totalOrders'] ?? 0 }}</p>
                                <small class="text-muted">Completed: {{ $stats['completedOrders'] ?? 0 }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6 col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100 stat-card">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="stat-icon bg-warning rounded-circle" style="width: 40px; height: 40px; font-size: 1rem;">
                                        <i class="fas fa-boxes text-white"></i>
                                    </div>
                                    @if(($stats['lowStockCount'] ?? 0) > 0)
                                    <span class="badge bg-danger text-white" style="font-size: 0.65rem;">
                                        {{ $stats['lowStockCount'] }} low
                                    </span>
                                    @endif
                                </div>
                                <h3 class="h6 text-secondary mb-1">Products</h3>
                                <p class="h5 mb-0 fw-bold">{{ $stats['totalProducts'] ?? 0 }}</p>
                                <small class="text-muted">Active: {{ $stats['activeProducts'] ?? 0 }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6 col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100 stat-card">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="stat-icon bg-info rounded-circle" style="width: 40px; height: 40px; font-size: 1rem;">
                                        <i class="fas fa-tags text-white"></i>
                                    </div>
                                </div>
                                <h3 class="h6 text-secondary mb-1">Categories</h3>
                                <p class="h5 mb-0 fw-bold">{{ $stats['totalCategories'] ?? 0 }}</p>
                                <small class="text-muted">With products</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Row -->
                <div class="row g-3 g-md-4 mb-4">
                    <!-- Revenue Chart -->
                    <div class="col-12 col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                <h3 class="h6 mb-0 fw-semibold">Revenue Overview</h3>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-secondary active" id="weeklyBtn" onclick="switchChart('weekly')">Weekly</button>
                                    <button type="button" class="btn btn-outline-secondary" id="monthlyBtn" onclick="switchChart('monthly')">Monthly</button>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="chart-container" style="height: 250px;">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Status -->
                    <div class="col-12 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0">
                                <h3 class="h6 mb-0 fw-semibold">Order Status</h3>
                            </div>
                            <div class="card-body p-3">
                                <div class="chart-container" style="height: 180px;">
                                    <canvas id="orderStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders & Top Products -->
                <div class="row g-3 g-md-4 mb-4">
                    <!-- Recent Orders -->
                    <div class="col-12 col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                <h3 class="h6 mb-0 fw-semibold">Recent Orders</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0 ps-3">Order</th>
                                                <th class="border-0">Customer</th>
                                                <th class="border-0">Amount</th>
                                                <th class="border-0">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentOrders ?? [] as $order)
                                            <tr>
                                                <td class="ps-3">
                                                    <span class="text-decoration-none fw-medium small">
                                                        #{{ $order['id'] ?? 'N/A' }}
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">{{ isset($order['created_at']) ? \Carbon\Carbon::parse($order['created_at'])->format('M d, Y') : 'N/A' }}</small>
                                                </td>
                                                <td class="small">{{ $order['user']->name ?? 'Guest' }}</td>
                                                <td class="fw-medium small">₹{{ number_format($order['vendor_total'] ?? 0, 2) }}</td>
                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'draft' => 'secondary',
                                                            'approved' => 'info',
                                                            'dispatch' => 'primary',
                                                            'out_for_delivery' => 'warning',
                                                            'delivered' => 'success',
                                                            'return' => 'danger'
                                                        ];
                                                        $color = $statusColors[$order['status'] ?? ''] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge bg-{{ $color }}" style="font-size: 0.65rem;">{{ ucfirst(str_replace('_', ' ', $order['status'] ?? 'Unknown')) }}</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                    No orders yet
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top Products -->
                    <div class="col-12 col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                <h3 class="h6 mb-0 fw-semibold">Top Selling Products</h3>
                                <a href="{{ route('vendor.products.index') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
                            </div>
                            <div class="card-body p-3">
                                @forelse($topProducts ?? [] as $index => $product)
                                <div class="d-flex justify-content-between align-items-center {{ !$loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-{{ $index < 3 ? 'primary' : 'secondary' }} rounded-circle me-3" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <h6 class="mb-0 small">{{ Str::limit($product->name, 25) }}</h6>
                                            <small class="text-muted">{{ $product->sold_count ?? 0 }} units sold</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold text-success small">₹{{ number_format($product->revenue ?? 0, 2) }}</span>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-chart-line fa-2x mb-2 d-block"></i>
                                    No sales data yet
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Low Stock Alert -->
                @if(count($lowStockProducts ?? []) > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <h3 class="h6 mb-0 fw-semibold">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>Low Stock Alert
                        </h3>
                        <a href="{{ route('vendor.products.index', ['filter' => 'low_stock']) }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            @foreach($lowStockProducts ?? [] as $product)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                    <div>
                                        <h6 class="mb-1 small">{{ Str::limit($product->name, 30) }}</h6>
                                        <div class="progress progress-thin" style="width: 100px; height: 4px;">
                                            @php
                                                $percentage = $product->low_quantity_threshold > 0 
                                                    ? min(100, ($product->stock_quantity / $product->low_quantity_threshold) * 100)
                                                    : 0;
                                                $progressColor = $percentage <= 25 ? 'danger' : ($percentage <= 50 ? 'warning' : 'success');
                                            @endphp
                                            <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                    <span class="badge bg-{{ $product->stock_quantity <= 5 ? 'danger' : 'warning' }}">
                                        {{ $product->stock_quantity }} left
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Chart.js configuration
    const weeklyData = @json($weeklyRevenueData ?? []);
    const monthlyData = @json($monthlyRevenueData ?? []);
    const orderStatusData = @json($orderStatusData ?? []);
    
    let revenueChart;
    let currentView = 'weekly';
    
    // Initialize Revenue Chart
    function initRevenueChart(data, isWeekly = true) {
        const chartElement = document.getElementById('revenueChart');
        if (!chartElement) return;
        
        const ctx = chartElement.getContext('2d');
        
        if (revenueChart) {
            revenueChart.destroy();
        }
        
        const labels = isWeekly ? data.map(d => d.day) : data.map(d => d.month);
        const revenues = data.map(d => d.revenue);
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(13, 110, 253, 0.3)');
        gradient.addColorStop(1, 'rgba(13, 110, 253, 0.01)');
        
        revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: revenues,
                    borderColor: '#0d6efd',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0d6efd',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Initialize Order Status Chart
    function initOrderStatusChart() {
        const chartElement = document.getElementById('orderStatusChart');
        if (!chartElement || !orderStatusData.length) return;
        
        const ctx = chartElement.getContext('2d');
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: orderStatusData.map(d => d.status),
                datasets: [{
                    data: orderStatusData.map(d => d.count),
                    backgroundColor: orderStatusData.map(d => d.color),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 10 }
                    }
                },
                cutout: '60%'
            }
        });
    }
    
    function switchChart(view) {
        currentView = view;
        document.getElementById('weeklyBtn').classList.toggle('active', view === 'weekly');
        document.getElementById('monthlyBtn').classList.toggle('active', view === 'monthly');
        initRevenueChart(view === 'weekly' ? weeklyData : monthlyData, view === 'weekly');
    }
    
    // Initialize charts on page load
    document.addEventListener('DOMContentLoaded', function() {
        initRevenueChart(weeklyData, true);
        initOrderStatusChart();
    });
</script>
@endsection
