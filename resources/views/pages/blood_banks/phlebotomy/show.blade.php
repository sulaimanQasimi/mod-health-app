@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="mb-1">{{ localize('global.phlebotomy_details') }}</h4>
                <small class="text-muted">#{{ $bloodBank->id }}</small>
            </div>
            @php
                $backRoute = match ($bloodBank->status) {
                    'approved' => 'blood_banks.phlebotomy.approved',
                    'rejected' => 'blood_banks.phlebotomy.rejected',
                    'delivered' => 'blood_banks.phlebotomy.delivered',
                    default => 'blood_banks.phlebotomy.new',
                };
            @endphp
            <a href="{{ route($backRoute) }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
            </a>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">{{ localize('global.phlebotomy_request_summary') }}</h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">{{ localize('global.patient_name') }}</dt>
                            <dd class="col-sm-8">
                                {{ trim(($bloodBank->patient?->name ?? '').' '.($bloodBank->patient?->last_name ?? '')) ?: '—' }}
                            </dd>
                            <dt class="col-sm-4">{{ localize('global.card_number') }}</dt>
                            <dd class="col-sm-8">{{ $bloodBank->patient?->id_card ?? '—' }}</dd>
                            <dt class="col-sm-4">{{ localize('global.department') }}</dt>
                            <dd class="col-sm-8">{{ $bloodBank->department?->name ?? '—' }}</dd>
                            <dt class="col-sm-4">{{ localize('global.blood_group') }}</dt>
                            <dd class="col-sm-8">{{ $bloodBank->group ?? '—' }}</dd>
                            <dt class="col-sm-4">{{ localize('global.rh') }}</dt>
                            <dd class="col-sm-8">{{ $bloodBank->rh ?? '—' }}</dd>
                            <dt class="col-sm-4">{{ localize('global.blood_type') }}</dt>
                            <dd class="col-sm-8">{{ $bloodBank->type ?? '—' }}</dd>
                            <dt class="col-sm-4">{{ localize('global.quantity') }} / {{ localize('global.size') }}</dt>
                            <dd class="col-sm-8">{{ $bloodBank->quantity ?? '—' }}</dd>
                            <dt class="col-sm-4">{{ localize('global.hemoglobin') }}</dt>
                            <dd class="col-sm-8">{{ $bloodBank->hemoglobin ?? '—' }}</dd>
                            <dt class="col-sm-4">{{ localize('global.status') }}</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-label-secondary">{{ $bloodBank->status }}</span>
                            </dd>
                            @if ($bloodBank->reject_reason)
                                <dt class="col-sm-4">{{ localize('global.reject_reason') }}</dt>
                                <dd class="col-sm-8">{{ $bloodBank->reject_reason }}</dd>
                            @endif
                            <dt class="col-sm-4">{{ localize('global.created_at') }}</dt>
                            <dd class="col-sm-8">{{ $bloodBank->created_at }}</dd>
                        </dl>
                    </div>
                </div>

                @if ($bloodBank->bloodUnits->isNotEmpty())
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">{{ localize('global.phlebotomy_stock_units') }}</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
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
                                            <td>{{ $unit->bag_number }}</td>
                                            <td>{{ $unit->blood_group }}{{ $unit->rh }}</td>
                                            <td>{{ $unit->status }}</td>
                                            <td>{{ $unit->volume_ml ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-5">
                @if ($bloodBank->status === 'new')
                    <div class="card mb-3">
                        <div class="card-header"><h6 class="mb-0">{{ localize('global.actions') }}</h6></div>
                        <div class="card-body d-flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('blood_banks.phlebotomy.approve', $bloodBank) }}">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bx bx-check me-1"></i>{{ localize('global.approve') }}
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">{{ localize('global.reject') }}</h6></div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('blood_banks.phlebotomy.reject', $bloodBank) }}">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label" for="reject_reason">{{ localize('global.reject_reason') }}</label>
                                    <textarea name="reject_reason" id="reject_reason" class="form-control" rows="3" required>{{ old('reject_reason') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bx bx-x me-1"></i>{{ localize('global.reject') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @elseif ($bloodBank->status === 'approved')
                    @canany(['receive-blood-units', 'manage-blood-inventory'])
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">{{ localize('global.phlebotomy_send_to_stock') }}</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">{{ localize('global.phlebotomy_send_to_stock_hint') }}</p>
                                <form method="POST" action="{{ route('blood_banks.phlebotomy.send_to_stock', $bloodBank) }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label" for="bag_number">{{ localize('global.bag_number') }}</label>
                                        <input type="text" name="bag_number" id="bag_number"
                                               class="form-control @error('bag_number') is-invalid @enderror"
                                               value="{{ old('bag_number') }}" required>
                                        @error('bag_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="volume_ml">{{ localize('global.volume_ml') }}</label>
                                        <input type="number" min="1" name="volume_ml" id="volume_ml" class="form-control"
                                               value="{{ old('volume_ml', is_numeric($bloodBank->quantity) ? $bloodBank->quantity : '') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="collected_at">{{ localize('global.collected_at') }}</label>
                                        <input type="datetime-local" name="collected_at" id="collected_at" class="form-control"
                                               value="{{ old('collected_at') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="expires_at">{{ localize('global.expires_at') }}</label>
                                        <input type="date" name="expires_at" id="expires_at" class="form-control"
                                               value="{{ old('expires_at') }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="notes">{{ localize('global.notes') }}</label>
                                        <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-package me-1"></i>{{ localize('global.phlebotomy_send_to_stock') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            {{ localize('global.phlebotomy_waiting_stock_permission') }}
                        </div>
                    @endcanany
                @elseif ($bloodBank->status === 'delivered')
                    <div class="alert alert-success mb-0">
                        {{ localize('global.phlebotomy_completed_status') }}
                    </div>
                @elseif ($bloodBank->status === 'rejected')
                    <div class="alert alert-secondary mb-0">
                        {{ localize('global.phlebotomy_rejected_status') }}
                    </div>
                @else
                    <div class="alert alert-secondary mb-0">
                        {{ localize('global.phlebotomy_read_only_status') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
