@extends('layouts.master')

@section('content')
<div class="content-wrapper">
    @if (Session::has('success') || Session::has('error'))
    @include('components.toast')
    @endif

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ localize('global.lab_test_registrations') }}</h5>
            </div>

            <div class="card-datatable table-responsive p-3">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ localize('global.patient') }}</th>
                            <th>{{ localize('global.appointment') }}</th>
                            <th>{{ localize('global.test_name') }}</th>
                            <th>{{ localize('global.status') }}</th>
                            <th>{{ localize('global.priority') }}</th>
                            <th>{{ localize('global.doctor') }}</th>
                            <th>{{ localize('global.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tests as $test)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if($test->testable && $test->testable->patient)
                                    {{ $test->testable->patient->first_name }} {{ $test->testable->patient->last_name }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($test->testable)
                                    @if($test->testable->date)
                                        {{ \Carbon\Carbon::parse($test->testable->date)->format('M d, Y') }}
                                    @else
                                        —
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $test->labTest->name ?? '—' }}</td>
                            <td>
                                @php
                                    $statusClass = match($test->status) {
                                        'pending' => 'badge-warning',
                                        'in_progress' => 'badge-info',
                                        'completed' => 'badge-success',
                                        'cancelled' => 'badge-danger',
                                        default => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    {{ localize('global.status_' . $test->status) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $priorityClass = match($test->priority) {
                                        'normal' => 'badge-secondary',
                                        'urgent' => 'badge-warning',
                                        'stat' => 'badge-danger',
                                        default => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $priorityClass }}">
                                    {{ localize('global.' . $test->priority) }}
                                </span>
                            </td>
                            <td>{{ $test->doctor->name ?? '—' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($test->status === 'pending')
                                        <form action="{{ route('laboratory.registrations.mark-in-progress', $test->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-info" title="{{ localize('global.mark_in_progress') }}">
                                                <i class="bx bx-play"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($test->status === 'in_progress')
                                        <form action="{{ route('laboratory.registrations.mark-completed', $test->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="{{ localize('global.mark_completed') }}">
                                                <i class="bx bx-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if(in_array($test->status, ['pending', 'in_progress']))
                                        <form action="{{ route('laboratory.registrations.cancel', $test->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" title="{{ localize('global.cancel_registration') }}">
                                                <i class="bx bx-x"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <a href="{{ route('laboratory.results.show', $test->testable->patient->id ?? 0) }}" 
                                       class="btn btn-sm btn-outline-info" title="{{ localize('global.view_results') }}">
                                        <i class="bx bx-detail"></i>
                                    </a>
                                </div>
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
