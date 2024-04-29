@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-body">

                    <div class="col-md-12">
                        <div class="border border-label-primary mb-4">
                            <h5 class="mb-4 p-3 bg-label-primary text-center">{{ localize('global.appointment_details') }}</h5>

                        <div class="row p-2">
                            <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                            <div>
                                {{$appointment->patient->name}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.referred_to') }}</h5>
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
                        </div>

                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i class="bx bx-popsicle p-1"></i>{{localize('global.diagnose') }}</h5>

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
                                <div class="container">
                                    <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                        <div class=" badge bg-label-danger mt-4">
                                            {{ localize('global.no_previous_diagnoses') }}
                                        </div>
                                    </div>
                                </div>
                                @endforelse
                            </tbody>
                        </table>

                        </div>





                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i class="bx bx-notepad p-1"></i>{{ localize('global.prescription') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createPrescriptionModal{{ $appointment->id }}"><span><i class="bx bx-plus"></i></span></button>
                                <!-- Create Diagnose Modal -->
                                <div class="modal fade modal-xl" id="createPrescriptionModal{{ $appointment->id }}" tabindex="-1" aria-labelledby="createPrescriptionModalLabel{{ $appointment->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createPrescriptionModalLabel{{ $appointment->id }}">{{localize('global.add_prescription')}}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('prescriptions.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" id="patient_id{{ $appointment->patient_id }}" name="patient_id" value="{{ $appointment->patient_id }}">
                                                    <input type="hidden" id="appointment_id{{ $appointment->id }}" name="appointment_id" value="{{ $appointment->id }}">
                                                    <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id" value="{{ auth()->user()->branch_id }}">
                                                    <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id" value="{{ auth()->user()->id }}">
                                                    <!-- Add other diagnosis form fields as needed -->
                                                    <div class="form-group" id="prescription-items">
                                                        <label>{{localize('global.description')}}</label>
                                                        <div id="prescription-input-container">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <input type="text" class="form-control" name="description[]" dir="ltr" placeholder="Enter name">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control" name="dosage[]" placeholder="Dosage">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control" name="frequency[]" placeholder="Frequency">
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <input type="text" class="form-control" name="amount[]" placeholder="Amount">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-primary" id="addPrescriptionInput"><i class="bx bx-plus"></i> Add Prescription</button>
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
                                        <th>{{localize('global.dosage')}}</th>
                                        <th>{{localize('global.frequency')}}</th>
                                        <th>{{localize('global.amount')}}</th>
                                        <th>{{localize('global.actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (is_array($appointment->prescription) || is_object($appointment->prescription))
                                        @forelse ($appointment->prescription as $prescription)
                                            @php
                                                $descriptions = is_array($prescription->description) ? $prescription->description : json_decode($prescription->description, true);
                                                $dosages = is_array($prescription->dosage) ? $prescription->dosage : json_decode($prescription->dosage, true);
                                                $frequencies = is_array($prescription->frequency) ? $prescription->frequency : json_decode($prescription->frequency, true);
                                                $amounts = is_array($prescription->amount) ? $prescription->amount : json_decode($prescription->amount, true);
                                            @endphp
                                            @foreach ($descriptions as $key => $description)
                                                <tr>
                                                    <td>{{ $loop->parent->iteration }}</td>
                                                    <td>{{ $description }}</td>
                                                    <td>{{ $dosages[$key] }}</td>
                                                    <td>{{ $frequencies[$key] }}</td>
                                                    <td>{{ $amounts[$key] }}</td>
                                                    <td>
                                                        <a href="{{ route('prescriptions.edit', $prescription->id) }}"><span><i class="bx bx-edit"></i></span></a>
                                                        <a href="{{ route('prescriptions.destroy', $prescription->id) }}"><span><i class="bx bx-trash text-danger"></i></span></a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="5">
                                                    <div class="container">
                                                        <div class="col-md-12 d-flex justify-content-center align-items-center">
                                                            <div class="badge bg-label-danger mt-4">
                                                                {{ localize('global.no_previous_prescriptions') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    @else
                                        <tr>
                                            <td colspan="5">
                                                <div class="container">
                                                    <div class="col-md-12 d-flex justify-content-center align-items-center">
                                                        <div class="badge bg-label-danger mt-4">
                                                            {{ localize('global.no_previous_prescriptions') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-center mt-4">
                                <form action="{{route('prescriptions.print-card', ['appointment' => $appointment->id])}}" method="GET" target="_blank">
                                    <button class="btn btn-primary" type="submit"><span class="bx bx-printer me-1"></span>{{localize('global.print_prescription')}}</button>
                                </form>
                            </div>
                        </div>



                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i class="bx bx-hard-hat p-1"></i>{{ localize('global.lab_tests') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createLabModal{{ $appointment->id }}"><span><i class="bx bx-plus"></i></span></button>
                                <!-- Create  Lab Modal -->
                                <div class="modal fade" id="createLabModal{{ $appointment->id }}" tabindex="-1" aria-labelledby="createLabModalLabel{{ $appointment->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createLabModalLabel{{ $appointment->id }}">{{localize('global.add_lab_test')}}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('lab_tests.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" id="patient_id{{ $appointment->patient_id }}" name="patient_id" value="{{ $appointment->patient_id }}">
                                                    <input type="hidden" id="appointment_id{{ $appointment->id }}" name="appointment_id" value="{{ $appointment->id }}">
                                                    <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id" value="{{ $appointment->doctor->id }}">
                                                    <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id" value="{{ auth()->user()->branch_id }}">
                                                    <input type="hidden" id="status{{ $appointment->id }}" name="status" value="0">

                                                    <div class="form-group">

                                                        <label for="lab_type_id{{ $appointment->id }}">{{localize('global.lab_type')}}</label>
                                                        <select class="form-control select2" name="lab_type_id">
                                                            <option value="">{{ localize('global.select') }}</option>
                                                            @foreach($labTypes as $value)
                                                                <option value="{{ $value->id }}"
                                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                {{ $value->name }}

                                                            </option>
                                                            @endforeach
                                                        </select>
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
                                <!-- End Create Lab Modal -->
                        <div class="col-md-12 mt-4">




                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{localize('global.number')}}</th>
                                    <th>{{localize('global.test_name')}}</th>
                                    <th>{{localize('global.test_status')}}</th>
                                    <th>{{localize('global.result')}}</th>
                                    <th>{{localize('global.result_file')}}</th>
                                    <th>{{localize('global.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($appointment->labs as $lab)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$lab->labType->name}}</td>
                                    <td>
                                        @if($lab->status == '0')
                                        <span class="badge bg-danger">{{localize('global.not_tested')}}</span>
                                        @else
                                        <span class="badge bg-success">{{localize('global.tested')}}</span>
                                        @endif
                                    </td>
                                    <td>{{$lab->result}}</td>
                                    <td>
                                        @isset($lab->result_file)
                                        <a href="{{ asset('storage/' . $lab->result_file) }}" target="_blank">
                                            <i class="fa fa-file"></i> {{localize('global.file')}}
                                        </a>
                                        @endisset

                                    </td>
                                    <td>
                                        <a href="{{route('lab_tests.edit', $lab->id)}}"><span><i class="bx bx-edit"></i></span></a>
                                        <a href="{{route('lab_tests.destroy', $lab->id)}}"><span><i class="bx bx-trash text-danger"></i></span></a>

                                    </td>

                                </tr>

                                @empty
                                <div class="container">
                                    <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                        <div class=" badge bg-label-danger mt-4">
                                            {{ localize('global.no_previous_labs') }}
                                        </div>
                                    </div>
                                </div>
                                @endforelse

                            </tbody>
                        </table>
                        
                        <div class="d-flex justify-content-center mt-4">
                            <form action="{{route('lab_tests.print-card', ['appointment' => $appointment->id])}}" method="GET" target="_blank">
                                <button class="btn btn-primary" type="submit"><span class="bx bx-printer me-1"></span>{{localize('global.print_test_ticket')}}</button>
                            </form>
                        </div>
                        </div>




                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i class="bx bx-chat p-1"></i>{{ localize('global.consultations') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createConsultationModal{{ $appointment->id }}"><span><i class="bx bx-plus"></i></span></button>
                                <!-- Create  Lab Modal -->
                                <div class="modal fade" id="createConsultationModal{{ $appointment->id }}" tabindex="-1" aria-labelledby="createConsultationModalLabel{{ $appointment->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createConsultationModalLabel{{ $appointment->id }}">{{localize('global.add_lab_test')}}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('consultations.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" id="patient_id{{ $appointment->patient_id }}" name="patient_id" value="{{ $appointment->patient_id }}">
                                                    <input type="hidden" id="appointment_id{{ $appointment->id }}" name="appointment_id" value="{{ $appointment->id }}">
                                                    <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id" value="{{ auth()->user()->branch_id }}">

                                                    <div class="form-group">

                                                        <label for="title{{ $appointment->id }}">{{localize('global.title')}}</label>
                                                        <input type="text" class="form-control" name="title">

                                                        <label for="doctor_id{{ $appointment->id }}">{{localize('global.doctors')}}</label>
                                                        <select class="form-control select2" name="doctor_id">
                                                            <option value="">{{ localize('global.select') }}</option>
                                                            @foreach($doctors as $value)
                                                                <option value="{{ $value->id }}"
                                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                {{ $value->name }}

                                                            </option>
                                                            @endforeach
                                                        </select>

                                                        <div class="mb-3">
                                                            <label for="date">{{localize('global.date')}}</label>
                                                            <input type="date" class="form-control" name="date"/>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="time">{{localize('global.time')}}</label>
                                                            <input type="time" class="form-control" name="time"/>
                                                        </div>

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
                                <!-- End Create Lab Modal -->
                        <div class="col-md-12 mt-4">




                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{localize('global.number')}}</th>
                                        <th>{{localize('global.title')}}</th>
                                        <th>{{localize('global.doctors')}}</th>
                                        <th>{{localize('global.result')}}</th>
                                        <th>{{localize('global.actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($appointment->consultations as $consultation)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$consultation->title}}</td>
                                        <td>
                                            {{$consultation->doctors}}
                                        </td>
                                        <td>
                                            {{$consultation->result}}
                                        </td>
                                        <td>
                                            <a href="{{route('consultations.edit', $consultation->id)}}"><span><i class="bx bx-edit"></i></span></a>
                                            <a href="{{route('consultations.destroy', $consultation->id)}}"><span><i class="bx bx-trash text-danger"></i></span></a>
                                        </td>
                                    </tr>
                                    @empty
                                    <div class="container">
                                        <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                            <div class=" badge bg-label-danger mt-4">
                                                {{ localize('global.no_previous_consultations') }}
                                            </div>
                                        </div>
                                    </div>
                                    @endforelse
                                </tbody>
                            </table>


                        </div>




                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i class="bx bx-bed p-1"></i>{{ localize('global.hospitalize') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createHospitalizationModal{{ $appointment->id }}"><span><i class="bx bx-plus"></i></span></button>
                                <!-- Create  Lab Modal -->
                                <div class="modal fade" id="createHospitalizationModal{{ $appointment->id }}" tabindex="-1" aria-labelledby="createHospitalizationModalLabel{{ $appointment->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createHospitalizationModalLabel{{ $appointment->id }}">{{localize('global.hospitalize_patient')}}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('hospitalizations.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" id="patient_id{{ $appointment->patient_id }}" name="patient_id" value="{{ $appointment->patient_id }}">
                                                    <input type="hidden" id="appointment_id{{ $appointment->id }}" name="appointment_id" value="{{ $appointment->id }}">
                                                    <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id" value="{{ auth()->user()->id }}">



                                                    <div class="form-group">

                                                        <div class="form-group">
                                                            <label for="reason{{ $appointment->id }}">{{localize('global.reason')}}</label>
                                                            <textarea class="form-control" id="reason{{ $appointment->id }}" name="reason" rows="3"></textarea>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="remarks{{ $appointment->id }}">{{localize('global.remarks')}}</label>
                                                            <textarea class="form-control" id="remarks{{ $appointment->id }}" name="remarks" rows="3"></textarea>
                                                        </div>


                                                        <label for="room_id{{ $appointment->id }}">{{localize('global.rooms')}}</label>
                                                        <select class="form-control select2" name="room_id">
                                                            <option value="">{{ localize('global.select') }}</option>
                                                            @foreach($rooms as $value)
                                                                <option value="{{ $value->id }}"
                                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                {{ $value->name }}

                                                            </option>
                                                            @endforeach
                                                        </select>

                                                        <label for="bed_id{{ $appointment->id }}">{{localize('global.beds')}}</label>
                                                        <select class="form-control select2" name="bed_id">
                                                            <option value="">{{ localize('global.select') }}</option>
                                                            @foreach($beds as $value)
                                                                <option value="{{ $value->id }}"
                                                                    {{ old('number') == $value->id ? 'selected' : '' }}>
                                                                {{ $value->number }}

                                                            </option>
                                                            @endforeach
                                                        </select>
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
                                <!-- End Create Lab Modal -->
                        <div class="col-md-12 mt-4">




                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{localize('global.number')}}</th>
                                    <th>{{localize('global.reason')}}</th>
                                    <th>{{localize('global.remarks')}}</th>
                                    <th>{{localize('global.room')}}</th>
                                    <th>{{localize('global.bed')}}</th>
                                    <th>{{localize('global.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($appointment->hospitalization as $hospitalization)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$hospitalization->reason}}</td>
                                    <td>
                                        {{$hospitalization->remarks}}
                                    </td>
                                    <td>
                                        {{$hospitalization->room->name}}
                                    </td>
                                    <td>
                                        {{$hospitalization->bed->number}}
                                    </td>
                                    <td>
                                        <a href="{{route('hospitalizations.edit', $hospitalization->id)}}"><span><i class="bx bx-edit"></i></span></a>
                                        <a href="{{route('hospitalizations.destroy', $hospitalization->id)}}"><span><i class="bx bx-trash text-danger"></i></span></a>

                                    </td>
                                </tr>
                                @empty
                                <div class="container">
                                    <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                        <div class=" badge bg-label-danger mt-4">
                                            {{ localize('global.no_previous_hospitalizations') }}
                                        </div>
                                    </div>
                                </div>
                                @endforelse
                            </tbody>
                        </table>

                        </div>





                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Get the add button and prescription input container
    const addButton = document.getElementById('addPrescriptionInput');
    const prescriptionContainer = document.getElementById('prescription-input-container');

    // Add click event listener to the add button
    addButton.addEventListener('click', function() {
        // Create a new row div
        const newRow = document.createElement('div');
        newRow.className = 'row';

        // Create the description input field
        const descriptionInput = document.createElement('input');
        descriptionInput.type = 'text';
        descriptionInput.className = 'form-control';
        descriptionInput.name = 'description[]';
        descriptionInput.dir = 'ltr';
        descriptionInput.placeholder = 'Enter name';

        // Create the dosage input field
        const dosageInput = document.createElement('input');
        dosageInput.type = 'text';
        dosageInput.className = 'form-control';
        dosageInput.name = 'dosage[]';
        dosageInput.placeholder = 'Dosage';

        // Create the frequency input field
        const frequencyInput = document.createElement('input');
        frequencyInput.type = 'text';
        frequencyInput.className = 'form-control';
        frequencyInput.name = 'frequency[]';
        frequencyInput.placeholder = 'Frequency';

        // Create the amount input field
        const amountInput = document.createElement('input');
        amountInput.type = 'text';
        amountInput.className = 'form-control';
        amountInput.name = 'amount[]';
        amountInput.placeholder = 'Amount';

        // Create the column divs
        const descriptionCol = document.createElement('div');
        descriptionCol.className = 'col-md-4';
        const dosageCol = document.createElement('div');
        dosageCol.className = 'col-md-3';
        const frequencyCol = document.createElement('div');
        frequencyCol.className = 'col-md-3';
        const amountCol = document.createElement('div');
        amountCol.className = 'col-md-2';

        // Append the input fields to their respective column divs
        descriptionCol.appendChild(descriptionInput);
        dosageCol.appendChild(dosageInput);
        frequencyCol.appendChild(frequencyInput);
        amountCol.appendChild(amountInput);

        // Append the column divs to the new row div
        newRow.appendChild(descriptionCol);
        newRow.appendChild(dosageCol);
        newRow.appendChild(frequencyCol);
        newRow.appendChild(amountCol);

        // Append the new row div to the prescription input container
        prescriptionContainer.appendChild(newRow);
    });
</script>
@endsection