<!DOCTYPE html>
<html lang="en" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ localize('global.grouped_test_report') }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #000;
            margin: 0;
            font-size: 24px;
        }
        
        .header h2 {
            color: #333;
            margin: 5px 0 0 0;
            font-size: 16px;
            font-weight: normal;
        }
        
        .patient-info {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #000;
        }
        
        .patient-info h3 {
            margin: 0 0 10px 0;
            color: #000;
            font-size: 16px;
        }
        
        .patient-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        
        .patient-details div {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }
        
        .patient-details strong {
            color: #000;
        }
        
        .test-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .category-header {
            page-break-after: avoid;
        }
        
        .page-header {
            page-break-before: always;
        }
        
        .page-header:first-child {
            page-break-before: auto;
        }
        
        .test-header {
            background: #000;
            color: white;
            padding: 10px 15px;
            margin: 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .test-details {
            border: 1px solid #000;
            border-top: none;
            padding: 15px;
        }
        
        .test-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 3px;
            border: 1px solid #ccc;
        }
        
        .test-meta div {
            display: flex;
            justify-content: space-between;
        }
        
        .test-meta strong {
            color: #000;
        }
        
        .parameters-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .parameters-table th,
        .parameters-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: right;
        }
        
        .parameters-table th {
            background: #f0f0f0;
            font-weight: bold;
            color: #000;
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
        
        .group-info {
            background: #f5f5f5;
            border: 1px solid #000;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 20px;
        }
        
        .group-info h4 {
            margin: 0 0 5px 0;
            color: #000;
            font-size: 14px;
        }
        
        .ref-numbers {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .ref-number {
            background: #000;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .test-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    {{-- Test Results Grouped by Lab Test Category --}}
    @if($testsByLabCategory->count() > 0)
        @foreach($testsByLabCategory as $labCategoryId => $testsInCategory)
        @php
            $firstTest = $testsInCategory->first();
            $labCategory = $firstTest && $firstTest->labType ? $firstTest->labType->category : null;
            $categoryName = $labCategory ? $labCategory->name : 'Uncategorized';
        @endphp
        
        {{-- Page Header for each category --}}
        <div class="page-header" style="page-break-before: always;">
            {{-- Report Header --}}
            <div class="header">
                <h1>{{ localize('global.laboratory_test_report') }}</h1>
                <h2>{{ localize('global.grouped_test_results') }} - {{ $categoryName }}</h2>
            </div>

            {{-- Patient Information --}}
            @if($patient)
                <div class="patient-info">
                    <h3>{{ localize('global.patient_information') }}</h3>
                    <div class="patient-details"></div></div>
                        <div>
                            <strong>{{ localize('global.name') }}:</strong>
                            <span>{{ $patient->name }} {{ $patient->last_name }}</span>
                        </div>
                        <div>
                            <strong>{{ localize('global.father_name') }}:</strong>
                            <span>{{ $patient->father_name ?? '—' }}</span>
                        </div>
                        <div>
                            <strong>{{ localize('global.age') }}:</strong>
                            <span>{{ $patient->age ?? '—' }}</span>
                        </div>
                        <div>
                            <strong>{{ localize('global.phone') }}:</strong>
                            <span>{{ $patient->phone ?? '—' }}</span>
                        </div>
                        <div>
                            <strong>{{ localize('global.gender') }}:</strong>
                            <span>{{ $patient->gender ?? '—' }}</span>
                        </div>
                        <div>
                            <strong>{{ localize('global.address') }}:</strong>
                            <span>{{ $patient->address ?? '—' }}</span>
                        </div>
                        @if($patient->id_number)
                            <div>
                                <strong>{{ localize('global.id_number') }}:</strong>
                                <span>{{ $patient->id_number }}</span>
                            </div>
                        @endif
                        @if($patient->date_of_birth)
                            <div>
                                <strong>{{ localize('global.date_of_birth') }}:</strong>
                                <span>{{ \Verta($patient->date_of_birth)->formatJalaliDate() }}</span>
                            </div>
                        @endif
                        @if($patient->email)
                            <div>
                                <strong>{{ localize('global.email') }}:</strong>
                                <span>{{ $patient->email }}</span>
                            </div>
                        @endif
                        @if($patient->emergency_contact)
                            <div>
                                <strong>{{ localize('global.emergency_contact') }}:</strong>
                                <span>{{ $patient->emergency_contact }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Tests in this category --}}
        @foreach($testsInCategory as $testRegistration)
            <div class="test-section">
                <h3 class="test-header">
                    {{ $testRegistration->labType ? $testRegistration->labType->name : localize('global.test_name') }}
                </h3>
                
                <div class="test-details">
                    {{-- Test Meta Information --}}
                    <div class="test-meta">
                        <div>
                            <strong>{{ localize('global.reference_number') }}:</strong>
                            <span>{{ $testRegistration->ref_no ?? '—' }}</span>
                        </div>
                        <div>
                            <strong>{{ localize('global.status') }}:</strong>
                            <span>{{ ucfirst($testRegistration->status ?? '—') }}</span>
                        </div>
                        <div>
                            <strong>{{ localize('global.priority') }}:</strong>
                            <span>{{ ucfirst($testRegistration->priority ?? '—') }}</span>
                        </div>
                        <div>
                            <strong>{{ localize('global.doctor') }}:</strong>
                            <span>{{ $testRegistration->doctor->name ?? '—' }}</span>
                        </div>
                        <div>
                            <strong>{{ localize('global.registration_date') }}:</strong>
                            <span>{{ $testRegistration->registration_date ? \Verta($testRegistration->registration_date)->formatJalaliDate() : '—' }}</span>
                        </div>
                        @if($testRegistration->completed_at)
                            <div>
                                <strong>{{ localize('global.completed_date') }}:</strong>
                                <span>{{ \Verta($testRegistration->completed_at)->formatJalaliDate() }}</span>
                            </div>
                        @endif
                        @if($testRegistration->assigned_to)
                            <div>
                                <strong>{{ localize('global.assigned_to') }}:</strong>
                                <span>{{ $testRegistration->assignedTo->name ?? '—' }}</span>
                            </div>
                        @endif
                        @if($testRegistration->assigned_section_id)
                            <div>
                                <strong>{{ localize('global.assigned_section') }}:</strong>
                                <span>{{ $testRegistration->assignedSection->name ?? '—' }}</span>
                            </div>
                        @endif
                        @if($testRegistration->assigned_at)
                            <div>
                                <strong>{{ localize('global.assigned_date') }}:</strong>
                                <span>{{ \Verta($testRegistration->assigned_at)->formatJalaliDate() }}</span>
                            </div>
                        @endif
                        @if($testRegistration->notes)
                            <div style="grid-column: 1 / -1; margin-top: 10px;">
                                <strong>{{ localize('global.notes') }}:</strong>
                                <div style="background: white; padding: 10px; border: 1px solid #ccc; border-radius: 3px; margin-top: 5px; direction: ltr; text-align: left;">
                                    {!! $testRegistration->notes !!}
                                </div>
                            </div>
                        @endif
                        @if($testRegistration->detailed_notes)
                            <div style="grid-column: 1 / -1; margin-top: 10px;">
                                <strong>{{ localize('global.detailed_notes') }}:</strong>
                                <div style="background: white; padding: 10px; border: 1px solid #ccc; border-radius: 3px; margin-top: 5px; direction: ltr; text-align: left;">
                                    {!! $testRegistration->detailed_notes !!}
                                </div>
                            </div>
                        @endif
                        @if($testRegistration->labType && $testRegistration->labType->category)
                            <div>
                                <strong>{{ localize('global.test_category') }}:</strong>
                                <span>{{ $testRegistration->labType->category->name ?? '—' }}</span>
                            </div>
                        @endif
                        @if($testRegistration->branch)
                            <div>
                                <strong>{{ localize('global.branch') }}:</strong>
                                <span>{{ $testRegistration->branch->name ?? '—' }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Test Parameters and Results --}}
                    @php
                        $testResults = $groupedResults[$testRegistration->id] ?? collect();
                        $hasParameters = $testRegistration->labType && $testRegistration->labType->directLabTestParameters && $testRegistration->labType->directLabTestParameters->count() > 0;
                        $hasTextResult = $testResults->where('text_result', '!=', null)->count() > 0;
                        $hasParameterResults = $testResults->where('parameter', '!=', null)->count() > 0;
                    @endphp
                    
                    @if($hasParameters && $hasParameterResults)
                        {{-- Parametered test - show parameter table --}}
                        <table class="parameters-table">
                            <thead>
                                <tr>
                                    <th style="direction: ltr;">Investigation</th>
                                    <th style="direction: ltr;">Result</th>
                                    <th style="direction: ltr;">Unit</th>
                                    <th style="direction: ltr;">Reference Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($testResults as $result)
                                    @if($result->parameter)
                                        <tr>
                                            <td>{{ $result->parameter->parameter_name ?? '—' }}</td>
                                            <td class="result-value">{{ $result->result ?? '—' }}</td>
                                            <td class="unit">{{ $result->unit ?? '—' }}</td>
                                            <td class="normal-range">{{ $result->normal_range ?? '—' }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @elseif($hasParameters && !$hasParameterResults)
                        {{-- Show expected parameters if no results yet --}}
                        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
                            <h4 style="margin-bottom: 15px; color: #333;">{{ localize('global.expected_parameters') }}</h4>
                            <table class="parameters-table">
                                <thead>
                                    <tr>
                                        <th style="direction: ltr;">Investigation</th>
                                        <th style="direction: ltr;">Unit</th>
                                        <th style="direction: ltr;">Reference Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($testRegistration->labType->directLabTestParameters as $parameter)
                                        <tr>
                                            <td>{{ $parameter->parameter_name ?? '—' }}</td>
                                            <td class="unit">{{ $parameter->unit ?? '—' }}</td>
                                            <td class="normal-range">{{ $parameter->normal_range ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($hasTextResult)
                        {{-- Non-parametered test - show text result --}}
                        <div class="text-result-section" style="background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; border-radius: 5px; margin: 20px 0;">
                            <h4 style="margin-bottom: 15px; color: #333;">{{ localize('global.test_result') }}</h4>
                            <div style="background: white; padding: 15px; border: 1px solid #ccc; border-radius: 3px; min-height: 100px; white-space: pre-wrap; direction: ltr; text-align: left;">
                                {!! $testResults->where('text_result', '!=', null)->first()->text_result ?? localize('global.no_result_available') !!}
                            </div>
                        </div>
                    @else
                        {{-- No results available --}}
                        <div style="text-align: center; padding: 20px; color: #6c757d;">
                            {{ localize('global.no_results_available') }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        {{-- Footer for each category page --}}
        <div class="footer">
            <p>{{ localize('global.report_generated_on') }}: {{ verta()->format('Y-m-d H:i:s') }}</p>
            <p>{{ localize('global.laboratory_system') }}</p>
        </div>
        @endforeach
    @else
        <div style="text-align: center; padding: 50px; color: #6c757d;">
            <h3>{{ localize('global.no_tests_found') }}</h3>
            <p>{{ localize('global.no_tests_found_description') }}</p>
        </div>
    @endif

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
