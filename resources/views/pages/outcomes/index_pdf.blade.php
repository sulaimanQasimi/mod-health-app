<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style type="text/css">
        body, body * {
            font-family: DejaVu Sans, Arial, sans-serif !important;
        }
        #outcomes_table {
            border-collapse: collapse;
            width: 100%;
        }
        #outcomes_table td,
        #outcomes_table th {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
        }
        #outcomes_table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        #outcomes_table th {
            background-color: #e0e0e0;
            color: #333;
        }
    </style>
</head>
<body>
    <h3 style="text-align: center; margin-bottom: 10px;">
        {{ localize('global.medicine_usage_statistics') }}
    </h3>

    <table id="outcomes_table">
        <thead>
            <tr>
                <th>{{ localize('global.number') }}</th>
                <th>{{ localize('global.medicine') }}</th>
                <th>{{ localize('global.pharmacy') }}</th>
                <th>{{ localize('global.usage_count') }}</th>
                <th>{{ localize('global.updated_by') }}</th>
                <th>{{ localize('global.prescription_completed_date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->name ?? '-' }}</td>
                    <td>{{ $item->pharmacy_name ?? '-' }}</td>
                    <td>{{ (int) ($item->usage_count ?? 0) }}</td>
                    <td>{{ $item->updated_by_name ? trim($item->updated_by_name) : '-' }}</td>
                    <td>
                        @if(!empty($item->prescription_updated_at))
                            {{ \Hekmatinasser\Verta\Facades\Verta::instance($item->prescription_updated_at)->format('Y/m/d H:i') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">{{ localize('global.no_medicine_usage_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
