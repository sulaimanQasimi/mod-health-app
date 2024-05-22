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
                                <div class="col-md-12 d-flex justify-content-center mb-2">
                                    @if($anesthesia->status == 0)
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#createAnasthesiaModal{{ $anesthesia->id }}"><span><i
                                            class="bx bx-check"></i>{{localize('global.approve')}}</span></button>
                                    @else
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#createAnasthesiaRejectModal{{ $anesthesia->id }}"><span><i
                                            class="bx bx-x"></i>{{localize('global.reject')}}</span></button>
                                    @endif
                                </div>


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
                                                        name="status" value="1">
        
                                                    <div class="form-group">
        
                                                        <div class="form-group">
                                                            <label
                                                                for="anesthesia_log_reply{{ $anesthesia->id }}">{{ localize('global.anesthesia_log_reply') }}</label>
                                                            <textarea class="form-control" id="anesthesia_log_reply{{ $anesthesia->id }}" name="anesthesia_log_reply" rows="3"></textarea>
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
                                                        name="status" value="0">
        
                                                    <div class="form-group">
        
                                                        <div class="form-group">
                                                            <label
                                                                for="rejection_reason{{ $anesthesia->id }}">{{ localize('global.rejection_reason') }}</label>
                                                            <textarea class="form-control" id="rejection_reason{{ $anesthesia->id }}" name="anesthesia_log_reply" rows="3"></textarea>
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
