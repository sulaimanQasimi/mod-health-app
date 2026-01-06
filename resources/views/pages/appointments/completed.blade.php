
@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <!-- Content -->
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">

            <!-- Filters Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.filters') }}</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('appointments.completedAppointments') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="token_id" class="form-label">{{ localize('global.token_id') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bx bx-hash"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       name="token_id" 
                                       id="token_id"
                                       placeholder="{{ localize('global.search_by_token_id') }}"
                                       value="{{ request('token_id') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="patient_id" class="form-label">{{ localize('global.patient_id') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bx bx-user"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       name="patient_id" 
                                       id="patient_id"
                                       placeholder="{{ localize('global.search_by_patient_id') }}"
                                       value="{{ request('patient_id') }}">
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-search me-1"></i>{{ localize('global.search') }}
                            </button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="{{ route('appointments.completedAppointments') }}" class="btn btn-secondary w-100">
                                <i class="bx bx-refresh me-1"></i>{{ localize('global.reset') }}
                            </a>
                        </div>
                        @if(request('token_id') || request('patient_id'))
                            <div class="col-12">
                                <small class="text-muted">
                                    <i class="bx bx-info-circle me-1"></i>
                                    {{ localize('global.active_filters') }}: 
                                    @if(request('token_id'))
                                        <span class="badge bg-info">{{ localize('global.token_id') }}: {{ request('token_id') }}</span>
                                    @endif
                                    @if(request('patient_id'))
                                        <span class="badge bg-info">{{ localize('global.patient_id') }}: {{ request('patient_id') }}</span>
                                    @endif
                                </small>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

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

@push('custom-js')
    <script>
        $(document).ready(function() {
            // Allow Enter key to submit form
            $('#token_id, #patient_id').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $(this).closest('form').submit();
                }
            });
        });
    </script>
@endpush
