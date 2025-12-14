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
                            <li class="breadcrumb-item active" aria-current="page">{{ localize('global.dentist_registrations') }}</li>
                        </ol>
                    </nav>
                    <h2 class="h4 mb-0">{{ localize('global.dentist_registrations') }}</h2>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.filters') }}</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('dentist-registrations.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="patient_name" class="form-label">{{ localize('global.patient_name') }}</label>
                            <input type="text" class="form-control" id="patient_name" name="patient_name" 
                                value="{{ request('patient_name') }}" placeholder="{{ localize('global.search_by_patient_name') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">{{ localize('global.status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">{{ localize('global.all') }}</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ localize('global.pending') }}</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ localize('global.in_progress') }}</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ localize('global.completed') }}</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ localize('global.cancelled') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="branch_id" class="form-label">{{ localize('global.branch') }}</label>
                            <select class="form-select" id="branch_id" name="branch_id">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="dentist_id" class="form-label">{{ localize('global.dentist') }}</label>
                            <select class="form-select" id="dentist_id" name="dentist_id">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($dentists as $dentist)
                                    <option value="{{ $dentist->id }}" {{ request('dentist_id') == $dentist->id ? 'selected' : '' }}>
                                        {{ $dentist->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 d-flex justify-content-end gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search"></i> {{ localize('global.search') }}
                            </button>
                            <a href="{{ route('dentist-registrations.index') }}" class="btn btn-secondary">
                                <i class="bx bx-refresh"></i> {{ localize('global.reset') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Registrations Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.dentist_registrations_list') }}</h5>
                </div>
                <div class="card-datatable table-responsive">
                    <table class="table border-top">
                        <thead>
                            <tr>
                                <th>{{ localize('global.ref_no') }}</th>
                                <th>{{ localize('global.patient_name') }}</th>
                                <th>{{ localize('global.appointment_date') }}</th>
                                <th>{{ localize('global.dentist') }}</th>
                                <th>{{ localize('global.registration_date') }}</th>
                                <th>{{ localize('global.status') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrations as $registration)
                                <tr>
                                    <td>{{ $registration->ref_no }}</td>
                                    <td>
                                        {{ $registration->appointment->patient->name ?? 'N/A' }}
                                        {{ $registration->appointment->patient->last_name ?? '' }}
                                    </td>
                                    <td>{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($registration->appointment->date) }}</td>
                                    <td>{{ $registration->dentist->name ?? 'N/A' }}</td>
                                    <td>{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($registration->registration_date) }}</td>
                                    <td>
                                        @if($registration->status == 'pending')
                                            <span class="badge bg-warning">{{ localize('global.pending') }}</span>
                                        @elseif($registration->status == 'in_progress')
                                            <span class="badge bg-info">{{ localize('global.in_progress') }}</span>
                                        @elseif($registration->status == 'completed')
                                            <span class="badge bg-success">{{ localize('global.completed') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ localize('global.cancelled') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('dentist-registrations.show', $registration) }}" class="btn btn-sm btn-primary">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            @can('edit-dentist-registrations')
                                            <a href="{{ route('dentist-registrations.edit', $registration) }}" class="btn btn-sm btn-warning">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            @endcan
                                            @can('delete-dentist-registrations')
                                            <form action="{{ route('dentist-registrations.destroy', $registration) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ localize('global.are_you_sure') }}')">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
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
