@extends('vendor.layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Edit Product'])
            
            @section('page-title', 'Edit Product')
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Edit Product</h4>
                                    <p class="mb-0 text-muted">{{ $product->name }}</p>
                                </div>
                                <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Products
                                </a>
                            </div>
                            
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if($errors->any())
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                
                                <form action="{{ route('vendor.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="product-form">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <!-- Basic Information -->
                                            <div class="mb-4">
                                                <label for="name" class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-pill px-4 py-2" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="description" class="form-label fw-bold">Description</label>
                                                <textarea class="form-control" id="description" name="description" rows="5">{{ old('description', $product->description) }}</textarea>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="short_description" class="form-label fw-bold">Short Description</label>
                                                <textarea class="form-control" id="short_description" name="short_description" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
                                            </div>
                                            
                                            <!-- Product Type (Read Only) -->
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Product Type</label>
                                                <input type="hidden" name="product_type" value="{{ $product->product_type }}">
                                                <div class="form-control-plaintext">
                                                    <span class="badge bg-{{ $product->product_type === 'simple' ? 'primary' : 'info' }} rounded-pill px-3 py-2">
                                                        {{ ucfirst($product->product_type) }} Product
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Pricing (Simple Product) -->
                                            @if($product->product_type === 'simple')
                                            <div id="simple-product-fields">
                                                <div class="row">
                                                    <div class="col-md-6 mb-4">
                                                        <label for="mrp" class="form-label fw-bold">MRP (₹) <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control rounded-pill px-4 py-2" id="mrp" name="mrp" value="{{ old('mrp', $product->mrp) }}" step="0.01" min="0" required>
                                                    </div>
                                                    <div class="col-md-6 mb-4">
                                                        <label for="selling_price" class="form-label fw-bold">Selling Price (₹)</label>
                                                        <input type="number" class="form-control rounded-pill px-4 py-2" id="selling_price" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" step="0.01" min="0">
                                                        <div class="form-text">Must be less than or equal to MRP</div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Inventory -->
                                                <div class="row">
                                                    <div class="col-md-4 mb-4">
                                                        <label for="sku" class="form-label fw-bold">SKU</label>
                                                        <input type="text" class="form-control rounded-pill px-4 py-2" id="sku" name="sku" value="{{ old('sku', $product->sku) }}">
                                                    </div>
                                                    <div class="col-md-4 mb-4">
                                                        <label for="stock_quantity" class="form-label fw-bold">Stock Quantity</label>
                                                        <input type="number" class="form-control rounded-pill px-4 py-2" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0">
                                                    </div>
                                                    <div class="col-md-4 mb-4">
                                                        <label for="low_quantity_threshold" class="form-label fw-bold">Low Stock Alert</label>
                                                        <input type="number" class="form-control rounded-pill px-4 py-2" id="low_quantity_threshold" name="low_quantity_threshold" value="{{ old('low_quantity_threshold', $product->low_quantity_threshold) }}" min="0">
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="in_stock" id="in_stock" value="1" {{ old('in_stock', $product->in_stock) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="in_stock">In Stock</label>
                                                    </div>
                                                </div>
                                            </div>
                                            @else
                                            <!-- Variable Product Variations -->
                                            <div id="variable-product-fields">
                                                <!-- Attribute Selection -->
                                                <div class="mb-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <label class="form-label fw-bold mb-0">Product Attributes</label>
                                                    </div>
                                                    <p class="text-muted small">Select attributes for variations (e.g., Size, Color)</p>
                                                    <div id="attribute-selection" class="border rounded-3 p-3">
                                                        @if(isset($attributes) && $attributes->count() > 0)
                                                            @php
                                                                // Get currently used attribute IDs from existing variations
                                                                $usedAttributeIds = [];
                                                                if ($product->variations) {
                                                                    foreach ($product->variations as $variation) {
                                                                        if ($variation->attribute_values) {
                                                                            $attrValues = is_array($variation->attribute_values) ? $variation->attribute_values : json_decode($variation->attribute_values, true);
                                                                            if ($attrValues) {
                                                                                $usedAttributeIds = array_merge($usedAttributeIds, array_keys($attrValues));
                                                                            }
                                                                        }
                                                                    }
                                                                    $usedAttributeIds = array_unique($usedAttributeIds);
                                                                }
                                                            @endphp
                                                            @foreach($attributes as $attribute)
                                                                <div class="form-check mb-2 attribute-item" data-attribute-id="{{ $attribute->id }}">
                                                                    <input class="form-check-input attribute-checkbox" type="checkbox" 
                                                                           id="attribute_{{ $attribute->id }}" 
                                                                           value="{{ $attribute->id }}" 
                                                                           data-attribute-name="{{ $attribute->name }}"
                                                                           data-attribute-values='@json($attribute->values->pluck('value', 'id'))'
                                                                           {{ in_array($attribute->id, $usedAttributeIds) ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="attribute_{{ $attribute->id }}">
                                                                        <strong>{{ $attribute->name }}</strong>
                                                                        <small class="text-muted d-block">
                                                                            Values: {{ $attribute->values->pluck('value')->join(', ') }}
                                                                        </small>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <p class="text-muted mb-0" id="no-attributes-message">No attributes available. Please contact admin to create attributes.</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <!-- Existing Variations -->
                                                <div class="mb-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <label class="form-label fw-bold mb-0">Product Variations</label>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-sm btn-primary rounded-pill me-2" id="generate-variations-btn">
                                                                <i class="fas fa-magic me-1"></i> Auto-Generate All
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="add-variation-manually-btn">
                                                                <i class="fas fa-plus me-1"></i> Add Manual
                                                            </button>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="accordion" id="variationsAccordion">
                                                        @if($product->variations && $product->variations->count() > 0)
                                                            @foreach($product->variations as $index => $variation)
                                                                @php
                                                                    $attrValues = is_array($variation->attribute_values) ? $variation->attribute_values : json_decode($variation->attribute_values, true);
                                                                    $variationName = [];
                                                                    $attributeBadges = '';
                                                                    if ($attrValues) {
                                                                        foreach ($attrValues as $attrId => $valueId) {
                                                                            $attr = $attributes->firstWhere('id', $attrId);
                                                                            if ($attr) {
                                                                                $value = $attr->values->firstWhere('id', $valueId);
                                                                                if ($value) {
                                                                                    $variationName[] = $value->value;
                                                                                    $attributeBadges .= '<span class="badge bg-light text-dark border me-1">' . $attr->name . ': ' . $value->value . '</span>';
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                    $variationNameStr = implode(' - ', $variationName) ?: 'Variation ' . ($index + 1);
                                                                @endphp
                                                                <div class="accordion-item variation-card" data-variation-index="{{ $index }}" data-variation-id="{{ $variation->id }}">
                                                                    <h2 class="accordion-header" id="heading-{{ $index }}">
                                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $index }}" aria-expanded="false" aria-controls="collapse-{{ $index }}">
                                                                            <div class="d-flex align-items-center w-100 me-3">
                                                                                <div class="variation-header-image me-3">
                                                                                    @if($variation->image)
                                                                                        <img src="{{ $variation->image->url }}" alt="{{ $variationNameStr }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                                                    @else
                                                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                                                            <i class="fas fa-image text-muted"></i>
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="flex-grow-1">
                                                                                    <strong class="text-primary">{{ $variationNameStr }}</strong>
                                                                                    <div class="small text-muted">
                                                                                        {!! $attributeBadges ?: 'No attributes' !!}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </button>
                                                                    </h2>
                                                                    <div id="collapse-{{ $index }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $index }}" data-bs-parent="#variationsAccordion">
                                                                        <div class="accordion-body">
                                                                            <div class="row g-3">
                                                                                <!-- Variation Image -->
                                                                                <div class="col-md-2">
                                                                                    <label class="form-label fw-bold small">Image</label>
                                                                                    <div class="variation-image-upload">
                                                                                        <div class="image-preview-container position-relative" style="width: 100%; height: 120px; border: 2px dashed #dee2e6; border-radius: 8px; overflow: hidden; background: #f8f9fa;">
                                                                                            @if($variation->image)
                                                                                                <img src="{{ $variation->image->url }}" alt="Variation Image" class="w-100 h-100" style="object-fit: cover;">
                                                                                            @else
                                                                                                <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                                                                                    <div class="text-center">
                                                                                                        <i class="fas fa-image fa-2x mb-2"></i>
                                                                                                        <div class="small">No Image</div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            @endif
                                                                                        </div>
                                                                                        <input type="file" 
                                                                                               class="form-control form-control-sm mt-2 variation-image-input" 
                                                                                               name="variations[{{ $index }}][image]" 
                                                                                               accept="image/*"
                                                                                               data-variation-index="{{ $index }}">
                                                                                        <input type="hidden" name="variations[{{ $index }}][id]" value="{{ $variation->id }}">
                                                                                        @if($variation->image_id)
                                                                                            <input type="hidden" name="variations[{{ $index }}][image_id]" value="{{ $variation->image_id }}">
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                                
                                                                                <!-- Variation Details -->
                                                                                <div class="col-md-10">
                                                                                    <div class="row g-3">
                                                                                        <!-- Hidden Inputs for Attributes -->
                                                                                        <div class="col-12">
                                                                                            @if($attrValues)
                                                                                                @foreach($attrValues as $attrId => $valueId)
                                                                                                    <input type="hidden" name="variations[{{ $index }}][attribute_values][{{ $attrId }}]" value="{{ $valueId }}">
                                                                                                @endforeach
                                                                                            @endif
                                                                                        </div>
                                                                                    
                                                                                    <!-- SKU -->
                                                                                    <div class="col-md-3">
                                                                                        <label class="form-label small fw-bold">SKU</label>
                                                                                        <input type="text" class="form-control form-control-sm" 
                                                                                               name="variations[{{ $index }}][sku]" 
                                                                                               value="{{ $variation->sku }}"
                                                                                               placeholder="Enter SKU">
                                                                                    </div>
                                                                                    
                                                                                    <!-- MRP -->
                                                                                    <div class="col-md-2">
                                                                                        <label class="form-label small fw-bold">MRP (₹)</label>
                                                                                        <input type="number" class="form-control form-control-sm" 
                                                                                               name="variations[{{ $index }}][mrp]" 
                                                                                               value="{{ $variation->mrp }}"
                                                                                               placeholder="MRP" 
                                                                                               step="0.01" min="0">
                                                                                    </div>
                                                                                    
                                                                                    <!-- Selling Price -->
                                                                                    <div class="col-md-2">
                                                                                        <label class="form-label small fw-bold">Selling Price (₹)</label>
                                                                                        <input type="number" class="form-control form-control-sm" 
                                                                                               name="variations[{{ $index }}][selling_price]" 
                                                                                               value="{{ $variation->selling_price }}"
                                                                                               placeholder="Price" 
                                                                                               step="0.01" min="0">
                                                                                    </div>
                                                                                    
                                                                                    <!-- Stock Quantity -->
                                                                                    <div class="col-md-2">
                                                                                        <label class="form-label small fw-bold">Stock <span class="text-danger">*</span></label>
                                                                                        <input type="number" class="form-control form-control-sm" 
                                                                                               name="variations[{{ $index }}][stock_quantity]" 
                                                                                               value="{{ $variation->stock_quantity }}" 
                                                                                               placeholder="Stock" 
                                                                                               min="0"
                                                                                               required>
                                                                                    </div>
                                                                                    
                                                                                    <!-- Low Stock Threshold -->
                                                                                    <div class="col-md-2">
                                                                                        <label class="form-label small fw-bold">Low Stock Alert</label>
                                                                                        <input type="number" class="form-control form-control-sm" 
                                                                                               name="variations[{{ $index }}][low_quantity_threshold]" 
                                                                                               value="{{ $variation->low_quantity_threshold ?? 10 }}" 
                                                                                               placeholder="Threshold" 
                                                                                               min="0">
                                                                                    </div>
                                                                                    
                                                                                    <!-- Status -->
                                                                                    <div class="col-md-1">
                                                                                        <label class="form-label small fw-bold">Status</label>
                                                                                        <div class="form-check form-switch">
                                                                                            <input class="form-check-input" type="checkbox" 
                                                                                                   name="variations[{{ $index }}][in_stock]" 
                                                                                                   value="1" {{ $variation->in_stock ? 'checked' : '' }}>
                                                                                        </div>
                                                                                    </div>
                                                                                    
                                                                                    <!-- Remove Button -->
                                                                                    <div class="col-12">
                                                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-variation-btn">
                                                                                            <i class="fas fa-trash me-1"></i> Remove This Variation
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                    
                                                    @if(!$product->variations || $product->variations->count() == 0)
                                                        <p class="text-muted text-center mb-0" id="no-variations-message">No variations added yet. Select attributes and click "Auto-Generate All" or "Add Manual".</p>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                        
                                        <div class="col-lg-4">
                                            <!-- Status -->
                                            <div class="card mb-4">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0 fw-bold">Publish</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label for="status" class="form-label fw-bold">Status</label>
                                                        <select class="form-select rounded-pill" id="status" name="status">
                                                            <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                                            <option value="published" {{ old('status', $product->status) == 'published' ? 'selected' : '' }}>Published</option>
                                                        </select>
                                                    </div>
                                                    <button type="submit" class="btn btn-theme w-100 rounded-pill">
                                                        <i class="fas fa-save me-2"></i>Update Product
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Categories -->
                                            <div class="card mb-4">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0 fw-bold">Categories</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="category-list" style="max-height: 200px; overflow-y: auto;">
                                                        @php
                                                            $productCategories = $product->categories->pluck('id')->toArray();
                                                        @endphp
                                                        @foreach($categories ?? [] as $category)
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}" id="cat_{{ $category->id }}" {{ in_array($category->id, old('categories', $productCategories)) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="cat_{{ $category->id }}">{{ $category->name }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Current Product Image -->
                                            <div class="card mb-4">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0 fw-bold">Product Image</h6>
                                                </div>
                                                <div class="card-body">
                                                    <input type="hidden" name="main_photo_id" id="main_photo_id" value="{{ old('main_photo_id', $product->main_photo_id) }}">
                                                    <div id="main-image-preview">
                                                        @if($product->mainPhoto)
                                                            <div class="position-relative">
                                                                <img src="{{ $product->mainPhoto->url }}" class="img-fluid rounded" alt="{{ $product->name }}" style="max-height: 200px; object-fit: contain; width: 100%;">
                                                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle" onclick="removeMainImage()">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                        @else
                                                            <div class="upload-area" id="main-image-upload-area" style="min-height: 180px; border: 2px dashed #dee2e6; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                                                <div class="text-center">
                                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                                    <p class="text-muted mb-2 small">Drag & drop an image here</p>
                                                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="openMediaLibrary('main')">
                                                                        <i class="fas fa-folder-open me-1"></i> Media Library
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="form-text mt-2">Recommended: 800x800 pixels</div>
                                                </div>
                                            </div>
                                            
                                            <!-- Gallery Images -->
                                            <div class="card mb-4">
                                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0 fw-bold">Gallery Images</h6>
                                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="openMediaLibrary('gallery')">
                                                        <i class="fas fa-plus me-1"></i> Add
                                                    </button>
                                                </div>
                                                <div class="card-body">
                                                    @php
                                                        $galleryIds = $product->galleryPhotos ? $product->galleryPhotos->pluck('id')->toArray() : [];
                                                    @endphp
                                                    <input type="hidden" name="product_gallery" id="product_gallery" value="{{ old('product_gallery', json_encode($galleryIds)) }}">
                                                    <div id="gallery-preview" class="row g-2">
                                                        @if($product->galleryPhotos && $product->galleryPhotos->count() > 0)
                                                            @foreach($product->galleryPhotos as $photo)
                                                                <div class="col-4 col-md-6 mb-2" data-gallery-id="{{ $photo->id }}">
                                                                    <div class="position-relative">
                                                                        <img src="{{ $photo->url }}" class="img-fluid rounded" alt="Gallery" style="height: 80px; width: 100%; object-fit: cover;">
                                                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle" style="width: 24px; height: 24px; padding: 0; font-size: 10px;" onclick="removeGalleryImage({{ $photo->id }})">
                                                                            <i class="fas fa-times"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="col-12 text-center text-muted py-3" id="gallery-empty-state">
                                                                <i class="fas fa-images fa-2x mb-2"></i>
                                                                <p class="small mb-0">No gallery images selected</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>

<!-- Media Library Modal -->
<div class="modal fade" id="mediaLibraryModal" tabindex="-1" aria-labelledby="mediaLibraryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaLibraryModalLabel">Media Library</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 mb-3">
                            <h6 class="mb-3">Upload New Media</h6>
                            <form id="mediaUploadForm">
                                @csrf
                                <div class="mb-3">
                                    <div class="upload-area" id="media-upload-area" style="min-height: 100px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                        <div class="text-center" id="media-upload-content">
                                            <i class="fas fa-cloud-upload-alt text-muted mb-2" style="font-size: 24px;"></i>
                                            <p class="text-muted mb-0 small">Drag & drop file here or click to upload</p>
                                        </div>
                                    </div>
                                    <input type="file" class="form-control d-none" id="mediaFile" name="file" accept="image/*">
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Select Image</h6>
                            <input type="text" class="form-control form-control-sm rounded-pill" id="mediaSearch" placeholder="Search..." style="width: 200px;">
                        </div>
                        
                        <div id="mediaLibraryContent" class="row">
                            <!-- Media items will be loaded here -->
                        </div>
                        
                        <div id="mediaLibraryPagination" class="d-flex justify-content-center mt-3">
                            <!-- Pagination will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Variable Product Functionality
    let selectedAttributes = [];
    let variationCounter = {{ $product->variations ? $product->variations->count() : 0 }};
    
    $(document).ready(function() {
        // Initialize selected attributes from checked checkboxes
        updateSelectedAttributes();
        
        // Attribute Selection
        $(document).on('change', '.attribute-checkbox', function() {
            updateSelectedAttributes();
        });
        
        function updateSelectedAttributes() {
            selectedAttributes = [];
            $('.attribute-checkbox:checked').each(function() {
                const attributeId = $(this).val();
                const attributeName = $(this).data('attribute-name');
                const attributeValues = $(this).data('attribute-values');
                
                selectedAttributes.push({
                    id: attributeId,
                    name: attributeName,
                    values: attributeValues
                });
            });
        }
        
        // Generate All Variations
        $('#generate-variations-btn').on('click', function() {
            if (selectedAttributes.length === 0) {
                alert('Please select at least one attribute first.');
                return;
            }
            
            // Generate all combinations
            const combinations = generateCombinations(selectedAttributes);
            
            // Add each combination as a row (skip duplicates)
            let addedCount = 0;
            combinations.forEach(combination => {
                const beforeCount = $('#variationsAccordion .accordion-item').length;
                addVariationRow(combination);
                const afterCount = $('#variationsAccordion .accordion-item').length;
                if (afterCount > beforeCount) {
                    addedCount++;
                }
            });
            
            // Hide no variations message if variations were added
            if ($('#variationsAccordion .accordion-item').length > 0) {
                $('#no-variations-message').hide();
            }
            
            if (addedCount > 0) {
                alert(`Successfully generated ${addedCount} new variation(s)!`);
            } else if (combinations.length > 0) {
                alert('All variations already exist. No new variations were added.');
            }
        });
        
        function generateCombinations(attributes) {
            if (attributes.length === 0) return [{}];
            
            const [first, ...rest] = attributes;
            const restCombinations = generateCombinations(rest);
            const combinations = [];
            
            // Convert values object to array of entries
            const valueEntries = Object.entries(first.values);
            
            valueEntries.forEach(([valueId, valueName]) => {
                restCombinations.forEach(restCombo => {
                    combinations.push({
                        ...restCombo,
                        [first.id]: { id: valueId, name: valueName, attributeName: first.name }
                    });
                });
            });
            
            return combinations;
        }
        
        function addVariationRow(combination = {}, skipDuplicateCheck = false) {
            // Check for duplicates if combination is provided
            if (!skipDuplicateCheck && Object.keys(combination).length > 0) {
                const attributeValues = {};
                Object.entries(combination).forEach(([attrId, attrData]) => {
                    attributeValues[attrId] = attrData.id;
                });
                
                if (isDuplicateVariation(attributeValues)) {
                    return;
                }
            }
            
            const index = variationCounter++;
            
            // Build variation name
            let variationName = '';
            let attributeInputs = '';
            const combinationEntries = Object.entries(combination);
            
            combinationEntries.forEach(([attrId, attrData], idx) => {
                if (idx > 0) variationName += ' - ';
                variationName += attrData.name;
                
                attributeInputs += `
                    <input type="hidden" name="variations[${index}][attribute_values][${attrId}]" value="${attrData.id}">
                `;
            });
            
            if (!variationName) {
                variationName = 'Variation ' + (index + 1);
            }
            
            // Build attribute badges for accordion header
            let attributeBadges = '';
            combinationEntries.forEach(([attrId, attrData]) => {
                attributeBadges += `<span class="badge bg-light text-dark border me-1">${attrData.attributeName}: ${attrData.name}</span>`;
            });
            
            const card = `
                <div class="accordion-item variation-card" data-variation-index="${index}" data-variation-id="">
                    <h2 class="accordion-header" id="heading-${index}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${index}" aria-expanded="false" aria-controls="collapse-${index}">
                            <div class="d-flex align-items-center w-100 me-3">
                                <div class="variation-header-image me-3">
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <strong class="text-primary">${variationName}</strong>
                                    <div class="small text-muted">
                                        ${attributeBadges || 'No attributes'}
                                    </div>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse-${index}" class="accordion-collapse collapse" aria-labelledby="heading-${index}" data-bs-parent="#variationsAccordion">
                        <div class="accordion-body">
                            <div class="row g-3">
                                <!-- Variation Image -->
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small">Image</label>
                                    <div class="variation-image-upload">
                                        <div class="image-preview-container position-relative" style="width: 100%; height: 120px; border: 2px dashed #dee2e6; border-radius: 8px; overflow: hidden; background: #f8f9fa;">
                                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                                <div class="text-center">
                                                    <i class="fas fa-image fa-2x mb-2"></i>
                                                    <div class="small">No Image</div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="file" 
                                               class="form-control form-control-sm mt-2 variation-image-input" 
                                               name="variations[${index}][image]" 
                                               accept="image/*"
                                               data-variation-index="${index}">
                                    </div>
                                </div>
                                
                                <!-- Variation Details -->
                                <div class="col-md-10">
                                    <div class="row g-3">
                                        <!-- Hidden Inputs for Attributes -->
                                        <div class="col-12">
                                            ${attributeInputs}
                                        </div>
                                    
                                    <!-- SKU -->
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">SKU</label>
                                        <input type="text" class="form-control form-control-sm" 
                                               name="variations[${index}][sku]" 
                                               placeholder="Enter SKU">
                                    </div>
                                    
                                    <!-- MRP -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">MRP (₹)</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][mrp]" 
                                               placeholder="MRP" 
                                               step="0.01" min="0">
                                    </div>
                                    
                                    <!-- Selling Price -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Selling Price (₹)</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][selling_price]" 
                                               placeholder="Price" 
                                               step="0.01" min="0">
                                    </div>
                                    
                                    <!-- Stock Quantity -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Stock <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][stock_quantity]" 
                                               value="0" 
                                               placeholder="Stock" 
                                               min="0"
                                               required>
                                    </div>
                                    
                                    <!-- Low Stock Threshold -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Low Stock Alert</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][low_quantity_threshold]" 
                                               value="10" 
                                               placeholder="Threshold" 
                                               min="0">
                                    </div>
                                    
                                    <!-- Status -->
                                    <div class="col-md-1">
                                        <label class="form-label small fw-bold">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="variations[${index}][in_stock]" 
                                                   value="1" checked>
                                        </div>
                                    </div>
                                    
                                    <!-- Remove Button -->
                                    <div class="col-12">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-variation-btn">
                                            <i class="fas fa-trash me-1"></i> Remove This Variation
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#variationsAccordion').append(card);
        }
        
        // Check for duplicate variation
        function isDuplicateVariation(attributeValues) {
            let isDuplicate = false;
            $('#variationsAccordion .variation-card').each(function() {
                const card = $(this);
                let matches = true;
                
                // Check if all attribute values match
                for (const [attrId, valueId] of Object.entries(attributeValues)) {
                    const existingValue = card.find(`input[name*="[attribute_values][${attrId}]"]`).val();
                    if (existingValue != valueId) {
                        matches = false;
                        break;
                    }
                }
                
                if (matches) {
                    isDuplicate = true;
                    return false; // break the loop
                }
            });
            
            return isDuplicate;
        }
        
        // Add Variation Manually
        $('#add-variation-manually-btn').on('click', function() {
            if (selectedAttributes.length === 0) {
                alert('Please select at least one attribute first.');
                return;
            }
            
            // Build a manual variation selector
            const index = variationCounter++;
            let attributeSelectors = '';
            
            selectedAttributes.forEach(attr => {
                const options = Object.entries(attr.values).map(([id, name]) => 
                    `<option value="${id}">${name}</option>`
                ).join('');
                
                attributeSelectors += `
                    <div class="mb-2">
                        <label class="form-label small">${attr.name}</label>
                        <select class="form-select form-select-sm variation-attribute-select" 
                                data-attribute-id="${attr.id}"
                                data-attribute-name="${attr.name}"
                                name="variations[${index}][attribute_values][${attr.id}]" required>
                            <option value="">Select ${attr.name}</option>
                            ${options}
                        </select>
                    </div>
                `;
            });
            
            const card = `
                <div class="variation-card card border mb-3" data-variation-index="${index}">
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Variation Image -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">Image</label>
                                <div class="variation-image-upload">
                                    <div class="image-preview-container position-relative" style="width: 100%; height: 120px; border: 2px dashed #dee2e6; border-radius: 8px; overflow: hidden; background: #f8f9fa;">
                                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                            <div class="text-center">
                                                <i class="fas fa-image fa-2x mb-2"></i>
                                                <div class="small">No Image</div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="file" 
                                           class="form-control form-control-sm mt-2 variation-image-input" 
                                           name="variations[${index}][image]" 
                                           accept="image/*"
                                           data-variation-index="${index}">
                                </div>
                            </div>
                            
                            <!-- Variation Details -->
                            <div class="col-md-10">
                                <div class="row g-3">
                                    <!-- Variation Name -->
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0 fw-bold text-primary variation-name-display">Select attributes</h6>
                                                <div class="variation-attributes mt-2">
                                                    ${attributeSelectors}
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-variation-btn" title="Remove Variation">
                                                <i class="fas fa-trash me-1"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- SKU -->
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">SKU</label>
                                        <input type="text" class="form-control form-control-sm" 
                                               name="variations[${index}][sku]" 
                                               placeholder="Enter SKU">
                                    </div>
                                    
                                    <!-- MRP -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">MRP (₹)</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][mrp]" 
                                               placeholder="MRP" 
                                               step="0.01" min="0">
                                    </div>
                                    
                                    <!-- Selling Price -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Selling Price (₹)</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][selling_price]" 
                                               placeholder="Price" 
                                               step="0.01" min="0">
                                    </div>
                                    
                                    <!-- Stock Quantity -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Stock <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][stock_quantity]" 
                                               value="0" 
                                               placeholder="Stock" 
                                               min="0"
                                               required>
                                    </div>
                                    
                                    <!-- Low Stock Threshold -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Low Stock Alert</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="variations[${index}][low_quantity_threshold]" 
                                               value="10" 
                                               placeholder="Threshold" 
                                               min="0">
                                    </div>
                                    
                                    <!-- Status -->
                                    <div class="col-md-1">
                                        <label class="form-label small fw-bold">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="variations[${index}][in_stock]" 
                                                   value="1" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#variationsAccordion').append(card);
            $('#no-variations-message').hide();
        });
        
        // Remove variation
        $(document).on('click', '.remove-variation-btn', function() {
            $(this).closest('.variation-card').remove();
            
            // Show no variations message if no variations left
            if ($('#variationsAccordion .variation-card').length === 0) {
                $('#no-variations-message').show();
            }
        });
        
        // Update variation name when attributes are selected (for manual variations)
        $(document).on('change', '.variation-attribute-select', function() {
            const card = $(this).closest('.variation-card');
            const nameDisplay = card.find('.variation-name-display');
            
            let nameParts = [];
            card.find('.variation-attribute-select').each(function() {
                const selectedOption = $(this).find('option:selected');
                if (selectedOption.val()) {
                    nameParts.push(selectedOption.text());
                }
            });
            
            if (nameParts.length > 0) {
                nameDisplay.text(nameParts.join(' - '));
            } else {
                nameDisplay.text('Select attributes');
            }
        });
        
        // Variation image preview
        $(document).on('change', '.variation-image-input', function() {
            const input = this;
            const container = $(this).closest('.variation-image-upload').find('.image-preview-container');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    container.html(`
                        <img src="${e.target.result}" alt="Variation Image" class="w-100 h-100" style="object-fit: cover;">
                    `);
                };
                reader.readAsDataURL(input.files[0]);
            }
        });
        
        // Form validation before submit
        $('#product-form').on('submit', function(e) {
            const productType = $('input[name="product_type"]').val();
            
            if (productType === 'variable') {
                // Add product_attributes[] hidden inputs
                $('input[name="product_attributes[]"]').remove();
                
                selectedAttributes.forEach(attr => {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'product_attributes[]',
                        value: attr.id
                    }).appendTo('#product-form');
                });
            }
            
            return true;
        });
        
        // ========== Media Library Functions ==========
        
        // Click handler for main image upload area
        $(document).on('click', '#main-image-upload-area', function(e) {
            if (!$(e.target).closest('button').length) {
                openMediaLibrary('main');
            }
        });
        
        // Drag and drop for main image
        $(document).on('dragover', '#main-image-upload-area', function(e) {
            e.preventDefault();
            $(this).addClass('drag-over');
        });
        
        $(document).on('dragleave', '#main-image-upload-area', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
        });
        
        $(document).on('drop', '#main-image-upload-area', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                uploadAndSelectImage(files[0], 'main');
            }
        });
        
        // Media search with debounce
        let mediaSearchTimeout;
        $('#mediaSearch').on('input', function() {
            clearTimeout(mediaSearchTimeout);
            mediaSearchTimeout = setTimeout(function() {
                loadMedia(1);
            }, 300);
        });
        
        // Media upload area click handler
        $(document).on('click', '#media-upload-area', function() {
            $('#mediaFile').click();
        });
        
        // Auto-upload on file select
        $('#mediaFile').on('change', function() {
            if (this.files && this.files[0]) {
                uploadMedia();
            }
        });
        
        // Drag and drop for media upload area
        $(document).on('dragover', '#media-upload-area', function(e) {
            e.preventDefault();
            $(this).addClass('drag-over');
        });
        
        $(document).on('dragleave', '#media-upload-area', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
        });
        
        $(document).on('drop', '#media-upload-area', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(files[0]);
                $('#mediaFile')[0].files = dataTransfer.files;
                uploadMedia();
            }
        });
    });
    
    // Media Library Variables
    const baseUrl = '{{ url('/') }}';
    let currentMediaTarget = null;
    let galleryImages = [];
    
    // Initialize gallery from existing data
    $(document).ready(function() {
        try {
            const existingGallery = JSON.parse($('#product_gallery').val() || '[]');
            if (Array.isArray(existingGallery) && existingGallery.length > 0) {
                // Load gallery images from existing data
                $('#gallery-preview [data-gallery-id]').each(function() {
                    const id = parseInt($(this).data('gallery-id'));
                    const url = $(this).find('img').attr('src');
                    const name = 'Gallery Image';
                    galleryImages.push({ id: id, url: url, name: name });
                });
            }
        } catch (e) {
            galleryImages = [];
        }
    });
    
    // Open media library modal
    function openMediaLibrary(target) {
        currentMediaTarget = target;
        loadMedia(1);
        $('#mediaLibraryModal').modal('show');
    }
    
    // Load media items
    function loadMedia(page = 1) {
        const searchTerm = $('#mediaSearch').val() || '';
        
        $.ajax({
            url: baseUrl + '/vendor/media/list',
            type: 'GET',
            data: {
                page: page,
                search: searchTerm,
                type: 'images'
            },
            success: function(data) {
                let html = '';
                
                if (data.data && data.data.length > 0) {
                    data.data.forEach(function(media) {
                        const isSelected = galleryImages.some(img => img.id === media.id);
                        html += `
                            <div class="col-md-3 col-sm-4 col-6 mb-3">
                                <div class="media-item card h-100 ${isSelected ? 'selected' : ''}" onclick="selectMedia(${media.id}, '${media.url}', '${media.name || media.file_name}')">
                                    <div class="card-body p-2 text-center">
                                        <img src="${media.url}" class="img-fluid rounded mb-2" alt="${media.name || media.file_name}" style="height: 100px; object-fit: cover; width: 100%;">
                                        <p class="small mb-0 text-truncate">${media.name || media.file_name}</p>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html = `
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-images fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No media found</p>
                        </div>
                    `;
                }
                
                $('#mediaLibraryContent').html(html);
                
                // Pagination
                if (data.last_page > 1) {
                    let paginationHtml = `
                        <nav>
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                ${data.prev_page_url ? 
                                    `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadMedia(${data.current_page - 1})">«</a></li>` :
                                    `<li class="page-item disabled"><span class="page-link">«</span></li>`
                                }
                    `;
                    
                    for (let i = 1; i <= data.last_page; i++) {
                        if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                            paginationHtml += `
                                <li class="page-item ${i === data.current_page ? 'active' : ''}">
                                    <a class="page-link" href="javascript:void(0)" onclick="loadMedia(${i})">${i}</a>
                                </li>
                            `;
                        } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                            paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                        }
                    }
                    
                    paginationHtml += `
                                ${data.next_page_url ? 
                                    `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadMedia(${data.current_page + 1})">»</a></li>` :
                                    `<li class="page-item disabled"><span class="page-link">»</span></li>`
                                }
                            </ul>
                        </nav>
                    `;
                    
                    $('#mediaLibraryPagination').html(paginationHtml);
                } else {
                    $('#mediaLibraryPagination').html('');
                }
            },
            error: function() {
                $('#mediaLibraryContent').html(`
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <p class="text-danger">Error loading media</p>
                    </div>
                `);
            }
        });
    }
    
    // Select media from library
    function selectMedia(id, url, name) {
        if (currentMediaTarget === 'main') {
            $('#main_photo_id').val(id);
            $('#main-image-preview').html(`
                <div class="position-relative">
                    <img src="${url}" class="img-fluid rounded" alt="${name}" style="max-height: 200px; object-fit: contain; width: 100%;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle" onclick="removeMainImage()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
            $('#mediaLibraryModal').modal('hide');
        } else if (currentMediaTarget === 'gallery') {
            // Check if already in gallery
            if (!galleryImages.some(img => img.id === id)) {
                galleryImages.push({ id: id, url: url, name: name });
                updateGalleryInput();
                renderGalleryPreview();
            }
        } else if (currentMediaTarget === 'variation') {
            const variationIndex = currentVariationIndex;
            $(`input[name="variations[${variationIndex}][image_id]"]`).val(id);
            $(`.variation-card[data-variation-index="${variationIndex}"] .image-preview-container`).html(`
                <img src="${url}" alt="Variation Image" class="w-100 h-100" style="object-fit: cover;">
            `);
            $('#mediaLibraryModal').modal('hide');
        }
    }
    
    // Remove main image
    function removeMainImage() {
        $('#main_photo_id').val('');
        $('#main-image-preview').html(`
            <div class="upload-area" id="main-image-upload-area" style="min-height: 180px; border: 2px dashed #dee2e6; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <div class="text-center">
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-2 small">Drag & drop an image here</p>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="openMediaLibrary('main')">
                        <i class="fas fa-folder-open me-1"></i> Media Library
                    </button>
                </div>
            </div>
        `);
    }
    
    // Remove gallery image
    function removeGalleryImage(id) {
        galleryImages = galleryImages.filter(img => img.id !== id);
        updateGalleryInput();
        renderGalleryPreview();
    }
    
    // Update gallery hidden input
    function updateGalleryInput() {
        $('#product_gallery').val(JSON.stringify(galleryImages.map(img => img.id)));
    }
    
    // Render gallery preview
    function renderGalleryPreview() {
        if (galleryImages.length === 0) {
            $('#gallery-preview').html(`
                <div class="col-12 text-center text-muted py-3" id="gallery-empty-state">
                    <i class="fas fa-images fa-2x mb-2"></i>
                    <p class="small mb-0">No gallery images selected</p>
                </div>
            `);
            return;
        }
        
        let html = '';
        galleryImages.forEach(img => {
            html += `
                <div class="col-4 col-md-6 mb-2" data-gallery-id="${img.id}">
                    <div class="position-relative">
                        <img src="${img.url}" class="img-fluid rounded" alt="${img.name}" style="height: 80px; width: 100%; object-fit: cover;">
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle" style="width: 24px; height: 24px; padding: 0; font-size: 10px;" onclick="removeGalleryImage(${img.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        $('#gallery-preview').html(html);
    }
    
    // Upload media
    function uploadMedia() {
        const formData = new FormData($('#mediaUploadForm')[0]);
        
        $('#media-upload-content').html(`
            <div class="spinner-border text-primary spinner-border-sm" role="status">
                <span class="visually-hidden">Uploading...</span>
            </div>
            <p class="text-muted mb-0 small mt-2">Uploading...</p>
        `);
        
        $.ajax({
            url: baseUrl + '/vendor/media',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#media-upload-content').html(`
                    <i class="fas fa-cloud-upload-alt text-muted mb-2" style="font-size: 24px;"></i>
                    <p class="text-muted mb-0 small">Drag & drop file here or click to upload</p>
                `);
                $('#mediaFile').val('');
                
                loadMedia(1);
                
                if (response.success && response.media) {
                    selectMedia(response.media.id, response.media.url, response.media.name || response.media.file_name);
                }
            },
            error: function(xhr) {
                $('#media-upload-content').html(`
                    <i class="fas fa-cloud-upload-alt text-muted mb-2" style="font-size: 24px;"></i>
                    <p class="text-muted mb-0 small">Drag & drop file here or click to upload</p>
                `);
                $('#mediaFile').val('');
                
                let errorMessage = 'Error uploading file';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                alert(errorMessage);
            }
        });
    }
    
    // Upload and select image directly
    function uploadAndSelectImage(file, target) {
        currentMediaTarget = target;
        
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        const previewId = '#main-image-preview';
        $(previewId).html(`
            <div class="d-flex align-items-center justify-content-center" style="min-height: 180px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Uploading...</span>
                </div>
            </div>
        `);
        
        $.ajax({
            url: baseUrl + '/vendor/media',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success && response.media) {
                    selectMedia(response.media.id, response.media.url, response.media.name || response.media.file_name);
                }
            },
            error: function(xhr) {
                removeMainImage();
                let errorMessage = 'Error uploading file';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                alert(errorMessage);
            }
        });
    }
    
    // Variable for variation image selection
    let currentVariationIndex = null;
    
    // Open media library for variation
    function openVariationMediaLibrary(index) {
        currentMediaTarget = 'variation';
        currentVariationIndex = index;
        loadMedia(1);
        $('#mediaLibraryModal').modal('show');
    }
</script>
@endsection

@section('styles')
<style>
    .upload-area.drag-over {
        border-color: var(--theme-color, #FF6B00) !important;
        background-color: rgba(255, 107, 0, 0.05);
    }
    
    .upload-area {
        transition: all 0.2s ease;
    }
    
    .upload-area:hover {
        border-color: var(--theme-color, #FF6B00);
        background-color: rgba(255, 107, 0, 0.03);
    }
    
    .media-item {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }
    
    .media-item:hover {
        border-color: var(--theme-color, #FF6B00);
    }
    
    .media-item.selected {
        border-color: var(--theme-color, #FF6B00);
        background-color: rgba(255, 107, 0, 0.1);
    }
</style>
@endsection
