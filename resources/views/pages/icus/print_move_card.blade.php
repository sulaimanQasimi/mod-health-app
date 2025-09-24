<!DOCTYPE html>
<html>

<head>
    <title>Transfer Sheet</title>
    <style>
        @font-face {
            font-family: 'ModFont';
            src: url('{{ asset("assets/fonts/mod_font.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        
        *{
            font-family: 'ModFont', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        table {
            margin: 0 auto;
            border-collapse: collapse;
            width: 80%;
            direction: rtl;
        }

        th,
        td {
            padding: 10px;
            text-align: right;
            border: 1px solid #ddd !important;
            color: black !important;
            direction: rtl;
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
            <h2>{{$icu->branch ? $icu->branch->name : 'No Branch'}}</h2>
            <h6 class="text-center">{{$icu->branch ? $icu->branch->address : 'No Address'}}</h6>
            <h5 class="text-center"> {{localize('global.transfer_sheet')}}</h5>
        </div>


        <div class="col-md-3 d-flex justify-content-end align-items-center">
            {!! QrCode::size(100)->generate($icu->patient ? $icu->patient->id : 'No Patient') !!}
        </div>

    </div>
    <table>
        <tr>
            <td>{{localize('global.name')}}</td>
            <td>{{ $icu->patient ? $icu->patient->name : 'No Patient' }}</td>
        </tr>
        <tr>
            <td>{{localize('global.father_name')}}</td>
            <td>{{ $icu->patient ? $icu->patient->father_name : 'No Father Name' }}</td>
        </tr>
        <tr>
            <td>{{localize('global.age')}}</td>
            <td>{{ $icu->patient ? $icu->patient->age : 'No Age' }}</td>
        </tr>
        <tr>
            <td>{{localize('global.transfer_date')}}</td>
            <td>{{ $icu->transfer_date ?? 'No Transfer Date' }}</td>
        </tr>
        <tr>
            <td>{{localize('global.brief_history')}}</td>
            <td>{{ $icu->brief_history ?? 'No Brief History' }}</td>
        </tr>

        <tr>
            <td>{{localize('global.procedures')}}</td>
            <td>
                <div class="container">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <h5 class="mb-4 p-1 bg-label-success text-center"><i
                                            class="bx bx-popsicle p-1"></i>{{localize('global.procedures')}}
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                @if($icu->procedures && $icu->procedures->count() > 0)
                                @foreach ($icu->procedures as $procedure)
                                    <li class="m-1 p-1">
                                        <span
                                            class="bg-label-warning text-center p-1">{{ $procedure->created_at ? verta($procedure->created_at)->format('Y-m-d') : 'No Date' }}</span>
                                            @if($procedure->procedure_type)
                                            <span
                                            class="bg-label-danger text-center p-1">{{ $procedure->procedure_type->name }}</span>
                                            @else
                                            <span
                                            class="bg-label-danger text-center p-1">{{localize('global.no_procedure_type')}}</span>
                                            @endif
                                        {{ $procedure->description ?? 'No Description' }}
                                    </li>
                                @endforeach
                                @else
                                <li class="m-1 p-1">{{localize('global.no_procedures_found')}}</li>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td>{{localize('global.diagnoses')}}</td>
            @php
                $primaryDiagnoses = $icu->appointment ? $icu->appointment->diagnose->where('type', 0) : collect();
                $finalDiagnoses = $icu->appointment ? $icu->appointment->diagnose->where('type', 1) : collect();
            @endphp
            <td>
                <div class="container">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="col-md-12">
                                    <h5 class="mb-4 p-1 bg-label-warning text-center"><i
                                            class="bx bx-popsicle p-1"></i>{{localize('global.primary_diagnoses')}}
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="col-md-12">
                                    <h5 class="mb-4 p-1 bg-label-success text-center"><i
                                            class="bx bx-popsicle p-1"></i>{{localize('global.final_diagnoses')}}
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                @if($primaryDiagnoses && $primaryDiagnoses->count() > 0)
                                @foreach ($primaryDiagnoses as $diagnose)
                                    <li class="m-1 p-1">
                                        <span
                                            class="bg-label-warning text-center p-1">{{ $diagnose->created_at ? verta($diagnose->created_at)->format('Y-m-d') : 'No Date' }}</span>
                                        {{ $diagnose->description ?? 'No Description' }}
                                    </li>
                                @endforeach
                                @else
                                <li class="m-1 p-1">{{localize('global.no_primary_diagnoses_found')}}</li>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($finalDiagnoses && $finalDiagnoses->count() > 0)
                                @foreach ($finalDiagnoses as $diagnose)
                                    <li class="m-1 p-1">
                                        <span
                                            class="bg-label-success text-center p-1">{{ $diagnose->created_at ? verta($diagnose->created_at)->format('Y-m-d') : 'No Date' }}</span>
                                        {{ $diagnose->description ?? 'No Description' }}
                                    </li>
                                @endforeach
                                @else
                                <li class="m-1 p-1">{{localize('global.no_final_diagnoses_found')}}</li>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td>{{localize('global.operations')}}</td>
            <td>
                <div class="container">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <h5 class="mb-4 p-1 bg-label-success text-center"><i
                                            class="bx bx-popsicle p-1"></i>{{localize('global.operations')}}
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                @if($icu->appointment)
                                @foreach ($icu->appointment->anesthesias as $operation)
                                    <li class="m-1 p-1">
                                        <span
                                            class="bg-label-warning text-center p-1">{{ $operation->created_at ? verta($operation->created_at)->format('Y-m-d') : 'No Date' }}</span>
                                            @if($operation->surgion)
                                            <span
                                            class="bg-label-danger text-center p-1">{{ $operation->surgion->name }}</span>
                                            @else
                                            <span
                                            class="bg-label-danger text-center p-1">{{localize('global.no_surgeon')}}</span>
                                            @endif
                                            @if($operation->operationType)
                                            <span
                                            class="bg-label-danger text-center p-1">{{ $operation->operationType->name }}</span>
                                            @else
                                            <span
                                            class="bg-label-danger text-center p-1">{{localize('global.no_operation_type')}}</span>
                                            @endif
                                        {{ $operation->operation_remark ?? 'No Operation Remark' }}
                                    </li>
                                @endforeach
                                @else
                                <li class="m-1 p-1">{{localize('global.no_appointments_found')}}</li>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td>{{localize('global.consultations')}}</td>
            <td>
                <div class="container">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <h5 class="mb-4 p-1 bg-label-success text-center"><i
                                            class="bx bx-popsicle p-1"></i>{{localize('global.consultations')}}
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                @if($icu->consultations && $icu->consultations->count() > 0)
                                @foreach ($icu->consultations as $consultation)
                                    <li class="m-1 p-1">
                                        <span
                                            class="bg-label-warning text-center p-1">{{ $consultation->created_at ? verta($consultation->created_at)->format('Y-m-d') : 'No Date' }}</span>
                                            @if ($consultation->consultation_type ==0)
                                            <span
                                            class="bg-label-warning text-center p-1">{{localize('global.normal')}}</span>
                                            @else
                                            <span
                                            class="bg-label-danger text-center p-1">{{localize('global.emergency')}}</span>
                                            @endif
                                        {{ $consultation->title ?? 'No Title' }}
                                        <ul>
                                        @foreach($consultation->comments as $comment)
                                            <li class="m-1 p-1">
                                                <span
                                            class="bg-label-warning text-center p-1">{{ $comment->created_at ? verta($comment->created_at)->format('Y-m-d') : 'No Date' }}</span>
                                            @if($comment->department)
                                            <span
                                            class="bg-label-danger text-center p-1">{{ $comment->department->name }}</span>
                                            @else
                                            <span
                                            class="bg-label-danger text-center p-1">{{localize('global.no_department')}}</span>
                                            @endif
                                            @if($comment->doctor)
                                            <span
                                            class="bg-label-success text-center p-1">{{ $comment->doctor->name }}</span>
                                            @else
                                            <span
                                            class="bg-label-success text-center p-1">{{localize('global.no_doctor')}}</span>
                                            @endif
                                            {{$comment->comment ?? 'No Comment'}}
                                            </li>
                                        @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                                @else
                                <li class="m-1 p-1">{{localize('global.no_consultations_found')}}</li>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
