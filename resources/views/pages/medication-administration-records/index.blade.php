@extends('layouts.master')

@section('title', localize('global.medication_administration_records'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-pills"></i> {{ localize('global.medication_administration_records') }} ({{ localize('global.mar') }})
                    </h3>
                    <div class="card-tools">
                        @can('create', App\Models\MedicationAdministrationRecord::class)
                            <a href="{{ route('medication-administration-records.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> {{ localize('global.add_mar') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control" id="medicine_filter">
                                <option value="">{{ localize('global.mar_filter_by_medicine') }}</option>
                                @foreach($medicines as $medicine)
                                    <option value="{{ $medicine->id }}">{{ $medicine->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="nurse_filter">
                                <option value="">{{ localize('global.mar_filter_by_nurse') }}</option>
                                @foreach($nurses as $nurse)
                                    <option value="{{ $nurse->id }}">{{ $nurse->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control datepicker_dari pdp-el" id="order_date_filter" placeholder="{{ localize('global.order_date') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="search_filter" placeholder="{{ localize('global.mar_search') }}">
                        </div>
                    </div>
                    
                    <!-- Filter Actions -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" id="search_btn">
                                    <i class="bx bx-search"></i> {{ localize('global.search') }}
                                </button>
                                <button type="button" class="btn btn-secondary" id="clear_filters_btn">
                                    <i class="bx bx-x"></i> {{ localize('global.clear_filters') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- MAR Records Table -->
                    <div id="mar-records-container">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.mar_id') }}</th>
                                    <th>{{ localize('global.medicine') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.nurse') }}</th>
                                    <th>{{ localize('global.order_date') }}</th>
                                    <th>{{ localize('global.signature_date') }}</th>
                                    <th>{{ localize('global.administration_times') }}</th>
                                    <th>{{ localize('global.mar_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($medicationAdministrationRecords as $mar)
                                    <tr>
                                        <td>{{ $mar->id }}</td>
                                        <td>
                                            <strong>{{ $mar->medicine->name ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            @if($mar->patient)
                                                {{ $mar->patient->first_name }} {{ $mar->patient->last_name }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            {{ $mar->nurse->full_name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $mar->order_date ? $mar->order_date->format('Y-m-d') : 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $mar->date_signature ? verta($mar->date_signature)->format('Y-m-d') : 'N/A' }}
                                        </td>
                                        <td>
                                            @if($mar->administrationTimes->count() > 0)
                                                <span class="badge badge-info">
                                                    {{ $mar->administrationTimes->count() }} {{ localize('global.times_count') }}
                                                </span>
                                                <br>
                                                <small>
                                                    @foreach($mar->administrationTimes as $time)
                                                        {{ $time->formatted_time }}@if(!$loop->last), @endif
                                                    @endforeach
                                                </small>
                                            @else
                                                <span class="text-muted">{{ localize('global.no_times_recorded') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view', $mar)
                                                    <a href="{{ route('medication-administration-records.show', $mar) }}" 
                                                       class="btn btn-info btn-sm" title="{{ localize('global.mar_view') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan
                                                @can('update', $mar)
                                                    <a href="{{ route('medication-administration-records.edit', $mar) }}" 
                                                       class="btn btn-warning btn-sm" title="{{ localize('global.mar_edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $mar)
                                                    <form action="{{ route('medication-administration-records.destroy', $mar) }}" 
                                                          method="POST" style="display: inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                                title="{{ localize('global.mar_delete') }}" 
                                                                onclick="return confirm('{{ localize('global.mar_confirm_delete') }}')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> {{ localize('global.no_mars_found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>

                        <!-- Pagination -->
                        @if($medicationAdministrationRecords->hasPages())
                            <div class="d-flex justify-content-center">
                                {{ $medicationAdministrationRecords->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Search button functionality
    $('#search_btn').on('click', function() {
        applyFilters();
    });
    
    // Clear filters button functionality
    $('#clear_filters_btn').on('click', function() {
        clearFilters();
    });
    
    // Auto-filter on change for select elements
    $('#medicine_filter, #nurse_filter').on('change', function() {
        applyFilters();
    });
    
    // Auto-filter on keyup for text inputs
    $('#order_date_filter, #search_filter').on('keyup', function() {
        clearTimeout(window.filterTimeout);
        window.filterTimeout = setTimeout(function() {
            applyFilters();
        }, 500); // Wait 500ms after user stops typing
    });
    
    function applyFilters() {
        var medicineId = $('#medicine_filter').val();
        var nurseId = $('#nurse_filter').val();
        var orderDate = $('#order_date_filter').val();
        var searchTerm = $('#search_filter').val();
        
        // Show loading state
        $('#search_btn').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> {{ localize("global.searching") }}...');
        
        // Make AJAX request to filter results
        $.ajax({
            url: '{{ route("medication-administration-records.index") }}',
            method: 'GET',
            data: {
                medicine_id: medicineId,
                nurse_id: nurseId,
                order_date: orderDate,
                search: searchTerm
            },
            success: function(response) {
                // Replace the entire records container with filtered results
                var $newContent = $(response).find('#mar-records-container');
                $('#mar-records-container').html($newContent.html());
            },
            error: function(xhr) {
                console.error('Filter error:', xhr);
                alert('{{ localize("global.error_occurred") }}');
            },
            complete: function() {
                // Re-enable search button
                $('#search_btn').prop('disabled', false).html('<i class="bx bx-search"></i> {{ localize("global.search") }}');
            }
        });
    }
    
    function clearFilters() {
        $('#medicine_filter').val('');
        $('#nurse_filter').val('');
        $('#order_date_filter').val('');
        $('#search_filter').val('');
        
        // Reload the page to show all records
        window.location.reload();
    }
});
</script>
@endpush
