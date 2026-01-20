@extends('vendor.layouts.app')

@section('title', 'Orders')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Orders'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 p-3">
                                        <i class="fas fa-shopping-cart text-primary fa-lg"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-muted mb-1 small">Total Orders</h6>
                                        <h4 class="mb-0 fw-bold">{{ number_format($stats['total']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 bg-warning bg-opacity-10 rounded-3 p-3">
                                        <i class="fas fa-clock text-warning fa-lg"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-muted mb-1 small">Pending</h6>
                                        <h4 class="mb-0 fw-bold">{{ number_format($stats['pending']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 bg-success bg-opacity-10 rounded-3 p-3">
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-muted mb-1 small">Delivered</h6>
                                        <h4 class="mb-0 fw-bold">{{ number_format($stats['delivered']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 bg-info bg-opacity-10 rounded-3 p-3">
                                        <i class="fas fa-rupee-sign text-info fa-lg"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-muted mb-1 small">Revenue</h6>
                                        <h4 class="mb-0 fw-bold">₹{{ number_format($stats['total_revenue'], 0) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders Table Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                            <div>
                                <h4 class="card-title mb-0 fw-bold h5">Your Orders</h4>
                                <p class="mb-0 text-muted small">Orders containing your products</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('vendor.orders.export', request()->query()) }}" class="btn btn-outline-success btn-sm rounded-pill px-3">
                                    <i class="fas fa-download me-1"></i> Export CSV
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Filters -->
                        <form method="GET" action="{{ route('vendor.orders.index') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Search</label>
                                    <input type="text" name="search" class="form-control" placeholder="Invoice #, Customer..." value="{{ $search ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="Draft" {{ ($status ?? '') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="Approved" {{ ($status ?? '') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="Dispatch" {{ ($status ?? '') == 'Dispatch' ? 'selected' : '' }}>Dispatch</option>
                                        <option value="Out for Delivery" {{ ($status ?? '') == 'Out for Delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                        <option value="Delivered" {{ ($status ?? '') == 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                        <option value="Return" {{ ($status ?? '') == 'Return' ? 'selected' : '' }}>Return</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted">From Date</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted">To Date</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo ?? '' }}">
                                </div>
                                <div class="col-md-3 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </form>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if($pagination->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Invoice #</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Your Products</th>
                                            <th>Your Revenue</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pagination as $index => $order)
                                        <tr>
                                            <td>{{ $pagination->firstItem() + $index }}</td>
                                            <td>
                                                <a href="{{ route('vendor.orders.show', $order['id']) }}" class="text-decoration-none fw-medium">
                                                    {{ $order['invoice_number'] }}
                                                </a>
                                            </td>
                                            <td>
                                                @if($order['user'])
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                            <span class="text-primary small fw-bold">{{ strtoupper(substr($order['user']->name ?? 'G', 0, 1)) }}</span>
                                                        </div>
                                                        <div>
                                                            <div class="fw-medium">{{ $order['user']->name }}</div>
                                                            <div class="text-muted small">{{ $order['user']->email }}</div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Guest</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ \Carbon\Carbon::parse($order['created_at'])->format('d M Y') }}</div>
                                                <div class="text-muted small">{{ \Carbon\Carbon::parse($order['created_at'])->format('h:i A') }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary rounded-pill">{{ $order['vendor_product_count'] }} items</span>
                                            </td>
                                            <td class="fw-bold text-success">₹{{ number_format($order['vendor_total'], 2) }}</td>
                                            <td>
                                                @switch($order['status'])
                                                    @case('Draft')
                                                        <span class="badge bg-secondary rounded-pill">Draft</span>
                                                        @break
                                                    @case('Approved')
                                                        <span class="badge bg-primary rounded-pill">Approved</span>
                                                        @break
                                                    @case('Dispatch')
                                                        <span class="badge bg-info rounded-pill">Dispatch</span>
                                                        @break
                                                    @case('Out for Delivery')
                                                        <span class="badge bg-warning text-dark rounded-pill">Out for Delivery</span>
                                                        @break
                                                    @case('Delivered')
                                                        <span class="badge bg-success rounded-pill">Delivered</span>
                                                        @break
                                                    @case('Return')
                                                        <span class="badge bg-danger rounded-pill">Return</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                <a href="{{ route('vendor.orders.show', $order['id']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted small">
                                    Showing {{ $pagination->firstItem() }} to {{ $pagination->lastItem() }} of {{ $pagination->total() }} orders
                                </div>
                                {{ $pagination->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <h5 class="mb-2">No orders found</h5>
                                <p class="mb-0 text-muted">Orders containing your products will appear here.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>
@endsection
