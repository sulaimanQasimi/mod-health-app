@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.appointment_details') }}</h5>
                </div>
                <div class="card-body">

                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                            <div>
                                {{$appointment->patient->name}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.diagnosed_by') }}</h5>
                            <div>
                                {{$appointment->doctor->name}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.date') }}</h5>
                            <div>
                                {{$appointment->date}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.time') }}</h5>
                            <div>
                                {{$appointment->time}}
                            </div>
                        </div>
                        </div>

                        <hr>
                        <h5 class="mb-3">{{ localize('global.diagnose') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createDiagnoseModal{{ $appointment->id }}"><span><i class="bx bx-plus"></i></span></button>
                                <!-- Create Diagnose Modal -->
                                <div class="modal fade" id="createDiagnoseModal{{ $appointment->id }}" tabindex="-1" aria-labelledby="createDiagnoseModalLabel{{ $appointment->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createDiagnoseModalLabel{{ $appointment->id }}">{{localize('global.add_diagnose')}}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('diagnoses.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" id="patient_id{{ $appointment->patient_id }}" name="patient_id" value="{{ $appointment->patient_id }}">
                                                    <input type="hidden" id="appointment_id{{ $appointment->id }}" name="appointment_id" value="{{ $appointment->id }}">
                                                    <!-- Add other diagnosis form fields as needed -->
                                                    <div class="form-group">
                                                        <label for="description{{ $appointment->id }}">{{localize('global.description')}}</label>
                                                        <textarea class="form-control" id="description{{ $appointment->id }}" name="description" rows="3"></textarea>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{localize('global.cancel')}}</button>
                                                <button type="submit" class="btn btn-primary">{{localize('global.save')}}</button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Create Diagnose Modal -->
                        <div class="col-md-12 mt-4">




                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{localize('global.number')}}</th>
                                    <th>{{localize('global.description')}}</th>
                                    <th>{{localize('global.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($appointment->diagnose as $diagnose)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$diagnose->description}}</td>
                                    <td>
                                        <a href="{{route('diagnoses.edit', $diagnose->id)}}"><span><i class="bx bx-edit"></i></span></a>
                                        <a href="{{route('diagnoses.destroy', $diagnose->id)}}"><span><i class="bx bx-trash text-danger"></i></span></a>

                                    </td>
                                </tr>
                                @empty

                                    <p class="text-center badge bg-primary">

                                        {{ localize('global.no_previous_diagnoses') }}
                                    </p>
                            @endforelse
                            </tbody>
                        </table>

                        </div>
                        <hr>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
