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
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        
        .header h2 {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 16px;
            font-weight: normal;
        }
        
        .patient-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        
        .patient-info h3 {
            margin: 0 0 10px 0;
            color: #007bff;
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
            color: #495057;
        }
        
        .test-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .category-header {
            page-break-after: avoid;
        }
        
        .test-header {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            margin: 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .test-details {
            border: 1px solid #dee2e6;
            border-top: none;
            padding: 15px;
        }
        
        .test-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 3px;
        }
        
        .test-meta div {
            display: flex;
            justify-content: space-between;
        }
        
        .test-meta strong {
            color: #495057;
        }
        
        .parameters-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .parameters-table th,
        .parameters-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: right;
        }
        
        .parameters-table th {
            background: #e9ecef;
            font-weight: bold;
            color: #495057;
        }
        
        .parameters-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .result-value {
            font-weight: bold;
            color: #007bff;
        }
        
        .normal-range {
            color: #28a745;
            font-size: 11px;
        }
        
        .unit {
            color: #6c757d;
            font-size: 11px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 11px;
        }
        
        .group-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 20px;
        }
        
        .group-info h4 {
            margin: 0 0 5px 0;
            color: #856404;
            font-size: 14px;
        }
        
        .ref-numbers {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .ref-number {
            background: #007bff;
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
    {{-- Header --}}
    <div class="header">
        <h1>{{ localize('global.laboratory_test_report') }}</h1>
        <h2>{{ localize('global.grouped_test_results') }}</h2>
    </div>

    {{-- Group Information --}}
    <div class="group-info">
        <h4>{{ localize('global.test_group') }} #{{ $category_id }}</h4>
        <div class="ref-numbers">
            @foreach($testRegistrations as $test)
                <span class="ref-number">{{ $test->ref_no }}</span>
            @endforeach
        </div>
    </div>

    {{-- Patient Information --}}
    @if($patient)
        <div class="patient-info">
            <h3>{{ localize('global.patient_information') }}</h3>
            <div class="patient-details">
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
            </div>
        </div>
    @endif

    {{-- Test Results Grouped by Lab Test Category --}}
    @foreach($testsByLabCategory as $labCategoryId => $testsInCategory)
        @php
            $labCategory = $testsInCategory->first()->labTest->category ?? null;
            $categoryName = $labCategory ? $labCategory->name : 'Uncategorized';
        @endphp
        
        {{-- Category Header --}}
        <div class="category-header" style="background: #007bff; color: white; padding: 15px; margin: 20px 0 10px 0; border-radius: 5px;">
            <h3 style="margin: 0; font-size: 18px;">
                <i class="bx bx-category" style="margin-left: 8px;"></i>
                {{ $categoryName }}
            </h3>
        </div>

        {{-- Tests in this category --}}
        @foreach($testsInCategory as $testRegistration)
            <div class="test-section">
                <h3 class="test-header">
                    {{ $testRegistration->labTest->name ?? localize('global.test_name') }}
                </h3>
                
                <div class="test-details">
                    {{-- Test Meta Information --}}
                    <div class="test-meta">
                        <div>
                            <strong>{{ localize('global.reference_number') }}:</strong>
                            <span>{{ $testRegistration->ref_no }}</span>
                        </div>
                        <div>
                            <strong>{{ localize('global.status') }}:</strong>
                            <span>{{ ucfirst($testRegistration->status) }}</span>
                        </div>
                        <div>
                            <strong>{{ localize('global.priority') }}:</strong>
                            <span>{{ ucfirst($testRegistration->priority) }}</span>
                        </div>
                        <div>
                            <strong>{{ localize('global.doctor') }}:</strong>
                            <span>{{ $testRegistration->doctor->name ?? '—' }}</span>
                        </div>
                        <div>
                            <strong>{{ localize('global.registration_date') }}:</strong>
                            <span>{{ $testRegistration->registration_date->format('Y-m-d H:i') }}</span>
                        </div>
                        @if($testRegistration->completed_at)
                            <div>
                                <strong>{{ localize('global.completed_date') }}:</strong>
                                <span>{{ $testRegistration->completed_at->format('Y-m-d H:i') }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Test Parameters and Results --}}
                    @if(isset($groupedResults[$testRegistration->id]) && $groupedResults[$testRegistration->id]->count() > 0)
                        <table class="parameters-table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.parameter') }}</th>
                                    <th>{{ localize('global.result') }}</th>
                                    <th>{{ localize('global.unit') }}</th>
                                    <th>{{ localize('global.normal_range') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedResults[$testRegistration->id] as $result)
                                    <tr>
                                        <td>{{ $result->parameter->parameter_name ?? '—' }}</td>
                                        <td class="result-value">{{ $result->result ?? '—' }}</td>
                                        <td class="unit">{{ $result->unit ?? '—' }}</td>
                                        <td class="normal-range">{{ $result->normal_range ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="text-align: center; padding: 20px; color: #6c757d;">
                            {{ localize('global.no_results_available') }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        {{-- Page break after each category (except the last one) --}}
        @if(!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach

    {{-- Footer --}}
    <div class="footer">
        <p>{{ localize('global.report_generated_on') }}: {{ now()->format('Y-m-d H:i:s') }}</p>
        <p>{{ localize('global.laboratory_system') }}</p>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
