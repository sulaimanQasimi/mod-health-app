@extends('layouts.master')

@section('title', localize('global.view_nurse_note'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ localize('global.nurse_note_details') }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('nurse-notes.edit', $nurseNote) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> {{ localize('global.edit_nurse_note') }}
                        </a>
                        <a href="{{ route('nurse-notes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ localize('global.back_to_list') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.nurse_note_nurse') }}:</label>
                                <p class="form-control-plaintext">{{ $nurseNote->nurse->full_name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.nurse_note_date') }}:</label>
                                <p class="form-control-plaintext">{{ $nurseNote->date ? $nurseNote->date->format('Y-m-d') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.nurse_note_type') }}:</label>
                                <p class="form-control-plaintext">
                                    @if($nurseNote->morphable_type == 'App\Models\UnderReview')
                                        <span class="badge bg-warning">Under Review</span>
                                    @elseif($nurseNote->morphable_type == 'App\Models\Hospitalization')
                                        <span class="badge bg-info">Hospitalization</span>
                                    @else
                                        <span class="badge bg-secondary">{{ class_basename($nurseNote->morphable_type) }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.record_id') }}:</label>
                                <p class="form-control-plaintext">{{ $nurseNote->morphable_id }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.nurse_note_am_time') }}:</label>
                                <div class="border rounded p-3 bg-light">
                                    @if($nurseNote->time_am)
                                        <p class="mb-0"><span class="badge bg-primary">{{ $nurseNote->time_am->format('H:i') }}</span></p>
                                    @else
                                        <p class="mb-0 text-muted">{{ localize('global.no_am_time_recorded') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.nurse_note_pm_time') }}:</label>
                                <div class="border rounded p-3 bg-light">
                                    @if($nurseNote->time_pm)
                                        <p class="mb-0"><span class="badge bg-primary">{{ $nurseNote->time_pm->format('H:i') }}</span></p>
                                    @else
                                        <p class="mb-0 text-muted">{{ localize('global.no_pm_time_recorded') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.note') }}:</label>
                                <div class="border rounded p-3 bg-light">
                                    @if($nurseNote->note)
                                        <p class="mb-0">{{ $nurseNote->note }}</p>
                                    @else
                                        <p class="mb-0 text-muted">{{ localize('global.no_note_recorded') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.nurse_note_created_by') }}:</label>
                                <p class="form-control-plaintext">{{ $nurseNote->createdBy->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.nurse_note_created_at') }}:</label>
                                <p class="form-control-plaintext">{{ $nurseNote->created_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($nurseNote->updated_at != $nurseNote->created_at)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ localize('global.updated_by') }}:</label>
                                    <p class="form-control-plaintext">{{ $nurseNote->updatedBy->name ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ localize('global.updated_at') }}:</label>
                                    <p class="form-control-plaintext">{{ $nurseNote->updated_at->format('Y-m-d H:i:s') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($nurseNote->morphable)
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ localize('global.related_record') }}:</label>
                                    <div class="border rounded p-3 bg-light">
                                        <p class="mb-1"><strong>Type:</strong> {{ class_basename($nurseNote->morphable_type) }}</p>
                                        <p class="mb-1"><strong>ID:</strong> {{ $nurseNote->morphable_id }}</p>
                                        @if($nurseNote->morphable->patient)
                                            <p class="mb-0"><strong>Patient:</strong> {{ $nurseNote->morphable->patient->first_name }} {{ $nurseNote->morphable->patient->last_name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <form action="{{ route('nurse-notes.destroy', $nurseNote) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this nurse note?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> {{ localize('global.delete') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
