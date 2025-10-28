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
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-gradient-primary text-white border-0">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <div class="avatar-initial bg-white text-primary rounded-circle">
                                <i class="bx bx-user fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-semibold">{{ localize('global.patient_information') }}</h5>
                            <small class="opacity-75">{{ localize('global.patient_details') }}</small>
                        </div>
                    </div>
                    @if($firstTest)
                        <div class="text-end">
                            <span class="badge bg-white text-primary fs-6 px-3 py-2">
                                <i class="bx bx-hash me-1"></i>
                                {{ $firstTest->ref_no ?? '—' }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                <div class="row g-0">
                    {{-- Patient Name --}}
                    <div class="col-md-3 col-sm-6">
                        <div class="p-4 border-end border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle">
                                        <i class="bx bx-user fs-5"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold text-dark">{{ $patient->name }} {{ $patient->last_name }}</h6>
                                    <small class="text-muted">{{ localize('global.patient_name') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Father Name --}}
                    <div class="col-md-3 col-sm-6">
                        <div class="p-4 border-end border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded-circle">
                                        <i class="bx bx-male-sign fs-5"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold text-dark">{{ $patient->father_name ?? '—' }}</h6>
                                    <small class="text-muted">{{ localize('global.father_name') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Age --}}
                    <div class="col-md-3 col-sm-6">
                        <div class="p-4 border-end border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-sm bg-success bg-opacity-10 text-success rounded-circle">
                                        <i class="bx bx-cake fs-5"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold text-dark">{{ $patient->age ?? '—' }} {{ localize('global.years') }}</h6>
                                    <small class="text-muted">{{ localize('global.age') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Phone --}}
                    <div class="col-md-3 col-sm-6">
                        <div class="p-4 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-sm bg-warning bg-opacity-10 text-warning rounded-circle">
                                        <i class="bx bx-phone-call fs-5"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold text-dark">{{ $patient->phone ?? '—' }}</h6>
                                    <small class="text-muted">{{ localize('global.phone') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Additional Info Row --}}
                <div class="row g-0">
                    {{-- ID Card --}}
                    <div class="col-md-3 col-sm-6">
                        <div class="p-4 border-end">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-sm bg-secondary bg-opacity-10 text-secondary rounded-circle">
                                        <i class="bx bx-id-card fs-5"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold text-dark">{{ $patient->id_card ?? '—' }}</h6>
                                    <small class="text-muted">{{ localize('global.id_card') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Gender --}}
                    <div class="col-md-3 col-sm-6">
                        <div class="p-4 border-end">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-sm bg-purple bg-opacity-10 text-purple rounded-circle">
                                        <i class="bx bx-{{ $patient->gender === 'male' ? 'male' : 'female' }} fs-5"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold text-dark">{{ ucfirst($patient->gender ?? '—') }}</h6>
                                    <small class="text-muted">{{ localize('global.gender') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Test Status --}}
                    <div class="col-md-3 col-sm-6">
                        <div class="p-4 border-end">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-sm bg-{{ $firstTest?->status === 'completed' ? 'success' : ($firstTest?->status === 'in_progress' ? 'warning' : 'secondary') }} bg-opacity-10 text-{{ $firstTest?->status === 'completed' ? 'success' : ($firstTest?->status === 'in_progress' ? 'warning' : 'secondary') }} rounded-circle">
                                        <i class="bx bx-{{ $firstTest?->status === 'completed' ? 'check-circle' : ($firstTest?->status === 'in_progress' ? 'time' : 'clock') }} fs-5"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold text-dark">
                                        <span class="badge bg-{{ $firstTest?->status === 'completed' ? 'success' : ($firstTest?->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($firstTest?->status ?? 'pending') }}
                                        </span>
                                    </h6>
                                    <small class="text-muted">{{ localize('global.status') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Assigned To --}}
                    <div class="col-md-3 col-sm-6">
                        <div class="p-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded-circle">
                                        <i class="bx bx-user-check fs-5"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold text-dark">{{ $firstTest?->assignedTo?->name ?? '—' }}</h6>
                                    <small class="text-muted">{{ localize('global.assigned_to') }}</small>
                                </div>
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
                        {{ $firstTest?->labType?->name ?? localize('global.test_results') }}
                    </h5>
                    <div class="text-end">
                        <small class="text-muted">{{ localize('global.reference_number') }}: <strong id="headerRef">{{ $firstTest->ref_no ?? '—' }}</strong></small>
                        @if($firstTest && $firstTest->assigned_to)
                            <br>
                            <small class="text-info">
                                <i class="bx bx-user me-1"></i>
                                {{ localize('global.assigned_to') }}: {{ $firstTest->assignedTo->name ?? 'Unknown' }}
                                @if($firstTest->assigned_at)
                                    ({{ \Verta($firstTest->assigned_at)->formatJalaliDate() }})
                                @endif
                            </small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form id="resultForm" action="{{ route('laboratory.results.update') }}" method="post">
                    @csrf
                    <input type="hidden" id="test_registration_id" name="test_registration_id" value="{{ $firstTest->id ?? '' }}">
                    <input type="hidden" id="ref_no" name="ref_no" value="{{ $firstTest->ref_no ?? '' }}">
                    <input type="hidden" id="complete_flag" name="complete" value="0">

                    {{-- Assignment Check --}}
                    @if($firstTest && !$firstTest->assigned_to && $firstTest->status === 'pending')
                        <div class="alert alert-warning d-flex align-items-center mb-4">
                            <i class="bx bx-info-circle me-2"></i>
                            <div class="flex-grow-1">
                                <strong>{{ localize('global.test_not_assigned') }}</strong>
                                <p class="mb-0">{{ localize('global.accept_test_to_continue') }}</p>
                            </div>
                            <button type="button" class="btn btn-success accept-test-btn" 
                                    data-registration-id="{{ $firstTest->id }}">
                                <i class="bx bx-check me-1"></i>
                                {{ localize('global.accept_test') }}
                            </button>
                        </div>
                    @endif

                    @if($firstTest && $firstTest->labType && $firstTest->labType->directLabTestParameters && $firstTest->labType->directLabTestParameters->count() > 0)
                        {{-- Parametered test - show parameter table --}}
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
                                                No parameters found for this test
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @else
                        {{-- Non-parametered test - show CKEditor --}}
                        <div class="mb-3">
                            <label for="text_result" class="form-label">{{ localize('global.test_result') }}</label>
                            <div id="text_result_editor"></div>
                            <textarea name="text_result" id="text_result" style="display: none;"></textarea>
                        </div>
                    @endif

                    {{-- Notes Field --}}
                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ localize('global.notes') }}</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" 
                            placeholder="{{ localize('global.add_notes_here') }}">{{ $firstTest->notes ?? '' }}</textarea>
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

