@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.create_new_permission') }}</h5>
                </div>
                <div class="card-body">
                    <p>{{$patient->name}}</p>
                    <p>{{$patient->last_name}}</p>
                    <p>{{$patient->phone}}</p>

                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAppointmentModal">Assign Appointment</button>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Doctor Name</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patient->appointments as $appointment)
                            <td>{{$appointment->doctor->name}}</td>
                            <td>{{$appointment->created_at}}</td>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal fade" id="createAppointmentModal" tabindex="-1" aria-labelledby="createAppointmentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createAppointmentModalLabel">Create Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="createAppointmentForm" action="{{ route('appointments.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                            <!-- Add other appointment form fields as needed -->
                            <select class="form-control select2" name="doctor_id">
                                <option value="">{{ localize('global.select') }}</option>
                                @foreach($doctors as $value)
                                    <option value="{{ $value->id }}"
                                        {{ old('name') == $value->id ? 'selected' : '' }}>
                                    {{ $value->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" form="createAppointmentForm">Create</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
