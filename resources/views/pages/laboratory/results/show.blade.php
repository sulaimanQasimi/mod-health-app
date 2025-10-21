@extends('layouts.master')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            {{-- <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ localize('global.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('laboratory.registrations.index') }}">{{ localize('global.lab_test_registrations') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ localize('global.test_results') }}</li>
            </ol> --}}
        </nav>

        {{-- Patient Information Header --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <div class="d-flex align-items-center">
                    <i class="bx bx-user me-2"></i>
                    <h5 class="mb-0">{{ localize('global.patient_information') }}</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center p-3 bg-none border mt-2 rounded">
                            <div class="flex-shrink-0">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bx bx-user"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block">{{ localize('global.name') }}</small>
                                <div class="fw-semibold">{{ $patient->name }} {{ $patient->last_name }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center p-3 bg-none border mt-2 rounded">
                            <div class="flex-shrink-0">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bx bx-male"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block">{{ localize('global.father_name') }}</small>
                                <div class="fw-semibold">{{ $patient->father_name }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center p-3 bg-none border mt-2 rounded">
                            <div class="flex-shrink-0">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bx bx-calendar"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block">{{ localize('global.age') }}</small>
                                <div class="fw-semibold">{{ $patient->age }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center p-3 bg-none border mt-2 rounded">
                            <div class="flex-shrink-0">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bx bx-phone"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block">{{ localize('global.phone') }}</small>
                                <div class="fw-semibold">{{ $patient->phone }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Test Results --}}
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-test-tube"></i>
                        {{ $firstTest?->labTest?->name ?? localize('global.test_results') }}
                    </h5>
                    <div class="text-end">
                        <small class="text-muted">{{ localize('global.reference_number') }}: <strong id="headerRef">{{ $firstTest->ref_no ?? '—' }}</strong></small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form id="resultForm" action="{{ route('laboratory.results.update') }}" method="post">
                    @csrf
                    <input type="hidden" id="test_registration_id" name="test_registration_id" value="{{ $firstTest->id ?? '' }}">
                    <input type="hidden" id="ref_no" name="ref_no" value="{{ $firstTest->ref_no ?? '' }}">
                    <input type="hidden" id="complete_flag" name="complete" value="0">

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="resultTable">
                            <thead class="table-light">
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
                                            <td><strong>{{ $result->parameter->parameter_name ?? '—' }}</strong></td>
                                            <td>
                                                <input type="text" name="results[{{ $result->parameter->id ?? '' }}]"
                                                    value="{{ $result->result ?? '' }}" class="form-control">
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
                    </div>

                    <div class="d-flex gap-3 justify-content-center mt-4">
                        <button type="submit" class="btn btn-primary" id="saveResults">
                            <i class="bx bx-save"></i> {{ localize('global.save') }}
                        </button>
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
@endsection

@section('scripts')
    <script>
        // Handle test selection
        document.querySelectorAll('.test-item, .test-card').forEach(item => {
            item.addEventListener('click', function () {
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
                               class="form-control">
                    </td>
                    <td><span class="text-muted">${result.unit || '—'}</span></td>
                    <td><span class="text-muted">${result.normal_range || '—'}</span></td>
                `;
                tbody.appendChild(row);
            });
        }

        // Handle form submission
        document.getElementById('resultForm').addEventListener('submit', function (e) {
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
                        // Handle redirect if provided
                        if (data.redirect) {
                            setTimeout(() => window.location.href = data.redirect, 1500);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr && toastr.error ? toastr.error('Error updating results') : alert('Error updating results');
                });
        });
    </script>
@endsection