@extends('layouts.master')
<title>{{ localize('global.home_page') }}</title>

<style>
    .bg-purple {
        background-color: #6f42c1 !important;
    }

    .bg-pink {
        background-color: #e83e8c !important;
    }

    .bg-orange {
        background-color: #fd7e14 !important;
    }

    .bg-teal {
        background-color: #20c997 !important;
    }

    .bg-indigo {
        background-color: #6610f2 !important;
    }

    .bg-cyan {
        background-color: #0dcaf0 !important;
    }
</style>
@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Loading Indicator -->
            <div id="dashboard-loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">{{ localize('global.loading_dashboard') }}</p>
            </div>

            <!-- Dashboard Content (hidden initially) -->
            <div id="dashboard-content" style="display: none;">
            <div class="row">
                <div class="col-md-12 order-3 order-md-2">
                    <div class="row g-4 mb-4">

                        <div class="col-sm-6 col-xl-3">
                            <div class="card border border-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.today_patients') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 p-1 rounded text-primary" id="today-patients">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.today_registered_patients') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-primary">
                                                <i class="bx bx-user-plus bx-md"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="card border border-danger">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.emergency_today_patients') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 p-1 rounded text-danger" id="total-emergency-patients">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.emergency') }} {{ localize('global.today_registered_patients') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-danger">
                                                <i class="bx bx-first-aid bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="card  border ">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_patients') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2  p-1 rounded" id="total-patients">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_patients') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-primary">
                                                <i class="bx bx-user bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card  border ">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_appointments') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2  p-1 rounded" id="total-appointments">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_appointments') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-success">
                                                <i class="bx bx-history bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card  border ">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.consultations') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2  p-1 rounded" id="total-consultations">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_consultations') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-info">
                                                <i class="bx bx-chat bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card  border ">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_hospitalized_patients') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2  p-1 rounded" id="total-inpatient-admissions">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_hospitalizations') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-warning">
                                                <i class="bx bx-bed bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card  border ">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.checkups') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2  p-1 rounded" id="total-checkups">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_checkups') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-danger">
                                                <i class="bx bx-hard-hat bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card  border ">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_icu_patients') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2  p-1 rounded" id="total-icu-admissions">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_icu') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-dark">
                                                <i class="bx bx-tv bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card border border-info">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_ccu_patients') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 p-1 rounded text-info" id="total-ccu-admissions">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_ccu') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-info">
                                                <i class="bx bx-heart-circle bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card  border ">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_prescriptions') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2  p-1 rounded" id="total-prescriptions">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_prescriptions') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-purple">
                                                <i class="bx bx-receipt bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card  border ">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_operations') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2  p-1 rounded" id="total-operations">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_operations') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-pink">
                                                <i class="bx bx-cut bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="card  border ">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_physiotherapy_procedures') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2  p-1 rounded" id="total-physiotherapy-procedures">-</h4>
                                            </div>
                                            <p class="mb-0">
                                                {{ localize('global.all_registered_physiotherapy_procedures') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-teal">
                                                <i class="bx bx-spa bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-xl-4">
                            <div class="card bg-label-warning border ">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.occupied_beds') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 badge badge-center bg-warning"
                                                    style="font-size: xx-large;" id="occupied-beds">-</h4>
                                            </div>
                                        </div>
                                        <span class="badge bg-warning rounded p-2">
                                            <i class="bx bx-bed bx-lg text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-4">
                            <div class="card bg-label-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_beds') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 badge badge-center bg-primary"
                                                    style="font-size: xx-large;" id="all-beds">-</h4>
                                            </div>
                                        </div>
                                        <span class="badge bg-primary rounded p-2">
                                            <i class="bx bx-bed bx-lg text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-4">
                            <div class="card bg-label-success ">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.free_beds') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 badge badge-center bg-success"
                                                    style="font-size: xx-large;" id="free-beds">-</h4>
                                            </div>
                                        </div>
                                        <span class="badge bg-success rounded p-2">
                                            <i class="bx bx-bed bx-lg text-white"></i>
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
                                        {{ localize('global.appointments_comparison_graph') }}
                                    </h5>
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
                                    {{ localize('global.doctors_activity_graph') }}
                                </h5>
                                <figure class="highcharts-figure">
                                    <div id="container"></div>
                                </figure>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
            </div>
            <!-- / Dashboard Content -->

        <!-- Footer -->
        @include('layouts.partial.footer')
        <!-- / Footer -->

        <div class="content-backdrop fade"></div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/vendor/libs/chartjs/chartjs.js') }}"></script>
    <script src="{{ asset('assets/js/echarts.js') }}"></script>
    <script src="{{ asset('assets/js/highcharts.js') }}"></script>
    <script src="{{ asset('assets/js/wordcloud.js') }}"></script>

    <script>
        let patientsTrendChart = null;
        let appointmentsTrendChart = null;
        let wordCloudChart = null;

        // Load dashboard data via AJAX
        function loadDashboardData() {
            $.ajax({
                url: '{{ route("home") }}',
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const data = response.data;
                        
                        // Update statistics cards
                        $('#today-patients').text(data.todayPatients || 0);
                        $('#total-emergency-patients').text(data.totalEmergencyPatients || 0);
                        $('#total-patients').text(data.totalPatients || 0);
                        $('#total-appointments').text(data.totalAppointments || 0);
                        $('#total-consultations').text(data.totalConsultations || 0);
                        $('#total-inpatient-admissions').text(data.totalInPatientAdmissions || 0);
                        $('#total-checkups').text(data.totalCheckups || 0);
                        $('#total-icu-admissions').text(data.totalIcuAdmissions || 0);
                        $('#total-ccu-admissions').text(data.totalCcuAdmissions || 0);
                        $('#total-prescriptions').text(data.totalPrescriptions || 0);
                        $('#total-operations').text(data.totalOperations || 0);
                        $('#total-physiotherapy-procedures').text(data.totalPhysiotherapyProcedures || 0);
                        
                        // Update bed statistics
                        $('#occupied-beds').text(data.occupied_beds || 0);
                        $('#all-beds').text(data.all_beds || 0);
                        $('#free-beds').text(data.free_beds || 0);
                        
                        // Initialize charts
                        initializeCharts(data);
                        
                        // Hide loading, show content
                        $('#dashboard-loading').hide();
                        $('#dashboard-content').fadeIn();
                    } else {
                        showError('Failed to load dashboard data');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading dashboard:', error);
                    showError('Error loading dashboard data. Please refresh the page.');
                }
            });
        }

        // Initialize charts with data
        function initializeCharts(data) {
            // Patients Trend Chart
            if (patientsTrendChart) {
                patientsTrendChart.destroy();
            }
            
            const patientsCtx = document.getElementById('patientsTrendChart');
            if (patientsCtx && data.patientsTrendData) {
                patientsTrendChart = new Chart(patientsCtx, {
                    type: 'line',
                    data: {
                        labels: data.patientsTrendData.labels || [],
                        datasets: [{
                            data: data.patientsTrendData.data || [],
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
            }

            // Appointments Trend Chart
            if (appointmentsTrendChart) {
                appointmentsTrendChart.destroy();
            }
            
            const appointmentsCtx = document.getElementById('appointmentsTrendChart');
            if (appointmentsCtx && data.appointmentsTrendData) {
                appointmentsTrendChart = new Chart(appointmentsCtx, {
                    type: 'line',
                    data: {
                        labels: data.appointmentsTrendData.labels || [],
                        datasets: [{
                            data: data.appointmentsTrendData.data || [],
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
                        responsive: true,
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
            }

            // Word Cloud Chart
            if (wordCloudChart) {
                wordCloudChart.destroy();
            }
            
            if (data.wordCloudData && data.wordCloudData.length > 0) {
                wordCloudChart = Highcharts.chart('container', {
                    accessibility: {
                        enabled: false
                    },
                    series: [{
                        type: 'wordcloud',
                        data: data.wordCloudData,
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
                        headerFormat: '<span style="font-size: 16px"><b>{point.key}</b></span><br>'
                    }
                });
            }
        }

        // Show error message
        function showError(message) {
            $('#dashboard-loading').html(
                '<div class="alert alert-danger" role="alert">' +
                '<i class="bx bx-error-circle"></i> ' + message +
                '</div>'
            );
        }

        // Load data when page is ready
        $(document).ready(function() {
            loadDashboardData();
        });
    </script>
@endsection
