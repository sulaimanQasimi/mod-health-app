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
                        <h5 class="mb-0">{{ localize('global.new_anesthesias') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <form action="{{ route('anesthesias.updateAnesthesia', $anesthesia) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" id="patient_id{{ $anesthesia->appointment->patient_id }}" name="patient_id" value="{{ $anesthesia->appointment->patient_id }}">
                                        <input type="hidden" id="appointment_id{{ $anesthesia->appointment->id }}" name="appointment_id" value="{{ $anesthesia->appointment->id }}">
                                        <input type="hidden" id="doctor_id{{ $anesthesia->appointment->id }}" name="doctor_id" value="{{ auth()->user()->id }}">
                                        <input type="hidden" id="branch_id{{ $anesthesia->appointment->id }}" name="branch_id" value="{{ auth()->user()->branch_id }}">
                                    
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="plan{{ $anesthesia->appointment->id }}">{{ localize('global.plan') }}</label>
                                                    <textarea class="form-control" id="plan{{ $anesthesia->appointment->id }}" name="plan" rows="3">{{ $anesthesia->plan }}</textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="other_problems{{ $anesthesia->appointment->id }}">{{ localize('global.other_problems') }}</label>
                                                    <textarea class="form-control" id="other_problems{{ $anesthesia->appointment->id }}" name="other_problems" rows="3">{{ $anesthesia->other_problems }}</textarea>
                                                </div>
                                            </div>
                                            <h5 class="mt-2">{{ localize('global.operation_team') }}</h5>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label for="operation_surgion_id{{ $anesthesia->appointment->id }}">{{ localize('global.operation_surgion') }}</label>
                                                        <select class="form-control select2 operation-doctor-select" 
                                                                name="operation_surgion_id" 
                                                                id="operation_surgion_id{{ $anesthesia->appointment->id }}"
                                                                data-anesthesia-id="{{ $anesthesia->id }}"
                                                                data-selected-value="{{ $anesthesia->operation_surgion_id }}">
                                                            <option value="">{{ localize('global.select') }}...</option>
                                                            <option value="loading" disabled>{{ localize('global.loading') }}...</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="operation_assistants_id{{ $anesthesia->appointment->id }}">{{ localize('global.operation_assistants') }}</label>
                                                        <select class="form-control select2 operation-doctor-select" 
                                                                name="operation_assistants_id[]" 
                                                                id="operation_assistants_id{{ $anesthesia->appointment->id }}"
                                                                multiple
                                                                data-anesthesia-id="{{ $anesthesia->id }}"
                                                                data-selected-values="{{ $anesthesia->operation_assistants_id }}">
                                                            <option value="">{{ localize('global.select') }}...</option>
                                                            <option value="loading" disabled>{{ localize('global.loading') }}...</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="anesthesia_type{{ $anesthesia->appointment->id }}">{{ localize('global.anesthesia_type') }}</label>
                                                        <select class="form-control select2" name="anesthesia_type" id="anesthesia_type">
                                                            <option value="">{{ localize('global.select') }}</option>
                                                            <option value="local" {{ $anesthesia->anesthesia_type == 'local' ? 'selected' : '' }}>{{ localize('global.local') }}</option>
                                                            <option value="spinal" {{ $anesthesia->anesthesia_type == 'spinal' ? 'selected' : '' }}>{{ localize('global.spinal') }}</option>
                                                            <option value="general" {{ $anesthesia->anesthesia_type == 'general' ? 'selected' : '' }}>{{ localize('global.general') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                    
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="operation_type_id{{ $anesthesia->appointment->id }}" class="mt-2 mb-2">{{ localize('global.operation_type') }}</label>
                                                    <select class="form-control select2" name="operation_type_id">
                                                        <option value="">{{ localize('global.select') }}</option>
                                                        @foreach ($operationTypes as $value)
                                                            <option value="{{ $value->id }}" {{ $anesthesia->operation_type_id == $value->id ? 'selected' : '' }}>
                                                                {{ $value->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="date" class="mt-2 mb-2">{{ localize('global.date') }}</label>
                                                    <x-tools.dariDatePicker name="date" dir="ltr"
                                                    withID="date" withPlaceHolder="{{ localize('global.date') }}"
                                                    withSize="3" extraClasses="" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="time" class="mt-2 mb-2">{{ localize('global.time') }}</label>
                                                    <input type="time" class="form-control" name="time" value="{{ $anesthesia->time }}">
                                                </div>
                                            </div>
                                    
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="planned_duration" class="mt-2 mb-2">{{ localize('global.planned_duration') }}</label>
                                                    <input type="text" class="form-control" name="planned_duration" value="{{ $anesthesia->planned_duration }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="position_on_bed" class="mt-2 mb-2">{{ localize('global.position_on_bed') }}</label>
                                                    <input type="text" class="form-control" name="position_on_bed" value="{{ $anesthesia->position_on_bed }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="estimated_blood_waste" class="mt-2 mb-2">{{ localize('global.estimated_blood_waste') }}</label>
                                                    <input type="text" class="form-control" name="estimated_blood_waste" value="{{ $anesthesia->estimated_blood_waste }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mt-2">
                                            <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Select2 styles for edit page */
        .operation-doctor-select + .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 0.75rem;
            padding-right: 20px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            padding: 0.25rem 0.5rem;
            line-height: 28px;
        }

        .select2-dropdown {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Load hospital doctors for anesthesia edit page
            loadHospitalDoctorsForEdit();
        });

        function loadHospitalDoctorsForEdit() {
            const surgionSelect = $('#operation_surgion_id{{ $anesthesia->appointment->id }}');
            const assistantsSelect = $('#operation_assistants_id{{ $anesthesia->appointment->id }}');
            
            // Get selected values from data attributes
            const selectedSurgionId = surgionSelect.data('selected-value');
            const selectedAssistantsIds = assistantsSelect.data('selected-values');
            let selectedAssistantsArray = [];
            
            // Parse selected assistants IDs (handle JSON string or null)
            if (selectedAssistantsIds) {
                try {
                    selectedAssistantsArray = JSON.parse(selectedAssistantsIds);
                    if (!Array.isArray(selectedAssistantsArray)) {
                        selectedAssistantsArray = [];
                    }
                } catch (e) {
                    selectedAssistantsArray = [];
                }
            }
            
            // Show loading state
            if (surgionSelect.length) {
                surgionSelect.html('<option value="">{{ localize("global.select") }}...</option><option value="loading" disabled>{{ localize("global.loading") }}...</option>');
            }
            if (assistantsSelect.length) {
                assistantsSelect.html('<option value="">{{ localize("global.select") }}...</option><option value="loading" disabled>{{ localize("global.loading") }}...</option>');
            }
            
            // Load doctors from API
            $.ajax({
                url: '{{ route("doctor-api.hospital-doctors") }}',
                method: 'GET',
                data: {
                    branch_id: {{ auth()->user()->branch_id }}
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // Clear loading option
                        let surgionOptions = '<option value="">{{ localize("global.select") }}...</option>';
                        let assistantsOptions = '<option value="">{{ localize("global.select") }}...</option>';
                        
                        // Add doctors to options
                        response.data.forEach(function(doctor) {
                            const optionText = doctor.name + (doctor.specialization ? ' - ' + doctor.specialization : '');
                            // Compare as numbers for surgion
                            const isSelectedSurgion = selectedSurgionId && Number(doctor.id) == Number(selectedSurgionId);
                            // Compare as numbers for assistants (convert array values to numbers)
                            const doctorIdNum = Number(doctor.id);
                            const isSelectedAssistant = selectedAssistantsArray.some(id => Number(id) == doctorIdNum);
                            
                            surgionOptions += `<option value="${doctor.id}" ${isSelectedSurgion ? 'selected' : ''}>${optionText}</option>`;
                            assistantsOptions += `<option value="${doctor.id}" ${isSelectedAssistant ? 'selected' : ''}>${optionText}</option>`;
                        });
                        
                        // Update selects
                        if (surgionSelect.length) {
                            surgionSelect.html(surgionOptions);
                            // Reinitialize Select2
                            if (surgionSelect.hasClass('select2-hidden-accessible')) {
                                surgionSelect.select2('destroy');
                            }
                            setTimeout(function() {
                                if (typeof $.fn.select2 !== 'undefined') {
                                    surgionSelect.select2({
                                        width: '100%',
                                        placeholder: '{{ localize("global.select") }}...',
                                        allowClear: true
                                    });
                                }
                            }, 100);
                        }
                        
                        if (assistantsSelect.length) {
                            assistantsSelect.html(assistantsOptions);
                            // Reinitialize Select2
                            if (assistantsSelect.hasClass('select2-hidden-accessible')) {
                                assistantsSelect.select2('destroy');
                            }
                            setTimeout(function() {
                                if (typeof $.fn.select2 !== 'undefined') {
                                    assistantsSelect.select2({
                                        width: '100%',
                                        placeholder: '{{ localize("global.select") }}...',
                                        allowClear: true
                                    });
                                }
                            }, 100);
                        }
                    } else {
                        console.error('Failed to load doctors:', response.message);
                        if (surgionSelect.length) {
                            surgionSelect.html('<option value="">{{ localize("global.select") }}...</option><option value="" disabled>{{ localize("global.failed_to_load_doctors") }}</option>');
                        }
                        if (assistantsSelect.length) {
                            assistantsSelect.html('<option value="">{{ localize("global.select") }}...</option><option value="" disabled>{{ localize("global.failed_to_load_doctors") }}</option>');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading doctors:', error);
                    if (surgionSelect.length) {
                        surgionSelect.html('<option value="">{{ localize("global.select") }}...</option><option value="" disabled>{{ localize("global.error_loading_doctors") }}</option>');
                    }
                    if (assistantsSelect.length) {
                        assistantsSelect.html('<option value="">{{ localize("global.select") }}...</option><option value="" disabled>{{ localize("global.error_loading_doctors") }}</option>');
                    }
                }
            });
        }
    </script>
@endsection
