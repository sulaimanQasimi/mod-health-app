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
                            @if ($hospitalization->is_discharged == 0)
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
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





                            <!-- Vital Signs Management Section -->
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-heart p-1"></i>{{ localize('global.vital_signs') }}</h5>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    @can('create', App\Models\VitalSign::class)
                                        <a href="{{ route('vital-signs.create', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}"
                                            class="btn btn-primary">
                                            <i class="bx bx-plus"></i> {{ localize('global.add_vital_sign') }}
                                        </a>
                                    @endcan
                                </div>
                                <div class="col-md-4 text-center">
                                    @if($hospitalization->vitalSigns->count() > 0)
                                        <a href="{{ route('vital-signs.print', ['App\\Models\\Hospitalization', $hospitalization->id]) }}" 
                                           class="btn btn-info" target="_blank">
                                            <i class="fas fa-print"></i> {{ localize('global.print_vital_signs_chart') }}
                                        </a>
                                    @endif
                                </div>
                                <div class="col-md-4 text-end">
                                    <a href="{{ route('vital-signs.index', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}"
                                        class="btn btn-outline-primary">
                                        <i class="bx bx-list-ul"></i> {{ localize('global.view_all_vital_signs') }}
                                    </a>
                                </div>
                            </div>

                            @if($hospitalization->vitalSigns->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>{{ localize('global.id') }}</th>
                                                <th>{{ localize('global.vital_sign_type') }}</th>
                                                <th>{{ localize('global.created_at') }}</th>
                                                <th>{{ localize('schedules') }}</th>
                                                <th>{{ localize('global.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($hospitalization->vitalSigns->take(5) as $vitalSign)
                                                <tr>
                                                    <td>{{ $vitalSign->id }}</td>
                                                    <td>
                                                        <span
                                                            class="badge bg-info">{{ $vitalSign->vitalSignType->name ?? 'N/A' }}</span>
                                                    </td>
                                                    <td>{{ $vitalSign->created_at->format('Y-m-d H:i') }}</td>
                                                    <td>
                                                        <span class="badge bg-secondary">{{ $vitalSign->schedules->count() }}
                                                            {{ localize('global.schedules') }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            @can('view', $vitalSign)
                                                                <a href="{{ route('vital-signs.show', $vitalSign) }}"
                                                                    class="btn btn-info btn-sm" title="{{ localize('global.view') }}">
                                                                    <i class="bx bx-show"></i>
                                                                </a>
                                                            @endcan
                                                            @can('create', App\Models\VitalSignSchedule::class)
                                                                <a href="{{ route('vital-signs.show', $vitalSign) }}"
                                                                    class="btn btn-success btn-sm"
                                                                    title="{{ localize('global.add_schedule') }}">
                                                                    <i class="bx bx-time"></i>
                                                                </a>
                                                            @endcan
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    @if($hospitalization->vitalSigns->count() > 5)
                                        <div class="text-center mt-3">
                                            <a href="{{ route('vital-signs.index', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}"
                                                class="btn btn-outline-primary">
                                                {{ localize('global.view_all') }} ({{ $hospitalization->vitalSigns->count() }}
                                                {{ localize('global.vital_signs') }})
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <div class="mb-3">
                                        <i class="bx bx-heart bx-lg text-muted"></i>
                                    </div>
                                    <h5 class="text-muted">{{ localize('global.no_vital_signs_found') }}</h5>
                                    <p class="text-muted">{{ localize('global.add_first_vital_sign') }}</p>
                                    @can('create', App\Models\VitalSign::class)
                                        <a href="{{ route('vital-signs.create', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}"
                                            class="btn btn-primary">
                                            <i class="bx bx-plus"></i> {{ localize('global.add_vital_sign') }}
                                        </a>
                                    @endcan
                                </div>
                            @endif

                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-notepad p-1"></i>{{ localize('global.prescription') }}</h5>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
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
                                                                <td>{{ $pres_list->created_at }}</td>
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

                            <div class="col-md-12 mt-4">
                                <h5 class="mb-4 p-3 bg-label-primary">
                                    <i class="bx bx-bar-chart p-1"></i>{{ localize('global.diabetes_charts') }}
                                </h5>
                                <div class="d-flex gap-2 mb-3">
                                    <a href="{{ route('diabetes-charts.print', ['chartable_type' => 'App\\Models\\Hospitalization', 'chartable_id' => $hospitalization->id]) }}"
                                        class="btn btn-info" target="_blank">
                                        <i class="fas fa-print"></i> {{ localize('global.print_chart') }}
                                    </a>
                                    <a href="{{ route('diabetes-charts.create', ['chartable_type' => 'App\\Models\\Hospitalization', 'chartable_id' => $hospitalization->id]) }}"
                                        class="btn btn-success">
                                        <i class="bx bx-plus"></i> {{ localize('global.add_diabetes_chart') }}
                                    </a>
                                </div>


                                <!-- Diabetes Charts Table -->
                                @if($diabetesCharts->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>{{ localize('global.date') }}</th>
                                                    <th>{{ localize('global.time') }}</th>
                                                    <th>{{ localize('global.rbs') }}</th>
                                                    <th>{{ localize('global.fbs') }}</th>
                                                    <th>{{ localize('global.insulin_dose') }}</th>
                                                    <th>{{ localize('global.unit') }}</th>
                                                    <th>{{ localize('global.nurse') }}</th>
                                                    <th>{{ localize('global.medicine') }}</th>
                                                    <th>{{ localize('global.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($diabetesCharts as $chart)
                                                    <tr>
                                                        <td>{{ $chart->id }}</td>
                                                        <td>
                                                            @if($chart->date)
                                                                <span class="badge bg-info">{{ $chart->date->format('Y-m-d') }}</span>
                                                            @else
                                                                <span class="text-muted">{{ localize('global.not_set') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($chart->time)
                                                                <span class="badge bg-secondary">{{ $chart->formatted_time }}</span>
                                                            @else
                                                                <span class="text-muted">{{ localize('global.not_set') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($chart->rbs)
                                                                <span class="badge bg-warning">{{ $chart->rbs }}
                                                                    {{ $chart->unit }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($chart->fbs)
                                                                <span class="badge bg-success">{{ $chart->fbs }}
                                                                    {{ $chart->unit }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($chart->insulin_dose)
                                                                <span class="badge bg-primary">{{ $chart->insulin_dose }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($chart->unit)
                                                                <small>{{ $chart->unit }}</small>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($chart->nurse)
                                                                <span class="badge bg-info">{{ $chart->nurse->full_name }}</span>
                                                            @else
                                                                <span class="text-muted">{{ localize('global.not_assigned') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($chart->medicine)
                                                                <span class="badge bg-secondary">{{ $chart->medicine->name }}</span>
                                                            @else
                                                                <span class="text-muted">{{ localize('global.not_assigned') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <a href="{{ route('diabetes-charts.show', $chart) }}"
                                                                    class="btn btn-sm btn-info"
                                                                    title="{{ localize('global.view') }}">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="{{ route('diabetes-charts.edit', $chart) }}"
                                                                    class="btn btn-sm btn-warning"
                                                                    title="{{ localize('global.edit') }}">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <form action="{{ route('diabetes-charts.destroy', $chart) }}"
                                                                    method="POST" class="d-inline"
                                                                    onsubmit="return confirm('{{ localize('global.are_you_sure_delete') }}')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                                        title="{{ localize('global.delete') }}">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <div class="mb-3">
                                            <i class="bx bx-clipboard bx-lg text-muted"></i>
                                        </div>
                                        <h5 class="text-muted">{{ localize('global.no_diabetes_charts_found') }}</h5>
                                        <p class="text-muted">{{ localize('global.add_first_diabetes_chart') }}</p>
                                        <a href="{{ route('diabetes-charts.create', ['chartable_type' => 'App\\Models\\Hospitalization', 'chartable_id' => $hospitalization->id]) }}"
                                            class="btn btn-primary">
                                            <i class="bx bx-plus"></i> {{ localize('global.add_diabetes_chart') }}
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <!-- Nurse Notes Section -->
                            <div class="col-md-12 mt-4">
                                <h5 class="mb-4 p-3 bg-label-primary">
                                    <i class="bx bx-note p-1"></i>{{ localize('global.nurse_notes') }}
                                </h5>
                                <div class="d-flex gap-2 mb-3">
                                    <a href="{{ route('nurse-notes.print', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}"
                                        class="btn btn-info" target="_blank">
                                        <i class="fas fa-print"></i> {{ localize('global.print_notes') }}
                                    </a>
                                    @can('create', App\Models\NurseNote::class)
                                        <a href="{{ route('nurse-notes.create', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}"
                                            class="btn btn-success">
                                            <i class="bx bx-plus"></i> {{ localize('global.add_nurse_note') }}
                                        </a>
                                    @endcan
                                </div>

                                @if($nurseNotes->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>{{ localize('global.date') }}</th>
                                                    <th>{{ localize('global.nurse') }}</th>
                                                    <th>{{ localize('global.am_time') }}</th>
                                                    <th>{{ localize('global.pm_time') }}</th>
                                                    <th>{{ localize('global.note') }}</th>
                                                    <th>{{ localize('global.created_by') }}</th>
                                                    <th>{{ localize('global.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($nurseNotes as $note)
                                                    <tr>
                                                        <td>{{ $note->id }}</td>
                                                        <td>
                                                            @if($note->date)
                                                                <span class="badge bg-info">{{ $note->date->format('Y-m-d') }}</span>
                                                            @else
                                                                <span class="text-muted">{{ localize('global.not_assigned') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($note->nurse)
                                                                <span class="badge bg-primary">{{ $note->nurse->full_name }}</span>
                                                            @else
                                                                <span class="text-muted">{{ localize('global.not_assigned') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($note->time_am)
                                                                <span
                                                                    class="badge bg-primary">{{ $note->time_am->format('H:i') }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($note->time_pm)
                                                                <span
                                                                    class="badge bg-primary">{{ $note->time_pm->format('H:i') }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($note->note)
                                                                <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                                                    title="{{ $note->note }}">
                                                                    {{ Str::limit($note->note, 50) }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($note->createdBy)
                                                                <span class="badge bg-secondary">{{ $note->createdBy->name }}</span>
                                                            @else
                                                                <span class="text-muted">{{ localize('global.not_assigned') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                @can('view', $note)
                                                                    <a href="{{ route('nurse-notes.show', $note) }}"
                                                                        class="btn btn-sm btn-info"
                                                                        title="{{ localize('global.view') }}">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('update', $note)
                                                                    <a href="{{ route('nurse-notes.edit', $note) }}"
                                                                        class="btn btn-sm btn-warning"
                                                                        title="{{ localize('global.edit') }}">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('delete', $note)
                                                                    <form action="{{ route('nurse-notes.destroy', $note) }}"
                                                                        method="POST" class="d-inline"
                                                                        onsubmit="return confirm('{{ localize('global.are_you_sure_delete') }}')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                                            title="{{ localize('global.delete') }}">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                @endcan
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <div class="mb-3">
                                            <i class="bx bx-note bx-lg text-muted"></i>
                                        </div>
                                        <h5 class="text-muted">{{ localize('global.no_nurse_notes_found') }}</h5>
                                        <p class="text-muted">{{ localize('global.add_first_nurse_note') }}</p>
                                        @can('create', App\Models\NurseNote::class)
                                            <a href="{{ route('nurse-notes.create', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}"
                                                class="btn btn-primary">
                                                <i class="bx bx-plus"></i> {{ localize('global.add_nurse_note') }}
                                            </a>
                                        @endcan
                                    </div>
                                @endif
                            </div>

                            <!-- Medication Administration Records Section -->
                            <div class="col-md-12 mt-4">
                                <h5 class="mb-4 p-3 bg-label-primary">
                                    <i
                                        class="bx bx-pills p-1"></i>{{ localize('global.medication_administration_records') }}
                                    ({{ localize('global.mar') }})
                                </h5>
                                <div class="d-flex gap-2 mb-3">
                                    <a href="{{ route('medication-administration-records.print', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}"
                                        class="btn btn-info" target="_blank">
                                        <i class="fas fa-print"></i> {{ localize('global.print_mars') }}
                                    </a>
                                    @can('create', App\Models\MedicationAdministrationRecord::class)
                                        <a href="{{ route('medication-administration-records.create', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}"
                                            class="btn btn-success">
                                            <i class="bx bx-plus"></i> {{ localize('global.add_mar') }}
                                        </a>
                                    @endcan
                                </div>

                                @if($medicationAdministrationRecords->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>{{ localize('global.mar_id') }}</th>
                                                    <th>{{ localize('global.medicine') }}</th>
                                                    <th>{{ localize('global.nurse') }}</th>
                                                    <th>{{ localize('global.order_date') }}</th>
                                                    <th>{{ localize('global.signature_date') }}</th>
                                                    <th>{{ localize('global.administration_times') }}</th>
                                                    <th>{{ localize('global.mar_created_by') }}</th>
                                                    <th>{{ localize('global.mar_actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($medicationAdministrationRecords as $mar)
                                                    <tr>
                                                        <td>{{ $mar->id }}</td>
                                                        <td>
                                                            <strong>{{ $mar->medicine->name ?? 'N/A' }}</strong>
                                                        </td>
                                                        <td>{{ $mar->nurse->full_name ?? 'N/A' }}</td>
                                                        <td>
                                                            @if($mar->order_date)
                                                                <span
                                                                    class="badge bg-info">{{ $mar->order_date->format('Y-m-d') }}</span>
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($mar->date_signature)
                                                                <span
                                                                    class="badge bg-success">{{ $mar->date_signature->format('Y-m-d') }}</span>
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($mar->administrationTimes->count() > 0)
                                                                <span class="badge badge-info">
                                                                    {{ $mar->administrationTimes->count() }}
                                                                    {{ localize('global.times_count') }}
                                                                </span>
                                                                <br>
                                                                <small>
                                                                    @foreach($mar->administrationTimes as $time)
                                                                        {{ $time->formatted_time }}@if(!$loop->last), @endif
                                                                    @endforeach
                                                                </small>
                                                            @else
                                                                <span
                                                                    class="text-muted">{{ localize('global.no_times_recorded') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $mar->createdBy->name ?? 'System' }}</td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                @can('view', $mar)
                                                                    <a href="{{ route('medication-administration-records.show', $mar) }}"
                                                                        class="btn btn-sm btn-info"
                                                                        title="{{ localize('global.mar_view') }}">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('update', $mar)
                                                                    <a href="{{ route('medication-administration-records.edit', $mar) }}"
                                                                        class="btn btn-sm btn-warning"
                                                                        title="{{ localize('global.mar_edit') }}">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('delete', $mar)
                                                                    <form
                                                                        action="{{ route('medication-administration-records.destroy', $mar) }}"
                                                                        method="POST" class="d-inline"
                                                                        onsubmit="return confirm('{{ localize('global.mar_confirm_delete') }}')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                                            title="{{ localize('global.mar_delete') }}">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                @endcan
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <div class="mb-3">
                                            <i class="bx bx-pills bx-lg text-muted"></i>
                                        </div>
                                        <h5 class="text-muted">{{ localize('global.no_mars_found') }}</h5>
                                        <p class="text-muted">{{ localize('global.add_first_mar') }}</p>
                                        @can('create', App\Models\MedicationAdministrationRecord::class)
                                            <a href="{{ route('medication-administration-records.create', ['morphable_type' => 'App\\Models\\Hospitalization', 'morphable_id' => $hospitalization->id]) }}"
                                                class="btn btn-primary">
                                                <i class="bx bx-plus"></i> {{ localize('global.add_mar') }}
                                            </a>
                                        @endcan
                                    </div>
                                @endif
                            </div>

                            <!-- Nutrition Care Section -->
                            <div class="col-md-12 mt-4" id="nutrition-care-section">
                                <h5 class="mb-4 p-3 bg-label-primary">
                                    <i class="bx bx-food-menu p-1"></i>{{ localize('global.nutrition_care') }}
                                </h5>
                                <div class="d-flex gap-2 mb-3">
                                    @can('create', \App\Models\NutritionCare::class)
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createNutritionCareModal">
                                            <i class="bx bx-plus"></i> {{ localize('global.create_nutrition_care') }}
                                        </button>
                                    @endcan
                                </div>

                                @if($hospitalization->nutritionCares->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>{{ localize('global.id') }}</th>
                                                    <th>{{ localize('global.patient_name') }}</th>
                                                    <th>{{ localize('global.nurse') }}</th>
                                                    <th>{{ localize('global.observations') }}</th>
                                                    <th>{{ localize('global.interventions') }}</th>
                                                    <th>{{ localize('global.nutrition_care_full_note') }}</th>
                                                    <th>{{ localize('global.date_signature') }}</th>
                                                    <th>{{ localize('global.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($hospitalization->nutritionCares as $nutritionCare)
                                                    <tr>
                                                        <td>{{ $nutritionCare->id }}</td>
                                                        <td>{{ $nutritionCare->patient_name }}</td>
                                                        <td>{{ $nutritionCare->nurse->full_name ?? 'N/A' }}</td>
                                                        <td>
                                                            @php
                                                                $observations = [];
                                                                if ($nutritionCare->cough) $observations[] = localize('global.cough');
                                                                if ($nutritionCare->sound) $observations[] = localize('global.sound');
                                                                if ($nutritionCare->fluid_swallowing_ability) $observations[] = localize('global.fluid_swallowing_ability');
                                                                if ($nutritionCare->weight) $observations[] = localize('global.weight');
                                                                if ($nutritionCare->amount_and_type_of_nutrition) $observations[] = localize('global.amount_and_type_of_nutrition');
                                                                if ($nutritionCare->diarrhea) $observations[] = localize('global.diarrhea');
                                                                if ($nutritionCare->heart_failure_and_kidney_disease) $observations[] = localize('global.heart_failure_and_kidney_disease');
                                                                if ($nutritionCare->remaining_materials) $observations[] = localize('global.remaining_materials');
                                                                if ($nutritionCare->type_of_tube) $observations[] = localize('global.type_of_tube');
                                                            @endphp
                                                            {{ implode(', ', $observations) ?: '-' }}
                                                        </td>
                                                        <td>
                                                            @php
                                                                $interventions = [];
                                                                if ($nutritionCare->constipation) $interventions[] = localize('global.constipation');
                                                                if ($nutritionCare->nutrition_is_provided) $interventions[] = localize('global.nutrition_is_provided');
                                                                if ($nutritionCare->mouth_hygiene) $interventions[] = localize('global.mouth_hygiene');
                                                                if ($nutritionCare->oral_nutrition_advices) $interventions[] = localize('global.oral_nutrition_advices');
                                                                if ($nutritionCare->voice_exercise) $interventions[] = localize('global.voice_exercise');
                                                                if ($nutritionCare->swallowing_exercise) $interventions[] = localize('global.swallowing_exercise');
                                                                if ($nutritionCare->aspiration_prevention_proceeded) $interventions[] = localize('global.aspiration_prevention_proceeded');
                                                            @endphp
                                                            {{ implode(', ', $interventions) ?: '-' }}
                                                        </td>
                                                        <td>
                                                            @if($nutritionCare->nutrition_care_full_note)
                                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $nutritionCare->nutrition_care_full_note }}">
                                                                    {{ Str::limit($nutritionCare->nutrition_care_full_note, 50) }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $nutritionCare->created_at->format('Y-m-d H:i') }}</td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                @can('view', $nutritionCare)
                                                                    <a href="{{ route('nutrition-cares.show', $nutritionCare) }}" class="btn btn-sm btn-info" title="{{ localize('global.view') }}">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                    <a href="{{ route('nutrition-cares.print', $nutritionCare) }}" class="btn btn-sm btn-primary" title="{{ localize('global.print') }}" target="_blank">
                                                                        <i class="fas fa-print"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('update', $nutritionCare)
                                                                    <a href="{{ route('nutrition-cares.edit', $nutritionCare) }}" class="btn btn-sm btn-warning" title="{{ localize('global.edit') }}">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('delete', $nutritionCare)
                                                                    <form action="{{ route('nutrition-cares.destroy', $nutritionCare) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ localize('global.are_you_sure_delete') }}')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-sm btn-danger" title="{{ localize('global.delete') }}">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                @endcan
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <div class="mb-3">
                                            <i class="bx bx-food-menu bx-lg text-muted"></i>
                                        </div>
                                        <h5 class="text-muted">{{ localize('global.no_nutrition_care_found') }}</h5>
                                        <p class="text-muted">{{ localize('global.add_first_nutrition_care') }}</p>
                                        @can('create', \App\Models\NutritionCare::class)
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNutritionCareModal">
                                                <i class="bx bx-plus"></i> {{ localize('global.create_nutrition_care') }}
                                            </button>
                                        @endcan
                                    </div>
                                @endif
                            </div>

                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-command p-1"></i>{{ localize('global.advice') }}</h5>
                            @if ($hospitalization->is_discharged == 0)
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
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


                            {{-- lab tests from hospitalization --}}

                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-hard-hat p-1"></i>{{ localize('global.hospitalization_checkups') }}</h5>
                            @if ($hospitalization->is_discharged == 0)
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
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

                            {{-- end lab tests from hospitalization --}}
                            {{-- icu starts here --}}
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-tv p-1"></i>{{ localize('global.refere_to_icu') }}</h5>
                            @if ($hospitalization->is_discharged == 0)
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


                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-first-aid p-1"></i>{{ localize('global.refere_to_anasthesia') }}</h5>
                            @if ($hospitalization->is_discharged == 0)
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
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
                            @if ($hospitalization->is_discharged == 0)
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
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
                            <div class="modal fade modal-xl" id="createNutritionCareModal" tabindex="-1" aria-labelledby="createNutritionCareModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createNutritionCareModalLabel">{{ localize('global.create_nutrition_care') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form id="createNutritionCareForm" action="{{ route('nutrition-cares.store') }}" method="POST">
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
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                                <button type="submit" class="btn btn-primary" id="submitNutritionCareBtn">{{ localize('global.create') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- End Create Nutrition Care Modal -->

                            <!-- Nursing Assessment Section -->
                            <div class="col-md-12 mt-4" id="nursing-assessment-section">
                                <h5 class="mb-4 p-3 bg-label-primary">
                                    <i class="bx bx-clipboard p-1"></i>{{ localize('global.nursing_assessment') }}
                                </h5>
                                <div class="d-flex gap-2 mb-3">
                                    @can('create', \App\Models\NursingAssessment::class)
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createNursingAssessmentModal">
                                            <i class="bx bx-plus"></i> {{ localize('global.create_nursing_assessment') }}
                                        </button>
                                    @endcan
                                </div>

                                @if($hospitalization->nursingAssessments->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>{{ localize('global.patient_name') }}</th>
                                                    <th>{{ localize('global.nurse') }}</th>
                                                    <th>{{ localize('global.assessment_date') }}</th>
                                                    <th>{{ localize('global.chief_complaint') }}</th>
                                                    <th>{{ localize('global.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($hospitalization->nursingAssessments as $assessment)
                                                    <tr>
                                                        <td>{{ $assessment->patient_name }}</td>
                                                        <td>{{ $assessment->nurse->full_name ?? 'N/A' }}</td>
                                                        <td>{{ $assessment->assessment_initiated_by_date ? $assessment->assessment_initiated_by_date->format('Y-m-d') : 'N/A' }}</td>
                                                        <td>{{ Str::limit($assessment->chief_complaint, 50) }}</td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                @can('view', $assessment)
                                                                    <a href="{{ route('nursing-assessments.show', $assessment) }}" class="btn btn-sm btn-outline-primary">
                                                                        <i class="bx bx-show"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('update', $assessment)
                                                                    <a href="{{ route('nursing-assessments.edit', $assessment) }}" class="btn btn-sm btn-outline-warning">
                                                                        <i class="bx bx-edit"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('delete', $assessment)
                                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteNursingAssessment({{ $assessment->id }})">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                @endcan
                                                                @can('view', $assessment)
                                                                    <a href="{{ route('nursing-assessments.print', $assessment) }}" class="btn btn-sm btn-outline-info" target="_blank">
                                                                        <i class="bx bx-printer"></i>
                                                                    </a>
                                                                @endcan
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle"></i> {{ localize('global.no_nursing_assessments_found') }}
                                    </div>
                                @endif
                            </div>

                            <!-- Create Nursing Assessment Modal -->
                            <div class="modal fade modal-xl" id="createNursingAssessmentModal" tabindex="-1" aria-labelledby="createNursingAssessmentModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createNursingAssessmentModalLabel">{{ localize('global.create_nursing_assessment') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form id="createNursingAssessmentForm" action="{{ route('nursing-assessments.store') }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                @php
                                                    $nurses = \App\Models\Nurse::all();
                                                    $morphable_type = 'App\Models\Hospitalization';
                                                    $morphable_id = $hospitalization->id;
                                                    $patient_name = $hospitalization->patient->first_name . ' ' . $hospitalization->patient->last_name;
                                                @endphp
                                                @include('pages.nursing-assessments.partials.form')
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                                <button type="submit" class="btn btn-primary" id="submitNursingAssessmentBtn">{{ localize('global.create') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- End Create Nursing Assessment Modal -->

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
        $(document).ready(function() {
            $('#createNutritionCareForm').on('submit', function(e) {
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
                    success: function(response) {
                        // Close modal
                        $('#createNutritionCareModal').modal('hide');
                        
                        // Reload the nutrition care section
                        reloadNutritionCareSection();
                        
                        // Show success message
                        toastr.success('{{ localize("global.nutrition_care_created_successfully") }}');
                        
                        // Reset form
                        form[0].reset();
                    },
                    error: function(xhr) {
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
                    complete: function() {
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
    </script>

@endsection