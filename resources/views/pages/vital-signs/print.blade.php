<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ localize('global.vital_signs_chart') }} - {{ $vitalSign->morphable->patient->name ?? 'N/A' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.5in;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: white;
            color: black;
            font-size: 12px;
            line-height: 1.2;
        }
        
        .chart-container {
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .patient-info {
            flex: 1;
            text-align: right;
        }
        
        .patient-info h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .patient-info p {
            margin: 5px 0;
            font-size: 12px;
        }
        
        .chart-title {
            flex: 2;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        
        .main-grid {
            flex: 1;
            display: flex;
            border: 2px solid #000;
        }
        
        .vital-signs-column {
            width: 200px;
            border-right: 2px solid #000;
            background-color: #f8f9fa;
        }
        
        .days-column {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .days-header {
            display: flex;
            border-bottom: 2px solid #000;
            background-color: #e9ecef;
        }
        
        .day-column {
            flex: 1;
            border-right: 1px solid #000;
            text-align: center;
            padding: 5px;
            font-weight: bold;
            font-size: 11px;
        }
        
        .day-column:last-child {
            border-right: none;
        }
        
        .day-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .time-slots {
            display: flex;
            font-size: 10px;
        }
        
        .time-slot {
            flex: 1;
            padding: 2px;
            border-right: 1px solid #ccc;
        }
        
        .time-slot:last-child {
            border-right: none;
        }
        
        .vital-signs-list {
            display: flex;
            flex-direction: column;
        }
        
        .vital-sign-row {
            display: flex;
            border-bottom: 1px solid #000;
            min-height: 30px;
            align-items: center;
        }
        
        .vital-sign-row:last-child {
            border-bottom: none;
        }
        
        .vital-sign-label {
            width: 200px;
            padding: 5px 10px;
            border-right: 2px solid #000;
            font-weight: bold;
            font-size: 11px;
            display: flex;
            align-items: center;
        }
        
        .vital-sign-data {
            flex: 1;
            display: flex;
        }
        
        .day-data {
            flex: 1;
            border-right: 1px solid #000;
            display: flex;
            min-height: 30px;
        }
        
        .day-data:last-child {
            border-right: none;
        }
        
        .time-data {
            flex: 1;
            padding: 2px;
            border-right: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }
        
        .time-data:last-child {
            border-right: none;
        }
        
        .signature-row {
            min-height: 40px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .no-data {
            color: #999;
            font-style: italic;
        }
        
        .watermark {
            position: fixed;
            bottom: 10px;
            left: 10px;
            font-size: 8px;
            color: #ccc;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="chart-container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="patient-info">
                <h3>{{ localize('global.patient_name') }}: {{ $vitalSign->morphable->patient->name ?? 'N/A' }}</h3>
                <p><strong>{{ localize('global.diagnosis') }}:</strong> {{ $vitalSign->morphable->reason ?? 'N/A' }}</p>
                <p><strong>{{ localize('global.record') }}:</strong> {{ class_basename($vitalSign->morphable_type) }} #{{ $vitalSign->morphable_id }}</p>
                <p><strong>{{ localize('global.room') }}:</strong> {{ $vitalSign->morphable->room ?? 'N/A' }}</p>
                <p><strong>{{ localize('global.date') }}:</strong> {{ $vitalSign->created_at->format('d/m/Y') }}</p>
            </div>
            <div class="chart-title">
                {{ localize('global.vital_signs_chart') }}<br>
                {{ $vitalSign->morphable->patient->name ?? 'N/A' }}
            </div>
        </div>

        <!-- Main Grid -->
        <div class="main-grid">
            <!-- Vital Signs Column -->
            <div class="vital-signs-column">
                <div class="vital-signs-list">
                    <div class="vital-sign-row">
                        <div class="vital-sign-label">1. {{ localize('global.bp') }}</div>
                    </div>
                    <div class="vital-sign-row">
                        <div class="vital-sign-label">2. {{ localize('global.pr') }}</div>
                    </div>
                    <div class="vital-sign-row">
                        <div class="vital-sign-label">3. {{ localize('global.rr') }}</div>
                    </div>
                    <div class="vital-sign-row">
                        <div class="vital-sign-label">4. {{ localize('global.spo2') }}</div>
                    </div>
                    <div class="vital-sign-row">
                        <div class="vital-sign-label">5. {{ localize('global.temperature') }}</div>
                    </div>
                    <div class="vital-sign-row">
                        <div class="vital-sign-label">6. {{ localize('global.ng_tube') }}</div>
                    </div>
                    <div class="vital-sign-row">
                        <div class="vital-sign-label">7. {{ localize('global.foley') }}</div>
                    </div>
                    <div class="vital-sign-row">
                        <div class="vital-sign-label">8. {{ localize('global.drain') }}</div>
                    </div>
                    <div class="vital-sign-row">
                        <div class="vital-sign-label">9. {{ localize('global.t_tube') }}</div>
                    </div>
                    <div class="vital-sign-row">
                        <div class="vital-sign-label">10. {{ localize('global.chest_tube') }}</div>
                    </div>
                    <div class="vital-sign-row">
                        <div class="vital-sign-label">11. {{ localize('global.urine_out') }}</div>
                    </div>
                    <div class="vital-sign-row">
                        <div class="vital-sign-label">12. {{ localize('global.feeding') }}</div>
                    </div>
                    <div class="vital-sign-row">
                        <div class="vital-sign-label">13. {{ localize('global.pain_scale') }}</div>
                    </div>
                    <div class="vital-sign-row signature-row">
                        <div class="vital-sign-label">{{ localize('global.signature') }}</div>
                    </div>
                </div>
            </div>

            <!-- Days Column -->
            <div class="days-column">
                <!-- Days Header -->
                <div class="days-header">
                    <div class="day-column">
                        <div class="day-title">{{ localize('global.no') }}</div>
                    </div>
                    @for($day = 1; $day <= 15; $day++)
                        <div class="day-column">
                            <div class="day-title">{{ localize('global.day') }} {{ $day }}</div>
                            <div class="time-slots">
                                <div class="time-slot">{{ localize('global.morning') }}</div>
                                <div class="time-slot">{{ localize('global.evening') }}</div>
                            </div>
                        </div>
                    @endfor
                </div>

                <!-- Vital Signs Data Rows -->
                <div class="vital-sign-data">
                    <!-- No Column -->
                    <div class="day-data" style="width: 50px; border-right: 2px solid #000;">
                        @for($i = 1; $i <= 13; $i++)
                            <div class="time-data" style="border-right: none; border-bottom: 1px solid #000; min-height: 30px;">
                                {{ $i }}
                            </div>
                        @endfor
                        <div class="time-data" style="border-right: none; min-height: 40px;">
                            -
                        </div>
                    </div>

                    <!-- Day Columns -->
                    @for($day = 1; $day <= 15; $day++)
                        <div class="day-data">
                            @php
                                $daySchedules = $vitalSign->schedules->where('day', 'Day ' . $day);
                            @endphp
                            
                            @for($i = 1; $i <= 13; $i++)
                                <div class="time-data" style="border-bottom: 1px solid #000; min-height: 30px;">
                                    @php
                                        $morningSchedule = $daySchedules->where('morning_time', '!=', null)->first();
                                        $eveningSchedule = $daySchedules->where('evening_time', '!=', null)->first();
                                        $hasBothTimes = $morningSchedule && $eveningSchedule;
                                    @endphp
                                    
                                    <div style="display: flex; flex-direction: column; width: 100%;">
                                        <div style="flex: 1; border-bottom: 1px solid #ccc; padding: 1px; font-size: 9px;">
                                            {{ $morningSchedule ? $morningSchedule->morning_time->format('H:i') : '-' }}
                                        </div>
                                        <div style="flex: 1; padding: 1px; font-size: 9px;">
                                            {{ $eveningSchedule ? $eveningSchedule->evening_time->format('H:i') : '-' }}
                                        </div>
                                    </div>
                                </div>
                            @endfor
                            
                            <!-- Signature row for this day -->
                            <div class="time-data" style="min-height: 40px;">
                                <div style="display: flex; flex-direction: column; width: 100%;">
                                    <div style="flex: 1; border-bottom: 1px solid #ccc; padding: 1px; font-size: 9px;">
                                        {{ $daySchedules->first() ? $daySchedules->first()->nurse->full_name ?? '' : '' }}
                                    </div>
                                    <div style="flex: 1; padding: 1px; font-size: 9px;">
                                        -
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    <div class="watermark">
        {{ localize('global.printed_on') }}: {{ now()->format('Y-m-d H:i:s') }}
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
