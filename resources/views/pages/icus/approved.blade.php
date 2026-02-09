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
                        {{-- Filter by discharge & Search --}}
                        <form method="GET" action="{{ route('icus.approved') }}" class="mb-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">{{ localize('global.filter_by_discharge') }}</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('icus.approved', array_merge(request()->except(['discharge_filter', 'page']), ['discharge_filter' => 'all'])) }}"
                                            class="btn btn-sm {{ (request('discharge_filter', 'in_icu') === 'all') ? 'btn-primary' : 'btn-outline-primary' }}">
                                            {{ localize('global.all_approved') }}
                                        </a>
                                        <a href="{{ route('icus.approved', array_merge(request()->except(['discharge_filter', 'page']), ['discharge_filter' => 'in_icu'])) }}"
                                            class="btn btn-sm {{ (request('discharge_filter', 'in_icu') === 'in_icu') ? 'btn-primary' : 'btn-outline-primary' }}">
                                            {{ localize('global.in_icu') }}
                                        </a>
                                        <a href="{{ route('icus.approved', array_merge(request()->except(['discharge_filter', 'page']), ['discharge_filter' => 'discharged'])) }}"
                                            class="btn btn-sm {{ (request('discharge_filter', 'in_icu') === 'discharged') ? 'btn-primary' : 'btn-outline-primary' }}">
                                            {{ localize('global.discharged') }}
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ localize('global.patient_name') }}</label>
                                    <input type="text" name="patient_name" class="form-control form-control-sm"
                                        value="{{ request('patient_name') }}"
                                        placeholder="{{ localize('global.search_by_patient_name') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">{{ localize('global.card_number') }}</label>
                                    <input type="text" name="card_number" class="form-control form-control-sm"
                                        value="{{ request('card_number') }}"
                                        placeholder="{{ localize('global.search_by_card_number') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">{{ localize('global.father_name') }}</label>
                                    <input type="text" name="father_name" class="form-control form-control-sm"
                                        value="{{ request('father_name') }}"
                                        placeholder="{{ localize('global.search_by_father_name') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">{{ localize('global.search') }}</label>
                                    <input type="text" name="search" class="form-control form-control-sm"
                                        value="{{ request('search') }}"
                                        placeholder="{{ localize('global.search_patient_placeholder') }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end gap-1">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bx bx-search"></i> {{ localize('global.search') }}
                                    </button>
                                    <a href="{{ route('icus.approved') }}" class="btn btn-outline-secondary btn-sm">{{ localize('global.clear_search') }}</a>
                                </div>
                            </div>
                            <input type="hidden" name="discharge_filter" value="{{ request('discharge_filter', 'in_icu') }}">
                        </form>

                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.card_number') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.father_name') }}</th>
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
                                        <td>{{ Str::limit($icu->description, 40) }}</td>
                                        <td>
                                            @if ($icu->is_discharged)
                                                <span class="badge bg-secondary">{{ localize('global.discharged') }}</span>
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
                                        <td colspan="7" class="text-center text-muted py-4">
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
