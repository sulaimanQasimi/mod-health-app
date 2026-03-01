@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            {{-- Page Header --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">
                            <i class="bx bx-time me-2 text-primary"></i>
                            {{ localize('global.new_anesthesias') }}
                        </h4>
                    </div>
                </div>
            </div>

            {{-- Advanced Search and Filters --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-none border-0 collapsed" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-filter-alt text-primary me-2" style="font-size: 1.2rem;"></i>
                            <h6 class="mb-0 fw-semibold">{{ localize('global.advanced_filters') ?: 'Advanced Filters' }}</h6>
                        </div>
                        <i class="bx bx-chevron-down"></i>
                    </div>
                </div>
                <div class="collapse" id="filterCollapse">
                    <div class="card-body">
                        <form method="GET" action="{{ route('anesthesias.new') }}" id="filterForm">
                            <div class="row g-3">
                                {{-- Search Input --}}
                                <div class="col-md-4">
                                    <label for="search" class="form-label fw-semibold">
                                        <i class="bx bx-search me-1 text-primary"></i>{{ localize('global.search') }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="bx bx-search"></i>
                                        </span>
                                        <input type="text" class="form-control" id="search" name="search" 
                                               value="{{ request('search') }}" 
                                               placeholder="{{ localize('global.search_by_patient_operation') ?: 'Search by patient, operation type...' }}"
                                               autocomplete="off">
                                    </div>
                                </div>

                                {{-- Operation Type Filter --}}
                                <div class="col-md-3">
                                    <label for="operation_type_id" class="form-label fw-semibold">
                                        <i class="bx bx-plus-medical me-1 text-success"></i>{{ localize('global.operation_type') }}
                                    </label>
                                    <select class="form-select select2" id="operation_type_id" name="operation_type_id">
                                        <option value="">{{ localize('global.all') ?: 'All' }}</option>
                                        @foreach($operationTypes ?? [] as $type)
                                            <option value="{{ $type->id }}" {{ request('operation_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Department Filter --}}
                                <div class="col-md-3">
                                    <label for="department_id" class="form-label fw-semibold">
                                        <i class="bx bx-group me-1 text-secondary"></i>{{ localize('global.department') }}
                                    </label>
                                    <select class="form-select select2" id="department_id" name="department_id">
                                        <option value="">{{ localize('global.all') ?: 'All' }}</option>
                                        @foreach($departments ?? [] as $department)
                                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Anesthesia Type Filter --}}
                                <div class="col-md-2">
                                    <label for="anesthesia_type" class="form-label fw-semibold">
                                        <i class="bx bx-pulse me-1 text-warning"></i>{{ localize('global.anesthesia_type') }}
                                    </label>
                                    <select class="form-select" id="anesthesia_type" name="anesthesia_type">
                                        <option value="">{{ localize('global.all') ?: 'All' }}</option>
                                        <option value="local" {{ request('anesthesia_type') == 'local' ? 'selected' : '' }}>{{ localize('global.local') }}</option>
                                        <option value="spinal" {{ request('anesthesia_type') == 'spinal' ? 'selected' : '' }}>{{ localize('global.spinal') }}</option>
                                        <option value="general" {{ request('anesthesia_type') == 'general' ? 'selected' : '' }}>{{ localize('global.general') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                {{-- Date From --}}
                                <div class="col-md-3">
                                    <label for="date_from" class="form-label fw-semibold">
                                        <i class="bx bx-calendar me-1 text-info"></i>{{ localize('global.date_from') ?: 'Date From' }}
                                    </label>
                                    <input type="text" class="form-control datepicker_dari pdp-el" id="date_from" name="date_from" 
                                           value="{{ request('date_from') }}">
                                </div>

                                {{-- Date To --}}
                                <div class="col-md-3">
                                    <label for="date_to" class="form-label fw-semibold">
                                        <i class="bx bx-calendar me-1 text-info"></i>{{ localize('global.date_to') ?: 'Date To' }}
                                    </label>
                                    <input type="text" class="form-control datepicker_dari pdp-el" id="date_to" name="date_to" 
                                           value="{{ request('date_to') }}">
                                </div>

                                {{-- Per Page --}}
                                <div class="col-md-2">
                                    <label for="per_page" class="form-label fw-semibold">
                                        <i class="bx bx-list-ul me-1 text-secondary"></i>{{ localize('global.per_page') ?: 'Per Page' }}
                                    </label>
                                    <select class="form-select" id="per_page" name="per_page">
                                        <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                        <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                                    </select>
                                </div>

                                {{-- Filter Buttons --}}
                                <div class="col-md-4 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-filter me-1"></i>{{ localize('global.apply_filters') ?: 'Apply Filters' }}
                                    </button>
                                    <a href="{{ route('anesthesias.new') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-refresh me-1"></i>{{ localize('global.reset') ?: 'Reset' }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Active Filters Display --}}
            @if(request()->hasAny(['search', 'operation_type_id', 'department_id', 'anesthesia_type', 'date_from', 'date_to']))
                <div class="card mb-3 shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 fw-semibold">
                                    <i class="bx bx-filter me-1 text-primary"></i>{{ localize('global.active_filters') ?: 'Active Filters' }}:
                                </h6>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @if(request('search'))
                                        <span class="badge bg-primary">
                                            {{ localize('global.search') }}: {{ request('search') }}
                                            <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('operation_type_id'))
                                        <span class="badge bg-success">
                                            {{ localize('global.operation_type') }}: {{ $operationTypes->find(request('operation_type_id'))->name ?? '' }}
                                            <a href="{{ request()->fullUrlWithQuery(['operation_type_id' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('department_id'))
                                        <span class="badge bg-info">
                                            {{ localize('global.department') }}: {{ $departments->find(request('department_id'))->name ?? '' }}
                                            <a href="{{ request()->fullUrlWithQuery(['department_id' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('anesthesia_type'))
                                        <span class="badge bg-warning">
                                            {{ localize('global.anesthesia_type') }}: {{ request('anesthesia_type') }}
                                            <a href="{{ request()->fullUrlWithQuery(['anesthesia_type' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('date_from'))
                                        <span class="badge bg-secondary">
                                            {{ localize('global.from') }}: {{ request('date_from') }}
                                            <a href="{{ request()->fullUrlWithQuery(['date_from' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('date_to'))
                                        <span class="badge bg-secondary">
                                            {{ localize('global.to') }}: {{ request('date_to') }}
                                            <a href="{{ request()->fullUrlWithQuery(['date_to' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Results Card --}}
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-list-ul me-2 text-primary"></i>
                        {{ localize('global.results') ?: 'Results' }}
                        <span class="badge bg-primary ms-2">{{ $anesthesias->total() }}</span>
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">
                            {{ localize('global.showing') ?: 'Showing' }} {{ $anesthesias->firstItem() ?? 0 }} 
                            {{ localize('global.to') ?: 'to' }} {{ $anesthesias->lastItem() ?? 0 }} 
                            {{ localize('global.of') ?: 'of' }} {{ $anesthesias->total() }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>{{ localize('global.card_number') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.father_name') }}</th>
                                    <th>{{ localize('global.operation_type') }}</th>
                                    <th>{{ localize('global.operation_surgion') }}</th>
                                    <th>{{ localize('global.date') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th style="width: 100px;">{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($anesthesias as $anesthesia)
                                    <tr>
                                        <td>{{ $anesthesias->firstItem() + $loop->index }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $anesthesia->patient->id_card ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-user me-2 text-primary"></i>
                                                <strong>{{ $anesthesia->patient->name ?? 'N/A' }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $anesthesia->patient->father_name ?? '-' }}</span>
                                        </td>
                                        <td>
                                            @if($anesthesia->operationType)
                                                <span class="badge bg-label-success">
                                                    <i class="bx bx-plus-medical me-1"></i>
                                                    {{ $anesthesia->operationType->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($anesthesia->surgion)
                                                <span class="text-primary">
                                                    <i class="bx bx-user-md me-1"></i>
                                                    {{ $anesthesia->surgion->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($anesthesia->date)
                                                <span class="text-muted">
                                                    <i class="bx bx-calendar me-1"></i>
                                                    {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($anesthesia->date) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <i class="bx bx-time me-1"></i>
                                                {{ localize('global.new') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('anesthesias.show', $anesthesia) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="{{ localize('global.view') ?: 'View' }}">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="bx bx-inbox fs-1 text-muted mb-3"></i>
                                                <p class="text-muted mb-0">{{ localize('global.no_anesthesias_found') ?: 'No new anesthesias found' }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($anesthesias->hasPages())
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                {{ localize('global.showing') ?: 'Showing' }} {{ $anesthesias->firstItem() ?? 0 }} 
                                {{ localize('global.to') ?: 'to' }} {{ $anesthesias->lastItem() ?? 0 }} 
                                {{ localize('global.of') ?: 'of' }} {{ $anesthesias->total() }} 
                                {{ localize('global.results') ?: 'results' }}
                            </div>
                            <div>
                                {{ $anesthesias->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .card-header[data-bs-toggle="collapse"] {
            cursor: pointer;
        }

        .badge {
            font-weight: 500;
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #6c757d;
        }

        /* Select2 Styles */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 0;
            padding-right: 20px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            right: 10px;
        }

        .select2-dropdown {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }

        .select2-results__option {
            padding: 0.5rem 0.75rem;
        }

        .select2-results__option--highlighted {
            background-color: #0d6efd;
            color: white;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            margin-right: 20px;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Select2 with proper configuration
            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2').each(function() {
                    var $select = $(this);
                    if (!$select.hasClass('select2-hidden-accessible')) {
                        $select.select2({
                            width: '100%',
                            placeholder: '{{ localize("global.select") }}...',
                            allowClear: true,
                            language: {
                                noResults: function() {
                                    return '{{ localize("global.no_results_found") ?: "No results found" }}';
                                }
                            }
                        });
                    }
                });
            } else {
                console.warn('Select2 is not loaded');
            }

            // Auto-submit on filter change
            $('#per_page').on('change', function() {
                $('#filterForm').submit();
            });

            // Reinitialize Select2 when filter collapse is shown
            $('#filterCollapse').on('shown.bs.collapse', function() {
                if (typeof $.fn.select2 !== 'undefined') {
                    $('.select2').each(function() {
                        var $select = $(this);
                        if (!$select.hasClass('select2-hidden-accessible')) {
                            $select.select2({
                                width: '100%',
                                placeholder: '{{ localize("global.select") }}...',
                                allowClear: true
                            });
                        }
                    });
                }
            });
        });
    </script>
@endsection
