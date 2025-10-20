@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <style>
        .sticky-sidebar { position: sticky; top: 90px; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .summary-item { border: 1px solid #e9ecef; border-radius: 8px; padding: 10px; background: #fff; }
        .summary-item .label { font-size: 12px; color: #6c757d; }
        .summary-item .value { font-weight: 600; }
        .table-results thead th { position: sticky; top: 0; background: #fff; z-index: 1; }
        .result-input { min-width: 140px; }
    </style>
    <div class="content-wrapper">
        <div class="row g-4">

            {{-- Left Sidebar: Patient + Test lists (sticky) --}}
            <div class="col-lg-4 order-2 order-lg-1">
                <div class="sticky-sidebar">
                    <div class="card p-0 shadow-sm mb-3">
                        <div class="card-header bg-dark">
                            <h5 class="mb-0 fw-bold text-white">{{ localize('global.patient_information') }}</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="summary-grid">
                                <div class="summary-item">
                                    <div class="label">{{ localize('global.name') }}</div>
                                    <div class="value">{{ $patient->name }} {{ $patient->last_name }}</div>
                                </div>
                                <div class="summary-item">
                                    <div class="label">{{ localize('global.father_name') }}</div>
                                    <div class="value">{{ $patient->father_name }}</div>
                                </div>
                                <div class="summary-item">
                                    <div class="label">{{ localize('global.age') }}</div>
                                    <div class="value">{{ $patient->age }}</div>
                                </div>
                                <div class="summary-item">
                                    <div class="label">{{ localize('global.phone') }}</div>
                                    <div class="value">{{ $patient->phone }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card p-0 shadow-sm mb-3">
                        <div class="card-header bg-warning">
                            <h6 class="mb-0 fw-bold text-dark">{{ localize('global.pending_tests') }}</h6>
                        </div>
                        <div class="card-body p-3">
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

                    <div class="card p-0 shadow-sm">
                        <div class="card-header bg-success">
                            <h6 class="mb-0 fw-bold text-white">{{ localize('global.completed_tests') }}</h6>
                        </div>
                        <div class="card-body p-3">
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

            

            {{-- Main: Results --}}
            <div class="col-lg-8 order-1 order-lg-2">
                <div class="card p-0 shadow-sm" id="resultCard">
                    <div class="card-header bg-dark text-white">
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

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="saveResults">{{ localize('global.save') }}</button>
                                <button type="button" class="btn btn-success" id="saveAndComplete">{{ localize('global.mark_completed') }}</button>
                                @if($firstTest?->ref_no)
                                <a href="{{ route('laboratory.reports.print', $firstTest->ref_no) }}" target="_blank" class="btn btn-outline-secondary">
                                    <i class="bx bx-printer"></i> {{ localize('global.print_report') }}
                                </a>
                                @endif
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

