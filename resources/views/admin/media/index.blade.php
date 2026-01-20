@extends('admin.layouts.app')

@section('title', 'Media Library')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Media Library'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                            <div class="mb-2 mb-md-0">
                                <h4 class="card-title mb-0 h5 h4-md">All Media</h4>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-md-normal btn-primary" id="upload-media-btn">
                                    <i class="fas fa-upload me-1"></i><span class="d-none d-sm-inline">Upload Media</span><span class="d-sm-none">Upload</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-md-normal btn-outline-primary" id="refresh-media-btn">
                                    <i class="fas fa-sync-alt me-1"></i><span class="d-none d-sm-inline">Refresh</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="media-search" placeholder="Search media...">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <select class="form-select form-select-sm" id="media-type-filter">
                                    <option value="all">All Types</option>
                                    <option value="images">Images</option>
                                    <option value="videos">Videos</option>
                                    <option value="documents">Documents</option>
                                </select>
                            </div>
                        </div>

                        <div id="media-library-items" class="row g-3">
                            <!-- Media items will be loaded here via AJAX -->
                            <div class="col-12 text-center py-5" id="media-loading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div class="col-12 text-center py-5 d-none" id="no-media-message">
                                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                <h5 class="mb-2">No media found</h5>
                                <p class="text-muted mb-3">Upload your first media file to get started</p>
                                <div class="upload-area" id="empty-state-upload">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p class="mb-0">Drag & drop files here or click to upload</p>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <button type="button" class="btn btn-outline-primary d-none" id="load-more-btn">
                                    <i class="fas fa-sync-alt me-1"></i> Load More
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>

<!-- Hidden form for media upload -->
<form id="mediaUploadForm" class="d-none">
    @csrf
    <input type="file" id="mediaFile" name="file" multiple>
    <input type="text" id="mediaName" name="name">
</form>

