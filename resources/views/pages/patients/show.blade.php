@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.view_patient') }}</h5>
                </div>
                <div class="card-body">

            <div class="container">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Content for the first column on the right side -->
                                <label for="label1">{{localize('global.name')}}</label>
                                <span id="label1" class="text-center">{{ $patient->name }}</span>
                            </div>
                            <div class="col-md-6">
                                <!-- Content for the second column on the right side -->
                                <label for="label2">{{localize('global.last_name')}}</label>
                                <span id="label2" class="text-end">{{ $patient->last_name }}</span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Content for the first column on the right side -->
                                <label for="label1">{{localize('global.phone')}}</label>
                                <span id="label1" class="text-center">{{ $patient->phone }}</span>
                            </div>
                            <div class="col-md-6">
                                <!-- Content for the second column on the right side -->
                                <label for="label2">{{localize('global.name')}}</label>
                                <span id="label2" class="text-end">{{ $patient->last_name }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 card p-2 border border-info">
                        <!-- Left side content -->
                        <div class="row">
                            <div class="col-md-12">
                                {!! QrCode::size(100)->generate($patient->id) !!}
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <a href="{{ route('patients.print-card', $patient->id) }}" target="_blank" class="btn btn-primary">{{localize('global.print_card')}}</a>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAppointmentModal">{{localize('global.assign_appointment')}}</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

                <hr>
                <h5 class="mb-0 p-3">{{ localize('global.all_appointments') }}</h5>

                <table class="table">
                    <thead>
                        <tr>
                            <th>{{localize('global.number')}}</th>
                            <th>{{localize('global.doctor_name')}}</th>
                            <th>{{localize('global.date')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patient->appointments as $appointment)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$appointment->doctor->name}}</td>
                            <td>{{$appointment->created_at}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <hr>
                <h5 class="mb-0 p-3">{{ localize('global.all_diagnoses') }}</h5>

                <table class="table">
                    <thead>
                        <tr>
                            <th>{{localize('global.number')}}</th>
                            <th>{{localize('global.doctor_name')}}</th>
                            <th>{{localize('global.description')}}</th>
                            <th>{{localize('global.date')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patient->diagnoses as $diagnose)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$diagnose->doctor->name}}</td>
                            <td>{{$diagnose->description}}</td>
                            <td>{{$diagnose->created_at}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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

