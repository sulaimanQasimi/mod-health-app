@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            @php
                $reservedUnitIds = $reservedUnitIds ?? collect();
                $bloodCheck = $bloodBank->bloodCheck();
                $wfCurrentStep = null;
                $wfStep1Done = false;
                $wfStep2Done = false;
                $wfStep3Done = false;
                if ($bloodBank->status === 'approved') {
                    $wfStep1Done = true;
                    $wfStep2Done = $bloodBank->patientSamples->isNotEmpty();
                    $wfStep3Done = $remainingQty < 1 || $reservedCompatibleQty >= $remainingQty;
                    if (! $wfStep2Done) {
                        $wfCurrentStep = 2;
                    } elseif (! $wfStep3Done) {
                        $wfCurrentStep = 3;
                    } else {
                        $wfCurrentStep = 4;
                    }
                }
            @endphp
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0">{{ localize('global.blood_request_details') }}</h5>
                        <a href="{{ $inventoryUrl }}" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-link-external me-1"></i>{{ localize('global.open_full_inventory') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <div class="card bg-label-primary h-100">
                                    <div class="card-body py-2">
                                        <div class="small text-muted">{{ localize('global.requested_quantity') }}</div>
                                        <div class="fs-5 fw-bold">
                                            @include('pages.blood_banks.partials.order_quantity_display', ['bloodBank' => $bloodBank])
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-label-info h-100">
                                    <div class="card-body py-2">
                                        <div class="small text-muted">{{ localize('global.crossmatch_reserved_compatible_summary') }}</div>
                                        <div class="fs-5 fw-bold">{{ $reservedCompatibleQty }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-label-success h-100">
                                    <div class="card-body py-2">
                                        <div class="small text-muted">{{ localize('global.issued_blood_units') }}</div>
                                        <div class="fs-5 fw-bold">{{ $issuedQty }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-label-warning h-100">
                                    <div class="card-body py-2">
                                        <div class="small text-muted">{{ localize('global.remaining_quantity') }}</div>
                                        <div class="fs-5 fw-bold">{{ $remainingQty }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (! empty($quantityInferredFromVolumeMl))
                            <div class="alert alert-warning py-2 small mb-3">
                                {{ \Illuminate\Support\Facades\Lang::get('global.quantity_inferred_from_volume_hint', ['raw' => (int) $bloodBank->quantity], session()->has('language') ? session('language') : 'dr') }}
                            </div>
                        @endif

                        @if ($bloodBank->status === 'approved')
                            {{-- Visual workflow stepper --}}
                            <div class="card bg-label-secondary bg-opacity-10 border mb-4">
                                <div class="card-body py-3">
                                    <div class="small text-muted text-uppercase mb-2 fw-semibold">
                                        {{ localize('global.blood_bank_workflow_title') }}</div>
                                    <div class="row g-2 align-items-start text-center">
                                        @foreach ([1, 2, 3, 4] as $sn)
                                            @php
                                                $done =
                                                    ($sn === 1 && $wfStep1Done) ||
                                                    ($sn === 2 && $wfStep2Done) ||
                                                    ($sn === 3 && $wfStep3Done) ||
                                                    ($sn === 4 && false);
                                                $current = $wfCurrentStep === $sn;
                                            @endphp
                                            <div class="col-6 col-md-3">
                                                <div
                                                    class="d-flex flex-column align-items-center gap-1">
                                                    <div
                                                        class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                                                        @if ($done) bg-success text-white
                                                        @elseif($current) bg-primary text-white shadow
                                                        @else bg-label-secondary text-muted @endif"
                                                        style="width: 2.5rem; height: 2.5rem;">
                                                        @if ($done && ! $current)
                                                            <i class="bx bx-check"></i>
                                                        @else
                                                            <span class="fw-bold">{{ $sn }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="small fw-semibold px-1">
                                                        @if ($sn === 1)
                                                            {{ localize('global.blood_bank_workflow_step_1') }}
                                                        @elseif($sn === 2)
                                                            {{ localize('global.blood_bank_workflow_step_2') }}
                                                        @elseif($sn === 3)
                                                            {{ localize('global.blood_bank_workflow_step_3') }}
                                                        @else
                                                            {{ localize('global.blood_bank_workflow_step_4') }}
                                                        @endif
                                                    </div>
                                                    <span
                                                        class="badge @if ($done) bg-label-success @elseif($current) bg-label-primary @else bg-label-secondary @endif">
                                                        @if ($done)
                                                            {{ localize('global.workflow_step_status_done') }}
                                                        @elseif($current)
                                                            {{ localize('global.workflow_step_status_current') }}
                                                        @else
                                                            {{ localize('global.workflow_step_status_pending') }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Step 1: Blood check (request / patient need) --}}
                            <div
                                class="card mb-3 border @if ($wfCurrentStep === 1) border-primary shadow-sm @else border-secondary @endif">
                                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary rounded-pill">1</span>
                                        <h6 class="mb-0">{{ localize('global.blood_bank_workflow_step_1') }}</h6>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        @canany(['receive-blood-units', 'manage-blood-inventory'])
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#bloodCheckModal{{ $bloodBank->id }}">
                                                <i class="bx bx-edit-alt me-1"></i>{{ localize('global.blood_check_fill_modal') }}
                                            </button>
                                        @endcanany
                                        <span class="badge bg-label-success">{{ localize('global.workflow_step_status_done') }}</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="small text-muted mb-3">{{ localize('global.blood_bank_workflow_step_1_hint') }}</p>
                                    @if ($bloodBank->bloodCheckRecord)
                                        <div class="alert alert-success py-2 mb-3 small">
                                            <div class="fw-semibold mb-1">{{ localize('global.blood_check_record_saved') }}</div>
                                            <span dir="ltr">{{ localize('global.patient_typed_group') }}:
                                                {{ $bloodBank->bloodCheckRecord->patient_typed_group ?? '—' }}</span>
                                            —
                                            <span dir="ltr">{{ localize('global.patient_typed_rh') }}:
                                                {{ $bloodBank->bloodCheckRecord->patient_typed_rh ?? '—' }}</span>
                                            @if ($bloodBank->bloodCheckRecord->verified_at)
                                                <span class="d-block mt-1 text-muted">
                                                    {{ localize('global.verified_at') }}
                                                    {{ $bloodBank->bloodCheckRecord->verified_at->format('Y-m-d H:i') }}
                                                    @if ($bloodBank->bloodCheckRecord->verifiedBy)
                                                        — {{ $bloodBank->bloodCheckRecord->verifiedBy->name }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="border rounded bg-body-secondary p-2">
                                        @include('pages.blood_banks.partials.request_summary')
                                    </div>
                                </div>
                            </div>

                            @canany(['receive-blood-units', 'manage-blood-inventory'])
                                @php
                                    $bcr = $bloodBank->bloodCheckRecord;
                                    $bcAbo = old('abo_group', $bcr->abo_group ?? $bloodBank->group);
                                    $bcRh = old('rh', $bcr->rh ?? $bloodBank->rh);
                                    $bcType = old('component_type', $bcr->component_type ?? $bloodBank->type);
                                    $bcQty = old('quantity', $bcr && (int) $bcr->quantity >= 1 ? \App\Models\BloodBank::normalizeRawQuantityToUnits((int) $bcr->quantity) : $requestedQty);
                                @endphp
                                <div class="modal fade" id="bloodCheckModal{{ $bloodBank->id }}" tabindex="-1"
                                    aria-labelledby="bloodCheckModalLabel{{ $bloodBank->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <form action="{{ route('blood_banks.blood_check.store', $bloodBank->id) }}"
                                            method="POST">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="bloodCheckModalLabel{{ $bloodBank->id }}">
                                                        {{ localize('global.blood_check_modal_title') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="small text-muted">{{ localize('global.blood_check_modal_intro') }}</p>
                                                    <div class="row g-2">
                                                        <div class="col-md-3">
                                                            <label class="form-label">{{ localize('global.blood_group') }}</label>
                                                            <select name="abo_group" class="form-select" required>
                                                                @foreach (['A', 'B', 'AB', 'O'] as $g)
                                                                    <option value="{{ $g }}" @selected($bcAbo == $g)>{{ $g }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">{{ localize('global.blood_rh') }}</label>
                                                            <select name="rh" class="form-select" required>
                                                                <option value="+" @selected($bcRh == '+')>+</option>
                                                                <option value="-" @selected($bcRh == '-')>-</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">{{ localize('global.blood_type') }}</label>
                                                            <select name="component_type" class="form-select" required>
                                                                @foreach (\App\Models\BloodCheckRecord::COMPONENT_TYPES as $t)
                                                                    <option value="{{ $t }}" @selected($bcType == $t)>{{ $t }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">{{ localize('global.quantity') }}</label>
                                                            <input type="number" name="quantity" class="form-control" min="0"
                                                                value="{{ $bcQty }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">{{ localize('global.patient_typed_group') }}
                                                                <span class="text-muted">({{ localize('global.optional') }})</span></label>
                                                            <select name="patient_typed_group" class="form-select">
                                                                <option value="">{{ localize('global.select') }}</option>
                                                                @foreach (['A', 'B', 'AB', 'O'] as $g)
                                                                    <option value="{{ $g }}" @selected(old('patient_typed_group', $bcr?->patient_typed_group ?? '') == $g)>{{ $g }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">{{ localize('global.patient_typed_rh') }}
                                                                <span class="text-muted">({{ localize('global.optional') }})</span></label>
                                                            <select name="patient_typed_rh" class="form-select">
                                                                <option value="">{{ localize('global.select') }}</option>
                                                                <option value="+" @selected(old('patient_typed_rh', $bcr?->patient_typed_rh ?? '') == '+')>+</option>
                                                                <option value="-" @selected(old('patient_typed_rh', $bcr?->patient_typed_rh ?? '') == '-')>-</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">{{ localize('global.notes') }}</label>
                                                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $bcr?->notes ?? '') }}</textarea>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="verify_lab_typing" id="verify_lab_typing{{ $bloodBank->id }}"
                                                                    value="1" @checked(old('verify_lab_typing', false))>
                                                                <label class="form-check-label"
                                                                    for="verify_lab_typing{{ $bloodBank->id }}">{{ localize('global.blood_check_verify_lab') }}</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                                    <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endcanany

                            {{-- Step 2: Patient sample --}}
                            <div
                                class="card mb-3 border @if ($wfCurrentStep === 2) border-primary shadow-sm @else border-secondary @endif">
                                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary rounded-pill">2</span>
                                        <h6 class="mb-0">{{ localize('global.blood_bank_workflow_step_2') }}</h6>
                                    </div>
                                    @if ($wfStep2Done)
                                        <span class="badge bg-label-success">{{ localize('global.workflow_step_status_done') }}</span>
                                    @elseif($wfCurrentStep === 2)
                                        <span class="badge bg-label-primary">{{ localize('global.workflow_step_status_current') }}</span>
                                    @else
                                        <span class="badge bg-label-warning">{{ localize('global.workflow_step_status_pending') }}</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <p class="small text-muted mb-3">{{ localize('global.blood_bank_workflow_step_2_hint') }}</p>
                                    @canany(['receive-blood-units', 'manage-blood-inventory'])
                                        <form action="{{ route('blood_banks.crossmatch.samples.store', $bloodBank->id) }}"
                                            method="POST" class="row g-2 mb-3 border rounded p-3 bg-body-tertiary">
                                            @csrf
                                            <div class="col-md-3">
                                                <label class="form-label">{{ localize('global.crossmatch_sample_id') }}</label>
                                                <input type="text" name="sample_id" class="form-control"
                                                    placeholder="{{ localize('global.optional') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">{{ localize('global.collected_at') }}</label>
                                                <input type="datetime-local" name="collected_at" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">{{ localize('global.notes') }}</label>
                                                <input type="text" name="notes" class="form-control">
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="submit"
                                                    class="btn btn-primary w-100">{{ localize('global.save_sample') }}</button>
                                            </div>
                                        </form>
                                    @endcanany

                                    @if ($bloodBank->patientSamples->isNotEmpty())
                                        <div>
                                            <div class="fw-semibold small mb-2">{{ localize('global.patient_samples') }}</div>
                                            <div class="table-responsive border rounded">
                                                <table class="table table-sm table-striped table-hover align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th scope="col">{{ localize('global.crossmatch_sample_id') }}</th>
                                                            <th scope="col">{{ localize('global.collected_at') }}</th>
                                                            <th scope="col">{{ localize('global.notes') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($bloodBank->patientSamples as $sample)
                                                            <tr>
                                                                <td class="font-monospace">{{ $sample->sample_id ?: '#'.$sample->id }}</td>
                                                                <td dir="ltr">
                                                                    @if ($sample->collected_at)
                                                                        {{ $sample->collected_at->format('Y-m-d H:i') }}
                                                                    @else
                                                                        —
                                                                    @endif
                                                                </td>
                                                                <td>{{ $sample->notes ? $sample->notes : '—' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-0 py-2 small">
                                            {{ localize('global.blood_bank_workflow_step_2_empty') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Step 3: Crossmatch & reserve --}}
                            <div
                                class="card mb-3 border @if ($wfCurrentStep === 3) border-primary shadow-sm @else border-secondary @endif">
                                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary rounded-pill">3</span>
                                        <h6 class="mb-0">{{ localize('global.blood_bank_workflow_step_3') }}</h6>
                                    </div>
                                    @if ($wfStep3Done)
                                        <span class="badge bg-label-success">{{ localize('global.workflow_step_status_done') }}</span>
                                    @elseif($wfCurrentStep === 3)
                                        <span class="badge bg-label-primary">{{ localize('global.workflow_step_status_current') }}</span>
                                    @else
                                        <span class="badge bg-label-warning">{{ localize('global.workflow_step_status_pending') }}</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <p class="small text-muted mb-3">{{ localize('global.blood_bank_workflow_step_3_hint') }}</p>

                                    <div class="alert alert-info py-2 mb-3">
                                        <div class="fw-semibold mb-1">{{ localize('global.crossmatch_reserve_progress_title') }}</div>
                                        @if ($remainingQty < 1)
                                            <span class="text-success">{{ localize('global.crossmatch_no_units_left_to_reserve') }}</span>
                                        @else
                                            <span dir="ltr"><strong>{{ $reservedCompatibleQty }}</strong> / <strong>{{ $remainingQty }}</strong></span>
                                            <span class="text-muted small ms-1">({{ localize('global.crossmatch_reserved_vs_remaining_caption') }})</span>
                                        @endif
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>{{ localize('global.bag_number') }}</th>
                                                    <th>{{ localize('global.blood_group') }}</th>
                                                    <th>{{ localize('global.blood_rh') }}</th>
                                                    <th>{{ localize('global.expires_at') }}</th>
                                                    <th>{{ localize('global.crossmatch_auto_check') }}</th>
                                                    <th>{{ localize('global.crossmatch_status') }}</th>
                                                    <th>{{ localize('global.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($availableUnits as $u)
                                                    @php
                                                        $cx = $crossmatchesByUnit->get($u->id);
                                                        $autoAboRh = $bloodCheck->isAboRhAutoCompatibleWithBloodUnit($u);
                                                        $isReserved = $reservedUnitIds->contains($u->id);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $u->bag_number }}</td>
                                                        <td>{{ $u->blood_group }}</td>
                                                        <td>{{ $u->rh }}</td>
                                                        <td dir="ltr">{{ $u->expires_at?->format('Y-m-d H:i') }}</td>
                                                        <td>
                                                            @if ($autoAboRh)
                                                                <span class="badge bg-label-success">{{ localize('global.compatible') }}</span>
                                                            @else
                                                                <span class="badge bg-label-danger">{{ localize('global.incompatible') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($cx)
                                                                <span
                                                                    class="badge bg-label-{{ in_array($cx->status, ['compatible', 'overridden'], true) ? 'success' : ($cx->status === 'incompatible' ? 'danger' : 'warning') }}">
                                                                    {{ $cx->status }}
                                                                </span>
                                                                <div class="small text-muted mt-1">{{ $cx->auto_reason }}</div>
                                                            @else
                                                                <span class="badge bg-label-secondary">{{ localize('global.not_tested') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @canany(['receive-blood-units', 'manage-blood-inventory'])
                                                                <form action="{{ route('blood_banks.crossmatch.save', [$bloodBank->id, $u->id]) }}"
                                                                    method="POST" class="row g-1 mb-1">
                                                                    @csrf
                                                                    <div class="col-6">
                                                                        <select name="major_result" class="form-select form-select-sm" required>
                                                                            @foreach (\App\Models\BloodCrossmatch::RESULT_VALUES as $val)
                                                                                <option value="{{ $val }}" @selected(($cx?->major_result ?? 'pending') === $val)>{{ $val }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <select name="minor_result" class="form-select form-select-sm" required>
                                                                            @foreach (\App\Models\BloodCrossmatch::RESULT_VALUES as $val)
                                                                                <option value="{{ $val }}" @selected(($cx?->minor_result ?? 'pending') === $val)>{{ $val }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-8">
                                                                        <select name="patient_sample_id" class="form-select form-select-sm">
                                                                            <option value="">{{ localize('global.select_sample') }}</option>
                                                                            @foreach ($bloodBank->patientSamples as $sample)
                                                                                <option value="{{ $sample->id }}" @selected(($cx?->patient_sample_id ?? null) == $sample->id)>{{ $sample->sample_id ?: '#'.$sample->id }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <button type="submit"
                                                                            class="btn btn-sm btn-outline-primary w-100">{{ localize('global.save') }}</button>
                                                                    </div>
                                                                </form>

                                                                @if ($cx && in_array($cx->status, ['compatible', 'overridden'], true))
                                                                    @if (! $isReserved)
                                                                        <form action="{{ route('blood_banks.crossmatch.reserve', [$bloodBank->id, $cx->id]) }}"
                                                                            method="POST" class="d-inline-block">
                                                                            @csrf
                                                                            <button
                                                                                class="btn btn-sm btn-success">{{ localize('global.reserve_unit') }}</button>
                                                                        </form>
                                                                    @else
                                                                        <form action="{{ route('blood_banks.crossmatch.unreserve', [$bloodBank->id, $u->id]) }}"
                                                                            method="POST" class="d-inline-block">
                                                                            @csrf
                                                                            <button
                                                                                class="btn btn-sm btn-outline-warning">{{ localize('global.unreserve_unit') }}</button>
                                                                        </form>
                                                                    @endif
                                                                @endif
                                                            @endcanany

                                                            @if ($cx && $cx->status === 'incompatible' && auth()->user()->can('manage-blood-inventory'))
                                                                <form action="{{ route('blood_banks.crossmatch.override', [$bloodBank->id, $cx->id]) }}"
                                                                    method="POST" class="mt-1">
                                                                    @csrf
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="text" name="override_reason"
                                                                            class="form-control"
                                                                            placeholder="{{ localize('global.override_reason') }}" required>
                                                                        <button
                                                                            class="btn btn-danger">{{ localize('global.override_compatible') }}</button>
                                                                    </div>
                                                                </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted py-3">
                                                            {{ localize('global.no_item_is_found') }}
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="alert alert-secondary mt-3 mb-0">
                                        {{ localize('global.delivery_readiness') }}:
                                        @if ($remainingQty === 0)
                                            <span class="text-success fw-semibold">{{ localize('global.ready') }}</span>
                                        @elseif ($reservedCompatibleQty >= $remainingQty)
                                            <span class="text-success fw-semibold">{{ localize('global.ready_for_partial_or_full_delivery') }}</span>
                                        @else
                                            <span class="text-danger fw-semibold">{{ localize('global.not_ready_need_more_reserved_compatible_units') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Step 4: Inventory preview & issue / complete --}}
                            <div
                                class="card mb-3 border @if ($wfCurrentStep === 4) border-primary shadow-sm @else border-secondary @endif">
                                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary rounded-pill">4</span>
                                        <h6 class="mb-0">{{ localize('global.blood_bank_workflow_step_4') }}</h6>
                                    </div>
                                    @if ($wfCurrentStep === 4)
                                        <span class="badge bg-label-primary">{{ localize('global.workflow_step_status_current') }}</span>
                                    @else
                                        <span class="badge bg-label-secondary">{{ localize('global.workflow_step_status_pending') }}</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <p class="small text-muted mb-3">{{ localize('global.blood_bank_workflow_step_4_hint') }}</p>

                                    <div class="card border mb-3">
                                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                                            <span class="small fw-semibold">{{ localize('global.inventory_preview') }}</span>
                                            <a href="{{ $inventoryUrl }}"
                                                class="btn btn-sm btn-outline-primary">{{ localize('global.open_full_inventory') }}</a>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ localize('global.bag_number') }}</th>
                                                            <th>{{ localize('global.blood_group') }}</th>
                                                            <th>{{ localize('global.blood_rh') }}</th>
                                                            <th>{{ localize('global.component_type') }}</th>
                                                            <th>{{ localize('global.expires_at') }}</th>
                                                            <th>{{ localize('global.screening_status') }}</th>
                                                            <th>{{ localize('global.crossmatch_status') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($inventoryPreviewUnits as $u)
                                                            @php
                                                                $cx = $crossmatchesByUnit->get($u->id);
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    <a href="{{ route('blood_banks.inventory.show', $u->id) }}">{{ $u->bag_number }}</a>
                                                                </td>
                                                                <td>{{ $u->blood_group }}</td>
                                                                <td>{{ $u->rh }}</td>
                                                                <td>{{ $u->component_type }}</td>
                                                                <td dir="ltr">{{ $u->expires_at?->format('Y-m-d H:i') }}</td>
                                                                <td>
                                                                    <span
                                                                        class="badge bg-label-{{ ($u->test?->overall_status ?? 'pending') === 'passed' ? 'success' : (($u->test?->overall_status ?? 'pending') === 'failed' ? 'danger' : 'warning') }}">
                                                                        {{ $u->test?->overall_status ?? 'pending' }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    @if ($cx)
                                                                        <span
                                                                            class="badge bg-label-{{ in_array($cx->status, ['compatible', 'overridden'], true) ? 'success' : ($cx->status === 'incompatible' ? 'danger' : 'warning') }}">
                                                                            {{ $cx->status }}
                                                                        </span>
                                                                    @else
                                                                        <span class="badge bg-label-secondary">{{ localize('global.not_tested') }}</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="7" class="text-center text-muted py-3">{{ localize('global.no_item_is_found') }}
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    @php
                                        $deliverableIds = $bloodBank->crossmatches
                                            ->filter(fn ($cx) => in_array($cx->status, ['compatible', 'overridden'], true))
                                            ->filter(fn ($cx) => $reservedUnitIds->contains($cx->blood_unit_id))
                                            ->pluck('blood_unit_id')
                                            ->values();
                                        $hasCrossmatchFlow = $bloodBank->crossmatches->isNotEmpty();
                                    @endphp
                                    <form action="{{ route('blood_banks.deliver', $bloodBank->id) }}" method="POST"
                                        class="border rounded p-3 bg-body">
                                        @csrf
                                        <h6 class="mb-2">{{ localize('global.blood_bank_delivery_select_units') }}</h6>
                                        <p class="small text-muted mb-2">
                                            {{ localize('global.blood_bank_delivery_receiver_hint') }}</p>
                                        <div class="row g-2 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label"
                                                    for="blood_receiver_department_{{ $bloodBank->id }}">{{ localize('global.blood_bank_receiver_department') }}
                                                    <span class="text-danger">*</span></label>
                                                <select name="receiver_department_id"
                                                    id="blood_receiver_department_{{ $bloodBank->id }}"
                                                    class="form-select @error('receiver_department_id') is-invalid @enderror"
                                                    required>
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($receiverDepartments as $d)
                                                        <option value="{{ $d->id }}"
                                                            @selected(old('receiver_department_id', $bloodBank->receiver_department_id) == $d->id)>
                                                            {{ $d->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('receiver_department_id')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label"
                                                    for="blood_receiver_nurse_{{ $bloodBank->id }}">{{ localize('global.blood_bank_receiver_nurse') }}
                                                    <span class="text-danger">*</span></label>
                                                <select name="receiver_nurse_id"
                                                    id="blood_receiver_nurse_{{ $bloodBank->id }}"
                                                    class="form-select @error('receiver_nurse_id') is-invalid @enderror"
                                                    required
                                                    data-initial-nurse="{{ old('receiver_nurse_id', $bloodBank->receiver_nurse_id) }}">
                                                    <option value="">{{ localize('global.select_receiver_nurse_first') }}
                                                    </option>
                                                </select>
                                                @error('receiver_nurse_id')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <p class="small text-muted mb-2">
                                            {{ localize('global.deliver_blood_fifo_hint') }}</p>
                                        @if ($hasCrossmatchFlow)
                                            <p class="small text-danger mb-2">
                                                {{ localize('global.crossmatch_delivery_uses_reserved_hint') }}
                                            </p>
                                        @endif
                                        @if ($availableUnits->isNotEmpty())
                                            <div class="table-responsive mb-2" style="max-height: 280px; overflow-y: auto;">
                                                <table class="table table-sm table-bordered align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th scope="col" class="text-center" style="width: 3rem;">
                                                                {{ localize('global.blood_bank_delivery_select_column') }}</th>
                                                            <th scope="col">{{ localize('global.bag_number') }}</th>
                                                            <th scope="col">{{ localize('global.blood_group') }}</th>
                                                            <th scope="col">{{ localize('global.blood_rh') }}</th>
                                                            <th scope="col">{{ localize('global.component_type') }}</th>
                                                            <th scope="col">{{ localize('global.expires_at') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($availableUnits as $u)
                                                            @php
                                                                $checked = $hasCrossmatchFlow ? $deliverableIds->contains($u->id) : false;
                                                                $disabled = $hasCrossmatchFlow && ! $checked;
                                                                $cbId = 'deliver_unit_'.$bloodBank->id.'_'.$u->id;
                                                            @endphp
                                                            <tr @class(['opacity-50' => $disabled])>
                                                                <td class="text-center">
                                                                    <div class="form-check d-flex justify-content-center mb-0">
                                                                        <input class="form-check-input border-primary"
                                                                            type="checkbox" name="unit_ids[]"
                                                                            value="{{ $u->id }}" id="{{ $cbId }}"
                                                                            @checked($checked) @disabled($disabled)
                                                                            aria-label="{{ $u->bag_number }}"
                                                                            style="width: 1.15em; height: 1.15em; cursor: pointer;">
                                                                    </div>
                                                                </td>
                                                                <td>{{ $u->bag_number }}</td>
                                                                <td>{{ $u->blood_group }}</td>
                                                                <td>{{ $u->rh }}</td>
                                                                <td>{{ $u->component_type }}</td>
                                                                <td dir="ltr">{{ $u->expires_at?->format('Y-m-d H:i') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="alert alert-warning mb-2 py-2">
                                                {{ localize('global.insufficient_blood_stock') }}
                                            </div>
                                        @endif
                                        <button type="submit" class="btn btn-success mt-2">
                                            <i class="bx bxs-check-circle"></i> {{ localize('global.complete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            {{-- Non-approved / non-workflow: classic summary --}}
                            <div class="col-md-12">
                                <div class="border border-label-primary mb-4">
                                    <h5 class="mb-4 p-3 bg-label-primary text-center">
                                        {{ localize('global.blood_request_details') }}</h5>
                                    @include('pages.blood_banks.partials.request_summary')
                                </div>
                            </div>
                        @endif

                        @if ($bloodBank->status === 'delivered' && ($bloodBank->receiverDepartment || $bloodBank->receiverNurse))
                            <div class="col-md-12 mt-3">
                                <h6 class="mb-2">{{ localize('global.blood_bank_receiver_summary') }}</h6>
                                <p class="mb-1 small">
                                    <span class="text-muted">{{ localize('global.blood_bank_receiver_department') }}:</span>
                                    {{ $bloodBank->receiverDepartment?->name ?? '—' }}
                                </p>
                                <p class="mb-0 small">
                                    <span class="text-muted">{{ localize('global.blood_bank_receiver_nurse') }}:</span>
                                    {{ $bloodBank->receiverNurse?->full_name ?? '—' }}
                                </p>
                            </div>
                        @endif

                        @if ($bloodBank->status === 'delivered' && $bloodBank->bloodUnits->isNotEmpty())
                            <div class="col-md-12 mt-3">
                                <h6 class="mb-2">{{ localize('global.issued_blood_units') }}</h6>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.bag_number') }}</th>
                                            <th>{{ localize('global.expires_at') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($bloodBank->bloodUnits as $u)
                                            <tr>
                                                <td>{{ $u->bag_number }}</td>
                                                <td dir="ltr">{{ $u->pivot->issued_at ? \Carbon\Carbon::parse($u->pivot->issued_at)->format('Y-m-d H:i') : '—' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <div class="col-md-12">
                            <div class="row d-flex justify-content-center">
                                @if ($bloodBank->status != 'delivered')
                                    @if ($bloodBank->status != 'approved')
                                        <div class="col-md-4 text-center">
                                            <button class="btn btn-primary">
                                                <a href="{{ route('blood_banks.approve', $bloodBank->id) }}"
                                                    class="text-white">
                                                    <span><i class="bx bx-calendar-check"></i>{{ localize('global.approve') }}</span>
                                                </a>
                                            </button>
                                        </div>
                                    @endif
                                    @if ($bloodBank->status != 'rejected')
                                        <div class="col-md-4 text-center">
                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#createRejectModal{{ $bloodBank->id }}"><span><i
                                                        class="bx bx-calendar-x"></i>{{ localize('global.reject') }}</span></button>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="modal fade" id="createRejectModal{{ $bloodBank->id }}" tabindex="-1"
                            aria-labelledby="createRejectModalLabel{{ $bloodBank->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="{{ route('blood_banks.reject', $bloodBank->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" id="is_reserved{{ $bloodBank->is_reserved }}"
                                        name="is_reserved" value="1">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="createRejectModalLabel{{ $bloodBank->id }}">
                                                {{ localize('global.reject_request') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <label class="form-label"
                                                for="reject_reason{{ $bloodBank->id }}">{{ localize('global.reject_reason') }}</label>
                                            <textarea class="form-control" id="reject_reason{{ $bloodBank->id }}" name="reject_reason" rows="3"></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                            <button type="submit"
                                                class="btn btn-primary">{{ localize('global.save') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($bloodBank->status === 'approved')
        <script>
            (function() {
                const depSel = document.getElementById('blood_receiver_department_{{ $bloodBank->id }}');
                const nurseSel = document.getElementById('blood_receiver_nurse_{{ $bloodBank->id }}');
                if (!depSel || !nurseSel) return;

                const initialNurse = nurseSel.getAttribute('data-initial-nurse');
                const placeholderOpt = @json(localize('global.select_receiver_nurse_first'));

                /** Same host/path as this page — avoids broken fetches when APP_URL differs from the browser URL. */
                function nursesEndpointUrl(departmentId) {
                    return new URL(
                        '../nurses-by-department/' + encodeURIComponent(departmentId),
                        window.location.href
                    ).href;
                }

                function fillNurses(departmentId, preselectId) {
                    nurseSel.innerHTML = '<option value="">' + placeholderOpt + '</option>';
                    if (!departmentId) return;
                    fetch(nursesEndpointUrl(departmentId), {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(function(r) {
                            if (!r.ok) throw new Error('fetch');
                            return r.json();
                        })
                        .then(function(data) {
                            const nurses = data.nurses || [];
                            nurses.forEach(function(n) {
                                const opt = document.createElement('option');
                                opt.value = n.id;
                                opt.textContent = n.name;
                                if (preselectId && String(n.id) === String(preselectId)) opt.selected = true;
                                nurseSel.appendChild(opt);
                            });
                        })
                        .catch(function() {
                            nurseSel.innerHTML = '<option value="">' + placeholderOpt + '</option>';
                        });
                }

                depSel.addEventListener('change', function() {
                    fillNurses(this.value, null);
                });

                if (depSel.value) {
                    fillNurses(depSel.value, initialNurse || null);
                }
            })();
        </script>
    @endif
@endpush
