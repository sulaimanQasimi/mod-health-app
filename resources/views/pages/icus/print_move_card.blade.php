<!DOCTYPE html>
<html>

<head>
    <title>Transfer Sheet</title>
    <style>
        table {
            margin: 0 auto;
            border-collapse: collapse;
            width: 80%;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd !important;
            color: black !important;
        }

        h2 {
            text-align: center !important;
            padding: 2%;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
</head>

<body>
    <div class="row m-2">
        <div class="col-md-3">

        </div>
        <div class="col-md-6">
            <h2>{{$icu->branch->name}}</h2>
            <h6 class="text-center">{{$icu->branch->address}}</h6>
            <h5 class="text-center"> Transfer Sheet </h5>
        </div>


        <div class="col-md-3 d-flex justify-content-end align-items-center">
            {!! QrCode::size(100)->generate($icu->patient->id) !!}
        </div>

    </div>
    <table>
        <tr>
            <td>Name:</td>
            <td>{{ $icu->patient->name }}</td>
        </tr>
        <tr>
            <td>Father Name:</td>
            <td>{{ $icu->patient->father_name }}</td>
        </tr>
        <tr>
            <td>Age:</td>
            <td>{{ $icu->patient->age }}</td>
        </tr>
        <tr>
            <td>Transfer Date:</td>
            <td>{{ $icu->transfer_date }}</td>
        </tr>
        <tr>
            <td>Brief History</td>
            <td>{{ $icu->brief_history }}</td>
        </tr>

        <tr>
            <td>Procedures:</td>
            <td>
                <div class="container">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <h5 class="mb-4 p-1 bg-label-success text-center"><i
                                            class="bx bx-popsicle p-1"></i>Procedures
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                @foreach ($icu->procedures as $procedure)
                                    <li class="m-1 p-1">
                                        <span
                                            class="bg-label-warning text-center p-1">{{ $procedure->created_at->format('Y-m-d') }}</span>
                                            <span
                                            class="bg-label-danger text-center p-1">{{ $procedure->procedure_type->name }}</span>
                                        {{ $procedure->description }}
                                    </li>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td>Diagnoses:</td>
            @php
                $primaryDiagnoses = $icu->appointment->diagnose->where('type', 0);
                $finalDiagnoses = $icu->appointment->diagnose->where('type', 1);
            @endphp
            <td>
                <div class="container">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="col-md-12">
                                    <h5 class="mb-4 p-1 bg-label-warning text-center"><i
                                            class="bx bx-popsicle p-1"></i>Primary Diagnoses
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="col-md-12">
                                    <h5 class="mb-4 p-1 bg-label-success text-center"><i
                                            class="bx bx-popsicle p-1"></i>Final Diagnoses
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
            </td>
        </tr>
        <tr>
            <td>Operations:</td>
            <td>
                <div class="container">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <h5 class="mb-4 p-1 bg-label-success text-center"><i
                                            class="bx bx-popsicle p-1"></i>Operations
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                @foreach ($icu->appointment->anesthesias as $operation)
                                    <li class="m-1 p-1">
                                        <span
                                            class="bg-label-warning text-center p-1">{{ $operation->created_at->format('Y-m-d') }}</span>
                                            <span
                                            class="bg-label-danger text-center p-1">{{ $operation->surgion->name }}</span>
                                            <span
                                            class="bg-label-danger text-center p-1">{{ $operation->operationType->name }}</span>
                                        {{ $operation->operation_remark }}
                                    </li>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td>consultations:</td>
            <td>
                <div class="container">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <h5 class="mb-4 p-1 bg-label-success text-center"><i
                                            class="bx bx-popsicle p-1"></i>consultations
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                @foreach ($icu->consultations as $consultation)
                                    <li class="m-1 p-1">
                                        <span
                                            class="bg-label-warning text-center p-1">{{ $consultation->created_at->format('Y-m-d') }}</span>
                                            @if ($consultation->consultation_type ==0)
                                            <span
                                            class="bg-label-warning text-center p-1">Normal</span>
                                            @else
                                            <span
                                            class="bg-label-danger text-center p-1">Emergency</span>
                                            @endif
                                        {{ $consultation->title }}
                                        <ul>
                                        @foreach($consultation->comments as $comment)
                                            <li class="m-1 p-1">
                                                <span
                                            class="bg-label-warning text-center p-1">{{ $comment->created_at->format('Y-m-d') }}</span>
                                            <span
                                            class="bg-label-danger text-center p-1">{{ $comment->department->name }}</span>
                                            <span
                                            class="bg-label-success text-center p-1">{{ $comment->doctor->name }}</span>
                                            {{$comment->comment}}
                                            </li>
                                        @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
