@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.view_patient') }}</h5>
                    <div class="d-flex gap-2">
                        @can('edit-patients')
                        <a href="{{ route('patients.edit', $patient) }}" class="btn btn-warning btn-sm">
                            <i class="bx bx-edit"></i> {{ localize('global.edit') }}
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
            <div class="container">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-3">

                                    <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                                    <div>
                                        {{$patient->name}}
                                    </div>
                            </div>
                            <div class="col-md-3">
                                <h5 class="mb-2">{{ localize('global.last_name') }}</h5>
                                    <div>
                                        {{$patient->last_name}}
                                    </div>
                            </div>
                            <div class="col-md-3">
                                <h5 class="mb-2">{{ localize('global.phone') }}</h5>
                                    <div>
                                        {{$patient->phone}}
                                    </div>
                            </div>
                            <div class="col-md-3">
                                <h5 class="mb-2">{{ localize('global.nid') }}</h5>
                                    <div>
                                        {{$patient->nid}}
                                    </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-3">
                                <h5 class="mb-2">{{ localize('global.age') }}</h5>
                                    <div>
                                        {{$patient->age ?? '-'}}
                                    </div>
                            </div>
                            <div class="col-md-3">
                                <h5 class="mb-2">{{ localize('global.province') }}</h5>
                                    <div>
                                        {{$patient->province->name_dr}}
                                    </div>
                            </div>
                            <div class="col-md-3">
                                <h5 class="mb-2">{{ localize('global.district') }}</h5>
                                    <div>
                                        {{$patient->district->name_dr}}
                                    </div>
                            </div>
                            <div class="col-md-3">
                                <h5 class="mb-2">{{ localize('global.referred_by') }}</h5>
                                    <div>
                                        {{$patient->recipient->name ?? $patient->referral_name}}
                                    </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-3">
                                <h5 class="mb-2">{{ localize('global.creation_date') }}</h5>
                                    <div>
                                        {{verta($patient->created_at)->format('Y-m-d') }}
                                    </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card p-3">
                            <div class="row">
                                <div class="col-md-6 text-center">
                                    <div class="mb-2">
                                        <small class="text-muted">{{ localize('global.qr_code') }}</small>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        {!! QrCode::size(100)->generate($patient->id) !!}
                                    </div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <div class="mb-2">
                                        <small class="text-muted">{{ localize('global.patient_image') }}</small>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        @isset($patient->image)
                                            <img src="{{ asset($patient->image) }}" alt="Patient Image" width="100" height="100" class="rounded">
                                        @else
                                            <div class="badge bg-label-danger p-3">
                                                {{ localize('global.no_image') }}
                                            </div>
                                        @endisset
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="d-grid gap-2">
                                        @can('print-patient-card')
                                        <a href="{{ route('patients.print-card', $patient->id) }}" target="_blank" class="btn btn-primary btn-sm">
                                            <i class="bx bx-printer"></i> {{localize('global.print_card')}}
                                        </a>
                                        @endcan
                                        
                                        @can('create-appointment')
                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#createAppointmentModal">
                                            <i class="bx bx-calendar-plus"></i> {{localize('global.assign_appointment')}}
                                        </button>
                                        @endcan
                                        
                                        @can('upload-patient-image')
                                        <a class="btn btn-success btn-sm" href="{{route('patients.webcam',$patient)}}">
                                            <i class="bx bx-camera"></i> {{localize('global.take_image')}}
                                        </a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

                <hr>
                <h5 class="mb-0 p-3 bg-label-primary">{{ localize('global.previous_appointments') }}</h5>

                <table class="table">
                    <thead>
                        <tr>
                            <th>{{localize('global.number')}}</th>
                            <th>{{localize('global.doctor_name')}}</th>
                            <th>{{localize('global.date')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patient->appointments as $appointment)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$appointment->doctor?->name}}</td>
                            <td>{{ verta($appointment->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @can('access-nephrology-registrations')
                <hr>
                <h5 class="mb-0 p-3 bg-label-primary d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span>{{ localize('global.nephrology_history') }}</span>
                    <a href="{{ route('hemodialysis-sessions.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-plus"></i> {{ localize('global.add_hemodialysis_session') }}
                    </a>
                </h5>
                <div class="p-3">
                    <h6 class="text-muted">{{ localize('global.nephrology_registrations') }}</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm border-top">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.ref_no') }}</th>
                                    <th>{{ localize('global.visit_date') }}</th>
                                    <th>{{ localize('global.doctor') }}</th>
                                    <th>{{ localize('global.diagnosis') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nephrologyRegistrations ?? [] as $registration)
                                    <tr>
                                        <td>{{ $registration->ref_no }}</td>
                                        <td>{{ $registration->visit_date ? \HanifHefaz\Dcter\Dcter::GregorianToJalali($registration->visit_date) : '—' }}</td>
                                        <td>{{ $registration->doctor->name ?? '—' }}</td>
                                        <td>{{ Str::limit($registration->diagnosis, 50) ?: '—' }}</td>
                                        <td>
                                            <a href="{{ route('nephrology-registrations.show', $registration) }}" class="btn btn-sm btn-primary">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">{{ localize('global.no_registrations_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <h6 class="text-muted">{{ localize('global.hemodialysis_sessions') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm border-top">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.ref_no') }}</th>
                                    <th>{{ localize('global.session_date') }}</th>
                                    <th>{{ localize('global.duration_minutes') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hemodialysisSessions ?? [] as $hdSession)
                                    <tr>
                                        <td>{{ $hdSession->ref_no }}</td>
                                        <td>{{ $hdSession->session_date ? \HanifHefaz\Dcter\Dcter::GregorianToJalali($hdSession->session_date) : '—' }}</td>
                                        <td>{{ $hdSession->duration_minutes ?? '—' }}</td>
                                        <td><span class="badge bg-info">{{ localize('global.' . $hdSession->status) }}</span></td>
                                        <td>
                                            <a href="{{ route('hemodialysis-sessions.show', $hdSession) }}" class="btn btn-sm btn-primary">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">{{ localize('global.no_hemodialysis_sessions_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(($hemodialysisSessions ?? collect())->isNotEmpty())
                        <a href="{{ route('hemodialysis-sessions.index', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-outline-primary">
                            {{ localize('global.view_all_hemodialysis_sessions') }}
                        </a>
                    @endif
                </div>
                @endcan
                <hr>
                <h5 class="mb-0 p-3 bg-label-primary">{{ localize('global.all_diagnoses') }}</h5>
                <div class="row p-4">
                    <div class="mb-4">
                        @php
                            $primaryDiagnoses = $previousDiagnoses->where('type', 0);
                            $finalDiagnoses = $previousDiagnoses->where('type', 1);
                        @endphp

                        <div class="container">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="col-md-12">
                                            <h5 class="mb-4 p-1 bg-label-warning text-center"><i
                                                    class="bx bx-popsicle p-1"></i>{{ localize('global.primary_diagnoses') }}
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="col-md-12">
                                            <h5 class="mb-4 p-1 bg-label-success text-center"><i
                                                    class="bx bx-popsicle p-1"></i>{{ localize('global.final_diagnoses') }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        @foreach ($primaryDiagnoses as $diagnose)
                                            <li class="m-1 p-1">
                                                <span
                                                    class="bg-label-warning text-center p-1">{{ verta($diagnose->created_at)->format('Y-m-d') }}</span>
                                                {{ $diagnose->description }}
                                            </li>
                                        @endforeach
                                    </div>
                                    <div class="col-md-6">
                                        @foreach ($finalDiagnoses as $diagnose)
                                            <li class="m-1 p-1">
                                                <span
                                                    class="bg-label-success text-center p-1">{{ verta($diagnose->created_at)->format('Y-m-d') }}</span>
                                                {{ $diagnose->description }}
                                            </li>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- <table class="table">
                    <thead>
                        <tr>
                            <th>{{localize('global.number')}}</th>
                            <th>{{localize('global.doctor_name')}}</th>
                            <th>{{localize('global.description')}}</th>
                            <th>{{localize('global.date')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patient->diagnoses as $diagnose)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$diagnose->doctor->name}}</td>
                            <td>{{$diagnose->description}}</td>
                            <td>{{$diagnose->created_at}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table> --}}
            </div>
            </div>
        </div>
        <div class="modal fade" id="createAppointmentModal" tabindex="-1" aria-labelledby="createAppointmentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createAppointmentModalLabel">{{localize('global.create_appointment')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="createAppointmentForm">
                            @csrf
                            @if(auth()->user()->clinic_type === 'both')
                            <div class="mb-3">
                                <label for="clinic_type">{{ localize('global.clinic_type') }} <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="clinic_type" id="create_appointment_clinic_type" required onchange="loadDoctorsByDepartmentFromSelect()">
                                    <option value="">{{ localize('global.select') }}...</option>
                                    <option value="hospital">{{ localize('global.hospital') }}</option>
                                    <option value="clinic">{{ localize('global.clinic') }}</option>
                                </select>
                            </div>
                            @endif
                            <div class="mb-3">
                                <label for="department_id">{{localize('global.department')}} <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="department_id" id="department_id" required onchange="loadDoctorsByDepartmentFromSelect()">
                                    <option value="">{{ localize('global.select_department') }}</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="doctor_id">{{localize('global.doctor')}} <small class="text-muted">({{ localize('global.optional') }})</small></label>
                                <select class="form-control select2" name="doctor_id" id="doctor_id" disabled>
                                    <option value="">{{ localize('global.select_doctor_first') }}</option>
                                </select>
                            </div>
                            <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                            <input type="hidden" name="is_completed" value="0">
                            <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{localize('global.cancel')}}</button>
                        <button type="submit" class="btn btn-primary" form="createAppointmentForm">{{localize('global.create')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Token Modal -->
<div class="modal fade" id="tokenModal" tabindex="-1" aria-labelledby="tokenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tokenModalLabel">
                    <i class="bx bx-printer me-2 text-success"></i>
                    {{ localize('global.token_ready') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">{{ localize('global.patient_information') }}</h6>
                        <p><strong>{{ localize('global.name') }}:</strong> <span id="modal-patient-name"></span></p>
                        <p><strong>{{ localize('global.last_name') }}:</strong> <span id="modal-patient-lastname"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">{{ localize('global.appointment_information') }}</h6>
                        <p><strong>{{ localize('global.department') }}:</strong> <span id="modal-appointment-department"></span></p>
                        <p><strong>{{ localize('global.doctor') }}:</strong> <span id="modal-appointment-doctor"></span></p>
                        <p><strong>{{ localize('global.date') }}:</strong> <span id="modal-appointment-date"></span></p>
                        <p><strong>{{ localize('global.time') }}:</strong> <span id="modal-appointment-time"></span></p>
                    </div>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="bx bx-info-circle me-2"></i>
                    {{ localize('global.token_ready_message') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>
                    {{ localize('global.close') }}
                </button>
                <button type="button" class="btn btn-success" id="printTokenBtn">
                    <i class="bx bx-printer me-1"></i>
                    {{ localize('global.print_token') }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    /* Ensure Select2 dropdown appears above modal */
    .select2-container--open .select2-dropdown {
        z-index: 9999 !important;
    }
    
    .select2-container {
        z-index: 9999 !important;
    }
    
    /* Ensure modal backdrop doesn't interfere */
    .modal-backdrop {
        z-index: 1040;
    }
    
    .modal {
        z-index: 1050;
    }
</style>
@endsection

@section('scripts')

<script>
    $(document).ready(function() {
        // Initialize Select2 when modal is shown
        $('#createAppointmentModal').on('shown.bs.modal', function () {
            initializeSelect2WithAutoFocus();
        });

        // Initialize appointment form functionality
        initializeAppointmentForm();
    });

    // Initialize select2 with search functionality and auto focus
    function initializeSelect2WithAutoFocus() {
        $('.select2').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%',
            minimumInputLength: 0,
            dropdownParent: $('#createAppointmentModal'),
            language: {
                noResults: function() {
                    return "No results found";
                },
                searching: function() {
                    return "Searching...";
                },
                inputTooShort: function() {
                    return "Type to search";
                }
            }
        }).on('select2:open', function() {
            // Auto focus on search input when dropdown opens
            setTimeout(function() {
                $('.select2-search__field').focus();
            }, 100);
        });
    }

    function initializeAppointmentForm() {
        // Initialize select2 with search functionality and auto focus
        initializeSelect2WithAutoFocus();
    }

    function loadDoctorsByDepartmentFromSelect() {
        const departmentId = $('#department_id').val();
        @if(auth()->user()->clinic_type === 'both')
        const clinicType = $('#create_appointment_clinic_type').val();
        if (!clinicType) {
            document.getElementById('doctor_id').innerHTML = '<option value="">{{ localize("global.select_clinic_type_first") }}</option>';
            document.getElementById('doctor_id').disabled = true;
            return;
        }
        @endif
        loadDoctorsByDepartment(departmentId);
    }

    function loadDoctorsByDepartment(departmentId) {
        const doctorSelect = document.getElementById('doctor_id');
        
        if (departmentId === '') {
            doctorSelect.innerHTML = '<option value="">{{ localize("global.select_doctor_first") }}</option>';
            doctorSelect.disabled = true;
            return;
        }

        // Show loading state
        doctorSelect.innerHTML = '<option value="">{{ localize("global.loading") }}...</option>';
        doctorSelect.disabled = true;

        let url = '/patients/get-doctors-by-department/' + departmentId;
        @if(auth()->user()->clinic_type === 'both')
        const clinicType = $('#create_appointment_clinic_type').val();
        if (clinicType) {
            url += '?clinic_type=' + encodeURIComponent(clinicType);
        }
        @endif

        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                doctorSelect.innerHTML = '<option value="">{{ localize("global.select_doctor") }}</option>';
                
                if (response.success && response.doctors && response.doctors.length > 0) {
                    response.doctors.forEach(function(doctor) {
                        const option = document.createElement('option');
                        option.value = doctor.id;
                        option.textContent = doctor.name;
                        doctorSelect.appendChild(option);
                    });
                    doctorSelect.disabled = false;
                } else {
                    doctorSelect.innerHTML = '<option value="">{{ localize("global.no_doctors_available") }}</option>';
                }
                
                // Reinitialize select2 with search functionality and auto focus
                $(doctorSelect).select2({
                    placeholder: 'Select a doctor',
                    allowClear: true,
                    width: '100%',
                    minimumInputLength: 0,
                    dropdownParent: $('#createAppointmentModal'),
                    language: {
                        noResults: function() {
                            return "No doctors found";
                        },
                        searching: function() {
                            return "Searching doctors...";
                        },
                        inputTooShort: function() {
                            return "Type to search";
                        }
                    }
                }).on('select2:open', function() {
                    // Auto focus on search input when dropdown opens
                    setTimeout(function() {
                        $('.select2-search__field').focus();
                    }, 100);
                });
            },
            error: function (xhr, status, error) {
                console.error('Error loading doctors:', error);
                doctorSelect.innerHTML = '<option value="">{{ localize("global.error_loading_doctors") }}</option>';
            }
        });
    }

    // Global event listener for any select2 dropdowns that might be added dynamically
    $(document).on('select2:open', '.select2', function() {
        // Auto focus on search input when any select2 dropdown opens
        setTimeout(function() {
            $('.select2-search__field').focus();
        }, 100);
    });

    // AJAX Appointment Form Submission
    function submitAppointmentForm() {
        const form = $('#createAppointmentForm');
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        
        // Disable submit button and show loading
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>{{ localize("global.creating") }}...');
        
        // Clear previous error messages
        $('.error-message').remove();
        $('.is-invalid').removeClass('is-invalid');
        
        $.ajax({
            url: '{{ route("appointments.store") }}',
            type: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Close the appointment modal
                    $('#createAppointmentModal').modal('hide');
                    
                    // Show token modal with appointment data
                    showTokenModal(response.patient, response.appointment);
                } else {
                    // Show validation errors
                    if (response.errors) {
                        displayValidationErrors(response.errors);
                    } else {
                        alert(response.message || 'Error creating appointment');
                    }
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    displayValidationErrors(errors);
                } else {
                    alert('Error creating appointment. Please try again.');
                }
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    }

    // Show token modal with appointment data
    function showTokenModal(patient, appointment) {
        // Populate modal with data
        $('#modal-patient-name').text(patient.name);
        $('#modal-patient-lastname').text(patient.last_name || '');
        $('#modal-appointment-department').text(appointment.department);
        $('#modal-appointment-doctor').text(appointment.doctor);
        $('#modal-appointment-date').text(appointment.date);
        $('#modal-appointment-time').text(appointment.time);
        
        // Set up print token button
        $('#printTokenBtn').off('click').on('click', function() {
            window.open(appointment.token_url, '_blank');
        });
        
        // Show modal
        $('#tokenModal').modal('show');
    }

    // Display validation errors
    function displayValidationErrors(errors) {
        // Clear previous error messages
        $('.error-message').remove();
        $('.is-invalid').removeClass('is-invalid');
        
        // Display new errors
        $.each(errors, function(field, messages) {
            const input = $('[name="' + field + '"]');
            input.addClass('is-invalid');
            
            // Add error message below the field
            const errorHtml = '<div class="error-message text-danger small mt-1">' + messages[0] + '</div>';
            input.closest('.mb-3').append(errorHtml);
        });
    }

    // Bind AJAX form submission to appointment form
    $(document).on('submit', '#createAppointmentForm', function(e) {
        e.preventDefault();
        submitAppointmentForm();
    });
</script>

@endsection

