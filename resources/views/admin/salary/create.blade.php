@extends('admin.layouts.app')

@section('title', 'Set/Update Salary')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Set/Update Salary'])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="card-title mb-0 fw-bold">
                                            <i class="fas fa-money-bill-wave me-2 text-theme"></i>
                                            {{ $user ? 'Update Salary for ' . $user->name : 'Set New Salary' }}
                                        </h4>
                                        <p class="text-muted mb-0 mt-1">Configure salary rates and working days</p>
                                    </div>
                                    <a href="{{ route('admin.salary.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                        <i class="fas fa-arrow-left me-2"></i> Back
                                    </a>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                
                                @if(!(auth()->user()->hasPermission('create_salary') || auth()->user()->hasPermission('update_salary') || auth()->user()->isSuperAdmin()))
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>You don't have permission to set or update salary.
                                    </div>
                                @else
                                <form method="POST" action="{{ route('admin.salary.store') }}">
                                    @csrf
                                    
                                    <div class="mb-4">
                                        <label class="form-label fw-medium">Select Staff Member <span class="text-danger">*</span></label>
                                        <select class="form-select" name="user_id" id="userSelect" required onchange="loadSalaryHistory(this.value)">
                                            <option value="">-- Select Staff Member --</option>
                                            @foreach($staffUsers as $staffUser)
                                                <option value="{{ $staffUser->id }}" {{ $user && $user->id == $staffUser->id ? 'selected' : '' }}>
                                                    {{ $staffUser->name }} ({{ ucfirst(str_replace('_', ' ', $staffUser->user_role)) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-medium">Base Salary (Monthly) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" class="form-control" name="base_salary" id="baseSalary" 
                                                       step="0.01" min="0" required placeholder="Enter monthly salary"
                                                       onchange="calculateRates()">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-medium">Working Days per Month <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="working_days_per_month" id="workingDays" 
                                                   min="1" max="31" value="26" required onchange="calculateRates()">
                                            <small class="text-muted">Standard is 26 days (excluding Sundays)</small>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-medium">Daily Rate (Auto-calculated)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="text" class="form-control bg-light" id="dailyRateDisplay" readonly>
                                            </div>
                                            <small class="text-muted">Base Salary ÷ Working Days</small>
                                        </div>
                                        
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-medium">Half Day Rate (Auto-calculated)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="text" class="form-control bg-light" id="halfDayRateDisplay" readonly>
                                            </div>
                                            <small class="text-muted">Daily Rate ÷ 2</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label fw-medium">Effective From <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="effective_from" required 
                                               value="{{ old('effective_from', date('Y-m-d')) }}">
                                        <small class="text-muted">The date from which this salary will be applicable. Previous salary will be marked as inactive.</small>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label fw-medium">Notes</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Optional notes about this salary change...">{{ old('notes') }}</textarea>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Note:</strong> When you update the salary, the new rates will apply from the effective date. 
                                        Salary calculations for days before the effective date will use the previous rates, and days after will use the new rates.
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-theme rounded-pill px-5">
                                            <i class="fas fa-save me-2"></i> Save Salary
                                        </button>
                                    </div>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Salary History -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-history me-2 text-theme"></i>Salary History
                                </h5>
                            </div>
                            <div class="card-body" id="salaryHistoryContainer">
                                @if($salaryHistory->count() > 0)
                                    @foreach($salaryHistory as $salary)
                                    <div class="border rounded p-3 mb-3 {{ $salary->is_active ? 'border-success' : '' }}" style="{{ $salary->is_active ? 'background-color: #d1e7dd;' : '' }}">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="fw-bold text-success">₹{{ number_format($salary->base_salary, 2) }}</span>
                                                <span class="text-muted">/month</span>
                                            </div>
                                            @if($salary->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted">
                                            <div><i class="fas fa-calendar me-1"></i> From: {{ $salary->effective_from->format('d M Y') }}</div>
                                            @if($salary->effective_to)
                                                <div><i class="fas fa-calendar-times me-1"></i> To: {{ $salary->effective_to->format('d M Y') }}</div>
                                            @endif
                                            <div><i class="fas fa-calculator me-1"></i> Daily: ₹{{ number_format($salary->daily_rate, 2) }}</div>
                                        </div>
                                        @if($salary->notes)
                                            <div class="small text-muted mt-2">
                                                <i class="fas fa-sticky-note me-1"></i> {{ $salary->notes }}
                                            </div>
                                        @endif
                                    </div>
                                    @endforeach
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-history fa-2x mb-2"></i>
                                        <p class="mb-0">No salary history</p>
                                        <small>Select a staff member to view history</small>
                                    </div>
                                @endif
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
    function calculateRates() {
        const baseSalary = parseFloat(document.getElementById('baseSalary').value) || 0;
        const workingDays = parseInt(document.getElementById('workingDays').value) || 26;
        
        const dailyRate = baseSalary / workingDays;
        const halfDayRate = dailyRate / 2;
        
        document.getElementById('dailyRateDisplay').value = dailyRate.toFixed(2);
        document.getElementById('halfDayRateDisplay').value = halfDayRate.toFixed(2);
    }
    
    function loadSalaryHistory(userId) {
        if (userId) {
            window.location.href = `{{ route('admin.salary.create') }}?user_id=${userId}`;
        }
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        calculateRates();
    });
</script>
@endsection
