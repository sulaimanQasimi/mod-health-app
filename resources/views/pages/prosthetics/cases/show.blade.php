@extends('layouts.master')

@section('content')
    @php
        $measRows = ($latestMeasurementSet?->measurements ?? collect())->values();
        $rxLines = ($activePrescription?->lines ?? collect())->values();

        $workflowRanks = [
            \App\Models\ProstheticCase::STATUS_NEW => 0,
            \App\Models\ProstheticCase::STATUS_REFERRED => 1,
            \App\Models\ProstheticCase::STATUS_UNDER_ASSESSMENT => 2,
            \App\Models\ProstheticCase::STATUS_MEASUREMENT_COMPLETED => 3,
            \App\Models\ProstheticCase::STATUS_PRESCRIPTION_COMPLETED => 4,
            \App\Models\ProstheticCase::STATUS_WAITING_APPROVAL => 5,
            \App\Models\ProstheticCase::STATUS_APPROVED => 6,
            \App\Models\ProstheticCase::STATUS_IN_PRODUCTION => 7,
            \App\Models\ProstheticCase::STATUS_TRIAL_FIT => 8,
            \App\Models\ProstheticCase::STATUS_DELIVERED => 9,
            \App\Models\ProstheticCase::STATUS_UNDER_FOLLOW_UP => 10,
            \App\Models\ProstheticCase::STATUS_CLOSED => 11,
            \App\Models\ProstheticCase::STATUS_CANCELLED => 12,
        ];

        $caseRank = $workflowRanks[$prosthetic_case->status] ?? -1;
        $isCaseReadOnly = in_array($prosthetic_case->status, [
            \App\Models\ProstheticCase::STATUS_CLOSED,
            \App\Models\ProstheticCase::STATUS_CANCELLED,
        ], true);

        $canEditAssessment = ! $isCaseReadOnly && $caseRank <= 2;
        $canEditMeasurements = ! $isCaseReadOnly && $caseRank <= 2;
        $canEditPrescription = ! $isCaseReadOnly && $caseRank <= 3;
        $canEditEstimate = ! $isCaseReadOnly && $caseRank <= 4;

        $canSubmitForApproval = ! $isCaseReadOnly && $caseRank === 4;
        $canApproveCase = ! $isCaseReadOnly && $caseRank === 5;

        $canCreateWorkOrder = ! $isCaseReadOnly && $caseRank === 6;
        $canUpdateWorkOrderStage = ! $isCaseReadOnly && $caseRank === 7
            && $activeWorkOrder
            && $activeWorkOrder->production_stage !== 'completed';
        $canIssueStock = $canUpdateWorkOrderStage;

        $canStoreFitting = ! $isCaseReadOnly && $caseRank === 7;
        $canStoreDelivery = ! $isCaseReadOnly && $caseRank === 8;
        $canStoreFollowUp = ! $isCaseReadOnly && $caseRank === 9;

        $canCloseCase = ! $isCaseReadOnly;
        $canManageAttachments = ! $isCaseReadOnly;

        $statusMeta = [
            \App\Models\ProstheticCase::STATUS_NEW => ['bg' => 'bg-label-primary', 'icon' => 'bx bx-plus-circle'],
            \App\Models\ProstheticCase::STATUS_REFERRED => ['bg' => 'bg-label-info', 'icon' => 'bx bx-transfer'],
            \App\Models\ProstheticCase::STATUS_UNDER_ASSESSMENT => ['bg' => 'bg-label-warning', 'icon' => 'bx bx-search'],
            \App\Models\ProstheticCase::STATUS_MEASUREMENT_COMPLETED => ['bg' => 'bg-label-secondary', 'icon' => 'bx bx-ruler'],
            \App\Models\ProstheticCase::STATUS_PRESCRIPTION_COMPLETED => ['bg' => 'bg-label-success', 'icon' => 'bx bx-file'],
            \App\Models\ProstheticCase::STATUS_WAITING_APPROVAL => ['bg' => 'bg-label-warning', 'icon' => 'bx bx-time-five'],
            \App\Models\ProstheticCase::STATUS_APPROVED => ['bg' => 'bg-label-primary', 'icon' => 'bx bx-check-circle'],
            \App\Models\ProstheticCase::STATUS_IN_PRODUCTION => ['bg' => 'bg-label-secondary', 'icon' => 'bx bx-cog'],
            \App\Models\ProstheticCase::STATUS_TRIAL_FIT => ['bg' => 'bg-label-secondary', 'icon' => 'bx bx-walk'],
            \App\Models\ProstheticCase::STATUS_DELIVERED => ['bg' => 'bg-label-success', 'icon' => 'bx bx-package'],
            \App\Models\ProstheticCase::STATUS_UNDER_FOLLOW_UP => ['bg' => 'bg-label-info', 'icon' => 'bx bx-calendar'],
            \App\Models\ProstheticCase::STATUS_CLOSED => ['bg' => 'bg-label-danger', 'icon' => 'bx bx-lock'],
            \App\Models\ProstheticCase::STATUS_CANCELLED => ['bg' => 'bg-label-secondary', 'icon' => 'bx bx-x-circle'],
        ];

        $statusMetaCurrent = $statusMeta[$prosthetic_case->status] ?? ['bg' => 'bg-label-secondary', 'icon' => 'bx bx-info-circle'];

        $workflowSteps = [
            \App\Models\ProstheticCase::STATUS_NEW,
            \App\Models\ProstheticCase::STATUS_REFERRED,
            \App\Models\ProstheticCase::STATUS_UNDER_ASSESSMENT,
            \App\Models\ProstheticCase::STATUS_MEASUREMENT_COMPLETED,
            \App\Models\ProstheticCase::STATUS_PRESCRIPTION_COMPLETED,
            \App\Models\ProstheticCase::STATUS_WAITING_APPROVAL,
            \App\Models\ProstheticCase::STATUS_APPROVED,
            \App\Models\ProstheticCase::STATUS_IN_PRODUCTION,
            \App\Models\ProstheticCase::STATUS_TRIAL_FIT,
            \App\Models\ProstheticCase::STATUS_DELIVERED,
            \App\Models\ProstheticCase::STATUS_UNDER_FOLLOW_UP,
        ];
    @endphp
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded d-flex align-items-center justify-content-center {{ $statusMetaCurrent['bg'] }}"
                         style="width:48px;height:48px;">
                        <i class="{{ $statusMetaCurrent['icon'] }} text-white fs-3"></i>
                    </div>
                    <div>
                        <h4 class="mb-1"><code>{{ $prosthetic_case->case_number }}</code></h4>
                        <p class="mb-1 fw-medium">
                            {{ $prosthetic_case->patient->name ?? '—' }}
                            <small class="text-muted">(ID {{ $prosthetic_case->patient_id }})</small>
                        </p>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge {{ $statusMetaCurrent['bg'] }} d-inline-flex align-items-center gap-1">
                                <i class="{{ $statusMetaCurrent['icon'] }}"></i>
                                {{ localize('global.prosthetics_case_status_'.$prosthetic_case->status) }}
                            </span>
                            @if ($prosthetic_case->referral)
                                <span class="text-muted small">
                                    <i class="bx bx-receipt me-1"></i>
                                    {{ localize('global.prosthetics_referral') }}: {{ $prosthetic_case->referral->referral_number ?? '—' }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('prosthetics.cases.index') }}" class="btn btn-sm btn-outline-secondary">{{ localize('global.back') }}</a>
                    <a href="{{ route('prosthetics.cases.print', $prosthetic_case) }}" class="btn btn-sm btn-outline-success">{{ localize('global.prosthetics_print_summary') }}</a>
                </div>
            </div>

            @if ($isCaseReadOnly)
                <div class="alert alert-secondary d-flex align-items-start gap-2">
                    <i class="bx bx-lock-alt mt-1" aria-hidden="true"></i>
                    <div>
                        <div class="fw-semibold">{{ __('global.prosthetics_case_readonly_notice') }}</div>
                    </div>
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($workflowSteps as $idx => $st)
                            @php
                                $stepRank = $workflowRanks[$st] ?? $idx;
                                $isDone = $stepRank <= $caseRank;
                            @endphp
                            <span class="badge rounded-pill {{ $isDone ? 'bg-primary' : 'bg-label-secondary' }}">
                                {{ $idx + 1 }}. {{ localize('global.prosthetics_case_status_'.$st) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Assessment --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong class="d-inline-flex align-items-center gap-2">
                        <i class="bx bx-user-check"></i>
                        {{ localize('global.prosthetics_assessment') }}
                    </strong>
                    @if (! $canEditAssessment)
                        <span class="badge bg-label-warning">{{ localize('global.completed') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('prosthetics.cases.assessment', $prosthetic_case) }}"
                          {{ $canEditAssessment ? '' : 'onsubmit="return false;"' }}>
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">{{ localize('global.prosthetics_fit_outcome_label') }}</label>
                            <select name="fit_outcome" class="form-select form-select-sm" {{ $canEditAssessment ? '' : 'disabled' }}>
                                @foreach (['pending', 'fit_for_device', 'delay', 'not_suitable', 'temporary_device', 'permanent_device'] as $o)
                                    <option value="{{ $o }}" @selected(old('fit_outcome', optional($prosthetic_case->assessment)->fit_outcome ?? 'pending') === $o)>{{ localize('global.prosthetics_fit_outcome_'.$o) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <textarea name="history_present_condition" class="form-control form-control-sm" rows="2" placeholder="{{ localize('global.prosthetics_history_present_condition_placeholder') }}" {{ $canEditAssessment ? '' : 'disabled' }}>{{ old('history_present_condition', optional($prosthetic_case->assessment)->history_present_condition) }}</textarea>
                        </div>
                        <div class="mb-2">
                            <textarea name="skin_stump_notes" class="form-control form-control-sm" rows="2" placeholder="{{ localize('global.prosthetics_skin_stump_placeholder') }}" {{ $canEditAssessment ? '' : 'disabled' }}>{{ old('skin_stump_notes', optional($prosthetic_case->assessment)->skin_stump_notes) }}</textarea>
                        </div>
                        <div class="mb-2">
                            <textarea name="functional_goals" class="form-control form-control-sm" rows="2" placeholder="{{ localize('global.prosthetics_functional_goals_placeholder') }}" {{ $canEditAssessment ? '' : 'disabled' }}>{{ old('functional_goals', optional($prosthetic_case->assessment)->functional_goals) }}</textarea>
                        </div>
                        @if ($canEditAssessment)
                            <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }}</button>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Measurements --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong class="d-inline-flex align-items-center gap-2">
                        <i class="bx bx-ruler"></i>
                        {{ localize('global.prosthetics_measurements') }}
                    </strong>
                    @if ($latestMeasurementSet?->is_locked)
                        <span class="badge bg-label-warning">{{ localize('global.prosthetics_locked') }} v{{ $latestMeasurementSet->version }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @php
                        $measurementsDisabled = ! $canEditMeasurements || ($latestMeasurementSet?->is_locked ?? false);
                    @endphp
                    <form method="post" action="{{ route('prosthetics.cases.measurements', $prosthetic_case) }}"
                          {{ $measurementsDisabled ? 'onsubmit="return false;"' : '' }}>
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.name') }}</th>
                                        <th>{{ localize('global.value') }}</th>
                                        <th>{{ localize('global.unit') }}</th>
                                        <th>{{ localize('global.notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($idx = 0; $idx < 8; $idx++)
                                        @php $row = $measRows->get($idx); @endphp
                                        <tr>
                                            <td><input class="form-control form-control-sm" name="rows[{{ $idx }}][name]" value="{{ old('rows.'.$idx.'.name', $row->name ?? '') }}" {{ $measurementsDisabled ? 'disabled' : '' }}></td>
                                            <td><input class="form-control form-control-sm" name="rows[{{ $idx }}][value_numeric]" value="{{ old('rows.'.$idx.'.value_numeric', $row->value_numeric ?? '') }}" {{ $measurementsDisabled ? 'disabled' : '' }}></td>
                                            <td><input class="form-control form-control-sm" name="rows[{{ $idx }}][unit]" value="{{ old('rows.'.$idx.'.unit', $row->unit ?? '') }}" {{ $measurementsDisabled ? 'disabled' : '' }}></td>
                                            <td><input class="form-control form-control-sm" name="rows[{{ $idx }}][notes]" value="{{ old('rows.'.$idx.'.notes', $row->notes ?? '') }}" {{ $measurementsDisabled ? 'disabled' : '' }}></td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                        @if (! $measurementsDisabled)
                            <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }}</button>
                        @endif
                    </form>
                    @if ($canEditMeasurements && $latestMeasurementSet && ! $latestMeasurementSet->is_locked)
                        <form method="post" action="{{ route('prosthetics.cases.measurements.lock', $prosthetic_case) }}" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-warning">{{ localize('global.prosthetics_lock_measurement_set') }}</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Prescription --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong class="d-inline-flex align-items-center gap-2">
                        <i class="bx bx-file"></i>
                        {{ localize('global.prosthetics_prescription') }}
                    </strong>
                    @if (! $canEditPrescription)
                        <span class="badge bg-label-warning">{{ localize('global.completed') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('prosthetics.cases.prescription', $prosthetic_case) }}"
                          {{ $canEditPrescription ? '' : 'onsubmit="return false;"' }}>
                        @csrf
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label class="form-label small">{{ localize('global.prosthetics_device_timing_label') }}</label>
                                <select name="device_timing" class="form-select form-select-sm" {{ $canEditPrescription ? '' : 'disabled' }}>
                                    @foreach (['definitive', 'temporary', 'preparatory'] as $t)
                                        <option value="{{ $t }}" @selected(old('device_timing', optional($activePrescription)->device_timing ?: 'definitive') === $t)>{{ localize('global.prosthetics_device_timing_'.$t) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <textarea name="special_instructions" class="form-control form-control-sm mb-2" rows="2" placeholder="{{ localize('global.prosthetics_special_instructions_placeholder') }}" {{ $canEditPrescription ? '' : 'disabled' }}>{{ old('special_instructions', $activePrescription->special_instructions ?? '') }}</textarea>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.prosthetics_component') }}</th>
                                        <th>{{ localize('global.quantity') }}</th>
                                        <th>{{ localize('global.notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($idx = 0; $idx < 8; $idx++)
                                        @php $line = $rxLines->get($idx); @endphp
                                        <tr>
                                            <td style="min-width:220px">
                                                <select name="lines[{{ $idx }}][catalog_id]" class="form-select form-select-sm" {{ $canEditPrescription ? '' : 'disabled' }}>
                                                    <option value="">—</option>
                                                    @foreach ($catalog as $item)
                                                        <option value="{{ $item->id }}" @selected(old('lines.'.$idx.'.catalog_id', $line->prosthetic_component_catalog_id ?? '') == $item->id)>
                                                            {{ $item->item_code }} — {{ $item->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input class="form-control form-control-sm" type="number" step="0.001" min="0" name="lines[{{ $idx }}][quantity]" value="{{ old('lines.'.$idx.'.quantity', $line->quantity ?? '1') }}" {{ $canEditPrescription ? '' : 'disabled' }}></td>
                                            <td><input class="form-control form-control-sm" name="lines[{{ $idx }}][notes]" value="{{ old('lines.'.$idx.'.notes', $line->notes ?? '') }}" {{ $canEditPrescription ? '' : 'disabled' }}></td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                        @if ($canEditPrescription)
                            <button type="submit" class="btn btn-sm btn-primary">
                                {{ localize('global.save') }} &amp; {{ localize('global.prosthetics_finalize_prescription') }}
                            </button>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Estimate --}}
            @if ($latestEstimate)
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong class="d-inline-flex align-items-center gap-2">
                            <i class="bx bx-money"></i>
                            {{ localize('global.prosthetics_estimate') }}
                        </strong>
                        @if (! $canEditEstimate)
                            <span class="badge bg-label-warning">{{ localize('global.prosthetics_read_only_badge') }}</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <p class="mb-1">{{ localize('global.parts') }}: <strong>{{ number_format($latestEstimate->parts_total, 2) }}</strong> {{ $latestEstimate->currency }}</p>
                        @php
                            $estimateDisabled = ! $canEditEstimate;
                        @endphp
                        <form method="post" action="{{ route('prosthetics.cases.estimate', $prosthetic_case) }}" class="row g-2 align-items-end"
                              {{ $estimateDisabled ? 'onsubmit="return false;"' : '' }}>
                            @csrf
                            <input type="hidden" name="estimate_id" value="{{ $latestEstimate->id }}">
                            <div class="col-auto">
                                <label class="form-label small">{{ localize('global.prosthetics_labor') }}</label>
                                <input type="number" step="0.01" name="labor_total" class="form-control form-control-sm" value="{{ old('labor_total', $latestEstimate->labor_total) }}" {{ $estimateDisabled ? 'disabled' : '' }}>
                            </div>
                            <div class="col-auto">
                                <label class="form-label small">{{ localize('global.prosthetics_discount') }}</label>
                                <input type="number" step="0.01" name="discount" class="form-control form-control-sm" value="{{ old('discount', $latestEstimate->discount) }}" {{ $estimateDisabled ? 'disabled' : '' }}>
                            </div>
                            <div class="col-auto">
                                @if (! $estimateDisabled)
                                    <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }}</button>
                                @endif
                            </div>
                        </form>
                        <p class="mt-2 mb-0"><strong>{{ localize('global.total') }}:</strong> {{ number_format($latestEstimate->total, 2) }} {{ $latestEstimate->currency }} ({{ $latestEstimate->status }})</p>
                    </div>
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong class="d-inline-flex align-items-center gap-2">
                        <i class="bx bx-refresh"></i>
                        {{ localize('global.prosthetics_workflow') }}
                    </strong>
                </div>
                <div class="card-body d-flex flex-wrap gap-2">
                    @if ($canSubmitForApproval)
                        <form method="post" action="{{ route('prosthetics.cases.submit_approval', $prosthetic_case) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">{{ localize('global.prosthetics_submit_for_approval') }}</button>
                        </form>
                    @endif

                    @if ($canApproveCase)
                        <form method="post" action="{{ route('prosthetics.cases.approve', $prosthetic_case) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">{{ localize('global.prosthetics_approve_case') }}</button>
                        </form>
                    @endif

                    @if (! $canSubmitForApproval && ! $canApproveCase)
                        <span class="text-muted small mt-1">{{ localize('global.prosthetics_workflow_actions_locked') }}</span>
                    @endif
                </div>
            </div>

            {{-- Work order --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong class="d-inline-flex align-items-center gap-2">
                        <i class="bx bx-briefcase"></i>
                        {{ localize('global.prosthetics_work_order') }}
                    </strong>
                </div>
                <div class="card-body">
                    @if ($activeWorkOrder)
                        <p class="mb-2">
                            <code>{{ $activeWorkOrder->work_order_number }}</code>
                                <span class="text-muted">— {{ $activeWorkOrder->status }} / {{ localize('global.prosthetics_work_order_stage_'.$activeWorkOrder->production_stage) }}</span>
                        </p>

                        @php
                            $workOrderStageDisabled = ! $canUpdateWorkOrderStage;
                        @endphp

                        <form method="post"
                              action="{{ route('prosthetics.work_orders.update', $activeWorkOrder) }}"
                              {{ $workOrderStageDisabled ? 'onsubmit="return false;"' : '' }}>
                            @csrf
                            @method('PUT')
                            <div class="row g-2 align-items-end">
                                <div class="col-auto">
                                    <select name="production_stage" class="form-select form-select-sm" {{ $workOrderStageDisabled ? 'disabled' : '' }}>
                                        @foreach (['pending', 'materials_issued', 'socket_fabrication', 'assembly', 'trial_fit_ready', 'quality_control', 'ready_for_delivery', 'completed'] as $st)
                                        <option value="{{ $st }}" @selected($activeWorkOrder->production_stage === $st)>{{ localize('global.prosthetics_work_order_stage_'.$st) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    @if ($canUpdateWorkOrderStage)
                                        <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }}</button>
                                    @endif
                                </div>
                            </div>
                        </form>
                        @if ($canIssueStock)
                            <form method="post" action="{{ route('prosthetics.cases.issue_stock', $prosthetic_case) }}" class="mt-2">
                                @csrf
                                <input type="hidden" name="prosthetic_work_order_id" value="{{ $activeWorkOrder->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-danger">{{ localize('global.prosthetics_issue_components') }}</button>
                            </form>
                        @else
                            <div class="text-muted small mt-2">{{ localize('global.prosthetics_stock_issue_locked') }}</div>
                        @endif
                    @else
                        @if ($canCreateWorkOrder)
                            <form method="post" action="{{ route('prosthetics.cases.work_order', $prosthetic_case) }}" class="row g-2">
                                @csrf
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.prosthetics_create_work_order') }}</button>
                                </div>
                            </form>
                        @else
                            <div class="text-muted small">{{ localize('global.prosthetics_work_order_available_after_approval') }}</div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Fitting --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong class="d-inline-flex align-items-center gap-2">
                        <i class="bx bx-walk"></i>
                        {{ localize('global.prosthetics_fitting') }}
                    </strong>
                    @if (! $canStoreFitting)
                        <span class="badge bg-label-warning">{{ localize('global.prosthetics_read_only_badge') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <form method="post"
                          action="{{ route('prosthetics.cases.fitting', $prosthetic_case) }}"
                          class="row g-2"
                          {{ $canStoreFitting ? '' : 'onsubmit="return false;"' }}>
                        @csrf
                        <div class="col-md-3">
                            <input type="date" name="session_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required {{ $canStoreFitting ? '' : 'disabled' }}>
                        </div>
                        <div class="col-md-3">
                            <select name="outcome" class="form-select form-select-sm" {{ $canStoreFitting ? '' : 'disabled' }}>
                                @foreach (['pending', 'passed', 'minor_adjustment', 'major_rework', 'remake'] as $o)
                                    <option value="{{ $o }}">{{ localize('global.prosthetics_fitting_outcome_'.$o) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="{{ localize('global.notes') }}" {{ $canStoreFitting ? '' : 'disabled' }}>
                        </div>
                        <div class="col-auto">
                            @if ($canStoreFitting)
                                <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }}</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Delivery --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong class="d-inline-flex align-items-center gap-2">
                        <i class="bx bx-package"></i>
                        {{ localize('global.prosthetics_delivery') }}
                    </strong>
                    @if (! $canStoreDelivery)
                        <span class="badge bg-label-warning">{{ localize('global.prosthetics_read_only_badge') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('prosthetics.cases.delivery', $prosthetic_case) }}"
                          {{ $canStoreDelivery ? '' : 'onsubmit="return false;"' }}>
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small">{{ localize('global.prosthetics_delivery_date') }}</label>
                                <input type="date" name="delivered_at" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required {{ $canStoreDelivery ? '' : 'disabled' }}>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">{{ localize('global.prosthetics_received_by') }}</label>
                                <input type="text" name="received_by_name" class="form-control form-control-sm" {{ $canStoreDelivery ? '' : 'disabled' }}>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="handover_signed" value="1" id="handover_signed" {{ $canStoreDelivery ? '' : 'disabled' }}>
                                    <label class="form-check-label" for="handover_signed">{{ localize('global.prosthetics_handover_signed') }}</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="{{ localize('global.notes') }}" {{ $canStoreDelivery ? '' : 'disabled' }}></textarea>
                            </div>
                            <div class="col-12">
                                @if ($canStoreDelivery)
                                    <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }}</button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Follow-up --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong class="d-inline-flex align-items-center gap-2">
                        <i class="bx bx-calendar"></i>
                        {{ localize('global.prosthetics_follow_up') }}
                    </strong>
                    @if (! $canStoreFollowUp)
                        <span class="badge bg-label-warning">{{ localize('global.prosthetics_read_only_badge') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('prosthetics.cases.follow_up', $prosthetic_case) }}" class="row g-2"
                          {{ $canStoreFollowUp ? '' : 'onsubmit="return false;"' }}>
                        @csrf
                        <div class="col-md-3">
                            <input type="date" name="scheduled_at" class="form-control form-control-sm" value="{{ now()->addMonth()->format('Y-m-d') }}" required {{ $canStoreFollowUp ? '' : 'disabled' }}>
                        </div>
                        <div class="col-md-3">
                            <select name="follow_up_type" class="form-select form-select-sm" {{ $canStoreFollowUp ? '' : 'disabled' }}>
                                @foreach (['1_week', '1_month', '3_month', '6_month', 'annual', 'unscheduled'] as $t)
                                    <option value="{{ $t }}">{{ localize('global.prosthetics_follow_up_type_'.$t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            @if ($canStoreFollowUp)
                                <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }}</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Attachments --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong class="d-inline-flex align-items-center gap-2">
                        <i class="bx bx-paperclip"></i>
                        {{ localize('global.prosthetics_attachments') }}
                    </strong>
                    <span class="text-muted small">{{ $prosthetic_case->attachments->count() }} {{ localize('global.prosthetics_files') }}</span>
                </div>
                <div class="card-body">
                    @php
                        $attachmentsDisabled = ! $canManageAttachments;
                    @endphp
                    <form method="post"
                          action="{{ route('prosthetics.cases.attachments.upload', $prosthetic_case) }}"
                          enctype="multipart/form-data"
                          class="mb-3"
                          {{ $attachmentsDisabled ? 'onsubmit="return false;"' : '' }}>
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small">{{ localize('global.category') }}</label>
                                <input type="text" name="category" class="form-control form-control-sm" value="general" {{ $attachmentsDisabled ? 'disabled' : '' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">{{ localize('global.prosthetics_files') }}</label>
                                <input type="file" name="files[]" class="form-control form-control-sm" multiple {{ $attachmentsDisabled ? 'disabled' : '' }}>
                            </div>
                            <div class="col-auto">
                                @if (! $attachmentsDisabled)
                                    <button type="submit" class="btn btn-sm btn-primary mt-0">{{ localize('global.prosthetics_upload') }}</button>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="form-label small">{{ localize('global.prosthetics_description_optional') }}</label>
                            <input type="text" name="description" class="form-control form-control-sm" {{ $attachmentsDisabled ? 'disabled' : '' }}>
                        </div>
                        @if ($attachmentsDisabled)
                            <div class="text-muted small mt-2 d-flex align-items-center gap-2">
                                <i class="bx bx-lock-alt"></i> {{ localize('global.prosthetics_attachments_readonly_notice') }}
                            </div>
                        @endif
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.file') }}</th>
                                    <th>{{ localize('global.category') }}</th>
                                    <th>{{ localize('global.date') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($prosthetic_case->attachments->sortByDesc('created_at') as $att)
                                    <tr>
                                        <td style="min-width:240px">
                                            <a href="{{ $att->file_url }}" target="_blank" class="text-primary text-decoration-underline">
                                                {{ $att->original_name ?? basename($att->path) }}
                                            </a>
                                        </td>
                                        <td>{{ $att->category ?? 'general' }}</td>
                                        <td>{{ $att->created_at?->format('Y-m-d') }}</td>
                                        <td class="text-end">
                                            @if (! $attachmentsDisabled)
                                                <form method="post"
                                                      action="{{ route('prosthetics.attachments.delete', $att->id) }}"
                                                      onsubmit="return confirm('{{ addslashes(localize('global.prosthetics_delete_attachment_confirm')) }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ localize('global.delete') }}</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($prosthetic_case->attachments->count() === 0)
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">{{ localize('global.no_attachments') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($canCloseCase)
                <form method="post" action="{{ route('prosthetics.cases.close', $prosthetic_case) }}" onsubmit="return confirm('{{ addslashes(localize('global.prosthetics_close_case_confirm')) }}');">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">{{ localize('global.prosthetics_close_case') }}</button>
                </form>
            @endif
        </div>
    </div>
@endsection
