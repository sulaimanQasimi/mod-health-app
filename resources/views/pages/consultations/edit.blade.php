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
                        <h5 class="mb-0">{{ localize('global.edit_advice') }}</h5>
                    </div>
                    <div class="card-body">
                    <div class="container">
                        <form action="{{ route('consultations.update', $consultation) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" id="patient_id{{ $consultation->appointment->patient_id }}" name="patient_id" value="{{ $consultation->appointment->patient_id }}">
                            <input type="hidden" id="appointment_id{{ $consultation->appointment->id }}" name="appointment_id" value="{{ $consultation->appointment->id }}">
                            <input type="hidden" id="branch_id{{ $consultation->appointment->id }}" name="branch_id" value="{{ auth()->user()->branch_id }}">

                            <div class="form-group">
                                <label for="description{{ $consultation->appointment->id }}">{{ localize('global.description') }}</label>
                                <input type="text" class="form-control" name="title" value="{{$consultation->title}}">

                                <label for="branch{{ $consultation->appointment->id }}">{{ localize('global.branch') }}</label>
                                <select class="form-control select2" name="branch" id="branch">
                                    <option value="">{{ localize('global.select') }}</option>
                                    @foreach ($branches as $value)
                                        <option value="{{ $value->id }}" {{ $value->id == $value->id ? 'selected' : '' }}>
                                            {{ $value->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <label for="department{{ $consultation->appointment->id }}">{{ localize('global.department') }}</label>
                                <select class="form-control select2" name="department_id[]" id="department" multiple>
                                    <option value="">{{ localize('global.select') }}</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" {{ in_array($department->id, json_decode($consultation->department_id, true)) ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <label for="type{{ $consultation->appointment->id }}">{{ localize('global.type') }}</label>
                                <select class="form-control select2" name="consultation_type" id="type">
                                    <option value="">{{ localize('global.select') }}</option>
                                    <option value="0" {{ $consultation->type == 0 ? 'selected' : '' }}>{{ localize('global.normal') }}</option>
                                    <option value="1" {{ $consultation->type == 1 ? 'selected' : '' }}>{{ localize('global.emergency') }}</option>
                                </select>

                                <div class="mb-3">
                                    <label for="date">{{ localize('global.date') }}</label>
                                    <input type="date" class="form-control" name="date" value="{{$consultation->date}}" />
                                </div>
                                <div class="mb-3">
                                    <label for="time">{{ localize('global.time') }}</label>
                                    <input type="time" class="form-control" name="time" value="{{$consultation->time}}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="button" class="btn btn-secondary">{{ localize('global.cancel') }}</button>
                                <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                            </div>
                        </form>
                    </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
