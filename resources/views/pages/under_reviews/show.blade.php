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
                    <h5 class="mb-0">{{ localize('global.under_review_details') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        <a class="btn btn-danger" href="{{ url()->previous() }}"
                           type="button">
                            <span class="text-white"> <span
                                      class="d-none d-sm-inline-block  ">{{ localize('global.back') }}</span></span>
                        </a>
                    </div>
                </div>
                <div class="card-body">

                    <div class="col-md-12">
                        <div class="border border-label-primary mb-4 text-center">
                            <h5 class="mb-4 p-3 bg-label-primary text-center">{{ localize('global.under_review_details') }}</h5>

                        <div class="row p-2">
                            <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                            <div>
                                {{$underReview->patient->name}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.referred_to') }}</h5>
                            <div>
                                {{$underReview->doctor->name}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.date') }}</h5>
                            <div>
                                {{$underReview->created_at->format('Y-m-d')}}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-2">{{ localize('global.time') }}</h5>
                            <div>
                                {{$underReview->created_at->format('H:m:s')}}
                            </div>
                        </div>
                        </div>

                        <div class="row text-start m-4">
                            <div class="col-md-12 mt-2 mb-2">
                            <h5 class="mb-2">{{ localize('global.reason') }}</h5>
                            <div>
                                {{$underReview->reason}}
                            </div>
                        </div>
                        <div class="col-md-12 mt-2 mb-2">
                            <h5 class="mb-2">{{ localize('global.remarks') }}</h5>
                            <div>
                                {{$underReview->remarks}}
                            </div>
                        </div>
                        </div>
                        </div>

                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i class="bx bx-glasses p-1"></i>{{localize('global.visits') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createVisitModal{{ $underReview->id }}"><span><i class="bx bx-plus"></i></span></button>
                                <!-- Create visit Modal -->
                                <div class="modal fade" id="createVisitModal{{ $underReview->id }}" tabindex="-1" aria-labelledby="createVisitModalLabel{{ $underReview->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createVisitModalLabel{{ $underReview->id }}">{{localize('global.add_visit')}}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('visits.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" id="patient_id{{ $underReview->patient_id }}" name="patient_id" value="{{ $underReview->patient_id }}">
                                                    <input type="hidden" id="under_review_id{{ $underReview->id }}" name="under_review_id" value="{{ $underReview->id }}">
                                                    <input type="hidden" id="doctor_id{{ $underReview->id }}" name="doctor_id" value="{{ $underReview->doctor->id }}">
                                                    <!-- Add other diagnosis form fields as needed -->
                                                    <div class="form-group">
                                                        <label for="description{{ $underReview->id }}">{{localize('global.description')}}</label>
                                                        <textarea class="form-control" id="description{{ $underReview->id }}" name="description" rows="3"></textarea>
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
                                <!-- End Create visit Modal -->
                        <div class="col-md-12 mt-4">




                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{localize('global.number')}}</th>
                                    <th>{{localize('global.description')}}</th>
                                    <th>{{localize('global.by')}}</th>
                                    <th>{{localize('global.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($underReview->visits as $visit)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$visit->description}}</td>
                                    <td>{{$visit->doctor->name}}</td>
                                    <td>
                                        @can('edit-under-review-visit')
                                        <a href="{{route('visits.edit', $visit->id)}}"><span><i class="bx bx-edit"></i></span></a>
                                        @endcan
                                        @can('delete-under-review-visit')
                                        <a href="{{ route('visits.destroyUnderReviewVisit', $visit) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$visit->id}}').submit(); }">
                                            <i class="bx bx-trash text-danger"></i>
                                        </a>
                                        @endcan
                                        <!-- Using a <form> element -->
                                        <form id="delete-form-{{$visit->id}}" action="{{ route('visits.destroyUnderReviewVisit', $visit) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
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
                            class="bx bx-notepad p-1"></i>{{ localize('global.prescription') }}</h5>

                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#createPrescriptionModal{{$underReview->id }}"><span><i
                                    class="bx bx-plus"></i></span></button>

                    <!-- Create Diagnose Modal -->
                    <div class="modal fade modal-xl" id="createPrescriptionModal{{$underReview->id }}"
                        tabindex="-1" aria-labelledby="createPrescriptionModalLabel{{$underReview->id }}"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="createPrescriptionModalLabel{{$underReview->id }}">
                                        {{ localize('global.add_prescription') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('prescriptions.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" id="patient_id{{$underReview->patient_id }}"
                                            name="patient_id" value="{{$underReview->patient_id }}">
                                        <input type="hidden" id="appointment_id{{ $underReview->appointment->id }}"
                                            name="appointment_id" value="{{ $underReview->appointment->id }}">
                                        <input type="hidden" id="branch_id{{ $underReview->id }}" name="branch_id"
                                            value="{{ auth()->user()->branch_id }}">
                                        <input type="hidden" id="doctor_id{{ $underReview->id }}" name="doctor_id"
                                            value="{{ auth()->user()->id }}">
                                        <input type="hidden" id="under_review_id{{ $underReview->id }}" name="under_review_id"
                                            value="{{ $underReview->id }}">

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
                                                                <option value="{{ $value->id }}"
                                                                    {{ old('type') == $value->id ? 'selected' : '' }}>
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
                                                                <option value="{{ $value->id }}"
                                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
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
                                                                <option value="{{ $value->id }}"
                                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                    {{ $value->name }}

                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
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
                                @forelse($underReview->prescription as $prescription)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $prescription->patient->name }}</td>
                                        <td>
                                            @if ($prescription->is_completed == '0')
                                                <span
                                                    class="badge bg-danger">{{ localize('global.not_delivered') }}</span>
                                            @else
                                                <span
                                                    class="badge bg-success">{{ localize('global.delivered') }}</span>
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


                    <div class="modal fade modal-xl" id="showPrescriptionModal{{ $underReview->id }}"
                        tabindex="-1" aria-labelledby="showPrescriptionModalLabel{{ $underReview->id }}"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="showPrescriptionModalLabel{{ $underReview->id }}">
                                        {{ localize('global.show_prescription_details') }}</h5>
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
                                            @if ($underReview->prescription)
                                                @foreach ($underReview->prescription as $pres_list)
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




                    <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                        class="bx bx-bed p-1"></i>{{ localize('global.hospitalize') }}</h5>

                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#createHospitalizationModal{{ $underReview->id }}"><span><i
                                class="bx bx-plus"></i></span></button>

                <!-- Create  Lab Modal -->
                <div class="modal fade modal-xl" id="createHospitalizationModal{{ $underReview->id }}"
                    tabindex="-1" aria-labelledby="createHospitalizationModalLabel{{ $underReview->id }}"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"
                                    id="createHospitalizationModalLabel{{ $underReview->id }}">
                                    {{ localize('global.hospitalize_patient') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('hospitalizations.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" id="patient_id{{ $underReview->patient_id }}"
                                        name="patient_id" value="{{ $underReview->patient_id }}">
                                    <input type="hidden" id="appointment_id{{ $underReview->appointment->id }}"
                                        name="appointment_id" value="{{ $underReview->appointment->id }}">
                                    <input type="hidden" id="doctor_id{{ $underReview->id }}" name="doctor_id"
                                        value="{{ auth()->user()->id }}">
                                    <input type="hidden" id="branch_id{{ $underReview->id }}" name="branch_id"
                                        value="{{ auth()->user()->branch_id }}">
                                    <input type="hidden" id="under_review_id{{ $underReview->id }}" name="under_review_id"
                                        value="{{ $underReview->id }}">
                                    <input type="hidden" id="is_discharged{{ $underReview->id }}"
                                        name="is_discharged" value="0">

                                    <div class="form-group">

                                        <div class="form-group">
                                            <label
                                                for="reason{{ $underReview->id }}">{{ localize('global.reason') }}</label>
                                            <textarea class="form-control" id="reason{{ $underReview->id }}" name="reason" rows="3"></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label
                                                for="remarks{{ $underReview->id }}">{{ localize('global.remarks') }}</label>
                                            <textarea class="form-control" id="remarks{{ $underReview->id }}" name="remarks" rows="3"></textarea>
                                        </div>

                                        <div class="form-group">
                                            <div class="row p-2">
                                                <div class="col-md-4">
                                                    <label
                                                        for="room_id{{ $underReview->id }}">{{ localize('global.rooms') }}</label>
                                                    <select class="form-control select2" name="room_id"
                                                        id="room_id">
                                                        <option value="">{{ localize('global.select') }}
                                                        </option>
                                                        @foreach ($rooms as $value)
                                                            <option value="{{ $value->id }}"
                                                                {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                {{ $value->name }}

                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <label
                                                        for="bed_id{{ $underReview->id }}">{{ localize('global.beds') }}</label>
                                                    <select class="form-control select2" name="bed_id"
                                                        id="bed_id">
                                                        <option value="">{{ localize('global.select') }}
                                                        </option>
                                                        @foreach ($beds as $value)
                                                            <option value="{{ $value->id }}"
                                                                {{ old('number') == $value->id ? 'selected' : '' }}>
                                                                {{ $value->number }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <label
                                                        for="food_type_id{{ $underReview->id }}">{{ localize('global.food_type') }}</label>
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
                                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                                    class="bx bx-info-circle p-1"></i>{{ localize('global.patient_companion_info') }}
                                            </h5>
                                            <div class="form-group">
                                                <div class="row p-2">
                                                    <div class="col-md-3">
                                                        <label>{{ localize('global.companion_name') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="patinet_companion">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label>{{ localize('global.companion_father_name') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="companion_father_name">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label>{{ localize('global.relation_to_patient') }}</label>
                                                        <select class="form-control select2"
                                                            name="relation_to_patient">
                                                            <option value="">
                                                                {{ localize('global.select') }}</option>
                                                            @foreach ($relations as $value)
                                                                <option value="{{ $value->id }}"
                                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                    {{ $value->name }}

                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label>{{ localize('global.companion_card_type') }}</label>
                                                        <select class="form-control select2"
                                                            name="companion_card_type">
                                                            <option value="">
                                                                {{ localize('global.select') }}</option>
                                                            <option value="12">
                                                                {{ localize('global.12_hours') }}</option>
                                                            <option value="24">
                                                                {{ localize('global.24_hours') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
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
                                <th class="text-wrap">{{ localize('global.reason') }}</th>
                                <th>{{ localize('global.remarks') }}</th>
                                <th>{{ localize('global.room') }}</th>
                                <th>{{ localize('global.bed') }}</th>
                                <th>{{ localize('global.status') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($underReview->hospitalization as $hospitalization)
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
                                            <span
                                                class="badge bg-success">{{ localize('global.discharged') }}</span>
                                        @endif

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
                        @forelse ($underReview->hospitalization as $single_hospitaliztion)
                            @foreach ($single_hospitaliztion->visits as $visit)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $visit->description }}</td>
                                    <td>{{ $visit->doctor->name }}</td>
                                    <td>
                                        {{ $visit->created_at }}
                                    </td>
                                </tr>
                            @endforeach
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






                        {{-- lab tests from underReview --}}

                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                            class="bx bx-hard-hat p-1"></i>{{ localize('global.under_review_checkups') }}</h5>

                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#createLabModal{{ $underReview->id }}"><span><i
                                class="bx bx-plus"></i></span></button>
                    <!-- Create  Lab Modal -->
                    <div class="modal fade" id="createLabModal{{ $underReview->id }}" tabindex="-1"
                        aria-labelledby="createLabModalLabel{{ $underReview->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="createLabModalLabel{{ $underReview->id }}">
                                        {{ localize('global.add_lab_test') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('lab_tests.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" id="patient_id{{ $underReview->patient_id }}"
                                            name="patient_id" value="{{ $underReview->patient_id }}">
                                        <input type="hidden" id="appointment_id{{ $underReview->appointment->id }}"
                                            name="appointment_id" value="{{ $underReview->id }}">
                                        <input type="hidden" id="doctor_id{{ $underReview->id }}" name="doctor_id"
                                            value="{{ $underReview->doctor->id }}">
                                        <input type="hidden" id="branch_id{{ $underReview->id }}" name="branch_id"
                                            value="{{ auth()->user()->branch_id }}">
                                        <input type="hidden" id="status{{ $underReview->id }}" name="status"
                                            value="0">
                                        <input type="hidden" id="under_review_id{{ $underReview->id }}" name="under_review_id"
                                            value="{{ $underReview->id }}">
                                        <div class="form-group">

                                            <label
                                                for="lab_type_section{{ $underReview->id }}">{{ localize('global.lab_type_section') }}</label>
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
                                                for="lab_type_id{{ $underReview->id }}">{{ localize('global.lab_type') }}</label>
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
                                @forelse ($underReview->labs as $lab)
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
                    </div>

                        {{-- end lab tests from underReview --}}


                        {{-- discharge --}}
                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                            class="bx bx-walk p-1"></i>{{ localize('global.discharge_patient') }}</h5>

                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#createDischargeModal{{ $underReview->id }}"><span><i
                                class="bx bx-plus"></i></span></button>
                    <!-- Create  Lab Modal -->
                    <div class="modal fade" id="createDischargeModal{{ $underReview->id }}" tabindex="-1"
                        aria-labelledby="createDischargeModalLabel{{ $underReview->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="createDischargeModalLabel{{ $underReview->id }}">
                                        {{ localize('global.add_lab_test') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('under_reviews.update', $underReview) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" id="is_discharged{{ $underReview->id }}" name="is_discharged"
                                            value="1">
                                            <div class="form-group">
                                                <label
                                                    for="discharge_remark{{ $underReview->id }}">{{ localize('global.discharge_remark') }}</label>
                                                <textarea class="form-control" id="discharge_remark{{ $underReview->id }}" name="discharge_remark" rows="3"></textarea>
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
                        {{$underReview->discharge_remark}}
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

    function getPrescriptionItems(id){ $.ajax({ type: "GET", url: "{{url('prescription_items/getItems/')}}/"+id, dataType: "html", success: function(data)
    {
         $('#prescription_items_table').html(data); }, error: function(xhr, status, error) {
         // Handle the error response
         console.error(error); } }); }

         $('#room_id').on('change', function() {
                var roomId = $(this).val();
                if (roomId !== '') {
                    $.ajax({
                        url: '/get_related_beds/' + roomId,
                        type: 'GET',
                        success: function(response) {

                            $('#bed_id').html(response);
                        }
                    })
                }
            })

</script>

@endsection
