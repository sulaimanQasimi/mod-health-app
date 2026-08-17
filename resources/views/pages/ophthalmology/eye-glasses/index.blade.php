@extends('layouts.master')

@section('content')
@php
    $statusClass = fn ($status) => match ($status) {
        'requested' => 'warning',
        'processing' => 'info',
        'paid' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger',
        default => 'secondary',
    };
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif

        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h4 class="fw-bold mb-0">
                    <i class="bx bx-glasses me-2 text-primary"></i>{{ localize('global.eye_glasses_orders') }}
                </h4>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-2">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">{{ localize('global.all') }}</div>
                        <div class="fs-4 fw-semibold">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
            @foreach (['requested' => 'warning', 'processing' => 'info', 'paid' => 'primary', 'delivered' => 'success'] as $status => $color)
                <div class="col-sm-6 col-xl-2">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-muted small">{{ localize('global.eye_glasses_status_' . $status) }}</div>
                            <div class="fs-4 fw-semibold text-{{ $color }}">{{ $stats[$status] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-none border-0">
                <h6 class="mb-0 fw-semibold">
                    <i class="bx bx-filter-alt text-primary me-2"></i>{{ localize('global.filter') }}
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('eye-glasses-orders.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">{{ localize('global.search') }}</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                               placeholder="{{ localize('global.search_by_name') }} / {{ localize('global.ref_no') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ localize('global.status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ localize('global.all') }}</option>
                            @foreach (['requested', 'processing', 'paid', 'delivered', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>
                                    {{ localize('global.eye_glasses_status_' . $status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
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
                    <div class="col-md-2">
                        <label class="form-label">{{ localize('global.from_date') }}</label>
                        <input type="text" autocomplete="off" name="date_from" class="form-control datepicker_dari pdp-el"
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ localize('global.to_date') }}</label>
                        <input type="text" autocomplete="off" name="date_to" class="form-control datepicker_dari pdp-el"
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-filter"></i>
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
                                <th>{{ localize('global.eye_glasses_request_date') }}</th>
                                <th>{{ localize('global.eye_glasses_frame_type') }}</th>
                                <th>{{ localize('global.eye_glasses_lens_type') }}</th>
                                <th>{{ localize('global.status') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $item)
                                @php
                                    $patient = $item->appointment?->patient;
                                    $patientName = trim(($patient?->name ?? '') . ' ' . ($patient?->last_name ?? ''));
                                @endphp
                                <tr>
                                    <td>{{ $orders->firstItem() + $loop->index }}</td>
                                    <td>{{ $item->ref_no }}</td>
                                    <td>{{ $patientName ?: '—' }}</td>
                                    <td>{{ $item->examiner?->name ?? '—' }}</td>
                                    <td>{{ $item->request_date ? verta($item->request_date)->format('Y/m/d') : '—' }}</td>
                                    <td>{{ $item->frame_type ? localize('global.eye_glasses_frame_' . $item->frame_type) : '—' }}</td>
                                    <td>{{ $item->lens_type ? localize('global.eye_glasses_lens_' . $item->lens_type) : '—' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $statusClass($item->status) }}">
                                            {{ localize('global.eye_glasses_status_' . $item->status) }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('eye-glasses-orders.show', $item) }}" class="btn btn-sm btn-outline-primary" title="{{ localize('global.show') }}">
                                            <i class="bx bx-show-alt"></i>
                                        </a>
                                        <a href="{{ route('eye-glasses-orders.print', $item) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="{{ localize('global.print') }}">
                                            <i class="bx bx-printer"></i>
                                        </a>
                                        @can('delete-ophthalmology-registrations')
                                            <form action="{{ route('eye-glasses-orders.destroy', $item) }}" method="POST" class="d-inline"
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
                                    <td colspan="9" class="text-center">{{ localize('global.eye_glasses_no_orders') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