@section('styles')
<style>
    .ck-editor__editable {
        min-height: 200px;
    }
    
    .ck-editor__editable_inline {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
    }
    
    .ck-editor__editable:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .ck.ck-editor__main > .ck-editor__editable:not(.ck-focused) {
        border-color: #d1d5db;
    }
    
    .ck.ck-editor__main > .ck-editor__editable.ck-focused {
        border-color: #3b82f6;
    }
</style>
@endsection

@section('scripts')
    @vite('resources/js/ckeditor.js')
    <script>
        let editor = null;
        
        // Initialize CKEditor
        function initCKEditor() {
            if (document.getElementById('text_result_editor') && !editor) {
                ClassicEditor
                    .create(document.querySelector('#text_result_editor'), {
                        toolbar: {
                            items: [
                                'heading', '|',
                                'bold', 'italic', 'underline', '|',
                                'bulletedList', 'numberedList', '|',
                                'outdent', 'indent', '|',
                                'blockQuote', 'insertTable', '|',
                                'undo', 'redo'
                            ]
                        },
                        language: 'en',
                        table: {
                            contentToolbar: [
                                'tableColumn',
                                'tableRow',
                                'mergeTableCells'
                            ]
                        }
                    })
                    .then(editorInstance => {
                        editor = editorInstance;
                        
                        // Set initial content
                        const textarea = document.getElementById('text_result');
                        if (textarea && textarea.value) {
                            editor.setData(textarea.value);
                        }
                        
                        // Update textarea when editor content changes
                        editor.model.document.on('change:data', () => {
                            textarea.value = editor.getData();
                        });
                    })
                    .catch(error => {
                        console.error('Error initializing CKEditor:', error);
                    });
            }
        }
        
        // Initialize CKEditor when page loads
        document.addEventListener('DOMContentLoaded', initCKEditor);
        
        // Re-initialize CKEditor when switching tests
        function reinitCKEditor(content = '') {
            if (editor) {
                editor.setData(content);
                document.getElementById('text_result').value = content;
            } else {
                initCKEditor();
                setTimeout(() => {
                    if (editor) {
                        editor.setData(content);
                        document.getElementById('text_result').value = content;
                    }
                }, 100);
            }
        }
    </script>
    
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
                        alert('{{ localize("global.error_loading_test_results_with_message") }}: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ localize("global.error_loading_test_results_alert") }}');
                });
        }

        function updateResultTable(test, results) {
            // Update hidden inputs
            document.getElementById('test_registration_id').value = test.id;
            document.getElementById('ref_no').value = test.ref_no;
            const headerRef = document.getElementById('headerRef');
            if (headerRef) headerRef.textContent = test.ref_no || '—';

            // Check if this is a parametered test or text-based test
            const resultTable = document.querySelector('#resultTable');
            const textResultEditor = document.getElementById('text_result_editor');
            
            if (resultTable) {
                // This is a parametered test
                const tbody = resultTable.querySelector('tbody');
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
            } else if (textResultEditor) {
                // This is a text-based test - update CKEditor
                const textResult = results.find(r => r.text_result !== undefined);
                const content = textResult ? textResult.text_result : '';
                reinitCKEditor(content);
            }
        }

        // Handle form submission
        document.getElementById('resultForm').addEventListener('submit', function (e) {
            e.preventDefault();

            // Update textarea with CKEditor content before submission
            if (editor) {
                document.getElementById('text_result').value = editor.getData();
            }

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
                        toastr && toastr.success ? toastr.success('{{ localize("global.results_updated_successfully_alert") }}') : alert('{{ localize("global.results_updated_successfully_alert") }}');
                        if (data.completed) {
                            toastr && toastr.success ? toastr.success('{{ localize("global.test_completed_alert") }}') : alert('{{ localize("global.test_completed_alert") }}');
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
                    toastr && toastr.error ? toastr.error('{{ localize("global.error_updating_results_alert") }}') : alert('{{ localize("global.error_updating_results_alert") }}');
                });
        });

        // Handle accept test functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('accept-test-btn') || e.target.closest('.accept-test-btn')) {
                const btn = e.target.classList.contains('accept-test-btn') ? e.target : e.target.closest('.accept-test-btn');
                const registrationId = btn.getAttribute('data-registration-id');
                const originalHtml = btn.innerHTML;
                
                // Show loading state
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Accepting...';
                
                fetch('/laboratory/results/' + registrationId + '/accept', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr && toastr.success ? toastr.success(data.message) : alert(data.message);
                        // Reload the page to show updated data
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr && toastr.error ? toastr.error(data.message) : alert(data.message);
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr && toastr.error ? toastr.error('Error accepting test') : alert('Error accepting test');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
            }
        });
    </script>
@endsection