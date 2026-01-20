<div class="mb-4">
    <h5 class="fw-bold mb-3">Basic Information</h5>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="text-muted small mb-1">Product Name</label>
            <div class="fw-medium">{{ $product->name }}</div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="text-muted small mb-1">Status</label>
            <div>
                @if($product->status === 'published')
                    <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
                        Published
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3 py-2">
                        Draft
                    </span>
                @endif
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="text-muted small mb-1">MRP</label>
            <div class="fw-medium">₹{{ number_format($product->mrp, 2) }}</div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="text-muted small mb-1">Selling Price</label>
            <div class="fw-medium">₹{{ number_format($product->selling_price ?? 0, 2) }}</div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="text-muted small mb-1">Stock Status</label>
            <div>
                @if($product->in_stock)
                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2">
                        In Stock ({{ $product->stock_quantity }})
                    </span>
                @else
                    <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill px-3 py-2">
                        Out of Stock
                    </span>
                @endif
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="text-muted small mb-1">Created</label>
            <div class="fw-medium">
                @if($product->created_at)
                    {{ $product->created_at->format('F j, Y \a\t g:i A') }}
                @else
                    N/A
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mb-4">
    <h5 class="fw-bold mb-3">Description</h5>
    <div class="border rounded-3 p-3">
        @if($product->description)
            {{ $product->description }}
        @else
            <span class="text-muted">No description provided</span>
        @endif
    </div>
</div>

<div class="mb-4">
    <h5 class="fw-bold mb-3">Main Photo</h5>
    <div class="border rounded-3 p-3 text-center">
        @if($product->mainPhoto)
            <img src="{{ $product->mainPhoto->url }}" class="img-fluid rounded" alt="{{ $product->name }}" style="max-height: 300px; object-fit: contain;">
        @else
            <div class="py-5">
                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">No main photo</p>
            </div>
        @endif
    </div>
</div>

<div class="mb-4">
    <h5 class="fw-bold mb-3">Gallery Photos</h5>
    <div class="border rounded-3 p-3">
        @if($product->product_gallery && count($product->product_gallery) > 0)
            <div class="d-flex flex-wrap gap-2">
                @foreach($product->product_gallery as $mediaId)
                    @php
                        $media = \App\Models\Media::find($mediaId);
                    @endphp
                    @if($media)
                        <div>
                            <img src="{{ $media->url }}" class="img-fluid rounded" alt="Gallery image" style="height: 80px; object-fit: cover;">
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="py-3 text-center">
                <i class="fas fa-images fa-2x text-muted mb-2"></i>
                <p class="text-muted mb-0">No gallery photos</p>
            </div>
        @endif
    </div>
</div>

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">SEO Settings</h5>
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" id="toggle-seo-settings-modal">
            <i class="fas fa-chevron-down me-1"></i> <span>Expand</span>
        </button>
    </div>
    <div id="seo-settings-content-modal" class="border rounded-3 p-3 d-none">
        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="text-muted small mb-1">Meta Title</label>
                <div class="fw-medium">
                    @if($product->meta_title)
                        {{ $product->meta_title }}
                    @else
                        <span class="text-muted">Not set</span>
                    @endif
                </div>
            </div>
            <div class="col-md-12 mb-3">
                <label class="text-muted small mb-1">Meta Description</label>
                <div class="fw-medium">
                    @if($product->meta_description)
                        {{ $product->meta_description }}
                    @else
                        <span class="text-muted">Not set</span>
                    @endif
                </div>
            </div>
            <div class="col-md-12 mb-3">
                <label class="text-muted small mb-1">Meta Keywords</label>
                <div class="fw-medium">
                    @if($product->meta_keywords)
                        {{ $product->meta_keywords }}
                    @else
                        <span class="text-muted">Not set</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mt-4">
    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-theme rounded-pill px-4 me-2">
        <i class="fas fa-edit me-2"></i> Edit Product
    </a>
    
    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger rounded-pill px-4">
            <i class="fas fa-trash me-2"></i> Delete Product
        </button>
    </form>
</div>