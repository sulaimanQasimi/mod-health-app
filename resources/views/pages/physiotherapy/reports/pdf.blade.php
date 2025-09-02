<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{ localize('global.physiotherapy_report') }}</title>
    <style>
        /* Print-specific styles for A4 page */
        @page {
            size: A4;
            margin: 1cm;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            .avoid-break {
                page-break-inside: avoid;
            }
        }
        
        @font-face {
            font-family: 'AdobeArabic';
            src: url('{{ asset("assets/fonts/AdobeArabic-Regular.otf") }}') format('opentype');
            font-weight: normal;
            font-style: normal;
        }
        
        @font-face {
            font-family: 'Shabnam';
            src: url('{{ asset("assets/fonts/Shabnam-Bold.ttf") }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        
        @font-face {
            font-family: 'ModFont';
            src: url('{{ asset("assets/fonts/mod_font.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        
        @font-face {
            font-family: 'EngFont';
            src: url('{{ asset("assets/fonts/eng.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        
        body {
            font-family: 'AdobeArabic', 'Shabnam', 'ModFont', 'Arial Unicode MS', 'Tahoma', 'Arial', sans-serif;
            margin: 20px;
            font-size: 12px;
            line-height: 1.4;
            direction: rtl;
            text-align: right;
            background: white;
            color: black;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            font-family: 'Shabnam', 'AdobeArabic', sans-serif;
            font-weight: bold;
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        .header p {
            font-family: 'ModFont', 'AdobeArabic', sans-serif;
            font-size: 14px;
            margin: 0;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section h3 {
            background-color: #f0f0f0;
            padding: 8px;
            margin: 0 0 15px 0;
            border: 1px solid #ccc;
            font-family: 'Shabnam', 'AdobeArabic', sans-serif;
            font-weight: bold;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            direction: rtl;
            page-break-inside: avoid;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: right;
            font-size: 11px;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-family: 'Shabnam', 'AdobeArabic', sans-serif;
        }
        .stats-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            direction: rtl;
            page-break-inside: avoid;
        }
        .stat-box {
            border: 1px solid #000;
            padding: 15px;
            text-align: center;
            width: 22%;
        }
        .stat-number {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            font-family: 'EngFont', 'ModFont', sans-serif;
        }
        .stat-label {
            font-size: 11px;
            font-family: 'AdobeArabic', 'ModFont', sans-serif;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            font-family: 'ModFont', 'AdobeArabic', sans-serif;
            page-break-inside: avoid;
        }
        /* Ensure proper RTL text alignment */
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        /* Fix for table headers alignment */
        thead th {
            text-align: center;
        }
        /* Fix for stat boxes text alignment */
        .stat-box {
            text-align: center;
        }
        /* Additional RTL support */
        * {
            unicode-bidi: inherit;
        }
        /* Ensure proper text direction for all elements */
        div, p, span, h1, h2, h3, h4, h5, h6 {
            direction: rtl;
            unicode-bidi: embed;
        }
        /* Fix for table cells */
        td, th {
            direction: rtl;
            unicode-bidi: embed;
        }
        /* Font styling for different content types */
        td {
            font-family: 'AdobeArabic', 'ModFont', sans-serif;
        }
        p, span {
            font-family: 'AdobeArabic', 'ModFont', sans-serif;
        }
        /* Numbers and IDs use English font */
        .stat-number, td:first-child {
            font-family: 'EngFont', 'ModFont', sans-serif;
        }
        /* Status and progress text */
        .status-text, .progress-text {
            font-family: 'Shabnam', 'AdobeArabic', sans-serif;
            font-weight: bold;
        }
        
        /* Print button styles */
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
        
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <button class="print-button no-print" onclick="window.print()">
        🖨️ {{ localize('global.print') }}
    </button>

    <div class="header">
        <h1>{{ localize('global.physiotherapy_report') }}</h1>
        <p>{{ localize('global.generated_on') }}: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <div class="section">
        <h3>{{ localize('global.report_information') }}</h3>
        <table>
            <tr>
                <td><strong>{{ localize('global.start_date') }}:</strong></td>
                <td>{{ verta($startDate)->format('Y-m-d') }}</td>
                <td><strong>{{ localize('global.end_date') }}:</strong></td>
                <td>{{ verta($endDate)->format('Y-m-d') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>{{ localize('global.summary_statistics') }}</h3>
        
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-number">{{ $data['summary']['total_procedures'] ?? 0 }}</div>
                <div class="stat-label">{{ localize('global.total_procedures') }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">{{ $data['summary']['completed_procedures'] ?? 0 }}</div>
                <div class="stat-label">{{ localize('global.completed') }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">{{ $data['summary']['in_progress_procedures'] ?? 0 }}</div>
                <div class="stat-label">{{ localize('global.in_progress') }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">{{ $data['summary']['pending_procedures'] ?? 0 }}</div>
                <div class="stat-label">{{ localize('global.pending') }}</div>
            </div>
        </div>

        <table>
            <tr>
                <th>{{ localize('global.metric') }}</th>
                <th>{{ localize('global.value') }}</th>
            </tr>
            <tr>
                <td>{{ localize('global.completion_rate') }}</td>
                <td>{{ number_format($data['summary']['completion_rate'] ?? 0, 1) }}%</td>
            </tr>
            <tr>
                <td>{{ localize('global.total_duration') }}</td>
                <td class="text-center">{{ $data['summary']['total_duration'] ?? 0 }} {{ localize('global.minutes') }}</td>
            </tr>
            <tr>
                <td>{{ localize('global.average_duration') }}</td>
                <td>{{ $data['summary']['average_duration'] ?? 0 }} {{ localize('global.minutes') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>{{ localize('global.detailed_report') }}</h3>
        
        @if(isset($data['detailed']) && $data['detailed']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>{{ localize('global.id') }}</th>
                    <th>{{ localize('global.patient') }}</th>
                    <th>{{ localize('global.type') }}</th>
                    <th>{{ localize('global.physiotherapist') }}</th>
                    <th>{{ localize('global.status') }}</th>
                    <th>{{ localize('global.progress') }}</th>
                    <th>{{ localize('global.start_date') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['detailed']->take(50) as $procedure)
                <tr>
                    <td>{{ $procedure->id }}</td>
                    <td>{{ $procedure->appointment->patient->name ?? 'N/A' }}</td>
                    <td>{{ $procedure->physiotherapyType->name ?? 'N/A' }}</td>
                    <td>{{ $procedure->physiotherapist->name ?? 'N/A' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $procedure->status)) }}</td>
                    <td>{{ $procedure->counter }}/{{ $procedure->days_count }}</td>
                    <td>{{ $procedure->start_date ? $procedure->start_date->format('Y-m-d') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p><em>{{ localize('global.showing_first_50_procedures') }} ({{ $data['detailed']->count() }} {{ localize('global.total') }})</em></p>
        @else
        <p>{{ localize('global.no_procedures_found') }}</p>
        @endif
    </div>

    <div class="section">
        <h3>{{ localize('global.report_by_type') }}</h3>
        
        @if(isset($data['by_type']) && $data['by_type']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>{{ localize('global.type') }}</th>
                    <th>{{ localize('global.total') }}</th>
                    <th>{{ localize('global.completed') }}</th>
                    <th>{{ localize('global.in_progress') }}</th>
                    <th>{{ localize('global.pending') }}</th>
                    <th>{{ localize('global.completion_rate') }}</th>
                    <th>{{ localize('global.avg_duration') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['by_type'] as $type)
                <tr>
                    <td>{{ $type['type']->name ?? 'N/A' }}</td>
                    <td>{{ $type['total_procedures'] ?? 0 }}</td>
                    <td>{{ $type['completed_procedures'] ?? 0 }}</td>
                    <td>{{ $type['in_progress_procedures'] ?? 0 }}</td>
                    <td>{{ $type['pending_procedures'] ?? 0 }}</td>
                    <td>{{ number_format($type['completion_rate'] ?? 0, 1) }}%</td>
                    <td>{{ $type['average_duration'] ?? 0 }} {{ localize('global.min') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>{{ localize('global.no_data_available_by_type') }}</p>
        @endif
    </div>

    <div class="section">
        <h3>{{ localize('global.report_by_physiotherapist') }}</h3>
        
        @if(isset($data['by_physiotherapist']['physiotherapists']) && $data['by_physiotherapist']['physiotherapists']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>{{ localize('global.physiotherapist') }}</th>
                    <th>{{ localize('global.total') }}</th>
                    <th>{{ localize('global.completed') }}</th>
                    <th>{{ localize('global.in_progress') }}</th>
                    <th>{{ localize('global.pending') }}</th>
                    <th>{{ localize('global.completion_rate') }}</th>
                    <th>{{ localize('global.performance_score') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['by_physiotherapist']['physiotherapists'] as $physiotherapist)
                <tr>
                    <td>
                        <strong>{{ $physiotherapist['name'] ?? 'N/A' }}</strong>
                        <br><small>{{ $physiotherapist['email'] ?? 'N/A' }}</small>
                    </td>
                    <td>{{ $physiotherapist['total_procedures'] ?? 0 }}</td>
                    <td>{{ $physiotherapist['completed_procedures'] ?? 0 }}</td>
                    <td>{{ $physiotherapist['in_progress_procedures'] ?? 0 }}</td>
                    <td>{{ $physiotherapist['pending_procedures'] ?? 0 }}</td>
                    <td>{{ number_format($physiotherapist['completion_rate'] ?? 0, 1) }}%</td>
                    <td>{{ number_format($physiotherapist['performance_score'] ?? 0, 1) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>{{ localize('global.no_data_available_by_physiotherapist') }}</p>
        @endif
    </div>

    <div class="footer">
        <p>{{ localize('global.report_generated_by') }} Mod Health App</p>
        <p>{{ localize('global.date_range') }}: {{ verta($startDate)->format('Y-m-d') }} {{ localize('global.to') }} {{ verta($endDate)->format('Y-m-d') }}</p>
    </div>
</body>
</html>
