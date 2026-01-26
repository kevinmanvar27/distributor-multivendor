@extends('admin.layouts.app')

@section('title', 'Vendor Management')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Vendor Management'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Vendor Management</h4>
                                        <p class="mb-0 text-muted small">Manage all vendors and their stores</p>
                                    </div>
                                    <a href="{{ route('admin.vendors.create') }}" class="btn btn-sm btn-md-normal btn-theme rounded-pill px-3 px-md-4">
                                        <i class="fas fa-plus me-1 me-md-2"></i><span class="d-none d-sm-inline">Add New Vendor</span><span class="d-sm-none">Add</span>
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
                                
                                <!-- Filter Tabs -->
                                <ul class="nav nav-pills mb-4" id="vendorTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link rounded-pill {{ request('status') == '' ? 'active' : '' }}" href="{{ route('admin.vendors.index') }}">
                                            All <span class="badge bg-secondary ms-1">{{ $counts['all'] ?? 0 }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link rounded-pill {{ request('status') == 'pending' ? 'active' : '' }}" href="{{ route('admin.vendors.index', ['status' => 'pending']) }}">
                                            Pending <span class="badge bg-warning ms-1">{{ $counts['pending'] ?? 0 }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link rounded-pill {{ request('status') == 'approved' ? 'active' : '' }}" href="{{ route('admin.vendors.index', ['status' => 'approved']) }}">
                                            Approved <span class="badge bg-success ms-1">{{ $counts['approved'] ?? 0 }}</span>
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link rounded-pill {{ request('status') == 'suspended' ? 'active' : '' }}" href="{{ route('admin.vendors.index', ['status' => 'suspended']) }}">
                                            Suspended <span class="badge bg-danger ms-1">{{ $counts['suspended'] ?? 0 }}</span>
                                        </a>
                                    </li>
                                </ul>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="vendorsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Store</th>
                                                <th>Owner</th>
                                                <th>Email</th>
                                                <th>Products</th>
                                                <th>Status</th>
                                                <th>Commission</th>
                                                <th>Joined</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($vendors as $vendor)
                                                <tr>
                                                    <td class="fw-bold">{{ $vendor->id }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($vendor->store_logo_url)
                                                                <img src="{{ $vendor->store_logo_url }}" class="rounded-circle me-3" width="40" height="40" alt="{{ $vendor->store_name }}" style="object-fit: cover;">
                                                            @else
                                                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                                    <span class="text-white fw-bold">{{ strtoupper(substr($vendor->store_name, 0, 1)) }}</span>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <div class="fw-medium">{{ $vendor->store_name }}</div>
                                                                <small class="text-muted">{{ $vendor->store_slug }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $vendor->user->name ?? 'N/A' }}</td>
                                                    <td>{{ $vendor->business_email }}</td>
                                                    <td>
                                                        <span class="badge bg-info-subtle text-info-emphasis rounded-pill px-3 py-2">
                                                            {{ $vendor->products_count ?? 0 }} products
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusClass = [
                                                                'pending' => 'bg-warning-subtle text-warning-emphasis',
                                                                'approved' => 'bg-success-subtle text-success-emphasis',
                                                                'suspended' => 'bg-danger-subtle text-danger-emphasis',
                                                                'rejected' => 'bg-dark-subtle text-dark-emphasis'
                                                            ][$vendor->status] ?? 'bg-secondary-subtle text-secondary-emphasis';
                                                        @endphp
                                                        <span class="badge {{ $statusClass }} rounded-pill px-3 py-2">
                                                            {{ ucfirst($vendor->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $vendor->commission_rate ?? 0 }}%</td>
                                                    <td>{{ $vendor->created_at->format('M d, Y') }}</td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <!-- View Button -->
                                                            <a href="{{ route('admin.vendors.show', $vendor) }}" class="btn btn-outline-info rounded-start-pill px-3" title="View">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            
                                                            <!-- Edit Button -->
                                                            <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-outline-primary px-3" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            
                                                            <!-- Status Actions Dropdown -->
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <button type="button" class="btn btn-outline-secondary dropdown-toggle px-3" data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                                                                    <i class="fas fa-ellipsis-v"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    @if($vendor->status === 'pending')
                                                                        <li>
                                                                            <form action="{{ route('admin.vendors.approve', $vendor) }}" method="POST">
                                                                                @csrf
                                                                                <button type="submit" class="dropdown-item text-success">
                                                                                    <i class="fas fa-check me-2"></i> Approve
                                                                                </button>
                                                                            </form>
                                                                        </li>
                                                                        <li>
                                                                            <form action="{{ route('admin.vendors.reject', $vendor) }}" method="POST">
                                                                                @csrf
                                                                                <button type="submit" class="dropdown-item text-danger">
                                                                                    <i class="fas fa-times me-2"></i> Reject
                                                                                </button>
                                                                            </form>
                                                                        </li>
                                                                    @elseif($vendor->status === 'approved')
                                                                        <li>
                                                                            <form action="{{ route('admin.vendors.suspend', $vendor) }}" method="POST">
                                                                                @csrf
                                                                                <button type="submit" class="dropdown-item text-warning">
                                                                                    <i class="fas fa-ban me-2"></i> Suspend
                                                                                </button>
                                                                            </form>
                                                                        </li>
                                                                    @elseif($vendor->status === 'suspended' || $vendor->status === 'rejected')
                                                                        <li>
                                                                            <form action="{{ route('admin.vendors.reactivate', $vendor) }}" method="POST">
                                                                                @csrf
                                                                                <button type="submit" class="dropdown-item text-success">
                                                                                    <i class="fas fa-redo me-2"></i> Reactivate
                                                                                </button>
                                                                            </form>
                                                                        </li>
                                                                    @endif
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li>
                                                                        <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this vendor? This action cannot be undone.');">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="dropdown-item text-danger">
                                                                                <i class="fas fa-trash me-2"></i> Delete
                                                                            </button>
                                                                        </form>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="fas fa-store fa-3x mb-3 opacity-50"></i>
                                                            <p class="mb-0">No vendors found</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                
                                @if($vendors->hasPages())
                                    <div class="d-flex justify-content-center mt-4">
                                        {{ $vendors->links() }}
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
@endsection
