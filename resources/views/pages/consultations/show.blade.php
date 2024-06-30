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
                        <h5 class="mb-0">{{ localize('global.consultation_details') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="col-md-12">
                            <div class="border border-label-primary mb-4">
                                <h5 class="mb-4 p-3 bg-label-primary text-center">
                                    {{ localize('global.appointment_details') }}</h5>

                                <div class="row p-2 text-center">
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                                        <div>
                                            {{ $consultation->appointment->patient->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.referred_to') }}</h5>
                                        <div>
                                            {{ $consultation->appointment->doctor->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.date') }}</h5>
                                        <div>
                                            {{ $consultation->appointment->date }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.time') }}</h5>
                                        <div>
                                            {{ $consultation->appointment->time }}
                                        </div>
                                    </div>
                                </div>


                                <div class="row p-4">
                                    <div class="mb-4">
                                        <div class="col-md-12 d-flex justify-content-center">
                                            <h5 class="mb-4 p-1 bg-label-primary mt-4"><i
                                                    class="bx bx-history p-1"></i>{{ localize('global.patient_history') }}
                                            </h5>
                                        </div>
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


                            </div>
                        </div>
                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                            class="bx bx-chat p-1"></i>{{ localize('global.doctors_comments') }}</h5>

                        <div class="container">
                            <div class="col-md-12">
                                <div class="row">
                                    @forelse ($consultation->comments as $comment )
                                    <div class="col-md-3">
                                        
                                        <span
                                        class="bg-label-primary p-1 m-1">{{ $comment->department->name }}</span>
                                        
                                    </div>
                                    <div class="col-md-1">
                                        <i class="bx bx-transfer text-success"></i>
                                    </div>
                                        <div class="col-md-2">
                                            <span
                                            class="bg-label-primary p-1 m-1">{{ $comment->doctor->name }}</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-10 p-4 mt-2" style="text-align: justify;">
                                                {{ $comment->comment }}
                                            </div>
                                        </div>
                                        <div class="white-space">
                                            <hr>
                                        </div>
                                    @empty
                                    <div class="container">
                                        <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                            <div class="p-2 bg-label-danger mt-4">
                                                {{ localize('global.no_comments_yet') }}
                                            </div>
                                        </div>
                                    </div>

                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <h5 class="mb-4 p-3 bg-label-success mt-4"><i
                            class="bx bx-chat p-1"></i>{{ localize('global.add_comment') }}</h5>

                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#createCommentModal{{ $appointment->id }}"><span><i
                                class="bx bx-plus"></i></span></button>
                    <!-- Create  Lab Modal -->
                    <div class="modal fade" id="createCommentModal{{ $appointment->id }}" tabindex="-1"
                        aria-labelledby="createCommentModalLabel{{ $appointment->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="createCommentModalLabel{{ $appointment->id }}">
                                        {{ localize('global.add_consultation_comment') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('consultation_comments.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                            name="patient_id" value="{{ $appointment->patient_id }}">
                                        <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                            name="appointment_id" value="{{ $appointment->id }}">
                                        <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                            value="{{ auth()->user()->id }}">
                                        <input type="hidden" id="department_id{{ $appointment->id }}" name="department_id"
                                            value="{{ $appointment->doctor->department_id }}">
                                        <input type="hidden" id="consultation_id{{ $appointment->id }}" name="consultation_id"
                                            value="{{ $consultation->id }}">

                                        <div class="form-group">

                                            <label
                                                for="comment{{ $appointment->id }}">{{ localize('global.consultation_comment') }}</label>
                                                <textarea class="form-control mt-2" id="reason{{ $appointment->id }}" name="comment" rows="3"></textarea>
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
