@extends('vendor.layouts.app')

@section('title', 'Lead Details')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('vendor.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('vendor.layouts.header', [
                'pageTitle' => 'Lead Details',
                'breadcrumbs' => [
                    'Leads' => route('vendor.leads.index'),
                    $lead->name => null
                ]
            ])
            
            <div class="pt-4 pb-2 mb-3">
                <div class="row">
                    <!-- Lead Details -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0 fw-bold">
                                    {{ $lead->name }}
                                    @php
                                        $statusColors = [
                                            'new' => 'primary',
                                            'contacted' => 'info',
                                            'qualified' => 'warning',
                                            'converted' => 'success',
                                            'lost' => 'danger',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$lead->status] ?? 'secondary' }} ms-2">
                                        {{ ucfirst($lead->status) }}
                                    </span>
                                </h4>
                                <div>
                                    <a href="{{ route('vendor.leads.edit', $lead) }}" class="btn btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-edit me-2"></i>Edit
                                    </a>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted mb-2">Contact Number</h6>
                                        <h5 class="fw-bold">
                                            <a href="tel:{{ $lead->contact_number }}" class="text-decoration-none">
                                                <i class="fas fa-phone me-2 text-success"></i>{{ $lead->contact_number }}
                                            </a>
                                        </h5>
                                    </div>
                                    
                                    @if($lead->email)
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted mb-2">Email</h6>
                                        <h5 class="fw-bold">
                                            <a href="mailto:{{ $lead->email }}" class="text-decoration-none">
                                                <i class="fas fa-envelope me-2 text-primary"></i>{{ $lead->email }}
                                            </a>
                                        </h5>
                                    </div>
                                    @endif
                                    
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted mb-2">Status</h6>
                                        <span class="badge bg-{{ $statusColors[$lead->status] ?? 'secondary' }} fs-6 px-3 py-2">
                                            {{ ucfirst($lead->status) }}
                                        </span>
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted mb-2">Created</h6>
                                        <h5 class="fw-bold">{{ $lead->created_at->format('d M Y, h:i A') }}</h5>
                                    </div>
                                </div>
                                
                                @if($lead->note)
                                <hr>
                                <div class="mt-3">
                                    <h6 class="text-muted mb-2">Notes</h6>
                                    <div class="p-3 bg-light rounded">
                                        {!! nl2br(e($lead->note)) !!}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <a href="tel:{{ $lead->contact_number }}" class="btn btn-success w-100 rounded-pill mb-2">
                                    <i class="fas fa-phone me-2"></i>Call Lead
                                </a>
                                @if($lead->email)
                                <a href="mailto:{{ $lead->email }}" class="btn btn-primary w-100 rounded-pill mb-2">
                                    <i class="fas fa-envelope me-2"></i>Email Lead
                                </a>
                                @endif
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->contact_number) }}" target="_blank" class="btn btn-outline-success w-100 rounded-pill mb-2">
                                    <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                </a>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-exchange-alt me-2"></i>Update Status</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('vendor.leads.update', $lead) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="name" value="{{ $lead->name }}">
                                    <input type="hidden" name="contact_number" value="{{ $lead->contact_number }}">
                                    <input type="hidden" name="note" value="{{ $lead->note }}">
                                    <select name="status" class="form-select mb-3" onchange="this.form.submit()">
                                        <option value="new" {{ $lead->status === 'new' ? 'selected' : '' }}>New</option>
                                        <option value="contacted" {{ $lead->status === 'contacted' ? 'selected' : '' }}>Contacted</option>
                                        <option value="qualified" {{ $lead->status === 'qualified' ? 'selected' : '' }}>Qualified</option>
                                        <option value="converted" {{ $lead->status === 'converted' ? 'selected' : '' }}>Converted</option>
                                        <option value="lost" {{ $lead->status === 'lost' ? 'selected' : '' }}>Lost</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <a href="{{ route('vendor.leads.edit', $lead) }}" class="btn btn-theme w-100 rounded-pill mb-2">
                                    <i class="fas fa-edit me-2"></i>Edit Lead
                                </a>
                                <a href="{{ route('vendor.leads.index') }}" class="btn btn-outline-secondary w-100 rounded-pill mb-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to List
                                </a>
                                <form action="{{ route('vendor.leads.destroy', $lead) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('Are you sure you want to delete this lead?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100 rounded-pill">
                                        <i class="fas fa-trash me-2"></i>Delete Lead
                                    </button>
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
