<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>
        {{ localize('global.prescription') }}
    </title>
    <style>
        @font-face {
        font-family: 'dir_font';
        src: url('../public/assets/fonts/AdobeArabic-Regular.otf') format('opentype');
        }

        @page {
            margin-top: 0px;
            margin-right: 30px;
            margin-left: 30px;
            margin-bottom: 0px;
        }

        .table-bordered {
            border: 1px solid #dee2e6;
            border-collapse: collapse;
            background-color: white !important;
        }

        .table-bordered,
        .table-bordered td {
            border: 1px solid #dee2e6;
        }

        .table-bordered thead th,
        .table-bordered thead td {
            border-bottom-width: 2px;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #ddd !important;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td,
        th {
            padding: 5px;
        }

        .title {
            background-color: #ddd;
        }

        .branch_name {
            color: #000000 !important;
            font-size: 10 !important;
            font-family: sans-serif !important;
        }

        .branch_info {
            color: #000000 !important;
            font-size: 10 !important;
            font-family: sans-serif !important;
        }

        .title {
            color: #000000 !important;
            font-size: 10 !important;
            font-family: sans-serif !important;
            font-weight: bolder !important;
        }

        .data {
            color: #000000 !important;
            font-size: 10 !important;
            font-family: sans-serif !important;
        }

        .header {
            border: 14 solid rgb(0, 0, 0);
            background-color: rgb(0, 0, 0);
            text-align: center !important;
        }

        .footer {
            border: 20 solid rgb(0, 0, 0);
            background-color: rgb(2, 0, 0);
            color: #000000 !important;
            font-size: 14 !important;
            font-family: cairo-bold !important;
            text-align: center !important;
        }

        .signature {
            color: #000000 !important;
            font-size: 18 !important;
            font-family: sans-serif !important;
        }
    </style>

</head>

<body>

    <htmlpageheader name="page-header">

        <table width="100%" style="padding:0px;border: none !important;">
            <tbody>
                <tr>
                    <td align="center" style="padding:0px; border: none !important;">
                        <img src="{{ asset('assets/img/header.PNG') }}" alt="" height="150" width="100%">
                    </td>
                </tr>
            </tbody>
        </table>


        <table width="100%" class="table table-bordered pdf-header" dir="rtl">
            <tbody>
                <tr>
                    <td width="50%" dir="rtl">
                        <span class="title">{{localize('global.name')}}</span>
                        <span class="data">
                            {{ $patient->name }}
                        </span>
                    </td>
                    <td width="50%" dir="rtl">
                        <span class="title">{{localize('global.department')}}</span>
                        <span class="data">
                            {{ $appointment->doctor->department->name }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
        <table width="100%" class="table table-bordered pdf-header" dir="rtl">
            <tbody>
                <tr>
                    <td width="50%" dir="rtl">
                        <span class="title">{{localize('global.father_name')}}</span>
                        <span class="data">
                            {{ $patient->father_name }}
                        </span>
                    </td>
                    <td width="50%">
                        <span class="title">{{localize('global.register_number')}}</span>
                        <span class="data">
                            {{ $patient->id }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
        <table width="100%" class="table table-bordered pdf-header" dir="rtl">
            <tbody>
                <tr>
                    <td width="50%" dir="rtl">
                        <span class="title">{{localize('global.job_and_rank')}}</span>
                        <span class="data">
                            {{ $patient->job ?? '' }} - {{$patient->rank ?? ''}}
                        </span>
                    </td>
                    <td width="50%">
                        <span class="title">{{localize('global.card_number')}}</span>
                        <span class="data">
                            {{ $patient->id_card ?? '' }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>

    </htmlpageheader>

    <style>
        .test_title {
            font-size: 20px;
            background-color: #dddddd;
            border: 1px solid black !important;
        }

        .beak-page {
            page-break-inside: avoid !important;
        }

        .subtitle {
            font-size: 15px;
        }

        .test {
            margin-top: 20px;
        }

        .transparent {
            border-color: white;
        }

        .transparent th {
            border-color: white;
        }

        .test_head td,
        th {
            border: 1px solid #000 !important;
        }

        .no-border {
            border-color: white;
        }

        .comment tr th,
        .comment tr td {
            border-color: white !important;
            vertical-align: top !important;
            text-align: left;
            padding: 0px !important;
        }

        .mw-100 {
            max-width: 100px;
        }

        .mw-200 {
            max-width: 200px;

        }

        .sensitivity {
            margin-top: 20px;
        }

        .test_title {
            color: #000000 !important;
            font-size: 15px !important;
            font-family: sans-serif !important;
        }

        .test_name {
            color: #000000 !important;
            font-size: 15px !important;
            font-family: sans-serif !important;
        }

        .test_head th {
            color: #000000 !important;
            font-size: 15px !important;
            font-family: sans-serif !important;
        }

        .unit {
            color: #000000 !important;
            font-size: 15px !important;
            font-family: sans-serif !important;
        }

        .reference_range {
            color: #000000 !important;
            font-size: 15px !important;
            font-family: sans-serif !important;
        }

        .result {
            color: #000000 !important;
            font-size: 18px !important;
            font-family: sans-serif !important;
        }

        .status {
            color: #000000 !important;
            font-size: 15px !important;
            font-family: sans-serif !important;
        }

        .comment th,
        .comment td {
            color: #000000 !important;
            font-size: 14px !important;
            font-family: sans-serif !important;
        }

        .antibiotic_name {
            color: #000000 !important;
            font-size: 14px !important;
            font-family: sans-serif !important;
        }

        .sensitivity {
            color: #000000 !important;
            font-size: 16px !important;
            font-family: sans-serif !important;
        }

        .commercial_name {
            color: #000000 !important;
            font-size: 14px !important;
            font-family: sans-serif !important;
        }

        .pdf-header {
            /*margin-top:50px; */
        }

        table,
        td,
        th {
            border: 1px solid #000 !important;
        }

        table {
            border-collapse: collapse !important;
        }

        .printable {
            min-height: 800px;
        }

        .badge {
    display: inline-block;
    padding: 0.3em 0.5em;
    font-size: 0.875em;
    font-weight: 700;
    color: white;
    background-color: #6c757d; /* Secondary color */
    border-radius: 0.25rem;
    text-align: center;
    transition: background-color 0.2s;
}

.badge:hover {
    background-color: #5a6268; /* Darker shade on hover */
}

footer {
            background-color: #a7a7a79c;
            color: white;
            text-align: center;
            padding: 5px 0;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        footer a {
            color: #ffffff;
            text-decoration: none;
            margin: 0 10px;
        }

        footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            footer {
                font-size: 0.9em;
            }
        }
    </style>

    <div class="printable">
        <table class="table test beak-page">
            <thead>

            </thead>
            <tbody class=" b-000">
                <tr>
                    <td width="20%">
                        <b> <span class=""> Date:</span></b>

                        <span class="data">
                            {{$appointment->created_at}}
                        </span>

                        <br>
                        <br>
                        {{-- <br>
                        <b> <span class=""> Past Diagnoses</span></b>

                        <br>
                        <span class="data">
                            @foreach ($patient->diagnoses as $diagnose)
                                <span class="badge" style="margin-top: 3%;"> {{$diagnose->description ?? 'Null' }}</span>
                            @endforeach
                        </span>

                        <br>
                        <br> --}}
                        <b><span class="">Current Diagnose</span></b>

                        <br>
                        <span class="data">
                            @foreach ($appointment->diagnose as $diagnose)
                            <span class="badge" style="margin-top: 3%;">{{ $diagnose->description }}</span>
                            @endforeach
                        </span>
                        <br>
                        <br>
                        <b><span class=""> Advices</span></b>

                        <br>
                        <span class="data">
                            @foreach ($appointment->advices as $advice)
                            <span style="margin-top: 3%;">{{ $advice->description }}</span>
                            <br>
                            @endforeach
                        </span>
                        <br>
                        <br>
                        <b><span class="">Signature</span></b>
                        <br>
                    </td>
                    <td width="80%">
                        <p><br></p>
                        <table class="table table-bordered">
                            <tbody>
                                <tr style="
    background: #ccc;
">
                                    <th width="100px" style="text-align: center; "><b>No.</b></th>
                                    <th width="150px" style="text-align: center; "><b>Type</b></th>
                                    <th style="text-align: center; width: 400px;"><b>Name Of Medicine</b></th>
                                    <th style="text-align: center;width: 250px;"><b>Dosage</b></th>
                                    <th style="text-align: center;width: 250px;"><b>Frequency</b></th>
                                    <th style="text-align: center; width: 150px;"><b>Amount</b></th>
                                </tr>
                                @foreach ($prescriptionItems as $prescription)
                                    <tr>
                                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                                        <td style="text-align: center;">{{ $prescription->medicineType->type }}</td>
                                        <td style="text-align: center;">{{ $prescription->medicine->name }}</td>
                                        <td style="text-align: center;">{{ $prescription->dosage }}</td>
                                        <td style="text-align: center;">{{ $prescription->frequency }}</td>
                                        <td style="text-align: center;">{{ $prescription->amount }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p><br></p>
                        <p><br></p>
                    </td>
                </tr>
            </tbody>

        </table>

        <footer style="margin-top: 5%;">
            <p>{{ QrCode::size(75)->generate($prescription->id) }}</p>
        </footer>
    </div>


    

    

</body>

</html>
