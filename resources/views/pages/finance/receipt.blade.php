<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ localize('global.payment_receipt') }}</title>
    <style>
        @page { size: A5; margin: 12mm; }
        body {
            font-family: Tahoma, Arial, sans-serif;
            color: #111;
            margin: 0;
            padding: 16px;
        }
        .card {
            border: 2px solid #111;
            padding: 16px 18px;
            max-width: 480px;
            margin: 0 auto;
        }
        h1 {
            font-size: 18px;
            margin: 0 0 12px;
            text-align: center;
            border-bottom: 1px solid #111;
            padding-bottom: 8px;
        }
        .uuid {
            font-family: ui-monospace, monospace;
            font-size: 12px;
            word-break: break-all;
            text-align: center;
            margin-bottom: 14px;
            letter-spacing: 0.02em;
        }
        .row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin: 6px 0;
            font-size: 13px;
        }
        .label { color: #444; }
        .value { font-weight: 700; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px;
            text-align: center;
        }
        th { background: #f2f2f2; }
        .total {
            margin-top: 12px;
            text-align: center;
            font-size: 16px;
            font-weight: 700;
        }
        .no-print { margin: 12px auto; text-align: center; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">{{ localize('global.print') }}</button>
    </div>
    <div class="card">
        <h1>{{ localize('global.payment_receipt') }}</h1>
        <div class="uuid">{{ $receipt->uuid }}</div>

        <div class="row">
            <span class="label">{{ localize('global.patient_name') }}</span>
            <span class="value">{{ $snapshot['patient_name'] ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="label">{{ localize('global.branch') }}</span>
            <span class="value">{{ $snapshot['branch_name'] ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="label">{{ localize('global.department') }}</span>
            <span class="value">{{ $snapshot['department_name'] ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="label">{{ localize('global.payment_method') }}</span>
            <span class="value">{{ $snapshot['method'] ?? 'cash' }}</span>
        </div>
        <div class="row">
            <span class="label">{{ localize('global.paid_at') }}</span>
            <span class="value">{{ $snapshot['paid_at'] ?? '—' }}</span>
        </div>

        @if (!empty($snapshot['items']))
            <table>
                <thead>
                    <tr>
                        <th>{{ localize('global.description') }}</th>
                        <th>{{ localize('global.price') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($snapshot['items'] as $item)
                        <tr>
                            <td>{{ $item['description'] ?? '—' }}</td>
                            <td>{{ $item['amount'] ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="total">
            {{ localize('global.paid_amount') }}: {{ $snapshot['amount'] ?? $receipt->payment?->amount ?? 0 }}
        </div>
    </div>
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 250);
        });
    </script>
</body>
</html>
