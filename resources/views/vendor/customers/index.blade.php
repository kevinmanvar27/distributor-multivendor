@extends('vendor.layouts.app')

@section('title', 'Customers')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Customers'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-6 col-md-4 mb-3">
                        <div class="card border-0 shadow-sm bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white">Total Customers</h6>
                                        <h3 class="mb-0 fw-bold text-white">{{ $totalCustomers }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-users fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 mb-3">
                        <div class="card border-0 shadow-sm bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-white">New This Month</h6>
                                        <h3 class="mb-0 fw-bold text-white">{{ $newCustomersThisMonth }}</h3>
                                    </div>
                                    <div class="bg-white bg-opacity-25 rounded-circle p-2 p-md-3 d-none d-sm-flex">
                                        <i class="fas fa-user-plus fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form action="{{ route('vendor.customers.index') }}" method="GET" class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control rounded-pill" 
                                       placeholder="Search by name, email or phone..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary rounded-pill me-2">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                                <a href="{{ route('vendor.customers.index') }}" class="btn btn-outline-secondary rounded-pill">
                                    <i class="fas fa-times me-1"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Customers Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 py-3">Customer</th>
                                        <th class="py-3">Email</th>
                                        <th class="py-3">Phone</th>
                                        <th class="py-3 text-center">Orders</th>
                                        <th class="py-3">Registered</th>
                                        <th class="py-3 text-end px-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customers as $customer)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 40px; height: 40px;">
                                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium">{{ $customer->name }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3">{{ $customer->email }}</td>
                                            <td class="py-3">{{ $customer->phone ?? '-' }}</td>
                                            <td class="py-3 text-center">
                                                <span class="badge bg-info">{{ $customer->orders_count }}</span>
                                            </td>
                                            <td class="py-3">{{ $customer->created_at->format('M d, Y') }}</td>
                                            <td class="py-3 text-end px-4">
                                                <a href="{{ route('vendor.customers.show', $customer->id) }}" 
                                                   class="btn btn-sm btn-outline-primary rounded-pill">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-users fa-3x mb-3"></i>
                                                    <p class="mb-0">No customers found</p>
                                                    <small>Customers who register on your store will appear here</small>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    @if($customers->hasPages())
                        <div class="card-footer bg-white border-0">
                            {{ $customers->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
