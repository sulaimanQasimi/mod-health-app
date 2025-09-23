@extends('layouts.master')

@section('title', localize('global.vital_sign_type') . ' - ' . localize('global.details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-heartbeat"></i> {{ localize('global.vital_sign_type') }} - {{ localize('global.details') }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vital-sign-types.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ localize('global.back_to_list') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">{{ localize('global.id') }}:</th>
                                    <td>{{ $vitalSignType->id }}</td>
                                </tr>
                                <tr>
                                    <th>{{ localize('global.name') }}:</th>
                                    <td>{{ $vitalSignType->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ localize('global.created_at') }}:</th>
                                    <td>{{ verta($vitalSignType->created_at)->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ localize('global.updated_at') }}:</th>
                                    <td>{{ verta($vitalSignType->updated_at)->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @if($vitalSignType->createdBy)
                                <tr>
                                    <th>{{ localize('global.created_by') }}:</th>
                                    <td>{{ $vitalSignType->createdBy->name }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Associated Vital Signs -->
                    @if($vitalSignType->vitalSigns->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>{{ localize('global.associated') }} {{ localize('global.vital_signs') }} ({{ $vitalSignType->vitalSigns->count() }})</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.id') }}</th>
                                            <th>{{ localize('global.related_record') }}</th>
                                            <th>{{ localize('global.created_at') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($vitalSignType->vitalSigns as $vitalSign)
                                            <tr>
                                                <td>{{ $vitalSign->id }}</td>
                                                <td>
                                                    @if($vitalSign->morphable)
                                                        {{ class_basename($vitalSign->morphable_type) }} #{{ $vitalSign->morphable_id }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>{{ verta($vitalSign->created_at)->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    <a href="{{ route('vital-signs.show', $vitalSign) }}" class="btn btn-info btn-sm" title="{{ localize('view') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            @can('update', $vitalSignType)
                                <a href="{{ route('vital-sign-types.edit', $vitalSignType) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> {{ localize('global.edit') }}
                                </a>
                            @endcan
                            @can('delete', $vitalSignType)
                                <form action="{{ route('vital-sign-types.destroy', $vitalSignType) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ localize('global.confirm_delete') }} {{ localize('global.vital_sign_type') }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> {{ localize('global.delete') }}
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
