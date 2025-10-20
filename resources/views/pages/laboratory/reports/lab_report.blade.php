<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Report - {{ $patient->name ?? 'Unknown' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .hospital-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .report-title {
            font-size: 18px;
            margin: 10px 0;
            color: #666;
        }
        .patient-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .patient-details {
            flex: 1;
        }
        .report-details {
            flex: 1;
            text-align: right;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .results-table th,
        .results-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .results-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .results-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .normal-range {
            color: #28a745;
            font-weight: bold;
        }
        .abnormal {
            color: #dc3545;
            font-weight: bold;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="hospital-name">{{ localize('global.hospital_name') }}</div>
        <div class="report-title">{{ localize('global.laboratory_test_report') }}</div>
        <div style="font-size: 14px; color: #666;">
            {{ localize('global.report_date') }}: {{ now()->format('d/m/Y H:i') }} | {{ localize('global.reference_number') }}: {{ $results->first()->ref_no ?? 'N/A' }}
        </div>
    </div>

    <div class="patient-info">
        <div class="patient-details">
            <h3>{{ localize('global.patient_information') }}</h3>
            <p><strong>{{ localize('global.name') }}:</strong> {{ $patient->name ?? 'N/A' }} {{ $patient->last_name ?? '' }}</p>
            <p><strong>{{ localize('global.father_name') }}:</strong> {{ $patient->father_name ?? 'N/A' }}</p>
            <p><strong>{{ localize('global.age') }}:</strong> {{ $patient->age ?? 'N/A' }} {{ localize('global.years') }}</p>
            <p><strong>{{ localize('global.phone') }}:</strong> {{ $patient->phone ?? 'N/A' }}</p>
        </div>
        <div class="report-details">
            <h3>{{ localize('global.test_information') }}</h3>
            <p><strong>{{ localize('global.test_name') }}:</strong> {{ $testName }}</p>
            <p><strong>{{ localize('global.reference_number') }}:</strong> {{ $results->first()->ref_no ?? 'N/A' }}</p>
            <p><strong>{{ localize('global.report_date') }}:</strong> {{ now()->format('d/m/Y') }}</p>
        </div>
    </div>

    <table class="results-table">
        <thead>
            <tr>
                <th>{{ localize('global.parameter_name') }}</th>
                <th>{{ localize('global.result') }}</th>
                <th>{{ localize('global.unit') }}</th>
                <th>{{ localize('global.normal_range') }}</th>
                <th>{{ localize('global.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $result)
            <tr>
                <td>{{ $result->parameter->parameter_name ?? 'N/A' }}</td>
                <td>{{ $result->result ?? localize('global.pending') }}</td>
                <td>{{ $result->unit ?? 'N/A' }}</td>
                <td>{{ $result->normal_range ?? 'N/A' }}</td>
                <td>
                    @if($result->result)
                        <span class="normal-range">{{ localize('global.normal') }}</span>
                    @else
                        <span class="abnormal">{{ localize('global.pending') }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated on {{ now()->format('d/m/Y H:i') }}</p>
        <p>For any queries, please contact the laboratory department.</p>
        <p class="no-print">
            <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Print Report
            </button>
        </p>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        };
    </script>
</body>
</html>
