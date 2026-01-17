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
                            <h5 class="mb-0">{{ localize('global.pharmacy_fulfillments') }}</h5>
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
                            <a class="btn btn-secondary create-new btn-primary" href="{{ route('pharmacy_fulfillments.create') }}"
                                type="button">
                                <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                        class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                            </a>
                        </div>
                    </div>

                    <!-- Advanced Search Form -->
                    <div class="card-body border-bottom">
                        <form method="GET" action="{{ route('pharmacy_fulfillments.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">{{ localize('global.search') }}</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="{{ localize('global.search_by_medicine_form_no') }}">
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
                                    <input autocomplete="off" type="text" name="date_from" placeholder="{{ localize('global.from') }}"
                                           class="form-control datepicker_dari" value="{{ request('date_from') }}" />
                                    <span class="input-group-text">...</span>
                                    <input autocomplete="off" type="text" name="date_to" placeholder="{{ localize('global.to') }}"
                                           class="form-control datepicker_dari" value="{{ request('date_to') }}" />
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
                                <a href="{{ route('pharmacy_fulfillments.index') }}" class="btn btn-secondary">
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
                                    {{ localize('global.showing') }} {{ $fulfillments->firstItem() ?? 0 }} 
                                    {{ localize('global.to') }} {{ $fulfillments->lastItem() ?? 0 }} 
                                    {{ localize('global.of') }} {{ $fulfillments->total() }} 
                                    {{ localize('global.results') }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                <label for="sort_by" class="form-label me-2 mb-0">{{ localize('global.sort_by') }}:</label>
                                <select class="form-select form-select-sm" id="sort_by" name="sort_by" style="width: auto;" onchange="updateSort()">
                                    <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>{{ localize('global.created_at') }}</option>
                                    <option value="date" {{ request('sort_by') == 'date' ? 'selected' : '' }}>{{ localize('global.date') }}</option>
                                    <option value="form_no" {{ request('sort_by') == 'form_no' ? 'selected' : '' }}>{{ localize('global.form_no') }}</option>
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
                                    <th>{{localize('global.unit_type')}}</th>
                                    <th>{{localize('global.amount')}}</th>
                                    <th>{{localize('global.form_no')}}</th>
                                    <th>{{localize('global.date')}}</th>
                                    <th>{{localize('global.pharmacy')}}</th>
                                    <th>{{localize('global.user')}}</th>
                                    <th>{{localize('global.form')}}</th>
                                    <th>{{localize('global.created_by')}}</th>
                                    <th>{{localize('global.created_at')}}</th>
                                    <th>{{localize('global.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($fulfillments as $fulfillment)
                                    <tr>
                                        <td>{{ $fulfillment->medicine->name ?? 'N/A' }}</td>
                                        <td>{{ $fulfillment->unit_type ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $fulfillment->amount }}</span>
                                        </td>
                                        <td>{{ $fulfillment->form_no }}</td>
                                        <td>
                                            @if($fulfillment->date)
                                                {{ \Verta::instance($fulfillment->date)->formatJalaliDate() }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($fulfillment->pharmacy)
                                                <span class="badge bg-secondary">{{ $fulfillment->pharmacy->name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $fulfillment->user->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($fulfillment->form)
                                                <a href="{{ Storage::disk('public')->url($fulfillment->form) }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="bx bx-file"></i> {{ localize('global.view_pdf') }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $fulfillment->createdBy->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($fulfillment->created_at)
                                                {{ \Verta::instance($fulfillment->created_at)->formatJalaliDate() }} {{ \Verta::instance($fulfillment->created_at)->format('H:i') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('pharmacy_fulfillments.show', $fulfillment->id) }}" class="btn btn-sm btn-info">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                <a href="{{ route('pharmacy_fulfillments.edit', $fulfillment->id) }}" class="btn btn-sm btn-warning">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <form action="{{ route('pharmacy_fulfillments.destroy', $fulfillment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ localize('global.are_you_sure') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">{{ localize('global.no_pharmacy_fulfillments_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        <!-- Enhanced Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                {{ localize('global.page') }} {{ $fulfillments->currentPage() }} 
                                {{ localize('global.of') }} {{ $fulfillments->lastPage() }}
                            </div>
                            <div>
                                {{ $fulfillments->appends(request()->query())->links('pagination::bootstrap-5') }}
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

@push('custom-js')
    <script src="{{ asset('ShamsiCalender/js/persianDatepicker.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize Persian date picker for date inputs
            $('.datepicker_dari').each(function() {
                var $this = $(this);
                
                // Clear any existing value that might cause issues
                $this.val('');
                
                $this.persianDatepicker({
                    formatDate: 'YYYY-MM-DD',
                    calendar: {
                        persian: {
                            locale: 'en',
                            showHint: true,
                            leapYearMode: 'algorithmic'
                        }
                    },
                    checkDate: function(unix) {
                        return true;
                    }
                });
            });
        });
    </script>
@endpush
