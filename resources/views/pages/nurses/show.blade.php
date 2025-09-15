@extends('layouts.master')

@section('title', localize('global.nurse_details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">{{ localize('global.nurse_details') }}: {{ $nurse->full_name }}</h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('nurses.edit', $nurse) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> {{ localize('global.edit') }}
                            </a>
                            <a href="{{ route('nurses.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> {{ localize('global.back') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- User Account Information -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user-circle"></i> {{ localize('global.user_account') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.user_account') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->user)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> 
                                                    {{ $nurse->user->name }} ({{ $nurse->user->email }})
                                                </span>
                                                <br>
                                                <small class="text-muted">{{ localize('global.has_login_access') }}</small>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-times-circle"></i> {{ localize('global.no_user_account') }}
                                                </span>
                                                <br>
                                                <small class="text-muted">{{ localize('global.no_login_access') }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- Personal Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user"></i> {{ localize('global.personal_information') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.full_name') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            {{ $nurse->full_name }}
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.gender') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-info">{{ ucfirst($nurse->gender) }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.date_of_birth') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->date_of_birth)
                                                {{ $nurse->date_of_birth->format('F d, Y') }}
                                                <small class="text-muted">({{ $nurse->date_of_birth->age }} {{ localize('global.years_old') }})</small>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_provided') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.phone') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->phone)
                                                <a href="tel:{{ $nurse->phone }}" class="text-decoration-none">
                                                    <i class="fas fa-phone"></i> {{ $nurse->phone }}
                                                </a>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_provided') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.email') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->email)
                                                <a href="mailto:{{ $nurse->email }}" class="text-decoration-none">
                                                    <i class="fas fa-envelope"></i> {{ $nurse->email }}
                                                </a>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_provided') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.address') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->address)
                                                {{ $nurse->address }}
                                            @else
                                                <span class="text-muted">{{ localize('global.not_provided') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Employment Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-briefcase"></i> {{ localize('global.employment_information') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.employee_id') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-primary">{{ $nurse->employee_id }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.branch') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->branch)
                                                <span class="badge bg-info">{{ $nurse->branch->name }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.no_branch_assigned') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.department') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->department)
                                                <span class="badge bg-secondary">{{ $nurse->department->name }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.no_department_assigned') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.specialization') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->specialization)
                                                <span class="badge bg-info">{{ $nurse->specialization }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.general') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.shift') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-warning">{{ ucfirst($nurse->shift) }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.status') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->employment_status == 'active')
                                                <span class="badge bg-success">{{ localize('global.active') }}</span>
                                            @elseif($nurse->employment_status == 'inactive')
                                                <span class="badge bg-danger">{{ localize('global.inactive') }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ localize('global.on_leave') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.date_of_joining') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->date_of_joining)
                                                {{ $nurse->date_of_joining->format('F d, Y') }}
                                                <small class="text-muted">({{ $nurse->date_of_joining->diffForHumans() }})</small>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_provided') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle"></i> {{ localize('global.system_information') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <strong>{{ localize('global.created_by') }}</strong>
                                                <div class="mt-2">
                                                    @if($nurse->createdBy)
                                                        <span class="badge bg-light text-dark">{{ $nurse->createdBy->name }}</span>
                                                    @else
                                                        <span class="text-muted">{{ localize('global.system') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <strong>{{ localize('global.created_at') }}</strong>
                                                <div class="mt-2">
                                                    <small>{{ $nurse->created_at->format('M d, Y H:i') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <strong>{{ localize('global.last_updated_by') }}</strong>
                                                <div class="mt-2">
                                                    @if($nurse->updatedBy)
                                                        <span class="badge bg-light text-dark">{{ $nurse->updatedBy->name }}</span>
                                                    @else
                                                        <span class="text-muted">{{ localize('global.not_updated') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <strong>{{ localize('global.last_updated') }}</strong>
                                                <div class="mt-2">
                                                    <small>{{ $nurse->updated_at->format('M d, Y H:i') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <form action="{{ route('nurses.destroy', $nurse) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this nurse? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> {{ localize('global.delete_nurse') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .badge {
        font-size: 0.875em;
    }
    
    .row.mb-3 {
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 0.75rem;
    }
    
    .row.mb-3:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
</style>
@endpush
