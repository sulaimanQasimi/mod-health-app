@extends('layouts.master')

@section('content')
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">
                        <i class="bx bx-health me-2 text-info"></i>
                        {{ localize('global.physiotherapy_procedures') }}
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}">{{ localize('global.home') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ localize('global.physiotherapy_procedures') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form id="searchForm" class="row g-3">
                            <div class="col-md-2">
                                <label for="search" class="form-label">{{ localize('global.search') }}</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    placeholder="{{ localize('global.search_patient_name') }}"
                                    value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">{{ localize('global.status') }}</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">{{ localize('global.all_statuses') }}</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                        {{ localize('global.pending') }}
                                    </option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>
                                        {{ localize('global.in_progress') }}
                                    </option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                        {{ localize('global.completed') }}
                                    </option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                        {{ localize('global.cancelled') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="physiotherapy_type_id"
                                    class="form-label">{{ localize('global.physiotherapy_type') }}</label>
                                <select class="form-control" id="physiotherapy_type_id" name="physiotherapy_type_id">
                                    <option value="">{{ localize('global.all_types') }}</option>
                                    @foreach($physiotherapyTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('physiotherapy_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="physiotherapist_id"
                                    class="form-label">{{ localize('global.physiotherapist') }}</label>
                                <select class="form-control" id="physiotherapist_id" name="physiotherapist_id">
                                    <option value="">{{ localize('global.all_physiotherapists') }}</option>
                                    @foreach($physiotherapists as $physio)
                                        <option value="{{ $physio->id }}" {{ request('physiotherapist_id') == $physio->id ? 'selected' : '' }}>
                                            {{ $physio->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="start_date" class="form-label">{{ localize('global.start_date') }}</label>
                                <input type="text" class="form-control datepicker_dari pdp-el persian-date" id="start_date" name="start_date"
                                    value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="end_date" class="form-label">{{ localize('global.end_date') }}</label>
                                <input type="text" class="form-control datepicker_dari pdp-el persian-date" id="end_date" name="end_date"
                                    value="{{ request('end_date') }}">
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search me-1"></i>
                                    {{ localize('global.search') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                                    <i class="bx bx-refresh me-1"></i>
                                    {{ localize('global.reset') }}
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="exportData()">
                                    <i class="bx bx-download me-1"></i>
                                    {{ localize('global.export') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $physiotherapyProcedures->total() }}</h4>
                                <p class="mb-0">{{ localize('global.total_procedures') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bx bx-health bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $physiotherapyProcedures->where('status', 'pending')->count() }}</h4>
                                <p class="mb-0">{{ localize('global.pending') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bx bx-time bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $physiotherapyProcedures->where('status', 'in_progress')->count() }}</h4>
                                <p class="mb-0">{{ localize('global.in_progress') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bx bx-play-circle bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $physiotherapyProcedures->where('status', 'completed')->count() }}</h4>
                                <p class="mb-0">{{ localize('global.completed') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bx bx-check-circle bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Procedures Table -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-none text-dark d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bx bx-list-ul me-2 text-primary"></i>
                            {{ localize('global.procedures_list') }}
                        </h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('physiotherapy-procedures.my-procedures') }}" class="btn btn-outline-primary">
                                <i class="bx bx-user me-1"></i>
                                {{ localize('global.my_procedures') }}
                            </a>
                            <a href="{{ route('physiotherapy-reports.index') }}" class="btn btn-outline-info">
                                <i class="bx bx-chart me-1"></i>
                                {{ localize('global.reports') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="proceduresTable">
                                <thead class="table-bg-none">
                                    <tr>
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.patient_name') }}</th>
                                        <th>{{ localize('global.physiotherapy_type') }}</th>
                                        <th>{{ localize('global.physiotherapist') }}</th>
                                        <th>{{ localize('global.type') }}</th>
                                        <th>{{ localize('global.duration') }}</th>
                                        <th>{{ localize('global.progress') }}</th>
                                        <th>{{ localize('global.status') }}</th>
                                        <th>{{ localize('global.start_date') }}</th>
                                        <th>{{ localize('global.reviews') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($physiotherapyProcedures as $procedure)
                                        <tr>
                                            <td>
                                                <span class="badge bg-info rounded-pill">{{ $loop->iteration }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $procedure->appointment->patient->name ?? 'N/A' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $procedure->appointment->patient->phone ?? 'N/A' }}</small>
                                            </td>
                                            <td>{{ $procedure->physiotherapyType->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $procedure->physiotherapist->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>{{ $procedure->type }}</td>
                                            <td>{{ $procedure->duration }} {{ localize('global.minutes') }}</td>
                                            <td>
                                                @php
                                                    $percentage = $procedure->days_count > 0 ? ($procedure->counter / $procedure->days_count) * 100 : 0;
                                                @endphp
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-info" role="progressbar"
                                                        style="width: {{ $percentage }}%">
                                                        {{ $procedure->counter }}/{{ $procedure->days_count }}
                                                    </div>
                                                </div>
                                                <small class="text-muted">{{ round($percentage, 1) }}%</small>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'secondary',
                                                        'in_progress' => 'warning',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $color = $statusColors[$procedure->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }}">
                                                    {{ localize('global.physiotherapy_procedures_' . $procedure->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $procedure->start_date ? $procedure->start_date->format('Y-m-d') : 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $procedure->reviews->count() }} {{ localize('global.reviews') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-outline-info btn-sm"
                                                        onclick="viewProcedure({{ $procedure->id }})"
                                                        title="{{ localize('global.view') }}">
                                                        <i class="bx bx-show"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success btn-sm"
                                                        onclick="addReview({{ $procedure->id }})"
                                                        title="{{ localize('global.add_review') }}">
                                                        <i class="bx bx-plus"></i>
                                                    </button>
                                                    @if($procedure->status !== 'completed' && $procedure->status !== 'cancelled')
                                                        <button type="button" class="btn btn-outline-warning btn-sm"
                                                            onclick="updateProgress({{ $procedure->id }})"
                                                            title="{{ localize('global.update_progress') }}">
                                                            <i class="bx bx-edit"></i>
                                                        </button>
                                                    @endif
                                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                                        onclick="viewReviews({{ $procedure->id }})"
                                                        title="{{ localize('global.view_reviews') }}">
                                                        <i class="bx bx-message-square-dots"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center text-muted py-4">
                                                <i class="bx bx-inbox bx-lg mb-3"></i>
                                                <p class="mb-0">{{ localize('global.no_procedures_found') }}</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($physiotherapyProcedures->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $physiotherapyProcedures->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        @include('pages.physiotherapy.procedures.partials.modals')
    </div>
@endsection

@push('custom-css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endpush

@push('custom-js')
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endpush

@section('scripts')
    @parent
    <script src="{{ asset('js/physiotherapy-procedures-index.js') }}"></script>
@endsection