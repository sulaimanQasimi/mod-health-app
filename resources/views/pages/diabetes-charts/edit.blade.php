@extends('layouts.master')

@section('title', localize('global.edit_diabetes_chart'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">{{ localize('global.edit_diabetes_chart') }} #{{ $diabetesChart->id }}</h4>
                        <a href="{{ route('diabetes-charts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ localize('global.back') }}
                        </a>
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
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('diabetes-charts.update', $diabetesChart) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Chartable Information (Read-only) -->
                            <div class="col-12">
                                <h5 class="mb-3">{{ localize('global.chartable_record') }}</h5>
                                
                                <div class="alert alert-info">
                                    <strong>{{ localize('global.linked_to') }}:</strong> 
                                    @if($diabetesChart->diabetes_chartable_type === 'App\\Models\\UnderReview')
                                        <span class="badge bg-warning">{{ localize('global.under_review') }}</span>
                                    @elseif($diabetesChart->diabetes_chartable_type === 'App\\Models\\Hospitalization')
                                        <span class="badge bg-danger">{{ localize('global.hospitalization') }}</span>
                                    @endif
                                    ID: {{ $diabetesChart->diabetes_chartable_id }}
                                    @if($diabetesChart->diabetesChartable && $diabetesChart->diabetesChartable->patient)
                                        - {{ $diabetesChart->diabetesChartable->patient->name }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <!-- Blood Sugar Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ localize('global.blood_sugar_reading') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="rbs" class="form-label">{{ localize('global.rbs') }} ({{ localize('global.random_blood_sugar') }})</label>
                                    <input type="number" step="0.1" class="form-control @error('rbs') is-invalid @enderror" 
                                           id="rbs" name="rbs" value="{{ old('rbs', $diabetesChart->rbs) }}" 
                                           placeholder="e.g., 180.5">
                                    @error('rbs')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="fbs" class="form-label">{{ localize('global.fbs') }} ({{ localize('global.fasting_blood_sugar') }})</label>
                                    <input type="number" step="0.1" class="form-control @error('fbs') is-invalid @enderror" 
                                           id="fbs" name="fbs" value="{{ old('fbs', $diabetesChart->fbs) }}" 
                                           placeholder="e.g., 95.0">
                                    @error('fbs')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="unit" class="form-label">{{ localize('global.unit') }}</label>
                                    <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit">
                                        <option value="">{{ localize('global.please_select') }}</option>
                                        <option value="mg/dl" {{ old('unit', $diabetesChart->unit) == 'mg/dl' ? 'selected' : '' }}>{{ localize('global.mg_dl') }}</option>
                                        <option value="mmol/l" {{ old('unit', $diabetesChart->unit) == 'mmol/l' ? 'selected' : '' }}>{{ localize('global.mmol_l') }}</option>
                                    </select>
                                    @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Insulin and Medicine Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ localize('global.insulin_administration') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="insulin_dose" class="form-label">{{ localize('global.insulin_dose') }}</label>
                                    <input type="number" step="0.1" class="form-control @error('insulin_dose') is-invalid @enderror" 
                                           id="insulin_dose" name="insulin_dose" value="{{ old('insulin_dose', $diabetesChart->insulin_dose) }}" 
                                           placeholder="e.g., 10.5">
                                    @error('insulin_dose')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="medicine_id" class="form-label">{{ localize('global.medicine') }}</label>
                                    <select class="form-select @error('medicine_id') is-invalid @enderror" id="medicine_id" name="medicine_id">
                                        <option value="">{{ localize('global.please_select') }}</option>
                                        @foreach($medicines as $medicine)
                                            <option value="{{ $medicine->id }}" {{ old('medicine_id', $diabetesChart->medicine_id) == $medicine->id ? 'selected' : '' }}>
                                                {{ $medicine->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('medicine_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="nurse_id" class="form-label">{{ localize('global.nurse') }}</label>
                                    <select class="form-select @error('nurse_id') is-invalid @enderror" id="nurse_id" name="nurse_id">
                                        <option value="">{{ localize('global.please_select') }}</option>
                                        @foreach($nurses as $nurse)
                                            <option value="{{ $nurse->id }}" {{ old('nurse_id', $diabetesChart->nurse_id) == $nurse->id ? 'selected' : '' }}>
                                                {{ $nurse->full_name }} ({{ $nurse->employee_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('nurse_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <!-- Date and Time Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ localize('global.date_time_information') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="date" class="form-label">{{ localize('global.date') }} <span class="text-danger">*</span></label>
                                    <input type="date" autocomplete="off" class="form-control @error('date') is-invalid @enderror" 
                                           id="date" name="date" value="{{ old('date', $diabetesChart->date?->format('Y-m-d')) }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="time" class="form-label">{{ localize('global.time') }}</label>
                                    <input type="time" class="form-control @error('time') is-invalid @enderror" 
                                           id="time" name="time" value="{{ old('time', $diabetesChart->time?->format('H:i')) }}">
                                    @error('time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('diabetes-charts.index') }}" class="btn btn-secondary">{{ localize('global.cancel') }}</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ localize('global.update') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Set maximum date for date field to today
    document.getElementById('date').max = new Date().toISOString().split('T')[0];
</script>
@endpush
