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
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 text-white">
                            <i class="bx bx-calendar-edit me-2"></i>
                            {{ localize('global.edit_appointment_details') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('appointments.update', $appointment) }}" method="POST" class="needs-validation" novalidate>
                            @csrf
                            @method('PUT')
                            
                            <!-- Patient Information Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3 ">
                                        <i class="bx bx-user me-2"></i>
                                        {{ localize('global.patient_information') }}
                                    </h6>
                                </div>
                                
                                <!-- Patient Selection -->
                                <div class="col-md-6 mb-3 border p-2">
                                    <label for="patient_id" class="form-label fw-semibold">
                                        {{ localize('global.patient') }} <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select border @error('patient_id') is-invalid @enderror" 
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
                                <div class="col-md-6 mb-3 border p-2">
                                    <label for="doctor_id" class="form-label fw-semibold">
                                        {{ localize('global.doctor') }} <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select border @error('doctor_id') is-invalid @enderror" 
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
                            </div>

                            <!-- Appointment Details Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="bx bx-calendar me-2"></i>
                                        {{ localize('global.appointment_details') }}
                                    </h6>
                                </div>

                                <!-- Branch Selection -->
                                <div class="col-md-6 mb-3 border p-2">
                                    <label for="branch_id" class="form-label fw-semibold">
                                        {{ localize('global.branch') }} <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select border @error('branch_id') is-invalid @enderror" 
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
                                <div class="col-md-6 mb-3 border p-2">
                                    <label for="date" class="form-label fw-semibold">
                                        {{ localize('global.date') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control datepicker_dari @error('date') is-invalid @enderror" 
                                           name="date" id="date" 
                                           placeholder="{{ localize('global.select_date') }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Time -->
                                <div class="col-md-6 mb-3 border p-2">
                                    <label for="time" class="form-label fw-semibold">
                                        {{ localize('global.time') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" class="form-control border @error('time') is-invalid @enderror" 
                                           name="time" id="time" 
                                           value="{{ old('time', $appointment->time) }}" required>
                                    @error('time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Additional Information Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="bx bx-message-square-detail me-2"></i>
                                        {{ localize('global.additional_information') }}
                                    </h6>
                                </div>

                                <!-- Referral Remarks -->
                                <div class="col-12 mb-3">
                                    <label for="refferal_remarks" class="form-label fw-semibold">
                                        {{ localize('global.referral_remarks') }}
                                    </label>
                                    <textarea class="form-control border @error('refferal_remarks') is-invalid @enderror" 
                                              name="refferal_remarks" id="refferal_remarks" 
                                              rows="4" placeholder="{{ localize('global.enter_referral_remarks') }}">{{ old('refferal_remarks', $appointment->refferal_remarks) }}</textarea>
                                    @error('refferal_remarks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-3">
                                        <a href="{{ route('appointments.show', $appointment) }}" 
                                           class="btn btn-outline-secondary">
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

@push('custom-js')
    <script src="{{ asset('ShamsiCalender/js/persianDatepicker.js') }}"></script>
@endpush

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Persian date picker for date inputs
            $('.datepicker_dari').each(function() {
                var $this = $(this);
                
                // Clear any existing value that might cause issues
                $this.val('');
                
                $this.persianDatepicker({
                    formatDate: 'YYYY-MM-DD',
                    calendar: {
                        persian: {
                            locale: 'en',
                            showHint: true,
                            leapYearMode: 'algorithmic'
                        }
                    },
                    checkDate: function(unix) {
                        return true;
                    }
                });
            });
            // Initialize Select2 with better styling
            $('.form-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder') || '{{ localize("global.select") }}';
                }
            });

            // Bootstrap form validation
            const forms = document.querySelectorAll('.needs-validation');
            
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            // Custom form validation
            $('form').on('submit', function(e) {
                var patientId = $('#patient_id').val();
                var doctorId = $('#doctor_id').val();
                var branchId = $('#branch_id').val();
                var date = $('#date').val();
                var time = $('#time').val();

                if (!patientId || !doctorId || !branchId || !date || !time) {
                    e.preventDefault();
                    
                    // Show validation messages
                    if (!patientId) {
                        $('#patient_id').addClass('is-invalid');
                        $('#patient_id').siblings('.invalid-feedback').text('{{ localize("global.please_select_patient") }}');
                    }
                    if (!doctorId) {
                        $('#doctor_id').addClass('is-invalid');
                        $('#doctor_id').siblings('.invalid-feedback').text('{{ localize("global.please_select_doctor") }}');
                    }
                    if (!branchId) {
                        $('#branch_id').addClass('is-invalid');
                        $('#branch_id').siblings('.invalid-feedback').text('{{ localize("global.please_select_branch") }}');
                    }
                    if (!date) {
                        $('#date').addClass('is-invalid');
                        $('#date').siblings('.invalid-feedback').text('{{ localize("global.please_select_date") }}');
                    }
                    if (!time) {
                        $('#time').addClass('is-invalid');
                        $('#time').siblings('.invalid-feedback').text('{{ localize("global.please_select_time") }}');
                    }
                    
                    return false;
                }
            });

            // Remove validation classes on input
            $('input, select, textarea').on('input change', function() {
                $(this).removeClass('is-invalid');
            });
        });
    </script>
@endsection

