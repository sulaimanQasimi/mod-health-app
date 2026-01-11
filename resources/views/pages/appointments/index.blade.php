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
                    <form method="GET" action="{{ route('appointments.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="patient_name" class="form-label">{{ localize('global.patient_name') }}</label>
                            <input type="text" class="form-control" id="patient_name" name="patient_name" 
                                value="{{ request('patient_name') }}" placeholder="{{ localize('global.search_by_patient_name') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="id_card" class="form-label">{{ localize('global.id_card') }}</label>
                            <input type="text" class="form-control" id="id_card" name="id_card" 
                                value="{{ request('id_card') }}" placeholder="{{ localize('global.search_by_card') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="patient_id" class="form-label">{{ localize('global.patient_id') }}</label>
                            <input type="text" class="form-control" id="patient_id" name="patient_id" 
                                value="{{ request('patient_id') }}" placeholder="{{ localize('global.search_by_patient_id') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="doctor_id" class="form-label">{{ localize('global.doctor') }}</label>
                            <select class="form-select" id="doctor_id" name="doctor_id">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="department_id" class="form-label">{{ localize('global.department') }}</label>
                            <select class="form-select" id="department_id" name="department_id">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="is_completed" class="form-label">{{ localize('global.status') }}</label>
                            <select class="form-select" id="is_completed" name="is_completed">
                                <option value="">{{ localize('global.all') }}</option>
                                <option value="0" {{ request('is_completed') == '0' ? 'selected' : '' }}>{{ localize('global.pending') }}</option>
                                <option value="1" {{ request('is_completed') == '1' ? 'selected' : '' }}>{{ localize('global.completed') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">{{ localize('global.date_from') }}</label>
                            <input type="text" class="form-control datepicker_dari pdp-el" id="date_from" name="date_from" 
                                value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">{{ localize('global.date_to') }}</label>
                            <input type="text" class="form-control datepicker_dari pdp-el" id="date_to" name="date_to" 
                                value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-12 d-flex justify-content-end gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search"></i> {{ localize('global.search') }}
                            </button>
                            <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                                <i class="bx bx-refresh"></i> {{ localize('global.reset') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.appointments_list') }}</h5>
                </div>
                <div class="card-datatable table-responsive">
                    <table class="table border-top">
                        <thead>
                            <tr>
                                <th>{{ localize('global.id') }}</th>
                                <th>{{ localize('global.card_number') }}</th>
                                <th>{{ localize('global.patient_name') }}</th>
                                <th>{{ localize('global.father_name') }}</th>
                                <th>{{ localize('global.referred_to') }}</th>
                                <th>{{ localize('global.department') }}</th>
                                <th>{{ localize('global.date') }}</th>
                                <th>{{ localize('global.time') }}</th>
                                <th>{{ localize('global.status') }}</th>
                                <th>{{ localize('global.processed_by') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($appointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->id }}</td>
                                    <td>{{ $appointment->patient->id_card ?? '-' }}</td>
                                    <td>{{ $appointment->patient->name ?? '-' }}</td>
                                    <td>{{ $appointment->patient->father_name ?? '-' }}</td>
                                    <td>{{ $appointment->doctor->name ?? '-' }}</td>
                                    <td>{{ $appointment->department->name ?? '-' }}</td>
                                    <td>{{ $appointment->jalali_date ?? '-' }}</td>
                                    <td>{{ $appointment->time ?? '-' }}</td>
                                    <td>
                                        @if($appointment->is_completed == 1)
                                            <span class="badge bg-success">{{ localize('global.completed') }}</span>
                                        @else
                                            <span class="badge bg-warning">{{ localize('global.pending') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($appointment->processedBy)
                                            {{ $appointment->processedBy->name }} {{ $appointment->processedBy->last_name }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('appointments.show', $appointment->id) }}"
                                            class="btn btn-sm btn-icon text-primary"><i class="bx bx-expand"></i></a>
                                        @if($appointment->patient_id)
                                        <a href="{{ route('patients.history', $appointment->patient_id) }}"
                                            class="btn btn-sm btn-icon text-primary"><i class="bx bx-history"></i></a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">{{ localize('global.no_records_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="col-md-12 mt-4 mb-4">
                        {{ $appointments->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-backdrop fade"></div>
    </div>
@endsection

@push('custom-css')
    <style>
    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
            text-align: right;
        }

    .table td {
            text-align: right;
        }

    .pagination {
        margin-bottom: 0;
        }
    </style>
@endpush

@push('custom-js')
    <script>
$(document).ready(function() {
    // Auto-submit form when select values change
    $('select[name="doctor_id"], select[name="department_id"], select[name="is_completed"]').change(function() {
        $(this).closest('form').submit();
    });
    
    // Add loading state to search button
    $('form').submit(function() {
        $('.btn-primary').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i>');
    });
        });
    </script>
@endpush
