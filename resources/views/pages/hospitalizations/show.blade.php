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
                        <h5 class="mb-0">{{ localize('global.hospitalization_details') }}</h5>
                        <div class="pt-3 pt-md-0 text-end">
                            <a class="btn btn-danger" href="{{ url()->previous() }}" type="button">
                                <span class="text-white"> <span
                                        class="d-none d-sm-inline-block  ">{{ localize('global.back') }}</span></span>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="col-md-12">
                            <div class="border border-label-primary mb-4 text-center">
                                <h5 class="mb-4 p-3 bg-label-primary text-center">
                                    {{ localize('global.hospitalization_details') }}</h5>

                                <div class="row p-2">
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                                        <div>
                                            {{ $hospitalization->patient->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.referred_to') }}</h5>
                                        <div>
                                            {{ $hospitalization->doctor->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.date') }}</h5>
                                        <div>
                                            {{ $hospitalization->created_at->format('Y-m-d') }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.time') }}</h5>
                                        <div>
                                            {{ $hospitalization->created_at->format('H:m:s') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="row text-start m-4">
                                    <div class="col-md-12 mt-2 mb-2">
                                        <h5 class="mb-2">{{ localize('global.reason') }}</h5>
                                        <div>
                                            {{ $hospitalization->reason }}
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-2 mb-2">
                                        <h5 class="mb-2">{{ localize('global.remarks') }}</h5>
                                        <div>
                                            {{ $hospitalization->remarks }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-glasses p-1"></i>{{ localize('global.visits') }}</h5>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#createVisitModal{{ $hospitalization->id }}"><span><i
                                        class="bx bx-plus"></i></span></button>
                            <!-- Create visit Modal -->
                            <div class="modal fade" id="createVisitModal{{ $hospitalization->id }}" tabindex="-1"
                                aria-labelledby="createVisitModalLabel{{ $hospitalization->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createVisitModalLabel{{ $hospitalization->id }}">
                                                {{ localize('global.add_visit') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('visits.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" id="patient_id{{ $hospitalization->patient_id }}"
                                                    name="patient_id" value="{{ $hospitalization->patient_id }}">
                                                <input type="hidden" id="hospitalization_id{{ $hospitalization->id }}"
                                                    name="hospitalization_id" value="{{ $hospitalization->id }}">
                                                <input type="hidden" id="doctor_id{{ $hospitalization->id }}"
                                                    name="doctor_id" value="{{ $hospitalization->doctor->id }}">
                                                <!-- Add other diagnosis form fields as needed -->
                                                <div class="form-group">
                                                    <label
                                                        for="description{{ $hospitalization->id }}">{{ localize('global.description') }}</label>
                                                    <textarea class="form-control" id="description{{ $hospitalization->id }}" name="description" rows="3"></textarea>
                                                </div>
                                                <h5 class="mt-2">{{ localize('global.vital_signs') }}</h5>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label
                                                                for="bp{{ $hospitalization->id }}">{{ localize('global.bp') }}</label>
                                                            <input type="text" class="form-control" name="bp" />
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label
                                                                for="pr{{ $hospitalization->id }}">{{ localize('global.pr') }}</label>
                                                            <input type="text" class="form-control" name="pr" />
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label
                                                                for="rr{{ $hospitalization->id }}">{{ localize('global.rr') }}</label>
                                                            <input type="text" class="form-control" name="rr" />
                                                        </div>
                                                    </div>
                                                    <div class="row mt-1 mb-1">
                                                        <div class="col-md-4">
                                                            <label
                                                                for="t{{ $hospitalization->id }}">{{ localize('global.t') }}</label>
                                                            <input type="text" class="form-control" name="t" />
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label
                                                                for="spo2{{ $hospitalization->id }}">{{ localize('global.spo2') }}</label>
                                                            <input type="text" class="form-control" name="spo2" />
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label
                                                                for="pain{{ $hospitalization->id }}">{{ localize('global.pain') }}</label>
                                                            <input type="text" class="form-control" name="pain" />
                                                        </div>
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
                            <!-- End Create visit Modal -->
                            <div class="col-md-12 mt-4">




                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.number') }}</th>
                                            <th>{{ localize('global.description') }}</th>
                                            <th>{{ localize('global.by') }}</th>
                                            <th>{{ localize('global.created_at') }}</th>
                                            <th>{{ localize('global.vital_signs') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($hospitalization->visits as $visit)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $visit->description }}</td>
                                                <td>{{ $visit->doctor->name }}</td>
                                                <td>{{ $visit->created_at }}</td>
                                                <td>
                                                    <span class="badge bg-primary">{{ localize('global.bp') }}</span>
                                                    {{ $visit->bp }}
                                                    <span class="badge bg-primary">{{ localize('global.pr') }}</span>
                                                    {{ $visit->pr }}
                                                    <span class="badge bg-primary">{{ localize('global.rr') }}</span>
                                                    {{ $visit->rr }}
                                                    <span class="badge bg-primary">{{ localize('global.t') }}</span>
                                                    {{ $visit->t }}
                                                    <span class="badge bg-primary">{{ localize('global.spo2') }}</span>
                                                    {{ $visit->spo2 }}
                                                    <span class="badge bg-primary">{{ localize('global.pain') }}</span>
                                                    {{ $visit->pain }}
                                                </td>
                                                <td>
                                                    <a href="{{ route('visits.edit', $visit->id) }}"><span><i
                                                                class="bx bx-edit"></i></span></a>
                                                    <a href="{{ route('visits.destroy', $visit->id) }}"><span><i
                                                                class="bx bx-trash text-danger"></i></span></a>

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
                                    </tbody>
                                </table>

                            </div>


                            {{-- lab tests from hospitalization --}}

                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-hard-hat p-1"></i>{{ localize('global.hospitalization_checkups') }}</h5>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#createLabModal{{ $hospitalization->id }}"><span><i
                                        class="bx bx-plus"></i></span></button>
                            <!-- Create  Lab Modal -->
                            <div class="modal fade" id="createLabModal{{ $hospitalization->id }}" tabindex="-1"
                                aria-labelledby="createLabModalLabel{{ $hospitalization->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createLabModalLabel{{ $hospitalization->id }}">
                                                {{ localize('global.add_lab_test') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('lab_tests.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" id="patient_id{{ $hospitalization->patient_id }}"
                                                    name="patient_id" value="{{ $hospitalization->patient_id }}">
                                                <input type="hidden"
                                                    id="appointment_id{{ $hospitalization->appointment->id }}"
                                                    name="appointment_id" value="{{ $hospitalization->id }}">
                                                <input type="hidden" id="doctor_id{{ $hospitalization->id }}"
                                                    name="doctor_id" value="{{ $hospitalization->doctor->id }}">
                                                <input type="hidden" id="branch_id{{ $hospitalization->id }}"
                                                    name="branch_id" value="{{ auth()->user()->branch_id }}">
                                                <input type="hidden" id="status{{ $hospitalization->id }}"
                                                    name="status" value="0">
                                                <input type="hidden" id="hospitalization_id{{ $hospitalization->id }}"
                                                    name="hospitalization_id" value="{{ $hospitalization->id }}">
                                                <div class="form-group">

                                                    <label
                                                        for="lab_type_section{{ $hospitalization->id }}">{{ localize('global.lab_type_section') }}</label>
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
                                                        for="lab_type_id{{ $hospitalization->id }}">{{ localize('global.lab_type') }}</label>
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
                                        @forelse ($hospitalization->labs as $lab)
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
                            </div>

                            {{-- end lab tests from hospitalization --}}
                            {{-- icu starts here  --}}
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-tv p-1"></i>{{ localize('global.refere_to_icu') }}</h5>
                            @if ($hospitalization->is_completed == 0)
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#createICUModal{{ $hospitalization->id }}"><span><i
                                            class="bx bx-plus"></i></span></button>
                            @endif
                            <!-- Create  Lab Modal -->
                            <div class="modal fade" id="createICUModal{{ $hospitalization->id }}" tabindex="-1"
                                aria-labelledby="createICUModalLabel{{ $hospitalization->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createICUModalLabel{{ $hospitalization->id }}">
                                                {{ localize('global.refere_to_icu') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('icus.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" id="patient_id{{ $hospitalization->patient_id }}"
                                                    name="patient_id" value="{{ $hospitalization->patient_id }}">
                                                <input type="hidden" id="appointment_id{{ $hospitalization->id }}"
                                                    name="appointment_id" value="{{ $hospitalization->id }}">
                                                <input type="hidden" id="doctor_id{{ $hospitalization->id }}"
                                                    name="doctor_id" value="{{ auth()->user()->id }}">
                                                <input type="hidden" id="branch_id{{ $hospitalization->id }}"
                                                    name="branch_id" value="{{ auth()->user()->branch_id }}">

                                                <div class="form-group">

                                                    <div class="form-group">
                                                        <label
                                                            for="description{{ $hospitalization->id }}">{{ localize('global.description') }}</label>
                                                        <textarea class="form-control" id="description{{ $hospitalization->id }}" name="description" rows="3"></textarea>
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
                                        @forelse ($hospitalization->icu as $icu)
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
                                    @foreach ($hospitalization->icu as $icu)
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

                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-first-aid p-1"></i>{{ localize('global.refere_to_anasthesia') }}</h5>
                            @if ($hospitalization->is_discharged == 0)
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#createAnasthesiaModal{{ $hospitalization->id }}"><span><i
                                            class="bx bx-plus"></i></span></button>
                            @endif
                            <!-- Create  Lab Modal -->
                            <div class="modal fade modal-xl" id="createAnasthesiaModal{{ $hospitalization->id }}"
                                tabindex="-1" aria-labelledby="createAnasthesiaModalLabel{{ $hospitalization->id }}"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"
                                                id="createAnasthesiaModalLabel{{ $hospitalization->id }}">
                                                {{ localize('global.refere_to_anasthesia') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('anesthesias.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" id="patient_id{{ $hospitalization->patient_id }}"
                                                    name="patient_id" value="{{ $hospitalization->patient_id }}">
                                                <input type="hidden" id="appointment_id{{ $hospitalization->id }}"
                                                    name="appointment_id" value="{{ $hospitalization->id }}">
                                                <input type="hidden" id="doctor_id{{ $hospitalization->id }}"
                                                    name="doctor_id" value="{{ auth()->user()->id }}">
                                                <input type="hidden" id="hospitalization_id{{ $hospitalization->id }}"
                                                    name="hospitalization_id" value="{{ $hospitalization->id }}">
                                                <input type="hidden" id="branch_id{{ $hospitalization->id }}"
                                                    name="branch_id" value="{{ auth()->user()->branch_id }}">

                                                <div class="form-group">

                                                    <div class="form-group">
                                                        <label
                                                            for="plan{{ $hospitalization->id }}">{{ localize('global.plan') }}</label>
                                                        <textarea class="form-control" id="plan{{ $hospitalization->id }}" name="plan" rows="3"></textarea>
                                                    </div>

                                                    <h5 class="mt-2">{{ localize('global.operation_team') }}</h5>
                                                    {{-- <select class="form-control select2" name="operation_doctor_id[]"
                        id="operation_doctor_id" multiple>
                        <option value="">{{ localize('global.select') }}</option>
                        @foreach ($operation_doctors as $value)
                            <option value="{{ $value->id }}"
                                {{ old('name') == $value->id ? 'selected' : '' }}>
                                {{ $value->name }}

                            </option>
                        @endforeach
                    </select> --}}

                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label
                                                                    for="operation_surgion_id{{ $hospitalization->id }}">{{ localize('global.operation_surgion') }}</label>
                                                                <select class="form-control select2"
                                                                    name="operation_surgion_id" id="operation_surgion_id">
                                                                    <option value="">
                                                                        {{ localize('global.select') }}
                                                                    </option>
                                                                    @foreach ($operation_doctors as $value)
                                                                        <option value="{{ $value->id }}"
                                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->name }}

                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label
                                                                    for="operation_assistants_id{{ $hospitalization->id }}">{{ localize('global.operation_assistants') }}</label>
                                                                <select class="form-control select2"
                                                                    name="operation_assistants_id[]"
                                                                    id="operation_assistants_id" multiple>
                                                                    <option value="">
                                                                        {{ localize('global.select') }}
                                                                    </option>
                                                                    @foreach ($operation_doctors as $value)
                                                                        <option value="{{ $value->id }}"
                                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->name }}

                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label
                                                                    for="operation_anesthesia_log_id{{ $hospitalization->id }}">{{ localize('global.anesthesia_log') }}</label>
                                                                <select class="form-control select2"
                                                                    name="operation_anesthesia_log_id"
                                                                    id="operation_anesthesia_log_id">
                                                                    <option value="">
                                                                        {{ localize('global.select') }}
                                                                    </option>
                                                                    @foreach ($operation_doctors as $value)
                                                                        <option value="{{ $value->id }}"
                                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->name }}

                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label
                                                                    for="anesthesist{{ $hospitalization->id }}">{{ localize('global.anesthesist') }}</label>
                                                                <select class="form-control select2"
                                                                    name="operation_anesthesist_id"
                                                                    id="operation_anesthesist_id">
                                                                    <option value="">
                                                                        {{ localize('global.select') }}
                                                                    </option>
                                                                    @foreach ($operation_doctors as $value)
                                                                        <option value="{{ $value->id }}"
                                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->name }}

                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label
                                                                    for="operation_scrub_nurse_id{{ $hospitalization->id }}">{{ localize('global.scrub_nurse') }}</label>
                                                                <select class="form-control select2"
                                                                    name="operation_scrub_nurse_id"
                                                                    id="operation_scrub_nurse_id">
                                                                    <option value="">
                                                                        {{ localize('global.select') }}
                                                                    </option>
                                                                    @foreach ($operation_doctors as $value)
                                                                        <option value="{{ $value->id }}"
                                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->name }}

                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label
                                                                    for="operation_circulation_nurse_id{{ $hospitalization->id }}">{{ localize('global.circulation_nurse') }}</label>
                                                                <select class="form-control select2"
                                                                    name="operation_circulation_nurse_id"
                                                                    id="operation_circulation_nurse_id">
                                                                    <option value="">
                                                                        {{ localize('global.select') }}
                                                                    </option>
                                                                    @foreach ($operation_doctors as $value)
                                                                        <option value="{{ $value->id }}"
                                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->name }}

                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="form-group">
                                                        <label for="other_problems{{ $hospitalization->id }}"
                                                            class="mt-2 mb-2">{{ localize('global.other_problems') }}</label>
                                                        <textarea class="form-control" id="other_problems{{ $hospitalization->id }}" name="other_problems" rows="3"></textarea>
                                                    </div>


                                                    <label for="operation_type_id{{ $hospitalization->id }}"
                                                        class="mt-2 mb-2">{{ localize('global.operation_type') }}</label>
                                                    <select class="form-control select2" name="operation_type_id">
                                                        <option value="">{{ localize('global.select') }}</option>
                                                        @foreach ($operationTypes as $value)
                                                            <option value="{{ $value->id }}"
                                                                {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                {{ $value->name }}

                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <div>
                                                        <label for="date"
                                                            class="mt-2 mb-2">{{ localize('global.date') }}</label>
                                                        <input type="date" class="form-control" name="date" />
                                                    </div>
                                                    <div>
                                                        <label for="time"
                                                            class="mt-2 mb-2">{{ localize('global.time') }}</label>
                                                        <input type="time" class="form-control" name="time" />
                                                    </div>
                                                    <div>
                                                        <label for="planned_duration"
                                                            class="mt-2 mb-2">{{ localize('global.planned_duration') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="planned_duration" />
                                                    </div>
                                                    <div>
                                                        <label for="position_on_bed"
                                                            class="mt-2 mb-2">{{ localize('global.position_on_bed') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="position_on_bed" />
                                                    </div>
                                                    <div>
                                                        <label for="estimated_blood_waste"
                                                            class="mt-2 mb-2">{{ localize('global.estimated_blood_waste') }}</label>
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
                                        @forelse ($hospitalization->anesthesias as $anesthesia)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $anesthesia->operationType->name }}</td>
                                                <td>
                                                    {{ $anesthesia->patient->name }}
                                                </td>
                                                <td>
                                                    @if ($anesthesia->status == 'new')
                                                        <span class="bx bx-plus-circle text-primary"></span>
                                                    @elseif ($anesthesia->status == 'rejected')
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
                                    class="bx bx-walk p-1"></i>{{ localize('global.create_complaint') }}</h5>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#createComplaintModal{{ $hospitalization->id }}"><span><i
                                        class="bx bx-plus"></i></span></button>
                            <!-- Create  Lab Modal -->
                            <div class="modal fade" id="createComplaintModal{{ $hospitalization->id }}" tabindex="-1"
                                aria-labelledby="createComplaintModalLabel{{ $hospitalization->id }}"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"
                                                id="createComplaintModalLabel{{ $hospitalization->id }}">
                                                {{ localize('global.add_complaint') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('complaints.store', $hospitalization) }}"
                                                method="POST">
                                                @csrf

                                                <input type="hidden" id="hospitalization_id{{ $hospitalization->id }}"
                                                    name="hospitalization_id" value="{{ $hospitalization->id }}">
                                                <div class="form-group">
                                                    <label
                                                        for="description{{ $hospitalization->id }}">{{ localize('global.description') }}</label>
                                                    <textarea class="form-control" id="description{{ $hospitalization->id }}" name="description" rows="3"></textarea>

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
                            <div class="col-md-12 mt-4">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.number') }}</th>
                                            <th>{{ localize('global.description') }}</th>
                                            <th>{{ localize('global.date') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($hospitalization->complaints as $complaint)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $complaint->description }}</td>
                                                <td>
                                                    {{ $complaint->created_at }}
                                                </td>



                                            </tr>
                                        @empty
                                            <div class="container">
                                                <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                    <div class=" badge bg-label-danger mt-4">
                                                        {{ localize('global.not_referred_to_complaint') }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- discharge --}}
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-walk p-1"></i>{{ localize('global.discharge_patient') }}</h5>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#createDischargeModal{{ $hospitalization->id }}"><span><i
                                        class="bx bx-plus"></i></span></button>
                            <!-- Create  Lab Modal -->
                            <div class="modal fade" id="createDischargeModal{{ $hospitalization->id }}" tabindex="-1"
                                aria-labelledby="createDischargeModalLabel{{ $hospitalization->id }}"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"
                                                id="createDischargeModalLabel{{ $hospitalization->id }}">
                                                {{ localize('global.add_lab_test') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('hospitalizations.update', $hospitalization) }}"
                                                method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" id="is_discharged{{ $hospitalization->id }}"
                                                    name="is_discharged" value="1">
                                                <div class="form-group">
                                                    <label
                                                        for="discharge_status{{ $hospitalization->id }}">{{ localize('global.discharge_status') }}</label>
                                                    <select class="form-control select2" name="discharge_status">
                                                        <option value="">{{ localize('global.select') }}</option>
                                                        <option value="recovered">{{ localize('global.recovered') }}
                                                        </option>
                                                        <option value="died">{{ localize('global.died') }}</option>
                                                        <option value="moved">{{ localize('global.moved') }}</option>

                                                    </select>
                                                    <label
                                                        for="discharge_remark{{ $hospitalization->id }}">{{ localize('global.discharge_remark') }}</label>
                                                    <textarea class="form-control" id="discharge_remark{{ $hospitalization->id }}" name="discharge_remark"
                                                        rows="3"></textarea>
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
                                {{ $hospitalization->discharge_remark }}
                            </div>
                            {{-- end discharge --}}









                        </div>

                    </div>


                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
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
