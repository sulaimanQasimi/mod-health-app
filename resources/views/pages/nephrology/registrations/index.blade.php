@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="h4 mb-0">{{ localize('global.nephrology_registrations') }}</h2>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.filters') }}</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('nephrology-registrations.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="patient_name" class="form-label">{{ localize('global.patient_name') }}</label>
                            <input type="text" class="form-control" id="patient_name" name="patient_name" value="{{ request('patient_name') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">{{ localize('global.status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach(['pending', 'in_progress', 'completed', 'cancelled'] as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ localize('global.' . $status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="branch_id" class="form-label">{{ localize('global.branch') }}</label>
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="doctor_id" class="form-label">{{ localize('global.doctor') }}</label>
                            <select class="form-select" id="doctor_id" name="doctor_id">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">{{ localize('global.search') }}</button>
                            <a href="{{ route('nephrology-registrations.index') }}" class="btn btn-secondary">{{ localize('global.reset') }}</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-datatable table-responsive">
                    <table class="table border-top">
                        <thead>
                            <tr>
                                <th>{{ localize('global.ref_no') }}</th>
                                <th>{{ localize('global.patient_name') }}</th>
                                <th>{{ localize('global.visit_date') }}</th>
                                <th>{{ localize('global.doctor') }}</th>
                                <th>{{ localize('global.status') }}</th>
                                <th>{{ localize('global.diagnosis') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrations as $registration)
                                <tr>
                                    <td>{{ $registration->ref_no }}</td>
                                    <td>{{ $registration->patient->name ?? '' }} {{ $registration->patient->last_name ?? '' }}</td>
                                    <td>{{ $registration->visit_date ? \HanifHefaz\Dcter\Dcter::GregorianToJalali($registration->visit_date) : '—' }}</td>
                                    <td>{{ $registration->doctor->name ?? 'N/A' }}</td>
                                    <td><span class="badge bg-info">{{ localize('global.' . $registration->status) }}</span></td>
                                    <td>{{ Str::limit($registration->diagnosis, 40) ?: '—' }}</td>
                                    <td>
                                        <a href="{{ route('nephrology-registrations.show', $registration) }}" class="btn btn-sm btn-primary">
                                            <i class="bx bx-show"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ localize('global.no_registrations_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $registrations->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
