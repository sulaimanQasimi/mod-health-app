@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ localize('global.test_results') }} - {{ localize('global.patients') }}</h5>
            </div>

            <div class="card-body">
                @if($patients->count() > 0)
                    @foreach($patients as $patientId => $registrations)
                        @php
                            $patient = $registrations->first()->testable->patient ?? null;
                        @endphp
                        
                        @if($patient)
                        <div class="card mb-4">
                            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-0 text-white">
                                            <strong>{{ $patient->name }} {{ $patient->last_name }}</strong>
                                            <small class="d-block">{{ $patient->father_name }} | Age: {{ $patient->age }} | Phone: {{ $patient->phone }}</small>
                                        </h6>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <span class="badge bg-light text-dark">{{ $registrations->count() }} {{ localize('global.tests') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ localize('global.test_name') }}</th>
                                                <th>{{ localize('global.ref_no') }}</th>
                                                <th>{{ localize('global.status') }}</th>
                                                <th>{{ localize('global.priority') }}</th>
                                                <th>{{ localize('global.doctor') }}</th>
                                                <th>{{ localize('global.date') }}</th>
                                                <th>{{ localize('global.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($registrations as $registration)
                                            <tr>
                                                <td>
                                                    <strong>{{ $registration->labTest->name ?? '—' }}</strong>
                                                </td>
                                                <td>
                                                    <code>{{ $registration->ref_no }}</code>
                                                </td>
                                                <td>
                                                    @php
                                                        $statusClass = match($registration->status) {
                                                            'pending' => 'badge-warning',
                                                            'in_progress' => 'badge-info',
                                                            'completed' => 'badge-success',
                                                            'cancelled' => 'badge-danger',
                                                            default => 'badge-secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $statusClass }}">
                                                        {{ localize('global.' . $registration->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $priorityClass = match($registration->priority) {
                                                            'normal' => 'badge-primary',
                                                            'urgent' => 'badge-warning',
                                                            'stat' => 'badge-danger',
                                                            default => 'badge-secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $priorityClass }}">
                                                        {{ localize('global.' . $registration->priority) }}
                                                    </span>
                                                </td>
                                                <td>{{ $registration->doctor->name ?? '—' }}</td>
                                                <td>
                                                    @if($registration->testable && $registration->testable->date)
                                                        {{ \Carbon\Carbon::parse($registration->testable->date)->format('M d, Y') }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        @if($registration->status === 'pending')
                                                            <form action="{{ route('laboratory.registrations.mark-in-progress', $registration->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-info" title="{{ localize('global.mark_in_progress') }}">
                                                                    <i class="bx bx-play"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                        
                                                        @if($registration->status === 'in_progress')
                                                            <a href="{{ route('laboratory.results.show', $registration->id) }}" class="btn btn-sm btn-primary" title="{{ localize('global.enter_results') }}">
                                                                <i class="bx bx-edit"></i>
                                                            </a>
                                                            <form action="{{ route('laboratory.registrations.mark-completed', $registration->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success" title="{{ localize('global.mark_completed') }}">
                                                                    <i class="bx bx-check"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                        
                                                        @if($registration->status === 'completed')
                                                            <a href="{{ route('laboratory.results.show', $registration->id) }}" class="btn btn-sm btn-outline-primary" title="{{ localize('global.view_results') }}">
                                                                <i class="bx bx-show"></i>
                                                            </a>
                                                            <a href="{{ route('laboratory.reports.print', $registration->ref_no) }}" class="btn btn-sm btn-outline-success" title="{{ localize('global.print_report') }}" target="_blank">
                                                                <i class="bx bx-printer"></i>
                                                            </a>
                                                        @endif
                                                        
                                                        @if(in_array($registration->status, ['pending', 'in_progress']))
                                                            <form action="{{ route('laboratory.registrations.cancel', $registration->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-danger" title="{{ localize('global.cancel_registration') }}">
                                                                    <i class="bx bx-x"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <i class="bx bx-test-tube display-1 text-muted"></i>
                        <h5 class="mt-3 text-muted">{{ localize('global.no_test_registrations_found') }}</h5>
                        <p class="text-muted">{{ localize('global.no_test_registrations_message') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Add any specific JavaScript for the patients page here
    $(document).ready(function() {
        // Initialize tooltips
        $('[title]').tooltip();
    });
</script>
@endsection
