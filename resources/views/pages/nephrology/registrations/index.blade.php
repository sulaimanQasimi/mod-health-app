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
                        <div class="col-md-2">
                            <label for="patient_id" class="form-label">{{ localize('global.patient_id') }}</label>
                            <input type="text" class="form-control" id="patient_id" name="patient_id"
                                value="{{ request('patient_id') }}" placeholder="{{ localize('global.search_by_patient_id') }}">
                        </div>
                        <div class="col-md-2">
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
                        <div class="col-md-2">
                            <label for="visit_date_from" class="form-label">{{ localize('global.from_date') }}</label>
                            <input type="text" autocomplete="off" class="form-control datepicker_dari pdp-el" id="visit_date_from" name="visit_date_from"
                                value="{{ request('visit_date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="visit_date_to" class="form-label">{{ localize('global.to_date') }}</label>
                            <input type="text" autocomplete="off" class="form-control datepicker_dari pdp-el" id="visit_date_to" name="visit_date_to"
                                value="{{ request('visit_date_to') }}">
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
                                <th>{{ localize('global.patient_id') }}</th>
                                <th>{{ localize('global.patient_name') }}</th>
                                <th>{{ localize('global.father_name') }}</th>
                                <th>{{ localize('global.phone_number') }}</th>
                                <th>{{ localize('global.old') }}</th>
                                <th>{{ localize('global.gender') }}</th>
                                <th>{{ localize('global.visit_date') }}</th>
                                <th>{{ localize('global.doctor') }}</th>
                                <th>{{ localize('global.status') }}</th>
                                <th>{{ localize('global.diseases') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrations as $registration)
                                <tr>
                                    <td>{{ $registration->ref_no }}</td>
                                    <td>{{ $registration->patient->patient_id ?? '—' }}</td>
                                    <td>{{ $registration->patient->name ?? '' }} {{ $registration->patient->last_name ?? '' }}</td>
                                    <td>{{ $registration->patient->father_name ?? '—' }}</td>
                                    <td>{{ $registration->patient->phone ?? '—' }}</td>
                                    <td>{{ $registration->patient->age ?? '—' }}</td>
                                    <td>
                                        @if(isset($registration->patient->gender))
                                            @if($registration->patient->gender == 0 || $registration->patient->gender == '0')
                                                {{ localize('global.male') }}
                                            @elseif($registration->patient->gender == 1 || $registration->patient->gender == '1')
                                                {{ localize('global.female') }}
                                            @elseif($registration->patient->gender == 'male')
                                                {{ localize('global.male') }}
                                            @elseif($registration->patient->gender == 'female')
                                                {{ localize('global.female') }}
                                            @else
                                                {{ $registration->patient->gender }}
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $registration->visit_date ? \HanifHefaz\Dcter\Dcter::GregorianToJalali($registration->visit_date) : '—' }}</td>
                                    <td>{{ $registration->doctor->name ?? localize('global.not_available') }}</td>
                                    <td><span class="badge bg-info">{{ localize('global.' . $registration->status) }}</span></td>
                                    <td>{{ $registration->displayDiagnosis() ?? '—' }}</td>
                                    <td>
                                        <div class="d-flex gap-1 flex-wrap">
                                            @if($registration->needsAcceptance())
                                                <form action="{{ route('nephrology-registrations.accept', $registration) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="{{ localize('global.accept') }}">
                                                        <i class="bx bx-check me-1"></i>{{ localize('global.accept') }}
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('nephrology-registrations.show', $registration) }}" class="btn btn-sm btn-primary" title="{{ localize('global.show') }}">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">{{ localize('global.no_registrations_found') }}</td>
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
