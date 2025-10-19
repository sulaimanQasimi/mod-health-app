<!DOCTYPE html>
<html lang="fa" dir="rtl">
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
            font-family: 'Tahoma', 'Arial', sans-serif;
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto;
            padding: 5px;
            font-size: 9px;
            line-height: 1.2;
            color: #000;
            background: white;
            direction: rtl;
            text-align: right;
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
            direction: rtl;
        }

        .patient-info {
            margin-bottom: 8px;
            font-size: 8px;
        }

        .patient-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            direction: rtl;
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
            text-align: right;
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
            <div class="pharmacy-name">{{ $pharmacy->name ?? 'دواخانه' }}</div>
            <div class="pharmacy-info">
                @if($pharmacy)
                    <div>تلفن: {{ $pharmacy->phone ?? 'نامشخص' }}</div>
                    <div>آدرس: {{ $pharmacy->address ?? 'نامشخص' }}</div>
                    <div>شناسه دواخانه: {{ $pharmacy->id ?? 'نامشخص' }}</div>
                    @if($pharmacy->created_at)
                        <div>تاریخ تاسیس: {{ \Hekmatinasser\Verta\Verta::instance($pharmacy->created_at)->format('Y/n/j') }}</div>
                    @endif
                @else
                    <div>اطلاعات دواخانه در دسترس نیست</div>
                @endif
            </div>
        </div>

        <div class="divider"></div>

        <!-- Receipt Info -->
        <div class="receipt-info">
            <span>تاریخ: {{ \Hekmatinasser\Verta\Verta::instance($prescription->created_at)->format('Y/n/j') }}</span>
            <span class="bold">نسخه شماره: {{ $prescription->id }}</span>
        </div>

        <!-- Patient Info -->
        <div class="patient-info">
            <div class="patient-row">
                <span class="bold">بیمار: {{ $prescription->patient->name ?? 'نامشخص' }}</span>
            </div>
            <div class="patient-row">
                <span>سن: {{ $prescription->patient->age ?? 'نامشخص' }}</span>
                <span>شناسه: {{ $prescription->patient->id_card ?? 'نامشخص' }}</span>
            </div>
        </div>

        <!-- Doctor Info -->
        <div class="doctor-info">
            <span class="bold">داکتر: {{ $prescription->doctor->name ?? 'نامشخص' }}</span>
        </div>

        <div class="divider"></div>

        <!-- Medicines Section -->
        <div class="medicines-section">
            <div class="medicines-header">ادویه ها:</div>
            <table class="medicine-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">#</th>
                        <th style="width: 45%;">ادویه</th>
                        <th style="width: 15%;">مقدار</th>
                        <th style="width: 12%;">تکرار</th>
                        <th style="width: 20%;">تعداد</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prescription->prescriptionItems as $index => $item)
                        <tr class="medicine-row">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if($item->selectedAlternative)
                                    {{ $item->selectedAlternative->medicine->name ?? 'نامشخص' }}
                                @else
                                    {{ $item->medicine->name ?? 'نامشخص' }}
                                @endif
                            </td>
                            <td>
                                @if($item->selectedAlternative)
                                    {{ $item->selectedAlternative->dosage ?? 'نامشخص' }}
                                @else
                                    {{ $item->dosage ?? 'نامشخص' }}
                                @endif
                            </td>
                            <td>
                                @if($item->selectedAlternative)
                                    {{ $item->selectedAlternative->frequency ?? 'نامشخص' }}
                                @else
                                    {{ $item->frequency ?? 'نامشخص' }}
                                @endif
                            </td>
                            <td>
                                @if($item->selectedAlternative)
                                    {{ $item->selectedAlternative->amount ?? 'نامشخص' }}
                                @else
                                    {{ $item->amount ?? 'نامشخص' }}
                                @endif
                            </td>
                        </tr>
                        @if($item->selectedAlternative)
                            <tr>
                                <td></td>
                                <td colspan="4" class="replaced-info">
                                    جایگزین: {{ $item->medicine->name ?? 'نامشخص' }} → {{ $item->selectedAlternative->medicine->name ?? 'نامشخص' }}
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
                <span class="bold">تحویل دهنده:</span> {{ $user->name ?? 'نامشخص' }}
            </div>
            @if($pharmacy)
                <div class="footer-info">
                    <span class="bold">دواخانه:</span> {{ $pharmacy->name ?? 'نامشخص' }}
                </div>
            @endif
            <div class="footer-info">
                <span class="bold">تاریخ:</span> {{ \Hekmatinasser\Verta\Verta::now()->format('Y/n/j H:i') }}
            </div>
            <div class="thank-you">از مراجعه شما متشکریم!</div>
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
