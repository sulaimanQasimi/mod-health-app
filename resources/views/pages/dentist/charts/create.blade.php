@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">{{ localize('global.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dentist-registrations.show', $dentistRegistration) }}" class="text-decoration-none">{{ localize('global.registration_details') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ localize('global.create_dental_chart') }}</li>
                        </ol>
                    </nav>
                    <h2 class="h4 mb-0">{{ localize('global.create_dental_chart') }}</h2>
                </div>
            </div>

            <!-- Chart Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.chart_information') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('dental-charts.store', $dentistRegistration) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tooth_number" class="form-label">{{ localize('global.tooth_number') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('tooth_number') is-invalid @enderror" id="tooth_number" name="tooth_number" required>
                                    <option value="">{{ localize('global.select_tooth') }}</option>
                                    @for($i = 11; $i <= 18; $i++)
                                        <option value="{{ $i }}" {{ old('tooth_number') == $i ? 'selected' : '' }}>{{ $i }} - Upper Right</option>
                                    @endfor
                                    @for($i = 21; $i <= 28; $i++)
                                        <option value="{{ $i }}" {{ old('tooth_number') == $i ? 'selected' : '' }}>{{ $i }} - Upper Left</option>
                                    @endfor
                                    @for($i = 31; $i <= 38; $i++)
                                        <option value="{{ $i }}" {{ old('tooth_number') == $i ? 'selected' : '' }}>{{ $i }} - Lower Left</option>
                                    @endfor
                                    @for($i = 41; $i <= 48; $i++)
                                        <option value="{{ $i }}" {{ old('tooth_number') == $i ? 'selected' : '' }}>{{ $i }} - Lower Right</option>
                                    @endfor
                                </select>
                                @error('tooth_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="chart_date" class="form-label">{{ localize('global.chart_date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('chart_date') is-invalid @enderror" 
                                    id="chart_date" name="chart_date" value="{{ old('chart_date', date('Y-m-d')) }}" required>
                                @error('chart_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tooth_condition" class="form-label">{{ localize('global.tooth_condition') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('tooth_condition') is-invalid @enderror" id="tooth_condition" name="tooth_condition" required>
                                    <option value="healthy" {{ old('tooth_condition') == 'healthy' ? 'selected' : '' }}>{{ localize('global.healthy') }}</option>
                                    <option value="cavity" {{ old('tooth_condition') == 'cavity' ? 'selected' : '' }}>{{ localize('global.cavity') }}</option>
                                    <option value="filling" {{ old('tooth_condition') == 'filling' ? 'selected' : '' }}>{{ localize('global.filling') }}</option>
                                    <option value="crown" {{ old('tooth_condition') == 'crown' ? 'selected' : '' }}>{{ localize('global.crown') }}</option>
                                    <option value="bridge" {{ old('tooth_condition') == 'bridge' ? 'selected' : '' }}>{{ localize('global.bridge') }}</option>
                                    <option value="root_canal" {{ old('tooth_condition') == 'root_canal' ? 'selected' : '' }}>{{ localize('global.root_canal') }}</option>
                                    <option value="implant" {{ old('tooth_condition') == 'implant' ? 'selected' : '' }}>{{ localize('global.implant') }}</option>
                                    <option value="decay" {{ old('tooth_condition') == 'decay' ? 'selected' : '' }}>{{ localize('global.decay') }}</option>
                                    <option value="fractured" {{ old('tooth_condition') == 'fractured' ? 'selected' : '' }}>{{ localize('global.fractured') }}</option>
                                    <option value="extraction" {{ old('tooth_condition') == 'extraction' ? 'selected' : '' }}>{{ localize('global.extraction') }}</option>
                                    <option value="missing" {{ old('tooth_condition') == 'missing' ? 'selected' : '' }}>{{ localize('global.missing') }}</option>
                                    <option value="impacted" {{ old('tooth_condition') == 'impacted' ? 'selected' : '' }}>{{ localize('global.impacted') }}</option>
                                </select>
                                @error('tooth_condition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gum_health" class="form-label">{{ localize('global.gum_health') }}</label>
                                <select class="form-select @error('gum_health') is-invalid @enderror" id="gum_health" name="gum_health">
                                    <option value="">{{ localize('global.select') }}</option>
                                    <option value="healthy" {{ old('gum_health') == 'healthy' ? 'selected' : '' }}>{{ localize('global.healthy') }}</option>
                                    <option value="gingivitis" {{ old('gum_health') == 'gingivitis' ? 'selected' : '' }}>{{ localize('global.gingivitis') }}</option>
                                    <option value="periodontitis" {{ old('gum_health') == 'periodontitis' ? 'selected' : '' }}>{{ localize('global.periodontitis') }}</option>
                                    <option value="recession" {{ old('gum_health') == 'recession' ? 'selected' : '' }}>{{ localize('global.recession') }}</option>
                                    <option value="bleeding" {{ old('gum_health') == 'bleeding' ? 'selected' : '' }}>{{ localize('global.bleeding') }}</option>
                                </select>
                                @error('gum_health')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="oral_hygiene_score" class="form-label">{{ localize('global.oral_hygiene_score') }}</label>
                                <input type="number" step="0.1" min="0" max="10" class="form-control @error('oral_hygiene_score') is-invalid @enderror" 
                                    id="oral_hygiene_score" name="oral_hygiene_score" value="{{ old('oral_hygiene_score') }}">
                                @error('oral_hygiene_score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pocket_depth" class="form-label">{{ localize('global.pocket_depth') }} (mm)</label>
                                <input type="number" step="0.01" min="0" max="20" class="form-control @error('pocket_depth') is-invalid @enderror" 
                                    id="pocket_depth" name="pocket_depth" value="{{ old('pocket_depth') }}">
                                @error('pocket_depth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="bleeding" class="form-label">{{ localize('global.bleeding') }}</label>
                                <select class="form-select @error('bleeding') is-invalid @enderror" id="bleeding" name="bleeding">
                                    <option value="0" {{ old('bleeding', 0) == 0 ? 'selected' : '' }}>{{ localize('global.no') }}</option>
                                    <option value="1" {{ old('bleeding') == 1 ? 'selected' : '' }}>{{ localize('global.yes') }}</option>
                                </select>
                                @error('bleeding')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="mobility" class="form-label">{{ localize('global.mobility') }}</label>
                                <select class="form-select @error('mobility') is-invalid @enderror" id="mobility" name="mobility">
                                    <option value="">{{ localize('global.select') }}</option>
                                    <option value="none" {{ old('mobility') == 'none' ? 'selected' : '' }}>{{ localize('global.none') }}</option>
                                    <option value="grade1" {{ old('mobility') == 'grade1' ? 'selected' : '' }}>{{ localize('global.grade1') }}</option>
                                    <option value="grade2" {{ old('mobility') == 'grade2' ? 'selected' : '' }}>{{ localize('global.grade2') }}</option>
                                    <option value="grade3" {{ old('mobility') == 'grade3' ? 'selected' : '' }}>{{ localize('global.grade3') }}</option>
                                </select>
                                @error('mobility')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="treatment_history" class="form-label">{{ localize('global.treatment_history') }}</label>
                                <textarea class="form-control @error('treatment_history') is-invalid @enderror" 
                                    id="treatment_history" name="treatment_history" rows="3">{{ old('treatment_history') }}</textarea>
                                @error('treatment_history')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">{{ localize('global.notes') }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                    id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('dentist-registrations.show', $dentistRegistration) }}" class="btn btn-secondary">
                                {{ localize('global.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save"></i> {{ localize('global.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
