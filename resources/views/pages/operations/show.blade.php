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
                                    {{ localize('global.operation_details') }}</h5>

                                <div class="row p-2 text-center">
                                    <div class="col-md-3">
                                        <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.patient_name') }}</h5>
                                        <div>
                                            {{ $operation->patient->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.operation_type') }}</h5>
                                        <div>
                                            {{ $operation->operationType->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.date') }}</h5>
                                        <div>
                                            {{ $operation->date }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.time') }}</h5>
                                        <div>
                                            {{ $operation->time }}
                                        </div>
                                    </div>
                                </div>
                                <div class="row p-2 text-center">
                                    <div class="col-md-3">
                                        <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.operation_plan') }}</h5>
                                        <div>
                                            {{ $operation->plan }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.operation_duration') }}
                                        </h5>
                                        <div>
                                            {{ $operation->planned_duration }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.position_on_bed') }}</h5>
                                        <div>
                                            {{ $operation->position_on_bed }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2 bg-label-primary p-1">
                                            {{ localize('global.estimated_blood_waste') }}</h5>
                                        <div>
                                            {{ $operation->estimated_blood_waste }}
                                        </div>
                                    </div>
                                </div>
                                <div class="row p-2 text-center">
                                    <div class="col-md-6">
                                        <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.other_problems') }}</h5>
                                        <div>
                                            {{ $operation->other_problems }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.operation_surgion') }}
                                        </h5>
                                        <div>
                                            <span class="badge bg-primary">
                                                {{ $operation->surgion->name }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.operation_assistants') }}
                                        </h5>
                                        <div>
                                            @foreach ($operation->associated_assistants as $doctor)
                                                <span class="badge bg-primary">
                                                    {{ $doctor->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>

                                <div class="row p-2 text-center">
                                    <div class="col-md-3">
                                        <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.anesthesia_log') }}</h5>
                                        <div>
                                            {{ $operation->anesthesia_log->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.anesthesist') }}</h5>
                                        <div>
                                            {{ $operation->anesthesist->name }}
                                        </div>
                                    </div>
                                    @if (isset($operation->scrub_nurse->name))
                                        <div class="col-md-3">
                                            <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.scrub_nurse') }}</h5>
                                            <div>
                                                {{ $operation->scrub_nurse->name }}
                                            </div>
                                        </div>
                                    @endif
                                    @if (isset($operation->circulation_nurse->name))
                                        <div class="col-md-3">
                                            <h5 class="mb-2 bg-label-primary p-1">
                                                {{ localize('global.circulation_nurse') }}</h5>
                                            <div>
                                                {{ $operation->circulation_nurse->name }}
                                            </div>
                                        </div>
                                    @endif

                                </div>

                                <div class="row p-2 text-center">
                                    <div class="col-md-4">
                                        <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.anesthesia_log_reply') }}
                                        </h5>
                                        <div>
                                            {{ $operation->anesthesia_log_reply }}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.anesthesia_plan') }}</h5>
                                        <div>
                                            {{ $operation->anesthesia_plan }}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h5 class="mb-2 bg-label-primary p-1">{{ localize('global.anesthesia_type') }}</h5>
                                        <div>
                                            {{ $operation->anesthesia_type }}
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-2 p-2">
                                    <div class="col-md-4 text-center">
                                    
                                            @if ($operation->is_operation_approved == 0 && $operation->is_operation_done == 0 && $operation->is_reserved == 0)
                                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                    data-bs-target="#createOperationNursesModal{{ $operation->id }}"><span><i
                                                            class="bx bx-check"></i>{{ localize('global.operation_approval') }}</span></button>
                                            @endif
                                    
                                    </div>
                                    <div class="col-md-4 text-center">
                                        @if ($operation->is_operation_approved == 1 && $operation->is_operation_done == 0 && $operation->is_reserved == 0)
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                data-bs-target="#createOperationModal{{ $operation->id }}"><span><i
                                                        class="bx bx-check"></i>{{ localize('global.complete_operation') }}</span></button>
                                        @endif
                                    </div>
                                    <div class="col-md-4 text-center">
                                        @if ($operation->is_operation_done == 0 && $operation->is_reserved == 0)
                                            <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#createReserveModal{{ $operation->id }}"><span><i
                                                        class="bx bx-calendar-check"></i>{{ localize('global.reserve_operation') }}</span></button>
                                            @else
                                            @if($operation->is_operation_done == 0)
                                            <button class="btn btn-success">
                                                <a href="{{ route('operations.unreserve', $operation->id) }}" class="text-white">
                                                  <span><i class="bx bx-transfer"></i>{{localize('global.move_operation')}}</span>
                                                </a>
                                              </button>
                                              @endif
                                        @endif
                                    </div>
                                </div>



                                <div class="modal fade" id="createOperationNursesModal{{ $operation->id }}" tabindex="-1"
                                    aria-labelledby="createOperationNursesModalLabel{{ $operation->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createOperationNursesModalLabel{{ $operation->id }}">
                                                    {{ localize('global.operation_approval') }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('operations.update', $operation) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label for="operation_scrub_nurse_id{{ $operation->id }}">{{ localize('global.scrub_nurse') }}</label>
                                                                <select class="form-control select2" name="operation_scrub_nurse_id"
                                                                    id="operation_scrub_nurse_id">
                                                                    <option value="">{{ localize('global.select') }}</option>
                                                                    @foreach ($operation_doctors as $value)
                                                                        <option value="{{ $value->id }}"
                                                                            {{ old('operation_scrub_nurse_id', $operation->operation_scrub_nurse_id) == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                
                                                            <div class="col-md-6">
                                                                <label for="operation_circulation_nurse_id{{ $operation->id }}">{{ localize('global.circulation_nurse') }}</label>
                                                                <select class="form-control select2" name="operation_circulation_nurse_id"
                                                                    id="operation_circulation_nurse_id">
                                                                    <option value="">{{ localize('global.select') }}</option>
                                                                    @foreach ($operation_doctors as $value)
                                                                        <option value="{{ $value->id }}"
                                                                            {{ old('operation_circulation_nurse_id', $operation->operation_circulation_nurse_id) == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label class="mt-2 mb-2" for="room_id{{ $operation->id }}">{{ localize('global.room') }}</label>
                                                                <select class="form-control select2" name="room_id" id="operation_room_id"
                                                                    {{ old('is_operation_approved', $operation->is_operation_approved) ? '' : 'disabled' }}>
                                                                    <option value="">{{ localize('global.select') }}</option>
                                                                    @foreach ($rooms as $value)
                                                                        <option value="{{ $value->id }}"
                                                                            {{ old('room_id', $operation->room_id) == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                
                                                            <div class="col-md-6">
                                                                <label class="mt-2 mb-2" for="bed_id{{ $operation->id }}">{{ localize('global.bed') }}</label>
                                                                <select class="form-control select2" name="bed_id" id="operation_bed_id"
                                                                    {{ old('is_operation_approved', $operation->is_operation_approved) ? '' : 'disabled' }}>
                                                                    <option value="">{{ localize('global.select') }}</option>
                                                                    @foreach ($beds as $value)
                                                                        <option value="{{ $value->id }}"
                                                                            {{ old('bed_id', $operation->bed_id) == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->number }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label for="date" class="mt-2 mb-2">{{ localize('global.date') }}</label>
                                                                <input type="date" class="form-control" name="date"
                                                                    value="{{ old('date', $operation->date) }}" />
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="time" class="mt-2 mb-2">{{ localize('global.time') }}</label>
                                                                <input type="time" class="form-control" name="time"
                                                                    value="{{ old('time', $operation->time) }}" />
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="form-group p-2">
                                                                <label>
                                                                    <input type="checkbox" name="is_operation_approved" value="1"
                                                                        {{ old('is_operation_approved', $operation->is_operation_approved) ? 'checked' : '' }}
                                                                        onchange="toggleRoomAndBedDropdowns(this)"> {{ localize('global.approve') }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                                <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>



                                <div class="modal fade" id="createOperationModal{{ $operation->id }}" tabindex="-1"
                                    aria-labelledby="createOperationModalLabel{{ $operation->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"
                                                    id="createOperationModalLabel{{ $operation->id }}">
                                                    {{ localize('global.complete_operation') }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('operations.complete', $operation) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="is_operation_done" value="1">

                                                    <div class="form-group">

                                                        <label
                                                            for="operation_result{{ $operation->id }}">{{ localize('global.operation_result') }}</label>
                                                        <select class="form-control form-select" name="operation_result"
                                                            id="operation_result">
                                                            <option value="1">{{ localize('global.success') }}
                                                            </option>
                                                            <option value="0">{{ localize('global.fail') }}</option>
                                                        </select>

                                                        <div class="form-group">
                                                            <label
                                                                for="operation_remark{{ $operation->id }}">{{ localize('global.operation_remark') }}</label>
                                                            <textarea class="form-control" id="operation_remark{{ $operation->id }}" name="operation_remark" rows="3"></textarea>
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
                            </div>

                           @if($operation->is_operation_done == 0)
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-tv p-1"></i>{{ localize('global.request_blood') }}</h5>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#requestBloodModal{{ $operation->id }}"><span><i
                                        class="bx bx-plus"></i></span></button>

                            <!-- Create  Lab Modal -->
                            <div class="modal fade" id="requestBloodModal{{ $operation->id }}" tabindex="-1"
                                aria-labelledby="requestBloodModalLabel{{ $operation->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="requestBloodModalLabel{{ $operation->id }}">
                                                {{ localize('global.request_blood') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('blood_banks.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" id="patient_id{{ $operation->patient_id }}"
                                                    name="patient_id" value="{{ $operation->patient_id }}">
                                                <input type="hidden" id="appointment_id{{ $operation->id }}"
                                                    name="appointment_id" value="{{ $operation->appointment->id }}">
                                                <input type="hidden"
                                                    id="operation_id{{ $operation->appointment->id }}"
                                                    name="operation_id" value="{{ $operation->id }}">
                                                <input type="hidden" id="department_id{{ $operation->id }}"
                                                    name="department_id" value="{{ auth()->user()->department_id }}">
                                                <input type="hidden" id="branch_id{{ $operation->id }}"
                                                    name="branch_id" value="{{ auth()->user()->branch_id }}">

                                                <div class="form-group">

                                                    <div class="form-group">
                                                        <label
                                                            for="blood_group{{ $operation->id }}">{{ localize('global.blood_group') }}</label>
                                                            <select class="form-control form-select" name="group"
                                                            id="group">
                                                            <option value="">{{ localize('global.select') }}
                                                            </option>
                                                            <option value="A">A</option>
                                                            <option value="B">B</option>
                                                            <option value="AB">AB</option>
                                                            <option value="O">O</option>
                                                        </select>
                                                        <label
                                                            for="blood_rh{{ $operation->id }}">{{ localize('global.blood_rh') }}</label>
                                                            <select class="form-control form-select" name="rh"
                                                            id="rh">
                                                            <option value="">{{ localize('global.select') }}
                                                            </option>
                                                            <option value="+">+</option>
                                                            <option value="-">-</option>
                                                        </select>
                                                        <label
                                                            for="quantity{{ $operation->id }}">{{ localize('global.quantity') }}</label>
                                                            <input type="text" class="form-control" name="quantity">
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
                                            <th>{{ localize('global.blood_group') }}</th>
                                            <th>{{ localize('global.rh') }}</th>
                                            <th>{{ localize('global.quantity') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($operation->bloodBanks as $bloodBank)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    {{ $bloodBank->patient->name }}
                                                </td>
                                                <td>
                                                    {{ $bloodBank->group }}
                                                </td>
                                                <td>
                                                    {{ $bloodBank->rh }}
                                                </td>
                                                <td>
                                                    {{ $bloodBank->quantity }}
                                                </td>
                                                <td>
                                                    <a href="{{ route('blood_banks.edit', $bloodBank->id) }}"><span><i
                                                                class="bx bx-edit"></i></span></a>
                                                    <a href="{{ route('blood_banks.destroy', $bloodBank->id) }}"><span><i
                                                                class="bx bx-trash text-danger"></i></span></a>

                                                </td>
                                            </tr>
                                        @empty
                                            <div class="container">
                                                <div
                                                    class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                    <div class=" badge bg-label-danger mt-4">
                                                        {{ localize('global.not_referred_to_bloodBank') }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif

                            @if ($operation->is_operation_done == 1)
                                <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                        class="bx bx-chat p-1"></i>{{ localize('global.add_remarks') }}</h5>

                                @if (isset($operation->operation_expense_remarks))
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#editOperationRemarks{{ $operation->id }}"><span><i
                                                class="bx bx-edit"></i></span></button>
                                @else
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                        data-bs-target="#createOperationRemarks{{ $operation->id }}"><span><i
                                                class="bx bx-plus"></i></span></button>
                                @endif
                            @endif
                            <!-- Create  Lab Modal -->
                            <div class="modal fade" id="createOperationRemarks{{ $operation->id }}" tabindex="-1"
                                aria-labelledby="createOperationRemarksLabel{{ $operation->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createOperationRemarksLabel{{ $operation->id }}">
                                                {{ localize('global.add_remarks') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('operations.update', $operation) }}" method="POST">
                                                @csrf
                                                @method('PUT')


                                                <div class="form-group">

                                                    <label
                                                        for="operation_expense_remarks{{ $operation->id }}">{{ localize('global.operation_expense_remarks') }}</label>
                                                    <textarea class="form-control" id="operation_expense_remarks{{ $operation->id }}" name="operation_expense_remarks"
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
                                        </form>
                                    </div>
                                </div>
                            </div>


                            <div class="modal fade" id="editOperationRemarks{{ $operation->id }}" tabindex="-1"
                                aria-labelledby="editOperationRemarksLabel{{ $operation->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editOperationRemarksLabel{{ $operation->id }}">
                                                {{ localize('global.refere_patient_to_another_doctor') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('operations.update', $operation) }}" method="POST">
                                                @csrf
                                                @method('PUT')


                                                <div class="form-group">

                                                    <label
                                                        for="operation_expense_remarks{{ $operation->id }}">{{ localize('global.operation_expense_remarks') }}</label>
                                                    <textarea class="form-control" id="operation_expense_remarks{{ $operation->id }}" name="operation_expense_remarks"
                                                        rows="3">{{ $operation->operation_expense_remarks }}</textarea>


                                                </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                            <button type="submit"
                                                class="btn btn-primary">{{ localize('global.save') }}</button>
                                        </div>
                                        </form>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="container">
                                <div class="col-md-12">
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            @if (isset($operation->operation_expense_remarks))
                                                <i class="bx bx-check-circle text-success"></i>
                                                <span class=" p-1 m-1">{{ $operation->operation_expense_remarks }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>


                            @if ($operation->is_operation_done == 1)
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-bed p-1"></i>{{ localize('global.hospitalize') }}</h5>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#createHospitalizationModal{{ $operation->id }}"><span><i
                                        class="bx bx-plus"></i></span></button>

                                        <div class="modal fade modal-xl" id="createHospitalizationModal{{ $operation->id }}"
                                            tabindex="-1" aria-labelledby="createHospitalizationModalLabel{{ $operation->id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="createHospitalizationModalLabel{{ $operation->id }}">
                                                            {{ localize('global.hospitalize_patient') }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('hospitalizations.store') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" id="patient_id{{ $operation->patient_id }}"
                                                                name="patient_id" value="{{ $operation->patient_id }}">
                                                            <input type="hidden" id="appointment_id{{ $operation->appointment->id }}"
                                                                name="appointment_id" value="{{ $operation->id }}">
                                                            <input type="hidden" id="doctor_id{{ $operation->id }}" name="doctor_id"
                                                                value="{{ auth()->user()->id }}">
                                                            <input type="hidden" id="branch_id{{ $operation->id }}" name="branch_id"
                                                                value="{{ auth()->user()->branch_id }}">
                                                            <input type="hidden" id="is_discharged{{ $operation->id }}"
                                                                name="is_discharged" value="0">
                
                                                            <div class="form-group">
                
                                                                <div class="form-group">
                                                                    <label
                                                                        for="reason{{ $operation->id }}">{{ localize('global.reason') }}</label>
                                                                    <textarea class="form-control" id="reason{{ $operation->id }}" name="reason" rows="3"></textarea>
                                                                </div>
                
                                                                <div class="form-group">
                                                                    <label
                                                                        for="remarks{{ $operation->id }}">{{ localize('global.remarks') }}</label>
                                                                    <textarea class="form-control" id="remarks{{ $operation->id }}" name="remarks" rows="3"></textarea>
                                                                </div>
                
                                                                <div class="form-group">
                                                                    <div class="row p-2">
                                                                        <div class="col-md-4">
                                                                            <label
                                                                                for="room_id{{ $operation->id }}">{{ localize('global.rooms') }}</label>
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
                                                                                for="bed_id{{ $operation->id }}">{{ localize('global.beds') }}</label>
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
                                                                                for="food_type_id{{ $operation->id }}">{{ localize('global.food_type') }}</label>
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
                        @endif


                        <div class="modal fade" id="createReserveModal{{ $operation->id }}" tabindex="-1"
                            aria-labelledby="createReserveModalLabel{{ $operation->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createReserveModalLabel{{ $operation->id }}">
                                            {{ localize('global.reserve_operation') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('operations.reserve', $operation->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" id="is_reserved{{ $operation->is_reserved }}"
                                                name="is_reserved" value="1">

                                            <div class="form-group">

                                                <div class="form-group">
                                                    <label
                                                        for="reserve_reason{{ $operation->id }}">{{ localize('global.reserve_reason') }}</label>
                                                    <textarea class="form-control" id="reserve_reason{{ $operation->id }}" name="reserve_reason" rows="3"></textarea>
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





                            @if ($operation->is_operation_done == 1)
                                <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                        class="bx bx-revision p-1"></i>{{ localize('global.under_review') }}</h5>

                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#createUnderReviewModal{{ $operation->id }}"><span><i
                                            class="bx bx-plus"></i></span></button>

                                <!-- Create  Lab Modal -->
                                <div class="modal fade" id="createUnderReviewModal{{ $operation->id }}" tabindex="-1"
                                    aria-labelledby="createUnderReviewModalLabel{{ $operation->id }}"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"
                                                    id="createUnderReviewModalLabel{{ $operation->id }}">
                                                    {{ localize('global.refere_to_under_review') }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('under_reviews.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" id="patient_id{{ $operation->patient_id }}"
                                                        name="patient_id" value="{{ $operation->patient_id }}">
                                                    <input type="hidden"
                                                        id="appointment_id{{ $operation->appointment->id }}"
                                                        name="appointment_id" value="{{ $operation->id }}">
                                                    <input type="hidden"
                                                        id="operation_id{{ $operation->appointment->id }}"
                                                        name="operation_id" value="{{ $operation->id }}">
                                                    <input type="hidden" id="doctor_id{{ $operation->id }}"
                                                        name="doctor_id" value="{{ auth()->user()->id }}">
                                                    <input type="hidden" id="branch_id{{ $operation->id }}"
                                                        name="branch_id" value="{{ auth()->user()->branch_id }}">
                                                    <input type="hidden" id="is_discharged{{ $operation->id }}"
                                                        name="is_discharged" value="0">

                                                    <div class="form-group">

                                                        <div class="form-group">
                                                            <label
                                                                for="reason{{ $operation->id }}">{{ localize('global.reason') }}</label>
                                                            <textarea class="form-control" id="reason{{ $operation->id }}" name="reason" rows="3"></textarea>
                                                        </div>

                                                        <div class="form-group">
                                                            <label
                                                                for="remarks{{ $operation->id }}">{{ localize('global.remarks') }}</label>
                                                            <textarea class="form-control" id="remarks{{ $operation->id }}" name="remarks" rows="3"></textarea>
                                                        </div>


                                                        <label
                                                            for="room_id{{ $operation->id }}">{{ localize('global.rooms') }}</label>
                                                        <select class="form-control select2" name="room_id"
                                                            id="under_review_room">
                                                            <option value="">{{ localize('global.select') }}
                                                            </option>
                                                            @foreach ($rooms as $value)
                                                                <option value="{{ $value->id }}"
                                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                    {{ $value->name }}

                                                                </option>
                                                            @endforeach
                                                        </select>

                                                        <label
                                                            for="bed_id{{ $operation->id }}">{{ localize('global.beds') }}</label>
                                                        <select class="form-control select2" name="bed_id"
                                                            id="under_review_bed_id">
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
                                </div>
                                <div class="col-md-12 mt-4">




                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ localize('global.number') }}</th>
                                                <th>{{ localize('global.reason') }}</th>
                                                <th>{{ localize('global.remarks') }}</th>
                                                <th>{{ localize('global.room') }}</th>
                                                <th>{{ localize('global.bed') }}</th>
                                                <th>{{ localize('global.status') }}</th>
                                                <th>{{ localize('global.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($operation->under_reviews as $underReview)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $underReview->reason }}</td>
                                                    <td>
                                                        {{ $underReview->remarks }}
                                                    </td>
                                                    <td>
                                                        {{ $underReview->room->name }}
                                                    </td>


                                                    <td>
                                                        {{ $underReview->bed->number }}
                                                    </td>
                                                    <td>
                                                        @if ($underReview->is_discharged == '0')
                                                            <span
                                                                class="bx bx-x-circle text-danger">{{ localize('global.under_review') }}</span>
                                                        @else
                                                            <span
                                                                class="bx bx-check-circle text-success">{{ localize('global.discharged') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('under_reviews.edit', $underReview->id) }}"><span><i
                                                                    class="bx bx-edit"></i></span></a>
                                                        <a href="{{ route('under_reviews.destroy', $underReview->id) }}"><span><i
                                                                    class="bx bx-trash text-danger"></i></span></a>

                                                    </td>
                                                </tr>
                                            @empty
                                                <div class="container">
                                                    <div
                                                        class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                        <div class=" badge bg-label-danger mt-4">
                                                            {{ localize('global.no_previous_hospitalizations') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                            {{-- icu starts here  --}}
                            @if ($operation->is_operation_done == 1)
                                <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                        class="bx bx-tv p-1"></i>{{ localize('global.refere_to_icu') }}</h5>

                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#createICUModal{{ $operation->id }}"><span><i
                                            class="bx bx-plus"></i></span></button>

                                <!-- Create  Lab Modal -->
                                <div class="modal fade" id="createICUModal{{ $operation->id }}" tabindex="-1"
                                    aria-labelledby="createICUModalLabel{{ $operation->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createICUModalLabel{{ $operation->id }}">
                                                    {{ localize('global.refere_to_icu') }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('icus.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" id="patient_id{{ $operation->patient_id }}"
                                                        name="patient_id" value="{{ $operation->patient_id }}">
                                                    <input type="hidden" id="appointment_id{{ $operation->id }}"
                                                        name="appointment_id" value="{{ $operation->appointment->id }}">
                                                    <input type="hidden"
                                                        id="operation_id{{ $operation->appointment->id }}"
                                                        name="operation_id" value="{{ $operation->id }}">
                                                    <input type="hidden" id="doctor_id{{ $operation->id }}"
                                                        name="doctor_id" value="{{ auth()->user()->id }}">
                                                    <input type="hidden" id="branch_id{{ $operation->id }}"
                                                        name="branch_id" value="{{ auth()->user()->branch_id }}">

                                                    <div class="form-group">

                                                        <div class="form-group">
                                                            <label
                                                                for="description{{ $operation->id }}">{{ localize('global.description') }}</label>
                                                            <textarea class="form-control" id="description{{ $operation->id }}" name="description" rows="3"></textarea>
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
                                            @forelse ($operation->icu as $icu)
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
                                                    <div
                                                        class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                        <div class=" badge bg-label-danger mt-4">
                                                            {{ localize('global.not_referred_to_icu') }}
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
    </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $('#under_review_room').on('change', function() {
                var roomId = $(this).val();
                if (roomId !== '') {
                    $.ajax({
                        url: '/get_related_beds/' + roomId,
                        type: 'GET',
                        success: function(response) {

                            $('#under_review_bed_id').html(response);
                        }
                    })
                }
            });
            $('#operation_room_id').on('change', function() {
                var roomId = $(this).val();
                if (roomId !== '') {
                    $.ajax({
                        url: '/get_related_beds/' + roomId,
                        type: 'GET',
                        success: function(response) {

                            $('#operation_bed_id').html(response);
                        }
                    })
                }
            });
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
            });
        });

        function toggleRoomAndBedDropdowns(checkbox) {
        const roomDropdown = document.getElementById('operation_room_id');
        const bedDropdown = document.getElementById('operation_bed_id');

        if (checkbox.checked) {
            roomDropdown.disabled = false;
            bedDropdown.disabled = false;
        } else {
            roomDropdown.disabled = true;
            bedDropdown.disabled = true;
        }
    }
    </script>
@endsection
