


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
            font-family: dir_font;

            src: url('https://lms.imllab1989.com/public/assets/fonts/AdobeArabic-Regular.otf');
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
                        <img src="{{asset('assets/img/test_header.jpg')}}" alt="" height="300">
                    </td>
                </tr>
            </tbody>
        </table>


        <table width="100%" class="table table-bordered pdf-header">
            <tbody>
                <tr>
                    <td width="50%">
                        <span class="title">Patient's Name:</span>
                        <span class="data">
                            {{$patient->name}}
                        </span>
                    </td>
                    <td width="50%">
                        <span class="title">Doctor's Name:</span>
                        <span class="data">
                            {{$appointment->doctor->name}}
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
    </style>

    <div class="printable">



        <table class="table test beak-page">
            <thead>

            </thead>
            <tbody class=" b-000">
                <tr>
                    <td width="20%">
                        <b> <span class="title"> Past Health Status</span></b>

                        <br>
                        <span class="data">Febrile </span>

                        <br>
                        <br>
                        <b><span class="title">Diagnose</span></b>

                        <br>
                        <span class="data">
                            @foreach($appointment->diagnose as $diagnose)
                                {{$diagnose->description}}
                            @endforeach
                        </span>
                        <br>
                        <br>
                        <b><span class="title"> Advice</span></b>

                        <br>
                        <span class="data">MP
                            Typhoid</span>
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
                                @foreach ($prescriptions as $prescription)
                                                @php
                                                    $descriptions = is_array($prescription->description) ? $prescription->description : json_decode($prescription->description, true);
                                                    $dosages = is_array($prescription->dosage) ? $prescription->dosage : json_decode($prescription->dosage, true);
                                                    $frequencies = is_array($prescription->frequency) ? $prescription->frequency : json_decode($prescription->frequency, true);
                                                    $amounts = is_array($prescription->amount) ? $prescription->amount : json_decode($prescription->amount, true);
                                                    $types = is_array($prescription->type) ? $prescription->type : json_decode($prescription->type, true);
                                                @endphp
                                                @foreach ($descriptions as $key => $description)
                                                <tr>
                                                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                                                    <td style="text-align: center;">{{ $types[$key] }}</td>
                                                    <td style="text-align: center;">{{ $description }}</td>
                                                    <td style="text-align: center;">{{ $dosages[$key] }}</td>
                                                    <td style="text-align: center;">{{ $frequencies[$key] }}</td>
                                                    <td style="text-align: center;">{{ $amounts[$key] }}</td>
                                                </tr>
                                                @endforeach
                                            @endforeach
                            </tbody>
                        </table>
                        <p><br></p>
                        <p><br></p>
                    </td>
                </tr>
            </tbody>

        </table>


    </div>


    <table width="100%" style="padding:0px;border: none !important;">
        <tbody>
            <tr>
                <td align="center" style="padding:0px; border: none !important;">
                    <img src="{{asset('assets/img/test_header.jpg')}}" alt="" height="300">
                </td>
            </tr>
        </tbody>
    </table>

</body>

</html>
