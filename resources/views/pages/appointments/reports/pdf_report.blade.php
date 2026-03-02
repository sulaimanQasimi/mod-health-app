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
    </style>
</head>

<body>
    <h2 class="excel_table_title">{{ localize('global.appointment_report_title') }}</h2>
    <div class="col-md-12 mt-2">
        <table class="table" id="print_excel_table">
            <thead>
                <tr>
                    <th>{{ localize('global.number') }}</th>
                    <th>{{ localize('global.patient_name') }}</th>
                    <th>{{ localize('global.doctor_name') }}</th>
                    <th>{{ localize('global.branch') }}</th>
                    <th>{{ localize('global.clinic_type') }}</th>
                    <th>{{ localize('global.processed_by') }}</th>
                    <th>{{ localize('global.registered_by') }}</th>
                    <th>{{ localize('global.job') }}</th>
                    <th>{{ localize('global.job_type') }}</th>
                    <th>{{ localize('global.gender') }}</th>
                    <th>{{ localize('global.rank') }}</th>
                    <th>{{ localize('global.relation') }}</th>
                    <th>{{ localize('global.province') }}</th>
                    <th>{{ localize('global.district') }}</th>
                    <th>{{ localize('global.status') }}</th>
                    <th>{{ localize('global.date') }}</th>
                    <th>{{ localize('global.time') }}</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->patient_name ?? '—' }}</td>
                        <td>{{ $item->doctor_name ?? '—' }}</td>
                        <td>{{ $item->branch_name ?? '—' }}</td>
                        <td>{{ $item->clinic_type === 'hospital' ? localize('global.hospital') : ($item->clinic_type === 'clinic' ? localize('global.clinic') : '—') }}</td>
                        <td>{{ $item->processed_by_name ?? '—' }}</td>
                        <td>{{ $item->registered_by_name ?? '—' }}</td>
                        <td>{{ $item->job ?? '—' }}</td>
                        <td>{{ $item->job_type ? localize('global.' . $item->job_type) : '—' }}</td>
                        <td>{{ isset($item->gender) ? ($item->gender == '1' ? localize('global.female') : localize('global.male')) : '—' }}</td>
                        <td>{{ $item->rank ?? '—' }}</td>
                        <td>{{ $item->relation_name ?? '—' }}</td>
                        <td>{{ $item->province_name ?? '—' }}</td>
                        <td>{{ $item->district_name ?? '—' }}</td>
                        <td>
                        @if ($item->is_completed == '0')
                            {{ localize('global.ongoing_appointments') }}
                        @else
                            {{ localize('global.completed_appointments') }}
                        @endif
                        </td>
                        <td>{{ $item->date ?? '—' }}</td>
                        <td>{{ $item->time ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>