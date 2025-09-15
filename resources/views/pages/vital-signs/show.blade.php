@extends('layouts.master')

@section('title', localize('vital_sign') . ' - ' . localize('details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-heartbeat"></i> {{ localize('vital_sign') }} - {{ localize('details') }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vital-signs.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ localize('back_to_list') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">{{ localize('id') }}:</th>
                                    <td>{{ $vitalSign->id }}</td>
                                </tr>
                                <tr>
                                    <th>{{ localize('vital_sign_type') }}:</th>
                                    <td>
                                        <span class="badge bg-info">{{ $vitalSign->vitalSignType->name ?? 'N/A' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ localize('morphable_type') }}:</th>
                                    <td>
                                        <span class="badge bg-primary">{{ class_basename($vitalSign->morphable_type) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ localize('morphable_id') }}:</th>
                                    <td>{{ $vitalSign->morphable_id }}</td>
                                </tr>
                                <tr>
                                    <th>{{ localize('related_record') }}:</th>
                                    <td>
                                        @if($vitalSign->morphable)
                                            <a href="{{ $vitalSign->morphable_type == 'App\\Models\\Hospitalization' ? route('hospitalizations.show', $vitalSign->morphable) : route('under_reviews.show', $vitalSign->morphable) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                {{ class_basename($vitalSign->morphable_type) }} #{{ $vitalSign->morphable_id }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ localize('created_at') }}:</th>
                                    <td>{{ $vitalSign->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ localize('updated_at') }}:</th>
                                    <td>{{ $vitalSign->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @if($vitalSign->createdBy)
                                <tr>
                                    <th>{{ localize('created_by') }}:</th>
                                    <td>{{ $vitalSign->createdBy->name }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>


                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            @can('update', $vitalSign)
                                <a href="{{ route('vital-signs.edit', $vitalSign) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> {{ localize('edit') }}
                                </a>
                            @endcan
                            @can('delete', $vitalSign)
                                <form action="{{ route('vital-signs.destroy', $vitalSign) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ localize('confirm_delete') }} {{ localize('vital_sign') }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> {{ localize('delete') }}
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
