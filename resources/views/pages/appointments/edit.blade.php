@extends('layouts.master')

@section('content')
    <div class="container-fluid py-4">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">{{ localize('global.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}" class="text-decoration-none">{{ localize('global.appointments') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('appointments.show', $appointment) }}" class="text-decoration-none">{{ localize('global.appointment_details') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ localize('global.edit_appointment') }}</li>
                    </ol>
                </nav>
                
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h4 mb-0">
                        <i class="bx bx-edit me-2 text-primary"></i>
                        {{ localize('global.edit_appointment') }}
                    </h2>
                    <div class="d-flex gap-2">
                        <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bx bx-arrow-back me-1"></i>
                            {{ localize('global.back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Appointment Form -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light text-dark">
                        <h5 class="mb-0">
                            <i class="bx bx-calendar-edit me-2 text-primary"></i>
                            {{ localize('global.edit_appointment_details') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('appointments.update', $appointment) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <!-- Patient Selection -->
                                <div class="col-md-6 mb-3">
                                    <label for="patient_id" class="form-label fw-bold">
                                        <i class="bx bx-user me-1 text-primary"></i>
                                        {{ localize('global.patient') }} <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control select2 @error('patient_id') is-invalid @enderror" 
                                            name="patient_id" id="patient_id" required>
                                        <option value="">{{ localize('global.select_patient') }}</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" 
                                                {{ old('patient_id', $appointment->patient_id) == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->name }} - {{ $patient->phone }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Doctor Selection -->
                                <div class="col-md-6 mb-3">
                                    <label for="doctor_id" class="form-label fw-bold">
                                        <i class="bx bx-user-check me-1 text-primary"></i>
                                        {{ localize('global.doctor') }} <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control select2 @error('doctor_id') is-invalid @enderror" 
                                            name="doctor_id" id="doctor_id" required>
                                        <option value="">{{ localize('global.select_doctor') }}</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" 
                                                {{ old('doctor_id', $appointment->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                                {{ $doctor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('doctor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Branch Selection -->
                                <div class="col-md-6 mb-3">
                                    <label for="branch_id" class="form-label fw-bold">
                                        <i class="bx bx-building me-1 text-primary"></i>
                                        {{ localize('global.branch') }} <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control select2 @error('branch_id') is-invalid @enderror" 
                                            name="branch_id" id="branch_id" required>
                                        <option value="">{{ localize('global.select_branch') }}</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" 
                                                {{ old('branch_id', $appointment->branch_id) == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Date -->
                                <div class="col-md-6 mb-3">
                                    <label for="date" class="form-label fw-bold">
                                        <i class="bx bx-calendar me-1 text-primary"></i>
                                        {{ localize('global.date') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                           name="date" id="date" 
                                           value="{{ old('date', $appointment->date) }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Time -->
                                <div class="col-md-6 mb-3">
                                    <label for="time" class="form-label fw-bold">
                                        <i class="bx bx-time me-1 text-primary"></i>
                                        {{ localize('global.time') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" class="form-control @error('time') is-invalid @enderror" 
                                           name="time" id="time" 
                                           value="{{ old('time', $appointment->time) }}" required>
                                    @error('time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Referral Remarks -->
                                <div class="col-md-12 mb-3">
                                    <label for="refferal_remarks" class="form-label fw-bold">
                                        <i class="bx bx-message-square-detail me-1 text-primary"></i>
                                        {{ localize('global.referral_remarks') }}
                                    </label>
                                    <textarea class="form-control @error('refferal_remarks') is-invalid @enderror" 
                                              name="refferal_remarks" id="refferal_remarks" 
                                              rows="3" placeholder="{{ localize('global.enter_referral_remarks') }}">{{ old('refferal_remarks', $appointment->refferal_remarks) }}</textarea>
                                    @error('refferal_remarks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('appointments.show', $appointment) }}" 
                                           class="btn btn-secondary">
                                            <i class="bx bx-x me-1"></i>
                                            {{ localize('global.cancel') }}
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i>
                                            {{ localize('global.update') }}
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

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Form validation
            $('form').on('submit', function(e) {
                var patientId = $('#patient_id').val();
                var doctorId = $('#doctor_id').val();
                var branchId = $('#branch_id').val();
                var date = $('#date').val();
                var time = $('#time').val();

                if (!patientId || !doctorId || !branchId || !date || !time) {
                    e.preventDefault();
                    alert('{{ localize("global.please_fill_all_required_fields") }}');
                    return false;
                }
            });
        });
    </script>
@endsection

