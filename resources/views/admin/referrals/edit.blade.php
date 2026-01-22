@extends('admin.layouts.app')

@section('title', 'Edit Referral')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Edit Referral',
                'breadcrumbs' => [
                    'Referrals' => route('admin.referrals.index'),
                    'Edit' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 fw-bold">
                                        <i class="fas fa-edit me-2 text-theme"></i>Edit Referral
                                    </h5>
                                    <span class="badge bg-dark fs-6 font-monospace">{{ $referral->referral_code }}</span>
                                </div>
                            </div>
                            
                            <div class="card-body">
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
                                
                                <form action="{{ route('admin.referrals.update', $referral) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="referrer_id" class="form-label">Referrer (Who Refers) <span class="text-danger">*</span></label>
                                            <select class="form-select @error('referrer_id') is-invalid @enderror" id="referrer_id" name="referrer_id" required>
                                                <option value="">Select User</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}" {{ old('referrer_id', $referral->referrer_id) == $user->id ? 'selected' : '' }}>
                                                        {{ $user->name }} ({{ $user->email }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('referrer_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="referred_id" class="form-label">Referred User</label>
                                            <select class="form-select @error('referred_id') is-invalid @enderror" id="referred_id" name="referred_id">
                                                <option value="">Select User (if joined)</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}" {{ old('referred_id', $referral->referred_id) == $user->id ? 'selected' : '' }}>
                                                        {{ $user->name }} ({{ $user->email }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('referred_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="reward_amount" class="form-label">Referrer Reward (₹) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('reward_amount') is-invalid @enderror" 
                                                id="reward_amount" name="reward_amount" 
                                                value="{{ old('reward_amount', $referral->reward_amount) }}" 
                                                step="0.01" min="0" required>
                                            @error('reward_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="referred_reward_amount" class="form-label">Referred User Reward (₹) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('referred_reward_amount') is-invalid @enderror" 
                                                id="referred_reward_amount" name="referred_reward_amount" 
                                                value="{{ old('referred_reward_amount', $referral->referred_reward_amount) }}" 
                                                step="0.01" min="0" required>
                                            @error('referred_reward_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                                <option value="pending" {{ old('status', $referral->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="completed" {{ old('status', $referral->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="expired" {{ old('status', $referral->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                                                <option value="cancelled" {{ old('status', $referral->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="expires_at" class="form-label">Expiry Date</label>
                                            <input type="date" class="form-control @error('expires_at') is-invalid @enderror" 
                                                id="expires_at" name="expires_at" 
                                                value="{{ old('expires_at', $referral->expires_at?->format('Y-m-d')) }}">
                                            @error('expires_at')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="reward_claimed" name="reward_claimed" 
                                                    {{ old('reward_claimed', $referral->reward_claimed) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="reward_claimed">
                                                    Referrer Reward Claimed
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="referred_reward_claimed" name="referred_reward_claimed" 
                                                    {{ old('referred_reward_claimed', $referral->referred_reward_claimed) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="referred_reward_claimed">
                                                    Referred User Reward Claimed
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                            id="notes" name="notes" rows="3" maxlength="500">{{ old('notes', $referral->notes) }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    @if($referral->completed_at)
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Completed on: {{ $referral->completed_at->format('d M Y, h:i A') }}
                                    </div>
                                    @endif
                                    
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('admin.referrals.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                            <i class="fas fa-arrow-left me-2"></i>Back
                                        </a>
                                        <div>
                                            <button type="button" class="btn btn-outline-danger rounded-pill px-4 me-2" 
                                                onclick="if(confirm('Are you sure you want to delete this referral?')) { document.getElementById('delete-form').submit(); }">
                                                <i class="fas fa-trash me-2"></i>Delete
                                            </button>
                                            <button type="submit" class="btn btn-theme rounded-pill px-4">
                                                <i class="fas fa-save me-2"></i>Update Referral
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                
                                <form id="delete-form" action="{{ route('admin.referrals.destroy', $referral) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
