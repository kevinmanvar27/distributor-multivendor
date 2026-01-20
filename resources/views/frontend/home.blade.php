@extends('frontend.layouts.app')

@section('title', 'Home - ' . setting('site_title', 'Frontend App'))

@section('content')
<div class="container-fluid px-0">
    <!-- Default Hero Section with AOS animations -->
    <div class="hero-section text-center py-5 mb-5" style="background: linear-gradient(135deg, var(--theme-color) 0%, var(--link-hover-color) 100%); color: white;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4" style="color: white !important;">
                        Welcome to {{ setting('site_title', 'Frontend App') }}
                    </h1>
                    <p class="lead mb-4" style="color: rgba(255,255,255,0.9) !important;">
                        @auth
                            Welcome back, {{ Auth::user()->name }}! Explore our latest products and categories.
                        @else
                            Discover our amazing products and categories. Join us today!
                        @endauth
                    </p>
                    @auth
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('frontend.profile') }}" class="btn btn-light btn-lg rounded-pill px-4 btn-ripple hover-lift">
                            <i class="fas fa-user me-2"></i>My Profile
                        </a>
                        <form method="POST" action="{{ route('frontend.logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-lg rounded-pill px-4 btn-ripple hover-lift">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('frontend.login') }}" class="btn btn-light btn-lg rounded-pill px-4 btn-ripple hover-lift">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                        <a href="{{ route('frontend.register') }}" class="btn btn-outline-light btn-lg rounded-pill px-4 btn-ripple hover-lift">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </a>
                    </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Categories Section -->
    <div class="section mb-5">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0 heading-text" style="color: var(--theme-color);">
                        <i class="fas fa-tags me-2"></i>Categories
                    </h2>
                </div>
                <hr class="my-3">
            </div>
        </div>
        
        @if($categories->count() > 0)
        <div class="row">
            @foreach($categories as $index => $category)
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                <div class="card h-100 shadow-sm border-0 category-card hover-lift">
                    <div class="position-relative overflow-hidden">
                        @if($category->image)
                            <img src="{{ $category->image->url }}" class="card-img-top" alt="{{ $category->name }}" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-success text-white">{{ $category->product_count }} Products</span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $category->name }}</h5>
                        <p class="card-text flex-grow-1">{{ Str::limit($category->description ?? 'No description available', 100) }}</p>
                        <div class="mt-auto">
                            <small class="text-muted">
                                {{ $category->subCategories->count() }} subcategories • 
                                {{ $category->product_count }} products
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="{{ route('frontend.category.show', $category) }}" class="btn btn-theme w-100 btn-ripple">Explore</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>No categories available at the moment.
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Products Section -->
    <div class="section mb-5">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0 heading-text" style="color: var(--theme-color);">
                        <i class="fas fa-box-open me-2"></i>Products
                    </h2>
                </div>
                <hr class="my-3">
            </div>
        </div>
        
        @if($products->count() > 0)
        <div class="row">
            @foreach($products as $index => $product)
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                <div class="card h-100 shadow-sm border-0 product-card hover-lift">
                    <div class="position-relative overflow-hidden">
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
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-danger">{{ $discount }}% OFF</span>
                            </div>
                        @endif
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title text-truncate">{{ $product->name }}</h6>
                        
                        <!-- Vendor Badge -->
                        @if($product->vendor)
                            <div class="mb-2">
                                <a href="{{ route('frontend.vendor.store', $product->vendor->store_slug) }}" class="badge bg-light text-dark text-decoration-none">
                                    <i class="fas fa-store me-1"></i>{{ $product->vendor->store_name }}
                                </a>
                            </div>
                        @endif
                        
                        <div class="d-flex align-items-center gap-2 mt-auto">
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
                    <div class="card-footer bg-transparent border-0">
                        <a href="{{ route('frontend.product.show', $product) }}" class="btn btn-theme w-100 btn-ripple">View Details</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>No products available at the moment.
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
