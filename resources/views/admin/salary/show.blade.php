@extends('admin.layouts.app')

@section('title', 'Salary Details - ' . $user->name)

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Salary Details'])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- User Info Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <img src="{{ $user->avatar_url }}" class="rounded-circle" width="80" height="80" alt="{{ $user->name }}">
                            </div>
                            <div class="col">
                                <h4 class="mb-1">{{ $user->name }}</h4>
                                <p class="text-muted mb-1">{{ $user->email }}</p>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill">
                                    {{ ucfirst(str_replace('_', ' ', $user->user_role)) }}
                                </span>
                            </div>
                            <div class="col-auto">
                                @if(auth()->user()->hasPermission('update_salary') || auth()->user()->hasPermission('create_salary') || auth()->user()->isSuperAdmin())
                                <a href="{{ route('admin.salary.create', ['user_id' => $user->id]) }}" class="btn btn-theme rounded-pill px-4">
                                    <i class="fas fa-edit me-2"></i> Update Salary
                                </a>
                                @endif
                                <a href="{{ route('admin.salary.index') }}" class="btn btn-outline-secondary rounded-pill px-4 ms-2">
                                    <i class="fas fa-arrow-left me-2"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Current Salary -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-money-bill-wave me-2 text-success"></i>Current Salary
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($activeSalary)
                                    <div class="row text-center mb-4">
                                        <div class="col-4">
                                            <div class="border rounded p-3">
                                                <h3 class="text-success mb-0">₹{{ number_format($activeSalary->base_salary, 2) }}</h3>
                                                <small class="text-muted">Monthly Salary</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border rounded p-3">
                                                <h3 class="text-primary mb-0">₹{{ number_format($activeSalary->daily_rate, 2) }}</h3>
                                                <small class="text-muted">Daily Rate</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="border rounded p-3">
                                                <h3 class="text-warning mb-0">₹{{ number_format($activeSalary->half_day_rate, 2) }}</h3>
                                                <small class="text-muted">Half Day Rate</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="text-muted">Working Days/Month:</td>
                                            <td class="fw-medium">{{ $activeSalary->working_days_per_month }} days</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Effective From:</td>
                                            <td class="fw-medium">{{ $activeSalary->effective_from->format('d M Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Status:</td>
                                            <td><span class="badge bg-success rounded-pill">Active</span></td>
                                        </tr>
                                        @if($activeSalary->notes)
                                        <tr>
                                            <td class="text-muted">Notes:</td>
                                            <td>{{ $activeSalary->notes }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                                        <h5>No Active Salary</h5>
                                        <p class="text-muted">Salary has not been set for this staff member.</p>
                                        @if(auth()->user()->hasPermission('create_salary') || auth()->user()->isSuperAdmin())
                                        <a href="{{ route('admin.salary.create', ['user_id' => $user->id]) }}" class="btn btn-theme rounded-pill px-4">
                                            <i class="fas fa-plus me-2"></i> Set Salary
                                        </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Salary History -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-history me-2 text-info"></i>Salary History
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($salaryHistory->count() > 0)
                                    <div class="timeline">
                                        @foreach($salaryHistory as $salary)
                                        <div class="border-start border-3 {{ $salary->is_active ? 'border-success' : 'border-secondary' }} ps-3 pb-4 position-relative">
                                            <div class="position-absolute bg-white" style="left: -8px; top: 0;">
                                                <i class="fas fa-circle {{ $salary->is_active ? 'text-success' : 'text-secondary' }}" style="font-size: 12px;"></i>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">₹{{ number_format($salary->base_salary, 2) }}/month</h6>
                                                    <small class="text-muted">
                                                        {{ $salary->effective_from->format('d M Y') }}
                                                        @if($salary->effective_to)
                                                            - {{ $salary->effective_to->format('d M Y') }}
                                                        @else
                                                            - Present
                                                        @endif
                                                    </small>
                                                </div>
                                                @if($salary->is_active)
                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Current</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted mt-1">
                                                Daily: ₹{{ number_format($salary->daily_rate, 2) }} | 
                                                Half Day: ₹{{ number_format($salary->half_day_rate, 2) }}
                                            </div>
                                            @if($salary->notes)
                                                <div class="small text-muted mt-1">
                                                    <i class="fas fa-sticky-note me-1"></i> {{ $salary->notes }}
                                                </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No salary history available</p>
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
