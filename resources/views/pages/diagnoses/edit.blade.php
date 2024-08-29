@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.edit_diagnose') }}</h5>
                    </div>

                    <div class="card-body">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-md-12">
                                    <form action="{{ route('diagnoses.update', $diagnose->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" id="patient_id{{ $diagnose->appointment->patient_id }}" name="patient_id" value="{{ $diagnose->appointment->patient_id }}">
                                        <input type="hidden" id="appointment_id{{ $diagnose->appointment->id }}" name="appointment_id" value="{{ $diagnose->appointment->id }}">

                                        <div class="form-group">
                                            <label for="type{{ $diagnose->appointment->id }}">{{ localize('global.diagnose_type') }}</label>
                                            <select class="form-control select2" name="type">
                                                <option value="">{{ localize('global.select') }}</option>
                                                <option value="0" {{ $diagnose->type == 0 ? 'selected' : '' }}>{{ localize('global.primary') }}</option>
                                                <option value="1" {{ $diagnose->type == 1 ? 'selected' : '' }}>{{ localize('global.final') }}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="description{{ $diagnose->appointment->id }}">{{ localize('global.description_with_diaseases') }}</label>
                                            <textarea class="form-control" id="description{{ $diagnose->appointment->id }}" name="description" rows="3">{{ $diagnose->description }}</textarea>
                                        </div>

                                        <h5 class="mt-4">{{ localize('global.vital_signs') }}</h5>

                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="bp{{ $diagnose->appointment->id }}">{{ localize('global.bp') }}</label>
                                                    <input type="text" class="form-control" name="bp" value="{{ $diagnose->bp }}" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="pr{{ $diagnose->appointment->id }}">{{ localize('global.pr') }}</label>
                                                    <input type="text" class="form-control" name="pr" value="{{ $diagnose->pr }}" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="weight{{ $diagnose->appointment->id }}">{{ localize('global.weight') }}</label>
                                                    <input type="text" class="form-control" name="weight" value="{{ $diagnose->weight }}" />
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-4">
                                                    <label for="t{{ $diagnose->appointment->id }}">{{ localize('global.t') }}</label>
                                                    <input type="text" class="form-control" name="t" value="{{ $diagnose->t }}" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="spo2{{ $diagnose->appointment->id }}">{{ localize('global.spo2') }}</label>
                                                    <input type="text" class="form-control" name="spo2" value="{{ $diagnose->spo2 }}" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="pain{{ $diagnose->appointment->id }}">{{ localize('global.pain') }}</label>
                                                    <input type="text" class="form-control" name="pain" value="{{ $diagnose->pain }}" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group text-right mt-4">
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
    </div>
@endsection