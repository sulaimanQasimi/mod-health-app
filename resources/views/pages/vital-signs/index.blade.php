@extends('layouts.master')

@section('title', localize('vital_signs'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-heartbeat"></i> {{ localize('global.vital_signs') }}
                    </h3>
                    <div class="card-tools">
                        @can('create', App\Models\VitalSign::class)
                            <a href="{{ route('vital-signs.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> {{ localize('global.add_vital_sign') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control" id="vital_sign_type_filter">
                                <option value="">{{ localize('global.filter_by_vital_sign_type') }}</option>
                                @foreach($vitalSignTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control datepicker_dari pdp-el" id="date_from_filter" placeholder="{{ localize('global.date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control datepicker_dari pdp-el" id="date_to_filter" placeholder="{{ localize('global.date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="search_filter" placeholder="{{ localize('global.search') }}...">
                        </div>
                    </div>

                    <!-- Vital Signs Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="vitalSignsTable">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.id') }}</th>
                                    <th>{{ localize('global.type') }}</th>
                                    <th>{{ localize('global.related_record') }}</th>
                                    <th>{{ localize('global.schedules') }}</th>
                                    <th>{{ localize('global.created_at') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vitalSigns as $vitalSign)
                                    <tr>
                                        <td>{{ $vitalSign->id }}</td>
                                        <td>{{ $vitalSign->vitalSignType->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($vitalSign->morphable)
                                                {{ class_basename($vitalSign->morphable_type) }} #{{ $vitalSign->morphable_id }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $vitalSign->schedules->count() }} {{ localize('global.schedules') }}</span>
                                        </td>
                                        <td>{{ $vitalSign->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view', $vitalSign)
                                                    <a href="{{ route('vital-signs.show', $vitalSign) }}" class="btn btn-info btn-sm" title="{{ localize('global.view') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan
                                                @can('update', $vitalSign)
                                                    <a href="{{ route('vital-signs.edit', $vitalSign) }}" class="btn btn-warning btn-sm" title="{{ localize('global.edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $vitalSign)
                                                    <form action="{{ route('vital-signs.destroy', $vitalSign) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ localize('global.confirm_delete') }} {{ localize('global.vital_sign') }}?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" title="{{ localize('global.delete') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ localize('global.no_vital_signs_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $vitalSigns->links() }}
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
    // Search functionality
    $('#search_filter').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#vitalSignsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
@endpush
