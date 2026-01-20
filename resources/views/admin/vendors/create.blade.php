@extends('admin.layouts.app')

@section('title', 'Add New Vendor')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Add New Vendor'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Add New Vendor</h4>
                                    <p class="mb-0 text-muted">Create a new vendor account</p>
                                </div>
                                <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Back
                                </a>
                            </div>
                            
                            <div class="card-body">
                                @if($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                
                                <form action="{{ route('admin.vendors.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    
                                    <div class="row">
                                        <!-- User Account Section -->
                                        <div class="col-md-6">
                                            <h5 class="fw-bold mb-3 border-bottom pb-2">
                                                <i class="fas fa-user me-2 text-primary"></i>User Account
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill px-4" id="name" name="name" value="{{ old('name') }}" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="user_email" class="form-label fw-bold">Login Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control rounded-pill px-4" id="user_email" name="user_email" value="{{ old('user_email') }}" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="password" class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control rounded-pill px-4" id="password" name="password" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="password_confirmation" class="form-label fw-bold">Confirm Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control rounded-pill px-4" id="password_confirmation" name="password_confirmation" required>
                                            </div>
                                        </div>
                                        
                                        <!-- Store Information Section -->
                                        <div class="col-md-6">
                                            <h5 class="fw-bold mb-3 border-bottom pb-2">
                                                <i class="fas fa-store me-2 text-primary"></i>Store Information
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label for="store_name" class="form-label fw-bold">Store Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill px-4" id="store_name" name="store_name" value="{{ old('store_name') }}" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="slug" class="form-label fw-bold">Store Slug</label>
                                                <input type="text" class="form-control rounded-pill px-4" id="slug" name="slug" value="{{ old('slug') }}" placeholder="auto-generated if empty">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="email" class="form-label fw-bold">Business Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control rounded-pill px-4" id="email" name="email" value="{{ old('email') }}" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="phone" class="form-label fw-bold">Phone Number</label>
                                                <input type="text" class="form-control rounded-pill px-4" id="phone" name="phone" value="{{ old('phone') }}">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="row">
                                        <!-- Address Section -->
                                        <div class="col-md-6">
                                            <h5 class="fw-bold mb-3 border-bottom pb-2">
                                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>Address
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label for="address" class="form-label fw-bold">Street Address</label>
                                                <input type="text" class="form-control rounded-pill px-4" id="address" name="address" value="{{ old('address') }}">
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="city" class="form-label fw-bold">City</label>
                                                    <input type="text" class="form-control rounded-pill px-4" id="city" name="city" value="{{ old('city') }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="state" class="form-label fw-bold">State/Province</label>
                                                    <input type="text" class="form-control rounded-pill px-4" id="state" name="state" value="{{ old('state') }}">
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="postal_code" class="form-label fw-bold">Postal Code</label>
                                                    <input type="text" class="form-control rounded-pill px-4" id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="country" class="form-label fw-bold">Country</label>
                                                    <input type="text" class="form-control rounded-pill px-4" id="country" name="country" value="{{ old('country') }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Settings Section -->
                                        <div class="col-md-6">
                                            <h5 class="fw-bold mb-3 border-bottom pb-2">
                                                <i class="fas fa-cog me-2 text-primary"></i>Settings
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label for="status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                                <select class="form-select rounded-pill" id="status" name="status" required>
                                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="commission_rate" class="form-label fw-bold">Commission Rate (%)</label>
                                                <input type="number" class="form-control rounded-pill px-4" id="commission_rate" name="commission_rate" value="{{ old('commission_rate', 10) }}" min="0" max="100" step="0.01">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="logo" class="form-label fw-bold">Store Logo</label>
                                                <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="description" class="form-label fw-bold">Store Description</label>
                                                <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                                        <button type="submit" class="btn btn-theme rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i>Create Vendor
                                        </button>
                                    </div>
                                </form>
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
