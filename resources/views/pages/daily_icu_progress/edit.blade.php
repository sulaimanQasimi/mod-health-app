@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="col-md-12">
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-hourglass p-1"></i>{{ localize('global.edit_daily_icu_progress') }}</h5>
                            <form action="{{ route('daily_icu_progress.update', $dailyIcuProgress->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <input type="hidden" id="i_c_u_id" name="i_c_u_id"
                                    value="{{ $dailyIcuProgress->i_c_u_id }}">

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>{{ localize('global.icu_day') }}</label>
                                            <input type="text" class="form-control" name="icu_day"
                                                value="{{ old('icu_day', $dailyIcuProgress->icu_day) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>{{ localize('global.icu_diagnose') }}</label>
                                            <input type="text" class="form-control" name="icu_diagnose"
                                                value="{{ old('icu_diagnose', $dailyIcuProgress->icu_diagnose) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>{{ localize('global.daily_events') }}</label>
                                            <input type="text" class="form-control" name="daily_events"
                                                value="{{ old('daily_events', $dailyIcuProgress->daily_events) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>{{ localize('global.hr') }}</label>
                                            <input type="text" class="form-control" name="hr"
                                                value="{{ old('hr', $dailyIcuProgress->hr) }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>{{ localize('global.bp') }}</label>
                                            <input type="text" class="form-control" name="bp"
                                                value="{{ old('bp', $dailyIcuProgress->bp) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>{{ localize('global.spo2') }}</label>
                                            <input type="text" class="form-control" name="spo2"
                                                value="{{ old('spo2', $dailyIcuProgress->spo2) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>{{ localize('global.t') }}</label>
                                            <input type="text" class="form-control" name="t"
                                                value="{{ old('t', $dailyIcuProgress->t) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>{{ localize('global.rr') }}</label>
                                            <input type="text" class="form-control" name="rr"
                                                value="{{ old('rr', $dailyIcuProgress->rr) }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>{{ localize('global.gcs') }}</label>
                                            <input type="text" class="form-control" name="gcs"
                                                value="{{ old('gcs', $dailyIcuProgress->gcs) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>{{ localize('global.cvs') }}</label>
                                            <input type="text" class="form-control" name="cvs"
                                                value="{{ old('cvs', $dailyIcuProgress->cvs) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>{{ localize('global.pupils') }}</label>
                                            <input type="text" class="form-control" name="pupils"
                                                value="{{ old('pupils', $dailyIcuProgress->pupils) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>{{ localize('global.s1s2') }}</label>
                                            <input type="text" class="form-control" name="s1s2"
                                                value="{{ old('s1s2', $dailyIcuProgress->s1s2) }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>{{ localize('global.rs') }}</label>
                                            <input type="text" class="form-control" name="rs"
                                                value="{{ old('rs', $dailyIcuProgress->rs) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>{{ localize('global.gi') }}</label>
                                            <input type="text" class="form-control" name="gi"
                                                value="{{ old('gi', $dailyIcuProgress->gi) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>{{ localize('global.renal') }}</label>
                                            <input type="text" class="form-control" name="renal"
                                                value="{{ old('renal', $dailyIcuProgress->renal) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>{{ localize('global.musculoskeletal_system') }}</label>
                                            <input type="text" class="form-control" name="musculoskeletal_system"
                                                value="{{ old('musculoskeletal_system', $dailyIcuProgress->musculoskeletal_system) }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>{{ localize('global.extremities') }}</label>
                                            <input type="text" class="form-control" name="extremities"
                                                value="{{ old('extremities', $dailyIcuProgress->extremities) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>{{ localize('global.assesment') }}</label>
                                            <input type="text" class="form-control" name="assesment"
                                                value="{{ old('assesment', $dailyIcuProgress->assesment) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>{{ localize('global.icu_daily_plan') }}</label>
                                            <input type="text" class="form-control" name="plan"
                                                value="{{ old('plan', $dailyIcuProgress->plan) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="lab_ids">{{ localize('global.lab_ids') }}</label>
                                            <select class="form-control select2" name="lab_ids[]" id="lab_ids"
                                                multiple>
                                                <option value="">{{ localize('global.select') }}</option>
                                                @foreach ($labTypes as $value)
                                                    <option value="{{ $value->id }}"
                                                        {{ in_array($value->id, json_decode($dailyIcuProgress->lab_ids)) ? 'selected' : '' }}>
                                                        {{ $value->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <a href="{{ url()->previous() }}"><button type="button"
                                        class="btn btn-danger">{{ localize('global.back') }}</button></a>
                                    <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
