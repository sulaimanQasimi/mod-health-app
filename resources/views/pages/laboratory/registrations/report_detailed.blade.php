@extends('layouts.master')
@section('title', localize('global.test_registration_report_detailed') ?? 'Full Detailed Test Report')
@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold mb-1">
                            <i class="bx bx-list-ul me-2"></i>
                            {{ localize('global.test_registration_report_detailed') ?? 'Full Detailed Test Report' }}
                        </h4>
                        <p class="text-muted mb-0">{{ localize('global.view_all_test_registrations_with_who_processed') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Filters Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header border-bottom">
                <div class="d-flex align-items-center">
                    <i class="bx bx-search-alt-2 me-2 fs-5"></i>
                    <h5 class="mb-0">{{ localize('global.documents.search') }}</h5>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('laboratory.registrations.report-detailed') }}" id="search-form">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-calendar me-1"></i>
                                {{ localize('global.between_two_date') }}
                            </label>
                            <div class="input-group input-daterange">
                                <input autocomplete="off" type="text" name="from"
                                    value="{{ old('from', request('from')) }}"
                                    placeholder="{{ localize('global.from') }}"
                                    class="form-control datepicker_dari" />
                                <span class="input-group-text bg-light">
                                    <i class="bx bx-right-arrow-alt"></i>
                                </span>
                                <input autocomplete="off" type="text" name="to"
                                    value="{{ old('to', request('to')) }}"
                                    placeholder="{{ localize('global.to') }}"
                                    class="form-control datepicker_dari" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-category me-1"></i>
                                {{ localize('global.test_type') }}
                            </label>
                            <select class="form-select select2" name="test_type" id="test_type">
                                <option value="">{{ localize('global.all') }}</option>
                                @if(isset($labTypes))
                                    @foreach($labTypes as $labType)
                                        <option value="{{ $labType->id }}" {{ request('test_type') == $labType->id ? 'selected' : '' }}>
                                            {{ $labType->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-check-circle me-1"></i>
                                {{ localize('global.status') }}
                            </label>
                            <select class="form-select select2" name="status" id="status">
                                <option value="">{{ localize('global.all') }}</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ localize('global.status_pending') }}</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ localize('global.status_in_progress') }}</option>
                                <option value="completed" {{ request('status', 'completed') == 'completed' ? 'selected' : '' }}>{{ localize('global.status_completed') }}</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ localize('global.status_cancelled') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-user me-1"></i>
                                {{ localize('global.patient_id') }}
                            </label>
                            <input type="text" class="form-control" name="patient_id" id="patient_id"
                                value="{{ old('patient_id', request('patient_id')) }}"
                                placeholder="{{ localize('global.search_by_patient_id') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-list-ul me-1"></i>
                                {{ localize('global.per_page') }}
                            </label>
                            <select class="form-select" name="per_page" id="per_page">
                                <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                                <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', 15) == 100 ? 'selected' : '' }}>100</option>
                                <option value="all" {{ request('per_page', 15) == 'all' ? 'selected' : '' }}>{{ localize('global.all') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ localize('global.doctor') }}</label>
                            <select class="form-select select2" name="doctor_id" id="doctor_id">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($doctors ?? [] as $d)
                                    <option value="{{ $d->id }}" {{ request('doctor_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ localize('global.branch') }}</label>
                            <select class="form-select select2" name="branch_id" id="branch_id">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($branches ?? [] as $b)
                                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ localize('global.department') }}</label>
                            <select class="form-select select2" name="department_id" id="department_id">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($departments ?? [] as $dept)
                                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ localize('global.created_by') }}</label>
                            <select class="form-select select2" name="created_by" id="created_by">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($users ?? [] as $u)
                                    <option value="{{ $u->id }}" {{ request('created_by') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ localize('global.updated_by') }}</label>
                            <select class="form-select select2" name="updated_by" id="updated_by">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($users ?? [] as $u)
                                    <option value="{{ $u->id }}" {{ request('updated_by') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ localize('global.completed_by') }}</label>
                            <select class="form-select select2" name="completed_by" id="completed_by">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($users ?? [] as $u)
                                    <option value="{{ $u->id }}" {{ request('completed_by') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ localize('global.completed_at') }}</label>
                            <div class="input-group input-daterange">
                                <input autocomplete="off" type="text" name="completed_at_from" value="{{ request('completed_at_from') }}" placeholder="{{ localize('global.from') }}" class="form-control datepicker_dari" />
                                <span class="input-group-text bg-light">...</span>
                                <input autocomplete="off" type="text" name="completed_at_to" value="{{ request('completed_at_to') }}" placeholder="{{ localize('global.to') }}" class="form-control datepicker_dari" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ localize('global.assigned_to') }}</label>
                            <select class="form-select select2" name="assigned_to" id="assigned_to">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($users ?? [] as $u)
                                    <option value="{{ $u->id }}" {{ request('assigned_to') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ localize('global.assigned_at') }}</label>
                            <div class="input-group input-daterange">
                                <input autocomplete="off" type="text" name="assigned_at_from" value="{{ request('assigned_at_from') }}" placeholder="{{ localize('global.from') }}" class="form-control datepicker_dari" />
                                <span class="input-group-text bg-light">...</span>
                                <input autocomplete="off" type="text" name="assigned_at_to" value="{{ request('assigned_at_to') }}" placeholder="{{ localize('global.to') }}" class="form-control datepicker_dari" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ localize('global.assigned_section') ?? 'Assigned Section' }}</label>
                            <select class="form-select select2" name="assigned_section_id" id="assigned_section_id">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($sections ?? [] as $sec)
                                    <option value="{{ $sec->id }}" {{ request('assigned_section_id') == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">{{ localize('global.notes') }}</label>
                            <input type="text" class="form-control" name="notes" id="notes" value="{{ request('notes') }}" placeholder="{{ localize('global.notes') }}">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-search me-1"></i>
                                {{ localize('global.documents.search') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="reset-form-btn">
                                <i class="bx bx-refresh me-1"></i>
                                {{ localize('global.reset') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Card -->
        @if(isset($items) && $items->count() > 0)
            @php
                $totalCount = $items->total ?? $items->count();
            @endphp

            <!-- Statistics -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-4">
                    <div class="card bg-label-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.total_registrations') ?? 'Total Rows' }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-primary" style="font-size: xx-large;">
                                            {{ number_format($totalCount) }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge bg-primary rounded p-2">
                                    <i class="bx bx-list-check bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card bg-label-info">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.date_range') ?? 'Date Range' }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h6 class="mb-0 me-2 fw-semibold">
                                            @if(request('from') && request('to'))
                                                {{ request('from') }} - {{ request('to') }}
                                            @else
                                                {{ localize('global.all_dates') ?? 'All Dates' }}
                                            @endif
                                        </h6>
                                    </div>
                                </div>
                                <span class="badge bg-info rounded p-2">
                                    <i class="bx bx-calendar-check bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="card shadow-sm">
                <div class="card-header border-bottom">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-table me-2 fs-5"></i>
                            <h5 class="mb-0">{{ localize('global.report_results') ?? 'Report Results' }} ({{ localize('global.no_grouping') }})</h5>
                        </div>
                        <form action="{{ route('laboratory.registrations.export-report-detailed') }}" method="POST" class="d-inline" id="export-form-detailed">
                            @csrf
                            <input type="hidden" name="from" value="{{ request('from', '') }}">
                            <input type="hidden" name="to" value="{{ request('to', '') }}">
                            <input type="hidden" name="test_type" value="{{ request('test_type', '') }}">
                            <input type="hidden" name="patient_id" value="{{ request('patient_id', '') }}">
                            <input type="hidden" name="status" value="{{ request('status', 'completed') }}">
                            <input type="hidden" name="doctor_id" value="{{ request('doctor_id', '') }}">
                            <input type="hidden" name="branch_id" value="{{ request('branch_id', '') }}">
                            <input type="hidden" name="department_id" value="{{ request('department_id', '') }}">
                            <input type="hidden" name="created_by" value="{{ request('created_by', '') }}">
                            <input type="hidden" name="updated_by" value="{{ request('updated_by', '') }}">
                            <input type="hidden" name="completed_by" value="{{ request('completed_by', '') }}">
                            <input type="hidden" name="completed_at_from" value="{{ request('completed_at_from', '') }}">
                            <input type="hidden" name="completed_at_to" value="{{ request('completed_at_to', '') }}">
                            <input type="hidden" name="assigned_to" value="{{ request('assigned_to', '') }}">
                            <input type="hidden" name="assigned_at_from" value="{{ request('assigned_at_from', '') }}">
                            <input type="hidden" name="assigned_at_to" value="{{ request('assigned_at_to', '') }}">
                            <input type="hidden" name="assigned_section_id" value="{{ request('assigned_section_id', '') }}">
                            <input type="hidden" name="notes" value="{{ request('notes', '') }}">
                            <button type="submit" name="type" value="excel" class="btn btn-sm btn-success me-2">
                                <i class="bx bx-file me-1"></i>Excel
                            </button>
                            <button type="submit" name="type" value="pdf" class="btn btn-sm btn-danger">
                                <i class="bx bx-file-blank me-1"></i>PDF
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 table-bordered" id="print_excel_table">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th>{{ localize('global.ref_no') }}</th>
                                    <th>{{ localize('global.registration_date') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.test_type') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.priority') }}</th>
                                    <th>{{ localize('global.doctor') }}</th>
                                    <th>{{ localize('global.branch') }}</th>
                                    <th>{{ localize('global.department') }}</th>
                                    <th>{{ localize('global.created_by') }}</th>
                                    <th>{{ localize('global.updated_by') }}</th>
                                    <th>{{ localize('global.completed_by') ?? 'Completed By' }}</th>
                                    <th>{{ localize('global.completed_at') ?? 'Completed At' }}</th>
                                    <th>{{ localize('global.assigned_to') }}</th>
                                    <th>{{ localize('global.assigned_at') ?? 'Assigned At' }}</th>
                                    <th>{{ localize('global.assigned_section') ?? 'Section' }}</th>
                                    <th>{{ localize('global.notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $rowNumber = 1;
                                    if (is_a($items, 'Illuminate\Pagination\LengthAwarePaginator')) {
                                        $rowNumber = $items->firstItem() ?? 1;
                                    }
                                @endphp
                                @foreach ($items as $item)
                                    @php
                                        $patientName = $item->testable && method_exists($item->testable, 'patient') && $item->testable->patient
                                            ? $item->testable->patient->name
                                            : '—';
                                        $regDate = $item->registration_date
                                            ? \Hekmatinasser\Verta\Verta::instance($item->registration_date)->format('Y/m/d')
                                            : '—';
                                        $completedAt = $item->completed_at
                                            ? \Hekmatinasser\Verta\Verta::instance($item->completed_at)->format('Y/m/d H:i')
                                            : '—';
                                        $assignedAt = $item->assigned_at
                                            ? \Hekmatinasser\Verta\Verta::instance($item->assigned_at)->format('Y/m/d H:i')
                                            : '—';
                                        $statusLabel = $item->status === 'pending' ? (localize('global.status_pending') ?? 'Pending') :
                                            ($item->status === 'in_progress' ? (localize('global.status_in_progress') ?? 'In Progress') :
                                            ($item->status === 'completed' ? (localize('global.status_completed') ?? 'Completed') :
                                            ($item->status === 'cancelled' ? (localize('global.status_cancelled') ?? 'Cancelled') : $item->status)));
                                    @endphp
                                    <tr>
                                        <td class="text-center"><span class="badge bg-label-secondary">{{ $rowNumber }}</span></td>
                                        <td>{{ $item->ref_no ?? '—' }}</td>
                                        <td>{{ $regDate }}</td>
                                        <td>{{ $patientName }}</td>
                                        <td>{{ $item->labType ? $item->labType->name : '—' }}</td>
                                        <td><span class="badge bg-label-info">{{ $statusLabel }}</span></td>
                                        <td>{{ $item->priority ?? '—' }}</td>
                                        <td>{{ $item->doctor ? $item->doctor->name : '—' }}</td>
                                        <td>{{ $item->branch ? $item->branch->name : '—' }}</td>
                                        <td>{{ $item->assignedSection && $item->assignedSection->department ? $item->assignedSection->department->name : '—' }}</td>
                                        <td>{{ $item->creator ? $item->creator->name : '—' }}</td>
                                        <td>{{ $item->updater ? $item->updater->name : '—' }}</td>
                                        <td>{{ $item->completedBy ? $item->completedBy->name : '—' }}</td>
                                        <td>{{ $completedAt }}</td>
                                        <td>{{ $item->assignedTo ? $item->assignedTo->name : '—' }}</td>
                                        <td>{{ $assignedAt }}</td>
                                        <td>{{ $item->assignedSection ? $item->assignedSection->name : '—' }}</td>
                                        <td class="text-truncate" style="max-width: 120px;" title="{{ $item->notes ?? '' }}">{{ $item->notes ?? '—' }}</td>
                                    </tr>
                                    @php $rowNumber++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(is_a($items, 'Illuminate\Pagination\LengthAwarePaginator') && $items->hasPages())
                        <div class="card-footer border-top bg-light">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div class="text-muted small mb-2 mb-md-0">
                                    <i class="bx bx-info-circle me-1"></i>
                                    {{ localize('global.showing') }} {{ $items->firstItem() }} {{ localize('global.to') }} {{ $items->lastItem() }}
                                    {{ localize('global.of') }} {{ $items->total() }} {{ localize('global.results') }}
                                </div>
                                <div>
                                    {{ $items->links() }}
                                </div>
                            </div>
                        </div>
                    @elseif(is_a($items, 'Illuminate\Pagination\LengthAwarePaginator'))
                        <div class="card-footer border-top bg-light">
                            <div class="text-muted small">
                                <i class="bx bx-info-circle me-1"></i>
                                {{ localize('global.showing') }} {{ $items->total() }} {{ localize('global.results') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @elseif(isset($items) && $items->count() == 0)
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-xl bg-label-warning mb-3 mx-auto">
                        <i class="bx bx-search-alt-2 fs-1"></i>
                    </div>
                    <h5 class="mb-2">{{ localize('global.no_item_is_found') }}</h5>
                    <p class="text-muted mb-4">
                        {{ localize('global.no_results_found_for_selected_filters') ?? 'No test registrations found for the selected filters.' }}
                    </p>
                    <button type="button" class="btn btn-outline-primary" id="reset-form-btn">
                        <i class="bx bx-refresh me-1"></i>
                        {{ localize('global.reset_filters') ?? 'Reset Filters' }}
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('custom-js')
<script>
    $(document).ready(function() {
        function initFilterSelect2() {
            var selectIds = ['#test_type', '#status', '#doctor_id', '#branch_id', '#department_id', '#created_by', '#updated_by', '#completed_by', '#assigned_to', '#assigned_section_id'];
            selectIds.forEach(function(id) {
                var $el = $(id);
                if ($el.length && !$el.hasClass('select2-hidden-accessible')) {
                    $el.wrap('<div class="position-relative"></div>').select2({
                        placeholder: '{{ localize("global.all") }}',
                        allowClear: true,
                        width: '100%',
                        language: { noResults: function() { return '{{ localize("global.no_results_found") ?? "No results found" }}'; } },
                        dropdownParent: $el.parent()
                    });
                }
            });
        }
        setTimeout(initFilterSelect2, 200);

        $('#per_page').on('change', function() {
            $('#search-form').submit();
        });
        $(document).on('change', '#test_type', function() {
            $('#search-form').submit();
        });

        $('#reset-form-btn').on('click', function(e) {
            e.preventDefault();
            $('#search-form input[type="text"]').val('');
            $('#search-form input[name="patient_id"]').val('');
            $('#search-form input[name="notes"]').val('');
            $('#per_page').val('15');
            $('#status').val('completed').trigger('change');
            $('#test_type, #doctor_id, #branch_id, #department_id, #created_by, #updated_by, #completed_by, #assigned_to, #assigned_section_id').val('').trigger('change');
            $('.datepicker_dari').val('');
            window.location.href = '{{ route("laboratory.registrations.report-detailed") }}';
        });

        $('#search-form').on('submit', function() {
            $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>{{ localize("global.loading") ?? "Loading..." }}');
        });
    });
</script>
@endpush

@push('custom-css')
<style>
.sadira_date_range, .wareda_date_range { display: none; }
.card { border: none; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); border-radius: 8px; }
.bg-label-primary { background: var(--bs-primary-bg-subtle); border: 1px solid var(--bs-primary-border-subtle); }
.bg-label-info { background: var(--bs-info-bg-subtle); border: 1px solid var(--bs-info-border-subtle); }
.bg-label-secondary { background-color: rgba(105, 122, 141, 0.1); color: #697a8d; }
.table-hover tbody tr:hover { background-color: rgba(105, 108, 255, 0.05); }
.table thead th { font-weight: 600; font-size: 0.75rem; padding: 0.6rem; }
.table tbody td { padding: 0.5rem; vertical-align: middle; font-size: 0.875rem; }
@media print {
    .card-header, .btn, .card-footer, .card.mb-4 { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>
@endpush
