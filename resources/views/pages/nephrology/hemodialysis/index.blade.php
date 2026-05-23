@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h2 class="h4 mb-0">{{ localize('global.hemodialysis') }}</h2>
                    <a href="{{ route('hemodialysis-sessions.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> {{ localize('global.add_hemodialysis_session') }}
                    </a>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.filters') }}</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('hemodialysis-sessions.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="patient_name" class="form-label">{{ localize('global.patient_name') }} / {{ localize('global.patient_id') }}</label>
                            <input type="text" class="form-control" id="patient_name" name="patient_name" value="{{ request('patient_name') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="session_date" class="form-label">{{ localize('global.session_date') }}</label>
                            <input type="date" class="form-control" id="session_date" name="session_date" value="{{ request('session_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="doctor_id" class="form-label">{{ localize('global.attending_nephrologist') }}</label>
                            <select class="form-select" id="doctor_id" name="doctor_id">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                                @endforeach
                            </select>
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
                        <div class="col-md-12 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary">{{ localize('global.search') }}</button>
                            <a href="{{ route('hemodialysis-sessions.index') }}" class="btn btn-secondary">{{ localize('global.reset') }}</a>
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
                                <th>{{ localize('global.diagnosis') }}</th>
                                <th>{{ localize('global.session_date') }}</th>
                                <th>{{ localize('global.session_time') }}</th>
                                <th>{{ localize('global.duration_minutes') }}</th>
                                <th>{{ localize('global.attending_nephrologist') }}</th>
                                <th>{{ localize('global.status') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions as $session)
                                <tr>
                                    <td>{{ $session->ref_no }}</td>
                                    <td>{{ $session->patient->patient_id ?? $session->patient_id }}</td>
                                    <td>{{ $session->patient->name ?? '' }} {{ $session->patient->last_name ?? '' }}</td>
                                    <td>{{ Str::limit($session->diagnosis, 40) ?: '—' }}</td>
                                    <td>{{ $session->session_date ? \HanifHefaz\Dcter\Dcter::GregorianToJalali($session->session_date) : '—' }}</td>
                                    <td>{{ $session->session_time ? \Carbon\Carbon::parse($session->session_time)->format('H:i') : '—' }}</td>
                                    <td>{{ $session->duration_minutes ?? '—' }}</td>
                                    <td>{{ $session->doctor->name ?? '—' }}</td>
                                    <td><span class="badge bg-info">{{ localize('global.' . $session->status) }}</span></td>
                                    <td>
                                        <a href="{{ route('hemodialysis-sessions.show', $session) }}" class="btn btn-sm btn-primary" title="{{ localize('global.view') }}">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('hemodialysis-sessions.edit', $session) }}" class="btn btn-sm btn-warning" title="{{ localize('global.edit') }}">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form action="{{ route('hemodialysis-sessions.destroy', $session) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('{{ localize('global.confirm_delete') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="{{ localize('global.delete') }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">{{ localize('global.no_hemodialysis_sessions_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $sessions->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
