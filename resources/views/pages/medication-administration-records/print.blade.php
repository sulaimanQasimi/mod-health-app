@php
use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ localize('global.mar_print_title') }} - Print</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #fff;
            direction: ltr;
        }
        
        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 5px 0 0 0;
            color: #333;
            font-size: 18px;
            font-weight: normal;
        }
        
        
        /* Patient Information Section */
        .patient-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .patient-info div {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .patient-info label {
            font-weight: bold;
            color: #333;
            min-width: 120px;
        }
        
        .patient-info span {
            border-bottom: 1px solid #333;
            min-width: 150px;
            padding-bottom: 2px;
        }
        
        /* Main Table */
        .mar-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 2px solid #333;
        }
        
        .mar-table th,
        .mar-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }
        
        .mar-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            color: #333;
            font-size: 12px;
        }
        
        .mar-table td {
            font-size: 11px;
            height: 40px;
        }
        
        /* Table Header Structure */
        .header-section {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }
        
        .medication-section {
            background-color: #f5f5f5;
        }
        
        .time-section {
            background-color: #f5f5f5;
        }
        
        /* Time columns styling */
        .time-column {
            width: 60px;
            min-width: 60px;
        }
        
        .medication-column {
            width: 120px;
            min-width: 120px;
        }
        
        .date-column {
            width: 80px;
            min-width: 80px;
        }
        
        
        /* Footer */
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .footer-text {
            text-align: left;
        }
        
        .footer-text p {
            margin: 2px 0;
        }
        
        /* Bottom QR Code Section */
        .bottom-qr-code {
            text-align: center;
        }
        
        .bottom-qr-code .qr-code {
            margin-bottom: 5px;
        }
        
        .bottom-qr-code .qr-label {
            font-size: 10px;
            color: #666;
        }
        
        /* Print specific styles */
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            .no-print {
                display: none;
            }
            .mar-table {
                page-break-inside: avoid;
            }
            .footer-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .bottom-qr-code .qr-code img {
                max-width: 80px;
                height: auto;
            }
        }
        
        /* Bilingual text styling */
        .bilingual {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .bilingual .english {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .bilingual .dari {
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>Medication Administration Record</h1>
        <h2>د درملو د تطبيق شيت</h2>
    </div>

    <!-- Patient Information -->
    <div class="patient-info" dir="rtl">
        <div>
            <label>د ناروغ نوم:</label>
            <span>{{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</span>
        </div>
        <div>
            <label>خونه/بستر شمیره:</label>
            <span>{{ $morphableRecord->room->name ?? '' }} / {{ $morphableRecord->bed->name ?? '' }}</span>
        </div>
        <div>
            <label>د ریکارد شمیره:</label>
            <span>{{ $morphableRecord->id ?? '' }}</span>
        </div>
    </div>

    <!-- Main MAR Table -->
    <table class="mar-table">
        <thead>
            <!-- First header row with section titles -->
            <tr class="header-section">
                <th colspan="3" class="medication-section">
                    <div class="bilingual">
                        <div class="english">Medication Information</div>
                        <div class="dari">د درملو معلومات</div>
                    </div>
                </th>
                <th colspan="6" class="time-section">
                    <div class="bilingual">
                        <div class="english">Administration Times</div>
                        <div class="dari">د تطبيق وختونه</div>
                    </div>
                </th>
            </tr>
            
            <!-- Second header row with column titles -->
            <tr class="header-section">
                <th class="medication-column">
                    <div class="bilingual">
                        <div class="english">Medication</div>
                        <div class="dari">درمل</div>
                    </div>
                </th>
                <th class="date-column">
                    <div class="bilingual">
                        <div class="english">Order Date</div>
                        <div class="dari">د امر نیټه</div>
                    </div>
                </th>
                <th class="date-column">
                    <div class="bilingual">
                        <div class="english">Date & Signature</div>
                        <div class="dari">نیټه او لاسلیک</div>
                    </div>
                </th>
                <th class="time-column">
                    <div class="bilingual">
                        <div class="english">Morning</div>
                        <div class="dari">سهار</div>
                    </div>
                </th>
                <th class="time-column">
                    <div class="bilingual">
                        <div class="english">Noon</div>
                        <div class="dari">غرمه</div>
                    </div>
                </th>
                <th class="time-column">
                    <div class="bilingual">
                        <div class="english">Evening</div>
                        <div class="dari">ماښام</div>
                    </div>
                </th>
                <th class="time-column">
                    <div class="bilingual">
                        <div class="english">Night</div>
                        <div class="dari">شپه</div>
                    </div>
                </th>
                <th class="time-column">
                    <div class="bilingual">
                        <div class="english">Late Night</div>
                        <div class="dari">ورځ</div>
                    </div>
                </th>
                <th class="time-column">
                    <div class="bilingual">
                        <div class="english">Afternoon</div>
                        <div class="dari">ماسپښین</div>
                    </div>
                </th>
            </tr>
        </thead>
        
        <tbody>
            @if($medicationAdministrationRecords->count() > 0)
                @foreach($medicationAdministrationRecords as $mar)
                    <tr>
                        <td class="medication-column">
                            <strong>{{ $mar->medicine->name ?? 'N/A' }}</strong>
                        </td>
                        <td class="date-column">
                            {{ $mar->order_date ? $mar->order_date->format('m/d/Y') : '' }}
                        </td>
                        <td class="date-column">
                            {{ $mar->date_signature ? $mar->date_signature->format('m/d/Y') : '' }}
                        </td>
                        @php
                            $times = $mar->administrationTimes->pluck('time')->map(function($time) {
                                return \Carbon\Carbon::parse($time);
                            })->sortBy('time');
                        @endphp
                        <td class="time-column">
                            @php
                                $morningTimes = $times->filter(function($time) {
                                    return $time->hour >= 5 && $time->hour < 12;
                                });
                            @endphp
                            @if($morningTimes->count() > 0)
                                @foreach($morningTimes as $time)
                                    {{ $time->format('g:i A') }}@if(!$loop->last), @endif
                                @endforeach
                            @endif
                        </td>
                        <td class="time-column">
                            @php
                                $noonTimes = $times->filter(function($time) {
                                    return $time->hour >= 12 && $time->hour < 14;
                                });
                            @endphp
                            @if($noonTimes->count() > 0)
                                @foreach($noonTimes as $time)
                                    {{ $time->format('g:i A') }}@if(!$loop->last), @endif
                                @endforeach
                            @endif
                        </td>
                        <td class="time-column">
                            @php
                                $eveningTimes = $times->filter(function($time) {
                                    return $time->hour >= 17 && $time->hour < 20;
                                });
                            @endphp
                            @if($eveningTimes->count() > 0)
                                @foreach($eveningTimes as $time)
                                    {{ $time->format('g:i A') }}@if(!$loop->last), @endif
                                @endforeach
                            @endif
                        </td>
                        <td class="time-column">
                            @php
                                $nightTimes = $times->filter(function($time) {
                                    return $time->hour >= 20 && $time->hour < 24;
                                });
                            @endphp
                            @if($nightTimes->count() > 0)
                                @foreach($nightTimes as $time)
                                    {{ $time->format('g:i A') }}@if(!$loop->last), @endif
                                @endforeach
                            @endif
                        </td>
                        <td class="time-column">
                            @php
                                $lateNightTimes = $times->filter(function($time) {
                                    return $time->hour >= 0 && $time->hour < 5;
                                });
                            @endphp
                            @if($lateNightTimes->count() > 0)
                                @foreach($lateNightTimes as $time)
                                    {{ $time->format('g:i A') }}@if(!$loop->last), @endif
                                @endforeach
                            @endif
                        </td>
                        <td class="time-column">
                            @php
                                $otherTimes = $times->filter(function($time) {
                                    return $time->hour >= 14 && $time->hour < 17;
                                });
                            @endphp
                            @if($otherTimes->count() > 0)
                                @foreach($otherTimes as $time)
                                    {{ $time->format('g:i A') }}@if(!$loop->last), @endif
                                @endforeach
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px; color: #666; font-style: italic;">
                        {{ localize('global.mar_print_no_data') }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-content">
            <div class="footer-text">
                <p>{{ localize('global.mar_print_generated_on') }} {{ now()->format('Y-m-d H:i:s') }}</p>
                <p>{{ localize('global.mar_print_computer_generated') }}</p>
            </div>
            <div class="bottom-qr-code">
                <div class="qr-code">
                    {!! QrCode::size(80)->generate(url()->current()) !!}
                </div>
                <div class="qr-label">
                    <small>Document URL</small>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>