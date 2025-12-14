@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Print Header -->
            <div class="row mb-4 no-print">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0">{{ localize('global.print_dental_chart') }}</h2>
                        <div class="d-flex gap-2">
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="bx bx-printer"></i> {{ localize('global.print') }}
                            </button>
                            <a href="{{ route('dentist-registrations.show', $dentistRegistration) }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> {{ localize('global.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Print Content -->
            <div class="card print-content">
                <div class="card-body">
                    <!-- Header -->
                    <div class="text-center mb-4 border-bottom pb-3">
                        <h3>{{ localize('global.dental_chart_report') }}</h3>
                        <p class="mb-0"><strong>{{ localize('global.reference_number') }}:</strong> {{ $dentistRegistration->ref_no }}</p>
                    </div>

                    <!-- Patient & Dentist Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>{{ localize('global.patient_information') }}</h5>
                            <p><strong>{{ localize('global.name') }}:</strong> {{ $dentistRegistration->appointment->patient->name ?? 'N/A' }}</p>
                            <p><strong>{{ localize('global.age') }}:</strong> {{ $dentistRegistration->appointment->patient->age ?? 'N/A' }}</p>
                            <p><strong>{{ localize('global.gender') }}:</strong> {{ $dentistRegistration->appointment->patient->gender ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ localize('global.dentist_information') }}</h5>
                            <p><strong>{{ localize('global.dentist') }}:</strong> {{ $dentistRegistration->dentist->name ?? 'N/A' }}</p>
                            <p><strong>{{ localize('global.registration_date') }}:</strong> {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($dentistRegistration->registration_date) }}</p>
                            <p><strong>{{ localize('global.status') }}:</strong> {{ ucfirst($dentistRegistration->status) }}</p>
                        </div>
                    </div>

                    <!-- Visual Chart -->
                    <div class="mb-4">
                        @include('pages.dentist.charts.partials.tooth-chart', ['allTeeth' => $allTeeth, 'dentistRegistration' => $dentistRegistration])
                    </div>

                    <!-- Chart Details Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.tooth_number') }}</th>
                                    <th>{{ localize('global.condition') }}</th>
                                    <th>{{ localize('global.gum_health') }}</th>
                                    <th>{{ localize('global.pocket_depth') }}</th>
                                    <th>{{ localize('global.oral_hygiene_score') }}</th>
                                    <th>{{ localize('global.notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestCharts as $chart)
                                    <tr>
                                        <td><strong>{{ $chart->tooth_number }}</strong></td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $chart->tooth_condition)) }}</td>
                                        <td>{{ $chart->gum_health ? ucfirst($chart->gum_health) : 'N/A' }}</td>
                                        <td>{{ $chart->pocket_depth ? $chart->pocket_depth . ' mm' : 'N/A' }}</td>
                                        <td>{{ $chart->oral_hygiene_score ?? 'N/A' }}</td>
                                        <td>{{ Str::limit($chart->notes, 50) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ localize('global.no_charts_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer -->
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-muted small mb-0">{{ localize('global.generated_on') }}: {{ now()->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
<style>
@media print {
    .no-print {
        display: none !important;
    }
    .print-content {
        border: none !important;
        box-shadow: none !important;
    }
    body {
        background: white !important;
    }
    .card {
        border: none !important;
    }
}
</style>
@endsection
