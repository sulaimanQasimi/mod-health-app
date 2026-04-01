@extends('layouts.master')

@section('content')
    @push('custom-css')
        <style>
            .blood-unit-details-card .detail-tile-label {
                background-color: #35365f;
                color: #696cff;
                padding: 0.5rem 0.75rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-weight: 600;
            }

            .blood-unit-details-card .detail-tile-value {
                background-color: var(--bs-secondary-bg);
                border: 1px solid var(--bs-border-color);
                padding: 0.9rem 0.75rem;
                min-height: 58px;
                display: flex;
                align-items: center;
                font-weight: 600;
            }
        </style>
    @endpush

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="mb-0">{{ localize('global.blood_unit_details') }} — {{ $bloodUnit->bag_number }}</h4>
                <a href="{{ route('blood_banks.inventory') }}" class="btn btn-outline-secondary btn-sm">
                    {{ localize('global.back') }}
                </a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <div class="card border shadow-sm blood-unit-details-card">
                        <div class="card-header d-flex align-items-center justify-content-between"
                            style="background-color: #35365f !important; color: #696cff !important;">
                            <h5 class="mb-0 d-flex align-items-center" style="color: #696cff !important;">
                                {{ localize('global.unit_information') }}
                            </h5>
                            <span class="badge fs-6 ms-auto" style="background-color: #696cff !important; color: #35365f !important;">
                                {{ $bloodUnit->status }}
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="h-100">
                                        <div class="detail-tile-label">
                                            <i class="bx bx-droplet"></i>
                                            <span>{{ localize('global.blood_group') }}</span>
                                        </div>
                                        <div class="detail-tile-value">{{ $bloodUnit->blood_group }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="h-100">
                                        <div class="detail-tile-label">
                                            <i class="bx bx-plus-medical"></i>
                                            <span>{{ localize('global.rh') }}</span>
                                        </div>
                                        <div class="detail-tile-value">{{ $bloodUnit->rh }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="h-100">
                                        <div class="detail-tile-label">
                                            <i class="bx bx-category"></i>
                                            <span>{{ localize('global.component_type') }}</span>
                                        </div>
                                        <div class="detail-tile-value">{{ $bloodUnit->component_type }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="h-100">
                                        <div class="detail-tile-label">
                                            <i class="bx bx-check-shield"></i>
                                            <span>{{ localize('global.status') }}</span>
                                        </div>
                                        <div class="detail-tile-value">{{ $bloodUnit->status }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="h-100">
                                        <div class="detail-tile-label">
                                            <i class="bx bx-test-tube"></i>
                                            <span>{{ localize('global.screening_status') }}</span>
                                        </div>
                                        <div class="detail-tile-value">
                                            @php
                                                $ts = $bloodUnit->test?->overall_status ?? 'pending';
                                            @endphp
                                            @if ($ts === 'passed')
                                                <span class="badge bg-label-success">{{ localize('global.passed') }}</span>
                                            @elseif ($ts === 'failed')
                                                <span class="badge bg-label-danger">{{ localize('global.failed') }}</span>
                                            @else
                                                <span class="badge bg-label-warning">{{ localize('global.pending') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="h-100">
                                        <div class="detail-tile-label">
                                            <i class="bx bx-flask"></i>
                                            <span>{{ localize('global.volume_ml') }}</span>
                                        </div>
                                        <div class="detail-tile-value">{{ $bloodUnit->volume_ml ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="h-100">
                                        <div class="detail-tile-label">
                                            <i class="bx bx-calendar"></i>
                                            <span>{{ localize('global.collected_at') }}</span>
                                        </div>
                                        <div class="detail-tile-value" dir="ltr">{{ $bloodUnit->collected_at?->format('Y-m-d') ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="h-100">
                                        <div class="detail-tile-label">
                                            <i class="bx bx-time-five"></i>
                                            <span>{{ localize('global.expires_at') }}</span>
                                        </div>
                                        <div class="detail-tile-value" dir="ltr">{{ $bloodUnit->expires_at?->format('Y-m-d H:i') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3 border shadow-sm blood-unit-details-card">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2"
                            style="background-color: #35365f !important; color: #696cff !important;">
                            <h5 class="mb-0" style="color: #696cff !important;">{{ localize('global.blood_unit_screening_results') }}</h5>
                            @if ($bloodUnit->test?->overall_status === 'passed')
                                <span class="badge fs-6 ms-auto"
                                    style="background-color: #696cff !important; color: #35365f !important;">{{ localize('global.passed') }}</span>
                            @elseif ($bloodUnit->test?->overall_status === 'failed')
                                <span class="badge fs-6 ms-auto"
                                    style="background-color: #696cff !important; color: #35365f !important;">{{ localize('global.failed') }}</span>
                            @elseif ($bloodUnit->test)
                                <span class="badge fs-6 ms-auto"
                                    style="background-color: #696cff !important; color: #35365f !important;">{{ localize('global.pending') }}</span>
                            @endif
                        </div>
                        <div class="card-body p-4">
                            @if ($bloodUnit->tests->isNotEmpty())
                                @foreach ($bloodUnit->tests as $singleTest)
                                    <div class="{{ $loop->first ? '' : 'border-top pt-3 mt-3' }}">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <div class="h-100">
                                                    <div class="detail-tile-label"><span>{{ localize('global.abo_result') }}</span></div>
                                                    <div class="detail-tile-value">{{ $singleTest->abo_result ?? '—' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="h-100">
                                                    <div class="detail-tile-label"><span>{{ localize('global.rh_result') }}</span></div>
                                                    <div class="detail-tile-value">{{ $singleTest->rh_result ?? '—' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="h-100">
                                                    <div class="detail-tile-label"><span>{{ localize('global.dct') }}</span></div>
                                                    <div class="detail-tile-value">{{ $singleTest->dct_result ?? '—' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="h-100">
                                                    <div class="detail-tile-label"><span>{{ localize('global.ict') }}</span></div>
                                                    <div class="detail-tile-value">{{ $singleTest->ict_result ?? '—' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="h-100">
                                                    <div class="detail-tile-label"><span>{{ localize('global.hbs') }}</span></div>
                                                    <div class="detail-tile-value">{{ $singleTest->hbs_result ?? '—' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="h-100">
                                                    <div class="detail-tile-label"><span>{{ localize('global.hcv') }}</span></div>
                                                    <div class="detail-tile-value">{{ $singleTest->hcv_result ?? '—' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="h-100">
                                                    <div class="detail-tile-label"><span>{{ localize('global.hiv') }}</span></div>
                                                    <div class="detail-tile-value">{{ $singleTest->hiv_result ?? '—' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="h-100">
                                                    <div class="detail-tile-label"><span>{{ localize('global.vdrl') }}</span></div>
                                                    <div class="detail-tile-value">{{ $singleTest->vdrl_result ?? '—' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="h-100">
                                                    <div class="detail-tile-label"><span>{{ localize('global.status') }}</span></div>
                                                    <div class="detail-tile-value">{{ $singleTest->overall_status ?? '—' }}</div>
                                                </div>
                                            </div>
                                            @if ($singleTest->remarks)
                                                <div class="col-md-9">
                                                    <div class="h-100">
                                                        <div class="detail-tile-label"><span>{{ localize('global.remarks') }}</span></div>
                                                        <div class="detail-tile-value">{{ $singleTest->remarks }}</div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($singleTest->tested_at)
                                                <div class="col-md-12">
                                                    <div class="h-100">
                                                        <div class="detail-tile-label"><span>{{ localize('global.last_tested_at') }}</span></div>
                                                        <div class="detail-tile-value" dir="ltr">
                                                            {{ $singleTest->tested_at->format('Y-m-d H:i') }}
                                                            @if ($singleTest->testedBy)
                                                                — {{ $singleTest->testedBy->name }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="detail-tile-value">{{ localize('global.blood_unit_no_screening_record') }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="card mt-3 border shadow-sm blood-unit-details-card">
                        <div class="card-header d-flex align-items-center justify-content-between"
                            style="background-color: #35365f !important; color: #696cff !important;">
                            <h5 class="mb-0" style="color: #696cff !important;">{{ localize('global.donor_and_sample') }}</h5>
                        </div>
                        <div class="card-body p-4">
                            @if ($bloodUnit->donation)
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="h-100">
                                            <div class="detail-tile-label"><span>{{ localize('global.donor') }}</span></div>
                                            <div class="detail-tile-value">{{ $bloodUnit->donation->donor?->name ?? '—' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="h-100">
                                            <div class="detail-tile-label"><span>{{ localize('global.patient') }}</span></div>
                                            <div class="detail-tile-value">
                                                @if ($bloodUnit->donation->donor?->patient)
                                                    <a href="{{ route('patients.show', $bloodUnit->donation->donor->patient) }}">
                                                        {{ trim($bloodUnit->donation->donor->patient->name.' '.($bloodUnit->donation->donor->patient->last_name ?? '')) }}
                                                    </a>
                                                @else
                                                    —
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="h-100">
                                            <div class="detail-tile-label"><span>{{ localize('global.department') }}</span></div>
                                            <div class="detail-tile-value">{{ $bloodUnit->donation->donor?->department?->name ?? '—' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="h-100">
                                            <div class="detail-tile-label"><span>{{ localize('global.phlebotomy_at') }}</span></div>
                                            <div class="detail-tile-value" dir="ltr">{{ $bloodUnit->donation->phlebotomy_at?->format('Y-m-d H:i') ?? '—' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="h-100">
                                            <div class="detail-tile-label"><span>{{ localize('global.samples') }}</span></div>
                                            <div class="detail-tile-value">{{ $bloodUnit->donation->samples?->count() ?? 0 }}</div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="detail-tile-value">{{ localize('global.no_donation_linked') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    @if (auth()->user()->can('receive-blood-units') || auth()->user()->can('manage-blood-inventory'))
                        @if (in_array($bloodUnit->status, ['available', 'quarantine'], true))
                            <div class="card mb-3">
                                <div class="card-header py-2">
                                    <h6 class="mb-0">{{ localize('global.actions') }}</h6>
                                </div>
                                <div class="card-body">
                                    @if ($bloodUnit->status === 'available')
                                        <form action="{{ route('blood_banks.inventory.quarantine', $bloodUnit) }}" method="POST"
                                            class="mb-2">
                                            @csrf
                                            <label class="form-label small">{{ localize('global.reason') }}</label>
                                            <input type="text" name="reason" class="form-control form-control-sm mb-2">
                                            <button type="submit" class="btn btn-sm btn-warning w-100">{{ localize('global.blood_quarantine_action') }}</button>
                                        </form>
                                    @endif
                                    @if ($bloodUnit->status === 'quarantine')
                                        <form action="{{ route('blood_banks.inventory.release_quarantine', $bloodUnit) }}"
                                            method="POST" class="mb-2">
                                            @csrf
                                            <label class="form-label small">{{ localize('global.reason') }}</label>
                                            <input type="text" name="reason" class="form-control form-control-sm mb-2">
                                            <button type="submit" class="btn btn-sm btn-outline-success w-100">{{ localize('global.blood_release_quarantine') }}</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('blood_banks.inventory.discard', $bloodUnit) }}" method="POST"
                                        onsubmit="return confirm('{{ localize('global.are_you_sure') }}');">
                                        @csrf
                                        <label class="form-label small">{{ localize('global.discard_reason') }}</label>
                                        <input type="text" name="reason" class="form-control form-control-sm mb-2">
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">{{ localize('global.discard_unit') }}</button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        <div class="card mb-3">
                            <div class="card-header py-2">
                                <h6 class="mb-0">{{ localize('global.screening_and_tests') }}</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('blood_banks.inventory.tests.save', $bloodUnit) }}" method="POST" class="vstack gap-2">
                                    @csrf
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small mb-0">{{ localize('global.abo_result') }}</label>
                                            <select class="form-select form-select-sm" name="abo_result">
                                                <option value="">{{ localize('global.select') }}</option>
                                                @foreach (['A','B','AB','O'] as $g)
                                                    <option value="{{ $g }}" @selected(old('abo_result', $bloodUnit->test?->abo_result) === $g)>{{ $g }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small mb-0">{{ localize('global.rh_result') }}</label>
                                            <select class="form-select form-select-sm" name="rh_result">
                                                <option value="">{{ localize('global.select') }}</option>
                                                <option value="+" @selected(old('rh_result', $bloodUnit->test?->rh_result) === '+')>+</option>
                                                <option value="-" @selected(old('rh_result', $bloodUnit->test?->rh_result) === '-')>-</option>
                                            </select>
                                        </div>
                                    </div>

                                    @php
                                        $test = $bloodUnit->test;
                                        $opts = ['pending','negative','positive','inconclusive'];
                                    @endphp

                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small mb-0">{{ localize('global.dct') }}</label>
                                            <select class="form-select form-select-sm" name="dct_result" required>
                                                @foreach ($opts as $o)
                                                    <option value="{{ $o }}" @selected(old('dct_result', $test?->dct_result ?? 'pending') === $o)>{{ $o }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small mb-0">{{ localize('global.ict') }}</label>
                                            <select class="form-select form-select-sm" name="ict_result" required>
                                                @foreach ($opts as $o)
                                                    <option value="{{ $o }}" @selected(old('ict_result', $test?->ict_result ?? 'pending') === $o)>{{ $o }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small mb-0">{{ localize('global.hbs') }}</label>
                                            <select class="form-select form-select-sm" name="hbs_result" required>
                                                @foreach ($opts as $o)
                                                    <option value="{{ $o }}" @selected(old('hbs_result', $test?->hbs_result ?? 'pending') === $o)>{{ $o }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small mb-0">{{ localize('global.hcv') }}</label>
                                            <select class="form-select form-select-sm" name="hcv_result" required>
                                                @foreach ($opts as $o)
                                                    <option value="{{ $o }}" @selected(old('hcv_result', $test?->hcv_result ?? 'pending') === $o)>{{ $o }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small mb-0">{{ localize('global.hiv') }}</label>
                                            <select class="form-select form-select-sm" name="hiv_result" required>
                                                @foreach ($opts as $o)
                                                    <option value="{{ $o }}" @selected(old('hiv_result', $test?->hiv_result ?? 'pending') === $o)>{{ $o }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small mb-0">{{ localize('global.vdrl') }}</label>
                                            <select class="form-select form-select-sm" name="vdrl_result" required>
                                                @foreach ($opts as $o)
                                                    <option value="{{ $o }}" @selected(old('vdrl_result', $test?->vdrl_result ?? 'pending') === $o)>{{ $o }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label small mb-0">{{ localize('global.remarks') }}</label>
                                        <textarea class="form-control form-control-sm" name="remarks" rows="2">{{ old('remarks', $test?->remarks) }}</textarea>
                                    </div>

                                    <button type="submit" class="btn btn-sm btn-primary w-100">
                                        {{ localize('global.save_tests') }}
                                    </button>
                                </form>

                                @if (($bloodUnit->test?->overall_status ?? 'pending') === 'passed' && $bloodUnit->status === 'quarantine')
                                    <form action="{{ route('blood_banks.inventory.tests.approve', $bloodUnit) }}" method="POST" class="mt-2">
                                        @csrf
                                        <button class="btn btn-sm btn-success w-100">{{ localize('global.release_to_stock') }}</button>
                                    </form>
                                @endif

                                @if ($bloodUnit->test?->tested_at)
                                    <div class="small text-muted mt-2">
                                        {{ localize('global.last_tested_at') }}:
                                        <span dir="ltr">{{ $bloodUnit->test->tested_at?->format('Y-m-d H:i') }}</span>
                                        — {{ $bloodUnit->test->testedBy?->name ?? '—' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.stock_movement_history') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.date') }}</th>
                                    <th>{{ localize('global.movement_type') }}</th>
                                    <th>{{ localize('global.user') }}</th>
                                    <th>{{ localize('global.notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bloodUnit->stockMovements->sortByDesc('created_at') as $m)
                                    <tr>
                                        <td dir="ltr">{{ $m->created_at?->format('Y-m-d H:i') }}</td>
                                        <td><span class="badge bg-label-secondary">{{ $m->movement_type }}</span></td>
                                        <td>{{ $m->user?->name ?? '—' }}</td>
                                        <td>{{ $m->notes ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">{{ localize('global.no_item_is_found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
