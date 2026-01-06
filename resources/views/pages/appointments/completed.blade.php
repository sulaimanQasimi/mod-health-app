
@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <!-- Content -->
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.patients_list') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover border-top">
                            <thead>
                                <tr>
                                    <th>{{localize('global.id')}}</th>
                                    <th>{{localize('global.card_number')}}</th>
                                    <th>{{localize('global.patient_name')}}</th>
                                    <th>{{localize('global.father_name')}}</th>
                                    <th>{{localize('global.referred_to')}}</th>
                                    <th>{{localize('global.date')}}</th>
                                    <th>{{localize('global.time')}}</th>
                                    <th>{{localize('global.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($appointments as $appointment)
                                    <tr>
                                        <td>{{ $appointment->id }}</td>
                                        <td>{{ $appointment->patient->id_card ?? '' }}</td>
                                        <td>{{ $appointment->patient->name ?? '' }}</td>
                                        <td>{{ $appointment->patient->father_name ?? '' }}</td>
                                        <td>{{ $appointment->doctor->name ?? '' }}</td>
                                        <td>{{ $appointment->jalali_date ?? '' }}</td>
                                        <td>{{ $appointment->time ?? '' }}</td>
                                        <td>
                                            <a href="{{ url('appointments/show/') }}/{{ $appointment->id }}" class="btn btn-sm btn-icon text-primary">
                                                <i class="bx bx-expand"></i>
                                            </a>
                                            <a href="{{ url('patients/history/') }}/{{ $appointment->patient_id }}" class="btn btn-sm btn-icon item-edit text-primary">
                                                <i class="bx bx-history"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">{{ localize('global.no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($appointments->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $appointments->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="content-backdrop fade"></div>
    </div>
@endsection

@push('custom-css')
    <style>
        .table thead th {
            text-align: right;
        }

        .table tbody td {
            text-align: right;
        }
    </style>
@endpush
