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
                        <h5 class="mb-0">{{ localize('global.edit_patient') }}</h5>
                        <a href="{{ route('patients.show', $patient) }}" class="btn btn-secondary btn-sm">
                            <i class="bx bx-arrow-back"></i> {{ localize('global.back') }}
                        </a>
                    </div>

                    <div class="card-body">
                        @php
                            $canAccessVip = auth()->user()->hasRole(['super_admin', 'admin']) || auth()->user()->can('access-to-vip');
                        @endphp
                        <div class="nav-align-top mb-4">
                            <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
                                <li class="nav-item">
                                    <button type="button"
                                        class="nav-link {{ $patient->type == '0' ? 'active' : '' }} fs-4"
                                        onclick="getTab('first')" role="tab" data-bs-toggle="tab" data-bs-target="#first"
                                        aria-controls="first"
                                        aria-selected="{{ $patient->type == '0' ? 'true' : 'false' }}">
                                        {{localize('global.mod')}}
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button"
                                        class="nav-link {{ $patient->type == '1' ? 'active' : '' }} fs-4"
                                        role="tab" onclick="getTab('second')" data-bs-toggle="tab" data-bs-target="#second"
                                        aria-controls="second"
                                        aria-selected="{{ $patient->type == '1' ? 'true' : 'false' }}">
                                        {{localize('global.recipient')}}
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button"
                                        class="nav-link {{ $patient->type == '3' ? 'active' : '' }} fs-4"
                                        role="tab" onclick="getTab('extraordinary')" data-bs-toggle="tab" data-bs-target="#extraordinary"
                                        aria-controls="extraordinary"
                                        aria-selected="{{ $patient->type == '3' ? 'true' : 'false' }}">
                                        {{localize('global.extraordinary')}}
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button"
                                        class="nav-link {{ $patient->type == '2' ? 'active' : '' }} fs-4"
                                        role="tab" onclick="getTab('third')" data-bs-toggle="tab" data-bs-target="#third"
                                        aria-controls="third"
                                        aria-selected="{{ $patient->type == '2' ? 'true' : 'false' }}">
                                        {{localize('global.family')}}
                                    </button>
                                </li>
                                @if($canAccessVip)
                                <li class="nav-item">
                                    <button type="button"
                                        class="nav-link {{ $patient->type == '4' ? 'active' : '' }} fs-4"
                                        role="tab" onclick="getTab('vip')" data-bs-toggle="tab" data-bs-target="#vip"
                                        aria-controls="vip"
                                        aria-selected="{{ $patient->type == '4' ? 'true' : 'false' }}">
                                        {{localize('global.vip')}}
                                    </button>
                                </li>
                                @endif
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade {{ $patient->type == '0' ? 'show active' : '' }}" id="first" role="tabpanel"></div>
                                <div class="tab-pane fade {{ $patient->type == '1' ? 'show active' : '' }}" id="second" role="tabpanel"></div>
                                <div class="tab-pane fade {{ $patient->type == '3' ? 'show active' : '' }}" id="extraordinary" role="tabpanel"></div>
                                <div class="tab-pane fade {{ $patient->type == '2' ? 'show active' : '' }}" id="third" role="tabpanel"></div>
                                @if($canAccessVip)
                                <div class="tab-pane fade {{ $patient->type == '4' ? 'show active' : '' }}" id="vip" role="tabpanel"></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@php
    $canAccessVipForTab = auth()->user()->hasRole(['super_admin', 'admin']) || auth()->user()->can('access-to-vip');
    if ($patient->type == '0') {
        $tab_name = 'first';
    } elseif ($patient->type == '1') {
        $tab_name = 'second';
    } elseif ($patient->type == '3') {
        $tab_name = 'extraordinary';
    } elseif ($patient->type == '2') {
        $tab_name = 'third';
    } elseif ($patient->type == '4' && $canAccessVipForTab) {
        $tab_name = 'vip';
    } else {
        $tab_name = 'first';
    }
