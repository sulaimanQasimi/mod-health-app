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
                        <div>
                            <h5 class="mb-0">{{ localize('global.medicine_usage_statistics') }}</h5>
                        </div>
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
                                       value="{{ request('search') }}" placeholder="{{ localize('global.search_by_medicine') }}">
                            </div>
                            @if($pharmacies)
                            <div class="col-md-2">
                                <label for="pharmacy_id" class="form-label">{{ localize('global.pharmacy') }}</label>
                                <select class="form-select" id="pharmacy_id" name="pharmacy_id">
                                    <option value="">{{ localize('global.all_pharmacies') }}</option>
                                    @foreach ($pharmacies as $pharmacy)
                                        <option value="{{ $pharmacy->id }}" {{ request('pharmacy_id') == $pharmacy->id ? 'selected' : '' }}>
                                            {{ $pharmacy->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-md-3">
                                <label class="form-label">{{ localize('global.between_two_date') }}</label>
                                <div class="input-group input-daterange">
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
                                    <option value="usage_count" {{ request('sort_by', 'usage_count') == 'usage_count' ? 'selected' : '' }}>{{ localize('global.usage_count') }}</option>
                                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>{{ localize('global.medicine') }}</option>
                                    <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>{{ localize('global.id') }}</option>
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
                                    <th>{{localize('global.usage_count')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($outcomes as $medicine)
                                    <tr>
                                        <td>{{ $medicine->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $medicine->usage_count ?? 0 }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">{{ localize('global.no_medicine_usage_found') }}</td>
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