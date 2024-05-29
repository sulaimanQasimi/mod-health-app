@extends('layouts.master')
<title>{{ localize('global.home_page') }}</title>
@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-md-12 order-3 order-md-2">
                    <div class="row g-4 mb-4">
                        <div class="col-sm-6 col-xl-3">
                            <div class="card bg-label-primary border border-label-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_patients') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">{{ $totalPatients }}</h4>
                                                @if ($patientPercentageChange > 0)
                                                    <h4
                                                        class="mb-0 me-2 bg-success p-1 rounded-circle bx bx-trending-up text-white">
                                                    </h4>
                                                    <span class="text-success">{{ $patientPercentageChange }}%</span>
                                                @else
                                                    <h4 class="mb-0 me-2 bg-label-danger p-1 rounded bx bx-trending-down text-white">
                                                    </h4>
                                                    <span class="text-danger">{{ $patientPercentageChange }}%</span>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_patients') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-secondary">
                                                <i class="bx bx-user bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card bg-label-primary border border-label-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_appointments') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">{{ $totalAppointments }}
                                                </h4>
                                                @if ($appointmentPercentageChange > 0)
                                                <h4
                                                class="mb-0 me-2 bg-success p-1 rounded-circle bx bx-trending-up text-white">
                                            </h4>
                                                    <span class="text-success">{{ $appointmentPercentageChange }}%</span>
                                                @else
                                                <h4 class="mb-0 me-2 bg-danger p-1 rounded-circle bx bx-trending-down text-white">
                                                </h4>
                                                    <span class="text-danger">{{ $appointmentPercentageChange }}%</span>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_appointments') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-secondary">
                                                <i class="bx bx-history bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card bg-label-primary border border-label-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.consultations') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">
                                                    {{ $totalConsultations }}
                                                </h4>
                                                @if ($consultationPercentageChange > 0)
                                                <h4
                                                class="mb-0 me-2 bg-success p-1 rounded-circle bx bx-trending-up text-white">
                                            </h4>
                                                    <span
                                                        class="text-success">{{ $consultationPercentageChange }}%</span>
                                                @else
                                                <h4 class="mb-0 me-2 bg-danger p-1 rounded-circle bx bx-trending-down text-white">
                                                </h4>
                                                    <span class="text-danger">{{ $consultationPercentageChange }}%</span>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_consultations') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-secondary">
                                                <i class="bx bx-chat bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card bg-label-primary border border-label-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_hospitalized_patients') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">
                                                    {{ $totalInPatientAdmissions }}</h4>
                                                @if ($hospitalizationPercentageChange > 0)
                                                <h4
                                                class="mb-0 me-2 bg-success p-1 rounded-circle bx bx-trending-up text-white">
                                            </h4>
                                                    <span
                                                        class="text-success">{{ $hospitalizationPercentageChange }}%</span>
                                                @else
                                                <h4 class="mb-0 me-2 bg-danger p-1 rounded-circle bx bx-trending-down text-white">
                                                </h4>
                                                    <span
                                                        class="text-danger">{{ $hospitalizationPercentageChange }}%</span>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_hospitalizations') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-secondary">
                                                <i class="bx bx-bed bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card bg-label-primary border border-label-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.checkups') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">{{ $totalCheckups }}
                                                </h4>
                                                @if ($checkupPercentageChange > 0)
                                                <h4
                                                class="mb-0 me-2 bg-success p-1 rounded-circle bx bx-trending-up text-white">
                                            </h4>
                                                    <span class="text-success">{{ $checkupPercentageChange }}%</span>
                                                @else
                                                <h4 class="mb-0 me-2 bg-danger p-1 rounded-circle bx bx-trending-down text-white">
                                                </h4>
                                                    <span class="text-danger">{{ $checkupPercentageChange }}%</span>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_checkups') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-secondary">
                                                <i class="bx bx-hard-hat bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card bg-label-primary border border-label-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_icu_patients') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">
                                                    {{ $totalIcuAdmissions }}</h4>
                                                @if ($icuPercentageChange > 0)
                                                <h4
                                                class="mb-0 me-2 bg-success p-1 rounded-circle bx bx-trending-up text-white">
                                            </h4>
                                                    <span class="text-success">{{ $icuPercentageChange }}%</span>
                                                @else
                                                <h4 class="mb-0 me-2 bg-danger p-1 rounded-circle bx bx-trending-down text-white">
                                                </h4>
                                                    <span class="text-danger">{{ $icuPercentageChange }}%</span>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_icu') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-secondary">
                                                <i class="bx bx-tv bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card bg-label-primary border border-label-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_prescriptions') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">
                                                    {{ $totalPrescriptions }}</h4>
                                                @if ($prescriptionPercentageChange > 0)
                                                <h4
                                                class="mb-0 me-2 bg-success p-1 rounded-circle bx bx-trending-up text-white">
                                            </h4>
                                                    <span
                                                        class="text-success">{{ $prescriptionPercentageChange }}%</span>
                                                @else
                                                <h4 class="mb-0 me-2 bg-danger p-1 rounded-circle bx bx-trending-down text-white">
                                                </h4>
                                                    <span
                                                        class="text-danger">{{ $prescriptionPercentageChange }}%</span>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_prescriptions') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-secondary">
                                                <i class="bx bx-receipt bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card bg-label-primary border border-label-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_operations') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 bg-label-primary p-1 rounded">{{ $totalOperations }}
                                                </h4>
                                                @if ($operationPercentageChange > 0)
                                                <h4
                                                class="mb-0 me-2 bg-success p-1 rounded-circle bx bx-trending-up text-white">
                                            </h4>
                                                    <span class="text-success">{{ $operationPercentageChange }}%</span>
                                                @else
                                                <h4 class="mb-0 me-2 bg-danger p-1 rounded-circle bx bx-trending-down text-white">
                                                </h4>
                                                    <span class="text-danger">{{ $operationPercentageChange }}%</span>
                                                @endif
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_operations') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-secondary">
                                                <i class="bx bx-cut bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-xl-4">
                            <div class="card bg-label-warning border border-label-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{localize('global.occupied_beds')}}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 badge badge-center bg-warning" style="font-size: xx-large;">{{$occupied_beds}}</h4>
                                            </div>
                                        </div>
                                        <span class="badge bg-warning rounded p-2">
                                            <i class="bx bx-bed bx-lg"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-4">
                            <div class="card bg-label-primary border-label-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{localize('global.all_beds')}}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 badge badge-center bg-primary" style="font-size: xx-large;">{{$all_beds}}</h4>
                                            </div>
                                        </div>
                                        <span class="badge bg-primary rounded p-2">
                                            <i class="bx bx-bed bx-lg"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-4">
                            <div class="card bg-label-success border-label-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{localize('global.free_beds')}}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 badge badge-center bg-success" style="font-size: xx-large;">{{$free_beds}}</h4>
                                            </div>
                                        </div>
                                        <span class="badge bg-success rounded p-2">
                                            <i class="bx bx-bed bx-lg"></i>
                                        </span>
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
