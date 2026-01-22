@extends('admin.layouts.app')

@section('title', 'Create Referral')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', [
                'pageTitle' => 'Create Referral',
                'breadcrumbs' => [
                    'Referrals' => route('admin.referrals.index'),
                    'Create' => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-user-plus me-2 text-theme"></i>Create New Referral
                                </h5>
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
                                
                                <form action="{{ route('admin.referrals.store') }}" method="POST">
                                    @csrf
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="referrer_id" class="form-label">Referrer (Who Refers) <span class="text-danger">*</span></label>
                                            <select class="form-select @error('referrer_id') is-invalid @enderror" id="referrer_id" name="referrer_id" required>
                                                <option value="">Select User</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}" {{ old('referrer_id') == $user->id ? 'selected' : '' }}>
                                                        {{ $user->name }} ({{ $user->email }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('referrer_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="referred_id" class="form-label">Referred User (Optional)</label>
                                            <select class="form-select @error('referred_id') is-invalid @enderror" id="referred_id" name="referred_id">
                                                <option value="">Select User (if already joined)</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}" {{ old('referred_id') == $user->id ? 'selected' : '' }}>
                                                        {{ $user->name }} ({{ $user->email }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('referred_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Leave empty if referral code is not yet used</small>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="reward_amount" class="form-label">Referrer Reward (₹) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('reward_amount') is-invalid @enderror" 
                                                id="reward_amount" name="reward_amount" 
                                                value="{{ old('reward_amount', $settings->referral_reward_amount ?? 100) }}" 
                                                step="0.01" min="0" required>
                                            @error('reward_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="referred_reward_amount" class="form-label">Referred User Reward (₹) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('referred_reward_amount') is-invalid @enderror" 
                                                id="referred_reward_amount" name="referred_reward_amount" 
                                                value="{{ old('referred_reward_amount', $settings->referred_reward_amount ?? 50) }}" 
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
                                                <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="expires_at" class="form-label">Expiry Date</label>
                                            <input type="date" class="form-control @error('expires_at') is-invalid @enderror" 
                                                id="expires_at" name="expires_at" 
                                                value="{{ old('expires_at', now()->addDays($settings->referral_expiry_days ?? 30)->format('Y-m-d')) }}">
                                            @error('expires_at')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                            id="notes" name="notes" rows="3" maxlength="500">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('admin.referrals.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                            <i class="fas fa-arrow-left me-2"></i>Back
                                        </a>
                                        <button type="submit" class="btn btn-theme rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i>Create Referral
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Quick Generate Card -->
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="fas fa-magic me-2 text-theme"></i>Quick Generate Codes
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">Generate multiple referral codes for a user at once.</p>
                                
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label for="quick_user_id" class="form-label">Select User</label>
                                        <select class="form-select" id="quick_user_id">
                                            <option value="">Select User</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="quick_count" class="form-label">Number of Codes</label>
                                        <input type="number" class="form-control" id="quick_count" value="1" min="1" max="10">
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-theme rounded-pill w-100" id="generateCodesBtn">
                                            <i class="fas fa-bolt me-2"></i>Generate Codes
                                        </button>
                                    </div>
                                </div>
                                
                                <div id="generatedCodes" class="mt-3 d-none">
                                    <div class="alert alert-success">
                                        <strong>Generated Codes:</strong>
                                        <div id="codesList" class="mt-2 font-monospace"></div>
                                    </div>
                                </div>
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
    const generateBtn = document.getElementById('generateCodesBtn');
    const generatedCodesDiv = document.getElementById('generatedCodes');
    const codesListDiv = document.getElementById('codesList');
    
    generateBtn.addEventListener('click', function() {
        const userId = document.getElementById('quick_user_id').value;
        const count = document.getElementById('quick_count').value;
        
        if (!userId) {
            alert('Please select a user');
            return;
        }
        
        generateBtn.disabled = true;
        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';
        
        fetch('{{ route("admin.referrals.generate-code") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                user_id: userId,
                count: count
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                codesListDiv.innerHTML = data.codes.map(code => 
                    `<span class="badge bg-dark me-2 mb-2">${code}</span>`
                ).join('');
                generatedCodesDiv.classList.remove('d-none');
            } else {
                alert(data.message || 'Failed to generate codes');
            }
        })
        .catch(error => {
            alert('An error occurred');
            console.error(error);
        })
        .finally(() => {
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="fas fa-bolt me-2"></i>Generate Codes';
        });
    });
});
</script>
@endsection
