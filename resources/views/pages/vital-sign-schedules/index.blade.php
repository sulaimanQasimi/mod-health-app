@extends('layouts.master')

@section('title', localize('vital_sign_schedules'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i> {{ localize('vital_sign_schedules') }}
                    </h3>
                    <div class="card-tools">
                        @can('create', App\Models\VitalSignSchedule::class)
                            <a href="{{ route('vital-sign-schedules.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> {{ localize('add_schedule') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control" id="vital_sign_filter">
                                <option value="">{{ localize('filter_by_vital_sign') }}</option>
                                @foreach($vitalSigns as $vitalSign)
                                    <option value="{{ $vitalSign->id }}">
                                        {{ $vitalSign->vitalSignType->name ?? 'N/A' }} - {{ class_basename($vitalSign->morphable_type) }} #{{ $vitalSign->morphable_id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="nurse_filter">
                                <option value="">{{ localize('filter_by_nurse') }}</option>
                                @foreach($nurses as $nurse)
                                    <option value="{{ $nurse->id }}">{{ $nurse->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="date_from_filter" placeholder="{{ localize('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="date_to_filter" placeholder="{{ localize('date_to') }}">
                        </div>
                    </div>

                    <!-- Schedules Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="schedulesTable">
                            <thead>
                                <tr>
                                    <th>{{ localize('id') }}</th>
                                    <th>{{ localize('vital_sign') }}</th>
                                    <th>{{ localize('day') }}</th>
                                    <th>{{ localize('date') }}</th>
                                    <th>{{ localize('morning_time') }}</th>
                                    <th>{{ localize('evening_time') }}</th>
                                    <th>{{ localize('nurse') }}</th>
                                    <th>{{ localize('created_at') }}</th>
                                    <th>{{ localize('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                    <tr>
                                        <td>{{ $schedule->id }}</td>
                                        <td>
                                            {{ $schedule->vitalSign->vitalSignType->name ?? 'N/A' }}
                                            <br>
                                            <small class="text-muted">
                                                {{ class_basename($schedule->vitalSign->morphable_type) }} #{{ $schedule->vitalSign->morphable_id }}
                                            </small>
                                        </td>
                                        <td>{{ $schedule->day ?? 'N/A' }}</td>
                                        <td>{{ $schedule->date ? $schedule->date->format('Y-m-d') : 'N/A' }}</td>
                                        <td>{{ $schedule->morning_time ?? 'N/A' }}</td>
                                        <td>{{ $schedule->evening_time ?? 'N/A' }}</td>
                                        <td>{{ $schedule->nurse->full_name ?? 'N/A' }}</td>
                                        <td>{{ $schedule->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view', $schedule)
                                                    <a href="{{ route('vital-sign-schedules.show', $schedule) }}" class="btn btn-info btn-sm" title="{{ localize('view') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan
                                                @can('update', $schedule)
                                                    <a href="{{ route('vital-sign-schedules.edit', $schedule) }}" class="btn btn-warning btn-sm" title="{{ localize('edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $schedule)
                                                    <form action="{{ route('vital-sign-schedules.destroy', $schedule) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ localize('confirm_delete') }} {{ localize('vital_sign_schedule') }}?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" title="{{ localize('delete') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">{{ localize('no_schedules_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $schedules->links() }}
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
    // Filter functionality
    $('#vital_sign_filter, #nurse_filter, #date_from_filter, #date_to_filter').on('change', function() {
        // Implement filtering logic here
        console.log('Filter changed');
    });
});
</script>
@endpush
