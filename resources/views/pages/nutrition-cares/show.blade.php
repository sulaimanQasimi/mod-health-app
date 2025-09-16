@extends('layouts.master')

@section('title', localize('global.nutrition_care'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ localize('global.nutrition_care_form') }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('nutrition-cares.edit', $nutritionCare) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> {{ localize('global.edit_nutrition_care') }}
                        </a>
                        <a href="{{ route('nutrition-cares.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ localize('global.back_to_list') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.patient_name') }}:</label>
                                <p class="form-control-plaintext">{{ $nutritionCare->patient_name }}</p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.nurse') }}:</label>
                                <p class="form-control-plaintext">{{ $nutritionCare->nurse->full_name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.record_type') }}:</label>
                                <p class="form-control-plaintext">
                                    @if($nutritionCare->morphable_type == 'App\Models\UnderReview')
                                        <span class="badge bg-warning">Under Review</span>
                                    @elseif($nutritionCare->morphable_type == 'App\Models\Hospitalization')
                                        <span class="badge bg-info">Hospitalization</span>
                                    @else
                                        <span class="badge bg-secondary">{{ class_basename($nutritionCare->morphable_type) }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.record_id') }}:</label>
                                <p class="form-control-plaintext">{{ $nutritionCare->morphable_id }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.created_at') }}:</label>
                                <p class="form-control-plaintext">{{ $nutritionCare->created_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Observations Section -->
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">{{ localize('global.observations') }}</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->cough ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.cough') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->sound ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.sound') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->fluid_swallowing_ability ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.fluid_swallowing_ability') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->weight ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.weight') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->amount_and_type_of_nutrition ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.amount_and_type_of_nutrition') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->diarrhea ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.diarrhea') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->heart_failure_and_kidney_disease ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.heart_failure_and_kidney_disease') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->remaining_materials ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.remaining_materials') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->type_of_tube ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.type_of_tube') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Interventions Section -->
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">{{ localize('global.interventions') }}</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->constipation ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.constipation') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->nutrition_is_provided ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.nutrition_is_provided') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->mouth_hygiene ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.mouth_hygiene') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->oral_nutrition_advices ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.oral_nutrition_advices') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->voice_exercise ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.voice_exercise') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->swallowing_exercise ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.swallowing_exercise') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" {{ $nutritionCare->aspiration_prevention_proceeded ? 'checked' : '' }} disabled>
                                        <label class="form-check-label">{{ localize('global.aspiration_prevention_proceeded') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Full Note Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.nutrition_care_full_note') }}:</label>
                                <div class="border rounded p-3 bg-light">
                                    @if($nutritionCare->nutrition_care_full_note)
                                        <p class="mb-0">{{ $nutritionCare->nutrition_care_full_note }}</p>
                                    @else
                                        <p class="mb-0 text-muted">{{ localize('global.no_note_recorded') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Audit Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.created_by') }}:</label>
                                <p class="form-control-plaintext">{{ $nutritionCare->createdBy->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.date_signature') }}:</label>
                                <p class="form-control-plaintext">{{ $nutritionCare->created_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($nutritionCare->updated_at != $nutritionCare->created_at)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ localize('global.updated_by') }}:</label>
                                    <p class="form-control-plaintext">{{ $nutritionCare->updatedBy->name ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ localize('global.updated_at') }}:</label>
                                    <p class="form-control-plaintext">{{ $nutritionCare->updated_at->format('Y-m-d H:i:s') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($nutritionCare->morphable)
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ localize('global.related_record') }}:</label>
                                    <div class="border rounded p-3 bg-light">
                                        <p class="mb-1"><strong>Type:</strong> {{ class_basename($nutritionCare->morphable_type) }}</p>
                                        <p class="mb-1"><strong>ID:</strong> {{ $nutritionCare->morphable_id }}</p>
                                        @if($nutritionCare->morphable->patient)
                                            <p class="mb-0"><strong>Patient:</strong> {{ $nutritionCare->morphable->patient->first_name }} {{ $nutritionCare->morphable->patient->last_name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <form action="{{ route('nutrition-cares.destroy', $nutritionCare) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this nutrition care record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> {{ localize('global.delete_nutrition_care') }}
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
