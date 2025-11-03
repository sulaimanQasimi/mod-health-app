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
            
            // Initialize select2 with search functionality for all dropdowns
            initializeSelect2WithAutoFocus();
        })

        function getDistricts(province_id) {
            var provinceID = province_id;
            if (provinceID !== '') {
                $.ajax({
                    url: '/get_districts/' + provinceID,
                    type: 'GET',
                    success: function (response) {
                        $('#district_id').html(response);
                        
                        // Reinitialize Select2 with auto-focus for district dropdown
                        $('#district_id').select2({
                            placeholder: 'Select a district',
                            allowClear: true,
                            width: '100%',
                            minimumInputLength: 0,
                            language: {
                                noResults: function() {
                                    return "No districts found";
                                },
                                searching: function() {
                                    return "Searching districts...";
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
                        
                        // Auto-focus on the district dropdown after it's loaded
                        setTimeout(function() {
                            $('#district_id').select2('open');
                        }, 200);
                    }
                })
            } else {
                $('#district_id').html('<option value="">{{ localize("global.select") }}</option>');
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
                    
                    // Initialize select2 with search functionality and auto focus
                    initializeSelect2WithAutoFocus();
                    
                    // Initialize appointment form functionality
                    initializeAppointmentForm();
                    
                    // Initialize age dropdowns for the loaded tab
                    var tabNum = '';
                    if (tab_type === 'first') {
                        tabNum = 'tab1';
                    } else if (tab_type === 'second') {
                        tabNum = 'tab2';
                    } else if (tab_type === 'third') {
                        tabNum = 'tab3';
                    }
                    
                    if (tabNum) {
                        // Parse existing age value if present
                        var ageInput = document.getElementById('age_' + tabNum);
                        if (ageInput && ageInput.value) {
                            parseAgeToDropdowns(tabNum, ageInput.value);
                        }
                        // Update age value from input fields
                        updateAgeValue(tabNum);
                        
                        // Add oninput event listeners for real-time updates
                        var dayInput = document.getElementById('age_day_' + tabNum);
                        var monthInput = document.getElementById('age_month_' + tabNum);
                        var yearInput = document.getElementById('age_year_' + tabNum);
                        
                        if (dayInput) {
                            dayInput.addEventListener('input', function() { updateAgeValue(tabNum); });
                        }
                        if (monthInput) {
                            monthInput.addEventListener('input', function() { updateAgeValue(tabNum); });
                        }
                        if (yearInput) {
                            yearInput.addEventListener('input', function() { updateAgeValue(tabNum); });
                        }
                    }
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }

        function initializeSelect2WithAutoFocus() {
            // Initialize select2 with search functionality and auto focus for all dropdowns
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder') || 'Select an option';
                },
                allowClear: true,
                width: '100%',
                minimumInputLength: 0,
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
                    
                    // Reinitialize select2 with search functionality and auto focus
                    $(doctorSelect).select2({
                        placeholder: 'Select a doctor',
                        allowClear: true,
                        width: '100%',
                        minimumInputLength: 0,
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

        // AJAX Patient Form Submission
        function submitPatientForm(formId) {
            const form = $('#' + formId);
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            
            // Disable submit button and show loading
            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>{{ localize("global.creating") }}...');
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        showSuccessMessage(response.message);
                        
                        // Clear form fields
                        clearFormFields(formId);
                        
                        // Show token modal if appointment was created
                        if (response.appointment) {
                            showTokenModal(response.patient, response.appointment);
                        }
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // Handle validation errors
                        const errors = xhr.responseJSON.errors;
                        displayValidationErrors(errors);
                    } else {
                        // Handle general errors
                        showErrorMessage('{{ localize("global.error_occurred") }}');
                    }
                },
                complete: function() {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        }

        // Clear form fields after successful submission
        function clearFormFields(formId) {
            const form = $('#' + formId);
            
            // Clear all text inputs
            form.find('input[type="text"], input[type="number"]').val('');
            
            // Clear select2 dropdowns
            form.find('.select2').val(null).trigger('change');
            
            // Reset district dropdown to default state
            $('#district_id').html('<option value="">{{ localize("global.select") }}</option>');
            
            // Reset appointment dropdowns
            $('#appointment_doctor_id').html('<option value="">{{ localize("global.select_doctor_first") }}</option>').prop('disabled', true);
            $('#appointment_department_id').val(null).trigger('change');
            
            // Re-initialize select2 for cleared dropdowns
            initializeSelect2WithAutoFocus();
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
                
                const errorHtml = '<div class="error-message text-danger small mt-1">' + messages[0] + '</div>';
                input.after(errorHtml);
            });
        }

        // Show success message
        function showSuccessMessage(message) {
            // Create and show toast notification
            const toastHtml = `
                <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bx bx-check-circle me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            $('body').append(toastHtml);
            $('.toast').toast('show');
            
            // Remove toast after 5 seconds
            setTimeout(function() {
                $('.toast').remove();
            }, 5000);
        }

        // Show error message
        function showErrorMessage(message) {
            const toastHtml = `
                <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bx bx-error-circle me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            $('body').append(toastHtml);
            $('.toast').toast('show');
            
            // Remove toast after 5 seconds
            setTimeout(function() {
                $('.toast').remove();
            }, 5000);
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

        // Parse age value from string and populate input fields
        function parseAgeToDropdowns(tab, ageString) {
            if (!ageString) return;
            
            const dayInput = document.getElementById('age_day_' + tab);
            const monthInput = document.getElementById('age_month_' + tab);
            const yearInput = document.getElementById('age_year_' + tab);
            
            // Match patterns: "X ساله", "X ماه", "X روز"
            const yearMatch = ageString.match(/^(\d+)\s*ساله/);
            const monthMatch = ageString.match(/^(\d+)\s*ماه/);
            const dayMatch = ageString.match(/^(\d+)\s*روز/);
            
            if (yearMatch && yearInput) {
                yearInput.value = yearMatch[1];
                if (dayInput) dayInput.value = '';
                if (monthInput) monthInput.value = '';
            } else if (monthMatch && monthInput) {
                monthInput.value = monthMatch[1];
                if (dayInput) dayInput.value = '';
                if (yearInput) yearInput.value = '';
            } else if (dayMatch && dayInput) {
                dayInput.value = dayMatch[1];
                if (monthInput) monthInput.value = '';
                if (yearInput) yearInput.value = '';
            }
        }

        // Update age value based on input field values
        function updateAgeValue(tab) {
            const dayInput = document.getElementById('age_day_' + tab);
            const monthInput = document.getElementById('age_month_' + tab);
            const yearInput = document.getElementById('age_year_' + tab);
            const ageInput = document.getElementById('age_' + tab);
            
            let ageValue = '';
            
            // Priority: year > month > day
            if (yearInput && yearInput.value !== '' && yearInput.value !== null) {
                ageValue = yearInput.value + ' ساله';
                // Clear other inputs
                if (dayInput) dayInput.value = '';
                if (monthInput) monthInput.value = '';
            } else if (monthInput && monthInput.value !== '' && monthInput.value !== null) {
                ageValue = monthInput.value + ' ماه';
                // Clear other inputs
                if (dayInput) dayInput.value = '';
                if (yearInput) yearInput.value = '';
            } else if (dayInput && dayInput.value !== '' && dayInput.value !== null) {
                ageValue = dayInput.value + ' روز';
                // Clear other inputs
                if (monthInput) monthInput.value = '';
                if (yearInput) yearInput.value = '';
            }
            
            if (ageInput) {
                ageInput.value = ageValue;
            }
        }

        // Initialize age values on page load
        $(document).ready(function() {
            // Initialize age for all tabs
            ['tab1', 'tab2', 'tab3'].forEach(function(tab) {
                // Check if there's an existing age value from the hidden input
                const ageInput = document.getElementById('age_' + tab);
                if (ageInput && ageInput.value) {
                    // Parse existing age value and populate input fields
                    parseAgeToDropdowns(tab, ageInput.value);
                }
                // Update the age value from input fields
                updateAgeValue(tab);
                
                // Add oninput event listeners for real-time updates
                const dayInput = document.getElementById('age_day_' + tab);
                const monthInput = document.getElementById('age_month_' + tab);
                const yearInput = document.getElementById('age_year_' + tab);
                
                if (dayInput) {
                    dayInput.addEventListener('input', function() { updateAgeValue(tab); });
                }
                if (monthInput) {
                    monthInput.addEventListener('input', function() { updateAgeValue(tab); });
                }
                if (yearInput) {
                    yearInput.addEventListener('input', function() { updateAgeValue(tab); });
                }
            });
        });

        // Bind AJAX form submission to all patient forms
        $(document).on('submit', '#patient-form-tab1, #patient-form-tab2, #patient-form-tab3', function(e) {
            e.preventDefault();
            const formId = $(this).attr('id');
            // Update age value before submission
            if (formId === 'patient-form-tab1') {
                updateAgeValue('tab1');
            } else if (formId === 'patient-form-tab2') {
                updateAgeValue('tab2');
            } else if (formId === 'patient-form-tab3') {
                updateAgeValue('tab3');
            }
            submitPatientForm(formId);
        });

    </script>
@endsection