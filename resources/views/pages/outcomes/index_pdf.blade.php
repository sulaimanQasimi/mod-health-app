<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style type="text/css">
        body, body * {
            font-family: persian_font, DejaVu Sans, sans-serif !important;
            font-weight: normal !important;
        }
        .header {
            width: 100%;
            margin-bottom: 16px;
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            border: none;
            vertical-align: middle;
            padding: 4px;
        }
        .logo-image {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }
        .header-title {
            text-align: center;
        }
        .header-title h3 {
            margin: 0 0 4px 0;
            font-size: 16px;
        }
        .header-title p {
            margin: 0;
            font-size: 11px;
            color: #555;
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
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 15%; text-align: right;">
                    <img src="{{ public_path('images/logos/لوگو قومنداني.JPG') }}" alt="Logo" class="logo-image">
                </td>
                <td style="width: 70%;" class="header-title">
                    <h3>{{ localize('global.medicine_usage_statistics') }}</h3>
                    <p>{{ localize('global.generated_on') ?? 'Generated' }}: {{ now()->format('Y/m/d H:i') }}</p>
                </td>
                <td style="width: 15%; text-align: left;">
                    <img src="{{ public_path('images/logos/لوگوی جدید وزارت دفاع ملی.png') }}" alt="Logo" class="logo-image">
                </td>
            </tr>
        </table>
    </div>

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
                    <td>{{ !empty($item->updated_by_name ?? null) ? trim($item->updated_by_name) : '-' }}</td>
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
