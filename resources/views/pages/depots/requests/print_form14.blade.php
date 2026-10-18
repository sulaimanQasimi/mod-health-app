<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ localize('global.depot.form_14_title') }} — {{ $depotRequest->request_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Tahoma, Arial, sans-serif;
            margin: 10px;
            direction: rtl;
            color: #000;
            font-size: 11px;
        }
        .no-print { margin-bottom: 12px; }
        .title-block {
            text-align: center;
            margin-bottom: 8px;
        }
        .title-block h1 {
            font-size: 15px;
            margin: 0 0 4px;
            font-weight: bold;
        }
        .title-block h2 {
            font-size: 13px;
            margin: 0 0 6px;
            font-weight: normal;
        }
        .subtitle {
            font-size: 11px;
            margin-bottom: 8px;
            text-align: center;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 16px;
            margin-bottom: 10px;
            border: 1px solid #333;
            padding: 8px;
        }
        .meta-row {
            display: flex;
            gap: 6px;
            align-items: baseline;
        }
        .meta-label {
            font-weight: bold;
            white-space: nowrap;
        }
        .meta-value {
            flex: 1;
            border-bottom: 1px dotted #666;
            min-height: 16px;
        }
        .supported-unit {
            border: 1px solid #333;
            padding: 6px 8px;
            margin-bottom: 10px;
            min-height: 28px;
        }
        table.form-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 9px;
            table-layout: fixed;
        }
        table.form-table th,
        table.form-table td {
            border: 1px solid #333;
            padding: 3px 2px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }
        table.form-table th {
            background: #e8e8e8;
            font-weight: bold;
            line-height: 1.2;
        }
        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 14px;
        }
        .signature-box {
            border: 1px solid #333;
            min-height: 56px;
            padding: 4px 6px;
            font-size: 9px;
        }
        .signature-box .role {
            font-weight: bold;
            margin-bottom: 28px;
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

    <div class="title-block">
        <h1>{{ localize('global.depot.form_14_title_dr') }}</h1>
        <h2>MOD FORM 14 REQUEST / REQUEST TO TURNIN</h2>
        <div class="subtitle">{{ localize('global.hospital_name') }} — {{ localize('global.depot.form_14_subtitle') }}</div>
    </div>

    <div class="meta-grid">
        <div class="meta-row">
            <span class="meta-label">{{ localize('global.depot.branch') }}:</span>
            <span class="meta-value">{{ $context['branch_name'] ?? '—' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">{{ localize('global.depot.requesting_department') }}:</span>
            <span class="meta-value">{{ $context['department_name'] ?? '—' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">{{ localize('global.depot.request_user') }}:</span>
            <span class="meta-value">{{ $context['request_user_name'] ?? '—' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">{{ localize('global.depot.pharmacy_depot') }}:</span>
            <span class="meta-value">{{ $context['pharmacy_depot_label'] ?? '—' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">{{ localize('global.depot.source_depot') }}:</span>
            <span class="meta-value">{{ $depotRequest->sourceDepot?->name ?? '—' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">{{ localize('global.number') }}:</span>
            <span class="meta-value">{{ $depotRequest->request_number }}</span>
        </div>
    </div>

    <div class="supported-unit">
        <strong>MODAAC, SUPPORTED UNIT / واحد پشتیبانی:</strong>
        {{ $supportedUnit }}
    </div>

    <table class="form-table">
        <thead>
            <tr>
                <th rowspan="2" style="width:3%">{{ localize('global.number') }}</th>
                <th rowspan="2" style="width:14%">{{ localize('global.depot.form_14_item_name') }}</th>
                <th rowspan="2" style="width:6%">{{ localize('global.unit') }}</th>
                <th rowspan="2" style="width:6%">{{ localize('global.depot.form_14_qty_available') }}</th>
                <th rowspan="2" style="width:6%">{{ localize('global.depot.form_14_qty_requested') }}</th>
                <th rowspan="2" style="width:7%">{{ localize('global.depot.form_14_delivery_date') }}</th>
                <th rowspan="2" style="width:8%">{{ localize('global.depot.form_14_part_number') }}</th>
                <th rowspan="2" style="width:6%">{{ localize('global.depot.form_14_solar_date') }}</th>
                <th rowspan="2" style="width:7%">{{ localize('global.depot.form_14_document_number') }}</th>
                <th colspan="2">CSSK</th>
                <th colspan="2">FSD</th>
                <th colspan="2">NSD</th>
            </tr>
            <tr>
                <th>{{ localize('global.depot.form_14_unfilled') }}</th>
                <th>{{ localize('global.depot.form_14_filled') }}</th>
                <th>{{ localize('global.depot.form_14_unfilled') }}</th>
                <th>{{ localize('global.depot.form_14_filled') }}</th>
                <th>{{ localize('global.depot.form_14_unfilled') }}</th>
                <th>{{ localize('global.depot.form_14_filled') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lines as $index => $line)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align:right">{{ $line['item_name'] }}</td>
                    <td>{{ $line['unit_name'] }}</td>
                    <td>{{ $line['available_quantity'] ?? '—' }}</td>
                    <td>{{ $line['requested_quantity'] }}</td>
                    <td>{{ $line['delivery_date'] ?? '—' }}</td>
                    <td>{{ $line['part_number'] }}</td>
                    <td>{{ $line['solar_date'] }}</td>
                    <td>{{ $line['document_number'] }}</td>
                    <td>{{ $line['cssk_unfilled'] !== '' ? $line['cssk_unfilled'] : '' }}</td>
                    <td>{{ $line['cssk_filled'] !== '' ? $line['cssk_filled'] : '' }}</td>
                    <td>{{ $line['fsd_unfilled'] !== '' ? $line['fsd_unfilled'] : '' }}</td>
                    <td>{{ $line['fsd_filled'] !== '' ? $line['fsd_filled'] : '' }}</td>
                    <td>{{ $line['nsd_unfilled'] !== '' ? $line['nsd_unfilled'] : '' }}</td>
                    <td>{{ $line['nsd_filled'] !== '' ? $line['nsd_filled'] : '' }}</td>
                </tr>
            @endforeach
            @for ($i = 0; $i < $emptyRows; $i++)
                <tr>
                    <td>{{ $lines->count() + $i + 1 }}</td>
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>

    @if ($depotRequest->notes)
        <p style="margin-top:8px;font-size:10px">
            <strong>{{ localize('global.notes') }}:</strong> {{ $depotRequest->notes }}
        </p>
    @endif

    <div class="signatures">
        <div class="signature-box">
            <div class="role">{{ localize('global.depot.form_14_supplier') }}</div>
        </div>
        <div class="signature-box">
            <div class="role">{{ localize('global.depot.form_14_medical_machinery') }}</div>
        </div>
        <div class="signature-box">
            <div class="role">{{ localize('global.depot.form_14_medical_logistics') }}</div>
        </div>
    </div>

    <script>
        window.onload = function () { window.print(); };
    </script>
</body>
</html>
