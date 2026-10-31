@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            @php
                $backRoute = match ($bloodBank->status) {
                    'approved' => 'blood_banks.phlebotomy.approved',
                    'rejected' => 'blood_banks.phlebotomy.rejected',
                    'delivered' => 'blood_banks.phlebotomy.delivered',
                    default => 'blood_banks.phlebotomy.new',
                };
                $patientName = trim(($bloodBank->patient?->name ?? '') . ' ' . ($bloodBank->patient?->last_name ?? ''));
                $statusBadge = match ($bloodBank->status) {
                    'approved' => 'bg-label-success',
                    'delivered' => 'bg-label-primary',
                    'rejected' => 'bg-label-danger',
                    default => 'bg-label-warning',
                };
            @endphp

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ localize('global.phlebotomy_details') }}</h4>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <small class="text-muted">#{{ $bloodBank->id }}</small>
                        <span class="badge {{ $statusBadge }}">{{ localize('global.' . $bloodBank->status) }}</span>
                    </div>
                </div>
                <a href="{{ route($backRoute) }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">{{ localize('global.phlebotomy_request_summary') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.patient_name') }}</h6>
                            <div class="fw-semibold">{{ $patientName !== '' ? $patientName : '—' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.card_number') }}</h6>
                            <div class="fw-semibold">{{ $bloodBank->patient?->id_card ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.father_name') }}</h6>
                            <div class="fw-semibold">{{ $bloodBank->patient?->father_name ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.requested_department') }}</h6>
                            <div class="fw-semibold">{{ $bloodBank->department?->name ?? '—' }}</div>
                        </div>

                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.blood_group') }}</h6>
                            <div class="fw-semibold">
                                @if ($bloodBank->group == 'A')
                                    <span class="text-danger"><i class="fa-solid fa-a"></i></span>
                                @elseif ($bloodBank->group == 'B')
                                    <span class="text-danger"><i class="fa-solid fa-b"></i></span>
                                @elseif ($bloodBank->group == 'AB')
                                    <span class="text-danger" dir="ltr"><i class="fa-solid fa-a"></i><i class="fa-solid fa-b"></i></span>
                                @elseif ($bloodBank->group == 'O')
                                    <span class="text-danger"><i class="fa-solid fa-o"></i></span>
                                @else
                                    {{ $bloodBank->group ?? '—' }}
                                @endif
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.blood_rh') }}</h6>
                            <div class="fw-semibold">
                                @if ($bloodBank->rh == '+')
                                    <span class="bx bx-plus-circle text-danger"></span>
                                @elseif ($bloodBank->rh == '-')
                                    <span class="bx bx-minus-circle text-danger"></span>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.blood_type') }}</h6>
                            <div class="fw-semibold">{{ $bloodBank->type ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.quantity') }}</h6>
                            <div class="fw-semibold">{{ $bloodBank->quantity ?? '—' }}</div>
                        </div>

                        <div class="col-6 col-md-4">
                            <h6 class="mb-2 bg-label-info p-2 rounded">{{ localize('global.hemoglobin') }}</h6>
                            <div class="fw-semibold">{{ $bloodBank->hemoglobin ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <h6 class="mb-2 bg-label-info p-2 rounded">{{ localize('global.hematocrit') }}</h6>
                            <div class="fw-semibold">{{ $bloodBank->hematocrit ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <h6 class="mb-2 bg-label-info p-2 rounded">{{ localize('global.clotting_factor') }}</h6>
                            <div class="fw-semibold">{{ $bloodBank->factor ?? '—' }}</div>
                        </div>

                        <div class="col-6 col-md-4">
                            <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.status') }}</h6>
                            <div>
                                <span class="badge {{ $statusBadge }}">{{ localize('global.' . $bloodBank->status) }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.created_at') }}</h6>
                            <div class="fw-semibold" dir="ltr">
                                {{ \Hekmatinasser\Verta\Verta::instance($bloodBank->created_at)->format('Y/n/j H:i') }}
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.created_by') }}</h6>
                            <div class="fw-semibold">{{ $bloodBank->createdBy?->name ?? '—' }}</div>
                        </div>

                        @if ($bloodBank->reject_reason)
                            <div class="col-12">
                                <h6 class="mb-2 bg-label-danger p-2 rounded">{{ localize('global.reject_reason') }}</h6>
                                <div class="text-start border rounded p-3 bg-label-danger">{{ $bloodBank->reject_reason }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row g-4">
                @if ($bloodBank->bloodUnits->isNotEmpty())
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">{{ localize('global.phlebotomy_stock_units') }}</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.bag_number') }}</th>
                                            <th>{{ localize('global.blood_group') }}</th>
                                            <th>{{ localize('global.status') }}</th>
                                            <th>{{ localize('global.volume_ml') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($bloodBank->bloodUnits as $unit)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('blood_banks.inventory.show', $unit) }}">
                                                        {{ $unit->bag_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $unit->blood_group }}{{ $unit->rh }}</td>
                                                <td>{{ $unit->status }}</td>
                                                <td>{{ $unit->volume_ml ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($bloodBank->status === 'new')
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">{{ localize('global.actions') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between">
                                            <h6 class="mb-3">{{ localize('global.approve') }}</h6>
                                            <form method="POST" action="{{ route('blood_banks.phlebotomy.approve', $bloodBank) }}" class="mb-0">
                                                @csrf
                                                <button type="submit" class="btn btn-success w-100">
                                                    <i class="bx bx-check me-1"></i>{{ localize('global.approve') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="mb-3">{{ localize('global.reject') }}</h6>
                                            <form method="POST" action="{{ route('blood_banks.phlebotomy.reject', $bloodBank) }}" class="mb-0">
                                                @csrf
                                                @method('PUT')
                                                <div class="mb-3">
                                                    <label class="form-label" for="reject_reason">{{ localize('global.reject_reason') }}</label>
                                                    <textarea name="reject_reason" id="reject_reason" class="form-control" rows="3" required>{{ old('reject_reason') }}</textarea>
                                                </div>
                                                <button type="submit" class="btn btn-danger w-100">
                                                    <i class="bx bx-x me-1"></i>{{ localize('global.reject') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif ($bloodBank->status === 'approved')
                    <div class="col-12">
                        @canany(['receive-blood-units', 'manage-blood-inventory'])
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">{{ localize('global.phlebotomy_send_to_stock') }}</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-4">{{ localize('global.phlebotomy_send_to_stock_hint') }}</p>
                                    <form method="POST" action="{{ route('blood_banks.phlebotomy.send_to_stock', $bloodBank) }}">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-sm-6 col-xl-3">
                                                <label class="form-label" for="bag_number">{{ localize('global.bag_number') }}</label>
                                                <input type="text" name="bag_number" id="bag_number"
                                                       class="form-control @error('bag_number') is-invalid @enderror"
                                                       value="{{ old('bag_number') }}" required>
                                                @error('bag_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-sm-6 col-xl-3">
                                                <label class="form-label" for="volume_ml">{{ localize('global.volume_ml') }}</label>
                                                <input type="number" min="1" name="volume_ml" id="volume_ml" class="form-control"
                                                       value="{{ old('volume_ml', is_numeric($bloodBank->quantity) ? $bloodBank->quantity : '') }}">
                                            </div>
                                            <div class="col-sm-6 col-xl-3">
                                                <label class="form-label" for="collected_at">{{ localize('global.collected_at') }}</label>
                                                <input type="datetime-local" name="collected_at" id="collected_at" class="form-control"
                                                       value="{{ old('collected_at') }}">
                                            </div>
                                            <div class="col-sm-6 col-xl-3">
                                                <label class="form-label" for="expires_at">{{ localize('global.expires_at') }}</label>
                                                <input type="date" name="expires_at" id="expires_at" class="form-control"
                                                       value="{{ old('expires_at') }}" required>
                                            </div>
                                            <div class="col-12 col-xl-9">
                                                <label class="form-label" for="notes">{{ localize('global.notes') }}</label>
                                                <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                            </div>
                                            <div class="col-12 col-xl-3 d-flex align-items-end">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <i class="bx bx-package me-1"></i>{{ localize('global.phlebotomy_send_to_stock') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                {{ localize('global.phlebotomy_waiting_stock_permission') }}
                            </div>
                        @endcanany
                    </div>
                @elseif ($bloodBank->status === 'delivered')
                    <div class="col-12">
                        <div class="alert alert-success mb-0">
                            {{ localize('global.phlebotomy_completed_status') }}
                        </div>
                    </div>
                @elseif ($bloodBank->status === 'rejected')
                    <div class="col-12">
                        <div class="alert alert-secondary mb-0">
                            {{ localize('global.phlebotomy_rejected_status') }}
                        </div>
                    </div>
                @else
                    <div class="col-12">
                        <div class="alert alert-secondary mb-0">
                            {{ localize('global.phlebotomy_read_only_status') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
