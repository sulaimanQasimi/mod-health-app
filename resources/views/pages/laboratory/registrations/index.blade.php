@extends('layouts.master')

@section('content')
<div class="content-wrapper">
    @if (Session::has('success') || Session::has('error'))
    @include('components.toast')
    @endif

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ localize('global.patients_with_lab_tests') }}</h5>
            </div>

            <div class="card-datatable table-responsive p-3">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ localize('global.patient') }}</th>
                            <th>{{ localize('global.phone') }}</th>
                            <th>{{ localize('global.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patients as $patientItem)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td style="display:none;">{{ $patientItem->patient->id }}</td>
                            <td>{{ $patientItem->patient->name ?? '—' }}</td>
                            <td>{{ $patientItem->patient->phone ?? '—' }}</td>
                            <td>
                                <a href="{{ route('laboratory.results.show', $patientItem->patient->id) }}"
                                    class="btn btn-sm btn-outline-info">
                                    <i class="bx bx-detail"></i> {{ localize('global.view_results') }}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>

<div class="content-backdrop fade"></div>
@endsection

@push('custom-css')
<style>
    .table th,
    .table td {
        text-align: right;
    }
</style>
@endpush
