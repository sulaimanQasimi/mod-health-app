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
                            <li class="breadcrumb-item active" aria-current="page">{{ localize('global.compare_charts') }}</li>
                        </ol>
                    </nav>
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0">{{ localize('global.compare_charts') }}</h2>
                        <div class="d-flex gap-2">
                            <a href="{{ route('dentist-registrations.show', $dentistRegistration) }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> {{ localize('global.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date Selectors -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('dental-charts.compare', $dentistRegistration) }}" class="row g-3">
                        <div class="col-md-5">
                            <label for="date1" class="form-label">{{ localize('global.date_1') }}</label>
                            <select name="date1" id="date1" class="form-select">
                                @foreach($chartDates as $date)
                                    <option value="{{ $date }}" {{ $date1 == $date ? 'selected' : '' }}>
                                        {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($date) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="date2" class="form-label">{{ localize('global.date_2') }}</label>
                            <select name="date2" id="date2" class="form-select">
                                @foreach($chartDates as $date)
                                    <option value="{{ $date }}" {{ $date2 == $date ? 'selected' : '' }}>
                                        {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($date) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-search"></i> {{ localize('global.compare') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Comparison View -->
            <div class="row">
                <!-- Date 1 Chart -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0 text-center">{{ localize('global.date') }} 1: {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($date1) }}</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $allTeeth1 = [];
                                for ($i = 11; $i <= 18; $i++) $allTeeth1[$i] = null;
                                for ($i = 21; $i <= 28; $i++) $allTeeth1[$i] = null;
                                for ($i = 31; $i <= 38; $i++) $allTeeth1[$i] = null;
                                for ($i = 41; $i <= 48; $i++) $allTeeth1[$i] = null;
                                foreach ($charts1 as $toothNumber => $chart) {
                                    $allTeeth1[$toothNumber] = $chart;
                                }
                            @endphp
                            @include('pages.dentist.charts.partials.tooth-chart', ['allTeeth' => $allTeeth1, 'dentistRegistration' => $dentistRegistration])
                        </div>
                    </div>
                </div>

                <!-- Date 2 Chart -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0 text-center">{{ localize('global.date') }} 2: {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($date2) }}</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $allTeeth2 = [];
                                for ($i = 11; $i <= 18; $i++) $allTeeth2[$i] = null;
                                for ($i = 21; $i <= 28; $i++) $allTeeth2[$i] = null;
                                for ($i = 31; $i <= 38; $i++) $allTeeth2[$i] = null;
                                for ($i = 41; $i <= 48; $i++) $allTeeth2[$i] = null;
                                foreach ($charts2 as $toothNumber => $chart) {
                                    $allTeeth2[$toothNumber] = $chart;
                                }
                            @endphp
                            @include('pages.dentist.charts.partials.tooth-chart', ['allTeeth' => $allTeeth2, 'dentistRegistration' => $dentistRegistration])
                        </div>
                    </div>
                </div>
            </div>

            <!-- Changes Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.changes_summary') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.tooth_number') }}</th>
                                    <th>{{ localize('global.date_1_condition') }}</th>
                                    <th>{{ localize('global.date_2_condition') }}</th>
                                    <th>{{ localize('global.change') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 11; $i <= 18; $i++)
                                    @php
                                        $chart1 = $charts1[$i] ?? null;
                                        $chart2 = $charts2[$i] ?? null;
                                        $condition1 = $chart1 ? $chart1->tooth_condition : 'no_data';
                                        $condition2 = $chart2 ? $chart2->tooth_condition : 'no_data';
                                        $changed = $condition1 !== $condition2;
                                    @endphp
                                    <tr class="{{ $changed ? 'table-warning' : '' }}">
                                        <td><strong>{{ $i }}</strong></td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $condition1)) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $condition2)) }}</td>
                                        <td>
                                            @if($changed)
                                                <span class="badge bg-warning">{{ localize('global.changed') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ localize('global.no_change') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endfor
                                @for($i = 21; $i <= 28; $i++)
                                    @php
                                        $chart1 = $charts1[$i] ?? null;
                                        $chart2 = $charts2[$i] ?? null;
                                        $condition1 = $chart1 ? $chart1->tooth_condition : 'no_data';
                                        $condition2 = $chart2 ? $chart2->tooth_condition : 'no_data';
                                        $changed = $condition1 !== $condition2;
                                    @endphp
                                    <tr class="{{ $changed ? 'table-warning' : '' }}">
                                        <td><strong>{{ $i }}</strong></td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $condition1)) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $condition2)) }}</td>
                                        <td>
                                            @if($changed)
                                                <span class="badge bg-warning">{{ localize('global.changed') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ localize('global.no_change') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endfor
                                @for($i = 31; $i <= 38; $i++)
                                    @php
                                        $chart1 = $charts1[$i] ?? null;
                                        $chart2 = $charts2[$i] ?? null;
                                        $condition1 = $chart1 ? $chart1->tooth_condition : 'no_data';
                                        $condition2 = $chart2 ? $chart2->tooth_condition : 'no_data';
                                        $changed = $condition1 !== $condition2;
                                    @endphp
                                    <tr class="{{ $changed ? 'table-warning' : '' }}">
                                        <td><strong>{{ $i }}</strong></td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $condition1)) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $condition2)) }}</td>
                                        <td>
                                            @if($changed)
                                                <span class="badge bg-warning">{{ localize('global.changed') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ localize('global.no_change') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endfor
                                @for($i = 41; $i <= 48; $i++)
                                    @php
                                        $chart1 = $charts1[$i] ?? null;
                                        $chart2 = $charts2[$i] ?? null;
                                        $condition1 = $chart1 ? $chart1->tooth_condition : 'no_data';
                                        $condition2 = $chart2 ? $chart2->tooth_condition : 'no_data';
                                        $changed = $condition1 !== $condition2;
                                    @endphp
                                    <tr class="{{ $changed ? 'table-warning' : '' }}">
                                        <td><strong>{{ $i }}</strong></td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $condition1)) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $condition2)) }}</td>
                                        <td>
                                            @if($changed)
                                                <span class="badge bg-warning">{{ localize('global.changed') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ localize('global.no_change') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
