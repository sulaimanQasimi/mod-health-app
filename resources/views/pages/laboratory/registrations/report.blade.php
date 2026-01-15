@extends('layouts.master')
@section('title', localize('global.test_registration_report'))
@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <div class="card">
            <div class="accordion m-3" id="accordionWithIcon">
                <div class="card accordion-item active">
                    <h2 class="accordion-header d-flex align-items-center">
                        <button type="button" class="accordion-button" data-bs-toggle="collapse"
                            data-bs-target="#accordionWithIcon-1" aria-expanded="true">
                            <i class="bx bx-search"></i>
                            {{ localize('global.documents.search') }}
                        </button>
                    </h2>
                    <div id="accordionWithIcon-1" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <form method="GET" action="{{ route('laboratory.registrations.report') }}">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ localize('global.between_two_date') }}</label>
                                        <div class="input-group input-daterange">
                                            <input type="text" name="from" 
                                                value="{{ old('from', request('from')) }}"
                                                placeholder="{{ localize('global.from') }}"
                                                class="form-control datepicker_dari" />
                                            <span class="input-group-text">...</span>
                                            <input type="text" name="to" 
                                                value="{{ old('to', request('to')) }}"
                                                placeholder="{{ localize('global.to') }}"
                                                class="form-control datepicker_dari" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-label-primary">
                                            <i class="fa fa-search m-2"></i> <span>
                                                {{ localize('global.documents.search') }}</span>
                                        </button>
                                        <button type="button" class="btn btn-label-secondary" id="reset-form-btn">
                                            <i class="fa fa-history m-2"></i>
                                            <span>{{ localize('global.reset') }}</span>
                                        </button>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="per_page" class="form-label">{{ localize('global.per_page') }}</label>
                                        <select class="form-select" name="per_page" id="per_page">
                                            <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                            <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ request('per_page', 15) == 100 ? 'selected' : '' }}>100</option>
                                            <option value="all" {{ request('per_page', 15) == 'all' ? 'selected' : '' }}>{{ localize('global.all') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive m-1" id="app">
                @if(isset($items) && $items->count() > 0)
                    <form action="{{ route('laboratory.registrations.export-report') }}" method="POST" class="mb-3">
                        {{ csrf_field() }}
                        {{-- Include filter parameters as hidden fields --}}
                        <input type="hidden" name="from" value="{{ request('from', '') }}">
                        <input type="hidden" name="to" value="{{ request('to', '') }}">
                        <input type="hidden" name="per_page" value="{{ request('per_page', '') }}">

                        <div class="demo-inline-spacing m-3">
                            <button type="submit" name="type" value="excel" class="btn btn-label-primary" id="export-excel-btn">
                                <i class="fa fa-file-excel me-1"></i>Excel
                            </button>
                            <button type="submit" name="type" value="pdf" class="btn btn-label-danger" id="export-pdf-btn">
                                <i class="fa fa-file-pdf me-1"></i>PDF
                            </button>
                        </div>
                    </form>
                    <div class="col-md-12 mt-2">
                        <table class="table table-bordered table-striped table-responsive w-100" id="print_excel_table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.test_type') }}</th>
                                    <th>{{ localize('global.total') }}</th>
                                    <th>{{ localize('global.date') }}</th>
                                    <th>{{ localize('global.count') }}</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @php
                                    $rowNumber = 1;
                                    if (is_a($items, 'Illuminate\Pagination\LengthAwarePaginator')) {
                                        $rowNumber = $items->firstItem() ?? 1;
                                    }
                                @endphp
                                @foreach ($items as $testType)
                                    @php
                                        $dateCount = $testType['dates']->count();
                                        $dateIndex = 0;
                                    @endphp
                                    @foreach ($testType['dates'] as $dateItem)
                                        <tr>
                                            @if($dateIndex == 0)
                                                <td rowspan="{{ $dateCount }}" class="align-middle">
                                                    {{ $rowNumber }}
                                                </td>
                                                <td rowspan="{{ $dateCount }}" class="align-middle">
                                                    <strong>{{ $testType['lab_type_name'] }}</strong>
                                                </td>
                                                <td rowspan="{{ $dateCount }}" class="align-middle">
                                                    <strong>{{ $testType['total_count'] }}</strong>
                                                </td>
                                            @endif
                                            <td>
                                                @php
                                                    try {
                                                        $vertaDate = \Hekmatinasser\Verta\Facades\Verta::createFromFormat('Y-m-d', $dateItem['date']);
                                                        $persianDate = $vertaDate->format('Y/m/d');
                                                    } catch (\Exception $e) {
                                                        $persianDate = $dateItem['date'];
                                                    }
                                                @endphp
                                                {{ $persianDate }}
                                            </td>
                                            <td>{{ $dateItem['count'] }}</td>
                                        </tr>
                                        @php $dateIndex++; @endphp
                                    @endforeach
                                    @php $rowNumber++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                        
                        <!-- Pagination -->
                        @if(is_a($items, 'Illuminate\Pagination\LengthAwarePaginator') && $items->hasPages())
                            <div class="card-footer border-top py-3">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="text-muted small mb-2 mb-md-0">
                                        {{ localize('global.showing') }} {{ $items->firstItem() }} {{ localize('global.to') }} {{ $items->lastItem() }} 
                                        {{ localize('global.of') }} {{ $items->total() }} {{ localize('global.results') }}
                                    </div>
                                    <div>
                                        {{ $items->links() }}
                                    </div>
                                </div>
                            </div>
                        @elseif(is_a($items, 'Illuminate\Pagination\LengthAwarePaginator'))
                            <div class="card-footer border-top py-3">
                                <div class="text-muted small">
                                    {{ localize('global.showing') }} {{ $items->total() }} {{ localize('global.results') }}
                                </div>
                            </div>
                        @endif
                    </div>
                @elseif(isset($items) && $items->count() == 0)
                    <div class="alert alert-warning m-3">
                        {{ localize('global.no_item_is_found') }} - {{ localize('global.cannot_export_empty_report') ?? 'نمی‌توان گزارش خالی را صادر کرد' }}
                    </div>
                @endif
            </div>
        </div>
        <!--/ Basic Bootstrap Table -->
    </div>
    <!-- / Content -->
</div>
@endsection

@push('custom-js')
<script>
    // Auto-submit when per_page changes
    $('#per_page').on('change', function() {
        $('form').submit();
    });

    // Handle reset button click
    $('#reset-form-btn').on('click', function(e) {
        e.preventDefault();
        
        // Reset all input fields
        $('form input[type="text"]').val('');
        
        // Reset per_page to default
        $('#per_page').val('15');
        
        // Clear date pickers
        $('.datepicker_dari').val('');
        
        // Redirect to clean report URL (without query parameters)
        window.location.href = '{{ route("laboratory.registrations.report") }}';
    });
</script>
@endpush
@push('custom-css')
<style>
.sadira_date_range,
.wareda_date_range {
    display: none;
}
</style>
@endpush
