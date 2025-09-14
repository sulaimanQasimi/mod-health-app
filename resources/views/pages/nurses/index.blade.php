@extends('layouts.master')

@section('title', localize('global.nurse_management'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ localize('global.nurse_management') }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('nurses.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ localize('global.add_nurse') }}
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

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
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

                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('nurses.index') }}" class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control" placeholder="{{ localize('global.search_nurses') }}" value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <select name="branch_id" class="form-select">
                                        <option value="">{{ localize('global.all_branches') }}</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="department_id" class="form-select">
                                        <option value="">{{ localize('global.all_departments') }}</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="shift" class="form-select">
                                        <option value="">{{ localize('global.all_shifts') }}</option>
                                        <option value="morning" {{ request('shift') == 'morning' ? 'selected' : '' }}>{{ localize('global.morning_shift') }}</option>
                                        <option value="evening" {{ request('shift') == 'evening' ? 'selected' : '' }}>{{ localize('global.evening_shift') }}</option>
                                        <option value="night" {{ request('shift') == 'night' ? 'selected' : '' }}>{{ localize('global.night_shift') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="employment_status" class="form-select">
                                        <option value="">{{ localize('global.all_statuses') }}</option>
                                        <option value="active" {{ request('employment_status') == 'active' ? 'selected' : '' }}>{{ localize('global.active') }}</option>
                                        <option value="inactive" {{ request('employment_status') == 'inactive' ? 'selected' : '' }}>{{ localize('global.inactive') }}</option>
                                        <option value="on_leave" {{ request('employment_status') == 'on_leave' ? 'selected' : '' }}>{{ localize('global.on_leave') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search"></i> {{ localize('global.filter') }}
                                    </button>
                                    <a href="{{ route('nurses.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> {{ localize('global.clear_filters') }}
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ localize('global.full_name') }}</th>
                                    <th>{{ localize('global.employee_id') }}</th>
                                    <th>{{ localize('global.user_account') }}</th>
                                    <th>{{ localize('global.branch') }}</th>
                                    <th>{{ localize('global.department') }}</th>
                                    <th>{{ localize('global.specialization') }}</th>
                                    <th>{{ localize('global.shift') }}</th>
                                    <th>{{ localize('global.employment_status') }}</th>
                                    <th>{{ localize('global.date_of_joining') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nurses as $nurse)
                                    <tr>
                                        <td>{{ $nurse->id }}</td>
                                        <td>
                                            <strong>{{ $nurse->full_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ ucfirst($nurse->gender) }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $nurse->employee_id }}</span>
                                        </td>
                                        <td>
                                            @if($nurse->user)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> {{ $nurse->user->name }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-times-circle"></i> {{ localize('global.no_user_account') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($nurse->branch)
                                                <span class="badge bg-info">{{ $nurse->branch->name }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.no_branch') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($nurse->department)
                                                {{ $nurse->department->name }}
                                            @else
                                                <span class="text-muted">{{ localize('global.no_department') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($nurse->specialization)
                                                {{ $nurse->specialization }}
                                            @else
                                                <span class="text-muted">{{ localize('global.general') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ ucfirst($nurse->shift) }}</span>
                                        </td>
                                        <td>
                                            @if($nurse->employment_status == 'active')
                                                <span class="badge bg-success">{{ localize('global.active') }}</span>
                                            @elseif($nurse->employment_status == 'inactive')
                                                <span class="badge bg-danger">{{ localize('global.inactive') }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ localize('global.on_leave') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($nurse->date_of_joining)
                                                <small>{{ $nurse->date_of_joining->format('Y-m-d') }}</small>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_set') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('nurses.show', $nurse) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="{{ localize('global.view') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('nurses.edit', $nurse) }}" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="{{ localize('global.edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('nurses.destroy', $nurse) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('{{ localize('global.are_you_sure_delete') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="{{ localize('global.delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-user-nurse fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">{{ localize('global.no_nurses_found') }}</p>
                                                <a href="{{ route('nurses.create') }}" class="btn btn-primary">
                                                    {{ localize('global.add_first_nurse') }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($nurses->hasPages())
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <small class="text-muted">
                                        {{ localize('global.showing') }} 
                                        <strong>{{ $nurses->firstItem() ?? 0 }}</strong> 
                                        {{ localize('global.to') }} 
                                        <strong>{{ $nurses->lastItem() ?? 0 }}</strong> 
                                        {{ localize('global.of') }} 
                                        <strong>{{ $nurses->total() }}</strong> 
                                        {{ localize('global.results') }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    <nav aria-label="Nurses Pagination">
                                        {{ $nurses->links('pagination::bootstrap-5') }}
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
                                        <strong>{{ $nurses->count() }}</strong> 
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
