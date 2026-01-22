@extends('admin.layouts.app')

@section('title', 'Referrals')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Referral Management',
                'breadcrumbs' => [
                    'Referrals' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-primary mb-2">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                                <small class="text-muted">Total Referrals</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-warning mb-2">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">{{ $stats['pending'] }}</h3>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-success mb-2">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">{{ $stats['completed'] }}</h3>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="text-info mb-2">
                                    <i class="fas fa-rupee-sign fa-2x"></i>
                                </div>
                                <h3 class="mb-0 fw-bold">₹{{ number_format($stats['total_rewards'] + $stats['total_referred_rewards'], 2) }}</h3>
                                <small class="text-muted">Total Rewards</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Referral Settings Card -->
                    <div class="col-12 col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-cog me-2 text-theme"></i>Referral Settings
                                </h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.referrals.settings') }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="referral_enabled" name="referral_enabled" 
                                                {{ ($settings->referral_enabled ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="referral_enabled">Enable Referral Program</label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="referral_reward_amount" class="form-label">Referrer Reward (₹)</label>
                                        <input type="number" class="form-control" id="referral_reward_amount" name="referral_reward_amount" 
                                            value="{{ $settings->referral_reward_amount ?? 100 }}" step="0.01" min="0">
                                        <small class="text-muted">Reward for the person who refers</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="referred_reward_amount" class="form-label">Referred User Reward (₹)</label>
                                        <input type="number" class="form-control" id="referred_reward_amount" name="referred_reward_amount" 
                                            value="{{ $settings->referred_reward_amount ?? 50 }}" step="0.01" min="0">
                                        <small class="text-muted">Reward for the new user who joins</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="referral_expiry_days" class="form-label">Expiry Days</label>
                                        <input type="number" class="form-control" id="referral_expiry_days" name="referral_expiry_days" 
                                            value="{{ $settings->referral_expiry_days ?? 30 }}" min="1" max="365">
                                        <small class="text-muted">Days until referral code expires</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="referral_min_order_amount" class="form-label">Min Order Amount (₹)</label>
                                        <input type="number" class="form-control" id="referral_min_order_amount" name="referral_min_order_amount" 
                                            value="{{ $settings->referral_min_order_amount ?? 500 }}" step="0.01" min="0">
                                        <small class="text-muted">Minimum order to complete referral</small>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-theme w-100 rounded-pill">
                                        <i class="fas fa-save me-2"></i>Save Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Referrals List -->
                    <div class="col-12 col-lg-8 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                                    <div class="mb-2 mb-md-0">
                                        <h5 class="card-title mb-0 fw-bold">Referrals</h5>
                                        <p class="mb-0 text-muted small">Manage all referrals</p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.referrals.export', request()->query()) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            <i class="fas fa-download me-1"></i>Export
                                        </a>
                                        <a href="{{ route('admin.referrals.create') }}" class="btn btn-sm btn-theme rounded-pill px-3">
                                            <i class="fas fa-plus me-1"></i>Add Referral
                                        </a>
                                    </div>
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

                                <!-- Filters -->
                                <form action="{{ route('admin.referrals.index') }}" method="GET" class="mb-4">
                                    <div class="row g-2">
                                        <div class="col-12 col-md-3">
                                            <input type="text" class="form-control form-control-sm" name="search" 
                                                placeholder="Search code, name, email..." value="{{ request('search') }}">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <select class="form-select form-select-sm" name="status">
                                                <option value="">All Status</option>
                                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <input type="date" class="form-control form-control-sm" name="date_from" 
                                                placeholder="From" value="{{ request('date_from') }}">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <input type="date" class="form-control form-control-sm" name="date_to" 
                                                placeholder="To" value="{{ request('date_to') }}">
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-sm btn-theme rounded-pill px-3">
                                                    <i class="fas fa-search me-1"></i>Filter
                                                </button>
                                                <a href="{{ route('admin.referrals.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                    <i class="fas fa-times me-1"></i>Clear
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Code</th>
                                                <th>Referrer</th>
                                                <th>Referred</th>
                                                <th>Rewards</th>
                                                <th>Status</th>
                                                <th>Expires</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($referrals as $referral)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-dark fs-6 font-monospace">{{ $referral->referral_code }}</span>
                                                </td>
                                                <td>
                                                    @if($referral->referrer)
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ $referral->referrer->avatar_url }}" alt="" class="rounded-circle me-2" width="32" height="32">
                                                            <div>
                                                                <div class="fw-medium">{{ $referral->referrer->name }}</div>
                                                                <small class="text-muted">{{ $referral->referrer->email }}</small>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($referral->referred)
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ $referral->referred->avatar_url }}" alt="" class="rounded-circle me-2" width="32" height="32">
                                                            <div>
                                                                <div class="fw-medium">{{ $referral->referred->name }}</div>
                                                                <small class="text-muted">{{ $referral->referred->email }}</small>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">Not yet used</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="small">
                                                        <span class="text-success">₹{{ number_format($referral->reward_amount, 2) }}</span>
                                                        @if($referral->reward_claimed)
                                                            <i class="fas fa-check-circle text-success ms-1" title="Claimed"></i>
                                                        @endif
                                                    </div>
                                                    <div class="small">
                                                        <span class="text-info">₹{{ number_format($referral->referred_reward_amount, 2) }}</span>
                                                        @if($referral->referred_reward_claimed)
                                                            <i class="fas fa-check-circle text-success ms-1" title="Claimed"></i>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $referral->getStatusBadgeClass() }} rounded-pill">
                                                        {{ ucfirst($referral->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($referral->expires_at)
                                                        <small class="{{ $referral->expires_at->isPast() ? 'text-danger' : 'text-muted' }}">
                                                            {{ $referral->expires_at->format('d M Y') }}
                                                        </small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('admin.referrals.show', $referral) }}" class="btn btn-outline-info rounded-start-pill px-3" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.referrals.edit', $referral) }}" class="btn btn-outline-primary px-3" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('admin.referrals.destroy', $referral) }}" method="POST" class="d-inline" 
                                                              onsubmit="return confirm('Are you sure you want to delete this referral?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger rounded-end-pill px-3" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="fas fa-user-friends fa-3x mb-3"></i>
                                                        <p class="mb-0">No referrals found</p>
                                                        <a href="{{ route('admin.referrals.create') }}" class="btn btn-theme btn-sm mt-3 rounded-pill">
                                                            <i class="fas fa-plus me-2"></i>Create your first referral
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                @if($referrals->hasPages())
                                    <div class="d-flex justify-content-center mt-4">
                                        {{ $referrals->withQueryString()->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
