<!DOCTYPE html>
<html>

<head>
    <title>Death Summary</title>
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
            direction: rtl;
            margin: 0 auto;
            border-collapse: collapse;
            width: 80%;
        }

        th,
        td {
            direction: rtl;
            padding: 10px;
            text-align: right;
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
            <h5 class="text-center"> {{localize('global.death_summary')}} </h5>
        </div>


        <div class="col-md-3 d-flex justify-content-end align-items-center">
            {!! QrCode::size(100)->generate($icu->patient->id) !!}
        </div>

    </div>

    <table>
        <tr>
            <td>{{localize('global.name')}}:</td>
            <td>{{ $icu->patient->name }}</td>
        </tr>
        <tr>
            <td>{{localize('global.father_name')}}:</td>
            <td>{{ $icu->patient->father_name }}</td>
        </tr>
        <tr>
            <td>{{localize('global.age')}}:</td>
            <td>{{ $icu->patient->age }}</td>
        </tr>
        <tr>
            <td>{{localize('global.rank')}}:</td>
            <td>{{ $icu->patient->rank }}</td>
        </tr>
        <tr>
            <td>{{localize('global.admission_date')}}:</td>
            <td>{{ verta($icu->appointment->created_at)->format('Y-m-d H:i') }}</td>
        </tr>

        <tr>
            <td>{{localize('global.icu_admission_date')}}:</td>
            <td>{{ verta($icu->created_at)->format('Y-m-d H:i') }}</td>
        </tr>
        <tr>
            <td>{{localize('global.procedures')}}:</td>
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
                                            class="bg-label-warning text-center p-1">{{ verta($procedure->created_at)->format('Y-m-d') }}</span>
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
            <td>{{localize('global.diagnose')}}:</td>
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
                                            class="bg-label-warning text-center p-1">{{ verta($diagnose->created_at)->format('Y-m-d') }}</span>
                                        {{ $diagnose->description }}
                                    </li>
                                @endforeach
                            </div>
                            <div class="col-md-6">
                                @foreach ($finalDiagnoses as $diagnose)
                                    <li class="m-1 p-1">
                                        <span
                                            class="bg-label-success text-center p-1">{{ verta($diagnose->created_at)->format('Y-m-d') }}</span>
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
            <td>{{localize('global.cause_of_death')}}:</td>
            <td>{{ $icu->cause_of_death }}</td>
        </tr>
    </table>
</body>

</html>
