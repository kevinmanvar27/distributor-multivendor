@extends('vendor.layouts.app')

@section('title', 'Products')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Product Management'])
            
            @section('page-title', 'Products')
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Product Management</h4>
                                        <p class="mb-0 text-muted small">Manage your store products</p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if(isset($lowStockCount) && $lowStockCount > 0)
                                        <a href="{{ route('vendor.products.index', ['filter' => 'low_stock']) }}" class="btn btn-sm btn-warning rounded-pill px-3">
                                            <i class="fas fa-exclamation-triangle me-1"></i><span class="d-none d-sm-inline">Low Stock</span> 
                                            <span class="badge bg-danger ms-1">{{ $lowStockCount }}</span>
                                        </a>
                                        @endif
                                        <a href="{{ route('vendor.products.create') }}" class="btn btn-sm btn-theme rounded-pill px-3">
                                            <i class="fas fa-plus me-1"></i><span class="d-none d-sm-inline">Add Product</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
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
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="productsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Product</th>
                                                <th>MRP</th>
                                                <th>Selling Price</th>
                                                <th>Stock</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($products as $product)
                                                <tr>
                                                    <td class="fw-bold">{{ $product->id }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($product->mainPhoto)
                                                                <img src="{{ $product->mainPhoto->url }}" 
                                                                     class="rounded me-3" width="40" height="40" alt="{{ $product->name }}" 
                                                                     loading="lazy">
                                                            @else
                                                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-image text-muted"></i>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <div class="fw-medium">{{ Str::limit($product->name, 30) }}</div>
                                                                @if($product->sku)
                                                                    <small class="text-muted">SKU: {{ $product->sku }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>₹{{ number_format($product->mrp, 2) }}</td>
                                                    <td>₹{{ number_format($product->selling_price ?? 0, 2) }}</td>
                                                    <td>
                                                        @php
                                                            $totalStock = $product->isVariable() ? $product->total_stock : $product->stock_quantity;
                                                            $isInStock = $product->isVariable() 
                                                                ? $product->variations()->where('in_stock', true)->exists()
                                                                : $product->in_stock;
                                                        @endphp
                                                        
                                                        @if($isInStock && $totalStock > 0)
                                                            @if($product->isLowStock())
                                                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-2">
                                                                    <i class="fas fa-exclamation-triangle me-1"></i> Low ({{ $totalStock }})
                                                                </span>
                                                            @else
                                                                <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2">
                                                                    <i class="fas fa-check-circle me-1"></i> {{ $totalStock }}
                                                                </span>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill px-3 py-2">
                                                                <i class="fas fa-times-circle me-1"></i> Out
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($product->status === 'published')
                                                            <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
                                                                Published
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3 py-2">
                                                                Draft
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="{{ route('vendor.products.show', $product) }}" class="btn btn-outline-info rounded-start-pill px-3">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="{{ route('vendor.products.edit', $product) }}" class="btn btn-outline-primary px-3">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="{{ route('vendor.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-outline-danger rounded-end-pill px-3">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-5">
                                                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted mb-3">No products found</p>
                                                        <a href="{{ route('vendor.products.create') }}" class="btn btn-theme rounded-pill">
                                                            <i class="fas fa-plus me-2"></i>Add Your First Product
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                
                                @if($products->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $products->links() }}
                                </div>
                                @endif
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

@section('scripts')
<script>
    $(document).ready(function() {
        @if($products->count() > 0)
        $('#productsTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']],
            language: {
                emptyTable: "No products available"
            }
        });
        @endif
    });
</script>
@endsection
