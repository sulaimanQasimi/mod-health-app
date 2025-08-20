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
                        <h5 class="mb-0">{{ localize('global.income_records') }}</h5>
                        <div class="pt-3 pt-md-0 text-end">
                            {{-- @can('create-incomes') --}}
                            <a class="btn btn-secondary create-new btn-primary" href="{{ route('incomes.create') }}"
                                type="button">
                                <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                        class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                            </a>
                            {{-- @endcan --}}
                        </div>
                    </div>

                    <!-- Advanced Search Form -->
                    <div class="card-body border-bottom">
                        <form method="GET" action="{{ route('incomes.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">{{ localize('global.search') }}</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="{{ localize('global.search_by_medicine_supplier') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="income_type" class="form-label">{{ localize('global.income_type') }}</label>
                                <select class="form-select" id="income_type" name="income_type">
                                    <option value="">{{ localize('global.all_types') }}</option>
                                    <option value="purchase" {{ request('income_type') == 'purchase' ? 'selected' : '' }}>
                                        {{ localize('global.purchase') }}
                                    </option>
                                    <option value="return" {{ request('income_type') == 'return' ? 'selected' : '' }}>
                                        {{ localize('global.return') }}
                                    </option>
                                    <option value="donation" {{ request('income_type') == 'donation' ? 'selected' : '' }}>
                                        {{ localize('global.donation') }}
                                    </option>
                                    <option value="transfer" {{ request('income_type') == 'transfer' ? 'selected' : '' }}>
                                        {{ localize('global.transfer') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">{{ localize('global.date_from') }}</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">{{ localize('global.date_to') }}</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="{{ request('date_to') }}">
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
                                <a href="{{ route('incomes.index') }}" class="btn btn-secondary">
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
                                    {{ localize('global.showing') }} {{ $incomes->firstItem() ?? 0 }} 
                                    {{ localize('global.to') }} {{ $incomes->lastItem() ?? 0 }} 
                                    {{ localize('global.of') }} {{ $incomes->total() }} 
                                    {{ localize('global.results') }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                <label for="sort_by" class="form-label me-2 mb-0">{{ localize('global.sort_by') }}:</label>
                                <select class="form-select form-select-sm" id="sort_by" name="sort_by" style="width: auto;" onchange="updateSort()">
                                    <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>{{ localize('global.created_at') }}</option>
                                    <option value="purchase_date" {{ request('sort_by') == 'purchase_date' ? 'selected' : '' }}>{{ localize('global.purchase_date') }}</option>
                                    <option value="amount" {{ request('sort_by') == 'amount' ? 'selected' : '' }}>{{ localize('global.amount') }}</option>
                                    <option value="purchase_price" {{ request('sort_by') == 'purchase_price' ? 'selected' : '' }}>{{ localize('global.purchase_price') }}</option>
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
                                    <th>{{localize('global.expiry_date')}}</th>
                                    <th>{{localize('global.supplier_name')}}</th>
                                    <th>{{localize('global.purchase_price')}}</th>
                                    <th>{{localize('global.purchase_date')}}</th>
                                    <th>{{localize('global.income_type')}}</th>
                                    <th>{{localize('global.created_by')}}</th>
                                    <th>{{localize('global.created_at')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($incomes as $income)
                                    <tr>
                                        <td>{{ $income->medicine->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $income->amount }}</span>
                                        </td>
                                        <td>{{ $income->batch_number }}</td>
                                        <td>
                                            @if($income->expiry_date)
                                                <span class="badge bg-{{ $income->expiry_date->diffInDays(now()) <= 30 ? 'warning' : 'success' }}">
                                                    {{ $income->expiry_date->format('Y-m-d') }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $income->supplier_name }}</td>
                                        <td>{{ number_format($income->purchase_price, 2) }}</td>
                                        <td>
                                            @if($income->purchase_date)
                                                {{ $income->purchase_date->format('Y-m-d') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'purchase' => 'primary',
                                                    'return' => 'success',
                                                    'donation' => 'info',
                                                    'transfer' => 'warning'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $typeColors[$income->income_type] ?? 'secondary' }}">
                                                {{ localize('global.' . $income->income_type) }}
                                            </span>
                                        </td>
                                        <td>{{ $income->createdBy->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($income->created_at)
                                                {{ $income->created_at->format('Y-m-d H:i') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">{{ localize('global.no_income_records_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        <!-- Enhanced Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                {{ localize('global.page') }} {{ $incomes->currentPage() }} 
                                {{ localize('global.of') }} {{ $incomes->lastPage() }}
                            </div>
                            <div>
                                {{ $incomes->appends(request()->query())->links('pagination::bootstrap-5') }}
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
