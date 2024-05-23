@extends('layouts.master')
<title>{{ localize('global.home_page') }}</title>
@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-md-12 order-3 order-md-2">
                    <div class="row g-4 mb-4">
                        <div class="col-sm-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_patients') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">{{ $totalPatients }}</h4>
                                                @if ($patientPercentageChange > 0)
                                                    <h4 class="mb-0 me-2 bg-label-success p-1 rounded-circle bx bx-trending-up"></h4>
                                                    <small class="text-success">{{ $patientPercentageChange }}%</small>
                                                @else
                                                    <h4 class="mb-0 me-2 bg-label-danger p-1 rounded bx bx-trending-down"></h4>
                                                    <small class="text-danger">{{ $patientPercentageChange }}%</small>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_patients') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="bx bx-user bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_appointments') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">{{ $totalAppointments }}
                                                </h4>
                                                @if ($appointmentPercentageChange > 0)
                                                    <h4 class="mb-0 me-2 bg-label-success p-1 rounded bx bx-trending-up"></h4>
                                                    <small class="text-success">{{ $appointmentPercentageChange }}%</small>
                                                @else
                                                    <h4 class="mb-0 me-2 bg-label-danger p-1 rounded bx bx-trending-down"></h4>
                                                    <small class="text-danger">{{ $appointmentPercentageChange }}%</small>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_appointments') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="bx bx-history bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.consultations') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">{{ $totalConsultations }}
                                                </h4>
                                                @if ($consultationPercentageChange > 0)
                                                    <h4 class="mb-0 me-2 bg-label-success p-1 rounded bx bx-trending-up"></h4>
                                                    <small class="text-success">{{ $consultationPercentageChange }}%</small>
                                                @else
                                                    <h4 class="mb-0 me-2 bg-label-danger p-1 rounded bx bx-trending-down"></h4>
                                                    <small class="text-danger">{{ $consultationPercentageChange }}%</small>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_consultations') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="bx bx-chat bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_hospitalized_patients') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">
                                                    {{ $totalInPatientAdmissions }}</h4>
                                                    @if ($hospitalizationPercentageChange > 0)
                                                    <h4 class="mb-0 me-2 bg-label-success p-1 rounded bx bx-trending-up"></h4>
                                                    <small class="text-success">{{ $hospitalizationPercentageChange }}%</small>
                                                @else
                                                    <h4 class="mb-0 me-2 bg-label-danger p-1 rounded bx bx-trending-down"></h4>
                                                    <small class="text-danger">{{ $hospitalizationPercentageChange }}%</small>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_hospitalizations') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="bx bx-bed bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_doctors') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">{{ $totalDoctors }}</h4>
                                                @if ($doctorPercentageChange > 0)
                                                    <h4 class="mb-0 me-2 bg-label-success p-1 rounded bx bx-trending-up"></h4>
                                                    <small class="text-success">{{ $doctorPercentageChange }}%</small>
                                                @else
                                                    <h4 class="mb-0 me-2 bg-label-danger p-1 rounded bx bx-trending-down"></h4>
                                                    <small class="text-danger">{{ $doctorPercentageChange }}%</small>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_doctors') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="bx bx-user bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_icu_patients') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">
                                                    {{ $totalIcuAdmissions }}</h4>
                                                    @if ($icuPercentageChange > 0)
                                                    <h4 class="mb-0 me-2 bg-label-success p-1 rounded bx bx-trending-up"></h4>
                                                    <small class="text-success">{{ $icuPercentageChange }}%</small>
                                                @else
                                                    <h4 class="mb-0 me-2 bg-label-danger p-1 rounded bx bx-trending-down"></h4>
                                                    <small class="text-danger">{{ $icuPercentageChange }}%</small>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_icu') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="bx bx-tv bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_prescriptions') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">
                                                    {{ $totalPrescriptions }}</h4>
                                                    @if ($prescriptionPercentageChange > 0)
                                                    <h4 class="mb-0 me-2 bg-label-success p-1 rounded bx bx-trending-up"></h4>
                                                    <small class="text-success">{{ $prescriptionPercentageChange }}%</small>
                                                @else
                                                    <h4 class="mb-0 me-2 bg-label-danger p-1 rounded bx bx-trending-down"></h4>
                                                    <small class="text-danger">{{ $prescriptionPercentageChange }}%</small>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_prescriptions') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="bx bx-receipt bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_operations') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">{{ $totalOperations }}
                                                </h4>
                                                @if ($operationPercentageChange > 0)
                                                    <h4 class="mb-0 me-2 bg-label-success p-1 rounded bx bx-trending-up"></h4>
                                                    <small class="text-success">{{ $operationPercentageChange }}%</small>
                                                @else
                                                    <h4 class="mb-0 me-2 bg-label-danger p-1 rounded bx bx-trending-down"></h4>
                                                    <small class="text-danger">{{ $operationPercentageChange }}%</small>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_operations') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="bx bx-cut bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <i class="bx bx-line-chart text-primary"></i>
                                    <h5 class="card-title text-center">{{ localize('global.patients_comparison_graph') }}
                                    </h5>
                                    <canvas id="patientsTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <i class="bx bx-line-chart text-primary"></i>
                                    <h5 class="card-title text-center">
                                        {{ localize('global.appointments_comparison_graph') }}</h5>
                                    <canvas id="appointmentsTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mt-3">
                        <div class="card">
                            <div class="card-body">
                                <i class="bx bx-line-chart text-primary"></i>
                                <h5 class="card-title text-center">
                                    {{ localize('global.doctors_activity_graph') }}</h5>
                                <figure class="highcharts-figure">
                                    <div id="container"></div>
                                </figure>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        @include('layouts.partial.footer')
        <!-- / Footer -->

        <div class="content-backdrop fade"></div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/chartjs.js') }}"></script>
    <script src="{{ asset('assets/js/echarts.js') }}"></script>
    <script src="{{ asset('assets/js/highcharts.js') }}"></script>
    <script src="{{ asset('assets/js/wordcloud.js') }}"></script>

    <script>
        // Render the patients trend chart
        const patientsTrendData = @json($patientsTrendData);
        const appointmentsTrendData = @json($appointmentsTrendData);
        var patientsTrendChart = new Chart(document.getElementById('patientsTrendChart'), {
            type: 'line',
            data: {
                labels: patientsTrendData.labels,
                datasets: [{
                    data: patientsTrendData.data,
                    backgroundColor: 'rgba(105,100,255, 0.1)',
                    borderColor: 'rgba(105,100,255, 0.8)',
                    pointBackgroundColor: 'rgba(105,100,255, 1)',
                    pointBorderColor: 'rgba(105,100,255, 1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        stepSize: 5,
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Render the appointments trend chart
        var appointmentsTrendChart = new Chart(document.getElementById('appointmentsTrendChart'), {
            type: 'line',
            data: {
                labels: appointmentsTrendData.labels,
                datasets: [{
                    data: appointmentsTrendData.data,
                    backgroundColor: 'rgba(105,108,255, 0.1)',
                    borderColor: 'rgba(105,100,255, 0.8)',
                    pointBackgroundColor: 'rgba(105,100,255, 1)',
                    pointBorderColor: 'rgba(105,100,255, 1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        stepSize: 5
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>

    <script>
        const data = <?php echo json_encode($wordCloudData); ?>;
        Highcharts.chart('container', {
            accessibility: {
                // ...
            },
            series: [{
                type: 'wordcloud',
                data,
                name: "{{ localize('global.occurred_count') }}"
            }],
            title: {
                text: null
            },
            credits: {
                enabled: false
            },
            exporting: {
                enabled: false
            },
            tooltip: {
                headerFormat: '<span style="font-size: 16px"><b>{point.key}</b>' +
                    '</span><br>'
            }

        });
    </script>
@endsection