<!-- Media Preview Modal -->
<div class="modal fade" id="mediaPreviewModal" tabindex="-1" aria-labelledby="mediaPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaPreviewModalLabel">Media Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="media-preview-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="select-media-btn">Select Media</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Add event listener to refresh button
    const refreshBtn = document.getElementById('refresh-media-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            // Clear search input field
            const searchInput = document.getElementById('media-search');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // Reset filter dropdown to default "All Types" option
            const filterSelect = document.getElementById('media-type-filter');
            if (filterSelect) {
                filterSelect.value = 'all';
            }
            
            // Load media library with cleared search and default filter
            loadMediaLibrary(1, '', 'all');
        });
    }
    
    // Add event listener to upload button
    const uploadBtn = document.getElementById('upload-media-btn');
    if (uploadBtn) {
        uploadBtn.addEventListener('click', function() {
            // Create a hidden file input that accepts multiple files
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/*,video/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,text/csv';
            fileInput.multiple = true;
            fileInput.style.display = 'none';
            
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    // Handle multiple file uploads
                    for (let i = 0; i < this.files.length; i++) {
                        handleFileUpload(this.files[i]);
                    }
                }
                // Remove the file input after use
                document.body.removeChild(this);
            });
            
            document.body.appendChild(fileInput);
            fileInput.click();
        });
    }
    
    // Search media with debounce
    let searchTimeout;
    const searchInput = document.getElementById('media-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const currentSearch = this.value;
            
            searchTimeout = setTimeout(function() {
                // Get current filter value
                const filterSelect = document.getElementById('media-type-filter');
                const currentFilter = filterSelect ? filterSelect.value : 'all';
                loadMediaLibrary(1, currentSearch, currentFilter);
            }, 300); // 300ms debounce
        });
    }
    
    // Filter media
    const filterSelect = document.getElementById('media-type-filter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            const currentFilter = this.value;
            // Get current search value
            const searchInput = document.getElementById('media-search');
            const currentSearch = searchInput ? searchInput.value : '';
            loadMediaLibrary(1, currentSearch, currentFilter);
        });
    }
    
    // Upload first media button (for empty state)
    const emptyStateUpload = document.getElementById('empty-state-upload');
    if (emptyStateUpload) {
        emptyStateUpload.addEventListener('click', function() {
            // Create a hidden file input that accepts multiple files
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/*,video/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,text/csv';
            fileInput.multiple = true;
            fileInput.style.display = 'none';
            
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    // Handle multiple file uploads
                    for (let i = 0; i < this.files.length; i++) {
                        handleFileUpload(this.files[i]);
                    }
                }
                // Remove the file input after use
                document.body.removeChild(this);
            });
            
            document.body.appendChild(fileInput);
            fileInput.click();
        });
        
        // Add drag and drop functionality to empty state upload area
        emptyStateUpload.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
        
        emptyStateUpload.addEventListener('dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.add('drag-over');
        });
        
        emptyStateUpload.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (e.target === this || !this.contains(e.relatedTarget)) {
                this.classList.remove('drag-over');
            }
        });
        
        emptyStateUpload.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                for (let i = 0; i < files.length; i++) {
                    handleFileUpload(files[i]);
                }
            }
        });
    }
    
    // Drag and drop functionality for media library
    const mediaLibraryItems = document.getElementById('media-library-items');
    if (mediaLibraryItems) {
        mediaLibraryItems.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
        
        mediaLibraryItems.addEventListener('dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.add('drag-over');
        });
        
        mediaLibraryItems.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            // Only remove drag-over class if we're actually leaving the element
            if (e.target === this || !this.contains(e.relatedTarget)) {
                this.classList.remove('drag-over');
            }
        });
        
        mediaLibraryItems.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                // Handle multiple file uploads
                for (let i = 0; i < files.length; i++) {
                    handleFileUpload(files[i]);
                }
            }
        });
    }
    
    // Handle file upload
    function handleFileUpload(file) {
        // Debug: Log file details when function is called
        console.log('handleFileUpload called with file:', file);
        
        // Validate file type
        const validTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv', 'video/mpeg', 'video/ogg',
            'application/pdf', 'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain', 'text/csv'
        ];
        if (!validTypes.includes(file.type)) {
            alert('Please upload a valid file (JPEG, PNG, GIF, WEBP, MP4, MOV, AVI, WMV, MPEG, OGG, PDF, DOC, DOCX, XLSX, PPTX, TXT, CSV).');
            return;
        }
        
        // Validate file size - Increase limits to 25MB for all file types
        const maxSize = 25 * 1024 * 1024; // 25MB in bytes
        if (file.size > maxSize) {
            alert('File size must be less than 25MB.');
            return;
        }
        
        // Debug: Log file details before creating FormData
        console.log('File details before FormData:', file);
        console.log('File name:', file.name);
        console.log('File size:', file.size);
        console.log('File type:', file.type);
        
        const formData = new FormData();
        formData.append('file', file); // This is the correct way to append a file
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        // Debug: Log FormData contents
        console.log('FormData created with file:', formData);
        
        // Debug: Log all FormData entries
        for (let pair of formData.entries()) {
            console.log(pair[0] + ', ' + pair[1]);
        }
        
        // Show upload indicator only during actual upload
        const uploadIndicator = document.createElement('div');
        uploadIndicator.className = 'col-md-3 mb-4';
        uploadIndicator.innerHTML = `
            <div class="card border-0 shadow-sm h-100">
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Uploading...</span>
                    </div>
                </div>
            </div>
        `;
        
        // Get media library items container
        const mediaLibraryContainer = document.getElementById('media-library-items');
        if (mediaLibraryContainer) {
            mediaLibraryContainer.insertBefore(uploadIndicator, mediaLibraryContainer.firstChild);
        }
        
        // Send AJAX request to upload media
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/admin/media', true);
        
        xhr.onload = function() {
            // Remove upload indicator
            if (uploadIndicator.parentNode) {
                uploadIndicator.parentNode.removeChild(uploadIndicator);
            }
            
            if (xhr.status === 200) {
                const data = JSON.parse(xhr.responseText);
                
                if (data.success) {
                    // Add the new item to the top of the grid without full refresh
                    if (data.media) {
                        const newItem = document.createElement('div');
                        newItem.className = 'col-md-3 mb-4';
                        newItem.innerHTML = `
                            <div class="card border-0 shadow-sm media-item position-relative h-100" data-id="${data.media.id}">
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                    <img src="${data.media.url || ''}" alt="${data.media.name || 'Media item'}" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-image fa-2x text-muted\\'></i>'">
                                </div>
                                <div class="selection-indicator">
                                    <i class="fas fa-check"></i>
                                </div>
                                <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-media-btn" data-id="${data.media.id}" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                        
                        // Add click event for selection
                        const mediaItem = newItem.querySelector('.media-item');
                        if (mediaItem) {
                            mediaItem.addEventListener('click', function(e) {
                                // Prevent click when clicking on remove button
                                if (e.target.classList.contains('remove-media-btn') || e.target.closest('.remove-media-btn')) {
                                    return;
                                }
                                
                                // For media library, allow multiple selections
                                if (this.classList.contains('border-primary')) {
                                    // Already selected, so deselect
                                    this.classList.remove('border-primary');
                                } else {
                                    // Not selected, so select
                                    this.classList.add('border-primary');
                                }
                            });
                        }
                        
                        // Add click event for remove button
                        const removeBtn = newItem.querySelector('.remove-media-btn');
                        if (removeBtn) {
                            removeBtn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                const mediaId = this.getAttribute('data-id');
                                if (mediaLibraryContainer) {
                                    removeMedia(mediaId, newItem);
                                }
                            });
                        }
                        
                        if (mediaLibraryContainer) {
                            mediaLibraryContainer.insertBefore(newItem, mediaLibraryContainer.firstChild);
                        }
                        
                        // If this was the first item, hide the no-media message
                        const noMediaMessage = document.getElementById('no-media-message');
                        if (noMediaMessage) {
                            noMediaMessage.classList.add('d-none');
                        }
                    }
                } else {
                    // Upload failed
                    // Show detailed error message to user
                    let errorMessage = 'Upload failed';
                    if (data.errors) {
                        // Handle validation errors
                        errorMessage += ': ' + Object.values(data.errors).flat().join(', ');
                    } else if (data.error) {
                        errorMessage += ': ' + data.error;
                    } else {
                        errorMessage += ': Unknown error occurred';
                    }
                    alert(errorMessage);
                }
            } else {
                // Upload failed
                // Show detailed error message to user
                let errorMessage = 'Upload failed';
                
                try {
                    const errorData = JSON.parse(xhr.responseText);
                    // Check if we have a responseJSON with validation errors
                    if (errorData && errorData.errors) {
                        // Handle validation errors
                        errorMessage += ': ' + Object.values(errorData.errors).flat().join(', ');
                    } else if (errorData && errorData.message) {
                        // Handle Laravel error messages
                        errorMessage += ': ' + errorData.message;
                    } else {
                        errorMessage += ': HTTP ' + xhr.status;
                    }
                } catch (e) {
                    errorMessage += ': HTTP ' + xhr.status;
                }
                
                alert(errorMessage);
            }
        };
        
        xhr.onerror = function() {
            // Remove upload indicator
            if (uploadIndicator.parentNode) {
                uploadIndicator.parentNode.removeChild(uploadIndicator);
            }
            
            alert('Upload failed due to network error');
        };
        
        xhr.send(formData);
    }
    
    // Function to remove media
    function removeMedia(mediaId, element) {
        
        const xhr = new XMLHttpRequest();
        xhr.open('DELETE', '/admin/media/' + mediaId, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        xhr.setRequestHeader('Content-Type', 'application/json');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                    // Remove the element from the DOM
                    element.style.transition = 'opacity 0.3s';
                    element.style.opacity = '0';
                    setTimeout(function() {
                        if (element.parentNode) {
                            element.parentNode.removeChild(element);
                        }
                        
                        // If no media items left, show empty message
                        const mediaItems = document.querySelectorAll('.col-md-3.mb-4');
                        if (mediaItems.length === 0) {
                            const noMediaMessage = document.getElementById('no-media-message');
                            if (noMediaMessage) {
                                noMediaMessage.classList.remove('d-none');
                            }
                        }
                    }, 300);
                }
            } else {
                alert('Error deleting media.');
            }
        };
        
        xhr.onerror = function() {
            alert('Network error while deleting media.');
        };
        
        xhr.send();
    }
    
    // Load more button
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const currentPage = parseInt(this.getAttribute('data-page') || '1');
            const nextPage = currentPage + 1;
            // Get current search and filter values
            const searchInput = document.getElementById('media-search');
            const filterSelect = document.getElementById('media-type-filter');
            const currentSearch = searchInput ? searchInput.value : '';
            const currentFilter = filterSelect ? filterSelect.value : 'all';
            loadMediaLibrary(nextPage, currentSearch, currentFilter);
        });
    }
    
    // Load media library function
    function loadMediaLibrary(page = 1, search = '', filter = 'all') {
        // Show loading indicator
        const mediaLoading = document.getElementById('media-loading');
        const noMediaMessage = document.getElementById('no-media-message');
        const loadMoreBtn = document.getElementById('load-more-btn');
        
        if (mediaLoading) {
            mediaLoading.classList.remove('d-none');
        }
        if (noMediaMessage) {
            noMediaMessage.classList.add('d-none');
        }
        if (loadMoreBtn) {
            loadMoreBtn.classList.add('d-none');
        }
        
        // Build URL with parameters
        let url = '/admin/media/list?page=' + page;
        if (search) {
            url += '&search=' + encodeURIComponent(search);
        }
        if (filter && filter !== 'all') {
            url += '&type=' + filter;
        }
        
        console.log('Loading media with URL:', url); // Debug log
        
        // Make AJAX request to fetch media items
        const xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        
        xhr.onload = function() {
            if (mediaLoading) {
                mediaLoading.classList.add('d-none');
            }
            
            if (xhr.status === 200) {
                const data = JSON.parse(xhr.responseText);
                console.log('Media data received:', data); // Debug log
                
                if (data.data && data.data.length > 0) {
                    renderMediaItems(data.data, page === 1);
                    
                    // Check if there are more pages
                    const hasMorePages = data.next_page_url !== null;
                    
                    // Show load more button if there are more pages
                    if (hasMorePages && loadMoreBtn) {
                        loadMoreBtn.classList.remove('d-none');
                        loadMoreBtn.setAttribute('data-page', page);
                    }
                } else if (page === 1) {
                    // No media items found, show the no-media-message
                    if (noMediaMessage) {
                        noMediaMessage.classList.remove('d-none');
                    }
                    // Clear the container if it's the first page
                    const container = document.getElementById('media-library-items');
                    if (container) {
                        // Keep the loading and no-media-message elements
                        const loadingElement = document.getElementById('media-loading');
                        const noMediaElement = document.getElementById('no-media-message');
                        
                        // Remove all other elements
                        const elementsToRemove = [];
                        container.childNodes.forEach(node => {
                            if (node !== loadingElement && node !== noMediaElement) {
                                elementsToRemove.push(node);
                            }
                        });
                        
                        elementsToRemove.forEach(element => {
                            container.removeChild(element);
                        });
                    }
                }
            } else {
                console.error('Error loading media:', xhr.status); // Debug log
                // Error occurred, show the no-media-message
                if (noMediaMessage) {
                    noMediaMessage.classList.remove('d-none');
                }
            }
        };
        
        xhr.onerror = function() {
            if (mediaLoading) {
                mediaLoading.classList.add('d-none');
            }
            if (noMediaMessage) {
                noMediaMessage.classList.remove('d-none');
            }
            console.error('Network error loading media'); // Debug log
        };
        
        xhr.send();
    }
    
    // Render media items function with better fallbacks
    function renderMediaItems(mediaItems, clearContainer = false) {
        const container = document.getElementById('media-library-items');
        
        // If this is the first page, clear the container
        if (clearContainer && container) {
            // Keep the loading and no-media-message elements
            const loadingElement = document.getElementById('media-loading');
            const noMediaElement = document.getElementById('no-media-message');
            
            // Remove all other elements
            const elementsToRemove = [];
            container.childNodes.forEach(node => {
                if (node !== loadingElement && node !== noMediaElement) {
                    elementsToRemove.push(node);
                }
            });
            
            elementsToRemove.forEach(element => {
                container.removeChild(element);
            });
        }
        
        if (!container) return;
        
        mediaItems.forEach(function(item) {
            // Ensure item has required properties
            if (!item || !item.id) {
                return;
            }
            
            const col = document.createElement('div');
            col.className = 'col-md-3 mb-4';
            
            // Determine the appropriate preview based on file type
            let previewHtml = '';
            
            // Check if MIME type exists and handle different file types
            if (item.mime_type) {
                if (item.mime_type.startsWith('image/')) {
                    // Image preview
                    previewHtml = `<img src="${item.url || ''}" alt="${item.name || 'Media item'}" onerror="this.parentElement.innerHTML='<i class=\\'fas fa-image fa-2x text-muted\\'></i>'">`;
                } else if (item.mime_type === 'application/pdf') {
                    // PDF preview
                    previewHtml = '<i class="fas fa-file-pdf fa-3x text-danger"></i>';
                } else if (item.mime_type === 'application/msword' || 
                           item.mime_type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                    // DOC/DOCX preview
                    previewHtml = '<i class="fas fa-file-word fa-3x text-primary"></i>';
                } else if (item.mime_type === 'application/vnd.ms-excel' || 
                           item.mime_type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                    // XLS/XLSX preview
                    previewHtml = '<i class="fas fa-file-excel fa-3x text-success"></i>';
                } else if (item.mime_type === 'application/vnd.ms-powerpoint' || 
                           item.mime_type === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') {
                    // PPT/PPTX preview
                    previewHtml = '<i class="fas fa-file-powerpoint fa-3x text-warning"></i>';
                } else if (item.mime_type.startsWith('text/')) {
                    // Text file preview
                    previewHtml = '<i class="fas fa-file-alt fa-3x text-secondary"></i>';
                } else {
                    // Generic file preview
                    previewHtml = '<i class="fas fa-file fa-3x text-secondary"></i>';
                }
            } else {
                // Fallback if MIME type is missing
                previewHtml = '<i class="fas fa-file fa-3x text-secondary"></i>';
            }
            
            col.innerHTML = `
                <div class="card border-0 shadow-sm media-item position-relative h-100" data-id="${item.id}" data-url="${item.url || ''}" data-name="${item.name || 'Media item'}" data-mime="${item.mime_type || ''}">
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                        ${previewHtml}
                    </div>
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-truncate small" title="${item.name || 'Media item'}">${item.name || 'Media item'}</div>
                            <a href="${item.url || '#'}" target="_blank" class="btn btn-sm btn-outline-primary preview-file-btn" title="Preview">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="selection-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-media-btn" data-id="${item.id}" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            // Add click event for selection
            const mediaItem = col.querySelector('.media-item');
            if (mediaItem) {
                mediaItem.addEventListener('click', function(e) {
                    // Prevent click when clicking on remove button
                    if (e.target.classList.contains('remove-media-btn') || e.target.closest('.remove-media-btn')) {
                        return;
                    }
                    
                    // For media library, allow multiple selections
                    if (this.classList.contains('border-primary')) {
                        // Already selected, so deselect
                        this.classList.remove('border-primary');
                    } else {
                        // Not selected, so select
                        this.classList.add('border-primary');
                    }
                });
            }
            
            // Add click event for remove button
            const removeBtn = col.querySelector('.remove-media-btn');
            if (removeBtn) {
                removeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const mediaId = this.getAttribute('data-id');
                    removeMedia(mediaId, col);
                });
            }
            
            // Add click event for preview button
            const previewBtn = col.querySelector('.preview-file-btn');
            if (previewBtn) {
                previewBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // The link already has target="_blank" so it will open in a new tab
                    const url = this.getAttribute('href');
                    // if (url && url !== '#') {
                    //     window.open(url, '_blank');
                    // }
                });
            }
            
            container.appendChild(col);
        });
    }
    
    // Load initial media items
    loadMediaLibrary();
});
</script>
@endsection