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
                                    {{ localize('global.hospitalization_details') }}
                                </h5>

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
                                            {{ verta($hospitalization->created_at)->format('Y-m-d') }}
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

                            <!-- Visits Accordion -->
                            <div class="accordion" id="visitsAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="visitsHeading">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#visitsCollapse" aria-expanded="true" aria-controls="visitsCollapse">
                                            <i class="bx bx-glasses p-1 me-2"></i>{{ localize('global.visits') }}
                                        </button>
                                    </h2>
                                    <div id="visitsCollapse" class="accordion-collapse collapse show" aria-labelledby="visitsHeading"
                                        data-bs-parent="#visitsAccordion">
                                        <div class="accordion-body">
                                            @if ($hospitalization->is_discharged == 0)
                                                <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal"
                                                    data-bs-target="#createVisitModal{{ $hospitalization->id }}"><span><i
                                                            class="bx bx-plus"></i></span></button>
                                            @endif
                            <!-- Create visit Modal -->
                            <div class="modal fade" id="createVisitModal{{ $hospitalization->id }}" tabindex="-1"
                                aria-labelledby="createVisitModalLabel{{ $hospitalization->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createVisitModalLabel{{ $hospitalization->id }}">
                                                {{ localize('global.add_visit') }}
                                            </h5>
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
                                                    <textarea class="form-control"
                                                        id="description{{ $hospitalization->id }}" name="description"
                                                        rows="3"></textarea>
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
                                                    <div class="row mt-1 mb-1">
                                                        <div class="col-md-6">
                                                            <label
                                                                for="antibiotic{{ $hospitalization->id }}">{{ localize('global.antibiotic') }}</label>
                                                            <input type="text" class="form-control" name="antibiotic" />
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label
                                                                for="food_type_id{{ $hospitalization->id }}">{{ localize('global.food_type') }}</label>
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
                                                                for="intake{{ $hospitalization->id }}">{{ localize('global.intake') }}</label>
                                                            <input type="text" class="form-control" name="intake" />
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label
                                                                for="output{{ $hospitalization->id }}">{{ localize('global.output') }}</label>
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
                                        @forelse ($hospitalization->visits as $visit)
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
                                    </div>
                                </div>
                            </div>
                            <!-- End Visits Accordion -->

                            <!-- Vital Signs Accordion -->
                            <div class="accordion mt-4" id="vitalSignsAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="vitalSignsHeading">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#vitalSignsCollapse" aria-expanded="false" aria-controls="vitalSignsCollapse">
                                            <i class="bx bx-heart p-1 me-2"></i>{{ localize('global.vital_signs') }}
                                        </button>
                                    </h2>
                                    <div id="vitalSignsCollapse" class="accordion-collapse collapse" aria-labelledby="vitalSignsHeading"
                                        data-bs-parent="#vitalSignsAccordion">
                                        <div class="accordion-body">
                                            <div id="vital-signs-section"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Vital Signs Accordion -->

                            <!-- Prescription Accordion -->
                            <div class="accordion mt-4" id="prescriptionAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="prescriptionHeading">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#prescriptionCollapse" aria-expanded="false" aria-controls="prescriptionCollapse">
                                            <i class="bx bx-notepad p-1 me-2"></i>{{ localize('global.prescription') }}
                                        </button>
                                    </h2>
                                    <div id="prescriptionCollapse" class="accordion-collapse collapse" aria-labelledby="prescriptionHeading"
                                        data-bs-parent="#prescriptionAccordion">
                                        <div class="accordion-body">
                                            <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal"
                                                data-bs-target="#createPrescriptionModal{{ $hospitalization->id }}"><span><i
                                                        class="bx bx-plus"></i></span></button>

                            <!-- Create Diagnose Modal -->
                            <div class="modal fade modal-xl" id="createPrescriptionModal{{ $hospitalization->id }}"
                                tabindex="-1" aria-labelledby="createPrescriptionModalLabel{{ $hospitalization->id }}"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"
                                                id="createPrescriptionModalLabel{{ $hospitalization->id }}">
                                                {{ localize('global.add_prescription') }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('prescriptions.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" id="patient_id{{ $hospitalization->patient_id }}"
                                                    name="patient_id" value="{{ $hospitalization->patient_id }}">
                                                <input type="hidden"
                                                    id="appointment_id{{ $hospitalization->appointment->id }}"
                                                    name="appointment_id" value="{{ $hospitalization->appointment->id }}">
                                                <input type="hidden" id="branch_id{{ $hospitalization->id }}"
                                                    name="branch_id" value="{{ auth()->user()->branch_id }}">
                                                <input type="hidden" id="doctor_id{{ $hospitalization->id }}"
                                                    name="doctor_id" value="{{ auth()->user()->id }}">
                                                <input type="hidden" id="hospitalization_id{{ $hospitalization->id }}"
                                                    name="hospitalization_id" value="{{ $hospitalization->id }}">

                                                <!-- Add other diagnosis form fields as needed -->
                                                <div class="form-group" id="prescription-items">
                                                    <label>{{ localize('global.description') }}</label>
                                                    <div id="prescription-input-container">
                                                        <div class="row">
                                                            <div class="col-md-2">
                                                                <select class="form-control select2"
                                                                    name="medicine_type_id[]">
                                                                    <option value="">{{ localize('global.select') }}
                                                                    </option>
                                                                    @foreach ($medicineTypes as $value)
                                                                        <option value="{{ $value->id }}" {{ old('type') == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->type }}

                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <select class="form-control select2" name="medicine_id[]">
                                                                    <option value="">{{ localize('global.select') }}
                                                                    </option>
                                                                    @foreach ($medicines as $value)
                                                                        <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->name }}

                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <select class="form-control select2" name="usage_type_id[]">
                                                                    <option value="">{{ localize('global.select') }}
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
                                                                    placeholder="Dosage">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control mt-2"
                                                                    name="frequency[]" placeholder="Frequency">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control mt-2" name="amount[]"
                                                                    placeholder="Amount">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <input type="hidden" class="form-control mt-2"
                                                                    name="is_delivered[]" value="0">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <button type="button" class="btn btn-primary mt-2" id="addPrescriptionInput"
                                                    onclick="addRow()">
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

                            <div class="col-md-12 mt-4">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.number') }}</th>
                                            <th>{{ localize('global.patient_name') }}</th>
                                            <th>{{ localize('global.status') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($hospitalization->prescription as $prescription)
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
                                                        data-bs-target="#showPrescriptionItemModal"><span><i
                                                                class="bx bx-expand"></i></span></a>
                                                </td>
                                            </tr>
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


                            <div class="modal fade modal-xl" id="showPrescriptionModal{{ $hospitalization->id }}"
                                tabindex="-1" aria-labelledby="showPrescriptionModalLabel{{ $hospitalization->id }}"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"
                                                id="showPrescriptionModalLabel{{ $hospitalization->id }}">
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
                                                    @if ($hospitalization->prescription)
                                                        @foreach ($hospitalization->prescription as $pres_list)
                                                            <tr>
                                                                <td>{{ $loop->iteration }}</td>
                                                                <td>{{ verta($pres_list->created_at)->format('Y-m-d H:i') }}</td>
                                                                <td>{{ $pres_list->is_completed }}</td>
                                                                <td>
                                                                    <a href="#" data-bs-toggle="modal"
                                                                        onclick="getPrescriptionItems({{ $pres_list->id }})"
                                                                        data-bs-target="#showPrescriptionItemModal"><span><i
                                                                                class="bx bx-expand"></i></span></a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
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
                                        </div>
                                    </div>
                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Prescription Accordion -->

                            <!-- Diabetes Charts Accordion -->
                            <div class="accordion mt-4" id="diabetesChartsAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="diabetesChartsHeading">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#diabetesChartsCollapse" aria-expanded="false" aria-controls="diabetesChartsCollapse">
                                            <i class="bx bx-line-chart p-1 me-2"></i>{{ localize('global.diabetes_charts') }}
                                        </button>
                                    </h2>
                                    <div id="diabetesChartsCollapse" class="accordion-collapse collapse" aria-labelledby="diabetesChartsHeading"
                                        data-bs-parent="#diabetesChartsAccordion">
                                        <div class="accordion-body">
                                            <div id="diabetes-charts-section"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Diabetes Charts Accordion -->

                            <!-- Nursing Notes Accordion -->
                            <div class="accordion mt-4" id="nursingNotesAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="nursingNotesHeading">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#nursingNotesCollapse" aria-expanded="false" aria-controls="nursingNotesCollapse">
                                            <i class="bx bx-note p-1 me-2"></i>{{ localize('global.nursing_notes') }}
                                        </button>
                                    </h2>
                                    <div id="nursingNotesCollapse" class="accordion-collapse collapse" aria-labelledby="nursingNotesHeading"
                                        data-bs-parent="#nursingNotesAccordion">
                                        <div class="accordion-body">
                                            <div id="nursing-note-section"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Nursing Notes Accordion -->

                            <!-- Medication Administration Records Accordion -->
                            <div class="accordion mt-4" id="medicationRecordsAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="medicationRecordsHeading">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#medicationRecordsCollapse" aria-expanded="false" aria-controls="medicationRecordsCollapse">
                                            <i class="bx bx-pill p-1 me-2"></i>{{ localize('global.medication_administration_records') }}
                                        </button>
                                    </h2>
                                    <div id="medicationRecordsCollapse" class="accordion-collapse collapse" aria-labelledby="medicationRecordsHeading"
                                        data-bs-parent="#medicationRecordsAccordion">
                                        <div class="accordion-body">
                                            <div id="medication-administration-records-section"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Medication Administration Records Accordion -->

                            <!-- Nutrition Care Accordion -->
                            <div class="accordion mt-4" id="nutritionCareAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="nutritionCareHeading">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#nutritionCareCollapse" aria-expanded="false" aria-controls="nutritionCareCollapse">
                                            <i class="bx bx-food-menu p-1 me-2"></i>{{ localize('global.nutrition_care') }}
                                        </button>
                                    </h2>
                                    <div id="nutritionCareCollapse" class="accordion-collapse collapse" aria-labelledby="nutritionCareHeading"
                                        data-bs-parent="#nutritionCareAccordion">
                                        <div class="accordion-body">
                                            <div id="nutrition-care-section"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Nutrition Care Accordion -->

                            <!-- Advice Accordion -->
                            <div class="accordion mt-4" id="adviceAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="adviceHeading">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#adviceCollapse" aria-expanded="false" aria-controls="adviceCollapse">
                                            <i class="bx bx-command p-1 me-2"></i>{{ localize('global.advice') }}
                                        </button>
                                    </h2>
                                    <div id="adviceCollapse" class="accordion-collapse collapse" aria-labelledby="adviceHeading"
                                        data-bs-parent="#adviceAccordion">
                                        <div class="accordion-body">
                                            @if ($hospitalization->is_discharged == 0)
                                                <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal"
                                                    data-bs-target="#createAdviceModal{{ $hospitalization->id }}"><span><i
                                                            class="bx bx-plus"></i></span></button>
                                            @endif
                            <!-- Create Diagnose Modal -->
                            <div class="modal fade" id="createAdviceModal{{ $hospitalization->id }}" tabindex="-1"
                                aria-labelledby="createAdviceModalLabel{{ $hospitalization->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createAdviceModalLabel{{ $hospitalization->id }}">
                                                {{ localize('global.add_advice') }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('advices.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" id="patient_id{{ $hospitalization->patient_id }}"
                                                    name="patient_id" value="{{ $hospitalization->patient_id }}">
                                                <input type="hidden"
                                                    id="appointment_id{{ $hospitalization->appointment->id }}"
                                                    name="appointment_id" value="{{ $hospitalization->id }}">
                                                <input type="hidden" id="doctor_id{{ $hospitalization->id }}"
                                                    name="doctor_id" value="{{ auth()->user()->id }}">
                                                <input type="hidden" id="hospitalization_id{{ $hospitalization->id }}"
                                                    name="hospitalization_id" value="{{ $hospitalization->id }}">
                                                <!-- Add other diagnosis form fields as needed -->
                                                <div class="form-group">

                                                    <label
                                                        for="description{{ $hospitalization->id }}">{{ localize('global.description') }}</label>
                                                    <textarea class="form-control"
                                                        id="description{{ $hospitalization->id }}" name="description"
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
                                        @forelse ($hospitalization->advices as $advice)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $advice->description }}</td>
                                                <td>
                                                    {{ $advice->doctor->name }}
                                                </td>
                                                <td dir="ltr">{{ verta($advice->created_at)->format('Y-m-d H:i') }}</td>
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
                                    </div>
                                </div>
                            </div>
                            <!-- End Advice Accordion -->

                            {{-- lab tests from hospitalization --}}

                            <!-- Lab Tests Accordion -->
                            <div class="accordion mt-4" id="labTestsAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="labTestsHeading">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#labTestsCollapse" aria-expanded="false" aria-controls="labTestsCollapse">
                                            <i class="bx bx-hard-hat p-1 me-2"></i>{{ localize('global.hospitalization_checkups') }}
                                        </button>
                                    </h2>
                                    <div id="labTestsCollapse" class="accordion-collapse collapse" aria-labelledby="labTestsHeading"
                                        data-bs-parent="#labTestsAccordion">
                                        <div class="accordion-body">
                                            @if ($hospitalization->is_discharged == 0)
                                                <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal"
                                                    data-bs-target="#createLabModal{{ $hospitalization->id }}"><span><i
                                                            class="bx bx-plus"></i></span></button>
                                            @endif
                            <!-- Create  Lab Modal -->
                            <div class="modal fade" id="createLabModal{{ $hospitalization->id }}" tabindex="-1"
                                aria-labelledby="createLabModalLabel{{ $hospitalization->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createLabModalLabel{{ $hospitalization->id }}">
                                                {{ localize('global.add_lab_test') }}
                                            </h5>
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
                                                <input type="hidden" id="status{{ $hospitalization->id }}" name="status"
                                                    value="0">
                                                <input type="hidden" id="hospitalization_id{{ $hospitalization->id }}"
                                                    name="hospitalization_id" value="{{ $hospitalization->id }}">
                                                <div class="form-group">

                                                    <label
                                                        for="lab_type_section{{ $hospitalization->id }}">{{ localize('global.lab_type_section') }}</label>
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
                                                        for="lab_type_id{{ $hospitalization->id }}">{{ localize('global.lab_type') }}</label>
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
                                    </div>
                                </div>
                            </div>
                            <!-- End Lab Tests Accordion -->

                            {{-- end lab tests from hospitalization --}}
                            {{-- icu starts here --}}
                            <!-- ICU Accordion -->
                            <div class="accordion mt-4" id="icuAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="icuHeading">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#icuCollapse" aria-expanded="false" aria-controls="icuCollapse">
                                            <i class="bx bx-tv p-1 me-2"></i>{{ localize('global.refere_to_icu') }}
                                        </button>
                                    </h2>
                                    <div id="icuCollapse" class="accordion-collapse collapse" aria-labelledby="icuHeading"
                                        data-bs-parent="#icuAccordion">
                                        <div class="accordion-body">
                                            @if ($hospitalization->is_discharged == 0)
                                                <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal"
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
                                                {{ localize('global.refere_to_icu') }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('icus.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" id="patient_id{{ $hospitalization->patient_id }}"
                                                    name="patient_id" value="{{ $hospitalization->patient_id }}">
                                                <input type="hidden" id="appointment_id{{ $hospitalization->id }}"
                                                    name="appointment_id" value="{{ $hospitalization->appointment->id }}">
                                                <input type="hidden" id="hospitalization_id{{ $hospitalization->id }}"
                                                    name="hospitalization_id" value="{{ $hospitalization->id }}">
                                                <input type="hidden" id="doctor_id{{ $hospitalization->id }}"
                                                    name="doctor_id" value="{{ auth()->user()->id }}">
                                                <input type="hidden" id="branch_id{{ $hospitalization->id }}"
                                                    name="branch_id" value="{{ auth()->user()->branch_id }}">

                                                <div class="form-group">

                                                    <div class="form-group">
                                                        <label
                                                            for="description{{ $hospitalization->id }}">{{ localize('global.description') }}</label>
                                                        <textarea class="form-control"
                                                            id="description{{ $hospitalization->id }}" name="description"
                                                            rows="3"></textarea>
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
                                                    {{ verta($icu->created_at)->format('Y-m-d H:i') }}
                                                </td>
                                                <td>
                                                    <a href="{{ route('icus.edit', $icu->id) }}"><span><i
                                                                class="bx bx-edit"></i></span></a>
                                                    <a href="{{ route('icus.destroy', $icu->id) }}"><span><i
                                                                class="bx bx-trash text-danger"></i></span></a>

                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5">
                                                    <div class="container">
                                                        <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                            <div class=" badge bg-label-danger mt-4">
                                                                {{ localize('global.not_referred_to_icu') }}
                                                            </div>
                                                        </div>
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
                            <!-- End ICU Accordion -->

                            <!-- Anesthesia Accordion -->
                            <div class="accordion mt-4" id="anesthesiaAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="anesthesiaHeading">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#anesthesiaCollapse" aria-expanded="false" aria-controls="anesthesiaCollapse">
                                            <i class="bx bx-first-aid p-1 me-2"></i>{{ localize('global.refere_to_anasthesia') }}
                                        </button>
                                    </h2>
                                    <div id="anesthesiaCollapse" class="accordion-collapse collapse" aria-labelledby="anesthesiaHeading"
                                        data-bs-parent="#anesthesiaAccordion">
                                        <div class="accordion-body">
                                            @if ($hospitalization->is_discharged == 0)
                                                <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal"
                                                    data-bs-target="#createAnasthesiaModal{{ $hospitalization->id }}"><span><i
                                                            class="bx bx-plus"></i></span></button>
                                            @endif
                            <!-- Create  Lab Modal -->
                            <div class="modal fade" id="createAnasthesiaModal{{ $hospitalization->id }}" tabindex="-1"
                                aria-labelledby="createAnasthesiaModalLabel{{ $hospitalization->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"
                                                id="createAnasthesiaModalLabel{{ $hospitalization->id }}">
                                                {{ localize('global.refere_to_anasthesia') }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('anesthesias.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" id="patient_id{{ $hospitalization->patient_id }}"
                                                    name="patient_id" value="{{ $hospitalization->patient_id }}">
                                                <input type="hidden" id="appointment_id{{ $hospitalization->id }}"
                                                    name="appointment_id" value="{{ $hospitalization->appointment->id }}">
                                                <input type="hidden" id="hospitalization_id{{ $hospitalization->id }}"
                                                    name="hospitalization_id" value="{{ $hospitalization->id }}">
                                                <input type="hidden" id="doctor_id{{ $hospitalization->id }}"
                                                    name="doctor_id" value="{{ auth()->user()->id }}">
                                                <input type="hidden" id="branch_id{{ $hospitalization->id }}"
                                                    name="branch_id" value="{{ auth()->user()->branch_id }}">

                                                <div class="form-group">

                                                    <div class="form-group">
                                                        <label
                                                            for="plan{{ $hospitalization->id }}">{{ localize('global.plan') }}</label>
                                                        <textarea class="form-control" id="plan{{ $hospitalization->id }}"
                                                            name="plan" rows="3"></textarea>
                                                    </div>

                                                    <h5 class="mt-2">{{ localize('global.operation_team') }}</h5>
                                                    {{-- <select class="form-control select2" name="operation_doctor_id[]"
                                                        id="operation_doctor_id" multiple>
                                                        <option value="">{{ localize('global.select') }}</option>
                                                        @foreach ($operation_doctors as $value)
                                                        <option value="{{ $value->id }}" {{ old('name')==$value->id ?
                                                            'selected' : '' }}>
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
                                                                        <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
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
                                                                        <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
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
                                                        <textarea class="form-control"
                                                            id="other_problems{{ $hospitalization->id }}"
                                                            name="other_problems" rows="3"></textarea>
                                                    </div>


                                                    <label for="operation_type_id{{ $hospitalization->id }}"
                                                        class="mt-2 mb-2">{{ localize('global.operation_type') }}</label>
                                                    <select class="form-control select2" name="operation_type_id">
                                                        <option value="">{{ localize('global.select') }}</option>
                                                        @foreach ($operationTypes as $value)
                                                            <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                {{ $value->name }}

                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <div>
                                                        <label for="date"
                                                            class="mt-2 mb-2">{{ localize('global.date') }}</label>
                                                        <input type="text" class="form-control form-control datepicker_dari pdp-el" name="date" />
                                                    </div>
                                                    <div>
                                                        <label for="time"
                                                            class="mt-2 mb-2">{{ localize('global.time') }}</label>
                                                        <input type="time" class="form-control" name="time" />
                                                    </div>
                                                    <div>
                                                        <label for="planned_duration"
                                                            class="mt-2 mb-2">{{ localize('global.planned_duration') }}</label>
                                                        <input type="text" class="form-control" name="planned_duration" />
                                                    </div>
                                                    <div>
                                                        <label for="position_on_bed"
                                                            class="mt-2 mb-2">{{ localize('global.position_on_bed') }}</label>
                                                        <input type="text" class="form-control" name="position_on_bed" />
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
                                                <td>{{ $anesthesia->operationType?->name ?? 'N/A' }}</td>
                                                <td>
                                                    {{ $anesthesia->patient?->name ?? 'N/A' }}
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
                                                    {{ $anesthesia->date ? verta($anesthesia->date)->format('Y-m-d') : 'N/A' }}
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
                                    </div>
                                </div>
                            </div>
                            <!-- End Anesthesia Accordion -->

                            <!-- Complaint Accordion -->
                            <div class="accordion mt-4" id="complaintAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="complaintHeading">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#complaintCollapse" aria-expanded="false" aria-controls="complaintCollapse">
                                            <i class="bx bx-walk p-1 me-2"></i>{{ localize('global.create_complaint') }}
                                        </button>
                                    </h2>
                                    <div id="complaintCollapse" class="accordion-collapse collapse" aria-labelledby="complaintHeading"
                                        data-bs-parent="#complaintAccordion">
                                        <div class="accordion-body">
                            @if ($hospitalization->is_discharged == 0)
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#createComplaintModal{{ $hospitalization->id }}"><span><i
                                            class="bx bx-plus"></i></span></button>
                            @endif
                            <!-- Create  Lab Modal -->
                            <div class="modal fade" id="createComplaintModal{{ $hospitalization->id }}" tabindex="-1"
                                aria-labelledby="createComplaintModalLabel{{ $hospitalization->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"
                                                id="createComplaintModalLabel{{ $hospitalization->id }}">
                                                {{ localize('global.add_complaint') }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('complaints.store', $hospitalization) }}" method="POST">
                                                @csrf

                                                <input type="hidden" id="hospitalization_id{{ $hospitalization->id }}"
                                                    name="hospitalization_id" value="{{ $hospitalization->id }}">
                                                <div class="form-group">
                                                    <label
                                                        for="description{{ $hospitalization->id }}">{{ localize('global.description') }}</label>
                                                    <textarea class="form-control"
                                                        id="description{{ $hospitalization->id }}" name="description"
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
                                                    {{ verta($complaint->created_at)->format('Y-m-d H:i') }}
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
                                    </div>
                                </div>
                            </div>
                            <!-- End Complaint Accordion -->

                            {{-- discharge --}}
                            <!-- Discharge Accordion -->
                            <div class="accordion mt-4" id="dischargeAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="dischargeHeading">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#dischargeCollapse" aria-expanded="false" aria-controls="dischargeCollapse">
                                            <i class="bx bx-walk p-1 me-2"></i>{{ localize('global.discharge_patient') }}
                                        </button>
                                    </h2>
                                    <div id="dischargeCollapse" class="accordion-collapse collapse" aria-labelledby="dischargeHeading"
                                        data-bs-parent="#dischargeAccordion">
                                        <div class="accordion-body">
                                            @if ($hospitalization->is_discharged == 0)
                                                <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal"
                                                    data-bs-target="#createDischargeModal{{ $hospitalization->id }}"><span><i
                                                            class="bx bx-plus"></i></span></button>
                                            @endif
                            <!-- Create  Lab Modal -->
                            <div class="modal fade" id="createDischargeModal{{ $hospitalization->id }}" tabindex="-1"
                                aria-labelledby="createDischargeModalLabel{{ $hospitalization->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"
                                                id="createDischargeModalLabel{{ $hospitalization->id }}">
                                                {{ localize('global.add_lab_test') }}
                                            </h5>
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
                                                    <textarea class="form-control"
                                                        id="discharge_remark{{ $hospitalization->id }}"
                                                        name="discharge_remark" rows="3"></textarea>
                                                    <input type="hidden" name="discharged_at"
                                                        value="{{ \Carbon\Carbon::now() }}">
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

                            <!-- Create Nutrition Care Modal -->
                            <div class="modal fade modal-xl" id="createNutritionCareModal" tabindex="-1"
                                aria-labelledby="createNutritionCareModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createNutritionCareModalLabel">
                                                {{ localize('global.create_nutrition_care') }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form id="createNutritionCareForm" action="{{ route('nutrition-cares.store') }}"
                                            method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                @php
                                                    $nurses = \App\Models\Nurse::all();
                                                    $morphable_type = 'App\Models\Hospitalization';
                                                    $morphable_id = $hospitalization->id;
                                                    $patient_name = $hospitalization->patient->first_name . ' ' . $hospitalization->patient->last_name;
                                                @endphp
                                                @include('pages.nutrition-cares.partials.form')
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                                <button type="submit" class="btn btn-primary"
                                                    id="submitNutritionCareBtn">{{ localize('global.create') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- End Create Nutrition Care Modal -->

                            <!-- Nursing Assessment Section -->
                            <div id="nursing-assessment-section">
                            </div>
                            <!-- End Create Nursing Assessment Modal -->

                                            <div class="col-md-12 mt-4">
                                                {{ $hospitalization->discharge_remark }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Discharge Accordion -->
                            {{-- end discharge --}}
                        </div>

                    </div>


                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Simple borders for accordion sections */
        #visitsAccordion .accordion-item { border: 2px solid #007bff; }
        #vitalSignsAccordion .accordion-item { border: 2px solid #dc3545; }
        #prescriptionAccordion .accordion-item { border: 2px solid #28a745; }
        #diabetesChartsAccordion .accordion-item { border: 2px solid #fd7e14; }
        #nursingNotesAccordion .accordion-item { border: 2px solid #6f42c1; }
        #medicationRecordsAccordion .accordion-item { border: 2px solid #20c997; }
        #nutritionCareAccordion .accordion-item { border: 2px solid #e83e8c; }
        #adviceAccordion .accordion-item { border: 2px solid #6610f2; }
        #labTestsAccordion .accordion-item { border: 2px solid #ffc107; }
        #icuAccordion .accordion-item { border: 2px solid #0b5ed7; }
        #anesthesiaAccordion .accordion-item { border: 2px solid #157347; }
        #complaintAccordion .accordion-item { border: 2px solid #b02a37; }
        #dischargeAccordion .accordion-item { border: 2px solid #6c757d; }
    </style>
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
            typeDropdown.className = 'form-control select2';
            typeDropdown.name = 'medicine_type_id[]';

            // Append the options to the type dropdown
            @foreach ($medicineTypes as $value)
                typeOption = document.createElement('option');
                typeOption.value = '{{ $value->id }}';
                typeOption.textContent = '{{ $value->type }}';
                typeDropdown.appendChild(typeOption);
            @endforeach

                            // Create the medicine dropdown
                            const medicineDropdown = document.createElement('select');
            medicineDropdown.className = 'form-control select2';
            medicineDropdown.name = 'medicine_id[]';

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
            medicineUsageDropdown.className = 'form-control select2';
            medicineUsageDropdown.name = 'usage_type_id[]';

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

        // Handle Nutrition Care form submission with AJAX
        $(document).ready(function () {
            $('#createNutritionCareForm').on('submit', function (e) {
                e.preventDefault();

                var form = $(this);
                var submitBtn = $('#submitNutritionCareBtn');
                var originalText = submitBtn.text();

                // Disable submit button and show loading
                submitBtn.prop('disabled', true).text('{{ localize("global.creating") }}...');

                $.ajax({
                    type: 'POST',
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function (response) {
                        // Close modal
                        $('#createNutritionCareModal').modal('hide');

                        // Reload the nutrition care section
                        reloadNutritionCareSection();

                        // Show success message
                        toastr.success('{{ localize("global.nutrition_care_created_successfully") }}');

                        // Reset form
                        form[0].reset();
                    },
                    error: function (xhr) {
                        // Handle validation errors
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorMessages = [];

                            for (var field in errors) {
                                errorMessages.push(errors[field][0]);
                            }

                            toastr.error(errorMessages.join('<br>'));
                        } else {
                            toastr.error('{{ localize("global.error_occurred") }}');
                        }
                    },
                    complete: function () {
                        // Re-enable submit button
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });
        });

        // Function to reload nutrition care section
        function reloadNutritionCareSection() {
            $.ajax({
                type: "GET",
                url: "{{ route('nutrition-cares.by-morphable', ['App\\Models\\Hospitalization', $hospitalization->id]) }}",
                dataType: "html",
                success: function (data) {
                    // Find and replace the nutrition care section
                    var nutritionCareSection = $(data).find('#nutrition-care-section');
                    if (nutritionCareSection.length > 0) {
                        $('#nutrition-care-section').replaceWith(nutritionCareSection);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error reloading nutrition care section:', error);
                    // Fallback: reload the entire page
                    location.reload();
                }
            });
        }

        // Delete Nursing Assessment function
        function deleteNursingAssessment(assessmentId) {
            if (confirm('{{ localize("global.are_you_sure_delete_nursing_assessment") }}')) {
                fetch(`/nursing-assessments/${assessmentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message) {
                            // Show success message
                            alert(data.message);
                            // Reload the page to refresh the data
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('{{ localize("global.error_deleting_nursing_assessment") }}');
                    });
            }
        }
        $(document).ready(function () {
            // Load all sections via AJAX
            $('#nursing-assessment-section').load('{{ route('nursing-assessments.section', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}');
            $('#nursing-note-section').load('{{ route('nurse-notes.section', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}');
            $('#diabetes-charts-section').load('{{ route('hospitalizations.diabetes-charts-section', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}');
            $('#medication-administration-records-section').load('{{ route('hospitalizations.medication-administration-records-section', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}');
            $('#vital-signs-section').load('{{ route('hospitalizations.vital-signs-section', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}');
            $('#nutrition-care-section').load('{{ route('hospitalizations.nutrition-care-section', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}');
        });




    </script>

@endsection