@php
    $patient = $order->appointment?->patient;
    $patientName = trim(($patient?->name ?? '') . ' ' . ($patient?->last_name ?? ''));
    $prescription = $order->prescription ?? [];
    $rx = function (string $side, string $key) use ($prescription) {
        $value = data_get($prescription, "{$side}.{$key}");
        return ($value === null || $value === '') ? '—' : $value;
    };
    $cell = function ($value) {
        return ($value === null || $value === '') ? '—' : $value;
    };
    $genderLabel = match ((string) ($patient?->gender ?? '')) {
        '0' => localize('global.male'),
        '1' => localize('global.female'),
        default => $patient?->gender ?: '—',
    };
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ localize('global.eye_glasses_order') }} - {{ $order->ref_no }}</title>
    <style>
        @font-face {
            font-family: 'ModFont';
            src: url('/assets/fonts/mod_font.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: 'ModFont', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.45;
            color: #111;
            background: #fff;
            direction: rtl;
        }
        .report-container { max-width: 210mm; margin: 0 auto; padding: 16px 18px; }
        .header { border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 14px; }
        .header-grid { display: grid; grid-template-columns: 100px 1fr 100px; gap: 12px; align-items: center; }
        .logo-image { max-width: 90px; max-height: 90px; object-fit: contain; }
        .text-column { text-align: center; }
        .text-column h2, .text-column div { margin: 0 0 3px; font-size: 13px; font-weight: 700; }
        .report-title { font-size: 16px !important; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: right; }
        th { background: #f3f3f3; width: 18%; }
        .section-title { font-weight: 700; margin: 12px 0 6px; }
        .footer { margin-top: 16px; font-size: 11px; text-align: center; }
        @media print { body { print-color-adjust: exact; -webkit-print-color-adjust: exact; } }
    </style>
</head>
<body onload="window.print()">
    <div class="report-container">
        <div class="header">
            <div class="header-grid">
                <img src="{{ $leftLogo }}" alt="" class="logo-image">
                <div class="text-column">
                    <h2>{{ localize('global.system_name') }}</h2>
                    <div>{{ localize('global.ophthalmology_department') }}</div>
                    <div class="report-title">{{ localize('global.eye_glasses_order') }}</div>
                </div>
                <img src="{{ $rightLogo }}" alt="" class="logo-image">
            </div>
        </div>

        <table>
            <tr>
                <th>{{ localize('global.ref_no') }}</th>
                <td>{{ $order->ref_no }}</td>
                <th>{{ localize('global.status') }}</th>
                <td>{{ localize('global.eye_glasses_status_' . $order->status) }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.patient_name') }}</th>
                <td>{{ $cell($patientName) }}</td>
                <th>{{ localize('global.father_name') }}</th>
                <td>{{ $cell($patient?->father_name) }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.id_card') }}</th>
                <td>{{ $cell($patient?->id_card) }}</td>
                <th>{{ localize('global.age') }}</th>
                <td>{{ $cell($patient?->age) }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.gender') }}</th>
                <td>{{ $genderLabel }}</td>
                <th>{{ localize('global.phone') }}</th>
                <td>{{ $cell($patient?->phone) }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.examiner') }}</th>
                <td>{{ $cell($order->examiner?->name) }}</td>
                <th>{{ localize('global.eye_glasses_request_date') }}</th>
                <td>{{ $order->request_date ? verta($order->request_date)->format('Y/m/d') : '—' }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.branch') }}</th>
                <td>{{ $cell($order->branch?->name) }}</td>
                <th>{{ localize('global.eye_glasses_quantity') }}</th>
                <td>{{ $cell($order->quantity) }}</td>
            </tr>
        </table>

        <div class="section-title">{{ localize('global.oph_glasses_rx') }}</div>
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>SPH</th>
                    <th>CYL</th>
                    <th>Axis</th>
                    <th>ADD</th>
                    <th>Prism H</th>
                    <th>Prism V</th>
                </tr>
            </thead>
            <tbody>
                @foreach (['od', 'os'] as $eye)
                    <tr>
                        <th>{{ strtoupper($eye) }}</th>
                        <td>{{ $rx($eye, 'sphere') }}</td>
                        <td>{{ $rx($eye, 'cylinder') }}</td>
                        <td>{{ $rx($eye, 'axis') }}</td>
                        <td>{{ $rx($eye, 'add') }}</td>
                        <td>{{ $rx($eye, 'prism_horizontal') }}</td>
                        <td>{{ $rx($eye, 'prism_vertical') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <th>IPD</th>
                    <td colspan="6">{{ $cell(data_get($prescription, 'ipd')) }}</td>
                </tr>
            </tbody>
        </table>

        <table>
            <tr>
                <th>{{ localize('global.eye_glasses_frame_type') }}</th>
                <td>{{ $order->frame_type ? localize('global.eye_glasses_frame_' . $order->frame_type) : '—' }}</td>
                <th>{{ localize('global.eye_glasses_lens_type') }}</th>
                <td>{{ $order->lens_type ? localize('global.eye_glasses_lens_' . $order->lens_type) : '—' }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.eye_glasses_lens_material') }}</th>
                <td>{{ $order->lens_material ? localize('global.eye_glasses_material_' . $order->lens_material) : '—' }}</td>
                <th>{{ localize('global.eye_glasses_tint') }}</th>
                <td>{{ $cell($order->tint) }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.amount') }}</th>
                <td>{{ $cell($order->amount) }}</td>
                <th>{{ localize('global.eye_glasses_payment_method') }}</th>
                <td>{{ $order->payment_method ? localize('global.eye_glasses_pay_' . $order->payment_method) : '—' }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.eye_glasses_paid_amount') }}</th>
                <td>{{ $cell($order->paid_amount) }}</td>
                <th>{{ localize('global.eye_glasses_delivery') }}</th>
                <td>{{ $order->delivered_at ? verta($order->delivered_at)->format('Y/m/d H:i') : '—' }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.eye_glasses_received_by') }}</th>
                <td>{{ $cell($order->received_by) }}</td>
                <th>{{ localize('global.notes') }}</th>
                <td>{{ $cell($order->notes) }}</td>
            </tr>
        </table>

        <div class="footer">{{ $generatedAt }}</div>
    </div>
</body>
</html>
