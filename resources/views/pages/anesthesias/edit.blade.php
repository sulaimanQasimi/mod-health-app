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
                        <h5 class="mb-0">{{ localize('global.new_anesthesias') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <form action="{{ route('anesthesias.updateAnesthesia', $anesthesia) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" id="patient_id{{ $anesthesia->appointment->patient_id }}" name="patient_id" value="{{ $anesthesia->appointment->patient_id }}">
                                        <input type="hidden" id="appointment_id{{ $anesthesia->appointment->id }}" name="appointment_id" value="{{ $anesthesia->appointment->id }}">
                                        <input type="hidden" id="doctor_id{{ $anesthesia->appointment->id }}" name="doctor_id" value="{{ auth()->user()->id }}">
                                        <input type="hidden" id="branch_id{{ $anesthesia->appointment->id }}" name="branch_id" value="{{ auth()->user()->branch_id }}">
                                    
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="plan{{ $anesthesia->appointment->id }}">{{ localize('global.plan') }}</label>
                                                    <textarea class="form-control" id="plan{{ $anesthesia->appointment->id }}" name="plan" rows="3">{{ $anesthesia->plan }}</textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="other_problems{{ $anesthesia->appointment->id }}">{{ localize('global.other_problems') }}</label>
                                                    <textarea class="form-control" id="other_problems{{ $anesthesia->appointment->id }}" name="other_problems" rows="3">{{ $anesthesia->other_problems }}</textarea>
                                                </div>
                                            </div>
                                            <h5 class="mt-2">{{ localize('global.operation_team') }}</h5>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label for="operation_surgion_id{{ $anesthesia->appointment->id }}">{{ localize('global.operation_surgion') }}</label>
                                                        <select class="form-control select2" name="operation_surgion_id" id="operation_surgion_id">
                                                            <option value="">{{ localize('global.select') }}</option>
                                                            @foreach ($operation_doctors as $value)
                                                                <option value="{{ $value->id }}" {{ $anesthesia->operation_surgion_id == $value->id ? 'selected' : '' }}>
                                                                    {{ $value->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="operation_assistants_id{{ $anesthesia->appointment->id }}">{{ localize('global.operation_assistants') }}</label>
                                                        <select class="form-control select2" name="operation_assistants_id[]" id="operation_assistants_id" multiple>
                                                            <option value="">{{ localize('global.select') }}</option>
                                                            @foreach ($operation_doctors as $value)
                                                                <option value="{{ $value->id }}" {{ in_array($value->id, json_decode($anesthesia->operation_assistants_id)) ? 'selected' : '' }}>
                                                                    {{ $value->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="anesthesia_type{{ $anesthesia->appointment->id }}">{{ localize('global.anesthesia_type') }}</label>
                                                        <select class="form-control select2" name="anesthesia_type" id="anesthesia_type">
                                                            <option value="">{{ localize('global.select') }}</option>
                                                            <option value="local" {{ $anesthesia->anesthesia_type == 'local' ? 'selected' : '' }}>{{ localize('global.local') }}</option>
                                                            <option value="spinal" {{ $anesthesia->anesthesia_type == 'spinal' ? 'selected' : '' }}>{{ localize('global.spinal') }}</option>
                                                            <option value="general" {{ $anesthesia->anesthesia_type == 'general' ? 'selected' : '' }}>{{ localize('global.general') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                    
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="operation_type_id{{ $anesthesia->appointment->id }}" class="mt-2 mb-2">{{ localize('global.operation_type') }}</label>
                                                    <select class="form-control select2" name="operation_type_id">
                                                        <option value="">{{ localize('global.select') }}</option>
                                                        @foreach ($operationTypes as $value)
                                                            <option value="{{ $value->id }}" {{ $anesthesia->operation_type_id == $value->id ? 'selected' : '' }}>
                                                                {{ $value->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="date" class="mt-2 mb-2">{{ localize('global.date') }}</label>
                                                    <x-tools.dariDatePicker name="date" dir="ltr"
                                                    withID="date" withPlaceHolder="{{ localize('global.date') }}"
                                                    withSize="3" extraClasses="" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="time" class="mt-2 mb-2">{{ localize('global.time') }}</label>
                                                    <input type="time" class="form-control" name="time" value="{{ $anesthesia->time }}">
                                                </div>
                                            </div>
                                    
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="planned_duration" class="mt-2 mb-2">{{ localize('global.planned_duration') }}</label>
                                                    <input type="text" class="form-control" name="planned_duration" value="{{ $anesthesia->planned_duration }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="position_on_bed" class="mt-2 mb-2">{{ localize('global.position_on_bed') }}</label>
                                                    <input type="text" class="form-control" name="position_on_bed" value="{{ $anesthesia->position_on_bed }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="estimated_blood_waste" class="mt-2 mb-2">{{ localize('global.estimated_blood_waste') }}</label>
                                                    <input type="text" class="form-control" name="estimated_blood_waste" value="{{ $anesthesia->estimated_blood_waste }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mt-2">
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
