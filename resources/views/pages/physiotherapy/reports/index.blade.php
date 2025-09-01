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
                                        <label for="start_date" class="form-label">{{ localize('global.start_date') }} <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                               id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d', strtotime('-30 days'))) }}" required>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="end_date" class="form-label">{{ localize('global.end_date') }} <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                               id="end_date" name="end_date" value="{{ old('end_date', date('Y-m-d')) }}" required>
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="report_type" class="form-label">{{ localize('global.report_type') }} <span class="text-danger">*</span></label>
                                        <select class="form-select @error('report_type') is-invalid @enderror" id="report_type" name="report_type" required>
                                            <option value="">{{ localize('global.select_report_type') }}</option>
                                            <option value="summary" {{ old('report_type') == 'summary' ? 'selected' : '' }}>{{ localize('global.summary_report') }}</option>
                                            <option value="detailed" {{ old('report_type') == 'detailed' ? 'selected' : '' }}>{{ localize('global.detailed_report') }}</option>
                                            <option value="by_type" {{ old('report_type') == 'by_type' ? 'selected' : '' }}>{{ localize('global.report_by_type') }}</option>
                                            <option value="by_physiotherapist" {{ old('report_type') == 'by_physiotherapist' ? 'selected' : '' }}>{{ localize('global.report_by_physiotherapist') }}</option>
                                        </select>
                                        @error('report_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search"></i> {{ localize('global.generate_report') }}
                                </button>
                            </div>
                        </form>

                        <div class="mt-4">
                            <h6>{{ localize('global.report_types_explanation') }}</h6>
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
                                            <p class="card-text">{{ localize('global.report_by_physiotherapist_description') }}</p>
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
        // Set end date to today if not set
        document.addEventListener('DOMContentLoaded', function() {
            const endDateInput = document.getElementById('end_date');
            if (!endDateInput.value) {
                endDateInput.value = new Date().toISOString().split('T')[0];
            }
        });
    </script>
@endsection
