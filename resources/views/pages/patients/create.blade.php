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
                        @if(isset($patient))
                            <h5 class="mb-0">{{ localize('global.edit_patient') }}</h5>
                        @else
                            <h5 class="mb-0">{{ localize('global.create_patient') }}</h5>

                        @endif


                    </div>

                    <div class="card-body">
                        <div class="nav-align-top mb-4">
                            <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
                                <li class="nav-item">
                                    <button type="button"
                                        class="nav-link {{ isset($patient) && $patient->type != '0' ? 'disabled' : ''}} {{ isset($patient) && $patient->type == '0' ? 'active' : (Route::currentRouteName() == 'patients.create' ? 'active' : '') }} fs-4"
                                        onclick="getTab('first')" role="tab" data-bs-toggle="tab" data-bs-target="#first"
                                        aria-controls="first"
                                        aria-selected="{{ isset($patient) && $patient->type == '0' ? 'true' : (Route::currentRouteName() == 'patients.create' ? 'true' : 'false') }}">
                                        {{localize('global.mod')}}
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button"
                                        class="nav-link {{ isset($patient) && $patient->type != '1' ? 'disabled' : ''}}  {{ isset($patient) && $patient->type == '1' ? 'active' : '' }} fs-4"
                                        role="tab" onclick="getTab('second')" data-bs-toggle="tab" data-bs-target="#second"
                                        aria-controls="second"
                                        aria-selected="{{ isset($patient) && $patient->type == '1' ? 'true' : 'false' }}">
                                        {{localize('global.recipient')}}

                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button"
                                        class="nav-link {{ isset($patient) && $patient->type != '2' ? 'disabled' : ''}}  {{ isset($patient) && $patient->type == '2' ? 'active' : '' }} fs-4"
                                        role="tab" onclick="getTab('third')" data-bs-toggle="tab" data-bs-target="#third"
                                        aria-controls="third"
                                        aria-selected="{{ isset($patient) && $patient->type == '2' ? 'true' : 'false' }}">
                                        {{localize('global.family')}}

                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade {{ isset($patient) && $patient->type == '0' ? 'show active' : (Route::currentRouteName() == 'patients.create' ? 'show active' : '') }}"
                                    id="first" role="tabpanel">
                                </div>
                                <div class="tab-pane fade {{ isset($patient) && $patient->type == '1' ? 'show active' : '' }}"
                                    id="second" role="tabpanel">
                                </div>
                                <div class="tab-pane fade {{ isset($patient) && $patient->type == '2' ? 'show active' : '' }}"
                                    id="third" role="tabpanel">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@php
    if (isset($patient) && $patient->type == '0') {
        $tab_name = 'first';
    } elseif (isset($patient) && $patient->type == '1') {
        $tab_name = 'second';
    } elseif (isset($patient) && $patient->type == '2') {
        $tab_name = 'third';
    } else {
        $tab_name = 'first';
    }

@endphp
@section('scripts')
    <script>
        function changeType(emp_type) {
            if (emp_type == '0') {
                label = "{{ localize('global.rank') }}";
            } else {
                label = "{{ localize('global.bast') }}";
            }
            $('#rank_label').html(label);
        }
        $(document).ready(function () {
            getTab('{{$tab_name}}');
        })

        function getDistricts(province_id) {
            var provinceID = province_id;
            if (provinceID !== '') {
                $.ajax({
                    url: '/get_districts/' + provinceID,
                    type: 'GET',
                    success: function (response) {

                        $('#district_id').html(response);
                    }
                })
            }
        }

        function getTab(tab_type) {
            $('#first').html('');
            $('#second').html('');
            $('#third').html('');
            $.ajax({
                url: " {{url('patients/get-tab')}}",
                type: 'GET',
                data: {
                    tab_type: tab_type,
                    patient_id: '{{isset($patient) ? $patient->id : ''}}',
                },
                success: function (data) {
                    // Update the container with the response
                    var tab_id = '#' + tab_type;
                    $(tab_id).html(data);
                    $('.select2').select2();
                    
                    // Initialize appointment form functionality
                    initializeAppointmentForm();
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }

        function initializeAppointmentForm() {
            // Initialize select2 for doctor dropdown
            $('.select2').select2();
        }

        function loadDoctorsByDepartment(departmentId) {
            const doctorSelect = document.getElementById('appointment_doctor_id');
            
            if (departmentId === '') {
                doctorSelect.innerHTML = '<option value="">{{ localize("global.select_doctor_first") }}</option>';
                doctorSelect.disabled = true;
                return;
            }

            // Show loading state
            doctorSelect.innerHTML = '<option value="">{{ localize("global.loading") }}...</option>';
            doctorSelect.disabled = true;

            $.ajax({
                url: '/patients/get-doctors-by-department/' + departmentId,
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
                    
                    // Reinitialize select2
                    $(doctorSelect).select2();
                },
                error: function (xhr, status, error) {
                    console.error('Error loading doctors:', error);
                    doctorSelect.innerHTML = '<option value="">{{ localize("global.error_loading_doctors") }}</option>';
                }
            });
        }



    </script>
@endsection