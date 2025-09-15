@extends('layouts.master')

@section('title', localize('global.mar_details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <i class="fas fa-pills"></i> {{ localize('global.medication_administration_records') }} ({{ localize('global.mar') }}) #{{ $medicationAdministrationRecord->id }}
                    </h4>
                    <div class="btn-group">
                        @can('update', $medicationAdministrationRecord)
                            <a href="{{ route('medication-administration-records.edit', $medicationAdministrationRecord) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> {{ localize('global.mar_edit') }}
                            </a>
                        @endcan
                        <a href="{{ route('medication-administration-records.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ localize('global.mar_back_to_list') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">{{ localize('global.mar_basic_information') }}</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>{{ localize('global.medicine') }}:</strong></td>
                                            <td>{{ $medicationAdministrationRecord->medicine->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ localize('global.nurse') }}:</strong></td>
                                            <td>{{ $medicationAdministrationRecord->nurse->full_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ localize('global.order_date') }}:</strong></td>
                                            <td>{{ $medicationAdministrationRecord->order_date ? $medicationAdministrationRecord->order_date->format('Y-m-d') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ localize('global.signature_date') }}:</strong></td>
                                            <td>{{ $medicationAdministrationRecord->date_signature ? $medicationAdministrationRecord->date_signature->format('Y-m-d') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ localize('global.select_record_type') }}:</strong></td>
                                            <td>
                                                @if($medicationAdministrationRecord->morphable_type == 'App\Models\UnderReview')
                                                    <span class="badge badge-info">Under Review</span>
                                                @elseif($medicationAdministrationRecord->morphable_type == 'App\Models\Hospitalization')
                                                    <span class="badge badge-primary">Hospitalization</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ $medicationAdministrationRecord->morphable_type }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ localize('global.record_id') }}:</strong></td>
                                            <td>{{ $medicationAdministrationRecord->morphable_id }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Patient Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Patient Information</h5>
                                </div>
                                <div class="card-body">
                                    @if($medicationAdministrationRecord->patient)
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Patient Name:</strong></td>
                                                <td>{{ $medicationAdministrationRecord->patient->first_name }} {{ $medicationAdministrationRecord->patient->last_name }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Patient ID:</strong></td>
                                                <td>{{ $medicationAdministrationRecord->patient->id }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Age:</strong></td>
                                                <td>{{ $medicationAdministrationRecord->patient->age ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Gender:</strong></td>
                                                <td>{{ $medicationAdministrationRecord->patient->gender ?? 'N/A' }}</td>
                                            </tr>
                                        </table>
                                    @else
                                        <p class="text-muted">No patient information available</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Administration Times -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title">Administration Times</h5>
                                    @can('update', $medicationAdministrationRecord)
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTimeModal">
                                            <i class="fas fa-plus"></i> Add Time
                                        </button>
                                    @endcan
                                </div>
                                <div class="card-body">
                                    @if($medicationAdministrationRecord->administrationTimes->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Time</th>
                                                        <th>Created At</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($medicationAdministrationRecord->administrationTimes as $time)
                                                        <tr>
                                                            <td>
                                                                <span class="badge badge-success">
                                                                    {{ $time->formatted_time ?? 'N/A' }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $time->created_at->format('Y-m-d H:i:s') }}</td>
                                                            <td>
                                                                @can('update', $medicationAdministrationRecord)
                                                                    <form action="{{ route('medication-administration-records.remove-time', $time) }}" 
                                                                          method="POST" style="display: inline-block;">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                                                title="Remove Time" 
                                                                                onclick="return confirm('Are you sure you want to remove this administration time?')">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                @endcan
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted text-center">
                                            <i class="fas fa-info-circle"></i> No administration times recorded yet.
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Audit Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Audit Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Created At:</strong></td>
                                            <td>{{ $medicationAdministrationRecord->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created By:</strong></td>
                                            <td>{{ $medicationAdministrationRecord->createdBy->name ?? 'System' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Updated At:</strong></td>
                                            <td>{{ $medicationAdministrationRecord->updated_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Updated By:</strong></td>
                                            <td>{{ $medicationAdministrationRecord->updatedBy->name ?? 'System' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Time Modal -->
@can('update', $medicationAdministrationRecord)
<div class="modal fade" id="addTimeModal" tabindex="-1" aria-labelledby="addTimeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTimeModalLabel">Add Administration Time</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('medication-administration-records.add-time', $medicationAdministrationRecord) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="time" class="form-label">Administration Time <span class="text-danger">*</span></label>
                        <input type="time" name="time" id="time" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Time</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
