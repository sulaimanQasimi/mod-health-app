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
                            @if ($userPharmacies && $userPharmacies->isNotEmpty())
                                <small class="text-muted">
                                    <i class="bx bx-building me-1"></i>
                                    {{ localize('global.pharmacies') }}:
                                    @foreach ($userPharmacies as $pharmacy)
                                        <strong>{{ $pharmacy->name }}</strong>@if (!$loop->last), @endif
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

                    <div class="card-body border-bottom py-2">
                        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#stockFiltersCollapse" aria-expanded="false" aria-controls="stockFiltersCollapse">
                            <i class="bx bx-filter-alt"></i>
                            <span>{{ localize('global.filters') }}</span>
                            <i class="bx bx-chevron-down filter-toggle-icon rotated ms-1" style="transition: transform 0.2s;"></i>
                        </button>
                        <div class="collapse mt-3" id="stockFiltersCollapse">
                            <form id="stockFiltersForm" method="GET" action="{{ route('pharmacy_fulfillments.stock') }}" class="row g-3">
                                <div class="col-md-3">
                                    <label for="search" class="form-label">{{ localize('global.search') }}</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                        value="{{ request('search') }}" placeholder="{{ localize('global.search_by_medicine_pharmacy') }}">
                                </div>

                                <div class="col-md-3">
                                    <label for="medicine_id" class="form-label">{{ localize('global.medicine') }}</label>
                                    <select class="form-select select2 filter-auto-submit" id="medicine_id" name="medicine_id" data-placeholder="{{ localize('global.all_medicines') }}">
                                        <option value="">{{ localize('global.all_medicines') }}</option>
                                        @foreach ($medicines as $medicine)
                                            <option value="{{ $medicine->id }}" {{ (string) request('medicine_id') === (string) $medicine->id ? 'selected' : '' }}>
                                                {{ $medicine->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                @if ($pharmacies && $pharmacies->isNotEmpty())
                                    <div class="col-md-3">
                                        <label for="pharmacy_id" class="form-label">{{ localize('global.pharmacy') }}</label>
                                        <select class="form-select filter-auto-submit" id="pharmacy_id" name="pharmacy_id">
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
                                    <label for="stock_status" class="form-label">{{ localize('global.stock_status') }}</label>
                                    <select class="form-select filter-auto-submit" id="stock_status" name="stock_status">
                                        <option value="">{{ localize('global.all_statuses') }}</option>
                                        <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>
                                            {{ localize('global.out_of_stock') }}
                                        </option>
                                        <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>
                                            {{ localize('global.low_stock') }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="sort_by" class="form-label">{{ localize('global.sort_by') }}</label>
                                    <select class="form-select filter-auto-submit" id="sort_by" name="sort_by">
                                        <option value="medicine" {{ request('sort_by', 'medicine') == 'medicine' ? 'selected' : '' }}>{{ localize('global.medicine') }}</option>
                                        <option value="pharmacy" {{ request('sort_by') == 'pharmacy' ? 'selected' : '' }}>{{ localize('global.pharmacy') }}</option>
                                        <option value="income" {{ request('sort_by') == 'income' ? 'selected' : '' }}>{{ localize('global.stock_income') }}</option>
                                        <option value="outcome" {{ request('sort_by') == 'outcome' ? 'selected' : '' }}>{{ localize('global.pharmacy_outcome') }}</option>
                                        <option value="stock" {{ request('sort_by') == 'stock' ? 'selected' : '' }}>{{ localize('global.stock') }}</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="sort_order" class="form-label">{{ localize('global.sort_order') }}</label>
                                    <select class="form-select filter-auto-submit" id="sort_order" name="sort_order">
                                        <option value="asc" {{ request('sort_order', 'asc') == 'asc' ? 'selected' : '' }}>{{ localize('global.ascending') }}</option>
                                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>{{ localize('global.descending') }}</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="per_page" class="form-label">{{ localize('global.per_page') }}</label>
                                    <select class="form-select filter-auto-submit" id="per_page" name="per_page">
                                        @foreach ([10, 15, 25, 50, 100] as $perPage)
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

                        <div class="table-responsive">
                            <table class="table stock-table align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.medicine') }}</th>
                                        <th>{{ localize('global.pharmacy') }}</th>
                                        <th class="text-end">{{ localize('global.stock_income') }}</th>
                                        <th class="text-end">{{ localize('global.pharmacy_outcome') }}</th>
                                        <th class="text-end">{{ localize('global.stock') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($stockItems as $row)
                                        @php
                                            $stock = (int) $row->stock;
                                            $income = (int) $row->income;
                                            $outcome = (int) $row->outcome;
                                            $rowClass = '';
                                            if ($stock <= 0) {
                                                $rowClass = 'stock-row-critical';
                                            } elseif ($stock <= 10) {
                                                $rowClass = 'stock-row-warning';
                                            }
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td>
                                                <div class="fw-semibold">{{ $row->medicine_name ?? 'N/A' }}</div>
                                            </td>
                                            <td>{{ $row->pharmacy_name ?? 'N/A' }}</td>
                                            <td class="text-end">
                                                <span class="badge bg-success">{{ number_format($income) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-warning">{{ number_format($outcome) }}</span>
                                            </td>
                                            <td class="text-end">
                                                @if ($stock < 0)
                                                    <span class="badge bg-danger">{{ number_format($stock) }}</span>
                                                @elseif($stock == 0)
                                                    <span class="badge bg-secondary">{{ number_format($stock) }}</span>
                                                @elseif($stock <= 10)
                                                    <span class="badge bg-warning">{{ number_format($stock) }}</span>
                                                @else
                                                    <span class="badge bg-primary">{{ number_format($stock) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">{{ localize('global.no_pharmacy_stock_found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

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

@push('custom-css')
    <style>
        .filter-toggle-icon.rotated {
            transform: rotate(-90deg);
        }

        .stock-table thead th {
            white-space: nowrap;
        }

        .stock-row-warning {
            background-color: rgba(255, 171, 0, 0.08);
        }

        .stock-row-critical {
            background-color: rgba(255, 62, 29, 0.08);
        }
    </style>
@endpush

@push('custom-js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('stockFiltersForm');
            document.querySelectorAll('.filter-auto-submit').forEach(function(el) {
                el.addEventListener('change', function() {
                    form.submit();
                });
            });

            var filtersCollapse = document.getElementById('stockFiltersCollapse');
            var toggleIcon = document.querySelector('.filter-toggle-icon');
            if (filtersCollapse && toggleIcon) {
                filtersCollapse.addEventListener('show.bs.collapse', function() {
                    toggleIcon.classList.remove('rotated');
                });
                filtersCollapse.addEventListener('hide.bs.collapse', function() {
                    toggleIcon.classList.add('rotated');
                });
                if (!filtersCollapse.classList.contains('show')) {
                    toggleIcon.classList.add('rotated');
                }
            }
        });
    </script>
@endpush
