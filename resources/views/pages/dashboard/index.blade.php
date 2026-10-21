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

    /* Keep dashboard stat cards the same width/height */
    #dashboard-stat-cards > [class*="col-"] {
        display: flex;
    }

    #dashboard-stat-cards .card {
        width: 100%;
        height: 100%;
    }

    #dashboard-stat-cards .card-body {
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    #dashboard-stat-cards .card-body > .d-flex {
        flex: 1;
        width: 100%;
        gap: 0.5rem;
    }

    #dashboard-stat-cards .content-left {
        flex: 1;
        min-width: 0;
    }

    #dashboard-stat-cards .content-left > h4:first-of-type {
        font-size: 1.05rem;
        line-height: 1.35;
        margin-bottom: 0;
        min-height: calc(1.35em * 2);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    #dashboard-stat-cards .content-left > p {
        font-size: 0.8125rem;
        line-height: 1.35;
        min-height: calc(1.35em * 2);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    #dashboard-stat-cards .avatar {
        flex-shrink: 0;
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
            @php
                $hasAnyWidget = collect($visible ?? [])->contains(true);
            @endphp
            <div id="dashboard-empty" class="alert alert-info {{ $hasAnyWidget ? 'd-none' : '' }}" role="alert">
                <i class="bx bx-info-circle me-1"></i>
                {{ localize('global.dashboard_no_widgets') ?? 'No dashboard widgets are available for your role.' }}
            </div>
            <div class="row" id="dashboard-widgets" @if(!$hasAnyWidget) style="display:none" @endif>
                <div class="col-md-12 order-3 order-md-2">
                    <div class="row g-4 mb-4" id="dashboard-stat-cards">

                        @if(!empty($visible['today_patients']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="today_patients">
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
                        @endif
                        @if(!empty($visible['emergency_today_patients']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="emergency_today_patients">
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
                        @endif
                        @if(!empty($visible['all_patients']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="all_patients">
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
                        @endif

                        @if(!empty($visible['all_appointments']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="all_appointments">
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
                        @endif

                        @if(!empty($visible['consultations']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="consultations">
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
                        @endif

                        @if(!empty($visible['hospitalizations']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="hospitalizations">
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
                        @endif

                        @if(!empty($visible['checkups']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="checkups">
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
                        @endif

                        @if(!empty($visible['icu']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="icu">
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
                        @endif

                        @if(!empty($visible['ccu']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="ccu">
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
                        @endif

                        @if(!empty($visible['prescriptions']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="prescriptions">
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
                        @endif

                        @if(!empty($visible['operations']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="operations">
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
                        @endif

                        @if(!empty($visible['physiotherapy']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="physiotherapy">
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
                        @endif

                        @if(!empty($visible['depot']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="depot">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_depots') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 p-1 rounded" id="total-depots">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_depots') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-secondary">
                                                <i class="bx bx-package bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(!empty($visible['depot']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="depot">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.all_depot_transactions') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 p-1 rounded" id="total-depot-transactions">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_registered_depot_transactions') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-indigo">
                                                <i class="bx bx-transfer bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(!empty($visible['depot']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="depot">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.pending_depot_requests') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 p-1 rounded" id="pending-depot-requests">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_pending_depot_requests') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-orange">
                                                <i class="bx bx-time-five bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(!empty($visible['depot']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="depot">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.depot_stock_items') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 p-1 rounded" id="depot-stock-items">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_depot_stock_items') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-cyan">
                                                <i class="bx bx-list-ul bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(!empty($visible['depot']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="depot">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.depot_stock_quantity') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 p-1 rounded" id="depot-stock-quantity">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_depot_stock_quantity') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-teal">
                                                <i class="bx bx-cube bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(!empty($visible['depot']))
                        <div class="col-sm-6 col-xl-3 dash-vis" data-vis="depot">
                            <div class="card border border-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="content-left">
                                            <h4>{{ localize('global.depot_low_stock') }}</h4>
                                            <div class="d-flex align-items-end mt-2">
                                                <h4 class="mb-0 me-2 p-1 rounded text-warning" id="depot-low-stock">-</h4>
                                            </div>
                                            <p class="mb-0">{{ localize('global.all_depot_low_stock') }}</p>
                                        </div>
                                        <div class="avatar p-4">
                                            <span class="avatar-initial rounded-circle bg-warning">
                                                <i class="bx bx-error bx-md text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @if(!empty($visible['beds']))
                    <div class="row g-3 mb-4 dash-vis" data-vis="beds">
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
                    @endif
                    <div class="row">
                        @if(!empty($visible['patients_trend']))
                        <div class="col-md-6 dash-vis" data-vis="patients_trend">
                            <div class="card">
                                <div class="card-body">
                                    <i class="bx bx-line-chart text-primary"></i>
                                    <h5 class="card-title text-center">{{ localize('global.patients_comparison_graph') }}
                                    </h5>
                                    <canvas id="patientsTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(!empty($visible['appointments_trend']))
                        <div class="col-md-6 dash-vis" data-vis="appointments_trend">
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
                        @endif
                    </div>

                    @if(!empty($visible['appointments_by_user']))
                    <div class="col-md-12 mt-3 dash-vis" data-vis="appointments_by_user">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                    <h5 class="card-title mb-0">
                                        <i class="bx bx-user-check text-primary me-1"></i>
                                        {{ localize('global.appointments_processed_by_user') }}
                                    </h5>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="form-label mb-0 text-nowrap">{{ localize('global.filter_by_branch') }}:</label>
                                        <select id="chart-branch-select" class="form-select form-select-sm" style="min-width: 180px;">
                                            <option value="">{{ localize('global.loading') }}...</option>
                                        </select>
                                    </div>
                                </div>
                                <canvas id="appointmentsByUserChart" height="280"></canvas>
                                <p class="text-muted small mb-0 mt-2 text-center">{{ localize('global.appointments_processed_by_user_hint') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(!empty($visible['doctors_activity']))
                    <div class="col-md-12 mt-3 dash-vis" data-vis="doctors_activity">
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
                    @endif

                    @if(!empty($visible['nurses_activity']))
                    <div class="col-md-12 mt-3 dash-vis" data-vis="nurses_activity">
                        <div class="card">
                            <div class="card-body">
                                <i class="bx bx-line-chart text-primary"></i>
                                <h5 class="card-title text-center">
                                    {{ localize('global.nurses_activity_graph') }}
                                </h5>
                                <canvas id="nurseActivityChart" height="280"></canvas>
                            </div>
                        </div>
                    </div>
                    @endif

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
        let appointmentsByUserChart = null;
        let nurseActivityChart = null;
        let dashboardVisible = @json($visible ?? []);

        function applyVisibility(visible) {
            dashboardVisible = visible || {};
            var anyVisible = false;
            document.querySelectorAll('.dash-vis').forEach(function(el) {
                var key = el.getAttribute('data-vis');
                var show = !!dashboardVisible[key];
                el.style.display = show ? '' : 'none';
                if (show) anyVisible = true;
            });
            // Also hide widgets that were never rendered server-side
            if (!anyVisible) {
                anyVisible = Object.keys(dashboardVisible).some(function(k) { return !!dashboardVisible[k]; });
            }
            $('#dashboard-empty').toggleClass('d-none', anyVisible);
            $('#dashboard-widgets').toggle(anyVisible);
        }

        function needsCharts() {
            return !!(dashboardVisible.patients_trend
                || dashboardVisible.appointments_trend
                || dashboardVisible.appointments_by_user
                || dashboardVisible.doctors_activity
                || dashboardVisible.nurses_activity);
        }

        function fetchDashboardSection(section, chartBranchId) {
            var params = { section: section };
            if (chartBranchId) params.chart_branch_id = chartBranchId;
            return $.ajax({
                url: '{{ route("home") }}',
                type: 'GET',
                data: params,
                dataType: 'json',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
        }

        function updateStatCards(data) {
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
            $('#total-depots').text(data.totalDepots || 0);
            $('#total-depot-transactions').text(data.totalDepotTransactions || 0);
            $('#pending-depot-requests').text(data.pendingDepotRequests || 0);
            $('#depot-stock-items').text(data.depotStockItems || 0);
            $('#depot-stock-quantity').text(data.depotStockQuantity || 0);
            $('#depot-low-stock').text(data.depotLowStock || 0);
            $('#occupied-beds').text(data.occupied_beds || 0);
            $('#all-beds').text(data.all_beds || 0);
            $('#free-beds').text(data.free_beds || 0);
        }

        function populateBranchFilter(data) {
            var $branchSelect = $('#chart-branch-select');
            if (!$branchSelect.length) return;
            $branchSelect.empty();
            if (data.branches && data.branches.length) {
                data.branches.forEach(function(b) {
                    $branchSelect.append($('<option></option>').attr('value', b.id).text(b.name));
                });
                $branchSelect.val(data.chartBranchId || '');
            } else {
                $branchSelect.append($('<option value="">—</option>'));
            }
            $branchSelect.off('change').on('change', function() {
                var branchId = $(this).val();
                if (!branchId) return;
                fetchDashboardSection('appointments_by_user', branchId).done(function(response) {
                    if (response.success && response.data) {
                        if (response.data.visible) applyVisibility(response.data.visible);
                        populateBranchFilter(response.data);
                        renderAppointmentsByUserChart(response.data);
                    }
                });
            });
        }

        function renderAppointmentsByUserChart(data) {
            if (appointmentsByUserChart) {
                appointmentsByUserChart.destroy();
                appointmentsByUserChart = null;
            }
            var byUserCtx = document.getElementById('appointmentsByUserChart');
            if (!byUserCtx || !data.appointmentsByUserData) return;
            var labels = data.appointmentsByUserData.labels || [];
            var counts = data.appointmentsByUserData.data || [];
            if (!(labels.length && counts.length)) return;

            appointmentsByUserChart = new Chart(byUserCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: "{{ localize('global.appointments_processed') }}",
                        data: counts,
                        backgroundColor: 'rgba(13, 202, 240, 0.6)',
                        borderColor: 'rgba(13, 202, 240, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        x: { beginAtZero: true },
                        y: { ticks: { maxRotation: 0, autoSkip: false } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                afterLabel: function(ctx) {
                                    var total = counts.reduce(function(a, b) { return a + b; }, 0);
                                    var pct = total ? Math.round((ctx.raw / total) * 100) : 0;
                                    return (pct + '% of total');
                                }
                            }
                        }
                    }
                }
            });
        }

        function loadDashboardData() {
            fetchDashboardSection('meta')
                .done(function(metaResponse) {
                    if (!(metaResponse.success && metaResponse.data)) {
                        showError('Failed to load dashboard data');
                        return;
                    }

                    applyVisibility(metaResponse.data.visible);
                    $('#dashboard-loading').hide();
                    $('#dashboard-content').fadeIn();

                    fetchDashboardSection('summary').done(function(summaryResponse) {
                        if (summaryResponse.success && summaryResponse.data) {
                            if (summaryResponse.data.visible) applyVisibility(summaryResponse.data.visible);
                            updateStatCards(summaryResponse.data);
                        }
                    });

                    if (needsCharts()) {
                        fetchDashboardSection('charts').done(function(chartsResponse) {
                            if (chartsResponse.success && chartsResponse.data) {
                                if (chartsResponse.data.visible) applyVisibility(chartsResponse.data.visible);
                                populateBranchFilter(chartsResponse.data);
                                initializeCharts(chartsResponse.data);
                            }
                        });
                    }
                })
                .fail(function(xhr, status, error) {
                    console.error('Error loading dashboard:', error);
                    showError('Error loading dashboard data. Please refresh the page.');
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

            renderAppointmentsByUserChart(data);

            // Nurses activity chart (horizontal bar chart – compares total activity per nurse)
            if (nurseActivityChart) {
                nurseActivityChart.destroy();
            }

            var nurseActivityCtx = document.getElementById('nurseActivityChart');
            if (nurseActivityCtx && data.nurseActivityData) {
                var nurseLabels = data.nurseActivityData.labels || [];
                var nurseCounts = data.nurseActivityData.data || [];
                if (nurseLabels.length && nurseCounts.length) {
                    nurseActivityChart = new Chart(nurseActivityCtx, {
                        type: 'bar',
                        data: {
                            labels: nurseLabels,
                            datasets: [{
                                label: "{{ localize('global.nurse_activity_count') }}",
                                data: nurseCounts,
                                backgroundColor: 'rgba(32, 201, 151, 0.6)',
                                borderColor: 'rgba(32, 201, 151, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: true,
                            scales: {
                                x: { beginAtZero: true },
                                y: {
                                    ticks: { maxRotation: 0, autoSkip: false }
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        afterLabel: function(ctx) {
                                            var total = nurseCounts.reduce(function(a, b) { return a + b; }, 0);
                                            var pct = total ? Math.round((ctx.raw / total) * 100) : 0;
                                            return (pct + '% of activity');
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
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
