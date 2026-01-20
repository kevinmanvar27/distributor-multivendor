@extends('admin.layouts.app')

@section('title', 'Edit Attribute')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Edit Attribute'])
            
            <div class="pt-4 pb-2 mb-3">
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Edit Attribute: {{ $attribute->name }}</h1>
                <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Attributes
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Attribute Details -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Attribute Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.attributes.update', $attribute) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Attribute Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $attribute->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', $attribute->sort_order) }}" min="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Status</label>
                                    <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
                                        <option value="1" {{ old('is_active', $attribute->is_active) == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('is_active', $attribute->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $attribute->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Attribute</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Attribute Values -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Attribute Values</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addValueModal">
                        <i class="fas fa-plus"></i> Add Value
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Value</th>
                                    <th>Color</th>
                                    <th>Sort</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="valuesTable">
                                @forelse($attribute->values as $value)
                                    <tr data-value-id="{{ $value->id }}">
                                        <td>{{ $value->value }}</td>
                                        <td>
                                            @if($value->color_code)
                                                <span class="d-inline-block" style="width: 20px; height: 20px; background-color: {{ $value->color_code }}; border: 1px solid #ddd;"></span>
                                                <code>{{ $value->color_code }}</code>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $value->sort_order }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger delete-value" data-value-id="{{ $value->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="noValuesRow">
                                        <td colspan="4" class="text-center text-muted">No values added yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Value Modal -->
<div class="modal fade" id="addValueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Attribute Value</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addValueForm">
                    <div class="mb-3">
                        <label for="value" class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="value" name="value" required>
                    </div>
                    <!-- <div class="mb-3">
                        <label for="color_code" class="form-label">Color Code (Optional)</label>
                        <input type="color" class="form-control form-control-color" id="color_code" name="color_code">
                        <small class="text-muted">Only for color attributes</small>
                    </div> -->
                    <div class="mb-3">
                        <label for="value_sort_order" class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="value_sort_order" name="sort_order" value="0" min="0">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveValueBtn">
                            <i class="fas fa-plus me-1"></i> Add Value
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
            </div>
        </main>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.getElementById('saveValueBtn').addEventListener('click', function() {
alert("sadasd");
});

document.addEventListener('DOMContentLoaded', function() {
    
    const attributeId = {{ $attribute->id }};
    const addValueModalElement = document.getElementById('addValueModal');
    const addValueModal = new bootstrap.Modal(addValueModalElement);
    const saveBtn = document.getElementById('saveValueBtn');
    const form = document.getElementById('addValueForm');
    
    
    // Check CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    
    // Helper function to show alerts
    function showAlert(message, type = 'success') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        const container = document.querySelector('.container-fluid');
        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
    
    // Validate form before submission
    function validateForm() {
        const valueInput = document.getElementById('value');
        if (!valueInput.value.trim()) {
            showAlert('Please enter a value', 'danger');
            return false;
        }
        return true;
    }
    
    // Function to handle form submission
    function handleFormSubmit(e) {
        
        if (e) {
            e.preventDefault(); // Prevent default form submission
        }
        
        
        if (!validateForm()) {
            return;
        }
        
        
        const formData = new FormData(form);
        
        // Disable button during request
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
        }
        
        const url = `/admin/attributes/${attributeId}/values`;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken.content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    // Handle validation errors
                    if (err.errors) {
                        const errorMessages = Object.values(err.errors).flat().join('<br>');
                        throw new Error(errorMessages);
                    }
                    throw new Error(err.message || 'Failed to add value');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Remove "no values" row if exists
                const noValuesRow = document.getElementById('noValuesRow');
                if (noValuesRow) {
                    noValuesRow.remove();
                }
                
                // Add new row
                const tbody = document.getElementById('valuesTable');
                const colorHtml = data.data.color_code 
                    ? `<span class="d-inline-block" style="width: 20px; height: 20px; background-color: ${data.data.color_code}; border: 1px solid #ddd;"></span> <code>${data.data.color_code}</code>`
                    : '<span class="text-muted">-</span>';
                    
                const row = `
                    <tr data-value-id="${data.data.id}">
                        <td>${data.data.value}</td>
                        <td>${colorHtml}</td>
                        <td>${data.data.sort_order}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger delete-value" data-value-id="${data.data.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
                
                // Reset form and close modal
                form.reset();
                addValueModal.hide();
                
                // Show success message
                showAlert('Attribute value added successfully!', 'success');
            } else {
                throw new Error(data.message || 'Failed to add value');
            }
        })
        .catch(error => {
            showAlert(error.message || 'Failed to add value. Please try again.', 'danger');
        })
        .finally(() => {
            // Re-enable button
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-plus me-1"></i> Add Value';
            }
        });
    }
    
    // Add event listeners for both button click and form submit
    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            handleFormSubmit(e);
        });
    } else {
        console.error('❌ Save button not found!');
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            handleFormSubmit(e);
        });
    } else {
        console.error('❌ Form not found!');
    }
    
    // Debug: Check when modal is shown
    addValueModalElement.addEventListener('shown.bs.modal', function() {
        const btnCheck = document.getElementById('saveValueBtn');
        const formCheck = document.getElementById('addValueForm');
    });
    
    // Delete value
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-value')) {
            const btn = e.target.closest('.delete-value');
            const valueId = btn.dataset.valueId;
            
            if (confirm('Are you sure you want to delete this value?')) {
                // Disable button during request
                btn.disabled = true;
                
                fetch(`/admin/attributes/${attributeId}/values/${valueId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || 'Failed to delete value');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(`tr[data-value-id="${valueId}"]`);
                        row.remove();
                        
                        // Add "no values" row if table is empty
                        const tbody = document.getElementById('valuesTable');
                        if (tbody.children.length === 0) {
                            tbody.innerHTML = '<tr id="noValuesRow"><td colspan="4" class="text-center text-muted">No values added yet</td></tr>';
                        }
                        
                        // Show success message
                        showAlert('Attribute value deleted successfully!', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to delete value');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert(error.message || 'Failed to delete value. Please try again.', 'danger');
                    btn.disabled = false;
                });
            }
        }
    });
});
</script>
@endsection
