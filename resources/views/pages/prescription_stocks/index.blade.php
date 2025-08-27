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
                        <div class="pt-3 pt-md-0 text-end">
                            @can('create-prescription-stocks')
                            <a class="btn btn-secondary create-new btn-primary" href="{{ route('incomes.create') }}"
                                type="button">
                                <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                        class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                            </a>
                            @endcan
                        </div>
                    </div>

                    <!-- Advanced Search Form -->
                    <div class="card-body border-bottom">
                        <form method="GET" action="{{ route('prescription_stocks.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">{{ localize('global.search') }}</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="{{ localize('global.search_by_medicine_pharmacy') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="pharmacy_id" class="form-label">{{ localize('global.pharmacy') }}</label>
                                <select class="form-select" id="pharmacy_id" name="pharmacy_id">
                                    <option value="">{{ localize('global.all_pharmacies') }}</option>
                                    @foreach($pharmacies as $pharmacy)
                                        <option value="{{ $pharmacy->id }}" {{ request('pharmacy_id') == $pharmacy->id ? 'selected' : '' }}>
                                            {{ $pharmacy->name }}
                                        </option>
                                    @endforeach
                                </select>
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
                                    <option value="total_low_stock" {{ request('stock_status') == 'total_low_stock' ? 'selected' : '' }}>
                                        {{ localize('global.total_low_stock') }}
                                    </option>
                                    <option value="total_out_of_stock" {{ request('stock_status') == 'total_out_of_stock' ? 'selected' : '' }}>
                                        {{ localize('global.total_out_of_stock') }}
                                    </option>
                                    <option value="total_overstocked" {{ request('stock_status') == 'total_overstocked' ? 'selected' : '' }}>
                                        {{ localize('global.total_overstocked') }}
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
                        </div>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{localize('global.medicine_name')}}</th>
                                    <th>{{localize('global.pharmacy_name')}}</th>
                                    <th>{{localize('global.pharmacy_stock')}}</th>
                                    <th>{{localize('global.total_stock')}}</th>
                                    <th>{{localize('global.pharmacy_income')}}</th>
                                    <th>{{localize('global.pharmacy_outcome')}}</th>
                                    <th>{{localize('global.minimum_stock')}}</th>
                                    <th>{{localize('global.maximum_stock')}}</th>
                                    <th>{{localize('global.stock_status')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($prescriptionStocks as $stock)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-pill me-2 text-primary"></i>
                                                {{ $stock->medicine_name }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($stock->pharmacy_id)
                                                <span class="badge bg-info">{{ $stock->pharmacy_name }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ localize('global.general_stock') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $stock->pharmacy_stock > 0 ? 'primary' : 'danger' }}">
                                                {{ $stock->pharmacy_stock }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $stock->total_stock > 0 ? 'success' : 'warning' }}">
                                                {{ $stock->total_stock }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ $stock->pharmacy_income }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">{{ $stock->pharmacy_outcome }}</span>
                                        </td>
                                        <td>{{ $stock->minimum_stock }}</td>
                                        <td>{{ $stock->maximum_stock }}</td>
                                        <td>
                                            @php
                                                $status = $stock->pharmacy_stock > 0 ? 'normal' : 'out_of_stock';
                                                if ($stock->pharmacy_stock <= $stock->minimum_stock && $stock->pharmacy_stock > 0) {
                                                    $status = 'low_stock';
                                                } elseif ($stock->pharmacy_stock >= $stock->maximum_stock) {
                                                    $status = 'overstocked';
                                                }
                                                $statusColors = [
                                                    'out_of_stock' => 'danger',
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
