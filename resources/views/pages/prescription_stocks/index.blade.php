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
                        <h5 class="mb-0">{{ localize('global.prescription_stock') }}</h5>
                    </div>

                    <!-- Advanced Search Form -->
                    <div class="card-body border-bottom">
                        <form method="GET" action="{{ route('prescription_stocks.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">{{ localize('global.search') }}</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="{{ localize('global.search_by_medicine') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="stock_status" class="form-label">{{ localize('global.stock_status') }}</label>
                                <select class="form-select" id="stock_status" name="stock_status">
                                    <option value="">{{ localize('global.all_statuses') }}</option>
                                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>
                                        {{ localize('global.low_stock') }}
                                    </option>
                                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>
                                        {{ localize('global.out_of_stock') }}
                                    </option>
                                    <option value="overstocked" {{ request('stock_status') == 'overstocked' ? 'selected' : '' }}>
                                        {{ localize('global.overstocked') }}
                                    </option>
                                    <option value="expired" {{ request('stock_status') == 'expired' ? 'selected' : '' }}>
                                        {{ localize('global.expired') }}
                                    </option>
                                    <option value="expiring_soon" {{ request('stock_status') == 'expiring_soon' ? 'selected' : '' }}>
                                        {{ localize('global.expiring_soon') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
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
                                <a href="{{ route('prescription_stocks.index') }}" class="btn btn-secondary">
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
                                    {{ localize('global.showing') }} {{ $prescriptionStocks->firstItem() ?? 0 }} 
                                    {{ localize('global.to') }} {{ $prescriptionStocks->lastItem() ?? 0 }} 
                                    {{ localize('global.of') }} {{ $prescriptionStocks->total() }} 
                                    {{ localize('global.results') }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                <label for="sort_by" class="form-label me-2 mb-0">{{ localize('global.sort_by') }}:</label>
                                <select class="form-select form-select-sm" id="sort_by" name="sort_by" style="width: auto;" onchange="updateSort()">
                                    <option value="medicine_name" {{ request('sort_by', 'medicine_name') == 'medicine_name' ? 'selected' : '' }}>{{ localize('global.medicine_name') }}</option>
                                    <option value="current_stock" {{ request('sort_by') == 'current_stock' ? 'selected' : '' }}>{{ localize('global.current_stock') }}</option>
                                    <option value="available_stock" {{ request('sort_by') == 'available_stock' ? 'selected' : '' }}>{{ localize('global.available_stock') }}</option>
                                    <option value="earliest_expiry" {{ request('sort_by') == 'earliest_expiry' ? 'selected' : '' }}>{{ localize('global.earliest_expiry') }}</option>
                                </select>
                                <select class="form-select form-select-sm ms-2" id="sort_order" name="sort_order" style="width: auto;" onchange="updateSort()">
                                    <option value="asc" {{ request('sort_order', 'asc') == 'asc' ? 'selected' : '' }}>{{ localize('global.ascending') }}</option>
                                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>{{ localize('global.descending') }}</option>
                                </select>
                            </div>
                        </div>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{localize('global.medicine_name')}}</th>
                                    <th>{{localize('global.medicine_type')}}</th>
                                    <th>{{localize('global.current_stock')}}</th>
                                    <th>{{localize('global.available_stock')}}</th>
                                    <th>{{localize('global.reserved_stock')}}</th>
                                    <th>{{localize('global.minimum_stock')}}</th>
                                    <th>{{localize('global.maximum_stock')}}</th>
                                    <th>{{localize('global.earliest_expiry')}}</th>
                                    <th>{{localize('global.stock_status')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($prescriptionStocks as $stock)
                                    <tr>
                                        <td>{{ $stock->medicine_name }}</td>
                                        <td>{{ $stock->medicine_type_name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $stock->current_stock > 0 ? 'primary' : 'danger' }}">
                                                {{ $stock->current_stock }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $stock->available_stock > 0 ? 'success' : 'warning' }}">
                                                {{ $stock->available_stock }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $stock->reserved_stock }}</span>
                                        </td>
                                        <td>{{ $stock->minimum_stock }}</td>
                                        <td>{{ $stock->maximum_stock }}</td>
                                        <td>
                                            @if($stock->earliest_expiry)
                                                <span class="badge bg-{{ $stock->earliest_expiry->diffInDays(now()) <= 30 ? 'warning' : 'success' }}">
                                                    {{ $stock->earliest_expiry->format('Y-m-d') }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $status = $stock->getComprehensiveStockStatus();
                                                $statusColors = [
                                                    'out_of_stock' => 'danger',
                                                    'has_expired_stock' => 'danger',
                                                    'expiring_soon' => 'warning',
                                                    'low_stock' => 'warning',
                                                    'overstocked' => 'info',
                                                    'normal' => 'success'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }}">
                                                {{ localize('global.' . $status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">{{ localize('global.no_stock_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        <!-- Enhanced Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                {{ localize('global.page') }} {{ $prescriptionStocks->currentPage() }} 
                                {{ localize('global.of') }} {{ $prescriptionStocks->lastPage() }}
                            </div>
                            <div>
                                {{ $prescriptionStocks->appends(request()->query())->links('pagination::bootstrap-5') }}
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
