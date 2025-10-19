<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ localize('global.prescription_receipt') }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                width: 80mm;
                font-size: 9px;
                line-height: 1.2;
            }
            
            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: 'Courier New', monospace;
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto;
            padding: 5px;
            font-size: 9px;
            line-height: 1.2;
            color: #000;
            background: white;
        }

        .receipt-container {
            width: 100%;
            max-width: 80mm;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .pharmacy-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .pharmacy-info {
            font-size: 8px;
            margin-bottom: 5px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 8px;
        }

        .patient-info {
            margin-bottom: 8px;
            font-size: 8px;
        }

        .patient-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .doctor-info {
            margin-bottom: 8px;
            font-size: 8px;
        }

        .medicines-section {
            margin-bottom: 8px;
        }

        .medicines-header {
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .medicine-table {
            width: 100%;
            font-size: 7px;
        }

        .medicine-table th {
            text-align: left;
            font-weight: bold;
            padding: 1px 0;
            border-bottom: 1px solid #000;
        }

        .medicine-table td {
            padding: 1px 0;
            vertical-align: top;
        }

        .medicine-row {
            border-bottom: 1px dotted #ccc;
        }

        .replaced-info {
            font-size: 6px;
            color: #666;
            margin-left: 10px;
            margin-top: 1px;
        }

        .footer {
            text-align: center;
            font-size: 8px;
            margin-top: 10px;
        }

        .footer-info {
            margin-bottom: 3px;
        }

        .thank-you {
            font-weight: bold;
            margin-top: 5px;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <div class="pharmacy-name">{{ $pharmacy->name ?? 'PHARMACY' }}</div>
            <div class="pharmacy-info">
                @if($pharmacy)
                    <div>Phone: {{ $pharmacy->phone ?? 'N/A' }}</div>
                    <div>{{ $pharmacy->address ?? 'N/A' }}</div>
                @else
                    <div>Pharmacy Information Not Available</div>
                @endif
            </div>
        </div>

        <div class="divider"></div>

        <!-- Receipt Info -->
        <div class="receipt-info">
            <span class="bold">Rx #: {{ $prescription->id }}</span>
            <span>Date: {{ $prescription->created_at->format('Y-m-d') }}</span>
        </div>

        <!-- Patient Info -->
        <div class="patient-info">
            <div class="patient-row">
                <span class="bold">Patient: {{ $prescription->patient->name ?? 'N/A' }}</span>
            </div>
            <div class="patient-row">
                <span>ID: {{ $prescription->patient->id_card ?? 'N/A' }}</span>
                <span>Age: {{ $prescription->patient->age ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Doctor Info -->
        <div class="doctor-info">
            <span class="bold">Doctor: {{ $prescription->doctor->name ?? 'N/A' }}</span>
        </div>

        <div class="divider"></div>

        <!-- Medicines Section -->
        <div class="medicines-section">
            <div class="medicines-header">MEDICINES:</div>
            <table class="medicine-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">#</th>
                        <th style="width: 45%;">Medicine</th>
                        <th style="width: 15%;">Dose</th>
                        <th style="width: 12%;">Freq</th>
                        <th style="width: 20%;">Amt</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prescription->prescriptionItems as $index => $item)
                        <tr class="medicine-row">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if($item->selectedAlternative)
                                    {{ $item->selectedAlternative->medicine->name ?? 'N/A' }}
                                @else
                                    {{ $item->medicine->name ?? 'N/A' }}
                                @endif
                            </td>
                            <td>
                                @if($item->selectedAlternative)
                                    {{ $item->selectedAlternative->dosage ?? 'N/A' }}
                                @else
                                    {{ $item->dosage ?? 'N/A' }}
                                @endif
                            </td>
                            <td>
                                @if($item->selectedAlternative)
                                    {{ $item->selectedAlternative->frequency ?? 'N/A' }}
                                @else
                                    {{ $item->frequency ?? 'N/A' }}
                                @endif
                            </td>
                            <td>
                                @if($item->selectedAlternative)
                                    {{ $item->selectedAlternative->amount ?? 'N/A' }}
                                @else
                                    {{ $item->amount ?? 'N/A' }}
                                @endif
                            </td>
                        </tr>
                        @if($item->selectedAlternative)
                            <tr>
                                <td></td>
                                <td colspan="4" class="replaced-info">
                                    REPLACED: {{ $item->medicine->name ?? 'N/A' }} → {{ $item->selectedAlternative->medicine->name ?? 'N/A' }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="divider"></div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-info">
                <span class="bold">Dispensed by:</span> {{ $user->name ?? 'N/A' }}
            </div>
            <div class="footer-info">
                <span class="bold">Date:</span> {{ now()->format('Y-m-d H:i') }}
            </div>
            <div class="thank-you">Thank you for your visit!</div>
        </div>
    </div>

    <script>
        // Auto-print on page load
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
