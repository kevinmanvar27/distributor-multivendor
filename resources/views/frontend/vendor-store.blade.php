@extends('frontend.layouts.app')

@section('title', $vendor->store_name . ' - ' . setting('site_title', 'Frontend App'))

@section('content')
<div class="container-fluid px-0">
    <!-- Vendor Store Banner Section -->
    <div class="store-banner position-relative" style="height: 300px; overflow: hidden;">
        @if($vendor->store_banner_url)
            <div class="w-100 h-100" style="background: linear-gradient(135deg, var(--theme-color) 0%, #333 100%);">
                <img src="{{ $vendor->store_banner_url }}" alt="{{ $vendor->store_name }}" class="w-100 h-100" style="object-fit: cover; opacity: 0.7;">
            </div>
        @else
            <!-- Default gradient banner when no banner is uploaded -->
            <div class="w-100 h-100" style="background: linear-gradient(135deg, var(--theme-color) 0%, #1a1a2e 50%, #16213e 100%);">
                <div class="position-absolute w-100 h-100" style="background: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><defs><pattern id=%22grid%22 width=%2210%22 height=%2210%22 patternUnits=%22userSpaceOnUse%22><path d=%22M 10 0 L 0 0 0 10%22 fill=%22none%22 stroke=%22rgba(255,255,255,0.05)%22 stroke-width=%220.5%22/></pattern></defs><rect width=%22100%22 height=%22100%22 fill=%22url(%23grid)%22/></svg>'); opacity: 0.5;"></div>
            </div>
        @endif
        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.3);">
            <div class="text-center text-white">
                @if($vendor->store_logo_url)
                    <img src="{{ $vendor->store_logo_url }}" alt="{{ $vendor->store_name }}" class="rounded-circle mb-3 border border-3 border-white shadow-lg" style="width: 120px; height: 120px; object-fit: cover;">
                @else
                    <div class="rounded-circle mb-3 border border-3 border-white shadow-lg d-inline-flex align-items-center justify-content-center bg-white" style="width: 120px; height: 120px;">
                        <span class="display-4 fw-bold text-theme">{{ strtoupper(substr($vendor->store_name, 0, 1)) }}</span>
                    </div>
                @endif
                <h1 class="fw-bold mb-2" style="color: white !important;">{{ $vendor->store_name }}</h1>
                @if($vendor->store_description)
                    <p class="mb-3 opacity-75 text-white">{{ Str::limit($vendor->store_description, 150) }}</p>
                @endif
                <a href="{{ route('frontend.home') }}" class="btn btn-outline-light btn-sm rounded-pill px-4 text-white">
                    <i class="fas fa-arrow-left me-2"></i>Back to Main Store
                </a>
            </div>
        </div>
    </div>
    
    <!-- Vendor Store Info Bar -->
    <div class="container py-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex flex-wrap gap-4">
                            @if($vendor->city || $vendor->state)
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt text-theme me-2"></i>
                                    <span>{{ $vendor->city }}{{ $vendor->city && $vendor->state ? ', ' : '' }}{{ $vendor->state }}</span>
                                </div>
                            @endif
                            @if($vendor->business_phone)
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-phone text-theme me-2"></i>
                                    <span>{{ $vendor->business_phone }}</span>
                                </div>
                            @endif
                            @if($vendor->business_email)
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-envelope text-theme me-2"></i>
                                    <span>{{ $vendor->business_email }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-success rounded-pill px-3 py-2">
                            <i class="fas fa-store me-1"></i>Verified Store
                        </span>
                        <span class="badge bg-primary rounded-pill px-3 py-2 ms-2">
                            <i class="fas fa-box me-1"></i>{{ $products->count() }} Products
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <!-- Categories Section -->
    @if($categories->count() > 0)
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0">
                <i class="fas fa-tags text-theme me-2"></i>Categories
            </h2>
        </div>
        
        <div class="row g-3">
            @foreach($categories as $category)
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('frontend.category.show', $category->slug) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body text-center py-4">
                            @if($category->image)
                                <img src="{{ $category->image->url }}" alt="{{ $category->name }}" class="rounded-circle mb-3" style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-theme bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="fas fa-folder text-theme fa-lg"></i>
                                </div>
                            @endif
                            <h6 class="card-title mb-1">{{ $category->name }}</h6>
                            <small class="text-muted">{{ $category->product_count }} products</small>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </section>
    @endif
    
    <!-- Products Section -->
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0">
                <i class="fas fa-box text-theme me-2"></i>All Products
            </h2>
        </div>
        
        @if($products->count() > 0)
        <div class="row g-4">
            @foreach($products as $product)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card product-card border-0 shadow-sm h-100 hover-lift">
                    <a href="{{ route('frontend.product.show', $product->slug) }}" class="text-decoration-none">
                        <div class="position-relative">
                            @if($product->mainPhoto)
                                <img src="{{ $product->mainPhoto->url }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                            
                            @if($product->mrp && $product->selling_price && $product->mrp > $product->selling_price)
                                @php
                                    $discount = round((($product->mrp - $product->selling_price) / $product->mrp) * 100);
                                @endphp
                                <span class="position-absolute top-0 end-0 badge bg-danger m-2">{{ $discount }}% OFF</span>
                            @endif
                        </div>
                        
                        <div class="card-body">
                            <h6 class="card-title text-truncate mb-2">{{ $product->name }}</h6>
                            
                            <div class="d-flex align-items-center gap-2">
                                @if($product->selling_price)
                                    <span class="fw-bold text-theme">₹{{ number_format($product->selling_price, 2) }}</span>
                                    @if($product->mrp && $product->mrp > $product->selling_price)
                                        <small class="text-muted text-decoration-line-through">₹{{ number_format($product->mrp, 2) }}</small>
                                    @endif
                                @else
                                    <span class="fw-bold text-theme">₹{{ number_format($product->mrp, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                    
                    <div class="card-footer bg-transparent border-0 pt-0">
                        <button class="btn btn-theme btn-sm w-100 rounded-pill add-to-cart-btn" 
                                data-product-id="{{ $product->id }}"
                                data-product-name="{{ $product->name }}">
                            <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">No products available</h5>
            <p class="text-muted">This store hasn't added any products yet.</p>
            <a href="{{ route('frontend.home') }}" class="btn btn-theme rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i>Back to Main Store
            </a>
        </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            
            // You can implement AJAX add to cart here
            alert('Added "' + productName + '" to cart!');
        });
    });
});
</script>
@endpush
