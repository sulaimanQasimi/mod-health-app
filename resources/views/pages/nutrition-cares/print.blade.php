<!DOCTYPE html>
<html lang="en" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ localize('global.nutrition_care') }} - {{ $nutritionCare->patient_name }}</title>
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
        
        .table-container {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .observation-table, .intervention-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-bottom: 15px;
        }
        
        .observation-table th, .observation-table td,
        .intervention-table th, .intervention-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }
        
        .observation-table th, .intervention-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 11px;
        }
        
        .observation-table td, .intervention-table td {
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
            width: 15%;
        }
        
        .notes-content {
            width: 85%;
            min-height: 120px;
            font-size: 11px;
            line-height: 1.3;
            word-wrap: break-word;
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
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> {{ localize('global.print') }}
    </button>
    
    <div class="print-container">
        <div class="form-title">
            {{ localize('global.nutrition_care') }}
        </div>
        
        <!-- Observations Table -->
        <div class="table-container">
            <table class="observation-table">
                <thead>
                    <tr>
                        <th>{{ localize('global.cough') }}</th>
                        <th>{{ localize('global.sound') }}</th>
                        <th>{{ localize('global.fluid_swallowing_ability') }}</th>
                        <th>{{ localize('global.weight') }}</th>
                        <th>{{ localize('global.amount_and_type_of_nutrition') }}</th>
                        <th>{{ localize('global.diarrhea') }}</th>
                        <th>{{ localize('global.heart_failure_and_kidney_disease') }}</th>
                        <th>{{ localize('global.remaining_materials') }}</th>
                        <th>{{ localize('global.type_of_tube') }}</th>
                        <th class="date-signature">{{ localize('global.date_signature') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->cough ? 'checked' : '' }}"></div>
                        </td>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->sound ? 'checked' : '' }}"></div>
                        </td>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->fluid_swallowing_ability ? 'checked' : '' }}"></div>
                        </td>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->weight ? 'checked' : '' }}"></div>
                        </td>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->amount_and_type_of_nutrition ? 'checked' : '' }}"></div>
                        </td>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->diarrhea ? 'checked' : '' }}"></div>
                        </td>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->heart_failure_and_kidney_disease ? 'checked' : '' }}"></div>
                        </td>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->remaining_materials ? 'checked' : '' }}"></div>
                        </td>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->type_of_tube ? 'checked' : '' }}"></div>
                        </td>
                        <td class="date-signature">
                            <div>{{ $nutritionCare->created_at->format('Y-m-d') }}</div>
                            <div class="signature-line"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Interventions Table -->
        <div class="table-container">
            <table class="intervention-table">
                <thead>
                    <tr>
                        <th>{{ localize('global.constipation') }}</th>
                        <th>{{ localize('global.nutrition_is_provided') }}</th>
                        <th>{{ localize('global.mouth_hygiene') }}</th>
                        <th>{{ localize('global.oral_nutrition_advices') }}</th>
                        <th>{{ localize('global.voice_exercise') }}</th>
                        <th>{{ localize('global.swallowing_exercise') }}</th>
                        <th>{{ localize('global.aspiration_prevention_proceeded') }}</th>
                        <th class="date-signature">{{ localize('global.date_signature') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->constipation ? 'checked' : '' }}"></div>
                        </td>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->nutrition_is_provided ? 'checked' : '' }}"></div>
                        </td>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->mouth_hygiene ? 'checked' : '' }}"></div>
                        </td>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->oral_nutrition_advices ? 'checked' : '' }}"></div>
                        </td>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->voice_exercise ? 'checked' : '' }}"></div>
                        </td>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->swallowing_exercise ? 'checked' : '' }}"></div>
                        </td>
                        <td class="checkbox-cell">
                            <div class="checkbox {{ $nutritionCare->aspiration_prevention_proceeded ? 'checked' : '' }}"></div>
                        </td>
                        <td class="date-signature">
                            <div>{{ $nutritionCare->created_at->format('Y-m-d') }}</div>
                            <div class="signature-line"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Nutrition Care Full Note Section -->
        <div class="notes-section">
            <table class="notes-table">
                <tr>
                    <th>{{ localize('global.nutrition_care_full_note') }}</th>
                    <th class="date-signature">{{ localize('global.date_signature') }}</th>
                </tr>
                <tr>
                    <td class="notes-content">
                        {{ $nutritionCare->nutrition_care_full_note ?: '_________________________________________________' }}
                    </td>
                    <td class="date-signature">
                        <div>{{ $nutritionCare->created_at->format('Y-m-d') }}</div>
                        <div class="signature-line"></div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Patient Information -->
        <table class="patient-info">
            <tr>
                <th>{{ localize('global.patient_card_number') }}</th>
                <th>{{ localize('global.date_of_birth_age') }}</th>
                <th>{{ localize('global.plan_name') }}</th>
                <th>{{ localize('global.patient_name') }}</th>
            </tr>
            <tr>
                <td>{{ $nutritionCare->morphable->patient->card_number ?? '________________' }}</td>
                <td>{{ $nutritionCare->morphable->patient->date_of_birth ? $nutritionCare->morphable->patient->date_of_birth->format('Y-m-d') : '________________' }}</td>
                <td>{{ $nutritionCare->morphable->patient->plan_name ?? '________________' }}</td>
                <td>{{ $nutritionCare->patient_name }}</td>
            </tr>
        </table>
        
        <!-- Additional Information -->
        <div style="margin-top: 20px; font-size: 10px; text-align: center;">
            <p><strong>{{ localize('global.nurse') }}:</strong> {{ $nutritionCare->nurse->full_name ?? '________________' }}</p>
            <p><strong>{{ localize('global.created_at') }}:</strong> {{ $nutritionCare->created_at->format('Y-m-d H:i') }}</p>
            @if($nutritionCare->updated_at != $nutritionCare->created_at)
                <p><strong>{{ localize('global.updated_at') }}:</strong> {{ $nutritionCare->updated_at->format('Y-m-d H:i') }}</p>
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
