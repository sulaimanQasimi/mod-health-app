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
                                        <i class="fas fa-user"></i> Personal Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Full Name:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            {{ $nurse->full_name }}
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Gender:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-info">{{ ucfirst($nurse->gender) }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Date of Birth:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->date_of_birth)
                                                {{ $nurse->date_of_birth->format('F d, Y') }}
                                                <small class="text-muted">({{ $nurse->date_of_birth->age }} years old)</small>
                                            @else
                                                <span class="text-muted">Not provided</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Phone:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->phone)
                                                <a href="tel:{{ $nurse->phone }}" class="text-decoration-none">
                                                    <i class="fas fa-phone"></i> {{ $nurse->phone }}
                                                </a>
                                            @else
                                                <span class="text-muted">Not provided</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Email:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->email)
                                                <a href="mailto:{{ $nurse->email }}" class="text-decoration-none">
                                                    <i class="fas fa-envelope"></i> {{ $nurse->email }}
                                                </a>
                                            @else
                                                <span class="text-muted">Not provided</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Address:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->address)
                                                {{ $nurse->address }}
                                            @else
                                                <span class="text-muted">Not provided</span>
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
                                        <i class="fas fa-briefcase"></i> Employment Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Employee ID:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-primary">{{ $nurse->employee_id }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Branch:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->branch)
                                                <span class="badge bg-info">{{ $nurse->branch->name }}</span>
                                            @else
                                                <span class="text-muted">No Branch Assigned</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Department:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->department)
                                                <span class="badge bg-secondary">{{ $nurse->department->name }}</span>
                                            @else
                                                <span class="text-muted">No Department Assigned</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Specialization:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->specialization)
                                                <span class="badge bg-info">{{ $nurse->specialization }}</span>
                                            @else
                                                <span class="text-muted">General</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Shift:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-warning">{{ ucfirst($nurse->shift) }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Status:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->employment_status == 'active')
                                                <span class="badge bg-success">Active</span>
                                            @elseif($nurse->employment_status == 'inactive')
                                                <span class="badge bg-danger">Inactive</span>
                                            @else
                                                <span class="badge bg-warning">On Leave</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>Date of Joining:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($nurse->date_of_joining)
                                                {{ $nurse->date_of_joining->format('F d, Y') }}
                                                <small class="text-muted">({{ $nurse->date_of_joining->diffForHumans() }})</small>
                                            @else
                                                <span class="text-muted">Not provided</span>
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
                                        <i class="fas fa-info-circle"></i> System Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <strong>Created By</strong>
                                                <div class="mt-2">
                                                    @if($nurse->createdBy)
                                                        <span class="badge bg-light text-dark">{{ $nurse->createdBy->name }}</span>
                                                    @else
                                                        <span class="text-muted">System</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <strong>Created At</strong>
                                                <div class="mt-2">
                                                    <small>{{ $nurse->created_at->format('M d, Y H:i') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <strong>Last Updated By</strong>
                                                <div class="mt-2">
                                                    @if($nurse->updatedBy)
                                                        <span class="badge bg-light text-dark">{{ $nurse->updatedBy->name }}</span>
                                                    @else
                                                        <span class="text-muted">Not updated</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <strong>Last Updated</strong>
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
                                        <i class="fas fa-trash"></i> Delete Nurse
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
