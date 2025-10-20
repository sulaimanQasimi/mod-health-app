@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="row g-4">

            {{-- Patient Info --}}
            <div class="col-md-4">
                <div class="card p-0 shadow-sm">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h4 class="mb-0 fw-bold text-white">{{ localize('global.patient_information') }}</h4>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>{{ localize('global.name') }}:</strong> {{ $patient->name }} {{ $patient->last_name }}</p>
                                <p><strong>{{ localize('global.father_name') }}:</strong> {{ $patient->father_name }}</p>
                                <p><strong>{{ localize('global.age') }}:</strong> {{ $patient->age }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>{{ localize('global.phone') }}:</strong> {{ $patient->phone }}</p>
                                <p><strong>{{ localize('global.job') }}:</strong> {{ $patient->job ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pending Tests --}}
            <div class="col-md-4">
                <div class="card p-0 shadow-sm">
                    <div class="card-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <h5 class="mb-0 fw-bold text-white">{{ localize('global.pending_tests') }}</h5>
                    </div>
                    <div class="card-body p-3">
                        <ul id="pendingTests" class="list-group">
                            @foreach ($pendingTests as $test)
                            <li class="list-group-item test-item cursor-pointer" data-id="{{ $test->id }}">
                                <strong>{{ $test->labTest->name ?? '—' }}</strong>
                                <small class="text-muted">({{ $test->ref_no }})</small>
                                <span class="badge bg-warning float-end">{{ localize('global.pending') }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Completed Tests --}}
            <div class="col-md-4">
                <div class="card p-0 shadow-sm">
                    <div class="card-header" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <h5 class="mb-0 fw-bold text-white">{{ localize('global.completed_tests') }}</h5>
                    </div>
                    <div class="card-body p-3">
                        <ul id="completedTests" class="list-group">
                            @foreach($completedTests as $test)
                            <div class="card mb-2 test-card cursor-pointer" data-id="{{ $test->id }}">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $test->labTest->name ?? 'Unknown Test' }}</strong><br>
                                        <small>Ref No: {{ $test->ref_no }}</small>
                                    </div>

                                    <div>
                                        <a href="{{ route('laboratory.reports.print', $test->ref_no) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           target="_blank">
                                            <i class="bx bx-printer"></i> {{ localize('global.print_lab_report') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Debug Information --}}
            @if(config('app.debug'))
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6>Debug Information</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>First Test:</strong> {{ $firstTest ? $firstTest->labTest->name : 'None' }}</p>
                        <p><strong>First Test Results Count:</strong> {{ $firstTestResults ? $firstTestResults->count() : 0 }}</p>
                        <p><strong>Pending Tests Count:</strong> {{ $pendingTests->count() }}</p>
                        <p><strong>Completed Tests Count:</strong> {{ $completedTests->count() }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Test Results Card (Full Width) --}}
            <div class="col-md-12">
                <div class="card p-0 shadow-sm" id="resultCard">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="mb-0 fw-bold text-white">Test Result</h5>
                    </div>
                    <div class="card-body p-3">
                        <form id="resultForm" action="{{ route('laboratory.results.update') }}" method="post">
                            @csrf
                            <input type="hidden" id="test_registration_id" name="test_registration_id"
                            value="{{ $firstTest->id ?? '' }}">
                            <input type="hidden" id="ref_no" name="ref_no" value="{{ $firstTest->ref_no ?? '' }}">
                            
                            <table class="table table-bordered" id="resultTable">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Result</th>
                                        <th>Unit</th>
                                        <th>Normal Range</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($firstTestResults && $firstTestResults->count() > 0)
                                        @foreach($firstTestResults as $result)
                                        <tr>
                                            <td>{{ $result->parameter->parameter_name ?? '—' }}</td>
                                            <td>
                                                <input type="text" 
                                                       name="results[{{ $result->parameter->id ?? '' }}]" 
                                                       value="{{ $result->result ?? '' }}" 
                                                       class="form-control result-input">
                                            </td>
                                            <td>{{ $result->unit ?? '—' }}</td>
                                            <td>{{ $result->normal_range ?? '—' }}</td>
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

                            <button type="submit" class="btn btn-primary" id="saveResults">Save Results</button>
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
                <td>${result.parameter.parameter_name || '—'}</td>
                <td>
                    <input type="text" 
                           name="results[${result.parameter.id}]" 
                           value="${result.result || ''}" 
                           class="form-control result-input">
                </td>
                <td>${result.unit || '—'}</td>
                <td>${result.normal_range || '—'}</td>
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
                alert('Results updated successfully!');
                if (data.completed) {
                    alert('Test completed!');
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating results');
        });
    });
</script>
@endsection
