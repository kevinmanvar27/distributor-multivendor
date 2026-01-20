@extends('vendor.layouts.app')

@section('title', 'Customer Details - ' . $customer->name)

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Customer Details'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Back Button -->
                <div class="mb-4">
                    <a href="{{ route('vendor.customers.index') }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fas fa-arrow-left me-2"></i>Back to Customers
                    </a>
                </div>

                <div class="row">
                    <!-- Customer Info Card -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-4">
                                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 80px; height: 80px; font-size: 2rem;">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <h4 class="fw-bold mb-1">{{ $customer->name }}</h4>
                                <p class="text-muted mb-3">{{ $customer->email }}</p>
                                
                                <hr>
                                
                                <div class="text-start">
                                    <div class="mb-3">
                                        <label class="text-muted small">Phone</label>
                                        <div class="fw-medium">{{ $customer->mobile_number ?? $customer->phone ?? 'Not provided' }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted small">Address</label>
                                        <div class="fw-medium">{{ $customer->address ?? 'Not provided' }}</div>
                                    </div>
                                    @if(isset($customerSince))
                                    <div class="mb-3">
                                        <label class="text-muted small">Customer Since</label>
                                        <div class="fw-medium">{{ $customerSince->format('M d, Y') }}</div>
                                    </div>
                                    @endif
                                    <div class="mb-3">
                                        <label class="text-muted small">Account Created</label>
                                        <div class="fw-medium">{{ $customer->created_at->format('M d, Y h:i A') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Statistics Card -->
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Statistics</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total Orders</span>
                                    <span class="fw-bold">{{ $totalOrders }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total Spent</span>
                                    <span class="fw-bold text-success">₹{{ number_format($totalSpent, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Orders Section -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold">Order History</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="px-4 py-3">Order #</th>
                                                <th class="py-3">Date</th>
                                                <th class="py-3">Items</th>
                                                <th class="py-3">Total</th>
                                                <th class="py-3">Status</th>
                                                <th class="py-3 text-end px-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($orders as $order)
                                                @php
                                                    $orderData = $order->invoice_data;
                                                    if (is_string($orderData)) {
                                                        $orderData = json_decode($orderData, true);
                                                    }
                                                    $cartItems = $orderData['cart_items'] ?? [];
                                                    $orderTotal = $orderData['total'] ?? $order->total_amount;
                                                @endphp
                                                <tr>
                                                    <td class="px-4 py-3">
                                                        <span class="fw-medium">{{ $order->invoice_number }}</span>
                                                    </td>
                                                    <td class="py-3">{{ $order->created_at->format('M d, Y') }}</td>
                                                    <td class="py-3">{{ count($cartItems) }} items</td>
                                                    <td class="py-3 fw-bold">₹{{ number_format($orderTotal, 2) }}</td>
                                                    <td class="py-3">
                                                        @php
                                                            $statusColors = [
                                                                'Draft' => 'secondary',
                                                                'Approved' => 'info',
                                                                'Dispatch' => 'primary',
                                                                'Out for Delivery' => 'warning',
                                                                'Delivered' => 'success',
                                                                'Return' => 'danger',
                                                            ];
                                                        @endphp
                                                        <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                            {{ $order->status }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 text-end px-4">
                                                        <a href="{{ route('vendor.invoices.show', $order->id) }}" 
                                                           class="btn btn-sm btn-outline-primary rounded-pill">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="fas fa-shopping-bag fa-3x mb-3"></i>
                                                            <p class="mb-0">No orders yet</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            @if($orders->hasPages())
                                <div class="card-footer bg-white border-0">
                                    {{ $orders->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
