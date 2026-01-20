<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vendor Registration - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <link href="{{ asset('assets/css/admin.css') }}" rel="stylesheet">
    <link href="{{ url('/css/dynamic.css') }}" rel="stylesheet">
</head>
<body class="bg-background min-vh-100 d-flex align-items-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-5">
                            @if(setting('header_logo'))
                                <img src="{{ asset('storage/' . setting('header_logo')) }}" alt="{{ setting('site_title', 'Vendor Panel') }}" class="mb-4 rounded" height="60">
                            @else
                                <div class="bg-theme rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 60px; height: 60px;">
                                    <i class="fas fa-store text-white"></i>
                                </div>
                            @endif
                            <h1 class="h2 fw-bold">Become a Vendor</h1>
                            <p class="text-secondary">Register your store and start selling</p>
                        </div>
                        
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Whoops!</strong> Please fix the following errors.
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('vendor.register') }}">
                            @csrf
                            
                            <h5 class="fw-semibold mb-3 pb-2 border-bottom">
                                <i class="fas fa-user me-2 text-primary"></i>Personal Information
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="name" class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                                    <input 
                                        id="name" 
                                        type="text" 
                                        class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                        name="name" 
                                        value="{{ old('name') }}" 
                                        required 
                                        autofocus 
                                        placeholder="Enter your full name">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label for="email" class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                                    <input 
                                        id="email" 
                                        type="email" 
                                        class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                        name="email" 
                                        value="{{ old('email') }}" 
                                        required 
                                        placeholder="Enter your email">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="mobile_number" class="form-label fw-medium">Phone Number <span class="text-danger">*</span></label>
                                    <input 
                                        id="mobile_number" 
                                        type="tel" 
                                        class="form-control form-control-lg @error('mobile_number') is-invalid @enderror" 
                                        name="mobile_number" 
                                        value="{{ old('mobile_number') }}" 
                                        required 
                                        placeholder="Enter your phone number">
                                    @error('mobile_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label for="password" class="form-label fw-medium">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input 
                                            id="password" 
                                            type="password" 
                                            class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                            name="password" 
                                            required 
                                            placeholder="Create a password">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="password_confirmation" class="form-label fw-medium">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input 
                                            id="password_confirmation" 
                                            type="password" 
                                            class="form-control form-control-lg" 
                                            name="password_confirmation" 
                                            required 
                                            placeholder="Confirm your password">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <h5 class="fw-semibold mb-3 pb-2 border-bottom mt-4">
                                <i class="fas fa-store me-2 text-primary"></i>Store Information
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="store_name" class="form-label fw-medium">Store Name <span class="text-danger">*</span></label>
                                    <input 
                                        id="store_name" 
                                        type="text" 
                                        class="form-control form-control-lg @error('store_name') is-invalid @enderror" 
                                        name="store_name" 
                                        value="{{ old('store_name') }}" 
                                        required 
                                        placeholder="Enter your store name">
                                    @error('store_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label for="store_slug" class="form-label fw-medium">Store URL Slug</label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ url('/') }}/store/</span>
                                        <input 
                                            id="store_slug" 
                                            type="text" 
                                            class="form-control form-control-lg @error('store_slug') is-invalid @enderror" 
                                            name="store_slug" 
                                            value="{{ old('store_slug') }}" 
                                            placeholder="your-store">
                                    </div>
                                    <small class="text-muted">Leave empty to auto-generate from store name</small>
                                    @error('store_slug')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="store_description" class="form-label fw-medium">Store Description</label>
                                <textarea 
                                    id="store_description" 
                                    class="form-control @error('store_description') is-invalid @enderror" 
                                    name="store_description" 
                                    rows="3" 
                                    placeholder="Describe your store and what you sell">{{ old('store_description') }}</textarea>
                                @error('store_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <h5 class="fw-semibold mb-3 pb-2 border-bottom mt-4">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>Business Address
                            </h5>
                            
                            <div class="mb-4">
                                <label for="business_address" class="form-label fw-medium">Street Address <span class="text-danger">*</span></label>
                                <input 
                                    id="business_address" 
                                    type="text" 
                                    class="form-control form-control-lg @error('business_address') is-invalid @enderror" 
                                    name="business_address" 
                                    value="{{ old('business_address') }}" 
                                    required 
                                    placeholder="Enter your business address">
                                @error('business_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <label for="city" class="form-label fw-medium">City <span class="text-danger">*</span></label>
                                    <input 
                                        id="city" 
                                        type="text" 
                                        class="form-control form-control-lg @error('city') is-invalid @enderror" 
                                        name="city" 
                                        value="{{ old('city') }}" 
                                        required 
                                        placeholder="City">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-4">
                                    <label for="state" class="form-label fw-medium">State <span class="text-danger">*</span></label>
                                    <input 
                                        id="state" 
                                        type="text" 
                                        class="form-control form-control-lg @error('state') is-invalid @enderror" 
                                        name="state" 
                                        value="{{ old('state') }}" 
                                        required 
                                        placeholder="State">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-4">
                                    <label for="postal_code" class="form-label fw-medium">Postal Code <span class="text-danger">*</span></label>
                                    <input 
                                        id="postal_code" 
                                        type="text" 
                                        class="form-control form-control-lg @error('postal_code') is-invalid @enderror" 
                                        name="postal_code" 
                                        value="{{ old('postal_code') }}" 
                                        required 
                                        placeholder="Postal Code">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="country" class="form-label fw-medium">Country <span class="text-danger">*</span></label>
                                <input 
                                    id="country" 
                                    type="text" 
                                    class="form-control form-control-lg @error('country') is-invalid @enderror" 
                                    name="country" 
                                    value="{{ old('country', 'India') }}" 
                                    required 
                                    placeholder="Country">
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" id="terms" required {{ old('terms') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" class="text-primary">Terms of Service</a> and <a href="#" class="text-primary">Vendor Agreement</a>
                                    </label>
                                    @error('terms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-theme btn-lg rounded-pill px-4">
                                    <i class="fas fa-user-plus me-2"></i>Register as Vendor
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-secondary mb-0">
                                Already have a vendor account? 
                                <a href="{{ route('vendor.login') }}" class="text-primary text-decoration-none fw-medium">Sign in here</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-secondary mb-0">
                        &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggle functionality
            const togglePasswordButtons = document.querySelectorAll('.toggle-password');
            togglePasswordButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const passwordInput = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
            
            // Auto-generate slug from store name
            const storeNameInput = document.getElementById('store_name');
            const storeSlugInput = document.getElementById('store_slug');
            
            storeNameInput.addEventListener('input', function() {
                if (!storeSlugInput.value || storeSlugInput.dataset.autoGenerated === 'true') {
                    const slug = this.value
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/(^-|-$)/g, '');
                    storeSlugInput.value = slug;
                    storeSlugInput.dataset.autoGenerated = 'true';
                }
            });
            
            storeSlugInput.addEventListener('input', function() {
                this.dataset.autoGenerated = 'false';
            });
        });
    </script>
</body>
</html>
