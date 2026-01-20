<form id="editUserGroupForm" action="{{ route('admin.user-groups.update', $userGroup) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-12">
            <div class="mb-3">
                <label for="name" class="form-label fw-bold">Group Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control rounded-pill px-4 py-2 @error('name') is-invalid @enderror" 
                       id="name" name="name" value="{{ old('name', $userGroup->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label fw-bold">Description</label>
                <textarea class="form-control rounded-3 px-4 py-2 @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="3">{{ old('description', $userGroup->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="discount_percentage" class="form-label fw-bold">Discount Percentage <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" class="form-control rounded-start-pill px-4 py-2 @error('discount_percentage') is-invalid @enderror" 
                           id="discount_percentage" name="discount_percentage" 
                           value="{{ old('discount_percentage', $userGroup->discount_percentage) }}" 
                           min="0" max="100" step="0.01" required>
                    <span class="input-group-text rounded-end-pill">%</span>
                </div>
                @error('discount_percentage')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Select Users</label>
                <div class="card border rounded-3">
                    <div class="card-header bg-light py-2 px-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0 rounded-pill">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control border-0 rounded-pill" 
                                   id="user-search" placeholder="Search users...">
                        </div>
                    </div>
                    <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                        <div class="list-group list-group-flush" id="user-list">
                            @foreach($users as $user)
                                <div class="list-group-item border-0 px-3 py-2 user-item" data-user-name="{{ strtolower($user->name) }}">
                                    <div class="form-check">
                                        <input class="form-check-input user-checkbox" type="checkbox" 
                                               id="user_{{ $user->id }}" name="users[]" 
                                               value="{{ $user->id }}"
                                               {{ in_array($user->id, old('users', $selectedUsers)) ? 'checked' : '' }}>
                                        <label class="form-check-label d-flex align-items-center" for="user_{{ $user->id }}">
                                            <img src="{{ $user->avatar_url }}" 
                                                 class="rounded-circle me-3" width="30" height="30" alt="{{ $user->name }}">
                                            <div>
                                                <div class="fw-medium">
                                                    {{ $user->name }} 
                                                    <span class="text-muted">
                                                        @if($user->mobile_number)
                                                            ({{ $user->mobile_number }})
                                                        @else
                                                            (No phone number)
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="small">
                                                    @if($user->userGroups->count() > 0)
                                                        <span class="badge bg-info">Group: {{ $user->userGroups->first()->name }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">No group assigned</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Selected Users</label>
                <div class="card border rounded-3">
                    <div class="card-body p-3">
                        <div id="selected-users-container">
                            <div class="text-center py-5 {{ count(old('users', $selectedUsers)) > 0 ? 'd-none' : '' }}" id="no-selected-users">
                                <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No users selected</p>
                                <p class="small text-muted">Select users from the list above</p>
                            </div>
                            <div id="selected-users-list" class="{{ count(old('users', $selectedUsers)) > 0 ? '' : 'd-none' }}">
                                @foreach($users as $user)
                                    @if(in_array($user->id, old('users', $selectedUsers)))
                                        <div class="d-inline-block me-2 mb-2 selected-user" data-user-id="{{ $user->id }}">
                                            <div class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
                                                <img src="{{ $user->avatar_url }}" class="rounded-circle me-2" width="20" height="20" alt="{{ $user->name }}">
                                                {{ $user->name }}
                                                <button type="button" class="btn-close btn-close-white ms-2 remove-user-btn" data-user-id="{{ $user->id }}"></button>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i> Cancel
        </button>
        <button type="submit" class="btn btn-theme rounded-pill px-4 py-2">
            <i class="fas fa-save me-1"></i> Update User Group
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Handle user search
    $('#user-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        if (searchTerm === '') {
            $('.user-item').show();
        } else {
            $('.user-item').each(function() {
                const userName = $(this).data('user-name');
                if (userName.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });
    
    // Handle user selection
    $('.user-checkbox').on('change', function() {
        const userId = $(this).val();
        const userName = $(this).closest('.user-item').find('.fw-medium').text();
        const userAvatar = $(this).closest('.user-item').find('img').attr('src');
        
        if ($(this).is(':checked')) {
            // Add to selected users
            addUserToSelected(userId, userName, userAvatar);
        } else {
            // Remove from selected users
            removeUserFromSelected(userId);
        }
        
        updateSelectedUsersDisplay();
    });
    
    // Add user to selected users list
    function addUserToSelected(userId, userName, userAvatar) {
        // Check if user is already in the selected list
        if ($(`.selected-user[data-user-id="${userId}"]`).length > 0) {
            return;
        }
        
        const userHtml = `
            <div class="d-inline-block me-2 mb-2 selected-user" data-user-id="${userId}">
                <div class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2">
                    <img src="${userAvatar}" class="rounded-circle me-2" width="20" height="20" alt="${userName}">
                    ${userName}
                    <button type="button" class="btn-close btn-close-white ms-2 remove-user-btn" data-user-id="${userId}"></button>
                </div>
            </div>
        `;
        
        $('#selected-users-list').append(userHtml);
    }
    
    // Remove user from selected users list
    function removeUserFromSelected(userId) {
        $(`.selected-user[data-user-id="${userId}"]`).remove();
    }
    
    // Update selected users display
    function updateSelectedUsersDisplay() {
        if ($('#selected-users-list').children().length > 0) {
            $('#no-selected-users').addClass('d-none');
            $('#selected-users-list').removeClass('d-none');
        } else {
            $('#no-selected-users').removeClass('d-none');
            $('#selected-users-list').addClass('d-none');
        }
    }
    
    // Handle remove user button click
    $(document).on('click', '.remove-user-btn', function() {
        const userId = $(this).data('user-id');
        
        // Uncheck the checkbox
        $(`#user_${userId}`).prop('checked', false);
        
        // Remove from selected users
        removeUserFromSelected(userId);
        
        updateSelectedUsersDisplay();
    });
    
    // Handle form submission
    $('#editUserGroupForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const url = form.attr('action');
        const formData = form.serialize();
        
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            headers: {
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function(response) {
                // Close the modal
                $('#userGroupModal').modal('hide');
                
                // Show success message
                showAlert('success', 'User group updated successfully.');
                
                // Reload the page to reflect changes
                location.reload();
            },
            error: function(xhr) {
                // Handle validation errors
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessages = '';
                    
                    for (const field in errors) {
                        errorMessages += errors[field][0] + '<br>';
                    }
                    
                    showAlert('error', errorMessages);
                } else {
                    showAlert('error', 'An error occurred while updating the user group.');
                }
            }
        });
    });
    
    // Function to show alerts
    function showAlert(type, message) {
        let alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        let iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        let alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                <i class="fas ${iconClass} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Remove any existing alerts
        $('.alert').remove();
        
        // Add the new alert to the card body
        $('.card-body').prepend(alertHtml);
    }
});
</script>