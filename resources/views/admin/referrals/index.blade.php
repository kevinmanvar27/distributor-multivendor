@extends('admin.layouts.app')

@section('title', 'Referral Codes')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Referral Codes',
                'breadcrumbs' => [
                    'Referral Codes' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-6 col-md-4 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-primary mb-2">
                                    <i class="fas fa-ticket-alt fa-2x"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                                <small class="text-muted">Total Codes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-success mb-2">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">{{ $stats['active'] }}</h3>
                                <small class="text-muted">Active</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-secondary mb-2">
                                    <i class="fas fa-ban fa-2x"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">{{ $stats['inactive'] }}</h3>
                                <small class="text-muted">Inactive</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referrals List -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                            <div class="mb-2 mb-md-0">
                                <h5 class="card-title mb-0 fw-bold">Referral Codes</h5>
                                <p class="mb-0 text-muted small">Manage all referral codes</p>
                            </div>
                            <a href="{{ route('admin.referrals.create') }}" class="btn btn-sm btn-theme rounded-pill px-3">
                                <i class="fas fa-plus me-1"></i>Create Referral Code
                            </a>
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

                        <!-- Filters -->
                        <form action="{{ route('admin.referrals.index') }}" method="GET" class="mb-4">
                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <input type="text" class="form-control form-control-sm" name="search" 
                                        placeholder="Search by name or code..." value="{{ request('search') }}">
                                </div>
                                <div class="col-6 col-md-3">
                                    <select class="form-select form-select-sm" name="status">
                                        <option value="">All Status</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-5">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-sm btn-theme rounded-pill px-3">
                                            <i class="fas fa-search me-1"></i>Filter
                                        </button>
                                        <a href="{{ route('admin.referrals.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            <i class="fas fa-times me-1"></i>Clear
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Referral Code</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($referrals as $referral)
                                    <tr>
                                        <td>
                                            <div class="fw-medium">{{ $referral->name }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-dark fs-6 font-monospace">{{ $referral->referral_code }}</span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('{{ $referral->referral_code }}')" title="Copy code">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle" type="checkbox" 
                                                    data-id="{{ $referral->id }}" 
                                                    {{ $referral->status == 'active' ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $referral->created_at->format('d M Y, h:i A') }}</small>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.referrals.edit', $referral) }}" class="btn btn-outline-primary rounded-start-pill px-3" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.referrals.destroy', $referral) }}" method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this referral code?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger rounded-end-pill px-3" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-ticket-alt fa-3x mb-3"></i>
                                                <p class="mb-0">No referral codes found</p>
                                                <a href="{{ route('admin.referrals.create') }}" class="btn btn-theme btn-sm mt-3 rounded-pill">
                                                    <i class="fas fa-plus me-2"></i>Create your first referral code
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($referrals->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $referrals->withQueryString()->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show a brief toast or alert
        alert('Referral code copied to clipboard!');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Status toggle handler
    document.querySelectorAll('.status-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const referralId = this.dataset.id;
            const newStatus = this.checked ? 'active' : 'inactive';
            
            fetch(`/admin/referrals/${referralId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Revert toggle if failed
                    this.checked = !this.checked;
                    alert(data.message || 'Failed to update status');
                }
            })
            .catch(error => {
                // Revert toggle on error
                this.checked = !this.checked;
                console.error('Error:', error);
            });
        });
    });
});
</script>
@endsection
