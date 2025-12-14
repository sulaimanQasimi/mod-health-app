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
                            <li class="breadcrumb-item active" aria-current="page">{{ localize('global.chart_history') }}</li>
                        </ol>
                    </nav>
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0">{{ localize('global.chart_history') }}</h2>
                        <div class="d-flex gap-2">
                            <a href="{{ route('dentist-registrations.show', $dentistRegistration) }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> {{ localize('global.back') }}
                            </a>
                            <a href="{{ route('dental-charts.compare', $dentistRegistration) }}" class="btn btn-info">
                                <i class="bx bx-git-compare"></i> {{ localize('global.compare_dates') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date Selector -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('dental-charts.history', $dentistRegistration) }}" class="row g-3">
                        <div class="col-md-6">
                            <label for="date" class="form-label">{{ localize('global.select_date') }}</label>
                            <select name="date" id="date" class="form-select" onchange="this.form.submit()">
                                @foreach($chartDates as $date)
                                    <option value="{{ $date }}" {{ $selectedDate == $date ? 'selected' : '' }}>
                                        {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($date) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search"></i> {{ localize('global.view_chart') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.chart_timeline') }}</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($chartDates as $date)
                            <div class="timeline-item {{ $selectedDate == $date ? 'active' : '' }}">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <a href="{{ route('dental-charts.history', ['dentistRegistration' => $dentistRegistration->id, 'date' => $date]) }}" 
                                       class="text-decoration-none">
                                        <h6>{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($date) }}</h6>
                                        <p class="text-muted small mb-0">
                                            {{ $dentistRegistration->dentalCharts()->where('chart_date', $date)->count() }} {{ localize('global.teeth_recorded') }}
                                        </p>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Visual Tooth Chart for Selected Date -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0 text-center">{{ localize('global.visual_tooth_chart') }} - {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($selectedDate) }}</h5>
                </div>
                <div class="card-body">
                    @include('pages.dentist.charts.partials.tooth-chart', ['allTeeth' => $allTeeth, 'dentistRegistration' => $dentistRegistration])
                    @vite('public/assets/js/vue/dental-chart-app.js')
                </div>
            </div>

            <!-- Chart Details Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.chart_details') }} - {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($selectedDate) }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.tooth_number') }}</th>
                                    <th>{{ localize('global.condition') }}</th>
                                    <th>{{ localize('global.gum_health') }}</th>
                                    <th>{{ localize('global.pocket_depth') }}</th>
                                    <th>{{ localize('global.notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($charts as $chart)
                                    <tr>
                                        <td><strong>{{ $chart->tooth_number }}</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $chart->tooth_condition == 'healthy' ? 'success' : ($chart->tooth_condition == 'cavity' ? 'warning' : ($chart->tooth_condition == 'missing' ? 'secondary' : 'info')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $chart->tooth_condition)) }}
                                            </span>
                                        </td>
                                        <td>{{ $chart->gum_health ? ucfirst($chart->gum_health) : 'N/A' }}</td>
                                        <td>{{ $chart->pocket_depth ? $chart->pocket_depth . ' mm' : 'N/A' }}</td>
                                        <td>{{ Str::limit($chart->notes, 50) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{ localize('global.no_charts_found') }}</td>
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

@section('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 30px;
    padding-left: 30px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 5px;
    top: 20px;
    bottom: -10px;
    width: 2px;
    background: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #6c757d;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-item.active .timeline-marker {
    background: #0d6efd;
    box-shadow: 0 0 0 2px #0d6efd;
}

.timeline-content h6 {
    margin-bottom: 5px;
    color: #333;
}

.timeline-item.active .timeline-content h6 {
    color: #0d6efd;
    font-weight: 600;
}
</style>
@endsection
