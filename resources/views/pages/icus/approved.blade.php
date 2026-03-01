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
                        <h5 class="mb-0">{{ localize('global.approved_icus') }}</h5>
                    </div>
                    <div class="card-body">
                        {{-- Advanced Filters (collapsible) - same design as anesthesias approved --}}
                        @php
                            $hasActiveFilters = request()->hasAny(['discharge_filter', 'patient_name', 'card_number', 'father_name', 'search', 'per_page']) && (request('discharge_filter', 'in_icu') !== 'in_icu' || request()->filled('patient_name') || request()->filled('card_number') || request()->filled('father_name') || request()->filled('search') || request('per_page', 15) != 15);
                        @endphp
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-none border-0 py-3 cursor-pointer d-flex align-items-center justify-content-between" data-bs-toggle="collapse" data-bs-target="#icuApprovedFilterCollapse" aria-expanded="{{ $hasActiveFilters ? 'true' : 'false' }}" aria-controls="icuApprovedFilterCollapse" role="button">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-filter-alt text-primary me-2" style="font-size: 1.2rem;"></i>
                                    <h6 class="mb-0 fw-semibold">{{ localize('global.advanced_filters') ?: 'Advanced Filters' }}</h6>
                                    @if($hasActiveFilters)
                                        <span class="badge bg-label-primary ms-2">{{ (request('discharge_filter', 'in_icu') !== 'in_icu' ? 1 : 0) + count(array_filter(request()->only(['patient_name', 'card_number', 'father_name', 'search']))) + (request('per_page', 15) != 15 ? 1 : 0) }}</span>
                                    @endif
                                </div>
                                <i class="bx bx-chevron-down transition-transform collapse-icon"></i>
                            </div>
                            <div class="collapse {{ $hasActiveFilters ? 'show' : '' }}" id="icuApprovedFilterCollapse">
                                <div class="card-body">
                                    <form method="GET" action="{{ route('icus.approved') }}" id="icuApprovedFilterForm">
                                        <input type="hidden" name="discharge_filter" value="{{ request('discharge_filter', 'in_icu') }}">
                                        {{-- Discharge status section --}}
                                        <div class="mb-4 pb-3 border-bottom">
                                            <label class="form-label fw-semibold text-muted small text-uppercase mb-2">
                                                <i class="bx bx-pie-chart-alt me-1"></i>{{ localize('global.filter_by_discharge') }}
                                            </label>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="{{ route('icus.approved', array_merge(request()->except(['discharge_filter', 'page']), ['discharge_filter' => 'all'])) }}"
                                                    class="btn btn-sm {{ (request('discharge_filter', 'in_icu') === 'all') ? 'btn-primary' : 'btn-outline-primary' }}">
                                                    {{ localize('global.all_approved') }}
                                                </a>
                                                <a href="{{ route('icus.approved', array_merge(request()->except(['discharge_filter', 'page']), ['discharge_filter' => 'in_icu'])) }}"
                                                    class="btn btn-sm {{ (request('discharge_filter', 'in_icu') === 'in_icu') ? 'btn-primary' : 'btn-outline-primary' }}">
                                                    <i class="bx bx-bed me-1"></i>{{ localize('global.in_icu') }}
                                                </a>
                                                <a href="{{ route('icus.approved', array_merge(request()->except(['discharge_filter', 'page']), ['discharge_filter' => 'discharged'])) }}"
                                                    class="btn btn-sm {{ (request('discharge_filter', 'in_icu') === 'discharged') ? 'btn-primary' : 'btn-outline-primary' }}">
                                                    {{ localize('global.discharged') }}
                                                </a>
                                                <a href="{{ route('icus.approved', array_merge(request()->except(['discharge_filter', 'page']), ['discharge_filter' => 'recovered'])) }}"
                                                    class="btn btn-sm {{ (request('discharge_filter', 'in_icu') === 'recovered') ? 'btn-success' : 'btn-outline-success' }}">
                                                    <i class="bx bx-check-circle me-1"></i>{{ localize('global.recovered') }}
                                                </a>
                                                <a href="{{ route('icus.approved', array_merge(request()->except(['discharge_filter', 'page']), ['discharge_filter' => 'died'])) }}"
                                                    class="btn btn-sm {{ (request('discharge_filter', 'in_icu') === 'died') ? 'btn-danger' : 'btn-outline-danger' }}">
                                                    <i class="bx bx-x-circle me-1"></i>{{ localize('global.died') }}
                                                </a>
                                                <a href="{{ route('icus.approved', array_merge(request()->except(['discharge_filter', 'page']), ['discharge_filter' => 'moved'])) }}"
                                                    class="btn btn-sm {{ (request('discharge_filter', 'in_icu') === 'moved') ? 'btn-warning' : 'btn-outline-warning' }}">
                                                    <i class="bx bx-transfer me-1"></i>{{ localize('global.moved') }}
                                                </a>
                                            </div>
                                        </div>
                                        {{-- Search row (like anesthesia) --}}
                                        <div class="row g-3">
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
                                                        placeholder="{{ localize('global.search_patient_placeholder') }}"
                                                        autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="patient_name" class="form-label fw-semibold">
                                                    <i class="bx bx-user me-1 text-success"></i>{{ localize('global.patient_name') }}
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-success text-white">
                                                        <i class="bx bx-user"></i>
                                                    </span>
                                                    <input type="text" class="form-control" id="patient_name" name="patient_name"
                                                        value="{{ request('patient_name') }}"
                                                        placeholder="{{ localize('global.search_by_patient_name') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="card_number" class="form-label fw-semibold">
                                                    <i class="bx bx-id-card me-1 text-info"></i>{{ localize('global.card_number') }}
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-info text-white">
                                                        <i class="bx bx-id-card"></i>
                                                    </span>
                                                    <input type="text" class="form-control" id="card_number" name="card_number"
                                                        value="{{ request('card_number') }}"
                                                        placeholder="{{ localize('global.search_by_card_number') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="father_name" class="form-label fw-semibold">
                                                    <i class="bx bx-user-circle me-1 text-secondary"></i>{{ localize('global.father_name') }}
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-secondary text-white">
                                                        <i class="bx bx-user-circle"></i>
                                                    </span>
                                                    <input type="text" class="form-control" id="father_name" name="father_name"
                                                        value="{{ request('father_name') }}"
                                                        placeholder="{{ localize('global.search_by_father_name') }}">
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Per Page + Buttons row --}}
                                        <div class="row g-3 mt-2">
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
                                            <div class="col-md-4 d-flex align-items-end gap-2">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bx bx-filter me-1"></i>{{ localize('global.apply_filters') ?: 'Apply Filters' }}
                                                </button>
                                                <a href="{{ route('icus.approved') }}" class="btn btn-outline-secondary">
                                                    <i class="bx bx-refresh me-1"></i>{{ localize('global.reset') ?: 'Reset' }}
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        {{-- Active filters chips --}}
                        @if($hasActiveFilters && (request()->filled('patient_name') || request()->filled('card_number') || request()->filled('father_name') || request()->filled('search')))
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                <span class="text-muted small fw-semibold">{{ localize('global.active_filters') ?: 'Active Filters' }}:</span>
                                @if(request('patient_name'))
                                    <a href="{{ request()->fullUrlWithQuery(['patient_name' => null, 'page' => null]) }}" class="badge bg-primary py-2 px-2 text-decoration-none">{{ localize('global.patient_name') }}: {{ request('patient_name') }} <i class="bx bx-x ms-1"></i></a>
                                @endif
                                @if(request('card_number'))
                                    <a href="{{ request()->fullUrlWithQuery(['card_number' => null, 'page' => null]) }}" class="badge bg-info py-2 px-2 text-decoration-none">{{ localize('global.card_number') }}: {{ request('card_number') }} <i class="bx bx-x ms-1"></i></a>
                                @endif
                                @if(request('father_name'))
                                    <a href="{{ request()->fullUrlWithQuery(['father_name' => null, 'page' => null]) }}" class="badge bg-secondary py-2 px-2 text-decoration-none">{{ localize('global.father_name') }}: {{ request('father_name') }} <i class="bx bx-x ms-1"></i></a>
                                @endif
                                @if(request('search'))
                                    <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => null]) }}" class="badge bg-success py-2 px-2 text-decoration-none">{{ localize('global.search') }}: {{ request('search') }} <i class="bx bx-x ms-1"></i></a>
                                @endif
                            </div>
                        @endif

                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.card_number') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.father_name') }}</th>
                                    <th>{{ localize('global.room') }}</th>
                                    <th>{{ localize('global.bed') }}</th>
                                    <th>{{ localize('global.description') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($icus as $icu)
                                    <tr>
                                        <td>{{ $icus->firstItem() + $loop->index }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $icu->patient->id_card ?? '-' }}</span>
                                        </td>
                                        <td>{{ $icu->patient->name ?? '-' }}</td>
                                        <td>
                                            <span class="text-muted">{{ $icu->patient->father_name ?? '-' }}</span>
                                        </td>
                                        <td>{{ $icu->hospitalization && $icu->hospitalization->room ? $icu->hospitalization->room->name : '—' }}</td>
                                        <td>{{ $icu->hospitalization && $icu->hospitalization->bed ? $icu->hospitalization->bed->number : '—' }}</td>
                                        <td>{{ Str::limit($icu->description, 40) }}</td>
                                        <td>
                                            @if ($icu->is_discharged)
                                                @if ($icu->discharge_status === 'recovered')
                                                    <span class="badge bg-success">{{ localize('global.recovered') }}</span>
                                                @elseif ($icu->discharge_status === 'died')
                                                    <span class="badge bg-danger">{{ localize('global.died') }}</span>
                                                @elseif ($icu->discharge_status === 'moved')
                                                    <span class="badge bg-warning">{{ localize('global.moved') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ localize('global.discharged') }}</span>
                                                @endif
                                            @else
                                                <span class="badge bg-success">{{ localize('global.in_icu') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('icus.show', $icu) }}" class="btn btn-sm btn-icon btn-outline-primary" title="{{ localize('global.actions') }}">
                                                <i class="bx bx-expand"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            {{ localize('global.try_adjusting_your_search_criteria') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        {{ $icus->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-css')
    <style>
        [data-bs-toggle="collapse"][aria-controls^="icu"] {
            cursor: pointer;
        }
        [data-bs-toggle="collapse"][aria-expanded="true"] .collapse-icon {
            transform: rotate(180deg);
        }
        .collapse-icon {
            transition: transform 0.2s ease;
        }
    </style>
@endpush
