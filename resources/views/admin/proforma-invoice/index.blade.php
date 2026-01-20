@extends('admin.layouts.app')

@section('title', 'Proforma Invoices - ' . setting('site_title', 'Admin Panel'))

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Proforma Invoices'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Invoices</h4>
                                        <p class="mb-0 text-muted small">Manage user invoices</p>
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
                                
                                @if($proformaInvoices->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="proformaInvoicesTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Invoice #</th>
                                                    <th>Customer</th>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($proformaInvoices as $index => $invoice)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $invoice->invoice_number }}</td>
                                                    <td>
                                                        @if($invoice->user)
                                                            {{ $invoice->user->name }}
                                                        @else
                                                            Guest ({{ substr($invoice->session_id, 0, 8) }}...)
                                                        @endif
                                                    </td>
                                                    <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                                                    <td>₹{{ number_format($invoice->total_amount, 2) }}</td>
                                                    <td>
                                                        @switch($invoice->status)
                                                            @case('Draft')
                                                                <span class="badge bg-secondary">Draft</span>
                                                                @break
                                                            @case('Approved')
                                                                <span class="badge bg-success">Approved</span>
                                                                @break
                                                            @case('Dispatch')
                                                                <span class="badge bg-info">Dispatch</span>
                                                                @break
                                                            @case('Out for Delivery')
                                                                <span class="badge bg-primary">Out for Delivery</span>
                                                                @break
                                                            @case('Delivered')
                                                                <span class="badge bg-success">Delivered</span>
                                                                @break
                                                            @case('Return')
                                                                <span class="badge bg-danger">Return</span>
                                                                @break
                                                        @endswitch
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="{{ route('admin.proforma-invoice.show', $invoice->id) }}" class="btn btn-outline-primary rounded-start-pill px-3">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="{{ route('admin.proforma-invoice.destroy', $invoice->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-outline-danger rounded-end-pill px-3" onclick="return confirm('Are you sure you want to delete this invoice?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                        <h5 class="mb-2">No proforma invoices found</h5>
                                        <p class="mb-0 text-muted">Proforma invoices will appear here once generated by users.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle status form submission
    document.querySelectorAll('.status-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const select = form.querySelector('.status-select');
            const selectedStatus = select.value;
            const currentStatus = select.options[select.selectedIndex].text;
            
            this.submit();
        });
        
        // Auto-submit when status changes
        const select = form.querySelector('.status-select');
        select.addEventListener('change', function() {
            form.dispatchEvent(new Event('submit'));
        });
    });
    
    $('#proformaInvoicesTable').DataTable({
        "pageLength": 25,
        "ordering": true,
        "info": true,
        "responsive": true,
        "columnDefs": [
            { "orderable": false, "targets": [6] } // Disable ordering on Actions column
        ],
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
    });
    // Adjust select width after DataTable initializes
    $('.dataTables_length select').css('width', '80px');
});
</script>
@endsection