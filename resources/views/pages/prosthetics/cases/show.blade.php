@extends('layouts.master')

@section('content')
    @php
        $measRows = ($latestMeasurementSet?->measurements ?? collect())->values();
        $rxLines = ($activePrescription?->lines ?? collect())->values();
    @endphp
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <div>
                    <h4 class="mb-1"><code>{{ $prosthetic_case->case_number }}</code></h4>
                    <p class="mb-0">
                        <span class="badge bg-label-secondary">{{ $prosthetic_case->status }}</span>
                        &mdash; {{ $prosthetic_case->patient->name ?? '—' }}
                        <small class="text-muted">(ID {{ $prosthetic_case->patient_id }})</small>
                    </p>
                </div>
                <a href="{{ route('prosthetics.cases.index') }}" class="btn btn-sm btn-outline-secondary">{{ localize('global.back') }}</a>
            </div>

            {{-- Assessment --}}
            <div class="card mb-3">
                <div class="card-header"><strong>{{ localize('global.prosthetics_assessment') }}</strong></div>
                <div class="card-body">
                    <form method="post" action="{{ route('prosthetics.cases.assessment', $prosthetic_case) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Fit outcome</label>
                            <select name="fit_outcome" class="form-select form-select-sm">
                                @foreach (['pending', 'fit_for_device', 'delay', 'not_suitable', 'temporary_device', 'permanent_device'] as $o)
                                    <option value="{{ $o }}" @selected(old('fit_outcome', optional($prosthetic_case->assessment)->fit_outcome ?? 'pending') === $o)>{{ $o }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <textarea name="history_present_condition" class="form-control form-control-sm" rows="2" placeholder="History / present condition">{{ old('history_present_condition', optional($prosthetic_case->assessment)->history_present_condition) }}</textarea>
                        </div>
                        <div class="mb-2">
                            <textarea name="skin_stump_notes" class="form-control form-control-sm" rows="2" placeholder="Skin / stump">{{ old('skin_stump_notes', optional($prosthetic_case->assessment)->skin_stump_notes) }}</textarea>
                        </div>
                        <div class="mb-2">
                            <textarea name="functional_goals" class="form-control form-control-sm" rows="2" placeholder="Functional goals">{{ old('functional_goals', optional($prosthetic_case->assessment)->functional_goals) }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }}</button>
                    </form>
                </div>
            </div>

            {{-- Measurements --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>{{ localize('global.prosthetics_measurements') }}</strong>
                    @if ($latestMeasurementSet?->is_locked)
                        <span class="badge bg-label-warning">{{ localize('global.prosthetics_locked') }} v{{ $latestMeasurementSet->version }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('prosthetics.cases.measurements', $prosthetic_case) }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Value</th>
                                        <th>Unit</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($idx = 0; $idx < 8; $idx++)
                                        @php $row = $measRows->get($idx); @endphp
                                        <tr>
                                            <td><input class="form-control form-control-sm" name="rows[{{ $idx }}][name]" value="{{ old('rows.'.$idx.'.name', $row->name ?? '') }}"></td>
                                            <td><input class="form-control form-control-sm" name="rows[{{ $idx }}][value_numeric]" value="{{ old('rows.'.$idx.'.value_numeric', $row->value_numeric ?? '') }}"></td>
                                            <td><input class="form-control form-control-sm" name="rows[{{ $idx }}][unit]" value="{{ old('rows.'.$idx.'.unit', $row->unit ?? '') }}"></td>
                                            <td><input class="form-control form-control-sm" name="rows[{{ $idx }}][notes]" value="{{ old('rows.'.$idx.'.notes', $row->notes ?? '') }}"></td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                        @if (!$latestMeasurementSet?->is_locked)
                            <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }}</button>
                        @endif
                    </form>
                    @if ($latestMeasurementSet && !$latestMeasurementSet->is_locked)
                        <form method="post" action="{{ route('prosthetics.cases.measurements.lock', $prosthetic_case) }}" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-warning">{{ localize('global.prosthetics_lock_measurement_set') }}</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Prescription --}}
            <div class="card mb-3">
                <div class="card-header"><strong>{{ localize('global.prosthetics_prescription') }}</strong></div>
                <div class="card-body">
                    <form method="post" action="{{ route('prosthetics.cases.prescription', $prosthetic_case) }}">
                        @csrf
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label class="form-label small">Device timing</label>
                                <select name="device_timing" class="form-select form-select-sm">
                                    @foreach (['definitive', 'temporary', 'preparatory'] as $t)
                                        <option value="{{ $t }}" @selected(old('device_timing', optional($activePrescription)->device_timing ?: 'definitive') === $t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <textarea name="special_instructions" class="form-control form-control-sm mb-2" rows="2" placeholder="Special instructions">{{ old('special_instructions', $activePrescription->special_instructions ?? '') }}</textarea>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Component</th>
                                        <th>Qty</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($idx = 0; $idx < 8; $idx++)
                                        @php $line = $rxLines->get($idx); @endphp
                                        <tr>
                                            <td style="min-width:220px">
                                                <select name="lines[{{ $idx }}][catalog_id]" class="form-select form-select-sm">
                                                    <option value="">—</option>
                                                    @foreach ($catalog as $item)
                                                        <option value="{{ $item->id }}" @selected(old('lines.'.$idx.'.catalog_id', $line->prosthetic_component_catalog_id ?? '') == $item->id)>
                                                            {{ $item->item_code }} — {{ $item->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input class="form-control form-control-sm" type="number" step="0.001" min="0" name="lines[{{ $idx }}][quantity]" value="{{ old('lines.'.$idx.'.quantity', $line->quantity ?? '1') }}"></td>
                                            <td><input class="form-control form-control-sm" name="lines[{{ $idx }}][notes]" value="{{ old('lines.'.$idx.'.notes', $line->notes ?? '') }}"></td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }} &amp; finalize prescription</button>
                    </form>
                </div>
            </div>

            {{-- Estimate --}}
            @if ($latestEstimate)
                <div class="card mb-3">
                    <div class="card-header"><strong>{{ localize('global.prosthetics_estimate') }}</strong></div>
                    <div class="card-body">
                        <p class="mb-1">{{ localize('global.parts') ?? 'Parts' }}: <strong>{{ number_format($latestEstimate->parts_total, 2) }}</strong> {{ $latestEstimate->currency }}</p>
                        <form method="post" action="{{ route('prosthetics.cases.estimate', $prosthetic_case) }}" class="row g-2 align-items-end">
                            @csrf
                            <input type="hidden" name="estimate_id" value="{{ $latestEstimate->id }}">
                            <div class="col-auto">
                                <label class="form-label small">Labor</label>
                                <input type="number" step="0.01" name="labor_total" class="form-control form-control-sm" value="{{ old('labor_total', $latestEstimate->labor_total) }}">
                            </div>
                            <div class="col-auto">
                                <label class="form-label small">Discount</label>
                                <input type="number" step="0.01" name="discount" class="form-control form-control-sm" value="{{ old('discount', $latestEstimate->discount) }}">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }}</button>
                            </div>
                        </form>
                        <p class="mt-2 mb-0"><strong>Total:</strong> {{ number_format($latestEstimate->total, 2) }} {{ $latestEstimate->currency }} ({{ $latestEstimate->status }})</p>
                    </div>
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-header"><strong>{{ localize('global.prosthetics_workflow') }}</strong></div>
                <div class="card-body d-flex flex-wrap gap-2">
                    <form method="post" action="{{ route('prosthetics.cases.submit_approval', $prosthetic_case) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">Submit for approval</button>
                    </form>
                    <form method="post" action="{{ route('prosthetics.cases.approve', $prosthetic_case) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">Approve case</button>
                    </form>
                </div>
            </div>

            {{-- Work order --}}
            <div class="card mb-3">
                <div class="card-header"><strong>{{ localize('global.prosthetics_work_order') }}</strong></div>
                <div class="card-body">
                    @if ($activeWorkOrder)
                        <p><code>{{ $activeWorkOrder->work_order_number }}</code> — {{ $activeWorkOrder->status }} / {{ $activeWorkOrder->production_stage }}</p>
                        <form method="post" action="{{ route('prosthetics.work_orders.update', $activeWorkOrder) }}">
                            @csrf
                            @method('PUT')
                            <div class="row g-2 align-items-end">
                                <div class="col-auto">
                                    <select name="production_stage" class="form-select form-select-sm">
                                        @foreach (['pending', 'materials_issued', 'socket_fabrication', 'assembly', 'trial_fit_ready', 'quality_control', 'ready_for_delivery', 'completed'] as $st)
                                            <option value="{{ $st }}" @selected($activeWorkOrder->production_stage === $st)>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }}</button>
                                </div>
                            </div>
                        </form>
                        <form method="post" action="{{ route('prosthetics.cases.issue_stock', $prosthetic_case) }}" class="mt-2">
                            @csrf
                            <input type="hidden" name="prosthetic_work_order_id" value="{{ $activeWorkOrder->id }}">
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ localize('global.prosthetics_issue_components') }}</button>
                        </form>
                    @else
                        <form method="post" action="{{ route('prosthetics.cases.work_order', $prosthetic_case) }}" class="row g-2">
                            @csrf
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-primary">Create work order</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Fitting --}}
            <div class="card mb-3">
                <div class="card-header"><strong>{{ localize('global.prosthetics_fitting') }}</strong></div>
                <div class="card-body">
                    <form method="post" action="{{ route('prosthetics.cases.fitting', $prosthetic_case) }}" class="row g-2">
                        @csrf
                        <div class="col-md-3">
                            <input type="date" name="session_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <select name="outcome" class="form-select form-select-sm">
                                @foreach (['pending', 'passed', 'minor_adjustment', 'major_rework', 'remake'] as $o)
                                    <option value="{{ $o }}">{{ $o }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Notes">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Delivery --}}
            <div class="card mb-3">
                <div class="card-header"><strong>{{ localize('global.prosthetics_delivery') }}</strong></div>
                <div class="card-body">
                    <form method="post" action="{{ route('prosthetics.cases.delivery', $prosthetic_case) }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small">Delivery date</label>
                                <input type="date" name="delivered_at" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Received by</label>
                                <input type="text" name="received_by_name" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="handover_signed" value="1" id="handover_signed">
                                    <label class="form-check-label" for="handover_signed">Handover signed</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Notes"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Follow-up --}}
            <div class="card mb-3">
                <div class="card-header"><strong>{{ localize('global.prosthetics_follow_up') }}</strong></div>
                <div class="card-body">
                    <form method="post" action="{{ route('prosthetics.cases.follow_up', $prosthetic_case) }}" class="row g-2">
                        @csrf
                        <div class="col-md-3">
                            <input type="date" name="scheduled_at" class="form-control form-control-sm" value="{{ now()->addMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <select name="follow_up_type" class="form-select form-select-sm">
                                @foreach (['1_week', '1_month', '3_month', '6_month', 'annual', 'unscheduled'] as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <form method="post" action="{{ route('prosthetics.cases.close', $prosthetic_case) }}" onsubmit="return confirm('Close case?');">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">{{ localize('global.close') ?? 'Close case' }}</button>
            </form>
        </div>
    </div>
@endsection
