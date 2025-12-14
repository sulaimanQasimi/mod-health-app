@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">{{ localize('global.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dentist-registrations.show', $dentistRegistration) }}" class="text-decoration-none">{{ localize('global.registration_details') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ localize('global.dental_chart') }}</li>
                        </ol>
                    </nav>
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0">{{ localize('global.dental_chart') }}</h2>
                        <div class="d-flex gap-2">
                            <a href="{{ route('dentist-registrations.show', $dentistRegistration) }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> {{ localize('global.back') }}
                            </a>
                            <a href="{{ route('dental-charts.history', $dentistRegistration) }}" class="btn btn-info">
                                <i class="bx bx-history"></i> {{ localize('global.history') }}
                            </a>
                            <a href="{{ route('dental-charts.print', $dentistRegistration) }}" class="btn btn-warning" target="_blank">
                                <i class="bx bx-printer"></i> {{ localize('global.print') }}
                            </a>
                            <a href="{{ route('dental-charts.export', $dentistRegistration) }}" class="btn btn-success">
                                <i class="bx bx-download"></i> {{ localize('global.export_pdf') }}
                            </a>
                            <a href="{{ route('dental-charts.create', $dentistRegistration) }}" class="btn btn-primary">
                                <i class="bx bx-plus"></i> {{ localize('global.add_tooth_record') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="card mb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#chart-tab" type="button">
                                <i class="bx bx-grid-alt me-1"></i> {{ localize('global.chart') }}
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#images-tab" type="button">
                                <i class="bx bx-image me-1"></i> {{ localize('global.images') }}
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#periodontal-tab" type="button">
                                <i class="bx bx-pulse me-1"></i> {{ localize('global.periodontal') }}
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Chart Tab -->
                        <div class="tab-pane fade show active" id="chart-tab">
                            <div class="text-center mb-3">
                                <h5>{{ localize('global.visual_tooth_chart') }}</h5>
                            </div>
                            @include('pages.dentist.charts.partials.tooth-chart', ['allTeeth' => $allTeeth, 'dentistRegistration' => $dentistRegistration])
                            @vite('public/assets/js/vue/dental-chart-app.js')
                        </div>

                        <!-- Images Tab -->
                        <div class="tab-pane fade" id="images-tab">
                            @if($latestCharts->isNotEmpty() && $latestCharts->first())
                                @php
                                    $firstChart = $latestCharts->first();
                                    $imagesData = $firstChart->images->map(function($img) {
                                        return [
                                            'id' => $img->id,
                                            'image_path' => $img->image_path,
                                            'image_url' => $img->image_url,
                                            'image_type' => $img->image_type,
                                            'description' => $img->description,
                                        ];
                                    })->toArray();
                                    $imagesJson = json_encode($imagesData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                                    
                                    $measurementsData = $firstChart->periodontalMeasurements->map(function($m) {
                                        return [
                                            'id' => $m->id,
                                            'measurement_point' => $m->measurement_point,
                                            'pocket_depth' => $m->pocket_depth,
                                            'recession' => $m->recession,
                                            'bleeding' => $m->bleeding,
                                            'plaque' => $m->plaque,
                                            'measurement_date' => $m->measurement_date ? $m->measurement_date->format('Y-m-d') : null,
                                            'notes' => $m->notes,
                                        ];
                                    })->toArray();
                                    $measurementsJson = json_encode($measurementsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                                @endphp
                                <div id="image-gallery-container" 
                                     data-dental-chart-id="{{ $firstChart->id }}"
                                     data-images="{{ $imagesJson }}">
                                    <!-- Vue component will mount here -->
                                </div>
                                @vite('public/assets/js/vue/dental-chart-advanced-app.js')
                            @else
                                <p class="text-center text-muted">{{ localize('global.no_chart_data') }}</p>
                            @endif
                        </div>

                        <!-- Periodontal Tab -->
                        <div class="tab-pane fade" id="periodontal-tab">
                            @if($latestCharts->isNotEmpty() && $latestCharts->first())
                                @php
                                    $firstChart = $latestCharts->first();
                                    $measurementsData = $firstChart->periodontalMeasurements->map(function($m) {
                                        return [
                                            'id' => $m->id,
                                            'measurement_point' => $m->measurement_point,
                                            'pocket_depth' => $m->pocket_depth,
                                            'recession' => $m->recession,
                                            'bleeding' => $m->bleeding,
                                            'plaque' => $m->plaque,
                                            'measurement_date' => $m->measurement_date ? $m->measurement_date->format('Y-m-d') : null,
                                            'notes' => $m->notes,
                                        ];
                                    })->toArray();
                                    $measurementsJson = json_encode($measurementsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                                @endphp
                                <div id="periodontal-chart-container"
                                     data-dental-chart-id="{{ $firstChart->id }}"
                                     data-measurements="{{ $measurementsJson }}">
                                    <!-- Vue component will mount here -->
                                </div>
                                @vite('public/assets/js/vue/dental-chart-advanced-app.js')
                            @else
                                <p class="text-center text-muted">{{ localize('global.no_chart_data') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Details Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.chart_details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.tooth_number') }}</th>
                                    <th>{{ localize('global.condition') }}</th>
                                    <th>{{ localize('global.gum_health') }}</th>
                                    <th>{{ localize('global.oral_hygiene_score') }}</th>
                                    <th>{{ localize('global.pocket_depth') }}</th>
                                    <th>{{ localize('global.bleeding') }}</th>
                                    <th>{{ localize('global.mobility') }}</th>
                                    <th>{{ localize('global.chart_date') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestCharts as $chart)
                                    <tr>
                                        <td><strong>{{ $chart->tooth_number }}</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $chart->tooth_condition == 'healthy' ? 'success' : ($chart->tooth_condition == 'cavity' ? 'warning' : ($chart->tooth_condition == 'missing' ? 'secondary' : 'info')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $chart->tooth_condition)) }}
                                            </span>
                                        </td>
                                        <td>{{ $chart->gum_health ? ucfirst($chart->gum_health) : 'N/A' }}</td>
                                        <td>{{ $chart->oral_hygiene_score ?? 'N/A' }}</td>
                                        <td>{{ $chart->pocket_depth ? $chart->pocket_depth . ' mm' : 'N/A' }}</td>
                                        <td>
                                            @if($chart->bleeding)
                                                <span class="badge bg-danger">{{ localize('global.yes') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ localize('global.no') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $chart->mobility ? ucfirst($chart->mobility) : 'N/A' }}</td>
                                        <td>{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($chart->chart_date) }}</td>
                                        <td>
                                            <a href="{{ route('dental-charts.edit', $chart) }}" class="btn btn-sm btn-warning">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">{{ localize('global.no_charts_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

