@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif

        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h4 class="fw-bold mb-0">
                    <i class="bx bx-show me-2 text-primary"></i>{{ localize('global.ophthalmology_registrations') }}
                </h4>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-none border-0">
                <h6 class="mb-0 fw-semibold">
                    <i class="bx bx-filter-alt text-primary me-2"></i>{{ localize('global.filter') }}
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('ophthalmology-registrations.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ localize('global.search') }}</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                               placeholder="{{ localize('global.search_by_name') }} / {{ localize('global.ref_no') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ localize('global.status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ localize('global.all') }}</option>
                            @foreach (['pending', 'in_progress', 'completed', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>
                                    {{ localize('global.status_' . $status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ localize('global.examiner') }}</label>
                        <select name="examiner_id" class="form-select">
                            <option value="">{{ localize('global.all') }}</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->id }}" @selected(request('examiner_id') == $doctor->id)>
                                    {{ $doctor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-filter me-1"></i>{{ localize('global.filter') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ localize('global.number') }}</th>
                                <th>{{ localize('global.ref_no') }}</th>
                                <th>{{ localize('global.patient_name') }}</th>
                                <th>{{ localize('global.examiner') }}</th>
                                <th>{{ localize('global.registration_date') }}</th>
                                <th>{{ localize('global.status') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($registrations as $item)
                                @php
                                    $patient = $item->appointment?->patient;
                                    $patientName = trim(($patient?->name ?? '') . ' ' . ($patient?->last_name ?? ''));
                                    $statusClass = match ($item->status) {
                                        'pending' => 'warning',
                                        'in_progress' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $registrations->firstItem() + $loop->index }}</td>
                                    <td>{{ $item->ref_no }}</td>
                                    <td>{{ $patientName ?: '—' }}</td>
                                    <td>{{ $item->examiner?->name ?? '—' }}</td>
                                    <td>{{ $item->registration_date ? verta($item->registration_date)->format('Y/m/d') : '—' }}</td>
                                    <td><span class="badge bg-{{ $statusClass }}">{{ localize('global.status_' . $item->status) }}</span></td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('ophthalmology-registrations.show', $item) }}" class="btn btn-sm btn-outline-primary" title="{{ localize('global.show') }}">
                                            <i class="bx bx-show-alt"></i>
                                        </a>
                                        <a href="{{ route('ophthalmology-registrations.print', $item) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="{{ localize('global.print') }}">
                                            <i class="bx bx-printer"></i>
                                        </a>
                                        @can('delete-ophthalmology-registrations')
                                            <form action="{{ route('ophthalmology-registrations.destroy', $item) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('{{ localize('global.are_you_sure') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ localize('global.no_data_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $registrations->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
