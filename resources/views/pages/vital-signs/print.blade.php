<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ localize('global.vital_signs_chart') }} - </title>
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
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .patient-info h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            font-weight: bold;
        }

        .patient-info p {
            margin: 2px 0;
            font-size: 10px;
            display: inline-block;
            margin-right: 15px;
        }

        .patient-details-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 10px;
        }

        .patient-details-row span {
            white-space: nowrap;
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

        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 10px;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
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
                <h3>{{ localize('global.patient_name') }}: {{ $vitalSigns->first()->morphable->patient->name ?? 'N/A' }}</h3>
                <div class="patient-details-row">
                    <span><strong>{{ localize('global.diagnosis') }}:</strong> {{ $vitalSigns->first()->morphable->reason ?? 'N/A' }}</span>
                    <span><strong>{{ localize('global.record') }}:</strong> {{ class_basename($vitalSigns->first()->morphable_type) }} #{{ $vitalSigns->first()->morphable_id }}</span>
                    <span><strong>{{ localize('global.room') }}:</strong> 
                        @php
                            $room = $vitalSigns->first()->morphable->room;
                            $roomName = 'N/A';
                            if (is_string($room)) {
                                $roomName = $room;
                            } elseif (is_object($room) && isset($room->name)) {
                                $roomName = $room->name;
                            } elseif (is_array($room) && isset($room['name'])) {
                                $roomName = $room['name'];
                            }
                        @endphp
                        {{ $roomName }}
                    </span>
                    <span><strong>{{ localize('global.date') }}:</strong> {{ $vitalSigns->first()->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="chart-title">
                {{ localize('global.vital_signs_chart') }}<br>
                {{ $vitalSigns->first()->morphable->patient->name ?? 'N/A' }}
            </div>
        </div>

        <!-- Main Grid -->
        <div>

            <table>
                <thead>
                    <tr>
                        <th rowspan="2">{{ localize('global.no') }}</th>
                        <th>{{ localize('global.vital_sign_type') }}</th>
                        <th colspan="2">Day 1</th>
                        <th colspan="2">Day 2</th>
                        <th colspan="2">Day 3</th>
                        <th colspan="2">Day 4</th>
                        <th colspan="2">Day 5</th>
                        <th colspan="2">Day 6</th>
                        <th colspan="2">Day 7</th>
                        <th colspan="2">Day 8</th>
                        <th colspan="2">Day 9</th>
                        <th colspan="2">Day 10</th>
                        <th colspan="2">Day 11</th>
                        <th colspan="2">Day 12</th>
                        <th colspan="2">Day 13</th>
                        <th colspan="2">Day 14</th>
                        <th colspan="2">Day 15</th>
                    </tr>
                    <tr>
                        <th></th>
                        @for($i = 1; $i <= 15; $i++)
                            <th>{{ localize('global.morning') }}</th>
                            <th>{{ localize('global.evening') }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($vitalSigns as $vitalSign)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $vitalSign->vitalSignType->name }}</td>
                            @for($day = 1; $day <= 15; $day++)
                                @php
                                    $daySchedules = $vitalSign->schedules->where('day', 'Day ' . $day);
                                    $morningSchedule = $daySchedules->filter(fn($s) => filled($s->morning_time))->first();
                                    $eveningSchedule = $daySchedules->filter(fn($s) => filled($s->evening_time))->first();
                                @endphp
                                <td>{{ $morningSchedule?->morning_time ?: '-' }}</td>
                                <td>{{ $eveningSchedule?->evening_time ?: '-' }}</td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="watermark">
        {{ localize('global.printed_on') }}: {{ now()->format('Y-m-d H:i:s') }}
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function () {
            // window.print();
        };
    </script>
</body>

</html>