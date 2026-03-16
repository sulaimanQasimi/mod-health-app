@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">{{ localize('global.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dentist-registrations.show', $dentistRegistration) }}" class="text-decoration-none">{{ localize('global.registration_details') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ localize('global.create_dental_chart') }}</li>
                        </ol>
                    </nav>
                    <h2 class="h4 mb-0">{{ localize('global.create_dental_chart') }}</h2>
                </div>
            </div>

            <!-- Chart Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.chart_information') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('dental-charts.store', $dentistRegistration) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tooth_number" class="form-label">{{ localize('global.tooth_number') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('tooth_number') is-invalid @enderror" id="tooth_number" name="tooth_number" required>
                                    <option value="">{{ localize('global.select_tooth') }}</option>
                                    @for($i = 11; $i <= 18; $i++)
                                        <option value="{{ $i }}" {{ old('tooth_number') == $i ? 'selected' : '' }}>{{ $i }} - Upper Right</option>
                                    @endfor
                                    @for($i = 21; $i <= 28; $i++)
                                        <option value="{{ $i }}" {{ old('tooth_number') == $i ? 'selected' : '' }}>{{ $i }} - Upper Left</option>
                                    @endfor
                                    @for($i = 31; $i <= 38; $i++)
                                        <option value="{{ $i }}" {{ old('tooth_number') == $i ? 'selected' : '' }}>{{ $i }} - Lower Left</option>
                                    @endfor
                                    @for($i = 41; $i <= 48; $i++)
                                        <option value="{{ $i }}" {{ old('tooth_number') == $i ? 'selected' : '' }}>{{ $i }} - Lower Right</option>
                                    @endfor
                                </select>
                                @error('tooth_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tooth_condition" class="form-label">{{ localize('global.tooth_condition') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('tooth_condition') is-invalid @enderror" id="tooth_condition" name="tooth_condition" required>
                                    <option value="healthy" {{ old('tooth_condition') == 'healthy' ? 'selected' : '' }}>{{ localize('global.healthy') }}</option>
                                    <option value="cavity" {{ old('tooth_condition') == 'cavity' ? 'selected' : '' }}>{{ localize('global.cavity') }}</option>
                                    <option value="filling" {{ old('tooth_condition') == 'filling' ? 'selected' : '' }}>{{ localize('global.filling') }}</option>
                                    <option value="crown" {{ old('tooth_condition') == 'crown' ? 'selected' : '' }}>{{ localize('global.crown') }}</option>
                                    <option value="bridge" {{ old('tooth_condition') == 'bridge' ? 'selected' : '' }}>{{ localize('global.bridge') }}</option>
                                    <option value="root_canal" {{ old('tooth_condition') == 'root_canal' ? 'selected' : '' }}>{{ localize('global.root_canal') }}</option>
                                    <option value="implant" {{ old('tooth_condition') == 'implant' ? 'selected' : '' }}>{{ localize('global.implant') }}</option>
                                    <option value="decay" {{ old('tooth_condition') == 'decay' ? 'selected' : '' }}>{{ localize('global.decay') }}</option>
                                    <option value="fractured" {{ old('tooth_condition') == 'fractured' ? 'selected' : '' }}>{{ localize('global.fractured') }}</option>
                                    <option value="extraction" {{ old('tooth_condition') == 'extraction' ? 'selected' : '' }}>{{ localize('global.extraction') }}</option>
                                    <option value="missing" {{ old('tooth_condition') == 'missing' ? 'selected' : '' }}>{{ localize('global.missing') }}</option>
                                    <option value="impacted" {{ old('tooth_condition') == 'impacted' ? 'selected' : '' }}>{{ localize('global.impacted') }}</option>
                                </select>
                                @error('tooth_condition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Implant-only fields -->
                            <div class="col-12 mb-3" data-implant-fields style="display:none;">
                                <div class="border rounded p-3 bg-body-secondary">
                                    <h6 class="mb-3">{{ localize('global.implant') }} Details</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Implant System/Brand</label>
                                            <input type="text" name="implant_system_brand" class="form-control"
                                                value="{{ old('implant_system_brand') }}">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Diameter (mm)</label>
                                            <input type="number" step="0.01" min="0" name="implant_diameter" class="form-control"
                                                value="{{ old('implant_diameter') }}">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Length (mm)</label>
                                            <input type="number" step="0.01" min="0" name="implant_length" class="form-control"
                                                value="{{ old('implant_length') }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Implant Status</label>
                                            <select name="implant_status" class="form-select">
                                                <option value="">{{ localize('global.select') }}</option>
                                                <option value="planned" {{ old('implant_status') == 'planned' ? 'selected' : '' }}>Planned</option>
                                                <option value="placed" {{ old('implant_status') == 'placed' ? 'selected' : '' }}>Placed</option>
                                                <option value="failed" {{ old('implant_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                                <option value="removed" {{ old('implant_status') == 'removed' ? 'selected' : '' }}>Removed</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Implant Notes</label>
                                            <textarea name="implant_notes" class="form-control" rows="3">{{ old('implant_notes') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gum_health" class="form-label">{{ localize('global.gum_health') }}</label>
                                <select class="form-select @error('gum_health') is-invalid @enderror" id="gum_health" name="gum_health">
                                    <option value="">{{ localize('global.select') }}</option>
                                    <option value="healthy" {{ old('gum_health') == 'healthy' ? 'selected' : '' }}>{{ localize('global.healthy') }}</option>
                                    <option value="gingivitis" {{ old('gum_health') == 'gingivitis' ? 'selected' : '' }}>{{ localize('global.gingivitis') }}</option>
                                    <option value="periodontitis" {{ old('gum_health') == 'periodontitis' ? 'selected' : '' }}>{{ localize('global.periodontitis') }}</option>
                                    <option value="recession" {{ old('gum_health') == 'recession' ? 'selected' : '' }}>{{ localize('global.recession') }}</option>
                                    <option value="bleeding" {{ old('gum_health') == 'bleeding' ? 'selected' : '' }}>{{ localize('global.bleeding') }}</option>
                                </select>
                                @error('gum_health')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="oral_hygiene_score" class="form-label">{{ localize('global.oral_hygiene_score') }}</label>
                                <input type="number" step="0.1" min="0" max="10" class="form-control @error('oral_hygiene_score') is-invalid @enderror" 
                                    id="oral_hygiene_score" name="oral_hygiene_score" value="{{ old('oral_hygiene_score') }}">
                                @error('oral_hygiene_score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pocket_depth" class="form-label">{{ localize('global.pocket_depth') }} (mm)</label>
                                <input type="number" step="0.01" min="0" max="20" class="form-control @error('pocket_depth') is-invalid @enderror" 
                                    id="pocket_depth" name="pocket_depth" value="{{ old('pocket_depth') }}">
                                @error('pocket_depth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="bleeding" class="form-label">{{ localize('global.bleeding') }}</label>
                                <select class="form-select @error('bleeding') is-invalid @enderror" id="bleeding" name="bleeding">
                                    <option value="0" {{ old('bleeding', 0) == 0 ? 'selected' : '' }}>{{ localize('global.no') }}</option>
                                    <option value="1" {{ old('bleeding') == 1 ? 'selected' : '' }}>{{ localize('global.yes') }}</option>
                                </select>
                                @error('bleeding')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="mobility" class="form-label">{{ localize('global.mobility') }}</label>
                                <select class="form-select @error('mobility') is-invalid @enderror" id="mobility" name="mobility">
                                    <option value="">{{ localize('global.select') }}</option>
                                    <option value="none" {{ old('mobility') == 'none' ? 'selected' : '' }}>{{ localize('global.none') }}</option>
                                    <option value="grade1" {{ old('mobility') == 'grade1' ? 'selected' : '' }}>{{ localize('global.grade1') }}</option>
                                    <option value="grade2" {{ old('mobility') == 'grade2' ? 'selected' : '' }}>{{ localize('global.grade2') }}</option>
                                    <option value="grade3" {{ old('mobility') == 'grade3' ? 'selected' : '' }}>{{ localize('global.grade3') }}</option>
                                </select>
                                @error('mobility')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="treatment_history" class="form-label">{{ localize('global.treatment_history') }}</label>
                                <textarea class="form-control @error('treatment_history') is-invalid @enderror" 
                                    id="treatment_history" name="treatment_history" rows="3">{{ old('treatment_history') }}</textarea>
                                @error('treatment_history')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">{{ localize('global.notes') }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                    id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('dentist-registrations.show', $dentistRegistration) }}" class="btn btn-secondary">
                                {{ localize('global.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save"></i> {{ localize('global.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Treatments Section -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.treatments') }}</h5>
                    <button type="button" class="btn btn-primary btn-sm" id="addTreatmentBtn" data-bs-toggle="modal" data-bs-target="#addTreatmentFromChartModal" disabled>
                        <i class="bx bx-plus"></i> {{ localize('global.add_treatment') }}
                    </button>
                </div>
                <div class="card-body">
                    <div id="treatmentsList">
                        <p class="text-muted text-center">{{ localize('global.select_tooth_to_view_treatments') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Treatment Modal -->
    <div class="modal fade" id="addTreatmentFromChartModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ localize('global.add_treatment') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="treatmentFormFromChart">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="treatment_dentist_registration_id" name="dentist_registration_id" value="{{ $dentistRegistration->id }}">
                        <input type="hidden" id="treatment_tooth_number" name="tooth_number">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="treatment_type" class="form-label">{{ localize('global.treatment_type') }}</label>
                                <input type="text" class="form-control" id="treatment_type" name="treatment_type" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="treatment_date" class="form-label">{{ localize('global.treatment_date') }} <span class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" class="form-control datepicker_dari" id="treatment_date" name="treatment_date" 
                                       placeholder="{{ localize('global.select_date') }}" required readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="treatment_status" class="form-label">{{ localize('global.status') }}</label>
                                <select class="form-select" id="treatment_status" name="status" required>
                                    <option value="planned">{{ localize('global.planned') }}</option>
                                    <option value="in_progress">{{ localize('global.in_progress') }}</option>
                                    <option value="completed">{{ localize('global.completed') }}</option>
                                    <option value="cancelled">{{ localize('global.cancelled') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="treatment_cost" class="form-label">{{ localize('global.cost') }}</label>
                                <input type="number" step="0.01" class="form-control" id="treatment_cost" name="cost">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="treatment_description" class="form-label">{{ localize('global.description') }}</label>
                                <textarea class="form-control" id="treatment_description" name="treatment_description" rows="3" required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="treatment_notes" class="form-label">{{ localize('global.notes') }}</label>
                                <textarea class="form-control" id="treatment_notes" name="notes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Persian Datepicker Library -->
    <script src="{{ asset('assets/persian date2/js/persianDatepicker.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/persian date2/css/persianDatepicker-default.css') }}" type="text/css" />
    
    <script>
        $(document).ready(function() {
            const dentistRegistrationId = {{ $dentistRegistration->id }};
            let currentToothNumber = null;

            // Initialize Persian date picker for treatment date
            $('#addTreatmentFromChartModal').on('shown.bs.modal', function() {
                const treatmentDateInput = $('#treatment_date');
                if (treatmentDateInput.length && !treatmentDateInput.data('persianDatepicker')) {
                    treatmentDateInput.persianDatepicker({
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
                }
            });

            // Handle tooth number selection change
            $('#tooth_number').on('change', function() {
                currentToothNumber = $(this).val();
                if (currentToothNumber) {
                    $('#addTreatmentBtn').prop('disabled', false);
                    $('#treatment_tooth_number').val(currentToothNumber);
                    loadTreatmentsForTooth(currentToothNumber);
                } else {
                    $('#addTreatmentBtn').prop('disabled', true);
                    $('#treatmentsList').html('<p class="text-muted text-center">{{ localize('global.select_tooth_to_view_treatments') }}</p>');
                }
            });

            // Load treatments for a specific tooth
            function loadTreatmentsForTooth(toothNumber) {
                $.ajax({
                    url: '{{ route("dentist-ajax.treatments.for-chart", $dentistRegistration) }}',
                    method: 'GET',
                    data: {
                        tooth_number: toothNumber
                    },
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            let html = '<div class="table-responsive"><table class="table table-sm">';
                            html += '<thead><tr><th>{{ localize("global.date") }}</th><th>{{ localize("global.treatment_type") }}</th><th>{{ localize("global.description") }}</th><th>{{ localize("global.status") }}</th></tr></thead><tbody>';
                            response.data.forEach(function(treatment) {
                                html += '<tr>';
                                html += '<td>' + treatment.treatment_date + '</td>';
                                html += '<td>' + treatment.treatment_type + '</td>';
                                html += '<td>' + treatment.treatment_description + '</td>';
                                html += '<td><span class="badge bg-' + getStatusColor(treatment.status) + '">' + treatment.status + '</span></td>';
                                html += '</tr>';
                            });
                            html += '</tbody></table></div>';
                            $('#treatmentsList').html(html);
                        } else {
                            $('#treatmentsList').html('<p class="text-muted text-center">{{ localize("global.no_treatments_found_for_this_tooth") }}</p>');
                        }
                    },
                    error: function() {
                        $('#treatmentsList').html('<p class="text-danger text-center">{{ localize("global.error_loading_treatments") }}</p>');
                    }
                });
            }

            function getStatusColor(status) {
                const colors = {
                    'planned': 'secondary',
                    'in_progress': 'info',
                    'completed': 'success',
                    'cancelled': 'danger'
                };
                return colors[status] || 'secondary';
            }

            // Handle treatment form submission
            $('#treatmentFormFromChart').on('submit', function(e) {
                e.preventDefault();
                
                const formData = {
                    treatment_type: $('#treatment_type').val(),
                    tooth_number: $('#treatment_tooth_number').val(),
                    treatment_description: $('#treatment_description').val(),
                    treatment_date: $('#treatment_date').val(),
                    status: $('#treatment_status').val(),
                    cost: $('#treatment_cost').val(),
                    notes: $('#treatment_notes').val()
                };

                $.ajax({
                    url: '{{ route("dentist-ajax.treatments.store", $dentistRegistration) }}',
                    method: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#addTreatmentFromChartModal').modal('hide');
                            $('#treatmentFormFromChart')[0].reset();
                            if (currentToothNumber) {
                                loadTreatmentsForTooth(currentToothNumber);
                            }
                            // Show success message
                            alert('{{ localize("global.treatment_created_successfully") }}');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = '{{ localize("global.failed_to_create_treatment") }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        alert(errorMsg);
                    }
                });
            });
        });
    </script>

    @vite('public/assets/js/vue/dental-chart-app.js')
@endsection
