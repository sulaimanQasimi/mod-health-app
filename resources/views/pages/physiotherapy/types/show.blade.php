@extends('layouts.master')

@section('title', localize('global.physiotherapy_type_details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">{{ localize('global.physiotherapy_type_details') }}</h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('physiotherapy-types.edit', $physiotherapyType) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> {{ localize('global.edit') }}
                            </a>
                            <a href="{{ route('physiotherapy-types.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> {{ localize('global.back_to_list') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ localize('global.basic_information') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ localize('global.name') }}</label>
                                        <p class="mb-0">{{ $physiotherapyType->name }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ localize('global.description') }}</label>
                                        <p class="mb-0">
                                            @if($physiotherapyType->description)
                                                {{ $physiotherapyType->description }}
                                            @else
                                                <span class="text-muted">{{ localize('global.no_description') }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ localize('global.recent_procedures') }}</h6>
                                </div>
                                <div class="card-body">
                                    @if($physiotherapyType->physiotherapyProcedures->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>{{ localize('global.id') }}</th>
                                                        <th>{{ localize('global.patient') }}</th>
                                                        <th>{{ localize('global.physiotherapist') }}</th>
                                                        <th>{{ localize('global.status') }}</th>
                                                        <th>{{ localize('global.progress') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($physiotherapyType->physiotherapyProcedures->take(10) as $procedure)
                                                        <tr>
                                                            <td>{{ $procedure->id }}</td>
                                                            <td>{{ $procedure->appointment->patient->name ?? 'N/A' }}</td>
                                                            <td>{{ $procedure->physiotherapist->name ?? 'N/A' }}</td>
                                                            <td>
                                                                <span class="badge bg-{{ $procedure->status === 'completed' ? 'success' : ($procedure->status === 'in_progress' ? 'warning' : 'info') }}">
                                                                    {{ ucfirst(str_replace('_', ' ', $procedure->status)) }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $procedure->counter }}/{{ $procedure->days_count }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted text-center mb-0">{{ localize('global.no_procedures_found_for_this_type') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ localize('global.statistics') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ localize('global.total_procedures') }}</label>
                                        <p class="mb-0">
                                            <span class="badge bg-primary fs-6">{{ $physiotherapyType->physiotherapyProcedures->count() }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ localize('global.actions') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('physiotherapy-types.edit', $physiotherapyType) }}" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> {{ localize('global.edit_type') }}
                                        </a>
                                        @if($physiotherapyType->physiotherapyProcedures->count() == 0)
                                            <form action="{{ route('physiotherapy-types.destroy', $physiotherapyType) }}" method="POST" onsubmit="return confirm('{{ localize('global.confirm_delete') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger w-100">
                                                    <i class="fas fa-trash"></i> {{ localize('global.delete_type') }}
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-secondary w-100" disabled title="{{ localize('global.cannot_delete_with_procedures') }}">
                                                <i class="fas fa-trash"></i> {{ localize('global.delete_type') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
