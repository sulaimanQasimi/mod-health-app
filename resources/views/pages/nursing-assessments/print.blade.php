<!DOCTYPE html>
<html lang="en" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ localize('global.nursing_assessment') }} - {{ $nursingAssessment->patient_name }}</title>
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
        
        .table-container {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .assessment-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-bottom: 15px;
        }
        
        .assessment-table th, .assessment-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }
        
        .assessment-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 11px;
        }
        
        .assessment-table td {
            height: 25px;
            font-size: 11px;
        }
        
        .checkbox-cell {
            width: 20px;
            text-align: center;
        }
        
        .checkbox {
            width: 15px;
            height: 15px;
            border: 1px solid #000;
            display: inline-block;
            position: relative;
        }
        
        .checkbox.checked::after {
            content: '✓';
            position: absolute;
            top: -2px;
            left: 2px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .patient-info {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-top: 20px;
        }
        
        .patient-info th, .patient-info td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-size: 11px;
        }
        
        .patient-info th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .date-signature {
            width: 15%;
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            height: 20px;
            margin-top: 5px;
        }
        
        .no-print {
            display: none;
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
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> {{ localize('global.print') }}
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
                <th>{{ localize('global.assessment_date') }}</th>
            </tr>
            <tr>
                <td>{{ $nursingAssessment->patient_name }}</td>
                <td>{{ $nursingAssessment->patient_age ?? '________________' }}</td>
                <td>{{ $nursingAssessment->file_number ?? '________________' }}</td>
                <td>{{ $nursingAssessment->assessment_initiated_by_date ? $nursingAssessment->assessment_initiated_by_date->format('Y-m-d') : '________________' }}</td>
            </tr>
        </table>
        
        <!-- Vital Signs -->
        <div class="section-title">{{ localize('global.vital_signs') }}</div>
        <div class="vital-signs-grid">
            <div class="vital-sign-item">
                <div class="vital-sign-label">{{ localize('global.blood_pressure') }}</div>
                <div class="vital-sign-value">{{ $nursingAssessment->blood_pressure ?? '________________' }}</div>
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
        
        <!-- Medical History -->
        <div class="section-title">{{ localize('global.medical_history') }}</div>
        <table class="assessment-table">
            <thead>
                <tr>
                    <th>{{ localize('global.underlying_disease') }}</th>
                    <th>{{ localize('global.hospitalization_history') }}</th>
                    <th>{{ localize('global.surgical_history') }}</th>
                    <th>{{ localize('global.allergy_history') }}</th>
                    <th>{{ localize('global.family_medical_history') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="checkbox-cell">
                        <div class="checkbox {{ $nursingAssessment->underlying_disease_yes ? 'checked' : '' }}"></div>
                        {{ localize('global.yes') }}
                    </td>
                    <td class="checkbox-cell">
                        <div class="checkbox {{ $nursingAssessment->hospitalization_history_yes ? 'checked' : '' }}"></div>
                        {{ localize('global.yes') }}
                    </td>
                    <td class="checkbox-cell">
                        <div class="checkbox {{ $nursingAssessment->surgical_history_yes ? 'checked' : '' }}"></div>
                        {{ localize('global.yes') }}
                    </td>
                    <td class="checkbox-cell">
                        <div class="checkbox {{ $nursingAssessment->allergy_history_yes ? 'checked' : '' }}"></div>
                        {{ localize('global.yes') }}
                    </td>
                    <td class="checkbox-cell">
                        <div class="checkbox {{ $nursingAssessment->family_medical_history_yes ? 'checked' : '' }}"></div>
                        {{ localize('global.yes') }}
                    </td>
                </tr>
                <tr>
                    <td class="checkbox-cell">
                        <div class="checkbox {{ $nursingAssessment->underlying_disease_no ? 'checked' : '' }}"></div>
                        {{ localize('global.no') }}
                    </td>
                    <td class="checkbox-cell">
                        <div class="checkbox {{ $nursingAssessment->hospitalization_history_no ? 'checked' : '' }}"></div>
                        {{ localize('global.no') }}
                    </td>
                    <td class="checkbox-cell">
                        <div class="checkbox {{ $nursingAssessment->surgical_history_no ? 'checked' : '' }}"></div>
                        {{ localize('global.no') }}
                    </td>
                    <td class="checkbox-cell">
                        <div class="checkbox {{ $nursingAssessment->allergy_history_no ? 'checked' : '' }}"></div>
                        {{ localize('global.no') }}
                    </td>
                    <td class="checkbox-cell">
                        <div class="checkbox {{ $nursingAssessment->family_medical_history_no ? 'checked' : '' }}"></div>
                        {{ localize('global.no') }}
                    </td>
                </tr>
            </tbody>
        </table>
        
        <!-- Pain Assessment -->
        <div class="section-title">{{ localize('global.pain_assessment') }}</div>
        <table class="assessment-table">
            <thead>
                <tr>
                    <th>{{ localize('global.pain_presence') }}</th>
                    <th>{{ localize('global.pain_location') }}</th>
                    <th>{{ localize('global.pain_intensity_score') }}</th>
                    <th>{{ localize('global.pain_pattern') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="checkbox-cell">
                        <div class="checkbox {{ $nursingAssessment->pain_yes ? 'checked' : '' }}"></div>
                        {{ localize('global.yes') }}
                    </td>
                    <td>{{ $nursingAssessment->pain_location ?? '________________' }}</td>
                    <td>{{ $nursingAssessment->pain_intensity_score ?? '________________' }}</td>
                    <td class="checkbox-cell">
                        <div class="checkbox {{ $nursingAssessment->pain_pattern_constant ? 'checked' : '' }}"></div>
                        {{ localize('global.constant') }}
                    </td>
                </tr>
                <tr>
                    <td class="checkbox-cell">
                        <div class="checkbox {{ $nursingAssessment->pain_no ? 'checked' : '' }}"></div>
                        {{ localize('global.no') }}
                    </td>
                    <td></td>
                    <td></td>
                    <td class="checkbox-cell">
                        <div class="checkbox {{ $nursingAssessment->pain_pattern_intermittent ? 'checked' : '' }}"></div>
                        {{ localize('global.intermittent') }}
                    </td>
                </tr>
            </tbody>
        </table>
        
        <!-- Chief Complaint -->
        <div class="section-title">{{ localize('global.chief_complaint') }}</div>
        <table class="assessment-table">
            <tr>
                <td style="text-align: left; padding: 15px;">
                    {{ $nursingAssessment->chief_complaint ?? '_________________________________________________' }}
                </td>
            </tr>
        </table>
        
        <!-- Additional Information -->
        <div style="margin-top: 20px; font-size: 10px; text-align: center;">
            <p><strong>{{ localize('global.nurse') }}:</strong> {{ $nursingAssessment->nurse->full_name ?? '________________' }}</p>
            <p><strong>{{ localize('global.created_at') }}:</strong> {{ $nursingAssessment->created_at->format('Y-m-d H:i') }}</p>
            @if($nursingAssessment->updated_at != $nursingAssessment->created_at)
                <p><strong>{{ localize('global.updated_at') }}:</strong> {{ $nursingAssessment->updated_at->format('Y-m-d H:i') }}</p>
            @endif
        </div>
    </div>
    
    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>
