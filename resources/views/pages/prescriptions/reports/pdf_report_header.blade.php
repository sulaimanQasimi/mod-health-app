<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <style type="text/css">
        body,
        body *,
        .label {
            font-family: 'Arial', sans-serif !important;
        }

        .excel_table_title {
            text-align: center;
        }

        #print_excel_table {
            border-collapse: collapse;
            width: 100%;
            direction: rtl;
        }

        #print_excel_table td,
        #print_excel_table th {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        #print_excel_table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        #print_excel_table tr:hover {
            background-color: #ddd;
        }

        #print_excel_table th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: center;
            background-color: #c2bfbf;
            color: rgb(34, 33, 33);
        }

        .department-summary {
            margin-bottom: 20px;
            border-collapse: collapse;
            width: 100%;
            direction: rtl;
        }

        .department-summary td,
        .department-summary th {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        .department-summary th {
            background-color: #4a90e2;
            color: white;
            font-weight: bold;
        }

        .department-summary tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2 class="excel_table_title">{{ localize('global.prescriptions_report_title') }}</h2>
    
    @if(!empty($departmentCounts) && count($departmentCounts) > 0)
    <div class="col-md-12 mt-2 mb-3">
        <h3 style="text-align: center; margin-bottom: 10px;">{{ localize('global.prescriptions_by_department') ?? 'Prescriptions by Department' }}</h3>
        <table class="department-summary">
            <thead>
                <tr>
                    <th>{{ localize('global.department') ?? 'Department' }}</th>
                    <th>{{ localize('global.count') ?? 'Count' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departmentCounts as $dept)
                <tr>
                    <td>{{ $dept['name'] }}</td>
                    <td>{{ $dept['count'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="col-md-12 mt-2">
        <table class="table" id="print_excel_table">
            <thead>
                <tr>
                    <th>{{ localize('global.number') }}</th>
                    <th>{{ localize('global.patient_name') }}</th>
                    <th>{{ localize('global.doctor_name') }}</th>
                    <th>{{ localize('global.branch') }}</th>
                    <th>{{ localize('global.status') }}</th>
                 
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
