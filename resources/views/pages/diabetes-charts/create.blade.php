@extends('layouts.master')

@section('title', localize('global.add_diabetes_chart'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">{{ localize('global.add_diabetes_chart') }}</h4>
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

                    @if(!$currentUserNurse)
                        <!-- Permission Denied Message -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                            </div>
                            <h2 class="text-danger mb-3">{{ localize('global.access_denied') }}</h2>
                            <h4 class="text-muted mb-4">{{ localize('global.no_nurse_permission_title') }}</h4>
                            <p class="lead text-muted mb-4">{{ localize('global.no_nurse_permission_message') }}</p>
                            <div class="alert alert-warning d-inline-block">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ localize('global.contact_admin_for_nurse_account') }}
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('diabetes-charts.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-arrow-left"></i> {{ localize('global.back_to_list') }}
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- Normal Form for Users with Nurse Records -->
                        <form action="{{ route('diabetes-charts.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <!-- Chartable Information -->
                            <div class="col-12">
                                <h5 class="mb-3">{{ localize('global.chartable_record') }}</h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="diabetes_chartable_type" class="form-label">{{ localize('global.chartable_type') }} <span class="text-danger">*</span></label>
                                            @if($chartableType && $chartableId)
                                                <!-- Pre-filled from URL, make readonly -->
                                                <div class="form-control-plaintext bg-light p-2 rounded">
                                                    <strong>
                                                        @if($chartableType === 'App\\Models\\UnderReview')
                                                            {{ localize('global.under_review') }}
                                                        @elseif($chartableType === 'App\\Models\\Hospitalization')
                                                            {{ localize('global.hospitalization') }}
                                                        @endif
                                                    </strong>
                                                    <small class="text-muted d-block">{{ localize('global.pre_filled_from_link') }}</small>
                                                </div>
                                                <input type="hidden" name="diabetes_chartable_type" value="{{ $chartableType }}">
                                            @else
                                                <!-- Not pre-filled, show dropdown -->
                                                <select class="form-select @error('diabetes_chartable_type') is-invalid @enderror" id="diabetes_chartable_type" name="diabetes_chartable_type" required>
                                                    <option value="">{{ localize('global.please_select') }}</option>
                                                    <option value="App\\Models\\UnderReview" {{ old('diabetes_chartable_type') == 'App\\Models\\UnderReview' ? 'selected' : '' }}>
                                                        {{ localize('global.under_review') }}
                                                    </option>
                                                    <option value="App\\Models\\Hospitalization" {{ old('diabetes_chartable_type') == 'App\\Models\\Hospitalization' ? 'selected' : '' }}>
                                                        {{ localize('global.hospitalization') }}
                                                    </option>
                                                </select>
                                                @error('diabetes_chartable_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="diabetes_chartable_id" class="form-label">{{ localize('global.chartable_id') }} <span class="text-danger">*</span></label>
                                            @if($chartableType && $chartableId)
                                                <!-- Pre-filled from URL, make readonly -->
                                                <div class="form-control-plaintext bg-light p-2 rounded">
                                                    <strong>{{ $chartableId }}</strong>
                                                    <small class="text-muted d-block">{{ localize('global.pre_filled_from_link') }}</small>
                                                </div>
                                                <input type="hidden" name="diabetes_chartable_id" value="{{ $chartableId }}">
                                            @else
                                                <!-- Not pre-filled, show input -->
                                                <input type="number" class="form-control @error('diabetes_chartable_id') is-invalid @enderror" 
                                                       id="diabetes_chartable_id" name="diabetes_chartable_id" 
                                                       value="{{ old('diabetes_chartable_id') }}" required>
                                                @error('diabetes_chartable_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($chartable)
                                    <div class="alert alert-info">
                                        <strong>{{ localize('global.linked_to') }}:</strong> 
                                        {{ $chartable->patient->name ?? 'Unknown Patient' }} 
                                        ({{ $chartableType === 'App\\Models\\UnderReview' ? localize('global.under_review') : localize('global.hospitalization') }})
                                    </div>
                                @endif
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
                                           id="rbs" name="rbs" value="{{ old('rbs') }}" 
                                           placeholder="e.g., 180.5">
                                    @error('rbs')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="fbs" class="form-label">{{ localize('global.fbs') }} ({{ localize('global.fasting_blood_sugar') }})</label>
                                    <input type="number" step="0.1" class="form-control @error('fbs') is-invalid @enderror" 
                                           id="fbs" name="fbs" value="{{ old('fbs') }}" 
                                           placeholder="e.g., 95.0">
                                    @error('fbs')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="unit" class="form-label">{{ localize('global.unit') }}</label>
                                    <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit">
                                        <option value="">{{ localize('global.please_select') }}</option>
                                        <option value="mg/dl" {{ old('unit') == 'mg/dl' ? 'selected' : '' }}>{{ localize('global.mg_dl') }}</option>
                                        <option value="mmol/l" {{ old('unit') == 'mmol/l' ? 'selected' : '' }}>{{ localize('global.mmol_l') }}</option>
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
                                           id="insulin_dose" name="insulin_dose" value="{{ old('insulin_dose') }}" 
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
                                            <option value="{{ $medicine->id }}" {{ old('medicine_id') == $medicine->id ? 'selected' : '' }}>
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
                                    <!-- Current user has a nurse record, show it as read-only -->
                                    <div class="form-control-plaintext bg-light p-2 rounded">
                                        <strong>{{ $currentUserNurse->full_name }}</strong> ({{ $currentUserNurse->employee_id }})
                                        <small class="text-muted d-block">{{ localize('global.auto_assigned_from_your_account') }}</small>
                                    </div>
                                    <input type="hidden" name="nurse_id" value="{{ $currentUserNurse->id }}">
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
                                    <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                           id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="time" class="form-label">{{ localize('global.time') }}</label>
                                    <input type="time" class="form-control @error('time') is-invalid @enderror" 
                                           id="time" name="time" value="{{ old('time') }}">
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
                                        <i class="fas fa-save"></i> {{ localize('global.save') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    @endif
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
    
    // Auto-fill current time if not provided
    if (!document.getElementById('time').value) {
        const now = new Date();
        const timeString = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        document.getElementById('time').value = timeString;
    }
</script>
@endpush
