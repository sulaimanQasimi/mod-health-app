<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $patient = $nursingAssessment->morphable->patient ?? null;
        $patientFullName = trim((string) ($nursingAssessment->patient_name ?: ($patient->name ?? '')));

        $morphable = $nursingAssessment->morphable;
        $departmentName = auth()->user()->department?->name
            ?? $morphable?->appointment?->department?->name
            ?? $morphable?->room?->name
            ?? null;

        $formatDate = function ($value) {
            if (!$value) {
                return '—';
            }
            try {
                if ($value instanceof \Carbon\Carbon || $value instanceof \DateTimeInterface) {
                    return \Verta($value)->format('Y/m/d');
                }
                return \Verta::parse((string) $value)->format('Y/m/d');
            } catch (\Throwable $e) {
                return (string) $value;
            }
        };

        $formatTime = function ($value) {
            if (!$value) {
                return '—';
            }
            if ($value instanceof \Carbon\Carbon || $value instanceof \DateTimeInterface) {
                return $value->format('H:i');
            }
            return substr((string) $value, 0, 5);
        };

        $mark = function ($checked) {
            return $checked ? '☑' : '☐';
        };

        $text = function ($value) {
            if ($value === null || $value === '') {
                return '—';
            }
            return $value;
        };
    @endphp
    <title>{{ localize('global.nursing_assessment') }} - {{ $patientFullName }}</title>
    <style>
        @font-face {
            font-family: 'ModFont';
            src: url('{{ asset("assets/fonts/mod_font.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: A4;
            margin: 8mm;
        }

        body {
            font-family: 'ModFont', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.45;
            color: #000;
            background: white;
            direction: rtl;
            text-align: right;
            padding: 12px;
        }

        .header {
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
        }

        .header-grid {
            display: grid;
            grid-template-columns: 110px 1fr 110px;
            gap: 12px;
            align-items: center;
            min-height: 110px;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100px;
            height: 100px;
        }

        .logo-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .text-column {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            gap: 2px;
        }

        .text-column h2 { font-size: 16px; font-weight: bold; margin: 0; }
        .text-column h4 { font-size: 13px; font-weight: bold; margin: 0; }

        .print-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 8px 0 10px;
            font-size: 12px;
            font-weight: bold;
        }

        .form-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 14px;
            padding: 6px 0;
            border: 2px solid #000;
            background: #f3f3f3;
        }

        table.info-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-bottom: 10px;
        }

        table.info-table th,
        table.info-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: middle;
            font-size: 11px;
        }

        table.info-table th {
            background: #f0f0f0;
            font-weight: bold;
            white-space: nowrap;
            text-align: center;
            width: 14%;
        }

        table.info-table td {
            text-align: right;
        }

        table.info-table td.center {
            text-align: center;
        }

        .section-head {
            background: #e8e8e8;
            font-weight: bold;
            text-align: center;
            font-size: 12px;
        }

        .checks {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 16px;
            align-items: center;
        }

        .check-item { white-space: nowrap; }

        .print-button {
            position: fixed;
            top: 16px;
            left: 16px;
            background: #000;
            color: #fff;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            font-size: 13px;
            z-index: 1000;
        }

        @media print {
            body { padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <button type="button" class="print-button no-print" onclick="window.print()">
        {{ localize('global.print') }}
    </button>

    <div class="print-container">
        <div class="header">
            <div class="header-grid">
                <div class="logo-container">
                    <img src="{{ asset('images/logos/لوگو قومنداني.JPG') }}" alt="Hospital Logo" class="logo-image">
                </div>
                <div class="text-column">
                    <h2>امارت اسلامی افغانستان</h2>
                    <h4>وزارت دفاع ملی</h4>
                    <h4>ستـــــــــــــردرستیــــــــــــز</h4>
                    <h4>قوماندانیت صحیه</h4>
                    <h4>قوماندانی اکادمی علوم طبی</h4>
                    @if($departmentName)
                        <h4>{{ $departmentName }}</h4>
                    @endif
                </div>
                <div class="logo-container">
                    <img src="{{ asset('images/logos/لوگوی جدید وزارت دفاع ملی.png') }}" alt="Ministry Logo" class="logo-image">
                </div>
            </div>
        </div>

        <div class="print-meta">
            <span>{{ localize('global.date') }}: {{ \Morilog\Jalali\Jalalian::now()->format('Y/m/d') }}</span>
            <span>{{ localize('global.time') }}: {{ now()->format('h:i A') }}</span>
        </div>

        <div class="form-title">{{ localize('global.nursing_assessment') }}</div>

        {{-- Same fields / order as the fill form --}}
        <table class="info-table">
            <tr>
                <th>{{ localize('global.patient_name') }}</th>
                <td>{{ $text($patientFullName) }}</td>
                <th>{{ localize('global.nurse') }}</th>
                <td colspan="3">{{ $text($nursingAssessment->nurse->full_name ?? null) }}</td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <th colspan="6" class="section-head">{{ localize('global.admission_details') }}</th>
            </tr>
            <tr>
                <th>{{ localize('global.time') }}</th>
                <td class="center">{{ $formatTime($nursingAssessment->admitted_from_time) }}</td>
                <th>{{ localize('global.date') }}</th>
                <td class="center">{{ $formatDate($nursingAssessment->admitted_from_date) }}</td>
                <th>{{ localize('global.admitted_from') }}</th>
                <td>
                    <div class="checks">
                        <span class="check-item">{{ $mark($nursingAssessment->admitted_from_emergency) }} {{ localize('global.emergency') }}</span>
                        <span class="check-item">{{ $mark($nursingAssessment->admitted_from_hospital) }} {{ localize('global.hospital') }}</span>
                        <span class="check-item">{{ $mark($nursingAssessment->admitted_from_family_member) }} {{ localize('global.family_member') }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>{{ localize('global.chief_complaint') }}</th>
                <td colspan="3">{{ $text($nursingAssessment->chief_complaint) }}</td>
                <th>{{ localize('global.information_provided_by') }}</th>
                <td>
                    <div class="checks">
                        <span class="check-item">{{ $mark($nursingAssessment->information_provided_by_patient) }} {{ localize('global.patient') }}</span>
                        <span class="check-item">{{ $mark($nursingAssessment->information_provided_by_family_member) }} {{ localize('global.family_member') }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <th colspan="8" class="section-head">{{ localize('global.vital_signs') }}</th>
            </tr>
            <tr>
                <th>{{ localize('global.blood_pressure') }}</th>
                <td class="center">{{ $text($nursingAssessment->blood_pressure) }}</td>
                <th>{{ localize('global.pulse_rate') }}</th>
                <td class="center">{{ $text($nursingAssessment->pulse_rate) }}</td>
                <th>{{ localize('global.respiratory_rate') }}</th>
                <td class="center">{{ $text($nursingAssessment->respiratory_rate) }}</td>
                <th>{{ localize('global.temperature') }}</th>
                <td class="center">{{ $text($nursingAssessment->temperature) }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.oxygen_saturation') }}</th>
                <td class="center">{{ $text($nursingAssessment->oxygen_saturation) }}</td>
                <th>{{ localize('global.weight') }}</th>
                <td class="center">{{ $text($nursingAssessment->weight) }}</td>
                <th>{{ localize('global.height') }}</th>
                <td class="center">{{ $text($nursingAssessment->height) }}</td>
                <th>{{ localize('global.bmi') }}</th>
                <td class="center">{{ $text($nursingAssessment->bmi) }}</td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <th colspan="6" class="section-head">{{ localize('global.pregnancy') }}</th>
            </tr>
            <tr>
                <th>{{ localize('global.yes') }} / {{ localize('global.not') }}</th>
                <td colspan="3">
                    <div class="checks">
                        <span class="check-item">{{ $mark($nursingAssessment->pregnancy_yes) }} {{ localize('global.yes') }}</span>
                        <span class="check-item">{{ $mark($nursingAssessment->pregnancy_no) }} {{ localize('global.not') }}</span>
                    </div>
                </td>
                <th>{{ localize('global.pregnancy_age') }}</th>
                <td class="center">{{ $text($nursingAssessment->pregnancy_age) }}</td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <th colspan="4" class="section-head">{{ localize('global.medical_history') }}</th>
            </tr>
            <tr>
                <th>{{ localize('global.underlying_disease') }}</th>
                <td colspan="3">
                    <div class="checks">
                        <span class="check-item">{{ $mark($nursingAssessment->underlying_disease_yes) }} {{ localize('global.yes') }}</span>
                        <span class="check-item">{{ $mark($nursingAssessment->underlying_disease_no) }} {{ localize('global.not') }}</span>
                        <span class="check-item">{{ $mark($nursingAssessment->underlying_disease_dm) }} DM</span>
                        <span class="check-item">{{ $mark($nursingAssessment->underlying_disease_ht) }} HT</span>
                    </div>
                    <div style="margin-top:4px;">
                        <strong>{{ localize('global.other') }}:</strong> {{ $text($nursingAssessment->underlying_disease_other) }}
                    </div>
                </td>
            </tr>
            <tr>
                <th>{{ localize('global.hospitalization_history') }}</th>
                <td>
                    <div class="checks">
                        <span class="check-item">{{ $mark($nursingAssessment->hospitalization_history_yes) }} {{ localize('global.yes') }}</span>
                        <span class="check-item">{{ $mark($nursingAssessment->hospitalization_history_no) }} {{ localize('global.not') }}</span>
                    </div>
                </td>
                <th>{{ localize('global.reasons') }}</th>
                <td>{{ $text($nursingAssessment->hospitalization_history_reasons) }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.surgical_history') }}</th>
                <td>
                    <div class="checks">
                        <span class="check-item">{{ $mark($nursingAssessment->surgical_history_yes) }} {{ localize('global.yes') }}</span>
                        <span class="check-item">{{ $mark($nursingAssessment->surgical_history_no) }} {{ localize('global.not') }}</span>
                    </div>
                </td>
                <th>{{ localize('global.reasons') }}</th>
                <td>{{ $text($nursingAssessment->surgical_history_reasons) }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.allergy_history') }}</th>
                <td colspan="3">
                    <div class="checks">
                        <span class="check-item">{{ $mark($nursingAssessment->allergy_history_yes) }} {{ localize('global.yes') }}</span>
                        <span class="check-item">{{ $mark($nursingAssessment->allergy_history_no) }} {{ localize('global.not') }}</span>
                        <span class="check-item">{{ $mark($nursingAssessment->allergy_history_food) }} {{ localize('global.food') }}</span>
                    </div>
                    <div style="margin-top:4px;">
                        <strong>{{ localize('global.others') }}:</strong> {{ $text($nursingAssessment->allergy_history_others) }}
                    </div>
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <th colspan="6" class="section-head">{{ localize('global.pain_assessment') }}</th>
            </tr>
            <tr>
                <th>{{ localize('global.not') }} / {{ localize('global.yes') }}</th>
                <td>
                    <div class="checks">
                        <span class="check-item">{{ $mark($nursingAssessment->pain_no) }} {{ localize('global.not') }}</span>
                        <span class="check-item">{{ $mark($nursingAssessment->pain_yes) }} {{ localize('global.yes') }}</span>
                    </div>
                </td>
                <th>{{ localize('global.pain_location') }}</th>
                <td>{{ $text($nursingAssessment->pain_location) }}</td>
                <th>{{ localize('global.pain_intensity_score') }}</th>
                <td class="center">{{ $text($nursingAssessment->pain_intensity_score) }}</td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <th colspan="8" class="section-head">{{ localize('global.administrative_details') }}</th>
            </tr>
            <tr>
                <th>{{ localize('global.assessment_initiated_by_rn') }}</th>
                <td>{{ $text($nursingAssessment->assessment_initiated_by_rn) }}</td>
                <th>{{ localize('global.date') }}</th>
                <td class="center">{{ $formatDate($nursingAssessment->assessment_initiated_by_date) }}</td>
                <th>{{ localize('global.time') }}</th>
                <td class="center">{{ $formatTime($nursingAssessment->assessment_initiated_by_time) }}</td>
                <th>{{ localize('global.patient_age') }}</th>
                <td class="center">{{ $text($nursingAssessment->patient_age) }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.file_number') }}</th>
                <td class="center">{{ $text($nursingAssessment->file_number) }}</td>
                <th>HN</th>
                <td class="center">{{ $text($nursingAssessment->hn) }}</td>
                <th>SN</th>
                <td class="center">{{ $text($nursingAssessment->sn) }}</td>
                <th>{{ localize('global.assessment_initiated_by_nurse') }}</th>
                <td>{{ $text($nursingAssessment->assessment_initiated_by_nurse) }}</td>
            </tr>
        </table>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.focus();
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
