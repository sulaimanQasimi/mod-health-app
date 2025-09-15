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
                            <input type="date" class="form-control" id="order_date_filter" placeholder="{{ localize('global.order_date') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="search_filter" placeholder="{{ localize('global.mar_search') }}">
                        </div>
                    </div>

                    <!-- MAR Records Table -->
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
                                            {{ $mar->date_signature ? $mar->date_signature->format('Y-m-d') : 'N/A' }}
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Filter functionality
    $('#medicine_filter, #nurse_filter, #order_date_filter, #search_filter').on('change keyup', function() {
        // Implement filtering logic here
        // This would typically make an AJAX request to filter the results
        console.log('Filter changed');
    });
});
</script>
@endpush
