@extends('frontend.layouts.app')

@section('title', 'Home - ' . setting('site_title', 'Frontend App'))

@section('content')
<div class="container-fluid px-0">
    <!-- Hero Section with AOS animations -->
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
                    <a href="#" class="btn btn-theme btn-ripple hover-lift">View All</a>
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
                    <a href="#" class="btn btn-theme btn-ripple hover-lift">View All</a>
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
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-success text-white">{{ ucfirst($product->status) }}</span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <a href="{{ route('frontend.product.show', $product->slug) }}" class="product-link text-decoration-none">
                                {{ $product->name }}
                            </a>
                        </h5>
                        <p class="card-text flex-grow-1">{{ Str::limit($product->description ?? 'No description available', 100) }}</p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                @php
                                    $hasSellingPrice = !is_null($product->selling_price) && $product->selling_price !== '';
                                    $displayPrice = $hasSellingPrice ? $product->selling_price : $product->mrp;
                                    $calculatedPrice = $displayPrice;
                                    
                                    if (Auth::check() && $hasSellingPrice) {
                                        $user = Auth::user();
                                        
                                        if (!is_null($user->discount_percentage) && $user->discount_percentage > 0) {
                                            $calculatedPrice = $product->selling_price * (1 - $user->discount_percentage / 100);
                                        } 
                                        else {
                                            $userGroups = $user->userGroups;
                                            if ($userGroups->count() > 0) {
                                                $highestGroupDiscount = 0;
                                                foreach ($userGroups as $group) {
                                                    if (!is_null($group->discount_percentage) && $group->discount_percentage > $highestGroupDiscount) {
                                                        $highestGroupDiscount = $group->discount_percentage;
                                                    }
                                                }
                                                
                                                if ($highestGroupDiscount > 0) {
                                                    $calculatedPrice = $product->selling_price * (1 - $highestGroupDiscount / 100);
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                <p class="fw-bold text-success mb-0">₹{{ number_format($calculatedPrice, 2) }}</p>
                                @if($hasSellingPrice && $product->mrp > $product->selling_price)
                                    <small class="text-muted text-decoration-line-through">₹{{ number_format($product->mrp, 2) }}</small>
                                @endif
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    @php
                                        // For variable products, show total stock from all variations
                                        $displayStock = $product->isVariable() ? $product->total_stock : $product->stock_quantity;
                                        $isInStock = $displayStock > 0;
                                    @endphp
                                    @if($isInStock)
                                        <i class="fas fa-check-circle text-success me-1"></i>In Stock ({{ $displayStock }})
                                    @else
                                        <i class="fas fa-times-circle text-danger me-1"></i>Out of Stock
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        @if($product->isVariable())
                            {{-- Variable Product: Show only View Product button --}}
                            <a href="{{ route('frontend.product.show', $product->slug) }}" class="btn btn-theme w-100 btn-ripple">
                                <i class="fas fa-eye me-1"></i>View Product
                            </a>
                        @else
                            {{-- Simple Product: Show Buy Now and Add to Cart buttons --}}
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-theme buy-now-btn btn-ripple flex-fill" data-product-id="{{ $product->id }}">
                                    <i class="fas fa-bolt me-1"></i>Buy Now
                                </button>
                                <button type="button" class="btn btn-outline-theme add-to-cart-btn btn-ripple flex-fill" data-product-id="{{ $product->id }}">
                                    <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                                </button>
                            </div>
                        @endif
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

<style>
    .hero-section {
        background-size: cover;
        background-position: center;
        position: relative;
        overflow: hidden;
    }
    
    .subcategories-container {
        border-top: 1px solid #eee;
        padding-top: 15px;
    }
    
    .product-link {
        font-weight: 600;
    }
    
    .category-card::after,
    .product-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, var(--theme-color), var(--link-hover-color));
    }
    
    .category-card:hover::after,
    .product-card:hover::after {
    }
    

</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.explore-btn').on('click', function() {
        var categoryId = $(this).data('category-id');
        var categoryName = $(this).data('category-name');
        var card = $(this).closest('.card');
        
        if (card.find('.subcategories-container').length > 0) {
            card.find('.subcategories-container').slideToggle(300);
            $(this).text(card.find('.subcategories-container').is(':visible') ? 'Hide Subcategories' : 'Explore');
            return;
        }
        
        var originalText = $(this).text();
        $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
        $(this).prop('disabled', true);
        
        $.ajax({
            url: '/frontend/category/' + categoryId + '/subcategories',
            type: 'GET',
            dataType: 'html',
            success: function(response) {
                var subContainer = $('<div class="subcategories-container mt-3" style="display: none;"></div>');
                subContainer.html(response);
                card.find('.card-body').append(subContainer);
                subContainer.slideDown(300);
                
                $('.explore-btn[data-category-id="' + categoryId + '"]').text('Hide Subcategories');
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                showToast('Error loading subcategories. Please try again.', 'error');
                $('.explore-btn[data-category-id="' + categoryId + '"]').text(originalText);
            },
            complete: function() {
                $('.explore-btn[data-category-id="' + categoryId + '"]').prop('disabled', false);
            }
        });
    });
});
</script>
@endsection
