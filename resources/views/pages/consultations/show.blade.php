@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
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
                                <div class="row p-2 text-center">
                                    <div class="col-md-12 d-flex justify-content-center">
                                        <h5 class="mb-4 p-1 bg-label-primary mt-4">{{ localize('global.consultation_doctors') }}</h5>
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
                                class="bx bx-chat p-1"></i>{{ localize('global.consultation_details') }}</h5>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
