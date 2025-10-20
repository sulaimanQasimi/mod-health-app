<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Report - {{ $patient->name ?? 'Unknown' }}</title>
    <style>
        @font-face {
            font-family: 'ModFont';
            src: url('{{ asset("assets/fonts/mod_font.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'ModFont', 'Tahoma', 'Arial', sans-serif;
            line-height: 1.4;
            color: #000;
            background: white;
            padding: 15mm;
            direction: rtl;
            text-align: right;
        }
        
        @page {
            margin: 15mm;
            size: A4;
        }
        
        .report-container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .hospital-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .report-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            font-size: 12px;
        }
        
        .qr-code {
            width: 60px;
            height: 60px;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            text-align: center;
        }
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: right;
            vertical-align: top;
        }
        
        .main-table th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 12px;
        }
        
        .main-table td {
            font-size: 11px;
        }
        
        .section-header {
            background: #000;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 14px;
        }
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .results-table th,
        .results-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: right;
            font-size: 10px;
        }
        
        .results-table th {
            background: #f0f0f0;
            font-weight: bold;
        }
        
        .result-value {
            font-weight: bold;
        }
        
        .status-normal {
            font-weight: bold;
        }
        
        .status-abnormal {
            font-weight: bold;
        }
        
        .status-pending {
            font-weight: bold;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        
        .disclaimer {
            background: #f0f0f0;
            border: 1px solid #000;
            padding: 8px;
            margin-top: 10px;
            font-size: 9px;
        }
        
        .print-button {
            background: #000;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .header-section {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .hospital-header {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .hospital-logo {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .header-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            margin-top: 10px;
        }
        
        .user-info {
            background: #f8f8f8;
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 10px;
            font-size: 10px;
        }
        
        @media print {
            @page {
                margin: 15mm;
                size: A4;
            }
            body { 
                margin: 0; 
                padding: 15mm;
                background: white;
                direction: rtl;
                text-align: right;
            }
            .no-print { 
                display: none; 
            }
            .print-button {
                display: none;
            }
            .main-table, .results-table {
                page-break-inside: avoid;
            }
            .section-header {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="hospital-header">
                <div class="hospital-logo">{{ config('app.name', 'Medical Center') }}</div>
                <div class="report-title">{{ localize('global.laboratory_test_report') }}</div>
            </div>
            
            <div class="header-info">
                <div>
                    <strong>{{ localize('global.report_date') }}:</strong> 
                    {{ \Morilog\Jalali\Jalalian::now()->format('Y/m/d H:i') }}
                </div>
                <div>
                    <strong>{{ localize('global.reference_number') }}:</strong> {{ $results->first()->ref_no ?? 'N/A' }}
                </div>
                <div class="qr-code" id="qr-code-container" data-qr="{{ $results->first()->ref_no ?? 'N/A' }}">
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 50px; font-size: 8px; text-align: center;">
                        <div style="margin-bottom: 2px;">QR</div>
                        <div style="font-size: 6px; word-break: break-all;">{{ $results->first()->ref_no ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Information -->
        <div class="user-info">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="border: 1px solid #000; padding: 4px; width: 25%;"><strong>{{ localize('global.generated_by') }}:</strong></td>
                    <td style="border: 1px solid #000; padding: 4px; width: 25%;">{{ auth()->user()->name ?? 'N/A' }}</td>
                    <td style="border: 1px solid #000; padding: 4px; width: 25%;"><strong>{{ localize('global.user_role') }}:</strong></td>
                    <td style="border: 1px solid #000; padding: 4px; width: 25%;">{{ auth()->user()->roles->first()->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 4px;"><strong>{{ localize('global.generated_at') }}:</strong></td>
                    <td style="border: 1px solid #000; padding: 4px;">{{ \Morilog\Jalali\Jalalian::now()->format('Y/m/d H:i:s') }}</td>
                    <td style="border: 1px solid #000; padding: 4px;"><strong>{{ localize('global.ip_address') }}:</strong></td>
                    <td style="border: 1px solid #000; padding: 4px;">{{ request()->ip() }}</td>
                </tr>
            </table>
        </div>

        <!-- Main Information Table -->
        <table class="main-table">
            <tr class="section-header">
                <th colspan="4">{{ localize('global.patient_information') }}</th>
            </tr>
            <tr>
                <th style="width: 25%;">{{ localize('global.name') }}</th>
                <td style="width: 25%;">{{ $patient->name ?? 'N/A' }} {{ $patient->last_name ?? '' }}</td>
                <th style="width: 25%;">{{ localize('global.father_name') }}</th>
                <td style="width: 25%;">{{ $patient->father_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.age') }}</th>
                <td>{{ $patient->age ?? 'N/A' }} {{ localize('global.years') }}</td>
                <th>{{ localize('global.gender') }}</th>
                <td>{{ $patient->gender ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.phone') }}</th>
                <td>{{ $patient->phone ?? 'N/A' }}</td>
                <th>{{ localize('global.address') }}</th>
                <td>{{ $patient->address ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.id_number') }}</th>
                <td>{{ $patient->id_number ?? 'N/A' }}</td>
                <th>{{ localize('global.blood_type') }}</th>
                <td>{{ $patient->blood_type ?? 'N/A' }}</td>
            </tr>
        </table>

        

        <!-- Test Results Table -->
        <table class="results-table">
            <tr class="section-header">
                <th colspan="5">{{ localize('global.test_results') }}</th>
            </tr>
            <tr>
                <th style="width: 30%;">{{ localize('global.parameter_name') }}</th>
                <th style="width: 20%;">{{ localize('global.result') }}</th>
                <th style="width: 15%;">{{ localize('global.unit') }}</th>
                <th style="width: 20%;">{{ localize('global.normal_range') }}</th>
                <th style="width: 15%;">{{ localize('global.status') }}</th>
            </tr>
            @foreach($results as $result)
            <tr>
                <td><strong>{{ $result->parameter->parameter_name ?? 'N/A' }}</strong></td>
                <td>
                    <span class="result-value">
                        {{ $result->result ?? localize('global.pending') }}
                    </span>
                </td>
                <td>{{ $result->unit ?? 'N/A' }}</td>
                <td>{{ $result->normal_range ?? 'N/A' }}</td>
                <td>
                    @if($result->result)
                        @php
                            $isNormal = true; // You can add logic here to check if result is within normal range
                        @endphp
                        @if($isNormal)
                            <span class="status-normal">{{ localize('global.normal') }}</span>
                        @else
                            <span class="status-abnormal">{{ localize('global.abnormal') }}</span>
                        @endif
                    @else
                        <span class="status-pending">{{ localize('global.pending') }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>

        <!-- Laboratory Information Table -->
        <table class="main-table">
            <tr class="section-header">
                <th colspan="4">{{ localize('global.laboratory_info') }}</th>
            </tr>
            <tr>
                <th style="width: 25%;">{{ localize('global.laboratory_name') }}</th>
                <td style="width: 25%;">{{ config('app.name', 'Medical Center') }}</td>
                <th style="width: 25%;">{{ localize('global.department') }}</th>
                <td style="width: 25%;">{{ localize('global.laboratory_department') }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.contact_phone') }}</th>
                <td>+93 XX XXX XXXX</td>
                <th>{{ localize('global.email') }}</th>
                <td>lab@medicalcenter.com</td>
            </tr>
            <tr>
                <th>{{ localize('global.generated_on') }}</th>
                <td>{{ \Morilog\Jalali\Jalalian::now()->format('Y/m/d H:i') }}</td>
                <th>{{ localize('global.valid_for') }}</th>
                <td>30 {{ localize('global.days') }}</td>
            </tr>
        </table>

        <!-- Verification Table -->
        <table class="main-table">
            <tr class="section-header">
                <th colspan="4">{{ localize('global.verification') }}</th>
            </tr>
            <tr>
                <th style="width: 25%;">{{ localize('global.verified_by') }}</th>
                <td style="width: 25%;">{{ $testRegistration->doctor->name ?? 'N/A' }}</td>
                <th style="width: 25%;">{{ localize('global.verified_on') }}</th>
                <td style="width: 25%;">{{ \Morilog\Jalali\Jalalian::now()->format('Y/m/d') }}</td>
            </tr>
            <tr>
                <th>{{ localize('global.digital_signature') }}</th>
                <td>_________________</td>
                <th>{{ localize('global.report_id') }}</th>
                <td>{{ $results->first()->ref_no ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer">
            <div class="disclaimer">
                <strong>{{ localize('global.disclaimer') }}:</strong> 
                {{ localize('global.report_disclaimer') }}
            </div>
            
            <div class="no-print">
                <button class="print-button" onclick="window.print()">
                    {{ localize('global.print_report') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Include QR Code Generator -->
    @vite('public/assets/js/qr-code-generator.js')
    @vite('public/assets/js/simple-qr-fallback.js')
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            const refNo = '{{ $results->first()->ref_no ?? "N/A" }}';
            
            // Try to generate QR code with npm package first
            if (window.QRCodeGenerator) {
                window.QRCodeGenerator.generateForReport(refNo, 'qr-code-container');
            } else if (window.SimpleQRGenerator) {
                // Fallback to simple QR generator
                window.SimpleQRGenerator.generateSimpleQR(refNo, 'qr-code-container');
            }
            
            // Wait for QR code to generate before printing
            setTimeout(function() {
                window.print();
            }, 3000);
        };
    </script>
</body>
</html>
