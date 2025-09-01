@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.physiotherapy_report_result') }}</h5>
                        <div>
                            <form action="{{ route('physiotherapy-reports.export') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                                <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                                <input type="hidden" name="report_type" value="{{ $reportType }}">
                                <input type="hidden" name="format" value="pdf">
                                <button type="submit" class="btn btn-danger me-2">
                                    <i class="bx bx-file-pdf"></i> {{ localize('global.export_pdf') }}
                                </button>
                            </form>
                            <form action="{{ route('physiotherapy-reports.export') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                                <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                                <input type="hidden" name="report_type" value="{{ $reportType }}">
                                <input type="hidden" name="format" value="excel">
                                <button type="submit" class="btn btn-success me-2">
                                    <i class="bx bx-file"></i> {{ localize('global.export_excel') }}
                                </button>
                            </form>
                            <a href="{{ route('physiotherapy-reports.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> {{ localize('global.back') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>{{ localize('global.report_information') }}</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>{{ localize('global.report_type') }}:</strong></td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $reportType)) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ localize('global.start_date') }}:</strong></td>
                                        <td>{{ $startDate->format('Y-m-d') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ localize('global.end_date') }}:</strong></td>
                                        <td>{{ $endDate->format('Y-m-d') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ localize('global.generated_at') }}:</strong></td>
                                        <td>{{ now()->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if($reportType == 'summary')
                            @include('pages.physiotherapy.reports.partials.summary')
                        @elseif($reportType == 'detailed')
                            @include('pages.physiotherapy.reports.partials.detailed')
                        @elseif($reportType == 'by_type')
                            @include('pages.physiotherapy.reports.partials.by_type')
                        @elseif($reportType == 'by_physiotherapist')
                            @include('pages.physiotherapy.reports.partials.by_physiotherapist')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
