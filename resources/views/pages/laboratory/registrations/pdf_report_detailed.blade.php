<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style type="text/css">
        body, body * { font-family: 'Arial', sans-serif !important; }
        .excel_table_title { text-align: center; }
        #print_excel_table { border-collapse: collapse; width: 100%; direction: rtl; font-size: 10px; }
        #print_excel_table td, #print_excel_table th { border: 1px solid #ddd; padding: 6px; text-align: center; }
        #print_excel_table tr:nth-child(even) { background-color: #f2f2f2; }
        #print_excel_table th {
            padding-top: 8px; padding-bottom: 8px; text-align: center;
            background-color: #c2bfbf; color: rgb(34, 33, 33);
        }
    </style>
</head>

<body>
    <h2 class="excel_table_title">{{ localize('global.test_registration_report_detailed') ?? 'Full Detailed Test Report' }}</h2>
    <div class="col-md-12 mt-2">
        <table class="table" id="print_excel_table">
            <thead>
                <tr>
                    <th>{{ localize('global.number') }}</th>
                    <th>{{ localize('global.ref_no') }}</th>
                    <th>{{ localize('global.registration_date') }}</th>
                    <th>{{ localize('global.patient_name') }}</th>
                    <th>{{ localize('global.test_type') }}</th>
                    <th>{{ localize('global.status') }}</th>
                    <th>{{ localize('global.priority') }}</th>
                    <th>{{ localize('global.doctor') }}</th>
                    <th>{{ localize('global.branch') }}</th>
                    <th>{{ localize('global.created_by') }}</th>
                    <th>{{ localize('global.updated_by') }}</th>
                    <th>Completed By</th>
                    <th>Completed At</th>
                    <th>{{ localize('global.assigned_to') }}</th>
                    <th>Assigned At</th>
                    <th>Section</th>
                    <th>{{ localize('global.notes') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach ($items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['ref_no'] }}</td>
                        <td>{{ $item['registration_date'] }}</td>
                        <td>{{ $item['patient_name'] }}</td>
                        <td>{{ $item['lab_type'] }}</td>
                        <td>{{ $item['status'] }}</td>
                        <td>{{ $item['priority'] }}</td>
                        <td>{{ $item['doctor'] }}</td>
                        <td>{{ $item['branch'] }}</td>
                        <td>{{ $item['created_by'] }}</td>
                        <td>{{ $item['updated_by'] }}</td>
                        <td>{{ $item['completed_by'] }}</td>
                        <td>{{ $item['completed_at'] }}</td>
                        <td>{{ $item['assigned_to'] }}</td>
                        <td>{{ $item['assigned_at'] }}</td>
                        <td>{{ $item['assigned_section'] }}</td>
                        <td>{{ $item['notes'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
