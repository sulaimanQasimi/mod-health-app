<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ localize('global.reception_report_title') }}</title>
    <style>
        body {
            font-family: Tahoma, Arial, sans-serif;
            margin: 12px;
            direction: rtl;
            color: #000;
        }
        h2 {
            text-align: center;
            font-size: 18px;
            margin: 0 0 12px;
        }
        .no-print {
            margin-bottom: 12px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 11px;
        }
        th, td {
            border: 1px solid #333;
            padding: 4px 5px;
            text-align: center;
        }
        th {
            background: #e8e8e8;
        }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" onclick="window.print()">{{ localize('global.print') }}</button>
    </div>
    <h2>{{ localize('global.reception_report_title') }}</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ localize('global.patient_name') }}</th>
                <th>{{ localize('global.nid') }}</th>
                <th>{{ localize('global.id_card') }}</th>
                <th>{{ localize('global.referral_name') }}</th>
                <th>{{ localize('global.age') }}</th>
                <th>{{ localize('global.gender') }}</th>
                <th>{{ localize('global.job_category') }}</th>
                <th>{{ localize('global.disease_type') }}</th>
                <th>{{ localize('global.referred_by') }}</th>
                <th>{{ localize('global.province') }}</th>
                <th>{{ localize('global.district') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->patient_name }}</td>
                    <td>{{ $item->nid }}</td>
                    <td>{{ $item->id_card }}</td>
                    <td>{{ $item->referral_name }}</td>
                    <td>{{ $item->age }}</td>
                    <td>{{ $item->gender_label }}</td>
                    <td>{{ $item->job_category_label }}</td>
                    <td>{{ $item->type_label }}</td>
                    <td>{{ $item->referred_by }}</td>
                    <td>{{ $item->province_name }}</td>
                    <td>{{ $item->district_name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12">{{ localize('global.no_records_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <script>
        window.onload = function () { window.print(); };
    </script>
</body>
</html>
