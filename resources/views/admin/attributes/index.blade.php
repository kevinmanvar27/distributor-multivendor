@extends('admin.layouts.app')

@section('title', 'Product Attributes')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        @include('admin.layouts.sidebar')
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            @include('admin.layouts.header', ['pageTitle' => 'Product Attributes'])
            
            <div class="pt-4 pb-2 mb-3">
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 gap-md-0">
                <h1 class="h4 h3-md mb-2 mb-md-0">Product Attributes</h1>
                <a href="{{ route('admin.attributes.create') }}" class="btn btn-sm btn-md-normal btn-primary">
                    <i class="fas fa-plus me-1"></i><span class="d-none d-sm-inline">Add New Attribute</span><span class="d-sm-none">Add</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Values</th>
                            <th>Sort Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attributes as $attribute)
                            <tr>
                                <td><strong>{{ $attribute->name }}</strong></td>
                                <td><code>{{ $attribute->slug }}</code></td>
                                <td>
                                    <span class="badge bg-info">{{ $attribute->values->count() }} values</span>
                                </td>
                                <td>{{ $attribute->sort_order }}</td>
                                <td>
                                    @if($attribute->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.attributes.edit', $attribute) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.attributes.destroy', $attribute) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this attribute?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <p class="text-muted mb-0">No attributes found. Create your first attribute to get started.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attributes->hasPages())
                <div class="mt-4">
                    {{ $attributes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
            </div>
        </main>
    </div>
</div>
@endsection
