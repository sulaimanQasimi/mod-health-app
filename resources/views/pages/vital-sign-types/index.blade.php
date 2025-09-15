@extends('layouts.master')

@section('title', localize('vital_sign_types'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-heartbeat"></i> {{ localize('vital_sign_types') }}
                    </h3>
                    <div class="card-tools">
                        @can('create', App\Models\VitalSignType::class)
                            <a href="{{ route('vital-sign-types.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> {{ localize('create_vital_sign_type') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search Filter -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="search_filter" placeholder="{{ localize('search') }} {{ localize('vital_sign_types') }}...">
                        </div>
                    </div>

                    <!-- Vital Sign Types Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="vitalSignTypesTable">
                            <thead>
                                <tr>
                                    <th>{{ localize('id') }}</th>
                                    <th>{{ localize('name') }}</th>
                                    <th>{{ localize('created_at') }}</th>
                                    <th>{{ localize('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vitalSignTypes as $vitalSignType)
                                    <tr>
                                        <td>{{ $vitalSignType->id }}</td>
                                        <td>{{ $vitalSignType->name }}</td>
                                        <td>{{ $vitalSignType->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view', $vitalSignType)
                                                    <a href="{{ route('vital-sign-types.show', $vitalSignType) }}" class="btn btn-info btn-sm" title="{{ localize('view') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan
                                                @can('update', $vitalSignType)
                                                    <a href="{{ route('vital-sign-types.edit', $vitalSignType) }}" class="btn btn-warning btn-sm" title="{{ localize('edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $vitalSignType)
                                                    <form action="{{ route('vital-sign-types.destroy', $vitalSignType) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ localize('confirm_delete') }} {{ localize('vital_sign_type') }}?')">
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
                                        <td colspan="4" class="text-center">{{ localize('no_vital_signs_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $vitalSignTypes->links() }}
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
        $('#vitalSignTypesTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
@endpush
