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
                            <li class="breadcrumb-item"><a href="{{ route('dentist-registrations.index') }}" class="text-decoration-none">{{ localize('global.dentist_registrations') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dentist-registrations.show', $dentistRegistration) }}" class="text-decoration-none">{{ localize('global.registration_details') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ localize('global.dental_charts') }}</li>
                        </ol>
                    </nav>
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0">{{ localize('global.dental_charts') }}</h2>
                        <div class="d-flex gap-2">
                            <a href="{{ route('dentist-registrations.show', $dentistRegistration) }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> {{ localize('global.back') }}
                            </a>
                            <a href="{{ route('dentist-registrations.show', $dentistRegistration) }}?tab=dental-chart" class="btn btn-primary">
                                <i class="bx bx-show"></i> {{ localize('global.view_chart') }}
                            </a>
                            <a href="{{ route('dental-charts.create', $dentistRegistration) }}" class="btn btn-success">
                                <i class="bx bx-plus"></i> {{ localize('global.add_chart') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.dental_charts_list') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.tooth_number') }}</th>
                                    <th>{{ localize('global.tooth_name') }}</th>
                                    <th>{{ localize('global.condition') }}</th>
                                    <th>{{ localize('global.gum_health') }}</th>
                                    <th>{{ localize('global.oral_hygiene_score') }}</th>
                                    <th>{{ localize('global.pocket_depth') }}</th>
                                    <th>{{ localize('global.chart_date') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($charts as $chart)
                                    <tr>
                                        <td><strong>{{ $chart->tooth_number }}</strong></td>
                                        <td>{{ $chart->tooth_name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $chart->tooth_condition == 'healthy' ? 'success' : ($chart->tooth_condition == 'cavity' ? 'warning' : 'info') }}">
                                                {{ ucfirst(str_replace('_', ' ', $chart->tooth_condition)) }}
                                            </span>
                                        </td>
                                        <td>{{ $chart->gum_health ? ucfirst($chart->gum_health) : 'N/A' }}</td>
                                        <td>{{ $chart->oral_hygiene_score ?? 'N/A' }}</td>
                                        <td>{{ $chart->pocket_depth ? $chart->pocket_depth . ' mm' : 'N/A' }}</td>
                                        <td>{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($chart->chart_date) }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('dental-charts.edit', $chart) }}" class="btn btn-sm btn-warning">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <form action="{{ route('dental-charts.destroy', $chart) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ localize('global.are_you_sure') }}')">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">{{ localize('global.no_charts_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        {{ $charts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
