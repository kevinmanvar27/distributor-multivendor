@extends('vendor.layouts.app')

@section('title', 'Categories')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', ['pageTitle' => 'Category Management'])
            
            @section('page-title', 'Categories')
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h4 class="card-title mb-0 fw-bold h5 h4-md">Category Management</h4>
                                        <p class="mb-0 text-muted small">Manage your product categories and subcategories</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-md-normal btn-theme rounded-pill px-3 px-md-4" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="showCategoryModal()">
                                        <i class="fas fa-plus me-1 me-md-2"></i><span class="d-none d-sm-inline">Add New Category</span><span class="d-sm-none">Add</span>
                                    </button>
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
                                    <table class="table table-hover align-middle" id="categoriesTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Category</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($categories as $category)
                                                <tr>
                                                    <td class="fw-bold">{{ $category->id }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($category->image)
                                                                <img src="{{ $category->image->url }}" 
                                                                     class="rounded me-3" width="40" height="40" alt="{{ $category->name }}" 
                                                                     style="object-fit: cover;"
                                                                     onerror="this.onerror=null;this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2240%22 height=%2240%22><rect fill=%22%23f0f0f0%22 width=%2240%22 height=%2240%22/><text fill=%22%23999%22 font-size=%2212%22 x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22>?</text></svg>';"
                                                                     loading="lazy">
                                                            @else
                                                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-image text-muted"></i>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <div class="fw-medium">{{ $category->name }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($category->description)
                                                            <span class="text-muted">{{ Str::limit($category->description, 50) }}</span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($category->is_active)
                                                            <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2">
                                                                Active
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3 py-2">
                                                                Inactive
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <?php $categoryId = $category->id; ?>
                                                            <button type="button" class="btn btn-outline-info rounded-start-pill px-3" onclick="showSubCategories(<?php echo $categoryId; ?>)">
                                                                <i class="fas fa-list"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="editCategory(<?php echo $categoryId; ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger rounded-end-pill px-3" onclick="deleteCategory(<?php echo $categoryId; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="fas fa-tags fa-2x mb-3"></i>
                                                            <p class="mb-0">No categories found</p>
                                                            <p class="small">Try creating a new category</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                
                                @if($categories->hasPages())
                                    <div class="d-flex justify-content-center mt-4">
                                        {{ $categories->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('vendor.layouts.footer')
        </main>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    @csrf
                    <input type="hidden" id="categoryId" name="id">
                    <input type="hidden" id="categoryMethod" name="_method">
                    <input type="hidden" id="categoryImageId" name="image_id">
                    
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-pill" id="categoryName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category Image</label>
                        <div class="border rounded-3 p-3 text-center position-relative" id="category-image-preview">
                            <div class="upload-area" id="category-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                <div>
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="openMediaLibrary('category')">
                                        <i class="fas fa-folder-open me-1"></i> Select from Media Library
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categoryStatus" class="form-label">Status</label>
                        <select class="form-select rounded-pill" id="categoryStatus" name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-theme rounded-pill" onclick="saveCategory()">
                    <i class="fas fa-save me-2"></i>Save
                </button>
            </div>
        </div>
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
                                    <label for="mediaFile" class="form-label">Select File</label>
                                    <div class="upload-area" id="media-upload-area" style="min-height: 100px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                        <div class="text-center" id="media-upload-content">
                                            <i class="fas fa-cloud-upload-alt text-muted mb-2" style="font-size: 24px;"></i>
                                            <p class="text-muted mb-0 small">Drag & drop file here or click to upload</p>
                                        </div>
                                    </div>
                                    <input type="file" class="form-control d-none" id="mediaFile" name="file" accept="image/*,video/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,text/csv">
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>Existing Media</h6>
                            <div class="d-flex">
                                <input type="text" class="form-control rounded-pill me-2" id="mediaSearch" placeholder="Search media...">
                            </div>
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

<!-- Subcategories Modal -->
<div class="modal fade" id="subcategoriesModal" tabindex="-1" aria-labelledby="subcategoriesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subcategoriesModalLabel">Subcategories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 id="subcategoryParentName">Subcategories</h6>
                    <button type="button" class="btn btn-theme rounded-pill" data-bs-toggle="modal" data-bs-target="#subcategoryModal" onclick="showSubCategoryModal()">
                        <i class="fas fa-plus me-2"></i> Add Subcategory
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Subcategory</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="subcategoriesTableBody">
                            <!-- Subcategories will be loaded here -->
                        </tbody>
                    </table>
                </div>
                
                <div id="subcategoriesPagination" class="d-flex justify-content-center mt-3">
                    <!-- Pagination will be loaded here -->
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

<!-- Subcategory Modal -->
<div class="modal fade" id="subcategoryModal" tabindex="-1" aria-labelledby="subcategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subcategoryModalLabel">Add New Subcategory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="subcategoryForm">
                    @csrf
                    <input type="hidden" id="subcategoryId" name="id">
                    <input type="hidden" id="subcategoryMethod" name="_method">
                    <input type="hidden" id="subcategoryCategoryId" name="category_id">
                    <input type="hidden" id="subcategoryImageId" name="image_id">
                    
                    <div class="mb-3">
                        <label for="subcategoryName" class="form-label">Subcategory Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-pill" id="subcategoryName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subcategoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="subcategoryDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Subcategory Image</label>
                        <div class="border rounded-3 p-3 text-center position-relative" id="subcategory-image-preview">
                            <div class="upload-area" id="subcategory-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                <div>
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="openMediaLibrary('subcategory')">
                                        <i class="fas fa-folder-open me-1"></i> Select from Media Library
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subcategoryStatus" class="form-label">Status</label>
                        <select class="form-select rounded-pill" id="subcategoryStatus" name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-theme rounded-pill" onclick="saveSubCategory()">
                    <i class="fas fa-save me-2"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    /* Drag and drop styles for all upload areas */
    #media-upload-area.drag-over,
    #category-image-upload-area.drag-over,
    #subcategory-image-upload-area.drag-over {
        border-color: var(--theme-color, #FF6B00) !important;
        background-color: rgba(255, 107, 0, 0.05);
    }
    
    /* Add more specific styles for better visual feedback */
    #media-upload-area,
    #category-image-upload-area,
    #subcategory-image-upload-area {
        transition: all 0.2s ease;
    }
    
    #media-upload-area:hover,
    #category-image-upload-area:hover,
    #subcategory-image-upload-area:hover {
        border-color: var(--theme-color, #FF6B00);
        background-color: rgba(255, 107, 0, 0.03);
    }
    
    /* Upload progress indicator */
    .upload-progress-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        z-index: 10;
    }
    
    /* Media item styles */
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

@section('scripts')
<script>
    // Base URL for AJAX requests
    const baseUrl = '{{ url('/') }}';
    
    let currentMediaTarget = null;
    let currentCategoryId = null;
    
    $(document).ready(function() {
        // Initialize DataTable with a more robust check and delay
        setTimeout(function() {
            if (typeof $.fn.DataTable !== 'undefined') {
                // Destroy existing DataTable instance if it exists
                if ($.fn.DataTable.isDataTable('#categoriesTable')) {
                    $('#categoriesTable').DataTable().destroy();
                }
                
                // Only initialize if there are categories
                if ($('#categoriesTable tbody tr td[colspan="5"]').length === 0) {
                    $('#categoriesTable').DataTable({
                        "pageLength": 10,
                        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                        "ordering": true,
                        "searching": true,
                        "info": true,
                        "paging": false, // Using Laravel pagination
                        "columnDefs": [
                            { "orderable": false, "targets": [4] } // Disable sorting on Actions column
                        ],
                        "language": {
                            "search": "Search:",
                            "info": "Showing _START_ to _END_ of _TOTAL_ categories",
                            "infoEmpty": "Showing 0 to 0 of 0 categories",
                        }
                    });
                }
            }
        }, 100);
        
        // Media search with debounce
        let mediaSearchTimeout;
        $('#mediaSearch').on('input', function() {
            clearTimeout(mediaSearchTimeout);
            const searchTerm = $(this).val();
            
            mediaSearchTimeout = setTimeout(function() {
                loadMedia(1);
            }, 300);
        });
        
        // Use event delegation for media upload area click handler
        $(document).on('click', '#media-upload-area', function() {
            $('#mediaFile').click();
        });
        
        // Click handler for category image upload area (opens media library)
        $(document).on('click', '#category-image-upload-area', function(e) {
            if (!$(e.target).closest('button').length) {
                openMediaLibrary('category');
            }
        });
        
        // Click handler for subcategory image upload area (opens media library)
        $(document).on('click', '#subcategory-image-upload-area', function(e) {
            if (!$(e.target).closest('button').length) {
                openMediaLibrary('subcategory');
            }
        });
        
        // Add change handler for file input to auto-upload
        $('#mediaFile').on('change', function() {
            if (this.files && this.files[0]) {
                uploadMedia();
            }
        });
        
        // Add drag and drop functionality for media upload area
        $(document).on('dragover', '#media-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        $(document).on('dragleave', '#media-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
        });
        
        $(document).on('drop', '#media-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                $('#mediaFile')[0].files = files;
                uploadMedia();
            }
        });
        
        // Drag and drop for Category Image Upload Area
        $(document).on('dragover', '#category-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        $(document).on('dragleave', '#category-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
        });
        
        $(document).on('drop', '#category-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    uploadAndSelectImage(file, 'category');
                } else {
                    alert('Please drop an image file (JPEG, PNG, GIF, or WEBP).');
                }
            }
        });
        
        // Drag and drop for Subcategory Image Upload Area
        $(document).on('dragover', '#subcategory-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        $(document).on('dragleave', '#subcategory-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
        });
        
        $(document).on('drop', '#subcategory-image-upload-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    uploadAndSelectImage(file, 'subcategory');
                } else {
                    alert('Please drop an image file (JPEG, PNG, GIF, or WEBP).');
                }
            }
        });
    });
    
    // Show category modal for creating new category
    function showCategoryModal() {
        $('#categoryModalLabel').text('Add New Category');
        $('#categoryForm')[0].reset();
        $('#categoryId').val('');
        $('#categoryMethod').val('');
        $('#category-image-preview').html(`
            <div class="upload-area" id="category-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <div>
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="openMediaLibrary('category')">
                        <i class="fas fa-folder-open me-1"></i> Select from Media Library
                    </button>
                </div>
            </div>
        `);
        $('#categoryImageId').val('');
    }
    
    // Edit existing category
    function editCategory(id) {
        $.ajax({
            url: baseUrl + '/vendor/categories/' + id,
            type: 'GET',
            success: function(data) {
                $('#categoryModalLabel').text('Edit Category');
                $('#categoryId').val(data.id);
                $('#categoryMethod').val('PUT');
                $('#categoryName').val(data.name);
                $('#categoryDescription').val(data.description);
                $('#categoryStatus').val(data.is_active ? '1' : '0');
                
                if (data.image) {
                    $('#category-image-preview').html(`
                        <div class="position-relative">
                            <img src="${data.image.url}" class="img-fluid rounded" alt="${data.name}" style="max-height: 200px; object-fit: contain;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle" onclick="removeCategoryImage()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `);
                    $('#categoryImageId').val(data.image.id);
                } else {
                    $('#category-image-preview').html(`
                        <div class="upload-area" id="category-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                            <div>
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="openMediaLibrary('category')">
                                    <i class="fas fa-folder-open me-1"></i> Select from Media Library
                                </button>
                            </div>
                        </div>
                    `);
                    $('#categoryImageId').val('');
                }
                
                $('#categoryModal').modal('show');
            },
            error: function() {
                alert('Error loading category data.');
            }
        });
    }
    
    // Save category (create or update)
    function saveCategory() {
        const id = $('#categoryId').val();
        const url = id ? baseUrl + '/vendor/categories/' + id : baseUrl + '/vendor/categories';
        
        const formData = $('#categoryForm').serialize();
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#categoryModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = '';
                    for (let field in errors) {
                        errorMessages += errors[field].join(', ') + '\n';
                    }
                    alert('Validation errors:\n' + errorMessages);
                } else {
                    alert('Error saving category.');
                }
            }
        });
    }
    
    // Delete category
    function deleteCategory(id) {
        if (confirm('Are you sure you want to delete this category? This will also delete all subcategories.')) {
            $.ajax({
                url: baseUrl + '/vendor/categories/' + id,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'DELETE'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    alert('Error deleting category.');
                }
            });
        }
    }
    
    // Remove category image
    function removeCategoryImage() {
        $('#categoryImageId').val('');
        $('#category-image-preview').html(`
            <div class="upload-area" id="category-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <div>
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="openMediaLibrary('category')">
                        <i class="fas fa-folder-open me-1"></i> Select from Media Library
                    </button>
                </div>
            </div>
        `);
    }
    
    // Show subcategories for a category
    function showSubCategories(categoryId) {
        currentCategoryId = categoryId;
        loadSubCategories(categoryId, 1);
        $('#subcategoriesModal').modal('show');
    }
    
    // Load subcategories
    function loadSubCategories(categoryId, page = 1) {
        $.ajax({
            url: baseUrl + '/vendor/categories/' + categoryId + '/subcategories?page=' + page,
            type: 'GET',
            success: function(data) {
                // Set parent category name
                if (data.data && data.data.length > 0 && data.data[0].category) {
                    $('#subcategoryParentName').text('Subcategories for ' + data.data[0].category.name);
                } else {
                    $('#subcategoryParentName').text('Subcategories');
                }
                
                // Populate table
                let html = '';
                if (data.data && data.data.length > 0) {
                    data.data.forEach(function(subcategory) {
                        html += `
                            <tr>
                                <td>${subcategory.id}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        ${subcategory.image ? 
                                            `<img src="${subcategory.image.url}" class="rounded me-3" width="40" height="40" alt="${subcategory.name}" style="object-fit: cover;" loading="lazy">` :
                                            `<div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>`
                                        }
                                        <div>
                                            <div class="fw-medium">${subcategory.name}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>${subcategory.description ? subcategory.description.substring(0, 50) + (subcategory.description.length > 50 ? '...' : '') : 'N/A'}</td>
                                <td>
                                    ${subcategory.is_active ? 
                                        `<span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2">Active</span>` :
                                        `<span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-3 py-2">Inactive</span>`
                                    }
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary rounded-start-pill px-3" data-bs-toggle="modal" data-bs-target="#subcategoryModal" onclick="editSubCategory(${subcategory.id})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger rounded-end-pill px-3" onclick="deleteSubCategory(${subcategory.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = `
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-list fa-2x mb-3"></i>
                                    <p class="mb-0">No subcategories found</p>
                                    <p class="small">Try creating a new subcategory</p>
                                </div>
                            </td>
                        </tr>
                    `;
                }
                
                $('#subcategoriesTableBody').html(html);
                
                // Populate pagination
                if (data.last_page > 1) {
                    let paginationHtml = `
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                ${data.prev_page_url ? 
                                    `<li class="page-item"><a class="page-link rounded-pill" href="javascript:void(0)" onclick="loadSubCategories(${categoryId}, ${data.current_page - 1})">Previous</a></li>` :
                                    `<li class="page-item disabled"><span class="page-link rounded-pill">Previous</span></li>`
                                }
                    `;
                    
                    for (let i = 1; i <= data.last_page; i++) {
                        paginationHtml += `
                            <li class="page-item ${i === data.current_page ? 'active' : ''}">
                                <a class="page-link rounded-pill" href="javascript:void(0)" onclick="loadSubCategories(${categoryId}, ${i})">${i}</a>
                            </li>
                        `;
                    }
                    
                    paginationHtml += `
                                ${data.next_page_url ? 
                                    `<li class="page-item"><a class="page-link rounded-pill" href="javascript:void(0)" onclick="loadSubCategories(${categoryId}, ${data.current_page + 1})">Next</a></li>` :
                                    `<li class="page-item disabled"><span class="page-link rounded-pill">Next</span></li>`
                                }
                            </ul>
                        </nav>
                    `;
                    
                    $('#subcategoriesPagination').html(paginationHtml);
                } else {
                    $('#subcategoriesPagination').html('');
                }
            },
            error: function() {
                alert('Error loading subcategories.');
            }
        });
    }
    
    // Show subcategory modal for creating new subcategory
    function showSubCategoryModal() {
        $('#subcategoryModalLabel').text('Add New Subcategory');
        $('#subcategoryForm')[0].reset();
        $('#subcategoryId').val('');
        $('#subcategoryMethod').val('');
        $('#subcategoryCategoryId').val(currentCategoryId);
        $('#subcategory-image-preview').html(`
            <div class="upload-area" id="subcategory-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <div>
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="openMediaLibrary('subcategory')">
                        <i class="fas fa-folder-open me-1"></i> Select from Media Library
                    </button>
                </div>
            </div>
        `);
        $('#subcategoryImageId').val('');
    }
    
    // Edit existing subcategory
    function editSubCategory(id) {
        $.ajax({
            url: baseUrl + '/vendor/subcategories/' + id,
            type: 'GET',
            success: function(data) {
                $('#subcategoryModalLabel').text('Edit Subcategory');
                $('#subcategoryId').val(data.id);
                $('#subcategoryMethod').val('PUT');
                $('#subcategoryCategoryId').val(data.category_id);
                $('#subcategoryName').val(data.name);
                $('#subcategoryDescription').val(data.description);
                $('#subcategoryStatus').val(data.is_active ? '1' : '0');
                
                if (data.image) {
                    $('#subcategory-image-preview').html(`
                        <div class="position-relative">
                            <img src="${data.image.url}" class="img-fluid rounded" alt="${data.name}" style="max-height: 200px; object-fit: contain;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle" onclick="removeSubcategoryImage()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `);
                    $('#subcategoryImageId').val(data.image.id);
                } else {
                    $('#subcategory-image-preview').html(`
                        <div class="upload-area" id="subcategory-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                            <div>
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="openMediaLibrary('subcategory')">
                                    <i class="fas fa-folder-open me-1"></i> Select from Media Library
                                </button>
                            </div>
                        </div>
                    `);
                    $('#subcategoryImageId').val('');
                }
                
                $('#subcategoryModal').modal('show');
            },
            error: function() {
                alert('Error loading subcategory data.');
            }
        });
    }
    
    // Save subcategory (create or update)
    function saveSubCategory() {
        const id = $('#subcategoryId').val();
        const url = id ? baseUrl + '/vendor/subcategories/' + id : baseUrl + '/vendor/subcategories';
        
        const formData = $('#subcategoryForm').serialize();
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#subcategoryModal').modal('hide');
                    loadSubCategories(currentCategoryId, 1);
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = '';
                    for (let field in errors) {
                        errorMessages += errors[field].join(', ') + '\n';
                    }
                    alert('Validation errors:\n' + errorMessages);
                } else {
                    alert('Error saving subcategory.');
                }
            }
        });
    }
    
    // Delete subcategory
    function deleteSubCategory(id) {
        if (confirm('Are you sure you want to delete this subcategory?')) {
            $.ajax({
                url: baseUrl + '/vendor/subcategories/' + id,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'DELETE'
                },
                success: function(response) {
                    if (response.success) {
                        loadSubCategories(currentCategoryId, 1);
                    }
                },
                error: function() {
                    alert('Error deleting subcategory.');
                }
            });
        }
    }
    
    // Remove subcategory image
    function removeSubcategoryImage() {
        $('#subcategoryImageId').val('');
        $('#subcategory-image-preview').html(`
            <div class="upload-area" id="subcategory-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <div>
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="openMediaLibrary('subcategory')">
                        <i class="fas fa-folder-open me-1"></i> Select from Media Library
                    </button>
                </div>
            </div>
        `);
    }
    
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
                        html += `
                            <div class="col-md-3 col-sm-4 col-6 mb-3">
                                <div class="media-item card h-100" onclick="selectMedia(${media.id}, '${media.url}', '${media.name || media.file_name}')">
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
        if (currentMediaTarget === 'category') {
            $('#categoryImageId').val(id);
            $('#category-image-preview').html(`
                <div class="position-relative">
                    <img src="${url}" class="img-fluid rounded" alt="${name}" style="max-height: 200px; object-fit: contain;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle" onclick="removeCategoryImage()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
        } else if (currentMediaTarget === 'subcategory') {
            $('#subcategoryImageId').val(id);
            $('#subcategory-image-preview').html(`
                <div class="position-relative">
                    <img src="${url}" class="img-fluid rounded" alt="${name}" style="max-height: 200px; object-fit: contain;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle" onclick="removeSubcategoryImage()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
        }
        
        $('#mediaLibraryModal').modal('hide');
    }
    
    // Upload media
    function uploadMedia() {
        const formData = new FormData($('#mediaUploadForm')[0]);
        
        // Show loading state
        $('#media-upload-content').html(`
            <div class="spinner-border text-primary" role="status">
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
                if (response.success) {
                    // Reset upload area
                    $('#media-upload-content').html(`
                        <i class="fas fa-cloud-upload-alt text-muted mb-2" style="font-size: 24px;"></i>
                        <p class="text-muted mb-0 small">Drag & drop file here or click to upload</p>
                    `);
                    $('#mediaFile').val('');
                    
                    // Reload media library
                    loadMedia(1);
                    
                    // Auto-select the uploaded media
                    if (response.media) {
                        selectMedia(response.media.id, response.media.url, response.media.name || response.media.file_name);
                    }
                }
            },
            error: function(xhr) {
                // Reset upload area
                $('#media-upload-content').html(`
                    <i class="fas fa-cloud-upload-alt text-muted mb-2" style="font-size: 24px;"></i>
                    <p class="text-muted mb-0 small">Drag & drop file here or click to upload</p>
                `);
                $('#mediaFile').val('');
                
                let errorMessage = 'Error uploading file';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
                alert(errorMessage);
            }
        });
    }
    
    // Upload and select image directly (for drag and drop)
    function uploadAndSelectImage(file, target) {
        currentMediaTarget = target;
        
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        // Show loading state
        const previewId = target === 'category' ? '#category-image-preview' : '#subcategory-image-preview';
        $(previewId).html(`
            <div class="d-flex align-items-center justify-content-center" style="min-height: 200px;">
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
                // Reset preview area
                const resetHtml = `
                    <div class="upload-area" id="${target}-image-upload-area" style="min-height: 200px; border: 2px dashed #ccc; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                        <div>
                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-2">Drag & drop an image here or click to select</p>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="openMediaLibrary('${target}')">
                                <i class="fas fa-folder-open me-1"></i> Select from Media Library
                            </button>
                        </div>
                    </div>
                `;
                $(previewId).html(resetHtml);
                
                let errorMessage = 'Error uploading file';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                alert(errorMessage);
            }
        });
    }
</script>
@endsection
