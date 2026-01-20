@extends('admin.layouts.app')

@section('title', 'Products')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Product Management'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Product Management</h4>
                                        <p class="mb-0 text-muted small">Manage all products</p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if(isset($lowStockCount) && $lowStockCount > 0)
                                        <a href="{{ route('admin.products.low-stock') }}" class="btn btn-sm btn-md-normal btn-warning rounded-pill px-3 px-md-4">
                                            <i class="fas fa-exclamation-triangle me-1 me-md-2"></i><span class="d-none d-sm-inline">Low Stock</span> 
                                            <span class="badge bg-danger ms-1">{{ $lowStockCount }}</span>
                                        </a>
                                        @endif
                                        @can('create', App\Models\Product::class)
                                        <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-md-normal btn-theme rounded-pill px-3 px-md-4">
                                            <i class="fas fa-plus me-1 me-md-2"></i><span class="d-none d-sm-inline">Add New Product</span><span class="d-sm-none">Add</span>
                                        </a>
                                        @endcan
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
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="productsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Product</th>
                                                <th>MRP</th>
                                                <th>Selling Price</th>
                                                <th>Stock Status</th>
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
                                                                     onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'bg-light rounded me-3 d-flex align-items-center justify-content-center\' style=\'width: 40px; height: 40px;\'><i class=\'fas fa-image text-muted\'></i></div><div><div class=\'fw-medium\'>{{ $product->name }}</div></div>';"
                                                                     loading="lazy">
                                                            @else
                                                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-image text-muted"></i>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <div class="fw-medium">{{ $product->name }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>₹{{ number_format($product->mrp, 2) }}</td>
                                                    <td>₹{{ number_format($product->selling_price ?? 0, 2) }}</td>
                                                    <td>
                                                        @php
                                                            // Get total stock (for variable products, sum all variations)
                                                            $totalStock = $product->isVariable() ? $product->total_stock : $product->stock_quantity;
                                                            $isInStock = $product->isVariable() 
                                                                ? $product->variations()->where('in_stock', true)->exists()
                                                                : $product->in_stock;
                                                        @endphp
                                                        
                                                        @if($isInStock && $totalStock > 0)
                                                            @if($product->isLowStock())
                                                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-2" title="Low Stock Alert! Threshold: {{ $product->low_quantity_threshold ?? 10 }}">
                                                                    <i class="fas fa-exclamation-triangle me-1"></i> Low Stock ({{ $totalStock }})
                                                                </span>
                                                            @else
                                                                <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2">
                                                                    <i class="fas fa-check-circle me-1"></i> In Stock ({{ $totalStock }})
                                                                </span>
                                                            @endif
                                                        @elseif($totalStock == 0)
                                                            <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill px-3 py-2">
                                                                <i class="fas fa-times-circle me-1"></i> Out of Stock (0)
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3 py-2">
                                                                <i class="fas fa-ban me-1"></i> Not Available
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
                                                            <button type="button" class="btn btn-outline-info rounded-start-pill px-3" data-product-id="{{ $product->id }}" onclick="showProductDetails(this.getAttribute('data-product-id'))">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            @can('update', $product)
                                                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline-primary px-3">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            @endcan
                                                            @can('delete', $product)
                                                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-outline-danger rounded-end-pill px-3">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                            @endcan
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                {{-- Handled by DataTables JavaScript --}}
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Modal for showing product details -->
                                <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="productModalLabel">Product Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body" id="productModalBody">
                                                <!-- Content will be loaded here via AJAX -->
                                            </div>
                                            <div class="modal-footer" id="productModalFooter">
                                                <!-- Buttons will be added here dynamically -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#productsTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "ordering": true,
            "searching": true,
            "info": true,
            "paging": true,
            "columnDefs": [
                { "orderable": false, "targets": [6] } // Disable sorting on Actions column
            ],
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ products",
                "infoEmpty": "Showing 0 to 0 of 0 products",
                "infoFiltered": "(filtered from _MAX_ total products)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "aoColumns": [
                null, // ID
                null, // Product
                null, // MRP
                null, // Selling Price
                null, // Stock Status
                null, // Status
                null  // Actions
            ],
            "preDrawCallback": function(settings) {
                // Ensure consistent column count
                if ($('#productsTable tbody tr').length === 0) {
                    $('#productsTable tbody').html('<tr><td colspan="7" class="text-center py-5"><div class="text-muted"><i class="fas fa-box-open fa-2x mb-3"></i><p class="mb-0">No products found</p><p class="small">Try creating a new product</p></div></td></tr>');
                }
            },
            "drawCallback": function(settings) {
                // Reinitialize tooltips after each draw
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });
        // Adjust select width after DataTable initializes
        $('.dataTables_length select').css('width', '80px');
    });
    
    // Function to show product details in modal
    function showProductDetails(productId) {
        $.ajax({
            url: '/admin/products/' + productId + '/details',
            type: 'GET',
            success: function(data) {
                // Set the modal body content
                $('#productModalBody').html(data);
                
                // Clear the modal footer since buttons are now in the partial view
                $('#productModalFooter').html(`
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Close
                    </button>
                `);
                
                // Add event listener for SEO toggle
                $('#toggle-seo-settings-modal').on('click', function() {
                    const $icon = $(this).find('i');
                    const $text = $(this).find('span');
                    if ($icon.hasClass('fa-chevron-down')) {
                        $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                        $text.text('Collapse');
                        $('#seo-settings-content-modal').removeClass('d-none');
                    } else {
                        $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                        $text.text('Expand');
                        $('#seo-settings-content-modal').addClass('d-none');
                    }
                });
                
                // Show the modal
                $('#productModal').modal('show');
            },
            error: function() {
                alert('Error loading product details.');
            }
        });
    }
    
    // Function to delete product
    function deleteProduct(productId) {
        if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
            $.ajax({
                url: '/admin/products/' + productId,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'DELETE'
                },
                success: function(response) {
                    $('#productModal').modal('hide');
                    // Reload the page to reflect changes
                    location.reload();
                },
                error: function() {
                    alert('Error deleting product.');
                }
            });
        }
    }
</script>
@endsection