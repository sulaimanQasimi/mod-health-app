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
                                        <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                                        <div>
                                            {{ $operation->patient->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.operation_type') }}</h5>
                                        <div>
                                            {{ $operation->operationType->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.date') }}</h5>
                                        <div>
                                            {{ $operation->date }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.time') }}</h5>
                                        <div>
                                            {{ $operation->time }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 d-flex justify-content-center mb-2">
                                    @if($operation->is_operation_done == 0)
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                        data-bs-target="#createOperationModal{{ $operation->id }}"><span><i
                                                class="bx bx-check"></i>{{ localize('global.complete_operation') }}</span></button>
                                                @endif
                                </div>


                                <div class="modal fade" id="createOperationModal{{ $operation->id }}" tabindex="-1"
                                    aria-labelledby="createOperationModalLabel{{ $operation->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createOperationModalLabel{{ $operation->id }}">
                                                    {{ localize('global.refere_to_operation') }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('operations.update', $operation) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="is_operation_done" value="1">

                                                    <div class="form-group">

                                                        <label
                                                            for="operation_result{{ $operation->id }}">{{ localize('global.doctors') }}</label>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
