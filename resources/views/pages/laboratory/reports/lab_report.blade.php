<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ localize('global.laboratory_test_report') }} - {{ $patient->name ?? 'Unknown' }}</title>
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
            font-family: 'ModFont', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
            background: white;
            direction: rtl;
            text-align: right;
        }

        @page {
            margin: 8mm;
            size: A4;
        }

        .report-container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
        }

        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }

        .header-grid {
            display: grid;
            grid-template-columns: 120px 1fr 120px;
            gap: 20px;
            align-items: center;
            min-height: 120px;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100px;
            height: 100px;
            position: relative;
        }

        .logo-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .text-column {
            padding: 10px;
            min-height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }

        .text-column h3 {
            color: #000;
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            padding-bottom: 5px;
        }

        .text-column p {
            color: #333;
            margin: 2px 0;
            font-size: 11px;
            line-height: 1.3;
            text-align: center;
        }

        .report-title {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }

        .report-title h1 {
            color: #000;
            margin: 0;
            font-size: 24px;
        }

        .report-title h2 {
            color: #333;
            margin: 5px 0 0 0;
            font-size: 16px;
            font-weight: normal;
        }

        .patient-info {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #000;
        }

        .patient-info h3 {
            margin: 0 0 10px 0;
            color: #000;
            font-size: 16px;
        }

        .patient-details {
            width: 100%;
            border-collapse: collapse;
        }

        .patient-details th,
        .patient-details td {
            border: 1px solid #000;
            padding: 2px;
            text-align: right;
        }

        .patient-details th {
            font-weight: bold;
            color: #000;
            white-space: nowrap;
        }

        .patient-details td {
            width: auto;
        }

        .test-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
            margin-top: 20px;
        }

        .test-header {
            color: #000;
            padding: 10px 15px;
            margin: 0;
            font-size: 14px;
            font-weight: bold;
        }

        .test-details {}

        .test-meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .test-meta th,
        .test-meta td {
            border: 1px solid #000;
            text-align: center;
            padding: 2px;
        }

        .test-meta th {
            font-weight: bold;
            color: #000;
        }

        .test-meta td {}

        .parameters-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            display: table;
        }

        .parameters-table thead {
            display: table-header-group;
        }

        .parameters-table tbody {
            display: table-row-group;
        }

        .parameters-table tr {
            display: table-row;
        }

        .parameters-table th,
        .parameters-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: right;
            display: table-cell;
            vertical-align: middle;
        }

        .parameters-table th {
            background: #f0f0f0;
            font-weight: bold;
            color: #000;
            width: 25%;
        }

        .parameters-table td {
            width: 25%;
        }

        .parameters-table tr:nth-child(even) {
            background: #f5f5f5;
        }

        .result-value {
            font-weight: bold;
            color: #000;
        }

        .normal-range {
            color: #000;
            font-size: 11px;
        }

        .unit {
            color: #333;
            font-size: 11px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #000;
            text-align: center;
            color: #000;
            font-size: 11px;
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

        .no-print {
            display: block;
        }

        @media print {
            body {
                margin: 0;
                padding: 15px;
            }

            .test-section {
                page-break-inside: avoid;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="report-container">
        <!-- Report Header -->
        <div class="header">
            <div class="header-grid">
                <!-- Left Logo -->
                <div class="logo-container logo-left">
                    <img src="{{ asset('images/logos/لوگو قومنداني.JPG') }}" alt="Left Logo" class="logo-image">
                </div>

                <!-- First Text Column (Arabic) -->
                <div class="text-column text-column-left">
                    <h2>امارت اسلامی افغانستان</h2>
                    <h4>وزارت دفاع ملی</h4>
                    <h4>ستـــــــــــــردرستیــــــــــــز</h4>
                    <h4>قوماندانیت صحیه</h4>
                    <h4>قوماندانی اکادمی علوم طبی</h4>
                <h4></h4>{{ auth()->user()->department?->name ?? '—' }}</h4>
                </div>


                <!-- Right Logo -->
                <div class="logo-container logo-right">
                    <img src="{{ asset('images/logos/لوگوی جدید وزارت دفاع ملی.png') }}" alt="Right Logo"
                        class="logo-image">
                </div>
            </div>
        </div>

        <!-- Patient Information -->
        @if($patient)
            <div class="">
                <table class="patient-details">
                    <tr>
                        <th>{{ localize('global.name') }}</th>
                        <td>{{ $patient->name }} {{ $patient->last_name }}</td>

                        <th>{{ localize('global.father_name') }}</th>
                        <td>{{ $patient->father_name ?? '—' }}</td>
                        <th>{{ localize('global.age') }}</th>
                        <td>{{ $patient->age ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>{{ localize('global.phone') }}</th>
                        <td>{{ $patient->phone ?? '—' }}</td>

                        <th>{{ localize('global.gender') }}</th>
                        <td>{{ $patient->gender ?? '—' }}</td>
                    </tr>
                    @if($patient->id_number)
                        <th>{{ localize('global.id_number') }}</th>
                        <td>{{ $patient->id_number }}</td>

                    @endif
                    @if($patient->date_of_birth)
                        <th>{{ localize('global.date_of_birth') }}</th>
                        <td>{{ \Verta($patient->date_of_birth)->formatJalaliDate() }}</td>
                    @endif
                    @if($patient->email)
                        <th>{{ localize('global.email') }}</th>
                        <td>{{ $patient->email }}</td>
                    @endif
                    @if($patient->emergency_contact)
                        <th>{{ localize('global.emergency_contact') }}</th>
                        <td>{{ $patient->emergency_contact }}</td>
                    @endif
                </table>
            </div>
        @endif

        <!-- Test Section -->
        <div class="test-section">
            <div class="test-details">
                <!-- Test Meta Information -->
                @if($testRegistration)
                    <table class="test-meta">
                        <tr>
                            <th colspan="6">{{ $testName ?? localize('global.test_name') }}</th>
                        </tr>
                        <tr>
                            <th>{{ localize('global.reference_number') }}</th>
                            <td>{{ $testRegistration->ref_no ?? '—' }}</td>

                            <th>{{ localize('global.doctor') }}</th>
                            <td>{{ $testRegistration->doctor->name ?? '—' }}</td>

                            <th>{{ localize('global.registration_date') }}</th>
                            <td>{{ $testRegistration->registration_date ? \Verta($testRegistration->registration_date)->formatJalaliDate() : '—' }}
                            </td>

                            @if($testRegistration->completed_at)

                                <th>{{ localize('global.completed_date') }}</th>
                                <td>{{ \Verta($testRegistration->completed_at)->formatJalaliDate() }}</td>

                            @endif
                        </tr>
                        <tr>
                            @if($testRegistration->assigned_to)
                                <th>{{ localize('global.assigned_to') }}</th>
                                <td>{{ $testRegistration->assignedTo->name ?? '—' }}</td>

                            @endif
                            @if($testRegistration->assigned_section_id)
                                <th>{{ localize('global.assigned_section') }}</th>
                                <td>{{ $testRegistration->assignedSection->name ?? '—' }}</td>
                            @endif
                            @if($testRegistration->labType && $testRegistration->labType->category)
                                <th>{{ localize('global.test_category') }}</th>
                                <td>{{ $testRegistration->labType->category->name ?? '—' }}</td>

                            @endif
                        </tr>
                    </table>
                @endif

                <!-- Test Parameters and Results -->
                @php
                    // Check if this is a parametered test by looking at the lab type
                    $hasParameters = $testRegistration && $testRegistration->labType && $testRegistration->labType->directLabTestParameters && $testRegistration->labType->directLabTestParameters->count() > 0;
                    $hasTextResult = $results->where('text_result', '!=', null)->count() > 0;
                @endphp

                @if($hasParameters)
                    {{-- Parametered test - show parameter table --}}
                    <table class="parameters-table">
                        <thead>
                            <tr>
                                <th style="text-align: center; direction: ltr;">Investigation</th>
                                <th style="text-align: center; direction: ltr;">Result</th>
                                <th style="text-align: center; direction: ltr;">Unit</th>
                                <th style="text-align: center; direction: ltr;">Reference Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $result)
                                @if($result->parameter)
                                    <tr>
                                        <td style="text-align: center;">{{ $result->parameter->parameter_name ?? '—' }}</td>
                                        <td class="result-value" style="text-align: center;">{{ $result->result ?? '—' }}</td>
                                        <td class="unit" style="text-align: center;">{{ $result->unit ?? '—' }}</td>
                                        <td class="normal-range" style="text-align: center;">{{ $result->normal_range ?? '—' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Show lab type parameters if no results yet --}}
                    @if($testRegistration && $testRegistration->labType && $testRegistration->labType->directLabTestParameters && $testRegistration->labType->directLabTestParameters->count() > 0 && $results->count() == 0)
                        <div
                            style="margin-top: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
                            <h4 style="margin-bottom: 15px; color: #333;">{{ localize('global.expected_parameters') }}</h4>
                            <table class="parameters-table">
                                <thead>
                                    <tr>
                                        <th style="text-align: center; direction: ltr;">Investigation</th>
                                        <th style="text-align: center; direction: ltr;">Unit</th>
                                        <th style="text-align: center; direction: ltr;">Reference Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($testRegistration->labType->directLabTestParameters as $parameter)
                                        <tr>
                                            <td style="text-align: center;">{{ $parameter->parameter_name ?? '—' }}</td>
                                            <td class="unit" style="text-align: center;">{{ $parameter->unit ?? '—' }}</td>
                                            <td class="normal-range" style="text-align: center;">
                                                {{ $parameter->normal_range ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @elseif($hasTextResult)
                    {{-- Non-parametered test - show text result --}}
                    <div class="text-result-section">
                        <h4 style="margin-bottom: 15px; color: #333;">{{ localize('global.test_result') }}</h4>
                        <div
                            style="background: white; border: 1px solid #ccc; border-radius: 3px; white-space: pre-wrap; direction: ltr; text-align: left;">
                            {!! $results->where('text_result', '!=', null)->first()->text_result ?? localize('global.no_result_available') !!}
                        </div>
                    </div>
                @else
                    {{-- No results available --}}
                    <div style="text-align: center; padding: 20px; color: #6c757d; direction: rtl;">
                        {{ localize('global.no_results_available') }}
                    </div>
                @endif
            </div>
        </div>


        <!-- Footer -->
        <div class="footer">
            <p>{{ localize('global.report_generated_on') }}:
                {{ \Morilog\Jalali\Jalalian::now()->format('Y/m/d H:i:s') }}</p>
            <p>{{ localize('global.laboratory_system') }}</p>

            <div class="no-print">
                <button class="print-button" onclick="window.print()">
                    {{ localize('global.print_report') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function () {
            window.print();
        }
    </script>
</body>

</html>