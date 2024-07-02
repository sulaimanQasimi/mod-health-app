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
                        <h5 class="mb-0">{{ localize('global.icu_details') }}</h5>
                        <div class="pt-3 pt-md-0 text-end">
                            <a class="btn btn-danger" href="{{ url()->previous() }}" type="button">
                                <span class="text-white"> <span
                                        class="d-none d-sm-inline-block  ">{{ localize('global.back') }}</span></span>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="col-md-12">
                            <div class="border border-label-primary mb-4 text-center p-4">
                                <h5 class="mb-4 p-3 bg-label-primary text-center">{{ localize('global.icu_details') }}</h5>

                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div class="col-md-3">

                                                    <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->name }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.last_name') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->last_name }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.phone') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->phone }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.nid') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->nid }}
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="col-md-3">

                                                    <h5 class="mb-2">{{ localize('global.province') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->province->name_dr }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.district') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->district->name_dr }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.referred_by') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->recipient->name }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.creation_date') }}</h5>
                                                    <div>
                                                        {{ $icu->patient->created_at }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4 card p-1">
                                            <!-- Left side content -->
                                            <div class="row">
                                                <div class="col-md-6 d-flex justify-content-end align-items-center">
                                                    {!! QrCode::size(100)->generate($icu->patient->id) !!}
                                                </div>
                                                <div class="col-md-6 d-flex justify-content-start align-items-center">
                                                    @isset($icu->patient->image)
                                                        <img src="{{ asset($icu->patient->image) }}" alt="Patient Image"
                                                            width="100" height="100">
                                                    @else
                                                        <div class=" badge bg-label-danger mt-4">
                                                            {{ localize('global.no_image') }}
                                                        </div>
                                                    @endisset
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <h5 class="mb-0 p-3 bg-label-primary">{{ localize('global.all_diagnoses') }}</h5>
                            <div class="row p-4">
                                <div class="mb-4">
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

                            <h5 class="mb-0 p-3 bg-label-primary">{{ localize('global.previous_labs') }}</h5>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.test_name') }}</th>
                                        <th>{{ localize('global.test_status') }}</th>
                                        <th>{{ localize('global.result') }}</th>
                                        <th>{{ localize('global.result_file') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($previousLabs as $lab)
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

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if ($icu->status == 'new')
                                <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                        class="bx bx-glasses p-1"></i>{{ localize('global.approve_reject_icu') }}</h5>

                                <div class="row d-flex justify-content-center">


                                    <div class="d-flex justify-content-center p-2">
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                data-bs-target="#createICUApproveModal{{ $icu->id }}"><span><i
                                                        class="bx bx-check"></i>{{ localize('global.approve') }}</span></button>
                                        </div>

                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#createICURejectModal{{ $icu->id }}"><span><i
                                                        class="bx bx-x"></i>{{ localize('global.reject') }}</span></button>
                                        </div>
                                    </div>
                            @endif
                        </div>

                        <div class="modal fade" id="createICUApproveModal{{ $icu->id }}" tabindex="-1"
                            aria-labelledby="createICUApproveModalLabel{{ $icu->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createICUApproveModalLabel{{ $icu->id }}">
                                            {{ localize('global.approve_icu') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('icus.update', $icu) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="approved">

                                            <div class="form-group">

                                                <div class="form-group">
                                                    <label
                                                        for="icu_enterance_note{{ $icu->id }}">{{ localize('global.icu_enterance_note') }}</label>
                                                    <textarea class="form-control" id="icu_enterance_note{{ $icu->id }}" name="icu_enterance_note" rows="3"></textarea>
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

                        <div class="modal fade" id="createICURejectModal{{ $icu->id }}" tabindex="-1"
                            aria-labelledby="createICURejectModalLabel{{ $icu->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createICURejectModalLabel{{ $icu->id }}">
                                            {{ localize('global.reject_icu') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('icus.update', $icu) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="rejected">

                                            <div class="form-group">

                                                <div class="form-group">
                                                    <label
                                                        for="icu_reject_reason{{ $icu->id }}">{{ localize('global.icu_reject_reason') }}</label>
                                                    <textarea class="form-control" id="icu_reject_reason{{ $icu->id }}" name="icu_reject_reason" rows="3"></textarea>
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

                        @if ($icu->status == 'approved')
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-chat p-1"></i>{{ localize('global.consultations') }}</h5>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#createConsultationModal{{ $icu->id }}"><span><i
                                        class="bx bx-plus"></i></span></button>

                            <!-- Create  Lab Modal -->
                            <div class="modal fade" id="createConsultationModal{{ $icu->id }}" tabindex="-1"
                                aria-labelledby="createConsultationModalLabel{{ $icu->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createConsultationModalLabel{{ $icu->id }}">
                                                {{ localize('global.add_consultation') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('consultations.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" id="patient_id{{ $icu->patient_id }}"
                                                    name="patient_id" value="{{ $icu->patient_id }}">
                                                <input type="hidden" id="appointment_id{{ $icu->id }}"
                                                    name="appointment_id" value="{{ $icu->appointment->id }}">
                                                <input type="hidden" id="branch_id{{ $icu->id }}" name="branch_id"
                                                    value="{{ auth()->user()->branch_id }}">
                                                <input type="hidden" id="i_c_u_id{{ $icu->id }}" name="i_c_u_id"
                                                    value="{{ $icu->id }}">
                                                <div class="form-group">

                                                    <label
                                                        for="description{{ $icu->id }}">{{ localize('global.description') }}</label>
                                                    <input type="text" class="form-control" name="title">

                                                    <label
                                                        for="branch{{ $icu->id }}">{{ localize('global.branch') }}</label>
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
                                                        for="department{{ $icu->id }}">{{ localize('global.department') }}</label>
                                                    <select class="form-control select2" name="department_id[]"
                                                        id="department_id">
                                                        <option value="">{{ localize('global.select') }}</option>
                                                        @foreach ($departments as $value)
                                                            <option value="{{ $value->id }}"
                                                                {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                {{ $value->name }}

                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <label
                                                    for="type{{ $icu->id }}">{{ localize('global.type') }}</label>
                                                <select class="form-control select2" name="consultation_type" id="type">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    <option value="0">{{ localize('global.normal') }}</option>
                                                    <option value="1">{{ localize('global.emergency') }}</option>

                                                </select>

                                                    <label
                                                        for="doctor_id{{ $icu->id }}">{{ localize('global.doctors') }}</label>
                                                    <select class="form-control select2" name="doctor_id[]"
                                                        id="doctor_id" multiple>
                                                        <option value="">{{ localize('global.select') }}</option>
                                                        @foreach ($doctors as $value)
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
                                            <th>{{ localize('global.departments') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($icu->consultations as $consultation)
                                            <tr>
                                                <td>
                                                    <div>

                                                        <span
                                                            style="width: 30px; height: 30px; line-height: 30px; border: 2px solid var(--bs-primary); border-radius: 50%; display: inline-block; text-align: center;">{{ $loop->iteration }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ $consultation->title }}</td>
                                                <td>
                                                    @foreach ($consultation->associated_departments as $department)
                                                        <span class="badge bg-primary">
                                                            {{ $department->name }}
                                                        </span>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    <a href="{{ route('consultations.edit', $consultation->id) }}"><span><i
                                                                class="bx bx-edit"></i></span></a>
                                                    <a href="{{ route('consultations.destroy', $consultation->id) }}"><span><i
                                                                class="bx bx-trash text-danger"></i></span></a>
                                                </td>
                                            </tr>
                                            @if ($consultation->comments->isNotEmpty())
                                                <tr>
                                                    <td colspan="4">
                                                        <div class="row">
                                                            <div class="col-md-12 d-flex justify-content-center">
                                                                <h5 class="mb-2 p-2 bg-label-primary mt-2"><i
                                                                        class="bx bx-chat p-1"></i>{{ localize('global.related_comments') }}
                                                                </h5>
                                                            </div>
                                                        </div>


                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">
                                                        @foreach ($consultation->comments as $comment)
                                                            <div class="row mb-2">
                                                                <div class="col-md-2">
                                                                    <i class="bx bx-check-circle text-success"></i>
                                                                    <span
                                                                        class="bg-label-primary p-1 m-1">{{ $comment->doctor->name }}</span>
                                                                </div>
                                                                <div class="col-md-10" style="text-align: justify;">
                                                                    {{ $comment->comment }}
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    <div class="badge bg-label-danger mt-4">
                                                        {{ localize('global.no_previous_consultations') }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>





                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-glasses p-1"></i>{{ localize('global.visits') }}</h5>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#createVisitModal{{ $icu->id }}"><span><i
                                        class="bx bx-plus"></i></span></button>

                        <!-- Create visit Modal -->
                        <div class="modal fade" id="createVisitModal{{ $icu->id }}" tabindex="-1"
                            aria-labelledby="createVisitModalLabel{{ $icu->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createVisitModalLabel{{ $icu->id }}">
                                            {{ localize('global.add_visit') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('visits.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $icu->patient_id }}"
                                                name="patient_id" value="{{ $icu->patient_id }}">
                                            <input type="hidden" id="i_c_u_id{{ $icu->id }}"
                                                name="i_c_u_id" value="{{ $icu->id }}">
                                            <input type="hidden" id="doctor_id{{ $icu->id }}"
                                                name="doctor_id" value="{{ $icu->doctor->id }}">

                                            <div class="form-group">
                                                <label
                                                    for="description{{ $icu->id }}">{{ localize('global.description') }}</label>
                                                <textarea class="form-control" id="description{{ $icu->id }}" name="description" rows="3"></textarea>
                                            </div>
                                            <h5 class="mt-2">{{ localize('global.vital_signs') }}</h5>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label
                                                            for="bp{{ $icu->id }}">{{ localize('global.bp') }}</label>
                                                        <input type="text" class="form-control" name="bp" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label
                                                            for="pr{{ $icu->id }}">{{ localize('global.pr') }}</label>
                                                        <input type="text" class="form-control" name="pr" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label
                                                            for="rr{{ $icu->id }}">{{ localize('global.rr') }}</label>
                                                        <input type="text" class="form-control" name="rr" />
                                                    </div>
                                                </div>
                                                <div class="row mt-1 mb-1">
                                                    <div class="col-md-4">
                                                        <label
                                                            for="t{{ $icu->id }}">{{ localize('global.t') }}</label>
                                                        <input type="text" class="form-control" name="t" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label
                                                            for="spo2{{ $icu->id }}">{{ localize('global.spo2') }}</label>
                                                        <input type="text" class="form-control" name="spo2" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label
                                                            for="pain{{ $icu->id }}">{{ localize('global.pain') }}</label>
                                                        <input type="text" class="form-control" name="pain" />
                                                    </div>
                                                </div>
                                                <div class="row mt-1 mb-1">
                                                    <div class="col-md-6">
                                                        <label
                                                            for="antibiotic{{ $icu->id }}">{{ localize('global.antibiotic') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="antibiotic" />
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label
                                                            for="food_type_id{{ $icu->id }}">{{ localize('global.food_type') }}</label>
                                                        <select class="form-control select2" name="food_type_id[]"
                                                            id="food_type_id" multiple>
                                                            <option value="">{{ localize('global.select') }}
                                                            </option>
                                                            @foreach ($foodTypes as $value)
                                                                <option value="{{ $value->id }}"
                                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                    {{ $value->name }}

                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                </div>
                                                <div class="row mt-1 mb-1">
                                                    <div class="col-md-6">
                                                        <label
                                                            for="intake{{ $icu->id }}">{{ localize('global.intake') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="intake" />
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label
                                                            for="output{{ $icu->id }}">{{ localize('global.output') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="output" />

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
                                        <th>{{ localize('global.antibiotic') }}</th>
                                        <th>{{ localize('global.food_type') }}</th>
                                        <th>{{ localize('global.intake') }}</th>
                                        <th>{{ localize('global.output') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($icu->visits as $visit)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $visit->description }}</td>
                                            <td>{{ $visit->doctor->name }}</td>
                                            <td>{{ $visit->created_at }}</td>
                                            <td dir="ltr">
                                                <span class="badge bg-primary">{{ localize('global.bp') }}</span>
                                                {{ $visit->bp }}
                                                <br>
                                                <span class="badge bg-primary">{{ localize('global.pr') }}</span>
                                                {{ $visit->pr }}
                                                <br>
                                                <span class="badge bg-primary">{{ localize('global.rr') }}</span>
                                                {{ $visit->rr }}
                                                <br>
                                                <span class="badge bg-primary">{{ localize('global.t') }}</span>
                                                {{ $visit->t }}
                                                <br>
                                                <span class="badge bg-primary">{{ localize('global.spo2') }}</span>
                                                {{ $visit->spo2 }}
                                                <br>
                                                <span class="badge bg-primary">{{ localize('global.pain') }}</span>
                                                {{ $visit->pain }}

                                            </td>
                                            <td>{{$visit->antibiotic}}</td>
                                            <td>
                                                @foreach ($visit->getAssociatedFoodTypesAttribute() as $foodType)
                                                    <span class="badge bg-primary">{{ $foodType->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>{{$visit->intake}}</td>
                                            <td>{{$visit->output}}</td>
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

                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-command p-1"></i>{{ localize('global.advice') }}</h5>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#createAdviceModal{{ $icu->id }}"><span><i
                                        class="bx bx-plus"></i></span></button>

                        <!-- Create Diagnose Modal -->
                        <div class="modal fade" id="createAdviceModal{{ $icu->id }}" tabindex="-1"
                            aria-labelledby="createAdviceModalLabel{{ $icu->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createAdviceModalLabel{{ $icu->id }}">
                                            {{ localize('global.add_advice') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('advices.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $icu->patient_id }}"
                                                name="patient_id" value="{{ $icu->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $icu->appointment->id }}"
                                                name="appointment_id" value="{{ $icu->id }}">
                                                <input type="hidden" id="doctor_id{{ $icu->id }}"
                                                name="doctor_id" value="{{ auth()->user()->id }}">
                                                <input type="hidden" id="i_c_u_id{{ $icu->id }}"
                                                name="i_c_u_id" value="{{ $icu->id }}">
                                            <!-- Add other diagnosis form fields as needed -->
                                            <div class="form-group">

                                                <label
                                                    for="description{{ $icu->id }}">{{ localize('global.description') }}</label>
                                                <textarea class="form-control" id="description{{ $icu->id }}" name="description" rows="3"></textarea>

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
                                        <th>{{ localize('global.by') }}</th>
                                        <th>{{ localize('global.created_at') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($icu->advices as $advice)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $advice->description }}</td>
                                            <td>
                                                {{$advice->doctor->name}}
                                            </td>
                                            <td dir="ltr">{{ $advice->created_at }}</td>
                                            <td>
                                                <a href="{{ route('advices.edit', $advice->id) }}"><span><i
                                                            class="bx bx-edit"></i></span></a>
                                                <a href="{{ route('advices.destroy', $advice->id) }}"><span><i
                                                            class="bx bx-trash text-danger"></i></span></a>

                                            </td>
                                        </tr>
                                    @empty
                                        <div class="container">
                                            <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                <div class=" badge bg-label-danger mt-4">
                                                    {{ localize('global.no_previous_advices') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>

                        </div>

                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-hard-hat p-1"></i>{{ localize('global.checkups') }}</h5>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#createLabModal{{ $icu->id }}"><span><i
                                        class="bx bx-plus"></i></span></button>

                            <!-- Create  Lab Modal -->
                            <div class="modal fade" id="createLabModal{{ $icu->id }}" tabindex="-1"
                                aria-labelledby="createLabModalLabel{{ $icu->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createLabModalLabel{{ $icu->id }}">
                                                {{ localize('global.add_lab_test') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('lab_tests.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" id="patient_id{{ $icu->patient_id }}"
                                                    name="patient_id" value="{{ $icu->patient_id }}">
                                                <input type="hidden" id="appointment_id{{ $icu->id }}"
                                                    name="appointment_id" value="{{ $icu->appointment->id }}">
                                                <input type="hidden" id="doctor_id{{ $icu->id }}"
                                                    name="doctor_id" value="{{ $icu->doctor->id }}">
                                                <input type="hidden" id="branch_id{{ $icu->id }}"
                                                    name="branch_id" value="{{ auth()->user()->branch_id }}">
                                                <input type="hidden" id="hospitalization_id{{ $icu->id }}"
                                                    name="hospitalization_id" value="">
                                                <input type="hidden" id="i_c_u_id{{ $icu->id }}" name="i_c_u_id"
                                                    value="{{ $icu->id }}">

                                                <input type="hidden" id="status{{ $icu->id }}" name="status"
                                                    value="0">

                                                <div class="form-group">

                                                    <label
                                                        for="lab_type_section{{ $icu->id }}">{{ localize('global.lab_type_section') }}</label>
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
                                                        for="lab_type_id{{ $icu->id }}">{{ localize('global.lab_type') }}</label>
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
                                        @forelse ($icu->labs as $lab)
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


                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-hourglass p-1"></i>{{ localize('global.daily_icu_progress') }}</h5>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#createDailyICUProgressModal{{ $icu->id }}"><span><i
                                        class="bx bx-plus"></i></span></button>

                            <!-- Create  Lab Modal -->
                            <div class="modal fade modal-xl" id="createDailyICUProgressModal{{ $icu->id }}"
                                tabindex="-1" aria-labelledby="createDailyICUProgressModalLabel{{ $icu->id }}"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"
                                                id="createDailyICUProgressModalLabel{{ $icu->id }}">
                                                {{ localize('global.add_daily_progress') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('daily_icu_progress.store') }}" method="POST">
                                                @csrf

                                                <input type="hidden" id="i_c_u_id{{ $icu->id }}" name="i_c_u_id"
                                                    value="{{ $icu->id }}">

                                                <div class="form-group">

                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.icu_day') }}</label>
                                                            <input type="text" class="form-control" name="icu_day">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.icu_diagnose') }}</label>
                                                            <input type="text" class="form-control"
                                                                name="icu_diagnose">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.daily_events') }}</label>
                                                            <input type="text" class="form-control"
                                                                name="daily_events">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.hr') }}</label>
                                                            <input type="text" class="form-control" name="hr">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.bp') }}</label>
                                                            <input type="text" class="form-control" name="bp">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.spo2') }}</label>
                                                            <input type="text" class="form-control" name="spo2">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.t') }}</label>
                                                            <input type="text" class="form-control" name="t">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.rr') }}</label>
                                                            <input type="text" class="form-control" name="rr">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.gcs') }}</label>
                                                            <input type="text" class="form-control" name="gcs">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.cvs') }}</label>
                                                            <input type="text" class="form-control" name="cvs">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.pupils') }}</label>
                                                            <input type="text" class="form-control" name="pupils">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.s1s2') }}</label>
                                                            <input type="text" class="form-control" name="s1s2">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.rs') }}</label>
                                                            <input type="text" class="form-control" name="rs">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.gi') }}</label>
                                                            <input type="text" class="form-control" name="gi">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.renal') }}</label>
                                                            <input type="text" class="form-control" name="renal">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.musculoskeletal_system') }}</label>
                                                            <input type="text" class="form-control"
                                                                name="musculoskeletal_system">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.extremities') }}</label>
                                                            <input type="text" class="form-control"
                                                                name="extremities">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.assesment') }}</label>
                                                            <input type="text" class="form-control" name="assesment">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>{{ localize('global.icu_daily_plan') }}</label>
                                                            <input type="text" class="form-control" name="plan">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label
                                                                for="lab_ids{{ $icu->id }}">{{ localize('global.lab_ids') }}</label>
                                                            <select class="form-control select2" name="lab_ids[]"
                                                                id="lab_ids" multiple>
                                                                <option value="">{{ localize('global.select') }}
                                                                </option>
                                                                @foreach ($labTypes as $value)
                                                                    <option value="{{ $value->id }}"
                                                                        {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                        {{ $value->name }}

                                                                    </option>
                                                                @endforeach
                                                            </select>
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
                            <!-- End Create Lab Modal -->
                            <div class="col-md-12 mt-4">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.number') }}</th>
                                            <th>{{ localize('global.icu_day') }}</th>
                                            <th>{{ localize('global.created_by') }}</th>
                                            <th>{{ localize('global.created_at') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($icu->dailyProgress as $progress)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $progress->icu_day }}</td>
                                                <td>
                                                    {{ $progress->createdBy->name }}
                                                </td>
                                                <td>{{ $progress->created_at }}</td>

                                                <td>
                                                    <a
                                                        href="{{ route('daily_icu_progress.show', $progress->id) }}"><span><i
                                                                class="bx bx-expand text-primary"></i></span></a>

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








                        @endif

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
    <script>
        $(document).ready(function() {
            $('#branch').on('change', function() {
                var branchId = $(this).val();
                if (branchId !== '') {
                    $.ajax({
                        url: '/get_departments/' + branchId,
                        type: 'GET',
                        success: function(response) {

                            $('#department').html(response);
                        }
                    })
                }
            });

            $('#department').on('change', function() {
                var departmentId = $(this).val();
                if (departmentId !== '') {
                    $.ajax({
                        url: '/get_doctors/' + departmentId,
                        type: 'GET',
                        success: function(response) {

                            $('#doctor_id').html(response);
                        }
                    })
                }
            });
        })
    </script>
@endsection
