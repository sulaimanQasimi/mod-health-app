@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error') || Session::has('warning'))
                @include('components.toast')
            @endif
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">{{ localize('global.pharmacy_stock') }}</h5>
                            @if($userPharmacies && $userPharmacies->isNotEmpty())
                            <small class="text-muted">
                                <i class="bx bx-building me-1"></i>
                                {{ localize('global.pharmacies') }}:
                                @foreach($userPharmacies as $pharmacy)
                                    <strong>{{ $pharmacy->name }}</strong>@if(!$loop->last), @endif
                                @endforeach
                            </small>
                            @endif
                        </div>
                        <div class="pt-3 pt-md-0 text-end">
                            <a class="btn btn-secondary" href="{{ route('pharmacy_fulfillments.index') }}">
                                <i class="bx bx-list-ul me-sm-1"></i>{{ localize('global.pharmacy_fulfillments') }}
                            </a>
                            <a class="btn btn-outline-secondary" href="{{ route('outcomes.index') }}">
                                <i class="bx bx-exit me-sm-1"></i>{{ localize('global.stock_outcome') }}
                            </a>
                        </div>
                    </div>

                    <!-- Search Form -->
                    <div class="card-body border-bottom">
                        <form method="GET" action="{{ route('pharmacy_fulfillments.stock') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">{{ localize('global.search') }}</label>
                                <input type="text" class="form-control" id="search" name="search"
                                       value="{{ request('search') }}" placeholder="{{ localize('global.search_by_medicine') }}">
                            </div>
                            @if($pharmacies && $pharmacies->isNotEmpty())
                            <div class="col-md-3">
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
                                <a href="{{ route('pharmacy_fulfillments.stock') }}" class="btn btn-secondary">
                                    <i class="bx bx-refresh"></i> {{ localize('global.clear') }}
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">
                                {{ localize('global.showing') }} {{ $stockItems->firstItem() ?? 0 }}
                                {{ localize('global.to') }} {{ $stockItems->lastItem() ?? 0 }}
                                {{ localize('global.of') }} {{ $stockItems->total() }}
                                {{ localize('global.results') }}
                            </span>
                        </div>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.medicine') }}</th>
                                    <th>{{ localize('global.pharmacy') }}</th>
                                    <th>{{ localize('global.stock_income') }}</th>
                                    <th>{{ localize('global.pharmacy_outcome') }}</th>
                                    <th>{{ localize('global.stock') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stockItems as $row)
                                    <tr>
                                        <td>{{ $row->medicine_name ?? 'N/A' }}</td>
                                        <td>{{ $row->pharmacy_name ?? 'N/A' }}</td>
                                        <td><span class="badge bg-success">{{ (int) $row->income }}</span></td>
                                        <td><span class="badge bg-warning">{{ (int) $row->outcome }}</span></td>
                                        <td>
                                            @php $stock = (int) $row->stock; @endphp
                                            @if($stock < 0)
                                                <span class="badge bg-danger">{{ $stock }}</span>
                                            @elseif($stock == 0)
                                                <span class="badge bg-secondary">{{ $stock }}</span>
                                            @else
                                                <span class="badge bg-primary">{{ $stock }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{ localize('global.no_pharmacy_stock_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                {{ localize('global.page') }} {{ $stockItems->currentPage() }}
                                {{ localize('global.of') }} {{ $stockItems->lastPage() }}
                            </div>
                            <div>
                                {{ $stockItems->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
