@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-body">

                        <!-- Enhanced Anesthesia Details Section -->
                        <div class="col-md-12 mb-4">
                            <div class="card border-0 shadow-sm anesthesia-details-card">
                                <div class="card-header bg-gradient-primary text-white d-flex align-items-center justify-content-between">
                                    <h5 class="mb-0 d-flex align-items-center">
                                        <i class="bx bx-first-aid me-2 fs-4"></i>
                                        {{ localize('global.anesthesia_details') }}
                                    </h5>
                                    <span class="badge bg-white text-primary fs-6">
                                        @if ($anesthesia->status == 'new')
                                            <i class="bx bx-time me-1"></i>{{ localize('global.new') }}
                                        @elseif ($anesthesia->status == 'rejected')
                                            <i class="bx bx-x-circle me-1"></i>{{ localize('global.rejected') }}
                                        @else
                                            <i class="bx bx-check-circle me-1"></i>{{ localize('global.approved') }}
                                        @endif
                                    </span>
                                </div>
                                <div class="card-body p-4">
                                    <!-- Patient & Operation Info Row -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-3">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-user me-2"></i>
                                                    <span>{{ localize('global.patient_name') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">{{ $anesthesia->patient?->name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-plus-medical me-2"></i>
                                                    <span>{{ localize('global.operation_type') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">
                                                        <span class="badge bg-success">{{ $anesthesia->operationType?->name ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-calendar me-2"></i>
                                                    <span>{{ localize('global.date') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($anesthesia->date) ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-time-five me-2"></i>
                                                    <span>{{ localize('global.time') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">{{ $anesthesia->time ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Operation Details Row -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-clipboard me-2"></i>
                                                    <span>{{ localize('global.operation_plan') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">{{ $anesthesia->plan ?: 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-timer me-2"></i>
                                                    <span>{{ localize('global.operation_duration') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">{{ $anesthesia->planned_duration ?: 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-bed me-2"></i>
                                                    <span>{{ localize('global.position_on_bed') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">{{ $anesthesia->position_on_bed ?: 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Medical Team Row -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-3">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-user-md me-2"></i>
                                                    <span>{{ localize('global.operation_surgion') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">{{ $anesthesia->surgion?->name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        @if(isset($anesthesia->anesthesia_log->name))
                                        <div class="col-md-3">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-file-blank me-2"></i>
                                                    <span>{{ localize('global.anesthesia_log') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">{{ $anesthesia->anesthesia_log->name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        @if(isset($anesthesia->anesthesist->name))
                                        <div class="col-md-3">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-user-circle me-2"></i>
                                                    <span>{{ localize('global.anesthesist') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">{{ $anesthesia->anesthesist->name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="col-md-3">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-droplet me-2"></i>
                                                    <span>{{ localize('global.estimated_blood_waste') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">{{ $anesthesia->estimated_blood_waste ?: 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Nurses & Additional Info Row -->
                                    <div class="row g-3 mb-4">
                                        @if(isset($anesthesia->scrub_nurse->name))
                                        <div class="col-md-3">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-user-pin me-2"></i>
                                                    <span>{{ localize('global.scrub_nurse') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">{{ $anesthesia->scrub_nurse->name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        @if(isset($anesthesia->circulation_nurse->name))
                                        <div class="col-md-3">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-user-voice me-2"></i>
                                                    <span>{{ localize('global.circulation_nurse') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">{{ $anesthesia->circulation_nurse->name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="col-md-3">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-message-dots me-2"></i>
                                                    <span>{{ localize('global.anesthesia_log_reply') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">{{ $anesthesia->anesthesia_log_reply ?: 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-clipboard me-2"></i>
                                                    <span>{{ localize('global.anesthesia_plan') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">{{ $anesthesia->anesthesia_plan ?: 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Anesthesia Type & Other Problems Row -->
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-pulse me-2"></i>
                                                    <span>{{ localize('global.anesthesia_type') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">
                                                        @if($anesthesia->anesthesia_type)
                                                            <span class="badge bg-info">{{ $anesthesia->anesthesia_type }}</span>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if($anesthesia->other_problems)
                                        <div class="col-md-9">
                                            <div class="rounded">
                                                <div class="bg-primary text-white p-2 rounded-top d-flex align-items-center">
                                                    <i class="bx bx-error-circle me-2"></i>
                                                    <span>{{ localize('global.other_problems') }}</span>
                                                </div>
                                                <div class="bg-body-secondary p-3 rounded-bottom">
                                                    <div class="fw-semibold">{{ $anesthesia->other_problems }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                                @if($anesthesia->status == 'new')
                                <hr class="border border-label-primary">
                                <div class="d-flex justify-content-center mb-2 p-2">
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                            data-bs-target="#createAnasthesiaModal{{ $anesthesia->id }}"><span><i
                                                    class="bx bx-check"></i>{{localize('global.approve')}}</span></button>
                                        </div>

                                        <div class="col-md-2">
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#createAnasthesiaRejectModal{{ $anesthesia->id }}"><span><i
                                                class="bx bx-x"></i>{{localize('global.reject')}}</span></button>
                                        </div>
                                </div>
                                @endif
                                <div class="modal fade" id="createAnasthesiaModal{{ $anesthesia->id }}" tabindex="-1"
                                    aria-labelledby="createAnasthesiaModalLabel{{ $anesthesia->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createAnasthesiaModalLabel{{ $anesthesia->id }}">
                                                    {{ localize('global.refere_to_operation') }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('anesthesias.update', $anesthesia) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden"
                                                        name="status" value="approved">

                                                    <div class="form-group">

                                                        <div class="form-group">
                                                            <label
                                                                for="anesthesia_log_reply{{ $anesthesia->id }}">{{ localize('global.anesthesia_log_reply') }}</label>
                                                            <textarea class="form-control" id="anesthesia_log_reply{{ $anesthesia->id }}" name="anesthesia_log_reply" rows="3"></textarea>
                                                        </div>

                                                        <div class="form-group">
                                                            <label
                                                                for="anesthesia_plan{{ $anesthesia->id }}">{{ localize('global.anesthesia_plan') }}</label>
                                                            <textarea class="form-control" id="anesthesia_plan{{ $anesthesia->id }}" name="anesthesia_plan" rows="3"></textarea>
                                                        </div>

                                                        <div class="row">
                                                        <div class="col-md-4">
                                                            <label
                                                                for="operation_anesthesia_log_id{{ $anesthesia->id }}">{{ localize('global.anesthesia_log') }}</label>
                                                            <select class="form-control select2 operation-doctor-select"
                                                                name="operation_anesthesia_log_id"
                                                                id="operation_anesthesia_log_id{{ $anesthesia->id }}"
                                                                data-anesthesia-id="{{ $anesthesia->id }}"
                                                                data-selected-value="{{ old('operation_anesthesia_log_id', $anesthesia->operation_anesthesia_log_id) }}">
                                                                <option value="">{{ localize('global.select') }}...</option>
                                                                <option value="loading" disabled>{{ localize('global.loading') }}...</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label
                                                                for="anesthesist{{ $anesthesia->id }}">{{ localize('global.anesthesist') }}</label>
                                                            <select class="form-control select2 operation-doctor-select"
                                                                name="operation_anesthesist_id"
                                                                id="operation_anesthesist_id{{ $anesthesia->id }}"
                                                                data-anesthesia-id="{{ $anesthesia->id }}"
                                                                data-selected-value="{{ old('operation_anesthesist_id', $anesthesia->operation_anesthesist_id) }}">
                                                                <option value="">{{ localize('global.select') }}...</option>
                                                                <option value="loading" disabled>{{ localize('global.loading') }}...</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label for="anesthesia_type{{ $anesthesia->id }}">{{ localize('global.anesthesia_type') }}</label>
                                                            <select class="form-control select2" name="anesthesia_type" id="anesthesia_type">
                                                                <option value="">{{ localize('global.select') }}</option>
                                                                <option value="local" {{ $anesthesia->anesthesia_type == 'local' ? 'selected' : '' }}>{{ localize('global.local') }}</option>
                                                                <option value="spinal" {{ $anesthesia->anesthesia_type == 'spinal' ? 'selected' : '' }}>{{ localize('global.spinal') }}</option>
                                                                <option value="general" {{ $anesthesia->anesthesia_type == 'general' ? 'selected' : '' }}>{{ localize('global.general') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                                <button type="submit"
                                                    class="btn btn-primary">{{ localize('global.save') }}</button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="createAnasthesiaRejectModal{{ $anesthesia->id }}" tabindex="-1"
                                    aria-labelledby="createAnasthesiaRejectModalLabel{{ $anesthesia->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createAnasthesiaRejectModalLabel{{ $anesthesia->id }}">
                                                    {{ localize('global.rejection_reason') }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('anesthesias.update', $anesthesia) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden"
                                                        name="status" value="rejected">

                                                    <div class="form-group">

                                                        <div class="form-group">
                                                            <label
                                                                for="rejection_reason{{ $anesthesia->id }}">{{ localize('global.rejection_reason') }}</label>
                                                            <textarea class="form-control" id="rejection_reason{{ $anesthesia->id }}" name="anesthesia_log_reply" rows="3"></textarea>
                                                        </div>

                                                        <div class="form-group">
                                                            <label
                                                                for="anesthesia_plan{{ $anesthesia->id }}">{{ localize('global.anesthesia_plan') }}</label>
                                                            <textarea class="form-control" id="anesthesia_plan{{ $anesthesia->id }}" name="anesthesia_plan" rows="3"></textarea>
                                                        </div>

                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                                <button type="submit"
                                                    class="btn btn-primary">{{ localize('global.save') }}</button>
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
        </div>
    </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Enhanced Anesthesia Details Card Styles */
        .anesthesia-details-card {
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .anesthesia-details-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .anesthesia-details-card .card-header {
            background: linear-gradient(135deg, #696cff 0%, #5a5fef 100%);
            border: none;
            padding: 1.25rem 1.5rem;
        }

        .anesthesia-details-card .card-header h5 {
            font-weight: 600;
            font-size: 1.25rem;
        }

        .anesthesia-details-card .card-header .badge {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }

        .detail-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            border-left: 4px solid #696cff;
            transition: all 0.3s ease;
            height: 100%;
        }

        .detail-item:hover {
            background: #f0f0f0;
            border-left-color: #5a5fef;
            transform: translateX(3px);
        }

        .detail-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        }

        .detail-label i {
            font-size: 1.1rem;
        }

        .detail-value {
            font-size: 1rem;
            font-weight: 500;
            color: #212529;
            word-wrap: break-word;
        }

        .detail-value .badge {
            font-size: 0.875rem;
            padding: 0.4rem 0.8rem;
        }

        /* Status Badge Colors */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #696cff 0%, #5a5fef 100%);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .anesthesia-details-card .card-header {
                padding: 1rem;
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.5rem;
            }

            .anesthesia-details-card .card-body {
                padding: 1rem !important;
            }

            .detail-item {
                padding: 0.75rem;
                margin-bottom: 0.75rem;
            }

            .detail-label {
                font-size: 0.8rem;
            }

            .detail-value {
                font-size: 0.9rem;
            }
        }

        /* Dark Mode Support */
        [data-bs-theme="dark"] .detail-item {
            background: #2b2c40;
            border-left-color: #696cff;
        }

        [data-bs-theme="dark"] .detail-item:hover {
            background: #3a3b4d;
        }

        [data-bs-theme="dark"] .detail-label {
            color: #a3a4cc;
        }

        [data-bs-theme="dark"] .detail-value {
            color: #a3a4cc;
        }

        /* Select2 styles for show page */
        #createAnasthesiaModal{{ $anesthesia->id }} .operation-doctor-select + .select2-container {
            width: 100% !important;
            z-index: 9999;
        }

        #createAnasthesiaModal{{ $anesthesia->id }} .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }

        #createAnasthesiaModal{{ $anesthesia->id }} .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 0.75rem;
            padding-right: 20px;
        }

        #createAnasthesiaModal{{ $anesthesia->id }} .select2-dropdown {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        #createAnasthesiaModal{{ $anesthesia->id }} .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }

        #createAnasthesiaModal{{ $anesthesia->id }} .select2-results__option {
            padding: 0.5rem 0.75rem;
        }

        #createAnasthesiaModal{{ $anesthesia->id }} .select2-results__option--highlighted {
            background-color: #0d6efd;
            color: white;
        }
    </style>
@endsection

@section('scripts')
    <script>
        // Load hospital doctors when anesthesia modal is opened
        $(document).on('shown.bs.modal', '#createAnasthesiaModal{{ $anesthesia->id }}', function() {
            loadHospitalDoctorsForShow();
        });

        function loadHospitalDoctorsForShow() {
            const anesthesiaLogSelect = $('#operation_anesthesia_log_id{{ $anesthesia->id }}');
            const anesthesistSelect = $('#operation_anesthesist_id{{ $anesthesia->id }}');
            const modal = $('#createAnasthesiaModal{{ $anesthesia->id }}');
            const dropdownParent = modal.length ? modal : $('body');
            
            // Get selected values from data attributes
            const selectedAnesthesiaLogId = anesthesiaLogSelect.data('selected-value');
            const selectedAnesthesistId = anesthesistSelect.data('selected-value');
            
            // Show loading state
            if (anesthesiaLogSelect.length) {
                anesthesiaLogSelect.html('<option value="">{{ localize("global.select") }}...</option><option value="loading" disabled>{{ localize("global.loading") }}...</option>');
            }
            if (anesthesistSelect.length) {
                anesthesistSelect.html('<option value="">{{ localize("global.select") }}...</option><option value="loading" disabled>{{ localize("global.loading") }}...</option>');
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
                        let anesthesiaLogOptions = '<option value="">{{ localize("global.select") }}...</option>';
                        let anesthesistOptions = '<option value="">{{ localize("global.select") }}...</option>';
                        
                        // Add doctors to options
                        response.data.forEach(function(doctor) {
                            const optionText = doctor.name + (doctor.specialization ? ' - ' + doctor.specialization : '');
                            const isSelectedAnesthesiaLog = selectedAnesthesiaLogId && Number(doctor.id) == Number(selectedAnesthesiaLogId);
                            const isSelectedAnesthesist = selectedAnesthesistId && Number(doctor.id) == Number(selectedAnesthesistId);
                            
                            anesthesiaLogOptions += `<option value="${doctor.id}" ${isSelectedAnesthesiaLog ? 'selected' : ''}>${optionText}</option>`;
                            anesthesistOptions += `<option value="${doctor.id}" ${isSelectedAnesthesist ? 'selected' : ''}>${optionText}</option>`;
                        });
                        
                        // Update selects
                        if (anesthesiaLogSelect.length) {
                            anesthesiaLogSelect.html(anesthesiaLogOptions);
                            // Reinitialize Select2
                            if (anesthesiaLogSelect.hasClass('select2-hidden-accessible')) {
                                anesthesiaLogSelect.select2('destroy');
                            }
                            setTimeout(function() {
                                if (typeof $.fn.select2 !== 'undefined') {
                                    anesthesiaLogSelect.select2({
                                        dropdownParent: dropdownParent,
                                        width: '100%',
                                        placeholder: '{{ localize("global.select") }}...',
                                        allowClear: true
                                    });
                                }
                            }, 100);
                        }
                        
                        if (anesthesistSelect.length) {
                            anesthesistSelect.html(anesthesistOptions);
                            // Reinitialize Select2
                            if (anesthesistSelect.hasClass('select2-hidden-accessible')) {
                                anesthesistSelect.select2('destroy');
                            }
                            setTimeout(function() {
                                if (typeof $.fn.select2 !== 'undefined') {
                                    anesthesistSelect.select2({
                                        dropdownParent: dropdownParent,
                                        width: '100%',
                                        placeholder: '{{ localize("global.select") }}...',
                                        allowClear: true
                                    });
                                }
                            }, 100);
                        }
                    } else {
                        console.error('Failed to load doctors:', response.message);
                        if (anesthesiaLogSelect.length) {
                            anesthesiaLogSelect.html('<option value="">{{ localize("global.select") }}...</option><option value="" disabled>{{ localize("global.failed_to_load_doctors") }}</option>');
                        }
                        if (anesthesistSelect.length) {
                            anesthesistSelect.html('<option value="">{{ localize("global.select") }}...</option><option value="" disabled>{{ localize("global.failed_to_load_doctors") }}</option>');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading doctors:', error);
                    if (anesthesiaLogSelect.length) {
                        anesthesiaLogSelect.html('<option value="">{{ localize("global.select") }}...</option><option value="" disabled>{{ localize("global.error_loading_doctors") }}</option>');
                    }
                    if (anesthesistSelect.length) {
                        anesthesistSelect.html('<option value="">{{ localize("global.select") }}...</option><option value="" disabled>{{ localize("global.error_loading_doctors") }}</option>');
                    }
                }
            });
        }
    </script>
@endsection
