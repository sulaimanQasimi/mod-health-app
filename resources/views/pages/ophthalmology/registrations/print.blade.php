@php
    $patient = $registration->appointment?->patient;
    $patientName = trim(($patient?->name ?? '') . ' ' . ($patient?->last_name ?? ''));
    $visual = $registration->visual_examination ?? [];
    $refraction = $registration->refraction ?? [];
    $val = function ($data, ...$keys) {
        $cursor = $data;
        foreach ($keys as $key) {
            $cursor = is_array($cursor) ? ($cursor[$key] ?? null) : null;
        }
        return ($cursor === null || $cursor === '') ? '—' : $cursor;
    };
    $genderLabel = match ((string) ($patient?->gender ?? '')) {
        '0' => localize('global.male'),
        '1' => localize('global.female'),
        default => $patient?->gender ?: '—',
    };
    $visualFields = [
        ['visual_acuity', 'حدت بینایی (VA)'],
        ['pinhole_vision', 'دید با سوراخ سوزنی (PH)'],
        ['vision_with_glasses', 'دید با عینک'],
        ['near_vision', 'دید نزدیک'],
        ['intraocular_pressure', 'فشار داخل چشم (IOP)'],
    ];
    $refractionFields = [
        ['sphere', 'SPH'],
        ['cylinder', 'CYL'],
        ['axis', 'Axis'],
        ['distance_vision', 'دید دور'],
        ['near_vision', 'دید نزدیک'],
        ['recommended_prescription', 'نسخه پیشنهادی'],
    ];
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ localize('global.print_eye_examination') }} - {{ $registration->ref_no }}</title>
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
        .report-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 16px 18px;
            background: #fff;
        }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .header-grid {
            display: grid;
            grid-template-columns: 100px 1fr 100px;
            gap: 12px;
            align-items: center;
        }
        .logo-image { max-width: 90px; max-height: 90px; object-fit: contain; }
        .text-column { text-align: center; }
        .text-column h2, .text-column div {
            margin: 0 0 3px;
            font-size: 13px;
            font-weight: 700;
        }
        .report-title {
            margin: 10px 0 0;
            text-align: center;
            font-size: 16px;
            font-weight: 800;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px 7px;
            vertical-align: middle;
        }
        th { background: #f3f4f6; font-weight: 700; white-space: nowrap; }
        .section-title {
            margin: 14px 0 8px;
            font-size: 13px;
            font-weight: 800;
            border-bottom: 1px solid #111;
            padding-bottom: 4px;
        }
        .eyes-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }
        .eye-card {
            border: 1px solid #111;
            border-radius: 8px;
            padding: 10px;
        }
        .eye-card.od { background: #ecfeff; }
        .eye-card.os { background: #f5f3ff; }
        .eye-card h3 { margin: 0 0 8px; font-size: 13px; text-align: center; }
        .eye-svg-wrap { display: flex; justify-content: center; margin-bottom: 8px; }
        .eye-svg-wrap svg { width: 170px; height: auto; }
        .metrics {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
        }
        .metric {
            border: 1px solid #94a3b8;
            border-radius: 6px;
            background: #fff;
            padding: 4px;
            text-align: center;
        }
        .metric .label { font-size: 10px; color: #475569; }
        .metric .value { font-size: 12px; font-weight: 700; }
        .notes-box {
            border: 1px solid #000;
            min-height: 40px;
            padding: 8px;
            white-space: pre-wrap;
            margin-bottom: 12px;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 28px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 6px;
            font-weight: 700;
        }
        .footer {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 11px;
        }
        .no-print { display: block; margin-top: 10px; }
        .print-button {
            background: #0f172a;
            color: #fff;
            border: none;
            padding: 10px 18px;
            font-size: 14px;
            cursor: pointer;
        }
        @media print {
            @page { size: A4; margin: 8mm; }
            body { margin: 0; padding: 0; }
            .report-container { max-width: none; padding: 0; }
            .no-print { display: none !important; }
            .eye-card, table, .signatures { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="report-container">
    <div class="header">
        <div class="header-grid">
            <div><img src="{{ $leftLogo }}" alt="" class="logo-image"></div>
            <div class="text-column">
                <h2>امارت اسلامی افغانستان</h2>
                <div>وزارت دفاع ملی</div>
                <div>ستـــــــــــــردرستیــــــــــــز</div>
                <div>قوماندانیت صحیه</div>
                <div>قوماندانی اکادمی علوم طبی</div>
                <div>{{ localize('global.ophthalmology_department') }}</div>
            </div>
            <div style="text-align:left"><img src="{{ $rightLogo }}" alt="" class="logo-image"></div>
        </div>
        <div class="report-title">{{ localize('global.eye_examination_report') }}</div>
    </div>

    <table>
        <tr>
            <th>{{ localize('global.ref_no') }}</th>
            <td>{{ $registration->ref_no }}</td>
            <th>{{ localize('global.registration_date') }}</th>
            <td>{{ $registration->registration_date ? verta($registration->registration_date)->format('Y/m/d') : '—' }}</td>
        </tr>
        <tr>
            <th>{{ localize('global.patient_name') }}</th>
            <td>{{ $patientName ?: '—' }}</td>
            <th>{{ localize('global.father_name') }}</th>
            <td>{{ $patient?->father_name ?: '—' }}</td>
        </tr>
        <tr>
            <th>{{ localize('global.id_card') }}</th>
            <td>{{ $patient?->id_card ?? '—' }}</td>
            <th>{{ localize('global.age') }}</th>
            <td>{{ $patient?->age ?? '—' }}</td>
        </tr>
        <tr>
            <th>{{ localize('global.gender') }}</th>
            <td>{{ $genderLabel }}</td>
            <th>{{ localize('global.phone') }}</th>
            <td>{{ $patient?->phone ?: '—' }}</td>
        </tr>
        <tr>
            <th>{{ localize('global.examiner') }}</th>
            <td>{{ $registration->examiner?->name ?: '—' }}</td>
            <th>{{ localize('global.status') }}</th>
            <td>{{ localize('global.status_' . $registration->status) }}</td>
        </tr>
    </table>

    @if ($registration->chief_complaint)
        <div class="section-title">{{ localize('global.chief_complaint') }}</div>
        <div class="notes-box">{{ $registration->chief_complaint }}</div>
    @endif

    <div class="section-title">{{ localize('global.visual_examination') }}</div>
    <div class="eyes-grid">
        @foreach ([
            ['od', 'OD · چشم راست', '#0891b2'],
            ['os', 'OS · چشم چپ', '#7c3aed'],
        ] as [$side, $title, $accent])
            <div class="eye-card {{ $side }}">
                <h3>{{ $title }}</h3>
                <div class="eye-svg-wrap">
                    <svg viewBox="0 0 220 160" aria-hidden="true">
                        <ellipse cx="110" cy="82" rx="92" ry="52" fill="{{ $accent }}22" />
                        <path d="M28 78 C60 28, 160 28, 192 78" fill="none" stroke="#475569" stroke-width="4" stroke-linecap="round" />
                        <path d="M28 78 C60 128, 160 128, 192 78" fill="none" stroke="#475569" stroke-width="4" stroke-linecap="round" />
                        <ellipse cx="110" cy="78" rx="78" ry="38" fill="#f8fafc" stroke="#64748b" stroke-width="2" />
                        <circle cx="110" cy="78" r="28" fill="{{ $accent }}" />
                        <circle cx="110" cy="78" r="12" fill="#0f172a" />
                        <circle cx="{{ $side === 'od' ? 102 : 118 }}" cy="70" r="4.5" fill="#fff" />
                    </svg>
                </div>
                <div class="metrics">
                    @foreach ($visualFields as [$key, $label])
                        <div class="metric">
                            <div class="label">{{ $label }}</div>
                            <div class="value">{{ $val($visual, $side, $key) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>اندازه‌گیری</th>
                <th>OD</th>
                <th>OS</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($visualFields as [$key, $label])
                <tr>
                    <th>{{ $label }}</th>
                    <td>{{ $val($visual, 'od', $key) }}</td>
                    <td>{{ $val($visual, 'os', $key) }}</td>
                </tr>
            @endforeach
            <tr>
                <th>انحراف چشم</th>
                <td colspan="2">{{ $val($visual, 'squint_assessment') }}</td>
            </tr>
            <tr>
                <th>فشار خون</th>
                <td colspan="2">{{ $val($visual, 'blood_pressure') }}</td>
            </tr>
            <tr>
                <th>دید رنگی</th>
                <td colspan="2">{{ $val($visual, 'color_vision') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">{{ localize('global.refraction') }}</div>
    <table>
        <thead>
            <tr>
                <th>اندازه‌گیری</th>
                <th>OD</th>
                <th>OS</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($refractionFields as [$key, $label])
                <tr>
                    <th>{{ $label }}</th>
                    <td>{{ $val($refraction, 'od', $key) }}</td>
                    <td>{{ $val($refraction, 'os', $key) }}</td>
                </tr>
            @endforeach
            <tr>
                <th>IPD</th>
                <td colspan="2">{{ $val($refraction, 'ipd') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">{{ localize('global.assessment_and_plan') }}</div>
    <table>
        <tr>
            <th style="width:22%">{{ localize('global.diagnosis') }}</th>
            <td>{{ $registration->diagnosis ?: '—' }}</td>
        </tr>
        <tr>
            <th>{{ localize('global.treatment_plan') }}</th>
            <td>{{ $registration->treatment_plan ?: '—' }}</td>
        </tr>
        <tr>
            <th>{{ localize('global.follow_up_date') }}</th>
            <td>{{ $registration->follow_up_date ? verta($registration->follow_up_date)->format('Y/m/d') : '—' }}</td>
        </tr>
        <tr>
            <th>{{ localize('global.notes') }}</th>
            <td>{{ $registration->notes ?: '—' }}</td>
        </tr>
    </table>

    <div class="signatures">
        <div><div class="signature-line">{{ localize('global.examiner') }}</div></div>
        <div><div class="signature-line">{{ localize('global.doctor') }}</div></div>
    </div>

    <div class="footer">
        <p>{{ localize('global.report_generated_on') }}: {{ $generatedAt }}</p>
        <div class="no-print">
            <button type="button" class="print-button" onclick="window.print()">{{ localize('global.print') }}</button>
        </div>
    </div>
</div>
<script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>
