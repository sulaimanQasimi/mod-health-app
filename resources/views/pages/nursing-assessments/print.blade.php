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
        
        .two-column-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .three-column-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .four-column-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
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
        
        .notes-section {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .notes-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }
        
        .notes-table th, .notes-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }
        
        .notes-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            width: 20%;
        }
        
        .notes-content {
            width: 80%;
            min-height: 60px;
            font-size: 11px;
            line-height: 1.3;
            word-wrap: break-word;
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
                <th>{{ localize('global.hospital_number') }}</th>
                <th>{{ localize('global.serial_number') }}</th>
            </tr>
            <tr>
                <td>{{ $nursingAssessment->patient_name }}</td>
                <td>{{ $nursingAssessment->patient_age ?? '________________' }}</td>
                <td>{{ $nursingAssessment->file_number ?? '________________' }}</td>
                <td>{{ $nursingAssessment->hospital_number ?? '________________' }}</td>
                <td>{{ $nursingAssessment->serial_number ?? '________________' }}</td>
            </tr>
        </table>
        
        <!-- Admission Details -->
        <div class="section-title">{{ localize('global.admission_details') }}</div>
        <div class="two-column-grid">
            <div>
                <strong>{{ localize('global.admission_date') }}:</strong> {{ $nursingAssessment->admission_date ? $nursingAssessment->admission_date->format('Y-m-d') : '________________' }}
            </div>
            <div>
                <strong>{{ localize('global.admission_time') }}:</strong> {{ $nursingAssessment->admission_time ?? '________________' }}
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
                <div class="checkbox {{ $nursingAssessment->admitted_from_family ? 'checked' : '' }}"></div>
                {{ localize('global.family_member') }}
            </div>
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->admitted_from_telephone ? 'checked' : '' }}"></div>
                {{ localize('global.telephone') }}
            </div>
        </div>
        
        <div class="section-title">{{ localize('global.information_provided_by') }}</div>
        <div class="checkbox-grid">
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->information_provided_by_patient ? 'checked' : '' }}"></div>
                {{ localize('global.patient') }}
            </div>
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->information_provided_by_family ? 'checked' : '' }}"></div>
                {{ localize('global.family_member') }}
            </div>
        </div>
        <div style="margin-top: 10px;">
            <strong>{{ localize('global.number') }}:</strong> {{ $nursingAssessment->information_provided_by_number ?? '________________' }}
        </div>
        
        <!-- Chief Complaint -->
        <div class="section-title">{{ localize('global.chief_complaint') }}</div>
        <div class="text-field">{{ $nursingAssessment->chief_complaint ?? '_________________________________________________' }}</div>
        
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
        @if($nursingAssessment->pregnancy_yes)
        <div style="margin-top: 10px;">
            <strong>{{ localize('global.pregnancy_age') }}:</strong> {{ $nursingAssessment->pregnancy_age ?? '________________' }}
        </div>
        @endif
        
        <!-- Medical History -->
        <div class="section-title">{{ localize('global.medical_history') }}</div>
        
        <!-- Underlying Disease -->
        <div style="margin-bottom: 10px;">
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
            </div>
            @if($nursingAssessment->underlying_disease_yes)
            <div style="margin-top: 5px;">
                <div class="checkbox-grid">
                    <div class="checkbox-item">
                        <div class="checkbox {{ $nursingAssessment->underlying_disease_dm ? 'checked' : '' }}"></div>
                        {{ localize('global.diabetes') }}
                    </div>
                    <div class="checkbox-item">
                        <div class="checkbox {{ $nursingAssessment->underlying_disease_ht ? 'checked' : '' }}"></div>
                        {{ localize('global.hypertension') }}
                    </div>
                    <div class="checkbox-item">
                        <div class="checkbox {{ $nursingAssessment->underlying_disease_other ? 'checked' : '' }}"></div>
                        {{ localize('global.other') }}
                    </div>
                </div>
                <div style="margin-top: 5px;">
                    <strong>{{ localize('global.reasons') }}:</strong> {{ $nursingAssessment->underlying_disease_reasons ?? '________________' }}
                </div>
            @endif
        </div>
        
        <!-- Hospitalization History -->
        <div style="margin-bottom: 10px;">
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
            @if($nursingAssessment->hospitalization_history_yes)
            <div style="margin-top: 5px;">
                <strong>{{ localize('global.reasons') }}:</strong> {{ $nursingAssessment->hospitalization_history_reasons ?? '________________' }}
            </div>
            @endif
        </div>
        
        <!-- Surgical History -->
        <div style="margin-bottom: 10px;">
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
            @if($nursingAssessment->surgical_history_yes)
            <div style="margin-top: 5px;">
                <strong>{{ localize('global.reasons') }}:</strong> {{ $nursingAssessment->surgical_history_reasons ?? '________________' }}
            </div>
            @endif
        </div>
        
        <!-- Allergy History -->
        <div style="margin-bottom: 10px;">
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
            </div>
            @if($nursingAssessment->allergy_history_yes)
            <div style="margin-top: 5px;">
                <div class="checkbox-grid">
                    <div class="checkbox-item">
                        <div class="checkbox {{ $nursingAssessment->allergy_food ? 'checked' : '' }}"></div>
                        {{ localize('global.food') }}
                    </div>
                    <div class="checkbox-item">
                        <div class="checkbox {{ $nursingAssessment->allergy_others ? 'checked' : '' }}"></div>
                        {{ localize('global.others') }}
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Family Medical History -->
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.family_medical_history') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->family_medical_history_yes ? 'checked' : '' }}"></div>
                    {{ localize('global.yes') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->family_medical_history_no ? 'checked' : '' }}"></div>
                    {{ localize('global.not') }}
                </div>
            </div>
        </div>
        
        <!-- Follow Up -->
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.follow_up') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->follow_up_yes ? 'checked' : '' }}"></div>
                    {{ localize('global.yes') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->follow_up_no ? 'checked' : '' }}"></div>
                    {{ localize('global.not') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->follow_up_never ? 'checked' : '' }}"></div>
                    {{ localize('global.never') }}
                </div>
            </div>
        </div>
        
        <!-- Drugs -->
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.drugs') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->drugs_yes ? 'checked' : '' }}"></div>
                    {{ localize('global.yes') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->drugs_no ? 'checked' : '' }}"></div>
                    {{ localize('global.not') }}
                </div>
            </div>
        </div>
        
        <!-- Vaccination -->
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.vaccination') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->vaccination_yes ? 'checked' : '' }}"></div>
                    {{ localize('global.yes') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->vaccination_no ? 'checked' : '' }}"></div>
                    {{ localize('global.not') }}
                </div>
            </div>
        </div>
        
        <!-- Physical Checkup -->
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.physical_checkup') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->physical_checkup_yes ? 'checked' : '' }}"></div>
                    {{ localize('global.yes') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->physical_checkup_no ? 'checked' : '' }}"></div>
                    {{ localize('global.not') }}
                </div>
            </div>
        </div>
        
        <!-- Nutrition/Metabolism -->
        <div class="section-title">{{ localize('global.nutrition_metabolism') }}</div>
        
        <!-- Nutrition Problems -->
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.nutrition_problems') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->nutrition_problem_none ? 'checked' : '' }}"></div>
                    {{ localize('global.none') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->nutrition_problem_normal ? 'checked' : '' }}"></div>
                    {{ localize('global.normal') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->nutrition_problem_decrease ? 'checked' : '' }}"></div>
                    {{ localize('global.decrease') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->nutrition_problem_vomiting ? 'checked' : '' }}"></div>
                    {{ localize('global.vomiting') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->nutrition_problem_difficulty_swallowing ? 'checked' : '' }}"></div>
                    {{ localize('global.difficulty_swallowing') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->nutrition_problem_other ? 'checked' : '' }}"></div>
                    {{ localize('global.other') }}
                </div>
            </div>
        </div>
        
        <!-- Diet -->
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.diet') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->diet_npo ? 'checked' : '' }}"></div>
                    NPO
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->diet_normal ? 'checked' : '' }}"></div>
                    {{ localize('global.normal_diet') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->diet_liquid ? 'checked' : '' }}"></div>
                    {{ localize('global.liquid_diet') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->diet_breast_feeding ? 'checked' : '' }}"></div>
                    {{ localize('global.breast_feeding') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->diet_other ? 'checked' : '' }}"></div>
                    {{ localize('global.other') }}
                </div>
            </div>
        </div>
        
        <!-- Skin Assessment -->
        <div class="section-title">{{ localize('global.skin_assessment') }}</div>
        
        <!-- Skin Color -->
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.skin_color') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->skin_color_normal ? 'checked' : '' }}"></div>
                    {{ localize('global.normal') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->skin_color_pale ? 'checked' : '' }}"></div>
                    {{ localize('global.pale') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->skin_color_jaundice ? 'checked' : '' }}"></div>
                    {{ localize('global.jaundice') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->skin_color_cyanosis ? 'checked' : '' }}"></div>
                    {{ localize('global.cyanosis') }}
                </div>
            </div>
        </div>
        
        <!-- Skin Elasticity -->
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.skin_elasticity') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->skin_elasticity_normal ? 'checked' : '' }}"></div>
                    {{ localize('global.normal') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->skin_elasticity_weak ? 'checked' : '' }}"></div>
                    {{ localize('global.weak') }}
                </div>
            </div>
        </div>
        
        <!-- Respiratory Assessment -->
        <div class="section-title">{{ localize('global.respiratory_assessment') }}</div>
        
        <!-- Respiratory Rhythm/Depth -->
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.respiratory_rhythm_depth') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->respiratory_rhythm_regular ? 'checked' : '' }}"></div>
                    {{ localize('global.regular') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->respiratory_rhythm_irregular ? 'checked' : '' }}"></div>
                    {{ localize('global.irregular') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->respiratory_depth_deep ? 'checked' : '' }}"></div>
                    {{ localize('global.deep') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->respiratory_depth_shallow ? 'checked' : '' }}"></div>
                    {{ localize('global.shallow') }}
                </div>
            </div>
        </div>
        
        <!-- Cough -->
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.cough') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->cough_yes ? 'checked' : '' }}"></div>
                    {{ localize('global.yes') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->cough_dry ? 'checked' : '' }}"></div>
                    {{ localize('global.dry') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->cough_productive ? 'checked' : '' }}"></div>
                    {{ localize('global.productive') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->cough_other ? 'checked' : '' }}"></div>
                    {{ localize('global.other') }}
                </div>
            </div>
        </div>
        
        <!-- Cardiovascular Assessment -->
        <div class="section-title">{{ localize('global.cardiovascular_assessment') }}</div>
        
        <!-- Pulse Amplitude -->
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.pulse_amplitude') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->pulse_amplitude_strong ? 'checked' : '' }}"></div>
                    {{ localize('global.strong') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->pulse_amplitude_weak ? 'checked' : '' }}"></div>
                    {{ localize('global.weak') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->pulse_amplitude_absent ? 'checked' : '' }}"></div>
                    {{ localize('global.absent') }}
                </div>
            </div>
        </div>
        
        <!-- Edema -->
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.edema') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->edema_no ? 'checked' : '' }}"></div>
                    {{ localize('global.no') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->edema_general ? 'checked' : '' }}"></div>
                    {{ localize('global.general') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->edema_location ? 'checked' : '' }}"></div>
                    {{ localize('global.location') }}
                </div>
            </div>
            @if($nursingAssessment->edema_location)
            <div style="margin-top: 5px;">
                <strong>{{ localize('global.location') }}:</strong> {{ $nursingAssessment->edema_location_details ?? '________________' }}
            </div>
            @endif
        </div>
        
        <!-- Pain Assessment -->
        <div class="section-title">{{ localize('global.pain_assessment') }}</div>
        
        <!-- Pain Presence -->
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.pain_presence') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->pain_yes ? 'checked' : '' }}"></div>
                    {{ localize('global.yes') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->pain_no ? 'checked' : '' }}"></div>
                    {{ localize('global.no') }}
                </div>
            </div>
        </div>
        
        @if($nursingAssessment->pain_yes)
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.pain_location') }}:</strong> {{ $nursingAssessment->pain_location ?? '________________' }}
        </div>
        
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.pain_pattern') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->pain_pattern_intermittent ? 'checked' : '' }}"></div>
                    {{ localize('global.intermittent') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->pain_pattern_constant ? 'checked' : '' }}"></div>
                    {{ localize('global.constant') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->pain_pattern_other ? 'checked' : '' }}"></div>
                    {{ localize('global.other') }}
                </div>
            </div>
        </div>
        
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.pain_intensity_score') }}:</strong> {{ $nursingAssessment->pain_intensity_score ?? '________________' }}
        </div>
        
        <div style="margin-bottom: 10px;">
            <strong>{{ localize('global.pain_description') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->pain_description_burning ? 'checked' : '' }}"></div>
                    {{ localize('global.burning_pain') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->pain_description_dull ? 'checked' : '' }}"></div>
                    {{ localize('global.dull_pain') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->pain_description_sharp ? 'checked' : '' }}"></div>
                    {{ localize('global.sharp_pain') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->pain_description_electrical ? 'checked' : '' }}"></div>
                    {{ localize('global.electrical_pain') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->pain_description_other ? 'checked' : '' }}"></div>
                    {{ localize('global.other') }}
                </div>
            </div>
        </div>
        @endif
        
        <!-- Religion -->
        <div class="section-title">{{ localize('global.religion') }}</div>
        <div class="checkbox-grid">
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->religion_islam ? 'checked' : '' }}"></div>
                {{ localize('global.islam') }}
            </div>
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->religion_other ? 'checked' : '' }}"></div>
                {{ localize('global.other') }}
            </div>
        </div>
        @if($nursingAssessment->religion_other)
        <div style="margin-top: 5px;">
            <strong>{{ localize('global.other') }}:</strong> {{ $nursingAssessment->religion_other_details ?? '________________' }}
        </div>
        @endif
        
        <!-- Anxiety -->
        <div class="section-title">{{ localize('global.anxiety') }}</div>
        <div class="checkbox-grid">
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->anxiety_no ? 'checked' : '' }}"></div>
                {{ localize('global.no') }}
            </div>
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->anxiety_yes ? 'checked' : '' }}"></div>
                {{ localize('global.yes') }}
            </div>
        </div>
        
        @if($nursingAssessment->anxiety_yes)
        <div style="margin-top: 10px;">
            <strong>{{ localize('global.anxiety_causes') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->anxiety_cause_illness ? 'checked' : '' }}"></div>
                    {{ localize('global.illness') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->anxiety_cause_family ? 'checked' : '' }}"></div>
                    {{ localize('global.family') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->anxiety_cause_work ? 'checked' : '' }}"></div>
                    {{ localize('global.work') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->anxiety_cause_finance ? 'checked' : '' }}"></div>
                    {{ localize('global.finance') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->anxiety_cause_other ? 'checked' : '' }}"></div>
                    {{ localize('global.other') }}
                </div>
            </div>
        </div>
        @endif
        
        <!-- Support System -->
        <div class="section-title">{{ localize('global.support_system') }}</div>
        <div class="checkbox-grid">
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->support_system_no ? 'checked' : '' }}"></div>
                {{ localize('global.no') }}
            </div>
            <div class="checkbox-item">
                <div class="checkbox {{ $nursingAssessment->support_system_yes ? 'checked' : '' }}"></div>
                {{ localize('global.yes') }}
            </div>
        </div>
        
        @if($nursingAssessment->support_system_yes)
        <div style="margin-top: 10px;">
            <strong>{{ localize('global.support_system_types') }}:</strong>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->support_system_family ? 'checked' : '' }}"></div>
                    {{ localize('global.family') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->support_system_friend ? 'checked' : '' }}"></div>
                    {{ localize('global.friend') }}
                </div>
                <div class="checkbox-item">
                    <div class="checkbox {{ $nursingAssessment->support_system_other ? 'checked' : '' }}"></div>
                    {{ localize('global.other') }}
                </div>
            </div>
        </div>
        @endif
        
        <!-- Assessment Initiation Details -->
        <div class="section-title">{{ localize('global.assessment_initiation_details') }}</div>
        <div class="two-column-grid">
            <div>
                <strong>{{ localize('global.assessment_initiated_by') }}:</strong> {{ $nursingAssessment->assessment_initiated_by ?? '________________' }}
            </div>
            <div>
                <strong>{{ localize('global.assessment_date') }}:</strong> {{ $nursingAssessment->assessment_initiated_by_date ? $nursingAssessment->assessment_initiated_by_date->format('Y-m-d') : '________________' }}
            </div>
        </div>
        <div style="margin-top: 10px;">
            <strong>{{ localize('global.assessment_time') }}:</strong> {{ $nursingAssessment->assessment_initiated_by_time ?? '________________' }}
        </div>
        
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
