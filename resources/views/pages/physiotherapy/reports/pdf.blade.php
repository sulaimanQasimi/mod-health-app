<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ localize('global.physiotherapy_report') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h3 {
            background-color: #f0f0f0;
            padding: 8px;
            margin: 0 0 15px 0;
            border: 1px solid #ccc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .stats-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
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
        }
        .stat-label {
            font-size: 11px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
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
                <td>{{ $data['summary']['total_duration'] ?? 0 }} {{ localize('global.minutes') }}</td>
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
