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
                    <h5 class="mb-4 p-3 bg-label-primary text-center">{{ localize('global.view_patient') }}</h5>
            <div class="container">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-3">

                                    <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                                    <div>
                                        {{$patient->name}}
                                    </div>
                            </div>
                            <div class="col-md-3">
                                <h5 class="mb-2">{{ localize('global.last_name') }}</h5>
                                    <div>
                                        {{$patient->last_name}}
                                    </div>
                            </div>
                            <div class="col-md-3">
                                <h5 class="mb-2">{{ localize('global.phone') }}</h5>
                                    <div>
                                        {{$patient->phone}}
                                    </div>
                            </div>
                            <div class="col-md-3">
                                <h5 class="mb-2">{{ localize('global.nid') }}</h5>
                                    <div>
                                        {{$patient->nid}}
                                    </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-3">

                                    <h5 class="mb-2">{{ localize('global.province') }}</h5>
                                    <div>
                                        {{$patient->province->name_dr}}
                                    </div>
                            </div>
                            <div class="col-md-3">
                                <h5 class="mb-2">{{ localize('global.district') }}</h5>
                                    <div>
                                        {{$patient->district->name_dr}}
                                    </div>
                            </div>
                            <div class="col-md-3">
                                <h5 class="mb-2">{{ localize('global.referred_by') }}</h5>
                                    <div>
                                        {{$patient->recipient->name}}
                                    </div>
                            </div>
                            <div class="col-md-3">
                                <h5 class="mb-2">{{ localize('global.creation_date') }}</h5>
                                    <div>
                                        {{$patient->created_at}}
                                    </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 card p-1">
                        <!-- Left side content -->
                        <div class="row">
                            <div class="col-md-6 d-flex justify-content-end align-items-center">
                                {!! QrCode::size(100)->generate($patient->id) !!}
                            </div>
                            <div class="col-md-6 d-flex justify-content-start align-items-center">
                                @isset($patient->image)
                                <img src="{{ asset($patient->image) }}" alt="Patient Image" width="100" height="100">
                            @else
                            <div class=" badge bg-label-danger mt-4">
                                {{ localize('global.no_image') }}
                            </div>
                            @endisset
                            </div>
                        </div>
                    </div>

                </div>
            </div>

                <hr>
                <h5 class="mb-0 p-3 bg-label-primary">{{ localize('global.previous_appointments') }}</h5>

                <table class="table">
                    <thead>
                        <tr>
                            <th>{{localize('global.number')}}</th>
                            <th>{{localize('global.doctor_name')}}</th>
                            <th>{{localize('global.date')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patient->appointments as $appointment)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$appointment->doctor->name}}</td>
                            <td>{{$appointment->created_at}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <h5 class="mb-0 p-3 bg-label-primary">{{ localize('global.all_diagnoses') }}</h5>
                <div class="row p-4">
                    <div class="mb-4">
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

                <h5 class="mb-0 p-3 bg-label-primary">{{ localize('global.previous_labs') }}</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ localize('global.number') }}</th>
                            <th>{{ localize('global.test_name') }}</th>
                            <th>{{ localize('global.test_status') }}</th>
                            <th>{{ localize('global.result') }}</th>
                            <th>{{ localize('global.result_file') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($patient->appointments as $appointment)
                        @foreach($appointment->labs as $lab)
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

                            </tr>
                            @endforeach

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
</div>

@endsection

@section('scripts')

<script>
    $(document).ready(function()
{
    $('#department_id').on('change', function()
{
    var departmentID = $(this).val();
    if(departmentID !== '')
    {
        $.ajax({
            url: '/get_doctors/' + departmentID,
            type: 'GET',
            success: function(response)
            {

                $('#doctor_id').html(response);
            }
        })
    }
})
})
</script>

@endsection

