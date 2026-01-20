@extends('admin.layouts.app')

@section('title', 'Send Notifications - ' . config('app.name', 'Laravel'))

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Send Notifications',
                'breadcrumbs' => [
                    'Notifications' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0 fw-bold">Send Push Notifications</h4>
                                    <p class="mb-0 text-muted">Send notifications to users or user groups</p>
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
                                
                                <ul class="nav nav-tabs mb-4" id="notificationTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active rounded-pill px-4" id="user-tab" data-bs-toggle="tab" data-bs-target="#user" type="button" role="tab" aria-controls="user" aria-selected="true">
                                            <i class="fas fa-user me-2"></i>Send to User
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link rounded-pill px-4" id="group-tab" data-bs-toggle="tab" data-bs-target="#group" type="button" role="tab" aria-controls="group" aria-selected="false">
                                            <i class="fas fa-users me-2"></i>Send to Group
                                        </button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="notificationTabContent">
                                    <!-- Send to User Tab -->
                                    <div class="tab-pane fade show active" id="user" role="tabpanel" aria-labelledby="user-tab">
                                        <form id="sendToUserForm">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="user_id" class="form-label fw-medium">Select User</label>
                                                <select class="form-select rounded-pill" id="user_id" name="user_id" required>
                                                    <option value="">Choose a user</option>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="user_title" class="form-label fw-medium">Title</label>
                                                <input type="text" class="form-control rounded-pill" id="user_title" name="title" placeholder="Notification title" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="user_body" class="form-label fw-medium">Message</label>
                                                <textarea class="form-control" id="user_body" name="body" rows="4" placeholder="Notification message" required></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="user_data" class="form-label fw-medium">Additional Data (JSON)</label>
                                                <textarea class="form-control" id="user_data" name="data" rows="3" placeholder='{"key": "value", "another_key": "another_value"}'></textarea>
                                                <div class="form-text">Optional JSON data to send with the notification</div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-end">
                                                <button type="submit" class="btn btn-theme rounded-pill px-4" id="sendToUserBtn">
                                                    <i class="fas fa-paper-plane me-2"></i>Send Notification
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- Send to Group Tab -->
                                    <div class="tab-pane fade" id="group" role="tabpanel" aria-labelledby="group-tab">
                                        <form id="sendToGroupForm">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="user_group_id" class="form-label fw-medium">Select User Group</label>
                                                <select class="form-select rounded-pill" id="user_group_id" name="user_group_id" required>
                                                    <option value="">Choose a user group</option>
                                                    @foreach($userGroups as $group)
                                                        <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->users->count() }} members)</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="group_title" class="form-label fw-medium">Title</label>
                                                <input type="text" class="form-control rounded-pill" id="group_title" name="title" placeholder="Notification title" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="group_body" class="form-label fw-medium">Message</label>
                                                <textarea class="form-control" id="group_body" name="body" rows="4" placeholder="Notification message" required></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="group_data" class="form-label fw-medium">Additional Data (JSON)</label>
                                                <textarea class="form-control" id="group_data" name="data" rows="3" placeholder='{"key": "value", "another_key": "another_value"}'></textarea>
                                                <div class="form-text">Optional JSON data to send with the notification</div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-end">
                                                <button type="submit" class="btn btn-theme rounded-pill px-4" id="sendToGroupBtn">
                                                    <i class="fas fa-paper-plane me-2"></i>Send Notification
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Results Section -->
                                <div class="mt-5" id="notificationResults" style="display: none;">
                                    <h5 class="fw-bold mb-3">Notification Results</h5>
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <div id="resultsContent"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('admin.layouts.footer')
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Handle send to user form submission
    $('#sendToUserForm').on('submit', function(e) {
        e.preventDefault();
        
        // Disable submit button and show loading
        const submitBtn = $('#sendToUserBtn');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Sending...');
        submitBtn.prop('disabled', true);
        
        // Hide previous results
        $('#notificationResults').hide();
        
        // Get form data
        const formData = {
            user_id: $('#user_id').val(),
            title: $('#user_title').val(),
            body: $('#user_body').val(),
            data: $('#user_data').val(),
            _token: $('input[name="_token"]').val()
        };
        
        // Validate JSON data if provided
        if (formData.data) {
            try {
                JSON.parse(formData.data);
            } catch (e) {
                alert('Invalid JSON in Additional Data field: ' + e.message);
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
                return;
            }
        }
        
        // Send AJAX request
        $.ajax({
            url: '{{ route("admin.firebase.notifications.user") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                displayResults(response, 'User Notification');
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while sending the notification.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                displayError(errorMessage);
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    // Handle send to group form submission
    $('#sendToGroupForm').on('submit', function(e) {
        e.preventDefault();
        
        // Disable submit button and show loading
        const submitBtn = $('#sendToGroupBtn');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Sending...');
        submitBtn.prop('disabled', true);
        
        // Hide previous results
        $('#notificationResults').hide();
        
        // Get form data
        const formData = {
            user_group_id: $('#user_group_id').val(),
            title: $('#group_title').val(),
            body: $('#group_body').val(),
            data: $('#group_data').val(),
            _token: $('input[name="_token"]').val()
        };
        
        // Validate JSON data if provided
        if (formData.data) {
            try {
                JSON.parse(formData.data);
            } catch (e) {
                alert('Invalid JSON in Additional Data field: ' + e.message);
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
                return;
            }
        }
        
        // Send AJAX request
        $.ajax({
            url: '{{ route("admin.firebase.notifications.group") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                displayResults(response, 'Group Notification');
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while sending the notification.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                displayError(errorMessage);
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    // Display results
    function displayResults(response, type) {
        let html = '';
        
        if (response.success) {
            html += `<div class="alert alert-success rounded-pill px-4 py-3">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>${type} sent successfully!</strong> ${response.message}
                     </div>`;
                     
            if (response.summary) {
                html += `<div class="row mt-3">
                            <div class="col-md-4">
                                <div class="card border-0 bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title mb-0">${response.summary.total_users}</h5>
                                        <p class="card-text mb-0">Total Users</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title mb-0">${response.summary.successful}</h5>
                                        <p class="card-text mb-0">Successful</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h5 class="card-title mb-0">${response.summary.failed}</h5>
                                        <p class="card-text mb-0">Failed</p>
                                    </div>
                                </div>
                            </div>
                        </div>`;
            }
            
            if (response.results && response.results.length > 0) {
                html += `<div class="mt-4">
                            <h6 class="fw-bold">Detailed Results:</h6>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User ID</th>
                                            <th>Status</th>
                                            <th>Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;
                
                response.results.forEach(function(result) {
                    const statusClass = result.success ? 'text-success' : 'text-danger';
                    const statusIcon = result.success ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>';
                    html += `<tr>
                                <td>${result.user_id}</td>
                                <td class="${statusClass}">${statusIcon} ${result.success ? 'Success' : 'Failed'}</td>
                                <td>${result.message}</td>
                             </tr>`;
                });
                
                html += `           </tbody>
                                </table>
                            </div>
                         </div>`;
            }
        } else {
            html += `<div class="alert alert-danger rounded-pill px-4 py-3">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Error:</strong> ${response.message}
                     </div>`;
        }
        
        $('#resultsContent').html(html);
        $('#notificationResults').show();
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#notificationResults').offset().top - 100
        }, 500);
    }
    
    // Display error
    function displayError(message) {
        const html = `<div class="alert alert-danger rounded-pill px-4 py-3">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Error:</strong> ${message}
                      </div>`;
                      
        $('#resultsContent').html(html);
        $('#notificationResults').show();
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#notificationResults').offset().top - 100
        }, 500);
    }
</script>
@endsection