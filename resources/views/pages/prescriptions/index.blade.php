@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <!-- Filters Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.filters') }}</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('prescriptions.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">{{ localize('global.search') }}</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search" name="search" 
                                    value="{{ request('search') }}" placeholder="{{ localize('global.search_by_patient_name') }}">
                                @if(request('search'))
                                    <button type="button" class="btn btn-outline-danger" id="clearSearch" title="{{ localize('global.clear_search') }}">
                                        <i class="bx bx-x"></i>
                                    </button>
                                @endif
                                <button type="submit" class="btn btn-outline-primary" title="{{ localize('global.search') }}">
                                    <i class="bx bx-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">{{ localize('global.status') }}</label>
                            <select class="form-select select2" id="status" name="status">
                                <option value="">{{ localize('global.all') }}</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ localize('global.not_delivered') }}</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ localize('global.delivered') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">{{ localize('global.date_from') }}</label>
                            <input type="text" class="form-control datepicker_dari pdp-el" id="date_from" name="date_from" 
                                value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">{{ localize('global.date_to') }}</label>
                            <input type="text" class="form-control datepicker_dari pdp-el" id="date_to" name="date_to" 
                                value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <a href="{{ route('prescriptions.index') }}" class="btn btn-secondary">
                                <i class="bx bx-refresh"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.new_prescriptions') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.card_number') }}</th>
                                        <th>{{ localize('global.patient_name') }}</th>
                                        <th>{{ localize('global.father_name') }}</th>
                                        <th>{{ localize('global.referred_to') }}</th>
                                        <th>{{ localize('global.created_at') }}</th>
                                        <th>{{ localize('global.status') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($prescriptions as $prescription)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $prescription->patient->id_card ?? '-' }}</span>
                                            </td>
                                            <td>{{ $prescription->patient->name ?? '-' }}</td>
                                            <td>
                                                <span class="text-muted">{{ $prescription->patient->father_name ?? '-' }}</span>
                                            </td>
                                            <td>{{ $prescription->doctor->name ?? '-' }}</td>
                                            <td>{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($prescription->created_at) }}</td>
                                            <td>
                                                @if($prescription->is_completed == 0)
                                                    <span class="badge bg-warning">{{ localize('global.not_delivered') }}</span>
                                                @else
                                                    <span class="badge bg-success">{{ localize('global.delivered') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('prescriptions.show', $prescription) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="{{ localize('global.view') }}">
                                                    <i class="bx bx-show-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="alert alert-info">
                                                    <i class="bx bx-info-circle me-2"></i>
                                                    {{ localize('global.no_prescriptions_found') }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($prescriptions->count() > 0)
                            <div class="col-md-12 mt-4 mb-4">
                                {{ $prescriptions->links('pagination::bootstrap-4') }}
                            </div>
                        @endif
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('custom-js')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        placeholder: '{{ localize("global.select_status") }}',
        allowClear: true,
        width: '100%'
    });
    
    // Initialize Persian datepicker
    $('.datepicker_dari').persianDatepicker({
        format: 'YYYY/MM/DD',
        altField: '.observer-example',
        altFormat: 'YYYY/MM/DD',
        observer: true,
        formatDate: function(unixDate) {
            var d = new Date(unixDate);
            return d.getFullYear() + '/' + (d.getMonth() + 1) + '/' + d.getDate();
        }
    });
    
    // Auto-submit form when select values change
    $('select[name="status"]').change(function() {
        $(this).closest('form').submit();
    });
    
    // Clear all filters on refresh button click
    $('.btn-secondary').click(function(e) {
        e.preventDefault();
        $('input[name="search"]').val('');
        $('select[name="status"]').val('').trigger('change');
        $('input[name="date_from"]').val('');
        $('input[name="date_to"]').val('');
        // Clear datepicker values
        $('.datepicker_dari').persianDatepicker('clear');
        // Redirect to clean URL
        window.location.href = '{{ route("prescriptions.index") }}';
    });
    
    // Add loading state to search button
    $('form').submit(function(e) {
        $('.btn-primary').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i>');
        $('.btn-secondary').prop('disabled', true);
    });
    
    // Validate date range for Persian dates
    $('input[name="date_from"], input[name="date_to"]').on('changeDate', function() {
        var dateFrom = $('input[name="date_from"]').val();
        var dateTo = $('input[name="date_to"]').val();
        
        if (dateFrom && dateTo) {
            // Convert Persian dates to comparable format
            var fromParts = dateFrom.split('/');
            var toParts = dateTo.split('/');
            
            if (fromParts.length === 3 && toParts.length === 3) {
                var fromDate = new Date(parseInt(fromParts[0]), parseInt(fromParts[1]) - 1, parseInt(fromParts[2]));
                var toDate = new Date(parseInt(toParts[0]), parseInt(toParts[1]) - 1, parseInt(toParts[2]));
                
                if (fromDate > toDate) {
                    alert('{{ localize("global.date_from_cannot_be_greater_than_date_to") }}');
                    $(this).val('');
                }
            }
        }
    });
    
    // Search functionality - submit on enter or manual search
    $('input[name="search"]').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            $(this).closest('form').submit();
        }
    });
    
    // Remove auto-submit on input change to prevent unwanted refreshes
    // Search will only submit on Enter key or button click
    
    // Clear search functionality
    $(document).on('click', '#clearSearch', function() {
        $('input[name="search"]').val('');
        $(this).closest('form').submit();
    });
    
    // Show active filters count
    function updateActiveFiltersCount() {
        var activeFilters = 0;
        if ($('input[name="search"]').val()) activeFilters++;
        if ($('select[name="status"]').val()) activeFilters++;
        if ($('input[name="date_from"]').val()) activeFilters++;
        if ($('input[name="date_to"]').val()) activeFilters++;
        
        if (activeFilters > 0) {
            $('.btn-secondary').html('<i class="bx bx-refresh"></i> (' + activeFilters + ')');
        } else {
            $('.btn-secondary').html('<i class="bx bx-refresh"></i>');
        }
    }
    
    // Update filter count on page load and changes
    updateActiveFiltersCount();
    $('input, select').on('change', updateActiveFiltersCount);
});
</script>
@endpush

