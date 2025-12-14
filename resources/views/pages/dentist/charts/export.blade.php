<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Chart Export - {{ $dentistRegistration->ref_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .patient-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .info-section {
            flex: 1;
        }
        .info-section h3 {
            font-size: 14px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .info-row {
            margin: 5px 0;
        }
        .tooth-chart {
            margin: 30px 0;
        }
        .tooth-row {
            display: flex;
            justify-content: center;
            margin: 10px 0;
            gap: 5px;
        }
        .tooth-oval {
            width: 40px;
            height: 55px;
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            border: 2px solid #000;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            margin: 2px;
        }
        .tooth-healthy { background-color: #008000; color: white; }
        .tooth-cavity { background-color: #ffc107; color: #000; }
        .tooth-filling { background-color: #17a2b8; color: white; }
        .tooth-crown { background-color: #6f42c1; color: white; }
        .tooth-missing { background-color: #6c757d; color: white; }
        .tooth-extraction { background-color: #dc3545; color: white; }
        .chart-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .chart-table th,
        .chart-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .chart-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dental Chart Report</h1>
        <p>Reference Number: {{ $dentistRegistration->ref_no }}</p>
    </div>

    <div class="patient-info">
        <div class="info-section">
            <h3>Patient Information</h3>
            <div class="info-row"><strong>Name:</strong> {{ $dentistRegistration->appointment->patient->name ?? 'N/A' }}</div>
            <div class="info-row"><strong>Age:</strong> {{ $dentistRegistration->appointment->patient->age ?? 'N/A' }}</div>
            <div class="info-row"><strong>Gender:</strong> {{ $dentistRegistration->appointment->patient->gender ?? 'N/A' }}</div>
        </div>
        <div class="info-section">
            <h3>Dentist Information</h3>
            <div class="info-row"><strong>Dentist:</strong> {{ $dentistRegistration->dentist->name ?? 'N/A' }}</div>
            <div class="info-row"><strong>Registration Date:</strong> {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($dentistRegistration->registration_date) }}</div>
            <div class="info-row"><strong>Status:</strong> {{ ucfirst($dentistRegistration->status) }}</div>
        </div>
    </div>

    <div class="tooth-chart">
        <h3 style="text-align: center; margin-bottom: 20px;">Visual Tooth Chart</h3>
        
        <!-- Upper Jaw -->
        <div style="margin-bottom: 20px;">
            <h4 style="text-align: center; margin-bottom: 10px;">Upper Jaw</h4>
            <div class="tooth-row">
                @for($i = 18; $i >= 11; $i--)
                    @php
                        $tooth = $allTeeth[$i] ?? null;
                        $condition = $tooth ? $tooth->tooth_condition : 'no_data';
                    @endphp
                    <div class="tooth-oval tooth-{{ $condition }}" title="Tooth {{ $i }}">
                        {{ $i }}
                    </div>
                @endfor
            </div>
            <div class="tooth-row">
                @for($i = 21; $i <= 28; $i++)
                    @php
                        $tooth = $allTeeth[$i] ?? null;
                        $condition = $tooth ? $tooth->tooth_condition : 'no_data';
                    @endphp
                    <div class="tooth-oval tooth-{{ $condition }}" title="Tooth {{ $i }}">
                        {{ $i }}
                    </div>
                @endfor
            </div>
        </div>

        <!-- Lower Jaw -->
        <div>
            <h4 style="text-align: center; margin-bottom: 10px;">Lower Jaw</h4>
            <div class="tooth-row">
                @for($i = 38; $i >= 31; $i--)
                    @php
                        $tooth = $allTeeth[$i] ?? null;
                        $condition = $tooth ? $tooth->tooth_condition : 'no_data';
                    @endphp
                    <div class="tooth-oval tooth-{{ $condition }}" title="Tooth {{ $i }}">
                        {{ $i }}
                    </div>
                @endfor
            </div>
            <div class="tooth-row">
                @for($i = 41; $i <= 48; $i++)
                    @php
                        $tooth = $allTeeth[$i] ?? null;
                        $condition = $tooth ? $tooth->tooth_condition : 'no_data';
                    @endphp
                    <div class="tooth-oval tooth-{{ $condition }}" title="Tooth {{ $i }}">
                        {{ $i }}
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <table class="chart-table">
        <thead>
            <tr>
                <th>Tooth Number</th>
                <th>Condition</th>
                <th>Gum Health</th>
                <th>Pocket Depth</th>
                <th>Oral Hygiene Score</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($latestCharts as $chart)
                <tr>
                    <td><strong>{{ $chart->tooth_number }}</strong></td>
                    <td>{{ ucfirst(str_replace('_', ' ', $chart->tooth_condition)) }}</td>
                    <td>{{ $chart->gum_health ? ucfirst($chart->gum_health) : 'N/A' }}</td>
                    <td>{{ $chart->pocket_depth ? $chart->pocket_depth . ' mm' : 'N/A' }}</td>
                    <td>{{ $chart->oral_hygiene_score ?? 'N/A' }}</td>
                    <td>{{ Str::limit($chart->notes, 50) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No chart data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
        <p>This is a computer-generated report.</p>
    </div>
</body>
</html>
