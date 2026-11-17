<!DOCTYPE html>
<html lang="en" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $patient = $nursingAssessment->morphable->patient ?? null;
        $patientFullName = trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
        if ($patientFullName === '') {
            $patientFullName = $nursingAssessment->patient_name;
        }

        $formatDate = function ($value) {
            if (!$value) {
                return '________________';
            }
            if ($value instanceof \Carbon\Carbon || $value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d');
            }
            return (string) $value;
        };

        $formatTime = function ($value) {
            if (!$value) {
                return '________________';
            }
            if ($value instanceof \Carbon\Carbon || $value instanceof \DateTimeInterface) {
                return $value->format('H:i');
            }
            return substr((string) $value, 0, 5);
        };
    @endphp
    <title>{{ localize('global.nursing_assessment') }} - {{ $patientFullName }}</title>
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: white;
            margin: 0;
            padding: 0;
        }

        .print-container {
            width: 100%;
            max-width: 100%;
        }

        .form-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .patient-info {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-top: 10px;
        }

        .patient-info th,
        .patient-info td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-size: 11px;
        }

        .patient-info th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .checkbox {
            width: 15px;
            height: 15px;
            border: 1px solid #000;
            display: inline-block;
            position: relative;
            vertical-align: middle;
        }

        .checkbox.checked::after {
            content: '✓';
            position: absolute;
            top: -2px;
            left: 2px;
            font-size: 12px;
            font-weight: bold;
        }

        .vital-signs-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }

        .vital-sign-item {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        .vital-sign-label {
            font-weight: bold;
            font-size: 10px;
        }

        .vital-sign-value {
            font-size: 12px;
            margin-top: 5px;
        }

        .two-column-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 5px;
            margin-bottom: 10px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
        }

        .text-field {
            border: 1px solid #000;
            padding: 8px;
            min-height: 20px;
            font-size: 11px;
        }

        .history-block {
            margin-bottom: 12px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }

        .print-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        {{ localize('global.print') }}
    </button>

    <div class="print-container">
        <div class="form-title">
            {{ localize('global.nursing_assessment') }}
        </div>

        <!-- Patient Information -->
        <table class="patient-info">
            <tr>
                <th>{{ localize('global.patient_name') }}</th>
                <th>{{ localize('global.patient_age') }}</th>
                <th>{{ localize('global.file_number') }}</th>
                <th>HN</th>
                <th>SN</th>
            </tr>
            <tr>
                <td>{{ $patientFullName ?: '________________' }}</td>
                <td>{{ $nursingAssessment->patient_age ?? '________________' }}</td>
                <td>{{ $nursingAssessment->file_number ?: '________________' }}</td>
                <td>{{ $nursingAssessment->hn ?: '________________' }}</td>
                <td>{{ $nursingAssessment->sn ?: '________________' }}</td>
            </tr>
        </table>

        <!-- Admission Details -->
        <div class="section-title">{{ localize('global.admission_details') }}</div>
        <div class="two-column-grid">
            <div>
                <strong>{{ localize('global.date') }}:</strong>
                {{ $formatDate($nursingAssessment->admitted_from_date) }}
            </div>
            <div>
                <strong>{{ localize('global.time') }}:</strong>
                {{ $formatTime($nursingAssessment->admitted_from_time) }}
            </div>
        </div>

        <div class="section-title">{{ localize('global.admitted_from') }}</div>
        <div class="checkbox-grid">
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->admitted_from_emergency ? 'checked' : '' }}"></div>
                {{ localize('global.emergency') }}
            </div>
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->admitted_from_hospital ? 'checked' : '' }}"></div>
                {{ localize('global.hospital') }}
            </div>
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->admitted_from_family_member ? 'checked' : '' }}"></div>
                {{ localize('global.family_member') }}
            </div>
        </div>

        <div class="section-title">{{ localize('global.information_provided_by') }}</div>
        <div class="checkbox-grid">
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->information_provided_by_patient ? 'checked' : '' }}"></div>
                {{ localize('global.patient') }}
            </div>
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->information_provided_by_family_member ? 'checked' : '' }}"></div>
                {{ localize('global.family_member') }}
            </div>
        </div>

        <!-- Chief Complaint -->
        <div class="section-title">{{ localize('global.chief_complaint') }}</div>
        <div class="text-field">{{ $nursingAssessment->chief_complaint ?: '_________________________________________________' }}</div>

        <!-- Vital Signs -->
        <div class="section-title">{{ localize('global.vital_signs') }}</div>
        <div class="vital-signs-grid">
            <div class="vital-sign-item">
                <div class="vital-sign-label">{{ localize('global.blood_pressure') }}</div>
                <div class="vital-sign-value">{{ $nursingAssessment->blood_pressure ?: '________________' }}</div>
            </div>
            <div class="vital-sign-item">
                <div class="vital-sign-label">{{ localize('global.pulse_rate') }}</div>
                <div class="vital-sign-value">{{ $nursingAssessment->pulse_rate ?? '________________' }}</div>
            </div>
            <div class="vital-sign-item">
                <div class="vital-sign-label">{{ localize('global.respiratory_rate') }}</div>
                <div class="vital-sign-value">{{ $nursingAssessment->respiratory_rate ?? '________________' }}</div>
            </div>
            <div class="vital-sign-item">
                <div class="vital-sign-label">{{ localize('global.temperature') }}</div>
                <div class="vital-sign-value">{{ $nursingAssessment->temperature ?? '________________' }}</div>
            </div>
            <div class="vital-sign-item">
                <div class="vital-sign-label">{{ localize('global.oxygen_saturation') }}</div>
                <div class="vital-sign-value">{{ $nursingAssessment->oxygen_saturation ?? '________________' }}</div>
            </div>
            <div class="vital-sign-item">
                <div class="vital-sign-label">{{ localize('global.weight') }}</div>
                <div class="vital-sign-value">{{ $nursingAssessment->weight ?? '________________' }}</div>
            </div>
            <div class="vital-sign-item">
                <div class="vital-sign-label">{{ localize('global.height') }}</div>
                <div class="vital-sign-value">{{ $nursingAssessment->height ?? '________________' }}</div>
            </div>
            <div class="vital-sign-item">
                <div class="vital-sign-label">{{ localize('global.bmi') }}</div>
                <div class="vital-sign-value">{{ $nursingAssessment->bmi ?? '________________' }}</div>
            </div>
        </div>

        <!-- Pregnancy -->
        <div class="section-title">{{ localize('global.pregnancy') }}</div>
        <div class="checkbox-grid">
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->pregnancy_yes ? 'checked' : '' }}"></div>
                {{ localize('global.yes') }}
            </div>
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->pregnancy_no ? 'checked' : '' }}"></div>
                {{ localize('global.not') }}
            </div>
        </div>
        @if($nursingAssessment->pregnancy_yes || $nursingAssessment->pregnancy_age)
        <div style="margin-top: 10px;">
            <strong>{{ localize('global.pregnancy_age') }}:</strong>
            {{ $nursingAssessment->pregnancy_age ?? '________________' }}
        </div>
        @endif

        <!-- Medical History -->
        <div class="section-title">{{ localize('global.medical_history') }}</div>

        <div class="history-block">
            <strong>{{ localize('global.underlying_disease') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->underlying_disease_yes ? 'checked' : '' }}"></div>
                    {{ localize('global.yes') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->underlying_disease_no ? 'checked' : '' }}"></div>
                    {{ localize('global.not') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->underlying_disease_dm ? 'checked' : '' }}"></div>
                    DM
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->underlying_disease_ht ? 'checked' : '' }}"></div>
                    HT
                </div>
            </div>
            @if($nursingAssessment->underlying_disease_other)
            <div style="margin-top: 5px;">
                <strong>{{ localize('global.other') }}:</strong>
                {{ $nursingAssessment->underlying_disease_other }}
            </div>
            @endif
        </div>

        <div class="history-block">
            <strong>{{ localize('global.hospitalization_history') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->hospitalization_history_yes ? 'checked' : '' }}"></div>
                    {{ localize('global.yes') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->hospitalization_history_no ? 'checked' : '' }}"></div>
                    {{ localize('global.not') }}
                </div>
            </div>
            @if($nursingAssessment->hospitalization_history_reasons)
            <div style="margin-top: 5px;">
                <strong>{{ localize('global.reasons') }}:</strong>
                {{ $nursingAssessment->hospitalization_history_reasons }}
            </div>
            @endif
        </div>

        <div class="history-block">
            <strong>{{ localize('global.surgical_history') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->surgical_history_yes ? 'checked' : '' }}"></div>
                    {{ localize('global.yes') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->surgical_history_no ? 'checked' : '' }}"></div>
                    {{ localize('global.not') }}
                </div>
            </div>
            @if($nursingAssessment->surgical_history_reasons)
            <div style="margin-top: 5px;">
                <strong>{{ localize('global.reasons') }}:</strong>
                {{ $nursingAssessment->surgical_history_reasons }}
            </div>
            @endif
        </div>

        <div class="history-block">
            <strong>{{ localize('global.allergy_history') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->allergy_history_yes ? 'checked' : '' }}"></div>
                    {{ localize('global.yes') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->allergy_history_no ? 'checked' : '' }}"></div>
                    {{ localize('global.not') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->allergy_history_food ? 'checked' : '' }}"></div>
                    {{ localize('global.food') }}
                </div>
            </div>
            @if($nursingAssessment->allergy_history_others)
            <div style="margin-top: 5px;">
                <strong>{{ localize('global.others') }}:</strong>
                {{ $nursingAssessment->allergy_history_others }}
            </div>
            @endif
        </div>

        <!-- Pain Assessment -->
        <div class="section-title">{{ localize('global.pain_assessment') }}</div>
        <div class="checkbox-grid">
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->pain_no ? 'checked' : '' }}"></div>
                {{ localize('global.not') }}
            </div>
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->pain_yes ? 'checked' : '' }}"></div>
                {{ localize('global.yes') }}
            </div>
        </div>
        @if($nursingAssessment->pain_yes || $nursingAssessment->pain_location || $nursingAssessment->pain_intensity_score !== null)
        <div class="two-column-grid" style="margin-top: 10px;">
            <div>
                <strong>{{ localize('global.pain_location') }}:</strong>
                {{ $nursingAssessment->pain_location ?: '________________' }}
            </div>
            <div>
                <strong>{{ localize('global.pain_intensity_score') }}:</strong>
                {{ $nursingAssessment->pain_intensity_score ?? '________________' }}
            </div>
        </div>
        @endif

        <!-- Administrative Details -->
        <div class="section-title">{{ localize('global.administrative_details') }}</div>
        <div class="two-column-grid">
            <div>
                <strong>{{ localize('global.assessment_initiated_by_rn') }}:</strong>
                {{ $nursingAssessment->assessment_initiated_by_rn ?: '________________' }}
            </div>
            <div>
                <strong>{{ localize('global.assessment_initiated_by_nurse') }}:</strong>
                {{ $nursingAssessment->assessment_initiated_by_nurse ?: '________________' }}
            </div>
            <div>
                <strong>{{ localize('global.date') }}:</strong>
                {{ $formatDate($nursingAssessment->assessment_initiated_by_date) }}
            </div>
            <div>
                <strong>{{ localize('global.time') }}:</strong>
                {{ $formatTime($nursingAssessment->assessment_initiated_by_time) }}
            </div>
        </div>

        <div style="margin-top: 20px; font-size: 10px; text-align: center;">
            <p><strong>{{ localize('global.nurse') }}:</strong> {{ $nursingAssessment->nurse->full_name ?? '________________' }}</p>
            <p><strong>{{ localize('global.created_at') }}:</strong> {{ $nursingAssessment->created_at->format('Y-m-d H:i') }}</p>
            @if($nursingAssessment->updated_at != $nursingAssessment->created_at)
                <p><strong>{{ localize('global.updated_at') }}:</strong> {{ $nursingAssessment->updated_at->format('Y-m-d H:i') }}</p>
            @endif
        </div>
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
