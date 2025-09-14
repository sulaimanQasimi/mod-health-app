@extends('layouts.master')

@section('title', localize('global.diabetes_chart_details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">{{ localize('global.diabetes_chart_details') }} #{{ $diabetesChart->id }}</h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('diabetes-charts.edit', $diabetesChart) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> {{ localize('global.edit') }}
                            </a>
                            <a href="{{ route('diabetes-charts.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> {{ localize('global.back') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Chartable Information -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-link"></i> {{ localize('global.chartable_record') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.chartable_type') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($diabetesChart->diabetes_chartable_type === 'App\\Models\\UnderReview')
                                                <span class="badge bg-warning">{{ localize('global.under_review') }}</span>
                                            @elseif($diabetesChart->diabetes_chartable_type === 'App\\Models\\Hospitalization')
                                                <span class="badge bg-danger">{{ localize('global.hospitalization') }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.unknown') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.chartable_id') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-info">{{ $diabetesChart->diabetes_chartable_id }}</span>
                                        </div>
                                    </div>
                                    @if($diabetesChart->diabetesChartable && $diabetesChart->diabetesChartable->patient)
                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>{{ localize('global.patient') }}:</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                <span class="badge bg-success">{{ $diabetesChart->diabetesChartable->patient->name }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- Blood Sugar Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-tint"></i> {{ localize('global.blood_sugar_reading') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.rbs') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($diabetesChart->rbs)
                                                <span class="badge bg-warning fs-6">{{ $diabetesChart->rbs }} {{ $diabetesChart->unit }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_recorded') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.fbs') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($diabetesChart->fbs)
                                                <span class="badge bg-success fs-6">{{ $diabetesChart->fbs }} {{ $diabetesChart->unit }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_recorded') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.unit') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($diabetesChart->unit)
                                                <span class="badge bg-secondary">{{ $diabetesChart->unit }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_set') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Insulin and Medicine Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-syringe"></i> {{ localize('global.insulin_administration') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.insulin_dose') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($diabetesChart->insulin_dose)
                                                <span class="badge bg-primary fs-6">{{ $diabetesChart->insulin_dose }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_recorded') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.medicine') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($diabetesChart->medicine)
                                                <span class="badge bg-info">{{ $diabetesChart->medicine->name }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_assigned') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.nurse') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($diabetesChart->nurse)
                                                <span class="badge bg-success">{{ $diabetesChart->nurse->full_name }}</span>
                                                <br><small class="text-muted">{{ $diabetesChart->nurse->employee_id }}</small>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_assigned') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- Date and Time Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-calendar-alt"></i> {{ localize('global.date_time_information') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.date') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($diabetesChart->date)
                                                <span class="badge bg-info">{{ $diabetesChart->date->format('Y-m-d') }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_set') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.time') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($diabetesChart->time)
                                                <span class="badge bg-secondary">{{ $diabetesChart->formatted_time }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.not_set') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Audit Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-history"></i> {{ localize('global.audit_information') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.created_by') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            @if($diabetesChart->createdBy)
                                                <span class="badge bg-success">{{ $diabetesChart->createdBy->name }}</span>
                                            @else
                                                <span class="text-muted">{{ localize('global.unknown') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.created_at') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <small class="text-muted">{{ $diabetesChart->created_at->format('Y-m-d H:i:s') }}</small>
                                        </div>
                                    </div>
                                    @if($diabetesChart->updatedBy)
                                        <div class="row mb-3">
                                            <div class="col-sm-4">
                                                <strong>{{ localize('global.updated_by') }}:</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                <span class="badge bg-warning">{{ $diabetesChart->updatedBy->name }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ localize('global.updated_at') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <small class="text-muted">{{ $diabetesChart->updated_at->format('Y-m-d H:i:s') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
