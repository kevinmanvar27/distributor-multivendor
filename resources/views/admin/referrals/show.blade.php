@extends('admin.layouts.app')

@section('title', 'View Referral')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Referral Details',
                'breadcrumbs' => [
                    'Referrals' => route('admin.referrals.index'),
                    'Details' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-10">
                        <!-- Referral Code Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body text-center py-5">
                                <div class="mb-3">
                                    <span class="badge {{ $referral->getStatusBadgeClass() }} rounded-pill px-4 py-2 fs-6">
                                        {{ ucfirst($referral->status) }}
                                    </span>
                                </div>
                                <h2 class="display-4 font-monospace fw-bold text-dark mb-3">{{ $referral->referral_code }}</h2>
                                <p class="text-muted mb-0">Referral Code</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Referrer Info -->
                            <div class="col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white border-0 py-3">
                                        <h5 class="card-title mb-0 fw-bold">
                                            <i class="fas fa-user me-2 text-primary"></i>Referrer
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @if($referral->referrer)
                                            <div class="d-flex align-items-center mb-3">
                                                <img src="{{ $referral->referrer->avatar_url }}" alt="" class="rounded-circle me-3" width="64" height="64">
                                                <div>
                                                    <h5 class="mb-1">{{ $referral->referrer->name }}</h5>
                                                    <p class="text-muted mb-0">{{ $referral->referrer->email }}</p>
                                                    <small class="text-info">
                                                        <i class="fas fa-wallet me-1"></i>Wallet: ₹{{ number_format($referral->referrer->wallet_balance ?? 0, 2) }}
                                                    </small>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row text-center">
                                                <div class="col-6">
                                                    <h4 class="text-success mb-1">₹{{ number_format($referral->reward_amount, 2) }}</h4>
                                                    <small class="text-muted">Reward Amount</small>
                                                </div>
                                                <div class="col-6">
                                                    @if($referral->reward_claimed)
                                                        <span class="badge bg-success rounded-pill px-3 py-2">
                                                            <i class="fas fa-check me-1"></i>Credited to Wallet
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning rounded-pill px-3 py-2">
                                                            <i class="fas fa-clock me-1"></i>Pending
                                                        </span>
                                                        @if($referral->isCompleted())
                                                            <button type="button" class="btn btn-sm btn-success rounded-pill mt-2 claim-reward-btn" 
                                                                data-type="referrer">
                                                                <i class="fas fa-wallet me-1"></i>Credit to Wallet
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center text-muted py-4">
                                                <i class="fas fa-user-slash fa-3x mb-3"></i>
                                                <p>Referrer not found</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Referred User Info -->
                            <div class="col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white border-0 py-3">
                                        <h5 class="card-title mb-0 fw-bold">
                                            <i class="fas fa-user-plus me-2 text-info"></i>Referred User
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @if($referral->referred)
                                            <div class="d-flex align-items-center mb-3">
                                                <img src="{{ $referral->referred->avatar_url }}" alt="" class="rounded-circle me-3" width="64" height="64">
                                                <div>
                                                    <h5 class="mb-1">{{ $referral->referred->name }}</h5>
                                                    <p class="text-muted mb-0">{{ $referral->referred->email }}</p>
                                                    <small class="text-info">
                                                        <i class="fas fa-wallet me-1"></i>Wallet: ₹{{ number_format($referral->referred->wallet_balance ?? 0, 2) }}
                                                    </small>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row text-center">
                                                <div class="col-6">
                                                    <h4 class="text-info mb-1">₹{{ number_format($referral->referred_reward_amount, 2) }}</h4>
                                                    <small class="text-muted">Reward Amount</small>
                                                </div>
                                                <div class="col-6">
                                                    @if($referral->referred_reward_claimed)
                                                        <span class="badge bg-success rounded-pill px-3 py-2">
                                                            <i class="fas fa-check me-1"></i>Credited to Wallet
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning rounded-pill px-3 py-2">
                                                            <i class="fas fa-clock me-1"></i>Pending
                                                        </span>
                                                        @if($referral->isCompleted())
                                                            <button type="button" class="btn btn-sm btn-success rounded-pill mt-2 claim-reward-btn" 
                                                                data-type="referred">
                                                                <i class="fas fa-wallet me-1"></i>Credit to Wallet
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center text-muted py-4">
                                                <i class="fas fa-user-clock fa-3x mb-3"></i>
                                                <p>Not yet used</p>
                                                <small>Waiting for someone to use this referral code</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Details Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-info-circle me-2 text-theme"></i>Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="text-muted small">Created At</label>
                                        <p class="mb-0 fw-medium">{{ $referral->created_at->format('d M Y, h:i A') }}</p>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="text-muted small">Expires At</label>
                                        <p class="mb-0 fw-medium {{ $referral->expires_at && $referral->expires_at->isPast() ? 'text-danger' : '' }}">
                                            {{ $referral->expires_at ? $referral->expires_at->format('d M Y, h:i A') : 'No expiry' }}
                                        </p>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="text-muted small">Completed At</label>
                                        <p class="mb-0 fw-medium">
                                            {{ $referral->completed_at ? $referral->completed_at->format('d M Y, h:i A') : 'Not completed' }}
                                        </p>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="text-muted small">Total Reward</label>
                                        <p class="mb-0 fw-bold text-success">₹{{ number_format($referral->getTotalRewardAmount(), 2) }}</p>
                                    </div>
                                </div>
                                
                                @if($referral->notes)
                                <hr>
                                <div>
                                    <label class="text-muted small">Notes</label>
                                    <p class="mb-0">{{ $referral->notes }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Claim All Rewards Button -->
                        @if($referral->isCompleted() && (!$referral->reward_claimed || !$referral->referred_reward_claimed) && $referral->referrer && $referral->referred)
                        <div class="card border-0 shadow-sm mb-4 bg-success bg-opacity-10">
                            <div class="card-body text-center py-4">
                                <h5 class="text-success mb-3">
                                    <i class="fas fa-gift me-2"></i>Rewards Ready to Claim
                                </h5>
                                <p class="text-muted mb-3">
                                    Total: <strong class="text-success">₹{{ number_format($referral->getTotalRewardAmount(), 2) }}</strong>
                                    @if(!$referral->reward_claimed && !$referral->referred_reward_claimed)
                                        (Both rewards pending)
                                    @elseif(!$referral->reward_claimed)
                                        (Referrer reward pending: ₹{{ number_format($referral->reward_amount, 2) }})
                                    @else
                                        (Referred reward pending: ₹{{ number_format($referral->referred_reward_amount, 2) }})
                                    @endif
                                </p>
                                @if(!$referral->reward_claimed && !$referral->referred_reward_claimed)
                                    <button type="button" class="btn btn-success btn-lg rounded-pill px-5" id="claim-all-rewards-btn">
                                        <i class="fas fa-wallet me-2"></i>Credit All Rewards to Wallets
                                    </button>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.referrals.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                            <div>
                                @if($referral->isPending())
                                    <button type="button" class="btn btn-success rounded-pill px-4 me-2 update-status-btn" data-status="completed">
                                        <i class="fas fa-check me-2"></i>Mark Completed
                                    </button>
                                    <button type="button" class="btn btn-warning rounded-pill px-4 me-2 update-status-btn" data-status="cancelled">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </button>
                                @endif
                                <a href="{{ route('admin.referrals.edit', $referral) }}" class="btn btn-theme rounded-pill px-4">
                                    <i class="fas fa-edit me-2"></i>Edit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update status buttons
    document.querySelectorAll('.update-status-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const status = this.dataset.status;
            const statusText = status.charAt(0).toUpperCase() + status.slice(1);
            
            if (!confirm(`Are you sure you want to mark this referral as ${statusText}?`)) {
                return;
            }
            
            fetch('{{ route("admin.referrals.update-status", $referral) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to update status');
                }
            })
            .catch(error => {
                alert('An error occurred');
                console.error(error);
            });
        });
    });
    
    // Claim reward buttons
    document.querySelectorAll('.claim-reward-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.dataset.type;
            
            if (!confirm(`Credit ${type} reward to wallet?`)) {
                return;
            }
            
            fetch('{{ route("admin.referrals.claim-reward", $referral) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ type: type })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to credit reward');
                }
            })
            .catch(error => {
                alert('An error occurred');
                console.error(error);
            });
        });
    });
    
    // Claim all rewards button
    const claimAllBtn = document.getElementById('claim-all-rewards-btn');
    if (claimAllBtn) {
        claimAllBtn.addEventListener('click', function() {
            if (!confirm('Credit both rewards to respective user wallets?')) {
                return;
            }
            
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            
            fetch('{{ route("admin.referrals.claim-all-rewards", $referral) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to credit rewards');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-wallet me-2"></i>Credit All Rewards to Wallets';
                }
            })
            .catch(error => {
                alert('An error occurred');
                console.error(error);
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-wallet me-2"></i>Credit All Rewards to Wallets';
            });
        });
    }
});
</script>
@endsection
