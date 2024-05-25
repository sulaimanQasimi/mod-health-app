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
                            <div class="border border-label-primary mb-4">
                                <h5 class="mb-4 p-3 bg-label-primary text-center">
                                    {{ localize('global.appointment_details') }}</h5>

                                <div class="row p-2 text-center">
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                                        <div>
                                            {{ $appointment->patient->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.referred_to') }}</h5>
                                        <div>
                                            {{ $appointment->doctor->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.date') }}</h5>
                                        <div>
                                            {{ $appointment->date }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.time') }}</h5>
                                        <div>
                                            {{ $appointment->time }}
                                        </div>
                                    </div>
                                </div>
                                <div class="row p-4">
                                    <div class="mb-4">
                                        <div class="col-md-12 d-flex justify-content-center">
                                            <h5 class="mb-4 p-1 bg-label-primary mt-4"><i
                                                    class="bx bx-history p-1"></i>{{ localize('global.patient_history') }}
                                            </h5>
                                        </div>
                                        @php
                                            $primaryDiagnoses = $previousDiagnoses->where('type', 0);
                                            $finalDiagnoses = $previousDiagnoses->where('type', 1);
                                        @endphp

                                        <div class="container">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="col-md-12">
                                                            <h5 class="mb-4 p-1 bg-label-warning text-center"><i
                                                                    class="bx bx-popsicle p-1"></i>{{ localize('global.primary_diagnoses') }}
                                                            </h5>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="col-md-12">
                                                            <h5 class="mb-4 p-1 bg-label-success text-center"><i
                                                                    class="bx bx-popsicle p-1"></i>{{ localize('global.final_diagnoses') }}
                                                            </h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        @foreach ($primaryDiagnoses as $diagnose)
                                                            <li class="m-1 p-1">
                                                                <span
                                                                    class="bg-label-warning text-center p-1">{{ $diagnose->created_at->format('Y-m-d') }}</span>
                                                                {{ $diagnose->description }}
                                                            </li>
                                                        @endforeach
                                                    </div>
                                                    <div class="col-md-6">
                                                        @foreach ($finalDiagnoses as $diagnose)
                                                            <li class="m-1 p-1">
                                                                <span
                                                                    class="bg-label-success text-center p-1">{{ $diagnose->created_at->format('Y-m-d') }}</span>
                                                                {{ $diagnose->description }}
                                                            </li>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-check-shield p-1"></i>{{ localize('global.appointment_status') }}

                        </h5>

                        @if ($appointment->is_completed == 0)
                            <div class="d-flex justify-content-center text-center">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#createStatusChangeModal{{ $appointment->id }}"><span><i
                                            class="bx bx-check-shield"></i></span></button>
                            </div>
                        @else
                        <div class="d-flex justify-content-center text-center">
                            <span><i
                                        class="bx bx-check-shield text-success"></i>{{localize('global.appointment_completed')}}</span>
                        </div>
                        @endif

                        <div class="modal fade" id="createStatusChangeModal{{ $appointment->id }}" tabindex="-1"
                            aria-labelledby="createStatusChangeModalLabel{{ $appointment->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createStatusChangeModalLabel{{ $appointment->id }}">
                                            {{ localize('global.make_appointment_completed') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('appointments.changeStatus', $appointment) }}"
                                            method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="is_completed" value="1">

                                            <div class="form-group">

                                                <div class="form-group">
                                                    <label
                                                        for="status_remark{{ $appointment->id }}">{{ localize('global.status_remark') }}</label>
                                                    <textarea class="form-control" id="status_remark{{ $appointment->id }}" name="status_remark" rows="3"></textarea>
                                                </div>

                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>



                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-popsicle p-1"></i>{{ localize('global.diagnose') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#createDiagnoseModal{{ $appointment->id }}"><span><i
                                    class="bx bx-plus"></i></span></button>
                        <!-- Create Diagnose Modal -->
                        <div class="modal fade" id="createDiagnoseModal{{ $appointment->id }}" tabindex="-1"
                            aria-labelledby="createDiagnoseModalLabel{{ $appointment->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createDiagnoseModalLabel{{ $appointment->id }}">
                                            {{ localize('global.add_diagnose') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('diagnoses.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                                name="patient_id" value="{{ $appointment->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                                name="appointment_id" value="{{ $appointment->id }}">
                                            <!-- Add other diagnosis form fields as needed -->
                                            <div class="form-group">
                                                <label
                                                    for="type{{ $appointment->id }}">{{ localize('global.diagnose_type') }}</label>
                                                <select class="form-control select2" name="type">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    <option value="0">{{ localize('global.primary') }}</option>
                                                    <option value="1">{{ localize('global.final') }}</option>

                                                </select>
                                                <label
                                                    for="description{{ $appointment->id }}">{{ localize('global.description') }}</label>
                                                <textarea class="form-control" id="description{{ $appointment->id }}" name="description" rows="3"></textarea>
                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
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
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.description') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($appointment->diagnose as $diagnose)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $diagnose->description }}</td>
                                            <td>
                                                <a href="{{ route('diagnoses.edit', $diagnose->id) }}"><span><i
                                                            class="bx bx-edit"></i></span></a>
                                                <a href="{{ route('diagnoses.destroy', $diagnose->id) }}"><span><i
                                                            class="bx bx-trash text-danger"></i></span></a>

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





                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-notepad p-1"></i>{{ localize('global.prescription') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#createPrescriptionModal{{ $appointment->id }}"><span><i
                                    class="bx bx-plus"></i></span></button>
                        <!-- Create Diagnose Modal -->
                        <div class="modal fade modal-xl" id="createPrescriptionModal{{ $appointment->id }}"
                            tabindex="-1" aria-labelledby="createPrescriptionModalLabel{{ $appointment->id }}"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createPrescriptionModalLabel{{ $appointment->id }}">
                                            {{ localize('global.add_prescription') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('prescriptions.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                                name="patient_id" value="{{ $appointment->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                                name="appointment_id" value="{{ $appointment->id }}">
                                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                                value="{{ auth()->user()->branch_id }}">
                                            <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                                value="{{ auth()->user()->id }}">

                                            <!-- Add other diagnosis form fields as needed -->
                                            <div class="form-group" id="prescription-items">
                                                <label>{{ localize('global.description') }}</label>
                                                <div id="prescription-input-container">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <input type="text" class="form-control mt-2"
                                                                name="description[]" dir="ltr"
                                                                placeholder="Enter name">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="text" class="form-control mt-2"
                                                                name="dosage[]" placeholder="Dosage">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control mt-2"
                                                                name="frequency[]" placeholder="Frequency">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control mt-2"
                                                                name="amount[]" placeholder="Amount">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="text" class="form-control mt-2"
                                                                name="type[]" placeholder="Type">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="hidden" class="form-control mt-2"
                                                                name="is_delivered[]" value="0">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-primary mt-2"
                                                id="addPrescriptionInput"><i
                                                    class="bx bx-plus"></i>{{ localize('global.add_prescription_item') }}</button>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
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
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.type') }}</th>
                                        <th>{{ localize('global.description') }}</th>
                                        <th>{{ localize('global.dosage') }}</th>
                                        <th>{{ localize('global.frequency') }}</th>
                                        <th>{{ localize('global.amount') }}</th>
                                        <th>{{ localize('global.status') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (is_array($appointment->prescription) || is_object($appointment->prescription))
                                        @forelse ($appointment->prescription as $prescription)
                                            @php
                                                $descriptions = is_array($prescription->description)
                                                    ? $prescription->description
                                                    : json_decode($prescription->description, true);
                                                $dosages = is_array($prescription->dosage)
                                                    ? $prescription->dosage
                                                    : json_decode($prescription->dosage, true);
                                                $frequencies = is_array($prescription->frequency)
                                                    ? $prescription->frequency
                                                    : json_decode($prescription->frequency, true);
                                                $amounts = is_array($prescription->amount)
                                                    ? $prescription->amount
                                                    : json_decode($prescription->amount, true);
                                                $types = is_array($prescription->type)
                                                    ? $prescription->type
                                                    : json_decode($prescription->type, true);
                                                $statuses = is_array($prescription->is_delivered)
                                                    ? $prescription->is_delivered
                                                    : json_decode($prescription->is_delivered, true);
                                            @endphp
                                            @foreach ($descriptions as $key => $description)
                                                <tr>
                                                    <td>{{ $loop->parent->iteration }}</td>
                                                    <td>{{ $types[$key] }}</td>
                                                    <td>{{ $description }}</td>
                                                    <td>{{ $dosages[$key] }}</td>
                                                    <td>{{ $frequencies[$key] }}</td>
                                                    <td>{{ $amounts[$key] }}</td>
                                                    <td>
                                                        <span><i
                                                                class="{{ $statuses[$key] == 0 ? 'bx bx-x-circle text-danger' : 'bx bx-check-circle text-success' }}"></i></span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('prescriptions.edit', $prescription->id) }}"><span><i
                                                                    class="bx bx-edit"></i></span></a>
                                                        <a href="{{ route('prescriptions.destroy', $prescription->id) }}"><span><i
                                                                    class="bx bx-trash text-danger"></i></span></a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="5">
                                                    <div class="container">
                                                        <div
                                                            class="col-md-12 d-flex justify-content-center align-items-center">
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
                                                    <div
                                                        class="col-md-12 d-flex justify-content-center align-items-center">
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
                                <form
                                    action="{{ route('prescriptions.print-card', ['appointment' => $appointment->id]) }}"
                                    method="GET" target="_blank">
                                    <button class="btn btn-primary" type="submit"><span
                                            class="bx bx-printer me-1"></span>{{ localize('global.print_prescription') }}</button>
                                </form>
                            </div>
                        </div>



                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-hard-hat p-1"></i>{{ localize('global.checkups') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#createLabModal{{ $appointment->id }}"><span><i
                                    class="bx bx-plus"></i></span></button>
                        <!-- Create  Lab Modal -->
                        <div class="modal fade" id="createLabModal{{ $appointment->id }}" tabindex="-1"
                            aria-labelledby="createLabModalLabel{{ $appointment->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createLabModalLabel{{ $appointment->id }}">
                                            {{ localize('global.add_lab_test') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('lab_tests.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                                name="patient_id" value="{{ $appointment->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                                name="appointment_id" value="{{ $appointment->id }}">
                                            <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                                value="{{ $appointment->doctor->id }}">
                                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                                value="{{ auth()->user()->branch_id }}">
                                            <input type="hidden" id="hospitalization_id{{ $appointment->id }}"
                                                name="hospitalization_id" value="">
                                            <input type="hidden" id="status{{ $appointment->id }}" name="status"
                                                value="0">

                                            <div class="form-group">

                                                <label
                                                    for="lab_type_section{{ $appointment->id }}">{{ localize('global.lab_type_section') }}</label>
                                                <select class="form-control select2" name="lab_type_section"
                                                    id="lab_type_section">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($labTypeSections as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->section }}

                                                        </option>
                                                    @endforeach
                                                </select>

                                                <label
                                                    for="lab_type_id{{ $appointment->id }}">{{ localize('global.lab_type') }}</label>
                                                <select class="form-control select2" name="lab_type_id[]"
                                                    id="lab_type_id" onchange="loadLabTypeTests()">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($labTypes as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->name }}

                                                        </option>
                                                    @endforeach
                                                </select>

                                                <div id="labTypeTestsContainer"></div>
                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
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
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.test_name') }}</th>
                                        <th>{{ localize('global.test_status') }}</th>
                                        <th>{{ localize('global.result') }}</th>
                                        <th>{{ localize('global.result_file') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($appointment->labs as $lab)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $lab->labType->name }}</td>
                                            <td>
                                                @if ($lab->status == '0')
                                                    <span
                                                        class="badge bg-danger">{{ localize('global.not_tested') }}</span>
                                                @else
                                                    <span class="badge bg-success">{{ localize('global.tested') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $lab->result }}</td>
                                            <td>
                                                @isset($lab->result_file)
                                                    <a href="{{ asset('storage/' . $lab->result_file) }}" target="_blank">
                                                        <i class="fa fa-file"></i> {{ localize('global.file') }}
                                                    </a>
                                                @endisset

                                            </td>
                                            <td>
                                                <a href="{{ route('lab_tests.edit', $lab->id) }}"><span><i
                                                            class="bx bx-edit"></i></span></a>
                                                <a href="{{ route('lab_tests.destroy', $lab->id) }}"><span><i
                                                            class="bx bx-trash text-danger"></i></span></a>

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
                                <form action="{{ route('lab_tests.print-card', ['appointment' => $appointment->id]) }}"
                                    method="GET" target="_blank">
                                    <button class="btn btn-primary" type="submit"><span
                                            class="bx bx-printer me-1"></span>{{ localize('global.print_test_ticket') }}</button>
                                </form>
                            </div>

                            <div class="col-md-12 d-flex justify-content-center">
                                <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                        class="bx bx-hard-hat p-1"></i>{{ localize('global.hospitalization_checkups') }}
                                </h5>
                            </div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.test_name') }}</th>
                                        <th>{{ localize('global.test_status') }}</th>
                                        <th>{{ localize('global.result') }}</th>
                                        <th>{{ localize('global.result_file') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($appointment->hospitalization as $single_hospitalization)
                                        @forelse ($single_hospitalization->labs as $lab)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $lab->labType->name }}</td>
                                                <td>
                                                    @if ($lab->status == '0')
                                                        <span
                                                            class="badge bg-danger">{{ localize('global.not_tested') }}</span>
                                                    @else
                                                        <span
                                                            class="badge bg-success">{{ localize('global.tested') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $lab->result }}</td>
                                                <td>
                                                    @isset($lab->result_file)
                                                        <a href="{{ asset('storage/' . $lab->result_file) }}"
                                                            target="_blank">
                                                            <i class="fa fa-file"></i> {{ localize('global.file') }}
                                                        </a>
                                                    @endisset

                                                </td>
                                                <td>
                                                    {{-- <a href="{{ route('lab_tests.edit', $lab->id) }}"><span><i
                                                            class="bx bx-edit"></i></span></a>
                                                <a href="{{ route('lab_tests.destroy', $lab->id) }}"><span><i
                                                            class="bx bx-trash text-danger"></i></span></a> --}}

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
                                    @endforeach

                                </tbody>
                            </table>

                        </div>




                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-chat p-1"></i>{{ localize('global.consultations') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#createConsultationModal{{ $appointment->id }}"><span><i
                                    class="bx bx-plus"></i></span></button>
                        <!-- Create  Lab Modal -->
                        <div class="modal fade" id="createConsultationModal{{ $appointment->id }}" tabindex="-1"
                            aria-labelledby="createConsultationModalLabel{{ $appointment->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createConsultationModalLabel{{ $appointment->id }}">
                                            {{ localize('global.add_consultation') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('consultations.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                                name="patient_id" value="{{ $appointment->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                                name="appointment_id" value="{{ $appointment->id }}">
                                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                                value="{{ auth()->user()->branch_id }}">

                                            <div class="form-group">

                                                <label
                                                    for="description{{ $appointment->id }}">{{ localize('global.description') }}</label>
                                                <input type="text" class="form-control" name="title">

                                                <label
                                                    for="branch{{ $appointment->id }}">{{ localize('global.branch') }}</label>
                                                <select class="form-control select2" name="branch" id="branch">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($branches as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->name }}

                                                        </option>
                                                    @endforeach
                                                </select>

                                                <label
                                                    for="doctor_id{{ $appointment->id }}">{{ localize('global.doctors') }}</label>
                                                <select class="form-control select2" name="doctor_id[]" id="doctor_id"
                                                    multiple>
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($doctors as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->name_en }}

                                                        </option>
                                                    @endforeach
                                                </select>

                                                <div class="mb-3">
                                                    <label for="date">{{ localize('global.date') }}</label>
                                                    <input type="date" class="form-control" name="date" />
                                                </div>
                                                <div class="mb-3">
                                                    <label for="time">{{ localize('global.time') }}</label>
                                                    <input type="time" class="form-control" name="time" />
                                                </div>

                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
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
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.title') }}</th>
                                        <th>{{ localize('global.doctors') }}</th>
                                        <th>{{ localize('global.result') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($appointment->consultations as $consultation)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $consultation->title }}</td>
                                            <td>
                                                {{ $consultation->doctors }}
                                            </td>
                                            <td>
                                                {{ $consultation->result }}
                                            </td>
                                            <td>
                                                <a href="{{ route('consultations.edit', $consultation->id) }}"><span><i
                                                            class="bx bx-edit"></i></span></a>
                                                <a href="{{ route('consultations.destroy', $consultation->id) }}"><span><i
                                                            class="bx bx-trash text-danger"></i></span></a>
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
                        <div class="col-md-12 d-flex justify-content-center">
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-chat p-1"></i>{{ localize('global.related_comments') }}</h5>
                        </div>
                        <div class="container">
                            <div class="col-md-12">
                                <div class="row">

                                    @foreach ($appointment->consultations as $consultation)
                                        @forelse($consultation->comments as $comment)
                                            <div class="col-md-2">
                                                <i class="bx bx-check-circle text-success"></i>
                                                <span
                                                    class="bg-label-primary p-1 m-1">{{ $comment->doctor->name }}</span>
                                            </div>
                                            <div class="col-md-10" style="text-align: justify;">
                                                {{ $comment->comment }}
                                            </div>
                                            <div class="white-space">
                                                <hr>
                                            </div>
                                        @empty
                                            <div class="container">
                                                <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                    <div class="p-2 bg-label-danger mt-4">
                                                        {{ localize('global.no_comments_yet') }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforelse
                                    @endforeach
                                </div>
                            </div>
                        </div>



                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-bed p-1"></i>{{ localize('global.hospitalize') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#createHospitalizationModal{{ $appointment->id }}"><span><i
                                    class="bx bx-plus"></i></span></button>
                        <!-- Create  Lab Modal -->
                        <div class="modal fade" id="createHospitalizationModal{{ $appointment->id }}" tabindex="-1"
                            aria-labelledby="createHospitalizationModalLabel{{ $appointment->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"
                                            id="createHospitalizationModalLabel{{ $appointment->id }}">
                                            {{ localize('global.hospitalize_patient') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('hospitalizations.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                                name="patient_id" value="{{ $appointment->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                                name="appointment_id" value="{{ $appointment->id }}">
                                            <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                                value="{{ auth()->user()->id }}">
                                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                                value="{{ auth()->user()->branch_id }}">
                                            <input type="hidden" id="is_discharged{{ $appointment->id }}"
                                                name="is_discharged" value="0">

                                            <div class="form-group">

                                                <div class="form-group">
                                                    <label
                                                        for="reason{{ $appointment->id }}">{{ localize('global.reason') }}</label>
                                                    <textarea class="form-control" id="reason{{ $appointment->id }}" name="reason" rows="3"></textarea>
                                                </div>

                                                <div class="form-group">
                                                    <label
                                                        for="remarks{{ $appointment->id }}">{{ localize('global.remarks') }}</label>
                                                    <textarea class="form-control" id="remarks{{ $appointment->id }}" name="remarks" rows="3"></textarea>
                                                </div>


                                                <label
                                                    for="room_id{{ $appointment->id }}">{{ localize('global.rooms') }}</label>
                                                <select class="form-control select2" name="room_id">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($rooms as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->name }}

                                                        </option>
                                                    @endforeach
                                                </select>

                                                <label
                                                    for="bed_id{{ $appointment->id }}">{{ localize('global.beds') }}</label>
                                                <select class="form-control select2" name="bed_id">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($beds as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('number') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->number }}

                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
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
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.reason') }}</th>
                                        <th>{{ localize('global.remarks') }}</th>
                                        <th>{{ localize('global.room') }}</th>
                                        <th>{{ localize('global.bed') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($appointment->hospitalization as $hospitalization)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $hospitalization->reason }}</td>
                                            <td>
                                                {{ $hospitalization->remarks }}
                                            </td>
                                            <td>
                                                {{ $hospitalization->room->name }}
                                            </td>
                                            <td>
                                                {{ $hospitalization->bed->number }}
                                            </td>
                                            <td>
                                                <a href="{{ route('hospitalizations.edit', $hospitalization->id) }}"><span><i
                                                            class="bx bx-edit"></i></span></a>
                                                <a href="{{ route('hospitalizations.destroy', $hospitalization->id) }}"><span><i
                                                            class="bx bx-trash text-danger"></i></span></a>

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
                        <div class="col-md-12 d-flex justify-content-center">
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-glasses p-1"></i>{{ localize('global.related_visits') }}</h5>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.description') }}</th>
                                    <th>{{ localize('global.by') }}</th>
                                    <th>{{ localize('global.visit_date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($appointment->hospitalization as $single_hospitaliztion)
                                    @forelse($single_hospitaliztion->visits as $visit)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $visit->description }}</td>
                                            <td>{{ $visit->doctor->name }}</td>
                                            <td>
                                                {{ $visit->created_at }}
                                            </td>
                                        </tr>
                                    @empty
                                        <div class="container">
                                            <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                <div class=" badge bg-label-danger mt-4">
                                                    {{ localize('global.no_previous_visits') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                @endforeach
                            </tbody>
                        </table>


                        {{-- To anasthesia --}}

                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-first-aid p-1"></i>{{ localize('global.refere_to_anasthesia') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#createAnasthesiaModal{{ $appointment->id }}"><span><i
                                    class="bx bx-plus"></i></span></button>
                        <!-- Create  Lab Modal -->
                        <div class="modal fade" id="createAnasthesiaModal{{ $appointment->id }}" tabindex="-1"
                            aria-labelledby="createAnasthesiaModalLabel{{ $appointment->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createAnasthesiaModalLabel{{ $appointment->id }}">
                                            {{ localize('global.refere_to_anasthesia') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('anesthesias.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                                name="patient_id" value="{{ $appointment->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                                name="appointment_id" value="{{ $appointment->id }}">
                                            <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                                value="{{ auth()->user()->id }}">
                                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                                value="{{ auth()->user()->branch_id }}">

                                            <div class="form-group">

                                                <div class="form-group">
                                                    <label
                                                        for="plan{{ $appointment->id }}">{{ localize('global.plan') }}</label>
                                                    <textarea class="form-control" id="plan{{ $appointment->id }}" name="plan" rows="3"></textarea>
                                                </div>

                                                <label
                                                    for="operation_doctor_id{{ $appointment->id }}">{{ localize('global.doctors') }}</label>
                                                <select class="form-control select2" name="operation_doctor_id[]"
                                                    id="operation_doctor_id" multiple>
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($operation_doctors as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->name }}

                                                        </option>
                                                    @endforeach
                                                </select>

                                                <div class="form-group">
                                                    <label
                                                        for="other_problems{{ $appointment->id }}">{{ localize('global.other_problems') }}</label>
                                                    <textarea class="form-control" id="other_problems{{ $appointment->id }}" name="other_problems" rows="3"></textarea>
                                                </div>


                                                <label
                                                    for="operation_type_id{{ $appointment->id }}">{{ localize('global.operation_type') }}</label>
                                                <select class="form-control select2" name="operation_type_id">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($operationTypes as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->name }}

                                                        </option>
                                                    @endforeach
                                                </select>

                                                <div class="mb-3">
                                                    <label for="date">{{ localize('global.date') }}</label>
                                                    <input type="date" class="form-control" name="date" />
                                                </div>
                                                <div class="mb-3">
                                                    <label for="time">{{ localize('global.time') }}</label>
                                                    <input type="time" class="form-control" name="time" />
                                                </div>
                                                <div class="mb-3">
                                                    <label
                                                        for="planned_duration">{{ localize('global.planned_duration') }}</label>
                                                    <input type="text" class="form-control" name="planned_duration" />
                                                </div>
                                                <div class="mb-3">
                                                    <label
                                                        for="position_on_bed">{{ localize('global.position_on_bed') }}</label>
                                                    <input type="text" class="form-control" name="position_on_bed" />
                                                </div>
                                                <div class="mb-3">
                                                    <label
                                                        for="estimated_blood_waste">{{ localize('global.estimated_blood_waste') }}</label>
                                                    <input type="text" class="form-control"
                                                        name="estimated_blood_waste" />
                                                </div>


                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
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
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.operation_type') }}</th>
                                        <th>{{ localize('global.patient_name') }}</th>
                                        <th>{{ localize('global.status') }}</th>
                                        <th>{{ localize('global.date') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($appointment->unapproved_anesthesias as $anesthesia)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $anesthesia->operationType->name }}</td>
                                            <td>
                                                {{ $anesthesia->patient->name }}
                                            </td>
                                            <td>
                                                @if ($anesthesia->status == '0')
                                                    <span class="bx bx-x-circle text-danger"></span>
                                                @else
                                                    <span class="bx bx-check-circle text-success"></span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $anesthesia->date }}
                                            </td>
                                            <td>
                                                <a href="{{ route('anesthesias.edit', $anesthesia->id) }}"><span><i
                                                            class="bx bx-edit"></i></span></a>
                                                <a href="{{ route('anesthesias.destroy', $anesthesia->id) }}"><span><i
                                                            class="bx bx-trash text-danger"></i></span></a>

                                            </td>
                                        </tr>
                                    @empty
                                        <div class="container">
                                            <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                <div class=" badge bg-label-danger mt-4">
                                                    {{ localize('global.not_referred_to_anesthesia') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-cut p-1"></i>{{ localize('global.operations') }}</h5>

                        <div class="col-md-12 mt-4">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.operation_type') }}</th>
                                        <th>{{ localize('global.patient_name') }}</th>
                                        <th>{{ localize('global.status') }}</th>
                                        <th>{{ localize('global.date') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($appointment->approved_anesthesias as $anesthesia)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $anesthesia->operationType->name }}</td>
                                            <td>
                                                {{ $anesthesia->patient->name }}
                                            </td>
                                            <td>
                                                @if ($anesthesia->status == '0')
                                                    <span class="bx bx-x-circle text-danger"></span>
                                                @else
                                                    <span class="bx bx-check-circle text-success"></span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $anesthesia->date }}
                                            </td>
                                            <td>
                                                <a href="{{ route('anesthesias.edit', $anesthesia->id) }}"><span><i
                                                            class="bx bx-edit"></i></span></a>
                                                <a href="{{ route('anesthesias.destroy', $anesthesia->id) }}"><span><i
                                                            class="bx bx-trash text-danger"></i></span></a>

                                            </td>
                                        </tr>
                                    @empty
                                        <div class="container">
                                            <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                <div class=" badge bg-label-danger mt-4">
                                                    {{ localize('global.not_referred_to_operation') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>



                        {{-- icu starts here  --}}
                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-tv p-1"></i>{{ localize('global.refere_to_icu') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#createICUModal{{ $appointment->id }}"><span><i
                                    class="bx bx-plus"></i></span></button>
                        <!-- Create  Lab Modal -->
                        <div class="modal fade" id="createICUModal{{ $appointment->id }}" tabindex="-1"
                            aria-labelledby="createICUModalLabel{{ $appointment->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createICUModalLabel{{ $appointment->id }}">
                                            {{ localize('global.refere_to_icu') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('icus.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                                name="patient_id" value="{{ $appointment->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                                name="appointment_id" value="{{ $appointment->id }}">
                                            <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                                value="{{ auth()->user()->id }}">
                                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                                value="{{ auth()->user()->branch_id }}">

                                            <div class="form-group">

                                                <div class="form-group">
                                                    <label
                                                        for="description{{ $appointment->id }}">{{ localize('global.description') }}</label>
                                                    <textarea class="form-control" id="description{{ $appointment->id }}" name="description" rows="3"></textarea>
                                                </div>
                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
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
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.patient_name') }}</th>
                                        <th>{{ localize('global.description') }}</th>
                                        <th>{{ localize('global.date') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($appointment->icu as $icu)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                {{ $icu->patient->name }}
                                            </td>
                                            <td>
                                                {{ $icu->description }}
                                            </td>
                                            <td>
                                                {{ $icu->created_at }}
                                            </td>
                                            <td>
                                                <a href="{{ route('icus.edit', $icu->id) }}"><span><i
                                                            class="bx bx-edit"></i></span></a>
                                                <a href="{{ route('icus.destroy', $icu->id) }}"><span><i
                                                            class="bx bx-trash text-danger"></i></span></a>

                                            </td>
                                        </tr>
                                    @empty
                                        <div class="container">
                                            <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                <div class=" badge bg-label-danger mt-4">
                                                    {{ localize('global.not_referred_to_icu') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-12 d-flex justify-content-center">
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-glasses p-1"></i>{{ localize('global.related_icu_visits') }}</h5>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.description') }}</th>
                                    <th>{{ localize('global.by') }}</th>
                                    <th>{{ localize('global.visit_date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($appointment->icu as $icu)
                                    @forelse($icu->visits as $visit)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $visit->description }}</td>
                                            <td>{{ $visit->doctor->name }}</td>
                                            <td>
                                                {{ $visit->created_at }}
                                            </td>
                                        </tr>
                                    @empty
                                        <div class="container">
                                            <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                <div class=" badge bg-label-danger mt-4">
                                                    {{ localize('global.no_previous_visits') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                @endforeach
                            </tbody>
                        </table>




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
            descriptionInput.className = 'form-control mt-2';
            descriptionInput.name = 'description[]';
            descriptionInput.dir = 'ltr';
            descriptionInput.placeholder = 'Enter name';

            // Create the dosage input field
            const dosageInput = document.createElement('input');
            dosageInput.type = 'text';
            dosageInput.className = 'form-control mt-2';
            dosageInput.name = 'dosage[]';
            dosageInput.placeholder = 'Dosage';

            // Create the frequency input field
            const frequencyInput = document.createElement('input');
            frequencyInput.type = 'text';
            frequencyInput.className = 'form-control mt-2';
            frequencyInput.name = 'frequency[]';
            frequencyInput.placeholder = 'Frequency';

            // Create the amount input field
            const amountInput = document.createElement('input');
            amountInput.type = 'text';
            amountInput.className = 'form-control mt-2';
            amountInput.name = 'amount[]';
            amountInput.placeholder = 'Amount';

            // Create the amount input field
            const typeInput = document.createElement('input');
            typeInput.type = 'text';
            typeInput.className = 'form-control mt-2';
            typeInput.name = 'type[]';
            typeInput.placeholder = 'Type';

            // Create the delivery input field
            const deliveryInput = document.createElement('input');
            deliveryInput.type = 'hidden';
            deliveryInput.className = 'form-control mt-2';
            deliveryInput.name = 'is_delivered[]';
            deliveryInput.value = 0;

            // Create the column divs
            const descriptionCol = document.createElement('div');
            descriptionCol.className = 'col-md-3';
            const dosageCol = document.createElement('div');
            dosageCol.className = 'col-md-3';
            const frequencyCol = document.createElement('div');
            frequencyCol.className = 'col-md-2';
            const amountCol = document.createElement('div');
            amountCol.className = 'col-md-2';
            const typeCol = document.createElement('div');
            typeCol.className = 'col-md-2';

            const deliveryCol = document.createElement('div');
            deliveryCol.className = 'col-md-2';


            // Append the input fields to their respective column divs
            descriptionCol.appendChild(descriptionInput);
            dosageCol.appendChild(dosageInput);
            frequencyCol.appendChild(frequencyInput);
            amountCol.appendChild(amountInput);
            typeCol.appendChild(typeInput);
            deliveryCol.appendChild(deliveryInput);

            // Append the column divs to the new row div
            newRow.appendChild(descriptionCol);
            newRow.appendChild(dosageCol);
            newRow.appendChild(frequencyCol);
            newRow.appendChild(amountCol);
            newRow.appendChild(typeCol);
            newRow.appendChild(deliveryCol);

            // Append the new row div to the prescription input container
            prescriptionContainer.appendChild(newRow);
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#lab_type_section').on('change', function() {
                var labSectionID = $(this).val();
                if (labSectionID !== '') {
                    $.ajax({
                        url: '/get_labTypes/' + labSectionID,
                        type: 'GET',
                        success: function(response) {

                            $('#lab_type_id').html(response);
                        }
                    })
                }
            })

            $('#branch').on('change', function() {
                var branchID = $(this).val();
                if (branchID !== '') {
                    $.ajax({
                        url: '/get_branch_doctors/' + branchID,
                        type: 'GET',
                        success: function(response) {

                            $('#doctor_id').html(response);
                        }
                    })
                }
            })
        })
    </script>

    <script>
        function loadLabTypeTests() {
            var labTypeId = document.getElementById('lab_type_id').value;
            var labTypeTestsContainer = document.getElementById('labTypeTestsContainer');
            labTypeTestsContainer.innerHTML = ''; // Clear previous checkboxes

            // Make an AJAX request to fetch the lab type tests based on the selected lab_type_id
            fetch('/lab-tests/' + labTypeId)
                .then(response => response.json())
                .then(data => {
                    // Create checkboxes for each lab type test
                    data.forEach(function(test) {
                        var checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.name = 'lab_type_id[]'; // Use an array to submit multiple values
                        checkbox.value = test.id;

                        // Update the lab_type_id value when a checkbox is checked/unchecked
                        checkbox.addEventListener('change', function() {
                            if (this.checked) {
                                // Append the test id to the lab_type_id value
                                document.getElementById('lab_type_id').value += ',' + this.value;
                            } else {
                                // Remove the test id from the lab_type_id value
                                var labTypeIdValue = document.getElementById('lab_type_id').value;
                                labTypeIdValue = labTypeIdValue.replace(',' + this.value, '');
                                labTypeIdValue = labTypeIdValue.replace(this.value + ',', '');
                                labTypeIdValue = labTypeIdValue.replace(this.value, '');
                                document.getElementById('lab_type_id').value = labTypeIdValue;
                            }
                        });

                        // Create a label for the checkbox
                        var label = document.createElement('label');
                        label.appendChild(checkbox);
                        label.appendChild(document.createTextNode(test.name));

                        // Append the checkbox to the labTypeTestsContainer
                        labTypeTestsContainer.appendChild(label);
                    });
                })
                .catch(error => {
                    console.log(error);
                });
        }
    </script>
@endsection
