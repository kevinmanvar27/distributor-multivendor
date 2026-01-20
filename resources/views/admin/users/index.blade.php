@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'User Management'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">User Management</h4>
                                        <p class="mb-0 text-muted small">Manage all users and their roles</p>
                                    </div>
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-md-normal btn-theme rounded-pill px-3 px-md-4">
                                        <i class="fas fa-plus me-1 me-md-2"></i><span class="d-none d-sm-inline">Add New User</span><span class="d-sm-none">Add</span>
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
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="usersTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>User</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Address</th>
                                                <th>Mobile</th>
                                                <th>Date of Birth</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($users as $user)
                                                <tr>
                                                    <td class="fw-bold">{{ $user->id }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ $user->avatar_url }}" 
                                                                 class="rounded-circle me-3" width="40" height="40" alt="{{ $user->name }}">
                                                            <div>
                                                                <div class="fw-medium">{{ $user->name }}</div>
                                                                @if(Auth::user()->id == $user->id)
                                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">You</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>
                                                        @php
                                                            $roleClass = [
                                                                'super_admin' => 'bg-danger-subtle text-danger-emphasis',
                                                                'admin' => 'bg-primary-subtle text-primary-emphasis',
                                                                'editor' => 'bg-warning-subtle text-warning-emphasis',
                                                                'user' => 'bg-secondary-subtle text-secondary-emphasis'
                                                            ][$user->user_role] ?? 'bg-secondary-subtle text-secondary-emphasis';
                                                        @endphp
                                                        <span class="badge {{ $roleClass }} rounded-pill px-3 py-2">
                                                            {{ ucfirst(str_replace('_', ' ', $user->user_role)) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($user->is_approved)
                                                            <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2">
                                                                Approved
                                                            </span>
                                                        @else
                                                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-2">
                                                                Pending
                                                            </span>
                                                            <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-outline-success ms-2">
                                                                    <i class="fas fa-check"></i> Approve
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                    <td>{{ $user->address ?? 'N/A' }}</td>
                                                    <td>{{ $user->mobile_number ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="text-muted">{{ $user->date_of_birth?->format('M d, Y') ?? 'N/A' }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <!-- View Button -->
                                                            <button type="button" class="btn btn-outline-info rounded-start-pill px-3 view-user-btn" data-user-id="{{ $user->id }}" data-bs-toggle="modal" data-bs-target="#userModal">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary px-3">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            @if(Auth::user()->id != $user->id)
                                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-outline-danger rounded-end-pill px-3">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <button type="button" class="btn btn-outline-secondary rounded-end-pill px-3" disabled>
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="fas fa-users fa-2x mb-3"></i>
                                                            <p class="mb-0">No users found</p>
                                                            <p class="small">Try creating a new user</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
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

<!-- User Details Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userModalBody">
                <!-- User details will be loaded here via AJAX -->
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#usersTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "ordering": true,
            "searching": true,
            "info": true,
            "paging": true,
            "columnDefs": [
                { "orderable": false, "targets": [8] } // Disable sorting on Actions column
            ],
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ users",
                "infoEmpty": "Showing 0 to 0 of 0 users",
                "infoFiltered": "(filtered from _MAX_ total users)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "aoColumns": [
                null, // #
                null, // User
                null, // Email
                null, // Role
                null, // Status
                null, // Address
                null, // Mobile
                null, // Date of Birth
                null  // Actions
            ],
            "preDrawCallback": function(settings) {
                // Ensure consistent column count
                if ($('#usersTable tbody tr').length === 0) {
                    $('#usersTable tbody').html('<tr><td colspan="9" class="text-center py-5"><div class="text-muted"><i class="fas fa-users fa-2x mb-3"></i><p class="mb-0">No users found</p><p class="small">Try creating a new user</p></div></td></tr>');
                }
            }
        });
        // Adjust select width after DataTable initializes
        $('.dataTables_length select').css('width', '80px');
        
        // Handle view user button click
        $('.view-user-btn').on('click', function() {
            var userId = $(this).data('user-id');
            
            // Show loading indicator
            $('#userModalBody').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            
            // Load user details via AJAX
            $.ajax({
                url: '/admin/users/' + userId,
                type: 'GET',
                success: function(data) {
                    $('#userModalBody').html(data);
                },
                error: function() {
                    $('#userModalBody').html('<div class="alert alert-danger">Failed to load user details.</div>');
                }
            });
        });
    });
</script>
@endsection