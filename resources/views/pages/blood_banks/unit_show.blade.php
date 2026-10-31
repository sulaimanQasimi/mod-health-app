@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            @php
                $screeningStatus = $bloodUnit->test?->overall_status ?? 'pending';
                $statusBadge = match ($bloodUnit->status) {
                    'available' => 'bg-label-success',
                    'quarantine' => 'bg-label-warning',
                    'reserved' => 'bg-label-info',
                    'issued' => 'bg-label-primary',
                    'discarded' => 'bg-label-danger',
                    default => 'bg-label-secondary',
                };
                $screeningBadge = match ($screeningStatus) {
                    'passed' => 'bg-label-success',
                    'failed' => 'bg-label-danger',
                    default => 'bg-label-warning',
                };
                $test = $bloodUnit->test;
                $testOpts = ['pending', 'negative', 'positive', 'inconclusive'];
            @endphp

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <h4 class="mb-1">{{ localize('global.blood_unit_details') }}</h4>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <small class="text-muted" dir="ltr">{{ $bloodUnit->bag_number }}</small>
                        <span class="badge {{ $statusBadge }}">{{ $bloodUnit->status }}</span>
                        <span class="badge {{ $screeningBadge }}">{{ localize('global.' . ($screeningStatus === 'pending' ? 'pending' : $screeningStatus)) }}</span>
                    </div>
                </div>
                <a href="{{ route('blood_banks.inventory') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
                </a>
            </div>

            {{-- Unit information --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">{{ localize('global.unit_information') }}</h5>
                    <span class="badge {{ $statusBadge }}">{{ $bloodUnit->status }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.bag_number') }}</h6>
                            <div class="fw-semibold" dir="ltr">{{ $bloodUnit->bag_number }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.blood_group') }}</h6>
                            <div class="fw-semibold">
                                @if ($bloodUnit->blood_group == 'A')
                                    <span class="text-danger"><i class="fa-solid fa-a"></i></span>
                                @elseif ($bloodUnit->blood_group == 'B')
                                    <span class="text-danger"><i class="fa-solid fa-b"></i></span>
                                @elseif ($bloodUnit->blood_group == 'AB')
                                    <span class="text-danger" dir="ltr"><i class="fa-solid fa-a"></i><i class="fa-solid fa-b"></i></span>
                                @elseif ($bloodUnit->blood_group == 'O')
                                    <span class="text-danger"><i class="fa-solid fa-o"></i></span>
                                @else
                                    {{ $bloodUnit->blood_group }}
                                @endif
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.blood_rh') }}</h6>
                            <div class="fw-semibold">
                                @if ($bloodUnit->rh == '+')
                                    <span class="bx bx-plus-circle text-danger"></span>
                                @elseif ($bloodUnit->rh == '-')
                                    <span class="bx bx-minus-circle text-danger"></span>
                                @else
                                    {{ $bloodUnit->rh }}
                                @endif
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.component_type') }}</h6>
                            <div class="fw-semibold">{{ $bloodUnit->component_type }}</div>
                        </div>

                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-info p-2 rounded">{{ localize('global.volume_ml') }}</h6>
                            <div class="fw-semibold">{{ $bloodUnit->volume_ml ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-info p-2 rounded">{{ localize('global.screening_status') }}</h6>
                            <div>
                                <span class="badge {{ $screeningBadge }}">
                                    @if ($screeningStatus === 'passed')
                                        {{ localize('global.passed') }}
                                    @elseif ($screeningStatus === 'failed')
                                        {{ localize('global.failed') }}
                                    @else
                                        {{ localize('global.pending') }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.collected_at') }}</h6>
                            <div class="fw-semibold" dir="ltr">{{ $bloodUnit->collected_at?->format('Y-m-d H:i') ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.expires_at') }}</h6>
                            <div class="fw-semibold" dir="ltr">{{ $bloodUnit->expires_at?->format('Y-m-d H:i') ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Screening results --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">{{ localize('global.blood_unit_screening_results') }}</h5>
                    <span class="badge {{ $screeningBadge }}">
                        @if ($screeningStatus === 'passed')
                            {{ localize('global.passed') }}
                        @elseif ($screeningStatus === 'failed')
                            {{ localize('global.failed') }}
                        @else
                            {{ localize('global.pending') }}
                        @endif
                    </span>
                </div>
                <div class="card-body">
                    @if ($bloodUnit->tests->isNotEmpty())
                        @foreach ($bloodUnit->tests as $singleTest)
                            <div class="{{ $loop->first ? '' : 'border-top pt-4 mt-4' }}">
                                <div class="row g-3 text-center">
                                    <div class="col-6 col-md-3">
                                        <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.abo_result') }}</h6>
                                        <div class="fw-semibold">{{ $singleTest->abo_result ?? '—' }}</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.rh_result') }}</h6>
                                        <div class="fw-semibold">{{ $singleTest->rh_result ?? '—' }}</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <h6 class="mb-2 bg-label-info p-2 rounded">{{ localize('global.dct') }}</h6>
                                        <div class="fw-semibold">{{ $singleTest->dct_result ?? '—' }}</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <h6 class="mb-2 bg-label-info p-2 rounded">{{ localize('global.ict') }}</h6>
                                        <div class="fw-semibold">{{ $singleTest->ict_result ?? '—' }}</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <h6 class="mb-2 bg-label-info p-2 rounded">{{ localize('global.hbs') }}</h6>
                                        <div class="fw-semibold">{{ $singleTest->hbs_result ?? '—' }}</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <h6 class="mb-2 bg-label-info p-2 rounded">{{ localize('global.hcv') }}</h6>
                                        <div class="fw-semibold">{{ $singleTest->hcv_result ?? '—' }}</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <h6 class="mb-2 bg-label-info p-2 rounded">{{ localize('global.hiv') }}</h6>
                                        <div class="fw-semibold">{{ $singleTest->hiv_result ?? '—' }}</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <h6 class="mb-2 bg-label-info p-2 rounded">{{ localize('global.vdrl') }}</h6>
                                        <div class="fw-semibold">{{ $singleTest->vdrl_result ?? '—' }}</div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.status') }}</h6>
                                        <div class="fw-semibold">{{ $singleTest->overall_status ?? '—' }}</div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.last_tested_at') }}</h6>
                                        <div class="fw-semibold" dir="ltr">
                                            {{ $singleTest->tested_at?->format('Y-m-d H:i') ?? '—' }}
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.user') }}</h6>
                                        <div class="fw-semibold">{{ $singleTest->testedBy?->name ?? '—' }}</div>
                                    </div>
                                    @if ($singleTest->remarks)
                                        <div class="col-12">
                                            <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.remarks') }}</h6>
                                            <div class="text-start border rounded p-3">{{ $singleTest->remarks }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-muted text-center py-3">{{ localize('global.blood_unit_no_screening_record') }}</div>
                    @endif
                </div>
            </div>

            {{-- Donor --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.donor_and_sample') }}</h5>
                </div>
                <div class="card-body">
                    @if ($bloodUnit->donation)
                        <div class="row g-3 text-center">
                            <div class="col-6 col-md-4">
                                <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.donor') }}</h6>
                                <div class="fw-semibold">{{ $bloodUnit->donation->donor?->name ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.patient') }}</h6>
                                <div class="fw-semibold">
                                    @if ($bloodUnit->donation->donor?->patient)
                                        <a href="{{ route('patients.show', $bloodUnit->donation->donor->patient) }}">
                                            {{ trim($bloodUnit->donation->donor->patient->name.' '.($bloodUnit->donation->donor->patient->last_name ?? '')) }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <h6 class="mb-2 bg-label-primary p-2 rounded">{{ localize('global.department') }}</h6>
                                <div class="fw-semibold">{{ $bloodUnit->donation->donor?->department?->name ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-6">
                                <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.phlebotomy_at') }}</h6>
                                <div class="fw-semibold" dir="ltr">{{ $bloodUnit->donation->phlebotomy_at?->format('Y-m-d H:i') ?? '—' }}</div>
                            </div>
                            <div class="col-6 col-md-6">
                                <h6 class="mb-2 bg-label-secondary p-2 rounded">{{ localize('global.samples') }}</h6>
                                <div class="fw-semibold">{{ $bloodUnit->donation->samples?->count() ?? 0 }}</div>
                            </div>
                        </div>
                    @else
                        <div class="text-muted text-center py-3">{{ localize('global.no_donation_linked') }}</div>
                    @endif
                </div>
            </div>

            @if (auth()->user()->can('receive-blood-units') || auth()->user()->can('manage-blood-inventory'))
                {{-- Actions --}}
                @if (in_array($bloodUnit->status, ['available', 'quarantine'], true))
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ localize('global.actions') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @if ($bloodUnit->status === 'available')
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="mb-3">{{ localize('global.blood_quarantine_action') }}</h6>
                                            <form action="{{ route('blood_banks.inventory.quarantine', $bloodUnit) }}" method="POST" class="mb-0">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="form-label" for="quarantine_reason">{{ localize('global.reason') }}</label>
                                                    <input type="text" name="reason" id="quarantine_reason" class="form-control">
                                                </div>
                                                <button type="submit" class="btn btn-warning w-100">
                                                    {{ localize('global.blood_quarantine_action') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                                @if ($bloodUnit->status === 'quarantine')
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="mb-3">{{ localize('global.blood_release_quarantine') }}</h6>
                                            <form action="{{ route('blood_banks.inventory.release_quarantine', $bloodUnit) }}" method="POST" class="mb-0">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="form-label" for="release_reason">{{ localize('global.reason') }}</label>
                                                    <input type="text" name="reason" id="release_reason" class="form-control">
                                                </div>
                                                <button type="submit" class="btn btn-outline-success w-100">
                                                    {{ localize('global.blood_release_quarantine') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <h6 class="mb-3">{{ localize('global.discard_unit') }}</h6>
                                        <form action="{{ route('blood_banks.inventory.discard', $bloodUnit) }}" method="POST"
                                              class="mb-0"
                                              onsubmit="return confirm('{{ localize('global.are_you_sure') }}');">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label" for="discard_reason">{{ localize('global.discard_reason') }}</label>
                                                <input type="text" name="reason" id="discard_reason" class="form-control">
                                            </div>
                                            <button type="submit" class="btn btn-outline-danger w-100">
                                                {{ localize('global.discard_unit') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Screening form --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0">{{ localize('global.screening_and_tests') }}</h5>
                        @if ($test?->tested_at)
                            <small class="text-muted">
                                {{ localize('global.last_tested_at') }}:
                                <span dir="ltr">{{ $test->tested_at->format('Y-m-d H:i') }}</span>
                                — {{ $test->testedBy?->name ?? '—' }}
                            </small>
                        @endif
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('blood_banks.inventory.tests.save', $bloodUnit) }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-sm-6 col-xl-3">
                                    <label class="form-label" for="abo_result">{{ localize('global.abo_result') }}</label>
                                    <select class="form-select @error('abo_result') is-invalid @enderror" name="abo_result" id="abo_result">
                                        <option value="">{{ localize('global.select') }}</option>
                                        @foreach (['A', 'B', 'AB', 'O'] as $g)
                                            <option value="{{ $g }}" @selected(old('abo_result', $test?->abo_result) === $g)>{{ $g }}</option>
                                        @endforeach
                                    </select>
                                    @error('abo_result')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <label class="form-label" for="rh_result">{{ localize('global.rh_result') }}</label>
                                    <select class="form-select @error('rh_result') is-invalid @enderror" name="rh_result" id="rh_result">
                                        <option value="">{{ localize('global.select') }}</option>
                                        <option value="+" @selected(old('rh_result', $test?->rh_result) === '+')>+</option>
                                        <option value="-" @selected(old('rh_result', $test?->rh_result) === '-')>-</option>
                                    </select>
                                    @error('rh_result')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <label class="form-label" for="dct_result">{{ localize('global.dct') }}</label>
                                    <select class="form-select @error('dct_result') is-invalid @enderror" name="dct_result" id="dct_result" required>
                                        @foreach ($testOpts as $o)
                                            <option value="{{ $o }}" @selected(old('dct_result', $test?->dct_result ?? 'pending') === $o)>{{ $o }}</option>
                                        @endforeach
                                    </select>
                                    @error('dct_result')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <label class="form-label" for="ict_result">{{ localize('global.ict') }}</label>
                                    <select class="form-select @error('ict_result') is-invalid @enderror" name="ict_result" id="ict_result" required>
                                        @foreach ($testOpts as $o)
                                            <option value="{{ $o }}" @selected(old('ict_result', $test?->ict_result ?? 'pending') === $o)>{{ $o }}</option>
                                        @endforeach
                                    </select>
                                    @error('ict_result')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <label class="form-label" for="hbs_result">{{ localize('global.hbs') }}</label>
                                    <select class="form-select @error('hbs_result') is-invalid @enderror" name="hbs_result" id="hbs_result" required>
                                        @foreach ($testOpts as $o)
                                            <option value="{{ $o }}" @selected(old('hbs_result', $test?->hbs_result ?? 'pending') === $o)>{{ $o }}</option>
                                        @endforeach
                                    </select>
                                    @error('hbs_result')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <label class="form-label" for="hcv_result">{{ localize('global.hcv') }}</label>
                                    <select class="form-select @error('hcv_result') is-invalid @enderror" name="hcv_result" id="hcv_result" required>
                                        @foreach ($testOpts as $o)
                                            <option value="{{ $o }}" @selected(old('hcv_result', $test?->hcv_result ?? 'pending') === $o)>{{ $o }}</option>
                                        @endforeach
                                    </select>
                                    @error('hcv_result')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <label class="form-label" for="hiv_result">{{ localize('global.hiv') }}</label>
                                    <select class="form-select @error('hiv_result') is-invalid @enderror" name="hiv_result" id="hiv_result" required>
                                        @foreach ($testOpts as $o)
                                            <option value="{{ $o }}" @selected(old('hiv_result', $test?->hiv_result ?? 'pending') === $o)>{{ $o }}</option>
                                        @endforeach
                                    </select>
                                    @error('hiv_result')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <label class="form-label" for="vdrl_result">{{ localize('global.vdrl') }}</label>
                                    <select class="form-select @error('vdrl_result') is-invalid @enderror" name="vdrl_result" id="vdrl_result" required>
                                        @foreach ($testOpts as $o)
                                            <option value="{{ $o }}" @selected(old('vdrl_result', $test?->vdrl_result ?? 'pending') === $o)>{{ $o }}</option>
                                        @endforeach
                                    </select>
                                    @error('vdrl_result')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-xl-9">
                                    <label class="form-label" for="remarks">{{ localize('global.remarks') }}</label>
                                    <textarea class="form-control @error('remarks') is-invalid @enderror" name="remarks" id="remarks" rows="2">{{ old('remarks', $test?->remarks) }}</textarea>
                                    @error('remarks')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-xl-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bx bx-save me-1"></i>{{ localize('global.save_tests') }}
                                    </button>
                                </div>
                            </div>
                        </form>

                        @if (($test?->overall_status ?? 'pending') === 'passed' && $bloodUnit->status === 'quarantine')
                            <form action="{{ route('blood_banks.inventory.tests.approve', $bloodUnit) }}" method="POST" class="mt-3">
                                @csrf
                                <button class="btn btn-success">
                                    <i class="bx bx-check me-1"></i>{{ localize('global.release_to_stock') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Movements --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.stock_movement_history') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm mb-0">
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
                                        <td colspan="4" class="text-center text-muted py-3">{{ localize('global.no_item_is_found') }}</td>
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
