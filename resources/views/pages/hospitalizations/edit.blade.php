@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ localize('global.edit_hospitalization') }}</h5>

            </div>

        <div class="card-body">


    <form action="{{ route('hospitalizations.updateHospitalization', $hospitalization->id) }}" method="POST">
        @csrf
        @method('PUT')

        <input type="hidden" name="patient_id" value="{{ $hospitalization->patient_id }}">
        <input type="hidden" name="appointment_id" value="{{ $hospitalization->appointment_id }}">
        <input type="hidden" name="doctor_id" value="{{ auth()->user()->id }}">
        <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-2">
                    <label for="reason">{{ localize('global.reason') }}</label>
                    <textarea class="form-control" id="reason" name="reason" rows="3">{{ $hospitalization->reason }}</textarea>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group mb-2">
                    <label for="remarks">{{ localize('global.remarks') }}</label>
                    <textarea class="form-control" id="remarks" name="remarks" rows="3">{{ $hospitalization->remarks }}</textarea>
                </div>
            </div>
        </div>




        <div class="form-group">
            <div class="row">
                <div class="col-md-4">
                    <label for="room_id">{{ localize('global.rooms') }}</label>
                    <select class="form-control select2" name="room_id" id="room_id">
                        <option value="">{{ localize('global.select') }}</option>
                        @foreach ($rooms as $value)
                            <option value="{{ $value->id }}"
                                {{ $hospitalization->room_id == $value->id ? 'selected' : '' }}>
                                {{ $value->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="bed_id">{{ localize('global.beds') }}</label>
                    <select class="form-control select2" name="bed_id" id="bed_id">
                        <option value="">{{ localize('global.select') }}</option>
                        @foreach ($beds as $value)
                            <option value="{{ $value->id }}"
                                {{ $hospitalization->bed_id == $value->id ? 'selected' : '' }}>
                                {{ $value->number }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="food_type_id">{{ localize('global.food_type') }}</label>
                    <select class="form-control select2" name="food_type_id[]" id="food_type_id" multiple>
                        <option value="">{{ localize('global.select') }}</option>
                        @foreach ($foodTypes as $value)
                            <option value="{{ $value->id }}"
                                {{ $hospitalization->food_type_id == $value->id ? 'selected' : '' }}>
                                {{ $value->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h5 class="mt-4">{{ localize('global.patient_companion_info') }}</h5>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-3">
                        <label>{{ localize('global.companion_name') }}</label>
                        <input type="text" class="form-control" name="patinet_companion" value="{{ $hospitalization->patinet_companion }}">
                    </div>
                    <div class="col-md-3">
                        <label>{{ localize('global.companion_father_name') }}</label>
                        <input type="text" class="form-control" name="companion_father_name" value="{{ $hospitalization->companion_father_name }}">
                    </div>
                    <div class="col-md-3">
                        <label>{{ localize('global.relation_to_patient') }}</label>
                        <select class="form-control select2" name="relation_to_patient">
                            <option value="">{{ localize('global.select') }}</option>
                            @foreach ($relations as $value)
                                <option value="{{ $value->id }}"
                                    {{ $hospitalization->relation_to_patient == $value->id ? 'selected' : '' }}>
                                    {{ $value->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>{{ localize('global.companion_card_type') }}</label>
                        <select class="form-control select2" name="companion_card_type">
                            <option value="">{{ localize('global.select') }}</option>
                            <option value="12" {{ $hospitalization->companion_card_type == 12 ? 'selected' : '' }}>
                                {{ localize('global.12_hours') }}</option>
                            <option value="24" {{ $hospitalization->companion_card_type == 24 ? 'selected' : '' }}>
                                {{ localize('global.24_hours') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
            <a href="{{ route('hospitalizations.index') }}" class="btn btn-secondary">{{ localize('global.cancel') }}</a>
        </div>
    </form>
        </div>
    </div>
</div>
@endsection
