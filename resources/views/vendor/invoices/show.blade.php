@extends('vendor.layouts.app')

@section('title', 'Invoice - ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Invoice Details'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('vendor.invoices.index') }}" class="text-decoration-none">
                                <i class="fas fa-file-invoice me-1"></i>Invoices
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $invoice->invoice_number }}</li>
                    </ol>
                </nav>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row">
                    <!-- Invoice Details -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">{{ $invoice->invoice_number }}</h4>
                                    <p class="mb-0 text-muted small">Created on {{ $invoice->created_at->format('d M Y, h:i A') }}</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('vendor.invoices.print', $invoice) }}" class="btn btn-outline-secondary rounded-pill" target="_blank">
                                        <i class="fas fa-print me-1"></i> Print
                                    </a>
                                    <a href="{{ route('vendor.invoices.download', $invoice) }}" class="btn btn-outline-success rounded-pill">
                                        <i class="fas fa-download me-1"></i> Download
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Customer Info -->
                                @if($customer)
                                <div class="mb-4 p-3 bg-light rounded">
                                    <h6 class="fw-bold mb-2"><i class="fas fa-user me-2"></i>Customer</h6>
                                    <p class="mb-1">{{ $customer['name'] ?? 'N/A' }}</p>
                                    <p class="mb-1 text-muted small">{{ $customer['email'] ?? '' }}</p>
                                    <p class="mb-0 text-muted small">{{ $customer['phone'] ?? '' }}</p>
                                </div>
                                @elseif($invoice->user)
                                <div class="mb-4 p-3 bg-light rounded">
                                    <h6 class="fw-bold mb-2"><i class="fas fa-user me-2"></i>Customer</h6>
                                    <p class="mb-1">{{ $invoice->user->name }}</p>
                                    <p class="mb-1 text-muted small">{{ $invoice->user->email }}</p>
                                    <p class="mb-0 text-muted small">{{ $invoice->user->phone ?? '' }}</p>
                                </div>
                                @endif

                                <!-- Items Table -->
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th class="text-end">Price</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($cartItems as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item['product_name'] ?? 'Product' }}</td>
                                                <td class="text-end">₹{{ number_format($item['price'] ?? 0, 2) }}</td>
                                                <td class="text-center">{{ $item['quantity'] ?? 1 }}</td>
                                                <td class="text-end">₹{{ number_format($item['total'] ?? 0, 2) }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No items found</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold">Total:</td>
                                                <td class="text-end fw-bold">₹{{ number_format($total, 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Payment -->
                    <div class="col-lg-4">
                        <!-- Status Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">Status</h5>
                            </div>
                            <div class="card-body">
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
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span>Order Status:</span>
                                    <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }} fs-6">
                                        {{ $invoice->status }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Payment Status:</span>
                                    @switch($invoice->payment_status)
                                        @case('unpaid')
                                            <span class="badge bg-secondary fs-6">Unpaid</span>
                                            @break
                                        @case('partial')
                                            <span class="badge bg-warning fs-6">Partial</span>
                                            @break
                                        @case('paid')
                                            <span class="badge bg-success fs-6">Paid</span>
                                            @break
                                    @endswitch
                                </div>
                            </div>
                        </div>

                        <!-- Payment Summary -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">Payment Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Amount:</span>
                                    <span class="fw-bold">₹{{ number_format($invoice->total_amount, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Paid Amount:</span>
                                    <span class="text-success fw-bold">₹{{ number_format($invoice->paid_amount, 2) }}</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span>Pending Amount:</span>
                                    <span class="text-danger fw-bold">₹{{ number_format($invoice->pending_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    @if($invoice->pending_amount > 0)
                                    <a href="{{ route('vendor.pending-bills.show', $invoice) }}" class="btn btn-outline-success rounded-pill">
                                        <i class="fas fa-plus me-1"></i> Record Payment
                                    </a>
                                    @endif
                                    <a href="{{ route('vendor.invoices.index') }}" class="btn btn-outline-secondary rounded-pill">
                                        <i class="fas fa-arrow-left me-1"></i> Back to Invoices
                                    </a>
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