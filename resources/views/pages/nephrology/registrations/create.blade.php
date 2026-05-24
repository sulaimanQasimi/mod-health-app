@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <h2 class="h4 mb-4">{{ localize('global.start_nephrology_visit') }}</h2>
            <div class="card">
                <div class="card-body">
                    <p class="mb-3">
                        <strong>{{ localize('global.patient') }}:</strong>
                        {{ $appointment->patient->name }} {{ $appointment->patient->last_name }}
                    </p>
                    <form action="{{ route('nephrology-registrations.store', $appointment) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="doctor_id" class="form-label">{{ localize('global.doctor') }}</label>
                                <select class="form-select" id="doctor_id" name="doctor_id">
                                    <option value="">{{ localize('global.select_doctor') }}</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ old('doctor_id', $appointment->doctor_id) == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="visit_date" class="form-label">{{ localize('global.visit_date') }} <span class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" class="form-control datepicker_dari pdp-el" id="visit_date" name="visit_date"
                                    value="{{ old('visit_date', $appointment->date ? verta($appointment->date)->format('Y/m/d') : verta()->format('Y/m/d')) }}" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">{{ localize('global.notes') }}</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-secondary">{{ localize('global.cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ localize('global.create_and_continue') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
