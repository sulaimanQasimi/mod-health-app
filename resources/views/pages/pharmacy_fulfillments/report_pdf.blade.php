<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style type="text/css">
        body, body * {
            font-family: DejaVu Sans, Arial, sans-serif !important;
        }
        #fulfillments_table {
            border-collapse: collapse;
            width: 100%;
            direction: rtl;
        }
        #fulfillments_table td,
        #fulfillments_table th {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
        }
        #fulfillments_table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        #fulfillments_table th {
            background-color: #e0e0e0;
            color: #333;
        }
    </style>
</head>
<body>
    <h3 style="text-align: center; margin-bottom: 10px;">
        {{ localize('global.pharmacy_fulfillments') }}
    </h3>

    <table id="fulfillments_table">
        <thead>
            <tr>
                <th>{{ localize('global.number') }}</th>
                <th>{{ localize('global.medicine') }}</th>
                <th>{{ localize('global.unit_type') }}</th>
                <th>{{ localize('global.amount') }}</th>
                <th>{{ localize('global.form_no') }}</th>
                <th>{{ localize('global.date') }}</th>
                <th>{{ localize('global.pharmacy') }}</th>
                <th>{{ localize('global.user') }}</th>
                <th>{{ localize('global.created_by') }}</th>
                <th>{{ localize('global.created_at') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->medicine->name ?? '-' }}</td>
                    <td>{{ $item->unit_type ?? '-' }}</td>
                    <td>{{ $item->amount }}</td>
                    <td>{{ $item->form_no }}</td>
                    <td>
                        @if($item->date)
                            {{ \Hekmatinasser\Verta\Facades\Verta::instance($item->date)->format('Y/m/d') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $item->pharmacy->name ?? '-' }}</td>
                    <td>{{ $item->user->name ?? '-' }}</td>
                    <td>{{ $item->createdBy->name ?? '-' }}</td>
                    <td>
                        @if($item->created_at)
                            {{ \Hekmatinasser\Verta\Facades\Verta::instance($item->created_at)->format('Y/m/d H:i') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">{{ localize('global.no_pharmacy_fulfillments_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

