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
                                    {{ localize('global.anesthesia_details') }}</h5>

                                <div class="row p-2 text-center">
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                                        <div>
                                            {{ $anesthesia->patient->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.operation_type') }}</h5>
                                        <div>
                                            {{ $anesthesia->operationType->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.date') }}</h5>
                                        <div>
                                            {{ $anesthesia->date }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.time') }}</h5>
                                        <div>
                                            {{ $anesthesia->time }}
                                        </div>
                                    </div>
                                </div>
                                <div class="row p-2 text-center">
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.operation_plan') }}</h5>
                                        <div>
                                            {{ $anesthesia->plan }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.operation_duration') }}</h5>
                                        <div>
                                            {{ $anesthesia->planned_duration }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.position_on_bed') }}</h5>
                                        <div>
                                            {{ $anesthesia->position_on_bed }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.estimated_blood_waste') }}</h5>
                                        <div>
                                            {{ $anesthesia->estimated_blood_waste }}
                                        </div>
                                    </div>
                                </div>
                                <div class="row p-2 text-center">
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.other_problems') }}</h5>
                                        <div>
                                            {{ $anesthesia->other_problems }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.operation_surgion') }}</h5>
                                        <div>
                                            {{ $anesthesia->surgion->name }}
                                        </div>
                                    </div>
                                    @if(isset($anesthesia->anesthesia_log->name))
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.anesthesia_log') }}</h5>
                                        <div>
                                            {{$anesthesia->anesthesia_log->name}}
                                        </div>
                                    </div>
                                    @endif
                                    @if(isset($anesthesia->anesthesist->name))
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.anesthesist') }}</h5>
                                        <div>
                                            {{$anesthesia->anesthesist->name}}
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <div class="row p-2 text-center">
                                    @if(isset($anesthesia->scrub_nurse->name))
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.scrub_nurse') }}</h5>
                                        <div>
                                            {{ $anesthesia->scrub_nurse->name }}
                                        </div>
                                    </div>
                                    @endif
                                    @if(isset($anesthesia->circulation_nurse->name))
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.circulation_nurse') }}</h5>
                                        <div>
                                            {{ $anesthesia->circulation_nurse->name }}
                                        </div>
                                    </div>
                                    @endif
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.anesthesia_log_reply') }}</h5>
                                        <div>
                                            {{ $anesthesia->anesthesia_log_reply }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.anesthesia_plan') }}</h5>
                                        <div>
                                            {{ $anesthesia->anesthesia_plan }}
                                        </div>
                                    </div>
                                </div>

                                @if($anesthesia->status == 'new')
                                <hr class="border border-label-primary">
                                <div class="d-flex justify-content-center mb-2 p-2">
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                            data-bs-target="#createAnasthesiaModal{{ $anesthesia->id }}"><span><i
                                                    class="bx bx-check"></i>{{localize('global.approve')}}</span></button>
                                        </div>

                                        <div class="col-md-2">
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#createAnasthesiaRejectModal{{ $anesthesia->id }}"><span><i
                                                class="bx bx-x"></i>{{localize('global.reject')}}</span></button>
                                        </div>
                                </div>
                                @endif
                                <div class="modal fade" id="createAnasthesiaModal{{ $anesthesia->id }}" tabindex="-1"
                                    aria-labelledby="createAnasthesiaModalLabel{{ $anesthesia->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createAnasthesiaModalLabel{{ $anesthesia->id }}">
                                                    {{ localize('global.refere_to_operation') }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('anesthesias.update', $anesthesia) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden"
                                                        name="status" value="approved">

                                                    <div class="form-group">

                                                        <div class="form-group">
                                                            <label
                                                                for="anesthesia_log_reply{{ $anesthesia->id }}">{{ localize('global.anesthesia_log_reply') }}</label>
                                                            <textarea class="form-control" id="anesthesia_log_reply{{ $anesthesia->id }}" name="anesthesia_log_reply" rows="3"></textarea>
                                                        </div>

                                                        <div class="form-group">
                                                            <label
                                                                for="anesthesia_plan{{ $anesthesia->id }}">{{ localize('global.anesthesia_plan') }}</label>
                                                            <textarea class="form-control" id="anesthesia_plan{{ $anesthesia->id }}" name="anesthesia_plan" rows="3"></textarea>
                                                        </div>

                                                        <div class="row">
                                                        <div class="col-md-6">
                                                            <label
                                                                for="operation_anesthesia_log_id{{ $anesthesia->id }}">{{ localize('global.anesthesia_log') }}</label>
                                                            <select class="form-control select2"
                                                                name="operation_anesthesia_log_id"
                                                                id="operation_anesthesia_log_id">
                                                                <option value="">{{ localize('global.select') }}
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
                                                                for="anesthesist{{ $anesthesia->id }}">{{ localize('global.anesthesist') }}</label>
                                                            <select class="form-control select2"
                                                                name="operation_anesthesist_id"
                                                                id="operation_anesthesist_id">
                                                                <option value="">{{ localize('global.select') }}
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

                                <div class="modal fade" id="createAnasthesiaRejectModal{{ $anesthesia->id }}" tabindex="-1"
                                    aria-labelledby="createAnasthesiaRejectModalLabel{{ $anesthesia->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createAnasthesiaRejectModalLabel{{ $anesthesia->id }}">
                                                    {{ localize('global.rejection_reason') }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('anesthesias.update', $anesthesia) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden"
                                                        name="status" value="rejected">

                                                    <div class="form-group">

                                                        <div class="form-group">
                                                            <label
                                                                for="rejection_reason{{ $anesthesia->id }}">{{ localize('global.rejection_reason') }}</label>
                                                            <textarea class="form-control" id="rejection_reason{{ $anesthesia->id }}" name="anesthesia_log_reply" rows="3"></textarea>
                                                        </div>

                                                        <div class="form-group">
                                                            <label
                                                                for="anesthesia_plan{{ $anesthesia->id }}">{{ localize('global.anesthesia_plan') }}</label>
                                                            <textarea class="form-control" id="anesthesia_plan{{ $anesthesia->id }}" name="anesthesia_plan" rows="3"></textarea>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
