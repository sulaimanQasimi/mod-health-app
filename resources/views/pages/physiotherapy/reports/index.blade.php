@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ localize('global.physiotherapy_reports') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('physiotherapy-reports.generate') }}" method="POST" id="reportForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="start_date" class="form-label">{{ localize('global.start_date') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control datepicker_dari pdp-el persian-date @error('start_date') is-invalid @enderror"
                                            dir="ltr" id="start_date" name="start_date" value="" required readonly>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="end_date" class="form-label">{{ localize('global.end_date') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control datepicker_dari pdp-el persian-date @error('end_date') is-invalid @enderror"
                                            dir="ltr" id="end_date" name="end_date" value="" required readonly>
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bx bx-search"></i> {{ localize('global.generate_report') }}
                                </button>
                                <button type="button" class="btn btn-success me-2" onclick="exportReport('excel')">
                                    <i class="bx bx-file"></i> {{ localize('global.export_excel') }}
                                </button>
                                <button type="button" class="btn btn-danger" onclick="exportReport('pdf')">
                                    <i class="bx bx-file-pdf"></i> {{ localize('global.export_pdf') }}
                                </button>
                            </div>
                        </form>

                        <div class="mt-4">
                            <h6>{{ localize('global.report_includes') }}</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ localize('global.summary_report') }}</h6>
                                            <p class="card-text">{{ localize('global.summary_report_description') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ localize('global.detailed_report') }}</h6>
                                            <p class="card-text">{{ localize('global.detailed_report_description') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ localize('global.report_by_type') }}</h6>
                                            <p class="card-text">{{ localize('global.report_by_type_description') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ localize('global.report_by_physiotherapist') }}</h6>
                                            <p class="card-text">
                                                {{ localize('global.report_by_physiotherapist_description') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Persian Datepicker
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize start date picker
            // $('#start_date').persianDatepicker();

            // // Initialize end date picker
            // $('#end_date').persianDatepicker();

            // Set default end date to today if not set
            // const endDateInput = document.getElementById('end_date');
            // if (!endDateInput.value) {
            //     var today = new Date();
            //     var formattedToday = today.getFullYear() + '-' + 
            //                        String(today.getMonth() + 1).padStart(2, '0') + '-' + 
            //                        String(today.getDate()).padStart(2, '0');
            //     endDateInput.value = formattedToday;
            // }
        });

        // Function to export reports
        function exportReport(format) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (!startDate || !endDate) {
                alert('Please select both start and end dates');
                return;
            }

            // Create a form for export
            const exportForm = document.createElement('form');
            exportForm.method = 'POST';
            exportForm.action = '{{ route("physiotherapy-reports.export") }}';
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            exportForm.appendChild(csrfToken);
            
            // Add dates
            const startDateInput = document.createElement('input');
            startDateInput.type = 'hidden';
            startDateInput.name = 'start_date';
            startDateInput.value = startDate;
            exportForm.appendChild(startDateInput);
            
            const endDateInput = document.createElement('input');
            endDateInput.type = 'hidden';
            endDateInput.name = 'end_date';
            endDateInput.value = endDate;
            exportForm.appendChild(endDateInput);
            
            // Add format
            const formatInput = document.createElement('input');
            formatInput.type = 'hidden';
            formatInput.name = 'format';
            formatInput.value = format;
            exportForm.appendChild(formatInput);
            
            // Submit form
            document.body.appendChild(exportForm);
            exportForm.submit();
            document.body.removeChild(exportForm);
        }
    </script>
@endsection