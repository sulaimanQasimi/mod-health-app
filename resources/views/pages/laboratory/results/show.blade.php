@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <style>
        .sticky-sidebar { position: sticky; top: 90px; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .summary-item { border: 1px solid #e9ecef; border-radius: 8px; padding: 10px; background: #fff; }
        .summary-item .label { font-size: 12px; color: #6c757d; }
        .summary-item .value { font-weight: 600; }
        .table-results thead th { 
            position: sticky; 
            top: 0; 
            background: #fff; 
            z-index: 1; 
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #495057;
            border-bottom: 3px solid #6f42c1;
            padding: 15px 12px;
        }
        .result-input { 
            min-width: 140px; 
            border: 2px solid #e9ecef;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .result-input:focus {
            border-color: #6f42c1;
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
            outline: none;
        }
        .table-results tbody tr {
            transition: all 0.2s ease;
        }
        .table-results tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
        }
        .table-results tbody td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }
        .table-results tbody td strong {
            color: #2c3e50;
            font-size: 14px;
        }
        .table-results tbody td .text-muted {
            color: #6c757d;
            font-size: 13px;
        }
        .results-actions {
            background: #6f42c1;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .results-actions .btn {
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 13px;
        }
        .results-actions .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            box-shadow: 0 4px 8px rgba(0,123,255,0.3);
        }
        .results-actions .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,123,255,0.4);
        }
        .results-actions .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            border: none;
            box-shadow: 0 4px 8px rgba(40,167,69,0.3);
        }
        .results-actions .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(40,167,69,0.4);
        }
        .results-actions .btn-outline-secondary {
            color: #6c757d;
            background:rgb(255, 255, 255);
        }
        .results-actions .btn-outline-secondary:hover {
            background: #6c757d;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(108,117,125,0.3);
        }
        .table-results {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        @media (prefers-color-scheme: dark) {
            .table-results thead th {
                background: #2d2d2d;
                color: #e0e0e0;
                border-bottom-color: #6f42c1;
            }
            .table-results tbody tr:hover {
                background-color: #3d3d3d;
            }
            .table-results tbody td strong {
                color: #e0e0e0;
            }
        }
        .test-section-header { 
            font-size: 13px; 
            font-weight: 700; 
            margin-bottom: 12px; 
            padding: 10px 14px; 
            border-radius: 8px; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        .test-section-header i {
            font-size: 18px;
        }
        .test-section-header.pending { 
            background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%); 
            color: #fff; 
            border: none;
        }
        .test-section-header.completed { 
            background: linear-gradient(135deg, #198754 0%, #157347 100%); 
            color: #fff; 
            border: none;
        }
        .test-list-container { 
            max-height: 320px; 
            overflow-y: auto; 
            border: 2px solid #e9ecef; 
            border-radius: 8px; 
            background: #fff; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .test-list-container::-webkit-scrollbar {
            width: 6px;
        }
        .test-list-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .test-list-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        .test-list-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        .test-list-container .list-group-item { 
            border: none; 
            border-bottom: 1px solid #f0f0f0; 
            padding: 12px 14px; 
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .test-list-container .list-group-item:hover {
            background-color: #f8f9fa;
            transform: translateX(2px);
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .test-list-container .list-group-item:last-child { 
            border-bottom: none; 
        }
        .test-list-container .list-group-item strong {
            color: #2c3e50;
            font-size: 14px;
        }
        .test-list-container .list-group-item small {
            font-size: 11px;
        }
        .test-list-container .list-group-item .badge {
            font-size: 10px;
            padding: 4px 8px;
            font-weight: 600;
        }
        .test-list-container .list-group-item .btn-sm {
            padding: 4px 8px;
            transition: all 0.2s ease;
        }
        .test-list-container .list-group-item .btn-sm:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        .test-column { 
            padding: 0 8px;
        }
        .test-column:first-child {
            padding-right: 12px;
            border-right: 2px solid #e9ecef;
        }
        .test-column:last-child { 
            padding-left: 12px;
        }
        .test-list-container .list-group {
            margin-bottom: 0;
        }
        @media (prefers-color-scheme: dark) {
            .test-list-container {
                background: #1a1a1a;
                border-color: #333;
            }
            .test-list-container .list-group-item {
                background: #1a1a1a;
                border-bottom-color: #2d2d2d;
            }
            .test-list-container .list-group-item:hover {
                background-color: #2d2d2d;
            }
            .test-list-container .list-group-item strong {
                color: #e0e0e0;
            }
        }
        .patient-info-table tbody tr {
            border-bottom: 1px solid #e9ecef;
        }
        .patient-info-table tbody tr:last-child {
            border-bottom: none;
        }
        .patient-info-table .label-col {
            font-size: 14px;
            color: #6c757d;
            font-weight: 500;
            width: 40%;
            padding: 12px 0;
        }
        .patient-info-table .value-col {
            font-size: 14px;
            font-weight: 600;
            color: #212529;
            padding: 12px 0;
        }
    </style>
    <div class="content-wrapper">
        <div class="row g-4">

            {{-- Left Sidebar: Patient + Test lists (sticky) --}}
            <div class="col-lg-4 order-2 order-lg-1">
                <div class="sticky-sidebar">
                    <div class="card p-0 shadow-sm">
                        <div class="card-header" style="background-color: #6f42c1;">
                            <h5 class="mb-0 fw-bold text-white">
                                <i class="bx bx-user"></i>
                                {{ localize('global.patient_information') }}
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <table class="table table-borderless patient-info-table mb-0">
                                <tbody>
                                    <tr>
                                        <td class="label-col">{{ localize('global.name') }}</td>
                                        <td class="value-col text-muted">{{ $patient->name }} {{ $patient->last_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label-col">{{ localize('global.father_name') }}</td>
                                        <td class="value-col text-muted">{{ $patient->father_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label-col">{{ localize('global.age') }}</td>
                                        <td class="value-col text-muted">{{ $patient->age }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label-col">{{ localize('global.phone') }}</td>
                                        <td class="value-col text-muted">{{ $patient->phone }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <hr class="my-3">
                            
                            <div class="row g-3">
                                <div class="col-6 test-column">
                                    <div class="test-section-header pending">
                                        <i class="bx bx-time-five"></i>
                                        {{ localize('global.pending_tests') }}
                                    </div>
                                    <div class="test-list-container">
                                        <ul id="pendingTests" class="list-group">
                                            @foreach ($pendingTests as $test)
                                            <li class="list-group-item test-item cursor-pointer d-flex justify-content-between align-items-center" data-id="{{ $test->id }}">
                                                <span>
                                                    <strong>{{ $test->labTest->name ?? '—' }}</strong>
                                                    <small class="text-muted">({{ $test->ref_no }})</small>
                                                </span>
                                                <span class="badge bg-warning">{{ localize('global.pending') }}</span>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-6 test-column">
                                    <div class="test-section-header completed">
                                        <i class="bx bx-check-circle"></i>
                                        {{ localize('global.completed_tests') }}
                                    </div>
                                    <div class="test-list-container">
                                        <ul id="completedTests" class="list-group">
                                            @foreach($completedTests as $test)
                                            <li class="list-group-item d-flex justify-content-between align-items-center test-card cursor-pointer" data-id="{{ $test->id }}">
                                                <span>
                                                    <strong>{{ $test->labTest->name ?? '—' }}</strong><br>
                                                    <small>{{ localize('global.reference_number') }}: {{ $test->ref_no }}</small>
                                                </span>
                                                <a href="{{ route('laboratory.reports.print', $test->ref_no) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                    <i class="bx bx-printer"></i>
                                                </a>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            

            {{-- Main: Results --}}
            <div class="col-lg-8 order-1 order-lg-2">
                <div class="card p-0 shadow-sm" id="resultCard">
                    <div class="card-header" style="background-color: #6f42c1;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">{{ $firstTest?->labTest?->name ?? localize('global.test_results') }}</h5>
                            <div class="text-end">
                                <div class="small">{{ localize('global.reference_number') }}: <strong id="headerRef">{{ $firstTest->ref_no ?? '—' }}</strong></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <form id="resultForm" action="{{ route('laboratory.results.update') }}" method="post">
                            @csrf
                            <input type="hidden" id="test_registration_id" name="test_registration_id"
                            value="{{ $firstTest->id ?? '' }}">
                            <input type="hidden" id="ref_no" name="ref_no" value="{{ $firstTest->ref_no ?? '' }}">
                            <input type="hidden" id="complete_flag" name="complete" value="0">
                            
                            <table class="table table-bordered table-results" id="resultTable">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.parameter_name') }}</th>
                                        <th>{{ localize('global.result') }}</th>
                                        <th>{{ localize('global.unit') }}</th>
                                        <th>{{ localize('global.normal_range') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($firstTestResults && $firstTestResults->count() > 0)
                                        @foreach($firstTestResults as $result)
                                        <tr>
                                            <td>
                                                <strong>{{ $result->parameter->parameter_name ?? '—' }}</strong>
                                            </td>
                                            <td>
                                                <input type="text" 
                                                       name="results[{{ $result->parameter->id ?? '' }}]" 
                                                       value="{{ $result->result ?? '' }}" 
                                                       class="form-control result-input">
                                            </td>
                                            <td><span class="text-muted">{{ $result->unit ?? '—' }}</span></td>
                                            <td><span class="text-muted">{{ $result->normal_range ?? '—' }}</span></td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center">
                                                @if($firstTest)
                                                    No parameters found for this test
                                                @else
                                                    No test selected
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>

                            <div class="results-actions">
                                <div class="d-flex gap-3 justify-content-center">
                                    <button type="submit" class="btn btn-primary" id="saveResults">
                                        <i class="bx bx-save"></i> {{ localize('global.save') }}
                                    </button>
                                    <button type="button" class="btn btn-success" id="saveAndComplete">
                                        <i class="bx bx-check-double"></i> {{ localize('global.mark_completed') }}
                                    </button>
                                    @if($firstTest?->ref_no)
                                    <a href="{{ route('laboratory.reports.print', $firstTest->ref_no) }}" target="_blank" class="btn btn-outline-secondary">
                                        <i class="bx bx-printer"></i> {{ localize('global.print_report') }}
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Handle test selection
    document.querySelectorAll('.test-item, .test-card').forEach(item => {
        item.addEventListener('click', function() {
            const testId = this.getAttribute('data-id');
            loadTestResults(testId);
        });
    });

    function loadTestResults(testId) {
        fetch(`/laboratory/results/load/${testId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    updateResultTable(data.test, data.results);
                } else {
                    alert('Error loading test results: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading test results');
            });
    }

    function updateResultTable(test, results) {
        // Update hidden inputs
        document.getElementById('test_registration_id').value = test.id;
        document.getElementById('ref_no').value = test.ref_no;
        const headerRef = document.getElementById('headerRef');
        if (headerRef) headerRef.textContent = test.ref_no || '—';

        // Update table body
        const tbody = document.querySelector('#resultTable tbody');
        tbody.innerHTML = '';

        if (results.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center">No parameters found</td></tr>';
            return;
        }

        results.forEach(result => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><strong>${result.parameter.parameter_name || '—'}</strong></td>
                <td>
                    <input type="text" 
                           name="results[${result.parameter.id}]" 
                           value="${result.result || ''}" 
                           class="form-control result-input">
                </td>
                <td><span class="text-muted">${result.unit || '—'}</span></td>
                <td><span class="text-muted">${result.normal_range || '—'}</span></td>
            `;
            tbody.appendChild(row);
        });
    }

    // Handle form submission
    document.getElementById('resultForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                toastr && toastr.success ? toastr.success('Results updated successfully!') : alert('Results updated successfully!');
                if (data.completed) {
                    toastr && toastr.success ? toastr.success('Test completed!') : alert('Test completed!');
                }
            } else {
                toastr && toastr.error ? toastr.error('Error: ' + data.message) : alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr && toastr.error ? toastr.error('Error updating results') : alert('Error updating results');
        });
    });

    // Save & Complete button
    const saveAndCompleteBtn = document.getElementById('saveAndComplete');
    if (saveAndCompleteBtn) {
        saveAndCompleteBtn.addEventListener('click', function() {
            const completeField = document.getElementById('complete_flag');
            if (completeField) completeField.value = '1';
            document.getElementById('resultForm').requestSubmit();
            setTimeout(() => { if (completeField) completeField.value = '0'; }, 1000);
        });
    }
</script>
@endsection

