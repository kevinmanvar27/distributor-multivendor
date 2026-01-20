@extends('vendor.layouts.app')

@section('title', 'Add Staff Member')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Add Staff Member'])
            
            <div class="pt-4 pb-2 mb-3">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="card-title mb-0 fw-bold">Add New Staff Member</h4>
                                <p class="text-muted mb-0 small">Create a new staff account for your store</p>
                            </div>
                            <a href="{{ route('vendor.staff.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <form action="{{ route('vendor.staff.store') }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <!-- Personal Information -->
                                <div class="col-lg-6">
                                    <h6 class="fw-bold mb-3 text-primary">
                                        <i class="fas fa-user me-2"></i>Personal Information
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control rounded-pill px-4" id="name" name="name" value="{{ old('name') }}" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control rounded-pill px-4" id="email" name="email" value="{{ old('email') }}" required>
                                        <div class="form-text">This will be used for login</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mobile_number" class="form-label">Mobile Number</label>
                                        <input type="text" class="form-control rounded-pill px-4" id="mobile_number" name="mobile_number" value="{{ old('mobile_number') }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control rounded-pill px-4" id="password" name="password" required>
                                        <div class="form-text">Minimum 8 characters</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control rounded-pill px-4" id="password_confirmation" name="password_confirmation" required>
                                    </div>
                                </div>
                                
                                <!-- Role & Permissions -->
                                <div class="col-lg-6">
                                    <h6 class="fw-bold mb-3 text-primary">
                                        <i class="fas fa-shield-alt me-2"></i>Role & Permissions
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                        <select class="form-select rounded-pill px-4" id="role" name="role" required>
                                            <option value="">Select Role</option>
                                            <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                                            <option value="sales" {{ old('role') == 'sales' ? 'selected' : '' }}>Sales Staff</option>
                                            <option value="inventory" {{ old('role') == 'inventory' ? 'selected' : '' }}>Inventory Staff</option>
                                            <option value="support" {{ old('role') == 'support' ? 'selected' : '' }}>Support Staff</option>
                                            <option value="delivery" {{ old('role') == 'delivery' ? 'selected' : '' }}>Delivery Staff</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Permissions</label>
                                        <div class="border rounded-3 p-3">
                                            <div class="row">
                                                <div class="col-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="products" id="perm_products" {{ in_array('products', old('permissions', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="perm_products">
                                                            <i class="fas fa-box me-1 text-muted"></i>Products
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="orders" id="perm_orders" {{ in_array('orders', old('permissions', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="perm_orders">
                                                            <i class="fas fa-shopping-cart me-1 text-muted"></i>Orders
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="categories" id="perm_categories" {{ in_array('categories', old('permissions', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="perm_categories">
                                                            <i class="fas fa-folder me-1 text-muted"></i>Categories
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="media" id="perm_media" {{ in_array('media', old('permissions', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="perm_media">
                                                            <i class="fas fa-images me-1 text-muted"></i>Media
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="leads" id="perm_leads" {{ in_array('leads', old('permissions', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="perm_leads">
                                                            <i class="fas fa-user-plus me-1 text-muted"></i>Leads
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="coupons" id="perm_coupons" {{ in_array('coupons', old('permissions', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="perm_coupons">
                                                            <i class="fas fa-ticket-alt me-1 text-muted"></i>Coupons
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="reports" id="perm_reports" {{ in_array('reports', old('permissions', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="perm_reports">
                                                            <i class="fas fa-chart-bar me-1 text-muted"></i>Reports
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="invoices" id="perm_invoices" {{ in_array('invoices', old('permissions', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="perm_invoices">
                                                            <i class="fas fa-file-invoice me-1 text-muted"></i>Invoices
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-text">Select the areas this staff member can access</div>
                                    </div>
                                    
                                    <div class="d-flex gap-2 mt-4">
                                        <button type="button" class="btn btn-outline-secondary rounded-pill" onclick="selectAllPermissions()">
                                            <i class="fas fa-check-double me-1"></i>Select All
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary rounded-pill" onclick="clearAllPermissions()">
                                            <i class="fas fa-times me-1"></i>Clear All
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('vendor.staff.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-theme rounded-pill px-4">
                                    <i class="fas fa-save me-2"></i>Create Staff Member
                                </button>
                            </div>
                        </form>
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
    function selectAllPermissions() {
        document.querySelectorAll('input[name="permissions[]"]').forEach(function(checkbox) {
            checkbox.checked = true;
        });
    }
    
    function clearAllPermissions() {
        document.querySelectorAll('input[name="permissions[]"]').forEach(function(checkbox) {
            checkbox.checked = false;
        });
    }
</script>
@endsection
