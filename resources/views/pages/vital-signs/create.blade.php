@extends('layouts.master')

@php
    $hasMorphable = $morphableType && $morphableId;
    $pageTitle = $hasMorphable ? localize('global.manage_vital_signs') : localize('global.create_vital_sign');
    $backUrl = null;
    if ($hasMorphable) {
        $backUrl = $morphableType === 'App\\Models\\Hospitalization'
            ? route('hospitalizations.show', $morphableId)
            : route('under_reviews.show', $morphableId);
    }
@endphp

@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-heartbeat"></i> {{ $pageTitle }}
                    </h3>
                    <div class="card-tools">
                        @if($backUrl)
                            <a href="{{ $backUrl }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> {{ localize('global.back') }}
                            </a>
                        @else
                            <a href="{{ route('vital-signs.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> {{ localize('global.back_to_list') }}
                            </a>
                        @endif
                        @if($hasMorphable && $morphModel && $morphModel->vitalSigns->count() > 0)
                            <a href="{{ route('vital-signs.print', [$morphableType, $morphableId]) }}"
                                class="btn btn-info btn-sm ms-1" target="_blank">
                                <i class="fas fa-print"></i> {{ localize('global.print_vital_signs_chart') }}
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($hasMorphable)
                        <form action="{{ route('vital-signs.store') }}" method="POST" id="multipleVitalSignsForm"
                            data-user-is-nurse="{{ $currentUserNurse ? '1' : '0' }}">
                            @csrf
                            <input type="hidden" name="morphable_type" value="{{ $morphableType }}">
                            <input type="hidden" name="morphable_id" value="{{ $morphableId }}">
                            <div id="delete-inputs-container"></div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-light border d-flex align-items-center" role="alert">
                                        <i class="fas fa-link text-primary me-2"></i>
                                        <div>
                                            <strong>{{ localize('global.related_record') }}:</strong>
                                            {{ class_basename($morphableType) }} #{{ $morphableId }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(!$currentUserNurse)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info d-flex align-items-center" role="alert">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <div>{{ localize('global.nurse_will_be_assigned_automatically_by_system') }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($morphModel && $morphModel->vitalSigns->count() > 0)
                                <div class="mb-4">
                                    <h5 class="text-secondary mb-3">
                                        <i class="fas fa-list me-1"></i> {{ localize('global.vital_signs') }}
                                    </h5>
                                    <div id="existing-vital-signs-container">
                                        @foreach($morphModel->vitalSigns as $exIndex => $vitalSign)
                                            @include('pages.vital-signs.partials.vital-sign-block', [
                                                'index' => $exIndex,
                                                'vitalSign' => $vitalSign,
                                                'vitalSignTypes' => $vitalSignTypes,
                                                'isExisting' => true,
                                            ])
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mb-3">
                                <h5 class="text-primary mb-2">
                                    <i class="fas fa-plus-circle me-1"></i> {{ localize('global.add_another_vital_sign') }}
                                </h5>
                                <p class="text-muted small mb-0">
                                    {{ localize('global.add_one_or_more_vital_signs_with_optional_schedules') }}
                                </p>
                            </div>

                            <div id="vital-signs-container">
                                @include('pages.vital-signs.partials.vital-sign-block', [
                                    'index' => 0,
                                    'vitalSign' => (object) ['vital_sign_type_id' => null, 'schedules' => collect()],
                                    'vitalSignTypes' => $vitalSignTypes,
                                    'isExisting' => false,
                                ])
                            </div>

                            <div class="mb-4">
                                <button type="button" id="add-vital-sign-btn" class="btn btn-outline-primary">
                                    <i class="fas fa-plus me-1"></i> {{ localize('global.add_another_vital_sign') }}
                                </button>
                            </div>

                            @error('vital_signs')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> {{ localize('global.save_all_vital_signs') }}
                                </button>
                                @if($backUrl)
                                    <a href="{{ $backUrl }}" class="btn btn-secondary">{{ localize('global.cancel') }}</a>
                                @endif
                            </div>
                        </form>
                    @else
                        <form action="{{ route('vital-signs.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="vital_sign_type_id">{{ localize('global.vital_sign_type_id') }} <span class="text-danger">*</span></label>
                                        <select class="form-control @error('vital_sign_type_id') is-invalid @enderror" id="vital_sign_type_id" name="vital_sign_type_id" required>
                                            <option value="">{{ localize('global.select_vital_sign_type') }}</option>
                                            @foreach($vitalSignTypes as $type)
                                                <option value="{{ $type->id }}" {{ old('vital_sign_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('vital_sign_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ localize('global.create_vital_sign') }}</button>
                                    <a href="{{ route('vital-signs.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> {{ localize('global.cancel') }}</a>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($hasMorphable)
@push('scripts')
@include('pages.vital-signs.partials.form-scripts')
@endpush
@endif
@endsection
