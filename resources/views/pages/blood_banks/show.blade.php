@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.blood_request_details') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="col-md-12">
                            <div class="border border-label-primary mb-4">
                                <h5 class="mb-4 p-3 bg-label-primary text-center">
                                    {{ localize('global.blood_request_details') }}</h5>

                                <div class="row p-2 text-center">
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.patient_name') }}</h5>
                                        <div>
                                            {{ $bloodBank->patient->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.requested_department') }}</h5>
                                        <div>
                                            {{ $bloodBank->department->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.blood_group') }}</h5>
                                        <div>
                                            @if ($bloodBank->group == 'A')
                                                <span class="text-danger"><i class="fa-solid fa-a"></i></span>
                                            @elseif($bloodBank->group == 'B')
                                                <span class="text-danger"><i class="fa-solid fa-b"></i></span>
                                            @elseif($bloodBank->group == 'AB')
                                                <span class="text-danger" dir="ltr"><i class="fa-solid fa-a"></i><i
                                                        class="fa-solid fa-b"></i></span>
                                            @elseif($bloodBank->group == 'O')
                                                <span class="text-danger"><i class="fa-solid fa-o"></i></span>
                                            @endif

                                        </div>
                                    </div>
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.blood_rh') }}</h5>
                                        <div>
                                            @if ($bloodBank->rh == '+')
                                                <span class="bx bx-plus-circle text-danger"></span>
                                            @else
                                                <span class="bx bx-minus-circle text-danger"></span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="row p-2 text-center">
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.quantity') }}</h5>
                                        <div>
                                            {{ $bloodBank->quantity }}
                                        </div>
                                    </div>
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.status') }}</h5>
                                        <div>
                                            {{ $bloodBank->status }}
                                        </div>
                                    </div>
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.created_by') }}</h5>
                                        <div>
                                            
                                            {{$bloodBank->createdBy->name}}
                                        </div>
                                    </div>
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.created_at') }}</h5>
                                        <div dir="ltr">
                                            {{$bloodBank->created_at->format('Y-m-d H:m:s')}}
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="row d-flex justify-content-center">
                                @if($bloodBank->status != 'delivered')
                                @if($bloodBank->status != 'approved')
                                <div class="col-md-4 text-center">
                                    <button class="btn btn-primary">
                                        <a href="{{route('blood_banks.approve', $bloodBank->id)}}" class="text-white">
                                          <span><i class="bx bx-calendar-check"></i>{{localize('global.approve')}}</span>
                                        </a>
                                      </button>
                                </div>
                                @endif
                                @if($bloodBank->status != 'rejected')
                                <div class="col-md-4 text-center">
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#createRejectModal{{ $bloodBank->id }}"><span><i
                                                        class="bx bx-calendar-x"></i>{{ localize('global.reject') }}</span></button>
                                </div>
                                @endif
                                @endif
                                @if($bloodBank->status == 'approved' || $bloodBank->status == 'rejected')
                                <div class="col-md-4 text-center">
                                    <button class="btn btn-success">
                                        <a href="{{route('blood_banks.deliver', $bloodBank->id)}}" class="text-white">
                                          <span><i class="bx bxs-check-circle"></i>{{localize('global.complete')}}</span>
                                        </a>
                                      </button>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="modal fade" id="createRejectModal{{ $bloodBank->id }}" tabindex="-1"
                            aria-labelledby="createRejectModalLabel{{ $bloodBank->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createRejectModalLabel{{ $bloodBank->id }}">
                                            {{ localize('global.reject_request') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('blood_banks.reject', $bloodBank->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" id="is_reserved{{ $bloodBank->is_reserved }}"
                                                name="is_reserved" value="1">

                                            <div class="form-group">

                                                <div class="form-group">
                                                    <label
                                                        for="reject_reason{{ $bloodBank->id }}">{{ localize('global.reject_reason') }}</label>
                                                    <textarea class="form-control" id="reject_reason{{ $bloodBank->id }}" name="reject_reason" rows="3"></textarea>
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
@endsection
