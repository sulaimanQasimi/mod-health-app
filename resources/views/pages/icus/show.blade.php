@extends('layouts.master')

@section('content')
    <style>
        .iteration-badge {
            width: 30px;
            height: 30px;
            line-height: 30px;
            border: 2px solid var(--bs-primary);
            border-radius: 50%;
            display: inline-block;
            text-align: center;
            font-weight: 600;
            color: var(--bs-primary);
        }

        .text-justify {
            text-align: justify;
        }

        /* Standardize button sizes */
        .btn-success.btn-sm {
            width: 40px;
            height: 40px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .btn-success.btn-sm span {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Improve modal styling */
        .modal-xl .modal-dialog {
            max-width: 90%;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            border-radius: 0.375rem;
        }

        .select2-container {
            width: 100% !important;
        }
    </style>
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
                                                        {{ $icu->patient->recipient->name ?? $icu->patient->referral_name }}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5 class="mb-2">{{ localize('global.creation_date') }}</h5>
                                                    <div>
                                                        {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($icu->patient->created_at) }}
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
                                                    <ul class="list-unstyled">
                                                        @foreach ($primaryDiagnoses as $diagnose)
                                                            <li
                                                                class="m-1 p-2 border-start border-warning border-3 bg-light rounded">
                                                                <span
                                                                    class="badge bg-warning text-dark me-2">{{ verta($diagnose->created_at)->format('Y-m-d') }}</span>
                                                                {{ $diagnose->description }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <ul class="list-unstyled">
                                                        @foreach ($finalDiagnoses as $diagnose)
                                                            <li
                                                                class="m-1 p-2 border-start border-success border-3 bg-light rounded">
                                                                <span
                                                                    class="badge bg-success text-white me-2">{{ verta($diagnose->created_at)->format('Y-m-d') }}</span>
                                                                {{ $diagnose->description }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Previous Labs Accordion -->
                            <div class="accordion" id="previousLabsAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="previousLabsHeader">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#previousLabsCollapse" aria-expanded="false" 
                                                aria-controls="previousLabsCollapse">
                                            <i class="bx bx-test-tube me-2"></i>
                                            {{ localize('global.previous_labs') }}
                                            <span class="badge bg-primary ms-2">{{ count($previousLabs) }}</span>
                                        </button>
                                    </h2>
                                    <div id="previousLabsCollapse" class="accordion-collapse collapse" 
                                         aria-labelledby="previousLabsHeader" data-bs-parent="#previousLabsAccordion">
                                        <div class="accordion-body p-0">
                                            <table class="table mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>{{ localize('global.number') }}</th>
                                                        <th>{{ localize('global.test_name') }}</th>
                                                        <th>{{ localize('global.test_status') }}</th>
                                                        <th>{{ localize('global.result') }}</th>
                                                        <th>{{ localize('global.result_file') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($previousLabs as $lab)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $lab->labType->name }}</td>
                                                            <td>
                                                                @if ($lab->status == '0')
                                                                    <span class="badge bg-danger">{{ localize('global.not_tested') }}</span>
                                                                @else
                                                                    <span class="badge bg-success">{{ localize('global.tested') }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $lab->result }}</td>
                                                            <td>
                                                                @isset($lab->result_file)
                                                                    <a href="{{ asset('storage/' . $lab->result_file) }}" target="_blank" 
                                                                       class="btn btn-sm btn-outline-primary">
                                                                        <i class="fa fa-file me-1"></i> {{ localize('global.file') }}
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted">{{ localize('global.no_file') }}</span>
                                                                @endisset
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted py-4">
                                                                <i class="bx bx-test-tube me-2"></i>
                                                                {{ localize('global.no_previous_labs') }}
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if ($icu->status == 'new')
                                <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                        class="bx bx-glasses p-1"></i>{{ localize('global.approve_reject_icu') }}</h5>

                                <div class="row d-flex justify-content-center">


                                    <div class="d-flex justify-content-center p-2">
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#createICUApproveModal{{ $icu->id }}"><span><i
                                                        class="bx bx-check"></i>{{ localize('global.approve') }}</span></button>
                                        </div>

                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
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
                                                {{ localize('global.approve_icu') }}
                                            </h5>
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
                                                        <textarea class="form-control" id="icu_enterance_note{{ $icu->id }}"
                                                            name="icu_enterance_note" rows="3"></textarea>
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
                                                {{ localize('global.reject_icu') }}
                                            </h5>
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
                                                        <textarea class="form-control" id="icu_reject_reason{{ $icu->id }}"
                                                            name="icu_reject_reason" rows="3"></textarea>
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
                                    <!-- Consultations Accordion -->
                                    <div class="accordion mt-4" id="consultationsAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="consultationsHeader">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                        data-bs-target="#consultationsCollapse" aria-expanded="false" 
                                                        aria-controls="consultationsCollapse">
                                                    <i class="bx bx-chat me-2"></i>
                                                    {{ localize('global.consultations') }}
                                                    <span class="badge bg-primary ms-2">{{ count($icu->consultations) }}</span>
                                                </button>
                                            </h2>
                                            <div id="consultationsCollapse" class="accordion-collapse collapse" 
                                                 aria-labelledby="consultationsHeader" data-bs-parent="#consultationsAccordion">
                                                <div class="accordion-body">
                                                    <!-- Consultation Section Vue Component -->
                                                    <div id="icu-consultation-section-container" 
                                                         data-icu='@json($icu)'
                                                         data-permissions='@json([
                                                             "canAddConsultation" => auth()->user()->can("add-consultations"),
                                                             "canEditConsultation" => auth()->user()->can("edit-consultations"),
                                                             "canDeleteConsultation" => auth()->user()->can("delete-consultations")
                                                         ])'>
                                                        <!-- Fallback content while Vue loads -->
                                                        <div class="text-center py-4">
                                                            <div class="spinner-border text-primary" role="status">
                                                                <span class="visually-hidden">Loading...</span>
                                                            </div>
                                                            <p class="mt-2">{{ localize('global.loading_consultation_section') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>





                                    <!-- Visits Accordion -->
                                    <div class="accordion mt-4" id="visitsAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="visitsHeader">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                        data-bs-target="#visitsCollapse" aria-expanded="false" 
                                                        aria-controls="visitsCollapse">
                                                    <i class="bx bx-glasses me-2"></i>
                                                    {{ localize('global.visits') }}
                                                    <span class="badge bg-primary ms-2">{{ count($icu->visits) }}</span>
                                                </button>
                                            </h2>
                                            <div id="visitsCollapse" class="accordion-collapse collapse" 
                                                 aria-labelledby="visitsHeader" data-bs-parent="#visitsAccordion">
                                                <div class="accordion-body p-0">
                                                    <!-- Add Visit Button -->
                                                    @if ($icu->is_discharged == '0')
                                                        <div class="p-3 border-bottom">
                                                            <button type="button" class="btn btn-success btn-sm" 
                                                                    data-bs-toggle="modal" data-bs-target="#createVisitModal{{ $icu->id }}"
                                                                    title="{{ localize('global.add_visit') }}">
                                                                <i class="bx bx-plus"></i>
                                                            </button>
                                                        </div>
                                                    @endif
                                    <!-- Create visit Modal -->
                                    <div class="modal fade" id="createVisitModal{{ $icu->id }}" tabindex="-1"
                                        aria-labelledby="createVisitModalLabel{{ $icu->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="createVisitModalLabel{{ $icu->id }}">
                                                        {{ localize('global.add_visit') }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('visits.store') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" id="patient_id{{ $icu->patient_id }}" name="patient_id"
                                                            value="{{ $icu->patient_id }}">
                                                        <input type="hidden" id="i_c_u_id{{ $icu->id }}" name="i_c_u_id"
                                                            value="{{ $icu->id }}">
                                                        <input type="hidden" id="doctor_id{{ $icu->id }}" name="doctor_id"
                                                            value="{{ $icu->doctor->id }}">

                                                        <div class="form-group">
                                                            <label
                                                                for="description{{ $icu->id }}">{{ localize('global.description') }}</label>
                                                            <textarea class="form-control" id="description{{ $icu->id }}"
                                                                name="description" rows="3" required></textarea>
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
                                                                    <label for="t{{ $icu->id }}">{{ localize('global.t') }}</label>
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
                                                                    <input type="text" class="form-control" name="antibiotic" />
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label
                                                                        for="food_type_id{{ $icu->id }}">{{ localize('global.food_type') }}</label>
                                                                    <select class="form-control select2" name="food_type_id[]"
                                                                        id="food_type_id" multiple>
                                                                        <option value="">{{ localize('global.select') }}
                                                                        </option>
                                                                        @foreach ($foodTypes as $value)
                                                                            <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
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
                                                                    <input type="text" class="form-control" name="intake" />
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label
                                                                        for="output{{ $icu->id }}">{{ localize('global.output') }}</label>
                                                                    <input type="text" class="form-control" name="output" />

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
                                                    <table class="table mb-0">
                                                        <thead class="table-light">
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
                                                                    <td>{{ verta($visit->created_at)->format('Y-m-d H:i') }}</td>
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
                                                                    <td>{{ $visit->antibiotic }}</td>
                                                                    <td>
                                                                        @foreach ($visit->getAssociatedFoodTypesAttribute() as $foodType)
                                                                            <span class="badge bg-primary">{{ $foodType->name }}</span>
                                                                        @endforeach
                                                                    </td>
                                                                    <td>{{ $visit->intake }}</td>
                                                                    <td>{{ $visit->output }}</td>
                                                                    <td>
                                                                        <a href="{{ route('visits.edit', $visit->id) }}"
                                                                            class="btn btn-outline-primary btn-sm" title="Edit">
                                                                            <i class="bx bx-edit"></i>
                                                                        </a>
                                                                        <form id="delete-form-{{$visit->id}}"
                                                                            action="{{ route('visits.destroy', $visit->id) }}" method="POST"
                                                                            style="display: none;">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                        </form>
                                                                        <a href="#"
                                                                            onclick="event.preventDefault(); if(confirm('{{ localize('global.are_you_sure_delete') }}')) { 
                                                                                            document.getElementById('delete-form-{{$visit->id}}').submit(); }"
                                                                            class="btn btn-outline-danger btn-sm" title="Delete">
                                                                            <i class="bx bx-trash"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="10" class="text-center text-muted py-4">
                                                                        <i class="bx bx-glasses me-2"></i>
                                                                        {{ localize('global.no_previous_visits') }}
                                                                    </td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>



                                    <!-- Prescription Accordion -->
                                    <div class="accordion mt-4" id="prescriptionAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="prescriptionHeader">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                        data-bs-target="#prescriptionCollapse" aria-expanded="false" 
                                                        aria-controls="prescriptionCollapse">
                                                    <i class="bx bx-notepad me-2"></i>
                                                    {{ localize('global.prescription') }}
                                                    <span class="badge bg-primary ms-2">{{ count($icu->prescription) }}</span>
                                                </button>
                                            </h2>
                                            <div id="prescriptionCollapse" class="accordion-collapse collapse" 
                                                 aria-labelledby="prescriptionHeader" data-bs-parent="#prescriptionAccordion">
                                                <div class="accordion-body p-0">
                                                    <!-- Add Prescription Button -->
                                                    @if ($icu->is_discharged == '0')
                                                        <div class="p-3 border-bottom">
                                                            <button type="button" class="btn btn-success btn-sm" 
                                                                    data-bs-toggle="modal" data-bs-target="#createPrescriptionModal{{ $icu->id }}"
                                                                    title="{{ localize('global.add_prescription') }}">
                                                                <i class="bx bx-plus"></i>
                                                            </button>
                                                        </div>
                                                    @endif
                                    <!-- Create Diagnose Modal -->
                                    <div class="modal fade modal-xl" id="createPrescriptionModal{{ $icu->id }}" tabindex="-1"
                                        aria-labelledby="createPrescriptionModalLabel{{ $icu->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="createPrescriptionModalLabel{{ $icu->id }}">
                                                        {{ localize('global.add_prescription') }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('prescriptions.store') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" id="patient_id{{ $icu->patient_id }}" name="patient_id"
                                                            value="{{ $icu->patient_id }}">
                                                        <input type="hidden" id="appointment_id{{ $icu->appointment->id ?? '' }}"
                                                            name="appointment_id" value="{{ $icu->appointment->id ?? '' }}">
                                                        <input type="hidden" id="branch_id{{ $icu->id }}" name="branch_id"
                                                            value="{{ auth()->user()->branch_id }}">
                                                        <input type="hidden" id="doctor_id{{ $icu->id }}" name="doctor_id"
                                                            value="{{ auth()->user()->id }}">
                                                        <input type="hidden" id="i_c_u_id{{ $icu->id }}" name="i_c_u_id"
                                                            value="{{ $icu->id }}">

                                                        <!-- Add other diagnosis form fields as needed -->
                                                        <div class="form-group" id="prescription-items">
                                                            <label>{{ localize('global.description') }}</label>
                                                            <div id="prescription-input-container">
                                                                <div class="row">
                                                                    <div class="col-md-2">
                                                                        <select class="form-control select2"
                                                                            name="medicine_type_id[]" required>
                                                                            <option value="">
                                                                                {{ localize('global.select') }}
                                                                            </option>
                                                                            @foreach ($medicineTypes as $value)
                                                                                <option value="{{ $value->id }}" {{ old('type') == $value->id ? 'selected' : '' }}>
                                                                                    {{ $value->type }}

                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <select class="form-control select2" name="medicine_id[]"
                                                                            required>
                                                                            <option value="">
                                                                                {{ localize('global.select') }}
                                                                            </option>
                                                                            @foreach ($medicines as $value)
                                                                                <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                                    {{ $value->name }}

                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <select class="form-control select2" name="usage_type_id[]"
                                                                            required>
                                                                            <option value="">
                                                                                {{ localize('global.select') }}
                                                                            </option>
                                                                            @foreach ($medicineUsageTypes as $value)
                                                                                <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                                    {{ $value->name }}

                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control mt-2" name="dosage[]"
                                                                            placeholder="Dosage" required>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control mt-2"
                                                                            name="frequency[]" placeholder="Frequency" required>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <input type="text" class="form-control mt-2" name="amount[]"
                                                                            placeholder="Amount" required>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <input type="hidden" class="form-control mt-2"
                                                                            name="is_delivered[]" value="0">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <button type="button" class="btn btn-primary btn-sm mt-2"
                                                            id="addPrescriptionInput" onclick="addRow()">
                                                            <i class="bx bx-plus"></i>{{ localize('global.add_prescription_item') }}
                                                        </button>
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

                                                    <table class="table mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>{{ localize('global.number') }}</th>
                                                                <th>{{ localize('global.patient_name') }}</th>
                                                                <th>{{ localize('global.status') }}</th>
                                                                <th>{{ localize('global.actions') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($icu->prescription as $prescription)
                                                                <tr>
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>{{ $prescription->patient->name }}</td>
                                                                    <td>
                                                                        @if ($prescription->is_completed == '0')
                                                                            <span class="badge bg-danger">{{ localize('global.not_delivered') }}</span>
                                                                        @else
                                                                            <span class="badge bg-success">{{ localize('global.delivered') }}</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <a href="#" data-bs-toggle="modal"
                                                                            onclick="getPrescriptionItems({{ $prescription->id }})"
                                                                            data-bs-target="#showPrescriptionItemModal"
                                                                            class="btn btn-outline-primary btn-sm" title="View Details">
                                                                            <i class="bx bx-expand"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="4" class="text-center text-muted py-4">
                                                                        <i class="bx bx-notepad me-2"></i>
                                                                        {{ localize('global.no_previous_prescriptions') }}
                                                                    </td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                        <div class="modal fade modal-xl" id="showPrescriptionItemModal" tabindex="-1"
                                            aria-labelledby="showPrescriptionItemModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content" id="prescription_items_table">



                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="modal fade modal-xl" id="showPrescriptionModal{{ $icu->id }}" tabindex="-1"
                                        aria-labelledby="showPrescriptionModalLabel{{ $icu->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="showPrescriptionModalLabel{{ $icu->id }}">
                                                        {{ localize('global.show_prescription_details') }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ localize('global.number') }}</th>
                                                                <th>{{ localize('global.date') }}</th>
                                                                {{-- <th>{{ localize('global.description') }}</th>
                                                                <th>{{ localize('global.dosage') }}</th>
                                                                <th>{{ localize('global.frequency') }}</th>
                                                                <th>{{ localize('global.amount') }}</th> --}}
                                                                <th>{{ localize('global.status') }}</th>
                                                                <th>{{ localize('global.actions') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if ($icu->prescription)
                                                                @foreach ($icu->prescription as $pres_list)
                                                                    <tr>
                                                                        <td>{{ $loop->iteration }}</td>
                                                                        <td>{{ verta($pres_list->created_at)->format('Y-m-d H:i') }}</td>
                                                                        <td>{{ $pres_list->is_completed }}</td>
                                                                        <td>
                                                                            <a href="#" data-bs-toggle="modal"
                                                                                onclick="getPrescriptionItems({{ $pres_list->id }})"
                                                                                data-bs-target="#showPrescriptionItemModal"
                                                                                class="btn btn-outline-primary btn-sm" title="View Details">
                                                                                <i class="bx bx-expand"></i>
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td colspan="4" class="text-center">
                                                                        <div class="badge bg-label-danger mt-4">
                                                                            {{ localize('global.no_previous_prescriptions') }}
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>




                                    <!-- Advice Accordion -->
                                    <div class="accordion mt-4" id="adviceAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="adviceHeader">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                        data-bs-target="#adviceCollapse" aria-expanded="false" 
                                                        aria-controls="adviceCollapse">
                                                    <i class="bx bx-command me-2"></i>
                                                    {{ localize('global.advice') }}
                                                    <span class="badge bg-primary ms-2">{{ count($icu->advices) }}</span>
                                                </button>
                                            </h2>
                                            <div id="adviceCollapse" class="accordion-collapse collapse" 
                                                 aria-labelledby="adviceHeader" data-bs-parent="#adviceAccordion">
                                                <div class="accordion-body p-0">
                                                    <!-- Add Advice Button -->
                                                    @if ($icu->is_discharged == '0')
                                                        <div class="p-3 border-bottom">
                                                            <button type="button" class="btn btn-success btn-sm" 
                                                                    data-bs-toggle="modal" data-bs-target="#createAdviceModal{{ $icu->id }}"
                                                                    title="{{ localize('global.add_advice') }}">
                                                                <i class="bx bx-plus"></i>
                                                            </button>
                                                        </div>
                                                    @endif
                                    <!-- Create Diagnose Modal -->
                                    <div class="modal fade" id="createAdviceModal{{ $icu->id }}" tabindex="-1"
                                        aria-labelledby="createAdviceModalLabel{{ $icu->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="createAdviceModalLabel{{ $icu->id }}">
                                                        {{ localize('global.add_advice') }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('advices.store') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" id="patient_id{{ $icu->patient_id }}" name="patient_id"
                                                            value="{{ $icu->patient_id }}">
                                                        <input type="hidden" id="appointment_id{{ $icu->appointment->id ?? '' }}"
                                                            name="appointment_id" value="{{ $icu->id }}">
                                                        <input type="hidden" id="doctor_id{{ $icu->id }}" name="doctor_id"
                                                            value="{{ auth()->user()->id }}">
                                                        <input type="hidden" id="i_c_u_id{{ $icu->id }}" name="i_c_u_id"
                                                            value="{{ $icu->id }}">
                                                        <!-- Add other diagnosis form fields as needed -->
                                                        <div class="form-group">

                                                            <label
                                                                for="description{{ $icu->id }}">{{ localize('global.description') }}</label>
                                                            <textarea class="form-control" id="description{{ $icu->id }}"
                                                                name="description" rows="3" required></textarea>

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
                                                    <table class="table mb-0">
                                                        <thead class="table-light">
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
                                                                        {{ $advice->doctor->name }}
                                                                    </td>
                                                                    <td dir="ltr">{{ verta($advice->created_at)->format('Y-m-d H:i') }}</td>
                                                                    <td>
                                                                        <a href="{{ route('advices.edit', $advice->id) }}"
                                                                            class="btn btn-outline-primary btn-sm" title="Edit">
                                                                            <i class="bx bx-edit"></i>
                                                                        </a>
                                                                        <a href="{{ route('advices.destroy', $advice->id) }}"
                                                                            onclick="event.preventDefault(); if(confirm('{{ localize('global.are_you_sure_delete') }}')) { document.getElementById('delete-form-{{$advice->id}}').submit(); }"
                                                                            class="btn btn-outline-danger btn-sm" title="Delete">
                                                                            <i class="bx bx-trash"></i>
                                                                        </a>
                                                                        <form id="delete-form-{{$advice->id}}"
                                                                            action="{{ route('advices.destroy', $advice->id) }}" method="POST"
                                                                            style="display: none;">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="5" class="text-center text-muted py-4">
                                                                        <i class="bx bx-command me-2"></i>
                                                                        {{ localize('global.no_previous_advices') }}
                                                                    </td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Lab Tests Accordion -->
                                    <div class="accordion mt-4" id="labTestsAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="labTestsHeader">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                        data-bs-target="#labTestsCollapse" aria-expanded="false" 
                                                        aria-controls="labTestsCollapse">
                                                    <i class="bx bx-test-tube me-2"></i>
                                                    {{ localize('global.checkups') }}
                                                    <span class="badge bg-primary ms-2">{{ count($icu->labs) }}</span>
                                                </button>
                                            </h2>
                                            <div id="labTestsCollapse" class="accordion-collapse collapse" 
                                                 aria-labelledby="labTestsHeader" data-bs-parent="#labTestsAccordion">
                                                <div class="accordion-body p-0">
                                                    <!-- Add Lab Test Button -->
                                                    @if ($icu->is_discharged == '0')
                                                        <div class="p-3 border-bottom">
                                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                                data-bs-target="#createLabModal{{ $icu->id }}">
                                                                <i class="bx bx-plus"></i>
                                                            </button>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Lab Tests Table -->
                                                    <div class="table-responsive">
                                                        <table class="table mb-0">
                                                            <thead class="table-light">
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
                                                                                <span class="badge bg-danger">{{ localize('global.not_tested') }}</span>
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
                                                                            <a href="{{ route('lab_tests.edit', $lab->id) }}"
                                                                                class="btn btn-outline-primary btn-sm" title="Edit">
                                                                                <i class="bx bx-edit"></i>
                                                                            </a>
                                                                            <a href="{{ route('lab_tests.destroy', $lab->id) }}"
                                                                                onclick="event.preventDefault(); if(confirm('{{ localize('global.are_you_sure_delete') }}')) { document.getElementById('delete-form-{{$lab->id}}').submit(); }"
                                                                                class="btn btn-outline-danger btn-sm" title="Delete">
                                                                                <i class="bx bx-trash"></i>
                                                                            </a>
                                                                            <form id="delete-form-{{$lab->id}}"
                                                                                action="{{ route('lab_tests.destroy', $lab->id) }}" method="POST"
                                                                                style="display: none;">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                            </form>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="6" class="text-center py-4">
                                                                            <div class="badge bg-label-danger">
                                                                                {{ localize('global.no_previous_labs') }}
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Create Lab Modal -->
                                    <div class="modal fade" id="createLabModal{{ $icu->id }}" tabindex="-1"
                                        aria-labelledby="createLabModalLabel{{ $icu->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="createLabModalLabel{{ $icu->id }}">
                                                        {{ localize('global.add_lab_test') }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('lab_tests.store') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" id="patient_id{{ $icu->patient_id }}" name="patient_id"
                                                            value="{{ $icu->patient_id }}">
                                                        <input type="hidden" id="appointment_id{{ $icu->id }}" name="appointment_id"
                                                            value="{{ $icu->appointment->id ?? '' }}">
                                                        <input type="hidden" id="doctor_id{{ $icu->id }}" name="doctor_id"
                                                            value="{{ $icu->doctor->id ?? '' }}">
                                                        <input type="hidden" id="branch_id{{ $icu->id }}" name="branch_id"
                                                            value="{{ auth()->user()->branch_id }}">
                                                        <input type="hidden" id="hospitalization_id{{ $icu->id }}"
                                                            name="hospitalization_id" value="">
                                                        <input type="hidden" id="i_c_u_id{{ $icu->id }}" name="i_c_u_id"
                                                            value="{{ $icu->id }}">

                                                        <input type="hidden" id="status{{ $icu->id }}" name="status" value="0">

                                                        <div class="form-group">
                                                            <label
                                                                for="lab_type_section{{ $icu->id }}">{{ localize('global.lab_type_section') }}</label>
                                                            <select class="form-control select2" name="lab_type_section"
                                                                id="lab_type_section">
                                                                <option value="">{{ localize('global.select') }}</option>
                                                                @foreach ($labTypeSections as $value)
                                                                    <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
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
                                                                    <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
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


                                    <!-- Procedures Accordion -->
                                    <div class="accordion mt-4" id="proceduresAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="proceduresHeader">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                        data-bs-target="#proceduresCollapse" aria-expanded="false" 
                                                        aria-controls="proceduresCollapse">
                                                    <i class="bx bx-dna me-2"></i>
                                                    {{ localize('global.procedures') }}
                                                    <span class="badge bg-primary ms-2">{{ count($icu->procedures) }}</span>
                                                </button>
                                            </h2>
                                            <div id="proceduresCollapse" class="accordion-collapse collapse" 
                                                 aria-labelledby="proceduresHeader" data-bs-parent="#proceduresAccordion">
                                                <div class="accordion-body p-0">
                                                    <!-- Add Procedure Button -->
                                                    @if ($icu->is_discharged == '0')
                                                        <div class="p-3 border-bottom">
                                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                                data-bs-target="#createProcedureModal{{ $icu->id }}">
                                                                <i class="bx bx-plus"></i>
                                                            </button>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Procedures Table -->
                                                    <div class="table-responsive">
                                                        <table class="table mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>{{ localize('global.number') }}</th>
                                                                    <th>{{ localize('global.type') }}</th>
                                                                    <th>{{ localize('global.description') }}</th>
                                                                    <th>{{ localize('global.created_by') }}</th>
                                                                    <th>{{ localize('global.created_at') }}</th>
                                                                    <th>{{ localize('global.actions') }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse ($icu->procedures as $procedure)
                                                                    <tr>
                                                                        <td>{{ $loop->iteration }}</td>
                                                                        <td>{{ $procedure->procedure_type->name }}</td>
                                                                        <td>{{ $procedure->description }}</td>
                                                                        <td>
                                                                            {{ $procedure->createdBy->name }}
                                                                        </td>
                                                                        <td>{{ verta($procedure->created_at)->format('Y-m-d H:i') }}</td>
                                                                        <td>
                                                                            @can('edit-icu-procedure')
                                                                                <a href="{{ route('procedures.edit', $procedure->id) }}"
                                                                                    class="btn btn-outline-primary btn-sm" title="Edit">
                                                                                    <i class="bx bx-edit"></i>
                                                                                </a>
                                                                            @endcan
                                                                            @can('delete-icu-procedure')
                                                                                <a href="{{ route('procedures.destroy', $procedure) }}"
                                                                                    onclick="event.preventDefault(); if(confirm('{{ localize('global.are_you_sure_delete') }}')) { document.getElementById('delete-form-{{$procedure->id}}').submit(); }"
                                                                                    class="btn btn-outline-danger btn-sm" title="Delete">
                                                                                    <i class="bx bx-trash"></i>
                                                                                </a>
                                                                            @endcan
                                                                            <form id="delete-form-{{$procedure->id}}"
                                                                                action="{{ route('procedures.destroy', $procedure) }}" method="POST"
                                                                                style="display: none;">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                            </form>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="6" class="text-center py-4">
                                                                            <div class="badge bg-label-danger">
                                                                                {{ localize('global.no_previous_procedures') }}
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Create Procedure Modal -->
                                    <div class="modal fade" id="createProcedureModal{{ $icu->id }}" tabindex="-1"
                                        aria-labelledby="createProcedureModalLabel{{ $icu->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('procedures.store') }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="createProcedureModalLabel{{ $icu->id }}">
                                                            {{ localize('global.add_procedure') }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" id="i_c_u_id{{ $icu->id }}" name="i_c_u_id"
                                                            value="{{ $icu->id }}">
                                                        <div class="form-group">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <label
                                                                        for="icu_procedure_type_id{{ $icu->id }}">{{ localize('global.procedure_type') }}</label>
                                                                    <select class="form-control select2"
                                                                        name="icu_procedure_type_id" id="icu_procedure_type_id"
                                                                        required>
                                                                        <option value="">{{ localize('global.select') }}
                                                                        </option>
                                                                        @foreach ($procedure_types as $value)
                                                                            <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                                {{ $value->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <label
                                                                        for="description{{ $icu->id }}">{{ localize('global.description') }}</label>
                                                                    <textarea class="form-control" id="description{{ $icu->id }}"
                                                                        name="description" rows="3" required></textarea>
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
                                    <!-- End Create Procedure Modal -->








                                <!-- Daily ICU Progress Accordion -->
                                <div class="accordion mt-4" id="dailyProgressAccordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="dailyProgressHeader">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                    data-bs-target="#dailyProgressCollapse" aria-expanded="false" 
                                                    aria-controls="dailyProgressCollapse">
                                                <i class="bx bx-hourglass me-2"></i>
                                                {{ localize('global.daily_icu_progress') }}
                                                <span class="badge bg-primary ms-2">{{ count($icu->dailyProgress) }}</span>
                                            </button>
                                        </h2>
                                        <div id="dailyProgressCollapse" class="accordion-collapse collapse" 
                                             aria-labelledby="dailyProgressHeader" data-bs-parent="#dailyProgressAccordion">
                                            <div class="accordion-body p-0">
                                                <!-- Add Daily Progress Button -->
                                                @if ($icu->is_discharged == '0')
                                                    <div class="p-3 border-bottom">
                                                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                                            data-bs-target="#createDailyICUProgressModal{{ $icu->id }}">
                                                            <i class="bx bx-plus"></i>
                                                        </button>
                                                    </div>
                                                @endif
                                                
                                                <!-- Daily Progress Table -->
                                                <div class="table-responsive">
                                                    <table class="table mb-0">
                                                        <thead class="table-light">
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
                                                                    <td>{{ verta($progress->created_at)->format('Y-m-d H:i') }}</td>
                                                                    <td>
                                                                        <a href="{{ route('daily_icu_progress.show', $progress->id) }}"
                                                                            class="btn btn-outline-primary btn-sm" title="View Details">
                                                                            <i class="bx bx-expand"></i>
                                                                        </a>
                                                                        @can('edit-daily-icu-progress')
                                                                            <a href="{{ route('daily_icu_progress.edit', $progress->id) }}"
                                                                                class="btn btn-outline-primary btn-sm" title="Edit">
                                                                                <i class="bx bx-message-edit"></i>
                                                                            </a>
                                                                        @endcan
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="5" class="text-center py-4">
                                                                        <div class="badge bg-label-danger">
                                                                            {{ localize('global.no_previous_progress') }}
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Create Daily ICU Progress Modal -->
                                <div class="modal fade modal-xl" id="createDailyICUProgressModal{{ $icu->id }}" tabindex="-1"
                                    aria-labelledby="createDailyICUProgressModalLabel{{ $icu->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title" id="createDailyICUProgressModalLabel{{ $icu->id }}">
                                                    <i class="bx bx-hourglass me-2"></i>{{ localize('global.add_daily_progress') }}
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('daily_icu_progress.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" id="i_c_u_id{{ $icu->id }}" name="i_c_u_id"
                                                        value="{{ $icu->id }}">

                                                    <div class="row g-3">
                                                        <!-- Basic Information -->
                                                        <div class="col-12">
                                                            <h6 class="text-primary border-bottom pb-2">
                                                                {{ localize('global.basic_information') }}</h6>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.icu_day') }}</label>
                                                                <input type="text" class="form-control" name="icu_day"
                                                                    placeholder="ICU Day">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.icu_diagnose') }}</label>
                                                                <input type="text" class="form-control" name="icu_diagnose"
                                                                    placeholder="ICU Diagnosis">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.daily_events') }}</label>
                                                                <input type="text" class="form-control" name="daily_events"
                                                                    placeholder="Daily Events">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.hr') }}</label>
                                                                <input type="text" class="form-control" name="hr"
                                                                    placeholder="Heart Rate">
                                                            </div>
                                                        </div>

                                                        <!-- Vital Signs -->
                                                        <div class="col-12">
                                                            <h6 class="text-primary border-bottom pb-2 mt-3">
                                                                {{ localize('global.vital_signs') }}</h6>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.bp') }}</label>
                                                                <input type="text" class="form-control" name="bp"
                                                                    placeholder="Blood Pressure">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.spo2') }}</label>
                                                                <input type="text" class="form-control" name="spo2"
                                                                    placeholder="SpO2">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.t') }}</label>
                                                                <input type="text" class="form-control" name="t"
                                                                    placeholder="Temperature">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.rr') }}</label>
                                                                <input type="text" class="form-control" name="rr"
                                                                    placeholder="Respiratory Rate">
                                                            </div>
                                                        </div>

                                                        <!-- Neurological Assessment -->
                                                        <div class="col-12">
                                                            <h6 class="text-primary border-bottom pb-2 mt-3">
                                                                {{ localize('global.neurological_assessment') }}</h6>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.gcs') }}</label>
                                                                <input type="text" class="form-control" name="gcs"
                                                                    placeholder="Glasgow Coma Scale">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.cvs') }}</label>
                                                                <input type="text" class="form-control" name="cvs"
                                                                    placeholder="Cardiovascular System">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.pupils') }}</label>
                                                                <input type="text" class="form-control" name="pupils"
                                                                    placeholder="Pupils">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.s1s2') }}</label>
                                                                <input type="text" class="form-control" name="s1s2"
                                                                    placeholder="S1/S2">
                                                            </div>
                                                        </div>

                                                        <!-- System Assessment -->
                                                        <div class="col-12">
                                                            <h6 class="text-primary border-bottom pb-2 mt-3">
                                                                {{ localize('global.system_assessment') }}</h6>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.rs') }}</label>
                                                                <input type="text" class="form-control" name="rs"
                                                                    placeholder="Respiratory System">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.gi') }}</label>
                                                                <input type="text" class="form-control" name="gi"
                                                                    placeholder="Gastrointestinal">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.renal') }}</label>
                                                                <input type="text" class="form-control" name="renal"
                                                                    placeholder="Renal">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.musculoskeletal_system') }}</label>
                                                                <input type="text" class="form-control"
                                                                    name="musculoskeletal_system" placeholder="Musculoskeletal">
                                                            </div>
                                                        </div>

                                                        <!-- Additional Information -->
                                                        <div class="col-12">
                                                            <h6 class="text-primary border-bottom pb-2 mt-3">
                                                                {{ localize('global.additional_information') }}</h6>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.extremities') }}</label>
                                                                <input type="text" class="form-control" name="extremities"
                                                                    placeholder="Extremities">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.assesment') }}</label>
                                                                <input type="text" class="form-control" name="assesment"
                                                                    placeholder="Assessment">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.icu_daily_plan') }}</label>
                                                                <input type="text" class="form-control" name="plan"
                                                                    placeholder="Daily Plan">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label
                                                                    for="lab_ids{{ $icu->id }}">{{ localize('global.lab_ids') }}</label>
                                                                <select class="form-control select2" name="lab_ids[]" id="lab_ids"
                                                                    multiple>
                                                                    <option value="">{{ localize('global.select') }}</option>
                                                                    @foreach ($labTypes as $value)
                                                                        <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="bx bx-x me-1"></i>{{ localize('global.cancel') }}
                                                </button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bx bx-save me-1"></i>{{ localize('global.save') }}
                                                </button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Create Lab Modal -->

                                <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                        class="bx bx-bed p-1"></i>{{ localize('global.hospitalize') }}</h5>
                                @if ($icu->is_discharged == 0)
                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#createHospitalizationModal{{ $icu->id }}"><span><i
                                                class="bx bx-plus"></i></span></button>
                                @endif
                                <!-- Create Hospitalization Modal -->
                                <div class="modal fade modal-xl" id="createHospitalizationModal{{ $icu->id }}" tabindex="-1"
                                    aria-labelledby="createHospitalizationModalLabel{{ $icu->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title" id="createHospitalizationModalLabel{{ $icu->id }}">
                                                    <i class="bx bx-bed me-2"></i>{{ localize('global.hospitalize_patient') }}
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('hospitalizations.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" id="patient_id{{ $icu->patient_id }}" name="patient_id"
                                                        value="{{ $icu->patient_id }}">
                                                    <input type="hidden" id="appointment_id{{ $icu->id }}" name="appointment_id"
                                                        value="{{ $icu->appointment->id ?? '' }}">
                                                    <input type="hidden" id="i_c_u_id{{ $icu->id }}" name="i_c_u_id"
                                                        value="{{ $icu->id }}">
                                                    <input type="hidden" id="doctor_id{{ $icu->id }}" name="doctor_id"
                                                        value="{{ auth()->user()->id }}">
                                                    <input type="hidden" id="branch_id{{ $icu->id }}" name="branch_id"
                                                        value="{{ auth()->user()->branch_id }}">
                                                    <input type="hidden" id="is_discharged{{ $icu->id }}" name="is_discharged"
                                                        value="0">

                                                    <div class="row g-3">
                                                        <!-- Hospitalization Details -->
                                                        <div class="col-12">
                                                            <h6 class="text-primary border-bottom pb-2">
                                                                {{ localize('global.hospitalization_details') }}</h6>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label
                                                                    for="reason{{ $icu->id }}">{{ localize('global.reason') }}</label>
                                                                <textarea class="form-control" id="reason{{ $icu->id }}"
                                                                    name="reason" rows="3" required
                                                                    placeholder="Reason for hospitalization"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label
                                                                    for="remarks{{ $icu->id }}">{{ localize('global.remarks') }}</label>
                                                                <textarea class="form-control" id="remarks{{ $icu->id }}"
                                                                    name="remarks" rows="3"
                                                                    placeholder="Additional remarks"></textarea>
                                                            </div>
                                                        </div>

                                                        <!-- Room and Bed Assignment -->
                                                        <div class="col-12">
                                                            <h6 class="text-primary border-bottom pb-2 mt-3">
                                                                {{ localize('global.room_bed_assignment') }}</h6>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    for="room_id{{ $icu->id }}">{{ localize('global.rooms') }}</label>
                                                                <select class="form-control select2" name="room_id" id="room_id"
                                                                    required>
                                                                    <option value="">{{ localize('global.select') }}</option>
                                                                    @foreach ($rooms as $value)
                                                                        <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    for="bed_id{{ $icu->id }}">{{ localize('global.beds') }}</label>
                                                                <select class="form-control select2" name="bed_id" id="bed_id"
                                                                    required>
                                                                    <option value="">{{ localize('global.select') }}</option>
                                                                    @foreach ($beds as $value)
                                                                        <option value="{{ $value->id }}" {{ old('number') == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->number }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label
                                                                    for="food_type_id{{ $icu->id }}">{{ localize('global.food_type') }}</label>
                                                                <select class="form-control select2" name="food_type_id[]"
                                                                    id="food_type_id" multiple>
                                                                    <option value="">{{ localize('global.select') }}</option>
                                                                    @foreach ($foodTypes as $value)
                                                                        <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <!-- Patient Companion Information -->
                                                        <div class="col-12">
                                                            <h6 class="text-primary border-bottom pb-2 mt-3">
                                                                <i
                                                                    class="bx bx-info-circle me-2"></i>{{ localize('global.patient_companion_info') }}
                                                            </h6>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.companion_name') }}</label>
                                                                <input type="text" class="form-control" name="patient_companion"
                                                                    placeholder="Companion Name">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.companion_father_name') }}</label>
                                                                <input type="text" class="form-control" name="companion_father_name"
                                                                    placeholder="Father's Name">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.relation_to_patient') }}</label>
                                                                <select class="form-control select2" name="relation_to_patient">
                                                                    <option value="">{{ localize('global.select') }}</option>
                                                                    @foreach ($relations as $value)
                                                                        <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>{{ localize('global.companion_card_type') }}</label>
                                                                <select class="form-control select2" name="companion_card_type">
                                                                    <option value="">{{ localize('global.select') }}</option>
                                                                    <option value="12">{{ localize('global.12_hours') }}</option>
                                                                    <option value="24">{{ localize('global.24_hours') }}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="bx bx-x me-1"></i>{{ localize('global.cancel') }}
                                                </button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bx bx-save me-1"></i>{{ localize('global.save') }}
                                                </button>
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
                                                <th class="text-wrap">{{ localize('global.reason') }}</th>
                                                <th>{{ localize('global.remarks') }}</th>
                                                <th>{{ localize('global.room') }}</th>
                                                <th>{{ localize('global.bed') }}</th>
                                                <th>{{ localize('global.status') }}</th>
                                                <th>{{ localize('global.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($icu->hospitalizations as $hospitalization)
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
                                                        @if ($hospitalization->is_discharged == 0)
                                                            <span class="badge bg-danger">{{ localize('global.in_bed') }}</span>
                                                        @else
                                                            <span class="badge bg-success">{{ localize('global.discharged') }}</span>
                                                        @endif

                                                    </td>
                                                    <td>
                                                        <a href="{{ route('hospitalizations.edit', $hospitalization->id) }}"
                                                            class="btn btn-outline-primary btn-sm" title="Edit">
                                                            <i class="bx bx-edit"></i>
                                                        </a>
                                                        <a href="{{ route('hospitalizations.destroy', $hospitalization->id) }}"
                                                            onclick="event.preventDefault(); if(confirm('{{ localize('global.are_you_sure_delete') }}')) { document.getElementById('delete-form-{{$hospitalization->id}}').submit(); }"
                                                            class="btn btn-outline-danger btn-sm" title="Delete">
                                                            <i class="bx bx-trash"></i>
                                                        </a>
                                                        <form id="delete-form-{{$hospitalization->id}}"
                                                            action="{{ route('hospitalizations.destroy', $hospitalization->id) }}"
                                                            method="POST" style="display: none;">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>

                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">
                                                        <div class="badge bg-label-danger mt-4">
                                                            {{ localize('global.no_previous_hospitalizations') }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>

                                </div>
                                <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                        class="bx bx-walk p-1"></i>{{ localize('global.discharge_move_patient') }}</h5>
                                @if ($icu->is_discharged == '0')
                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#createDischargeModal{{ $icu->id }}"><span><i
                                                class="bx bx-plus"></i></span></button>
                                @endif
                                <!-- Create Discharge Modal -->
                                <div class="modal fade" id="createDischargeModal{{ $icu->id }}" tabindex="-1"
                                    aria-labelledby="createDischargeModalLabel{{ $icu->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title text-white" id="createDischargeModalLabel{{ $icu->id }}">
                                                    <i class="bx bx-walk me-2"></i>{{ localize('global.discharge_patient') }}
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('icus.update', $icu) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="form-group mb-3">
                                                        <label for="discharge_status{{ $icu->id }}" class="form-label fw-bold">
                                                            {{ localize('global.discharge_status') }}
                                                        </label>
                                                        <select class="form-control select2" name="discharge_status"
                                                            id="discharge_status{{ $icu->id }}" required>
                                                            <option value="">{{ localize('global.select') }}</option>
                                                            <option value="recovered">{{ localize('global.recovered') }}</option>
                                                            <option value="died">{{ localize('global.died') }}</option>
                                                            <option value="moved">{{ localize('global.moved') }}</option>
                                                        </select>
                                                    </div>

                                                    <!-- Recovered Options -->
                                                    <div id="discharge_options{{ $icu->id }}" style="display: none;" class="mt-3">
                                                        <div class="alert alert-success">
                                                            <h6 class="alert-heading">
                                                                <i
                                                                    class="bx bx-check-circle me-2"></i>{{ localize('global.recovery_details') }}
                                                            </h6>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="discharge_remark{{ $icu->id }}" class="form-label">
                                                                {{ localize('global.discharge_remark') }}
                                                            </label>
                                                            <textarea class="form-control" id="discharge_remark{{ $icu->id }}"
                                                                name="discharge_remark" rows="3"
                                                                placeholder="Enter discharge remarks"></textarea>
                                                            <input type="hidden" name="discharged_at"
                                                                value="{{ \Carbon\Carbon::now() }}">
                                                            <input type="hidden" id="is_discharged_recovered{{ $icu->id }}"
                                                                name="is_discharged" value="1">
                                                        </div>
                                                    </div>

                                                    <!-- Death Options -->
                                                    <div id="died_options{{ $icu->id }}" style="display: none;" class="mt-3">
                                                        <div class="alert alert-danger">
                                                            <h6 class="alert-heading">
                                                                <i
                                                                    class="bx bx-x-circle me-2"></i>{{ localize('global.death_details') }}
                                                            </h6>
                                                        </div>
                                                        <div class="form-group mb-3">
                                                            <label for="cause_of_death{{ $icu->id }}" class="form-label">
                                                                {{ localize('global.cause_of_death') }}
                                                            </label>
                                                            <textarea class="form-control" id="cause_of_death{{ $icu->id }}"
                                                                name="cause_of_death" rows="3"
                                                                placeholder="Enter cause of death"></textarea>
                                                        </div>
                                                        <div class="form-group mb-3">
                                                            <label for="death_date_time{{ $icu->id }}" class="form-label">
                                                                {{ localize('global.death_date_time') }}
                                                            </label>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <input type="text" class="form-control datepicker_dari" name="death_date"
                                                                        id="death_date{{ $icu->id }}">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <input type="time" class="form-control" name="death_time"
                                                                        id="death_time{{ $icu->id }}">
                                                                </div>
                                                            </div>
                                                            <input type="hidden" id="is_discharged_died{{ $icu->id }}"
                                                                name="is_discharged" value="1">
                                                        </div>
                                                    </div>

                                                    <!-- Moved Options -->
                                                    <div id="moved_options{{ $icu->id }}" style="display: none;" class="mt-3">
                                                        <div class="alert alert-warning">
                                                            <h6 class="alert-heading">
                                                                <i
                                                                    class="bx bx-transfer me-2"></i>{{ localize('global.transfer_details') }}
                                                            </h6>
                                                        </div>
                                                        <div class="form-group mb-3">
                                                            <label for="moved_to{{ $icu->id }}" class="form-label">
                                                                {{ localize('global.moved_to') }}
                                                            </label>
                                                            <select class="form-control select2" name="move_department_id"
                                                                id="move_department_id{{ $icu->id }}">
                                                                <option value="">{{ localize('global.select') }}</option>
                                                                @foreach ($departments as $value)
                                                                    <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                        {{ $value->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="form-group mb-3">
                                                            <label for="brief_history{{ $icu->id }}" class="form-label">
                                                                {{ localize('global.brief_history') }}
                                                            </label>
                                                            <textarea class="form-control" id="brief_history{{ $icu->id }}"
                                                                name="brief_history" rows="3"
                                                                placeholder="Enter brief history"></textarea>
                                                            <input type="hidden" id="is_discharged_moved{{ $icu->id }}"
                                                                name="is_discharged" value="1">
                                                            <input type="hidden" name="transfer_date"
                                                                value="{{ \Carbon\Carbon::now() }}">
                                                        </div>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="bx bx-x me-1"></i>{{ localize('global.cancel') }}
                                                </button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bx bx-save me-1"></i>{{ localize('global.save') }}
                                                </button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-2">
                                    @if ($icu->discharge_status == 'recovered')
                                        <div class="row text-center">
                                            <div class="col-md-6">
                                                {{ localize('global.the_patient_has_been_recovered') }}
                                            </div>
                                            <div class="col-md-6">
                                                {{ $icu->discharge_remark }}
                                            </div>
                                        </div>
                                    @elseif($icu->discharge_status == 'died')
                                        <div class="row text-center">
                                            <div class="row mb-4">
                                                <div class="col-md-12">
                                                    {{ localize('global.the_patient_has_been_died') }}
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <h5 class="mb-2">{{ localize('global.cause_of_death') }}</h5>
                                                <div>
                                                    {{ $icu->cause_of_death }}
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <h5 class="mb-2">{{ localize('global.death_date') }}</h5>
                                                <div>
                                                    {{ $icu->death_date }}
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <h5 class="mb-2">{{ localize('global.death_time') }}</h5>
                                                <div>
                                                    {{ $icu->death_time }}
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-2 text-center">
                                                    <a href="{{ route('icus.print-death-card', $icu) }}" target="_blank"
                                                        class="btn btn-primary">{{ localize('global.death_summary') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif ($icu->discharge_status == 'moved')
                                        <div class="row text-center">
                                            <div class="row mb-4">
                                                <div class="col-md-12">
                                                    {{ localize('global.the_patient_has_been_moved') }}
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                {{ $icu->discharge_remark }}
                                            </div>
                                            <div class="row">
                                                <div class="col-md-2 text-center">
                                                    <a href="{{ route('icus.print-move-card', $icu) }}" target="_blank"
                                                        class="btn btn-primary">{{ localize('global.transfer_sheet') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
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

@vite(['public/assets/js/vue/icu-consultation-app.js'])
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
                    data.forEach(function (test) {
                        var checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.name = 'lab_type_id[]'; // Use an array to submit multiple values
                        checkbox.value = test.id;

                        // Update the lab_type_id value when a checkbox is checked/unchecked
                        checkbox.addEventListener('change', function () {
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
        $(document).ready(function () {
            $('#branch').on('change', function () {
                var branchId = $(this).val();
                if (branchId !== '') {
                    $.ajax({
                        url: '/get_departments/' + branchId,
                        type: 'GET',
                        success: function (response) {

                            $('#department').html(response);
                        }
                    })
                }
            });

            $('#department').on('change', function () {
                var departmentId = $(this).val();
                if (departmentId !== '') {
                    $.ajax({
                        url: '/get_doctors/' + departmentId,
                        type: 'GET',
                        success: function (response) {

                            $('#doctor_id').html(response);
                        }
                    })
                }
            });
            $('#room_id').on('change', function () {
                var roomId = $(this).val();
                if (roomId !== '') {
                    $.ajax({
                        url: '/get_related_beds/' + roomId,
                        type: 'GET',
                        success: function (response) {

                            $('#bed_id').html(response);
                        }
                    })
                }
            });
            $('#discharge_status{{ $icu->id }}').on('change', function () {
                var selectedDischargeStatus = $(this).val();
                var dischargeOptionsContainer = $('#discharge_options{{ $icu->id }}');
                var diedOptionsContainer = $('#died_options{{ $icu->id }}');
                var movedOptionsContainer = $('#moved_options{{ $icu->id }}');

                // Hide all option containers
                dischargeOptionsContainer.hide();
                diedOptionsContainer.hide();
                movedOptionsContainer.hide();

                // Remove required attributes from all fields
                $('#death_date{{ $icu->id }}, #death_time{{ $icu->id }}').removeAttr('required');
                $('#move_department_id{{ $icu->id }}').removeAttr('required');

                if (selectedDischargeStatus === 'recovered') {
                    dischargeOptionsContainer.show();
                } else if (selectedDischargeStatus === 'died') {
                    diedOptionsContainer.show();
                    // Add required to death fields
                    $('#death_date{{ $icu->id }}, #death_time{{ $icu->id }}').attr('required', 'required');
                } else if (selectedDischargeStatus === 'moved') {
                    movedOptionsContainer.show();
                    // Add required to move department field
                    $('#move_department_id{{ $icu->id }}').attr('required', 'required');
                }
            });

            // Form submission handler
            $('form[action*="icus.update"]').on('submit', function(e) {
                console.log('Form submission triggered');
                var selectedDischargeStatus = $('#discharge_status{{ $icu->id }}').val();
                console.log('Selected discharge status:', selectedDischargeStatus);
                
                if (!selectedDischargeStatus) {
                    e.preventDefault();
                    alert('Please select a discharge status');
                    return false;
                }

                if (selectedDischargeStatus === 'died') {
                    var deathDate = $('#death_date{{ $icu->id }}').val();
                    var deathTime = $('#death_time{{ $icu->id }}').val();
                    console.log('Death date:', deathDate, 'Death time:', deathTime);
                    if (!deathDate || !deathTime) {
                        e.preventDefault();
                        alert('Please fill in death date and time');
                        return false;
                    }
                }

                if (selectedDischargeStatus === 'moved') {
                    var moveDepartment = $('#move_department_id{{ $icu->id }}').val();
                    console.log('Move department:', moveDepartment);
                    if (!moveDepartment) {
                        e.preventDefault();
                        alert('Please select a department to move to');
                        return false;
                    }
                }

                console.log('Form validation passed, submitting...');
            });
        })
    </script>
    <script>
        // Get the add button and prescription input container
        const addButton = document.getElementById('addPrescriptionInput');
        const prescriptionContainer = document.getElementById('prescription-input-container');

        // Add click event listener to the add button
        function addRow() {
            // Create a new row div
            const newRow = document.createElement('div');
            newRow.className = 'row';

            // Create the type dropdown
            const typeDropdown = document.createElement('select');
            typeDropdown.className = 'form-control select2 mt-2';
            typeDropdown.name = 'medicine_type_id[]';
            typeDropdown.required = true;

            // Append the options to the type dropdown
            @foreach ($medicineTypes as $value)
                typeOption = document.createElement('option');
                typeOption.value = '{{ $value->id }}';
                typeOption.textContent = '{{ $value->type }}';
                typeDropdown.appendChild(typeOption);
            @endforeach

                    // Create the medicine dropdown
                    const medicineDropdown = document.createElement('select');
            medicineDropdown.className = 'form-control select2 mt-2';
            medicineDropdown.name = 'medicine_id[]';
            medicineDropdown.required = true;

            // Append the options to the medicine dropdown
            var medicineOption = '';
            @foreach ($medicines as $value)
                medicineOption = document.createElement('option');
                medicineOption.value = '{{ $value->id }}';
                medicineOption.textContent = '{{ $value->name }}';
                medicineDropdown.appendChild(medicineOption);
            @endforeach

                    // Create the medicine dropdown
                    const medicineUsageDropdown = document.createElement('select');
            medicineUsageDropdown.className = 'form-control select2 mt-2';
            medicineUsageDropdown.name = 'usage_type_id[]';
            medicineUsageDropdown.required = true;

            // Append the options to the medicine dropdown
            var medicineUsageOption = '';
            @foreach ($medicineUsageTypes as $value)
                medicineUsageOption = document.createElement('option');
                medicineUsageOption.value = '{{ $value->id }}';
                medicineUsageOption.textContent = '{{ $value->name }}';
                medicineUsageDropdown.appendChild(medicineUsageOption);
            @endforeach

                    // Create the dosage input field
                    const dosageInput = document.createElement('input');
            dosageInput.type = 'text';
            dosageInput.className = 'form-control mt-2';
            dosageInput.name = 'dosage[]';
            dosageInput.placeholder = 'Dosage';
            dosageInput.required = true;

            // Create the frequency input field
            const frequencyInput = document.createElement('input');
            frequencyInput.type = 'text';
            frequencyInput.className = 'form-control mt-2';
            frequencyInput.name = 'frequency[]';
            frequencyInput.placeholder = 'Frequency';
            frequencyInput.required = true;

            // Create the amount input field
            const amountInput = document.createElement('input');
            amountInput.type = 'text';
            amountInput.className = 'form-control mt-2';
            amountInput.name = 'amount[]';
            amountInput.placeholder = 'Amount';
            amountInput.required = true;

            // Create the delivery input field
            const deliveryInput = document.createElement('input');
            deliveryInput.type = 'hidden';
            deliveryInput.className = 'form-control mt-2';
            deliveryInput.name = 'is_delivered[]';
            deliveryInput.value = 0;

            // Create the column divs
            const typeCol = document.createElement('div');
            typeCol.className = 'col-md-2';
            const medicineCol = document.createElement('div');
            medicineCol.className = 'col-md-2';
            const medicineUsageCol = document.createElement('div');
            medicineUsageCol.className = 'col-md-2';
            const dosageCol = document.createElement('div');
            dosageCol.className = 'col-md-2';
            const frequencyCol = document.createElement('div');
            frequencyCol.className = 'col-md-2';
            const amountCol = document.createElement('div');
            amountCol.className = 'col-md-2';
            const deliveryCol = document.createElement('div');
            deliveryCol.className = 'col-md-2';

            // Append the input fields to their respective column divs
            typeCol.appendChild(typeDropdown);
            medicineCol.appendChild(medicineDropdown);
            medicineUsageCol.appendChild(medicineUsageDropdown);
            dosageCol.appendChild(dosageInput);
            frequencyCol.appendChild(frequencyInput);
            amountCol.appendChild(amountInput);
            deliveryCol.appendChild(deliveryInput);

            // Append the column divs to the new row div
            newRow.appendChild(typeCol);
            newRow.appendChild(medicineCol);
            newRow.appendChild(medicineUsageCol);
            newRow.appendChild(dosageCol);
            newRow.appendChild(frequencyCol);
            newRow.appendChild(amountCol);
            newRow.appendChild(deliveryCol);

            // Append the new row div to the prescription input container
            prescriptionContainer.appendChild(newRow);

            // Initialize the select2 plugin
            $('select').select2({
                dropdownParent: $('#createPrescriptionModal1')
            });

        }
    </script>

    <script>
        function getPrescriptionItems(id) {
            $.ajax({
                type: "GET",
                url: "{{ url('prescription_items/getItems/') }}/" + id,
                dataType: "html",
                success: function (data) {
                    $('#prescription_items_table').html(data);
                },
                error: function (xhr, status, error) {
                    // Handle the error response
                    console.error(error);
                }
            });
        }
    </script>
@endsection