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
                        <h5 class="mb-0">{{ localize('global.outcome_records') }}</h5>
                        <div>
                            <a href="{{ route('outcomes.report') }}" class="btn btn-success">
                                <i class="bx bx-file me-1"></i>{{ localize('global.reports') }}
                            </a>
                        </div>
                    </div>

                    <!-- Advanced Search Form -->
                    <div class="card-body border-bottom">
                        <form method="GET" action="{{ route('outcomes.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">{{ localize('global.search') }}</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="{{ localize('global.search_by_medicine_patient') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="outcome_type" class="form-label">{{ localize('global.outcome_type') }}</label>
                                <select class="form-select" id="outcome_type" name="outcome_type">
                                    <option value="">{{ localize('global.all_types') }}</option>
                                    <option value="prescription" {{ request('outcome_type') == 'prescription' ? 'selected' : '' }}>
                                        {{ localize('global.prescription') }}
                                    </option>
                                    <option value="expired" {{ request('outcome_type') == 'expired' ? 'selected' : '' }}>
                                        {{ localize('global.expired') }}
                                    </option>
                                    <option value="damaged" {{ request('outcome_type') == 'damaged' ? 'selected' : '' }}>
                                        {{ localize('global.damaged') }}
                                    </option>
                                    <option value="lost" {{ request('outcome_type') == 'lost' ? 'selected' : '' }}>
                                        {{ localize('global.lost') }}
                                    </option>
                                    <option value="return" {{ request('outcome_type') == 'return' ? 'selected' : '' }}>
                                        {{ localize('global.return') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ localize('global.between_two_date') }}</label>
                                <div class="input-group input-daterange" id="bs-datepicker-daterange">
                                    <input type="text" name="date_from"
                                        placeholder="{{ localize('global.from') }}"
                                        class="form-control form-control datepicker_dari pdp-el persian-date" 
                                        value="{{ request('date_from') }}" />
                                    <span class="input-group-text">...</span>
                                    <input type="text" name="date_to"
                                        placeholder="{{ localize('global.to') }}"
                                        class="form-control form-control datepicker_dari pdp-el persian-date" 
                                        value="{{ request('date_to') }}" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="per_page" class="form-label">{{ localize('global.per_page') }}</label>
                                <select class="form-select" id="per_page" name="per_page">
                                    @foreach([10, 15, 25, 50, 100] as $perPage)
                                        <option value="{{ $perPage }}" {{ request('per_page', 15) == $perPage ? 'selected' : '' }}>
                                            {{ $perPage }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search"></i> {{ localize('global.search') }}
                                </button>
                                <a href="{{ route('outcomes.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-refresh"></i> {{ localize('global.clear') }}
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <!-- Results Summary -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="text-muted">
                                    {{ localize('global.showing') }} {{ $outcomes->firstItem() ?? 0 }} 
                                    {{ localize('global.to') }} {{ $outcomes->lastItem() ?? 0 }} 
                                    {{ localize('global.of') }} {{ $outcomes->total() }} 
                                    {{ localize('global.results') }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                <label for="sort_by" class="form-label me-2 mb-0">{{ localize('global.sort_by') }}:</label>
                                <select class="form-select form-select-sm" id="sort_by" name="sort_by" style="width: auto;" onchange="updateSort()">
                                    <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>{{ localize('global.created_at') }}</option>
                                    <option value="outcome_date" {{ request('sort_by') == 'outcome_date' ? 'selected' : '' }}>{{ localize('global.outcome_date') }}</option>
                                    <option value="amount" {{ request('sort_by') == 'amount' ? 'selected' : '' }}>{{ localize('global.amount') }}</option>
                                    <option value="outcome_type" {{ request('sort_by') == 'outcome_type' ? 'selected' : '' }}>{{ localize('global.outcome_type') }}</option>
                                </select>
                                <select class="form-select form-select-sm ms-2" id="sort_order" name="sort_order" style="width: auto;" onchange="updateSort()">
                                    <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>{{ localize('global.descending') }}</option>
                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>{{ localize('global.ascending') }}</option>
                                </select>
                            </div>
                        </div>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{localize('global.medicine')}}</th>
                                    <th>{{localize('global.amount')}}</th>
                                    <th>{{localize('global.batch_number')}}</th>
                                    <th>{{localize('global.patient')}}</th>
                                    <th>{{localize('global.doctor')}}</th>
                                    <th>{{localize('global.outcome_type')}}</th>
                                    <th>{{localize('global.outcome_date')}}</th>
                                    <th>{{localize('global.reason')}}</th>
                                    <th>{{localize('global.created_by')}}</th>
                                    <th>{{localize('global.created_at')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($outcomes as $outcome)
                                    <tr>
                                        <td>{{ $outcome->medicine->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $outcome->amount }}</span>
                                        </td>
                                        <td>{{ $outcome->batch_number ?? '-' }}</td>
                                        <td>{{ $outcome->patient->name ?? 'N/A' }}</td>
                                        <td>{{ $outcome->doctor->name ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'prescription' => 'primary',
                                                    'expired' => 'danger',
                                                    'damaged' => 'warning',
                                                    'lost' => 'dark',
                                                    'return' => 'success'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $typeColors[$outcome->outcome_type] ?? 'secondary' }}">
                                                {{ localize('global.' . $outcome->outcome_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($outcome->outcome_date)
                                                {{ $outcome->outcome_date->format('Y-m-d') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $outcome->reason ?? '-' }}</td>
                                        <td>{{ $outcome->createdBy->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($outcome->created_at)
                                                {{ $outcome->created_at->format('Y-m-d H:i') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">{{ localize('global.no_outcome_records_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        <!-- Enhanced Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                {{ localize('global.page') }} {{ $outcomes->currentPage() }} 
                                {{ localize('global.of') }} {{ $outcomes->lastPage() }}
                            </div>
                            <div>
                                {{ $outcomes->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateSort() {
            const sortBy = document.getElementById('sort_by').value;
            const sortOrder = document.getElementById('sort_order').value;
            const url = new URL(window.location);
            url.searchParams.set('sort_by', sortBy);
            url.searchParams.set('sort_order', sortOrder);
            window.location.href = url.toString();
        }
    </script>
@endsection
@push('custom-css')
    <style>
        .persian-date {
            direction: rtl;
            text-align: right;
        }
    </style>
@endpush
