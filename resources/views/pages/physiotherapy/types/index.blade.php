@extends('layouts.master')

@section('title', localize('global.physiotherapy_types'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ localize('global.physiotherapy_types') }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('physiotherapy-types.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ localize('global.create_new') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.id') }}</th>
                                    <th>{{ localize('global.name') }}</th>
                                    <th>{{ localize('global.description') }}</th>
                                    <th>{{ localize('global.total_procedures') }}</th>
                                    <th>{{ localize('global.created_by') }}</th>
                                    <th>{{ localize('global.created_at') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($physiotherapyTypes as $type)
                                    <tr>
                                        <td>{{ $type->id }}</td>
                                        <td>
                                            <strong>{{ $type->name }}</strong>
                                        </td>
                                        <td>
                                            @if($type->description)
                                                {{ Str::limit($type->description, 100) }}
                                            @else
                                                <span class="text-muted">{{ localize('global.no_description') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $type->physiotherapyProcedures->count() }}</span>
                                        </td>
                                        <td>
                                            @if($type->createdBy)
                                                <small>{{ $type->createdBy->name }}</small>
                                            @else
                                                <span class="text-muted">{{ localize('global.system') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $type->created_at->format('Y-m-d H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('physiotherapy-types.show', $type) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="{{ localize('global.view') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('physiotherapy-types.edit', $type) }}" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="{{ localize('global.edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($type->physiotherapyProcedures->count() == 0)
                                                    <form action="{{ route('physiotherapy-types.destroy', $type) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('{{ localize('global.confirm_delete') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-danger" 
                                                                title="{{ localize('global.delete') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" 
                                                            disabled 
                                                            title="{{ localize('global.cannot_delete_with_procedures') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">{{ localize('global.no_physiotherapy_types_found') }}</p>
                                                <a href="{{ route('physiotherapy-types.create') }}" class="btn btn-primary">
                                                    {{ localize('global.create_first_type') }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($physiotherapyTypes->hasPages())
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <small class="text-muted">
                                        {{ localize('global.showing') }} 
                                        <strong>{{ $physiotherapyTypes->firstItem() ?? 0 }}</strong> 
                                        {{ localize('global.to') }} 
                                        <strong>{{ $physiotherapyTypes->lastItem() ?? 0 }}</strong> 
                                        {{ localize('global.of') }} 
                                        <strong>{{ $physiotherapyTypes->total() }}</strong> 
                                        {{ localize('global.results') }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    <nav aria-label="Physiotherapy Types Pagination">
                                        {{ $physiotherapyTypes->links('pagination::bootstrap-5') }}
                                    </nav>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-center">
                                    <small class="text-muted">
                                        {{ localize('global.showing') }} 
                                        <strong>{{ $physiotherapyTypes->count() }}</strong> 
                                        {{ localize('global.results') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Custom pagination styling */
    .pagination {
        margin-bottom: 0;
    }
    
    .page-link {
        color: #007bff;
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
        margin-left: -1px;
        transition: all 0.15s ease-in-out;
    }
    
    .page-link:hover {
        color: #0056b3;
        background-color: #e9ecef;
        border-color: #dee2e6;
        z-index: 2;
    }
    
    .page-link:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        z-index: 3;
    }
    
    .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }
    
    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }
    
    .page-item:first-child .page-link {
        margin-left: 0;
        border-top-left-radius: 0.25rem;
        border-bottom-left-radius: 0.25rem;
    }
    
    .page-item:last-child .page-link {
        border-top-right-radius: 0.25rem;
        border-bottom-right-radius: 0.25rem;
    }
    
    /* Responsive pagination */
    @media (max-width: 576px) {
        .pagination {
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .page-link {
            padding: 0.375rem 0.5rem;
            font-size: 0.875rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>
@endpush
