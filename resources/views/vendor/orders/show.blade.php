@extends('vendor.layouts.app')

@section('title', 'Order Details - ' . ($invoice->invoice_number ?? 'INV-' . $invoice->id))

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Order Details'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Back Button -->
                <div class="mb-4">
                    <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i> Back to Orders
                    </a>
                </div>

                <div class="row g-4">
                    <!-- Order Info Card -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="card-title mb-0 fw-bold">{{ $invoice->invoice_number ?? 'INV-' . $invoice->id }}</h4>
                                        <p class="mb-0 text-muted small">Order placed on {{ $invoice->created_at->format('d M Y, h:i A') }}</p>
                                    </div>
                                    <div>
                                        @switch($invoice->status)
                                            @case('Draft')
                                                <span class="badge bg-secondary rounded-pill px-3 py-2">Draft</span>
                                                @break
                                            @case('Approved')
                                                <span class="badge bg-primary rounded-pill px-3 py-2">Approved</span>
                                                @break
                                            @case('Dispatch')
                                                <span class="badge bg-info rounded-pill px-3 py-2">Dispatch</span>
                                                @break
                                            @case('Out for Delivery')
                                                <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Out for Delivery</span>
                                                @break
                                            @case('Delivered')
                                                <span class="badge bg-success rounded-pill px-3 py-2">Delivered</span>
                                                @break
                                            @case('Return')
                                                <span class="badge bg-danger rounded-pill px-3 py-2">Return</span>
                                                @break
                                        @endswitch
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Your Products in this Order -->
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-box text-primary me-2"></i>Your Products in this Order
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 60px;">Image</th>
                                                <th>Product</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-end">Price</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($vendorItems as $item)
                                            <tr>
                                                <td>
                                                    @if($item['image'])
                                                        <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="fw-medium">{{ $item['name'] }}</div>
                                                    @if($item['sku'])
                                                        <div class="text-muted small">SKU: {{ $item['sku'] }}</div>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $item['quantity'] }}</td>
                                                <td class="text-end">₹{{ number_format($item['price'], 2) }}</td>
                                                <td class="text-end fw-bold">₹{{ number_format($item['total'], 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold">Your Total:</td>
                                                <td class="text-end fw-bold text-success fs-5">₹{{ number_format($vendorTotal, 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Information -->
                        @if($invoice->user)
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-user text-primary me-2"></i>Customer Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="text-muted small">Name</label>
                                            <p class="mb-0 fw-medium">{{ $invoice->user->name }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="text-muted small">Email</label>
                                            <p class="mb-0">{{ $invoice->user->email }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="text-muted small">Phone</label>
                                            <p class="mb-0">{{ $invoice->user->mobile_number ?? 'N/A' }}</p>
                                        </div>
                                        @if(isset($invoice->invoice_data['shipping_address']))
                                        <div class="mb-3">
                                            <label class="text-muted small">Shipping Address</label>
                                            <p class="mb-0">{{ $invoice->invoice_data['shipping_address'] }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Earnings Summary -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-calculator text-primary me-2"></i>Earnings Summary
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted">Your Revenue</span>
                                    <span class="fw-medium">₹{{ number_format($vendorTotal, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted">Commission ({{ $commissionRate }}%)</span>
                                    <span class="fw-medium text-danger">-₹{{ number_format($commission, 2) }}</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Net Earnings</span>
                                    <span class="fw-bold text-success fs-5">₹{{ number_format($netEarnings, 2) }}</span>
                                </div>
                                
                                @if($invoice->status == 'Delivered')
                                    <div class="alert alert-success mt-4 mb-0 rounded-3">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <small>This order has been delivered. Earnings will be credited to your account.</small>
                                    </div>
                                @elseif($invoice->status == 'Return')
                                    <div class="alert alert-danger mt-4 mb-0 rounded-3">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <small>This order has been returned. No earnings will be credited.</small>
                                    </div>
                                @else
                                    <div class="alert alert-info mt-4 mb-0 rounded-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <small>Earnings will be credited once the order is delivered.</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Order Timeline -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-history text-primary me-2"></i>Order Status
                                </h5>
                            </div>
                            <div class="card-body">
                                @php
                                    $statuses = ['Draft', 'Approved', 'Dispatch', 'Out for Delivery', 'Delivered'];
                                    $currentIndex = array_search($invoice->status, $statuses);
                                    if ($invoice->status == 'Return') {
                                        $currentIndex = -1;
                                    }
                                @endphp
                                <div class="timeline">
                                    @foreach($statuses as $index => $statusItem)
                                        @php
                                            $isCompleted = $currentIndex !== false && $index <= $currentIndex;
                                            $isCurrent = $invoice->status == $statusItem;
                                        @endphp
                                        <div class="timeline-item {{ $isCompleted ? 'completed' : '' }} {{ $isCurrent ? 'current' : '' }}">
                                            <div class="timeline-marker {{ $isCompleted ? 'bg-success' : 'bg-secondary' }}">
                                                @if($isCompleted)
                                                    <i class="fas fa-check text-white small"></i>
                                                @else
                                                    <span class="small">{{ $index + 1 }}</span>
                                                @endif
                                            </div>
                                            <div class="timeline-content">
                                                <span class="{{ $isCurrent ? 'fw-bold' : '' }}">{{ $statusItem }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($invoice->status == 'Return')
                                        <div class="timeline-item current">
                                            <div class="timeline-marker bg-danger">
                                                <i class="fas fa-times text-white small"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <span class="fw-bold text-danger">Returned</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection

@section('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-item:last-child {
    padding-bottom: 0;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -22px;
    top: 24px;
    bottom: 0;
    width: 2px;
    background-color: #dee2e6;
}
.timeline-item:last-child::before {
    display: none;
}
.timeline-item.completed::before {
    background-color: #198754;
}
.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 10px;
}
.timeline-content {
    padding-top: 2px;
}
</style>
@endsection