@endphp
@section('scripts')
    <script>
        function changeType(emp_type) {
            // Handle rank label update (for tab2)
            const rankLabel = document.getElementById('rank_label');
            if (rankLabel) {
                if (emp_type == '0') {
                    rankLabel.innerHTML = "{{ localize('global.rank') }}";
                } else {
                    rankLabel.innerHTML = "{{ localize('global.bast') }}";
                }
            }
            
            // Handle tab1 elements (military type div, rank div) - only if they exist
            const militeryTypeDiv = document.getElementById('militery_type_div');
            const rankDiv = document.getElementById('rank_div');
            const militeryTypeSelect = document.getElementById('militery_type_id');
            const rankInput = document.getElementById('rank');
            
            if (militeryTypeDiv && rankDiv && militeryTypeSelect && rankInput) {
                // This is tab1, handle the display logic
                if (emp_type === '0') { // Military
                    militeryTypeDiv.style.display = 'block';
                    rankDiv.style.display = 'none';
                    militeryTypeSelect.required = true;
                    rankInput.required = false;
                    rankInput.value = ''; // Clear rank value when switching to military
                } else { // Civilian
                    militeryTypeDiv.style.display = 'none';
                    rankDiv.style.display = 'block';
                    militeryTypeSelect.required = false;
                    rankInput.required = true;
                    militeryTypeSelect.value = ''; // Clear military type when switching to civilian
                }
            }
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

        function loadRecipientParts(recipientId, targetSelectId) {
            const partSelect = document.getElementById(targetSelectId);
            if (!partSelect) {
                return;
            }

            if (!recipientId) {
                partSelect.innerHTML = '<option value="">{{ localize("global.select") }}</option>';
                partSelect.disabled = true;
                $(partSelect).trigger('change');
                return;
            }

            partSelect.innerHTML = '<option value="">{{ localize("global.loading") }}...</option>';
            partSelect.disabled = true;

            $.ajax({
                url: '/patients/recipient-parts/' + recipientId,
                type: 'GET',
                success: function (response) {
                    partSelect.innerHTML = '<option value="">{{ localize("global.select") }}</option>';
                    if (response.success && response.recipientParts) {
                        response.recipientParts.forEach(function (part) {
                            const option = document.createElement('option');
                            option.value = part.id;
                            option.textContent = part.name + ' (' + part.code + ')';
                            partSelect.appendChild(option);
                        });
                    }
                    partSelect.disabled = false;
                    $(partSelect).trigger('change');
                    initializeSelect2WithAutoFocus();
                },
                error: function () {
                    partSelect.innerHTML = '<option value="">{{ localize("global.select") }}</option>';
                    partSelect.disabled = true;
                }
            });
        }

        function getTab(tab_type) {
            $('#first, #second, #extraordinary, #third, #vip').html('');
            $.ajax({
                url: " {{url('patients/get-tab')}}",
                type: 'GET',
                data: {
                    tab_type: tab_type,
                    patient_id: '{{$patient->id}}',
                },
                success: function (data) {
                    var tab_id = '#' + tab_type;
                    $(tab_id).html(data);

                    initializeSelect2WithAutoFocus();

                    setTimeout(function() {
                        const jobCategorySelect = document.getElementById('job_category');
                        if (jobCategorySelect && jobCategorySelect.value !== '') {
                            changeType(jobCategorySelect.value);
                        }
                    }, 100);

                    var tabNum = '';
                    if (tab_type === 'first') {
                        tabNum = 'tab1';
                    } else if (tab_type === 'second') {
                        tabNum = 'tab2';
                    } else if (tab_type === 'third') {
                        tabNum = 'tab3';
                    } else if (tab_type === 'extraordinary') {
                        tabNum = 'tab4';
                    } else if (tab_type === 'vip') {
                        tabNum = 'tab5';
                    }

                    if (tabNum) {
                        var ageInput = document.getElementById('age_' + tabNum);
                        if (ageInput && ageInput.value) {
                            parseAgeToDropdowns(tabNum, ageInput.value);
                        }
                        updateAgeValue(tabNum);

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

        // AJAX Patient Form Submission for Edit
        function submitPatientForm(formId) {
            const form = $('#' + formId);
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            
            // Disable submit button and show loading
            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>{{ localize("global.updating") }}...');
            
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
                        
                        // Redirect to patient show page after a short delay
                        setTimeout(function() {
                            window.location.href = '{{ route("patients.show", $patient) }}';
                        }, 1500);
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

        // Bind AJAX form submission to all patient forms
        $(document).on('submit', '#patient-form-tab1, #patient-form-tab2, #patient-form-tab3, #patient-form-tab4, #patient-form-tab5', function(e) {
            e.preventDefault();
            const formId = $(this).attr('id');
            const tabMap = {
                'patient-form-tab1': 'tab1',
                'patient-form-tab2': 'tab2',
                'patient-form-tab3': 'tab3',
                'patient-form-tab4': 'tab4',
                'patient-form-tab5': 'tab5'
            };
            if (tabMap[formId]) {
                updateAgeValue(tabMap[formId]);
            }
            submitPatientForm(formId);
        });

    </script>
@endsection
