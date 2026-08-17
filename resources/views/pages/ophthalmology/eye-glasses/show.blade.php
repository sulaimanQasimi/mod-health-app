@extends('layouts.master')

@section('content')
@php
    $patient = $order->appointment?->patient;
    $patientName = trim(($patient?->name ?? '') . ' ' . ($patient?->last_name ?? ''));
    $prescription = old('prescription', $order->prescription ?? ['od' => [], 'os' => [], 'ipd' => '']);
    $rxVal = function (string $side, string $key) use ($prescription) {
        return data_get($prescription, "{$side}.{$key}", '');
    };
    $genderLabel = match ((string) ($patient?->gender ?? '')) {
        '0' => localize('global.male'),
        '1' => localize('global.female'),
        default => $patient?->gender ?: '—',
    };
    $statusClass = match ($order->status) {
        'requested' => 'warning',
        'processing' => 'info',
        'paid' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger',
        default => 'secondary',
    };
    $steps = ['requested', 'processing', 'paid', 'delivered'];
    $stepIndex = array_search($order->status, $steps, true);
    $rxFields = [
        ['sphere', 'oph_sphere'],
        ['cylinder', 'oph_cylinder'],
        ['axis', 'oph_axis'],
        ['add', 'oph_add'],
        ['prism_horizontal', 'oph_prism_h'],
        ['prism_vertical', 'oph_prism_v'],
    ];
    $canEdit = $permissions['edit'];
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif

        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="bx bx-glasses me-2 text-primary"></i>{{ localize('global.eye_glasses_order') }}
                </h4>
                <div class="text-muted">{{ localize('global.ref_no') }}: {{ $order->ref_no }}</div>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="badge bg-{{ $statusClass }} fs-6">{{ localize('global.eye_glasses_status_' . $order->status) }}</span>
                <a href="{{ route('eye-glasses-orders.print', $order) }}" target="_blank" class="btn btn-outline-secondary">
                    <i class="bx bx-printer me-1"></i>{{ localize('global.print') }}
                </a>
                @if ($permissions['delete'])
                    <form action="{{ route('eye-glasses-orders.destroy', $order) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('{{ localize('global.are_you_sure') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bx bx-trash me-1"></i>{{ localize('global.delete') }}
                        </button>
                    </form>
                @endif
                <a href="{{ route('eye-glasses-orders.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
                </a>
            </div>
        </div>

        @if ($order->appointment?->is_completed)
            <div class="alert alert-warning">{{ localize('global.oph_readonly_appointment_completed') }}</div>
        @endif

        <div class="row g-3 mb-4">
            @foreach ($steps as $index => $step)
                @php
                    $done = $order->status === 'delivered' || ($stepIndex !== false && $stepIndex > $index);
                    $current = $stepIndex === $index;
                @endphp
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body {{ $done ? 'bg-label-success' : ($current ? 'bg-label-primary' : 'bg-label-secondary') }}">
                            <div class="small text-muted">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</div>
                            <div class="fw-semibold">{{ localize('global.eye_glasses_step_' . $step) }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ localize('global.patient') }}</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><div class="text-muted small">{{ localize('global.patient_name') }}</div><div class="fw-semibold">{{ $patientName ?: '—' }}</div></div>
                    <div class="col-md-3"><div class="text-muted small">{{ localize('global.father_name') }}</div><div class="fw-semibold">{{ $patient?->father_name ?: '—' }}</div></div>
                    <div class="col-md-3"><div class="text-muted small">{{ localize('global.id_card') }}</div><div class="fw-semibold">{{ $patient?->id_card ?: '—' }}</div></div>
                    <div class="col-md-3"><div class="text-muted small">{{ localize('global.age') }}</div><div class="fw-semibold">{{ $patient?->age ?: '—' }}</div></div>
                    <div class="col-md-3"><div class="text-muted small">{{ localize('global.gender') }}</div><div class="fw-semibold">{{ $genderLabel }}</div></div>
                    <div class="col-md-3"><div class="text-muted small">{{ localize('global.phone') }}</div><div class="fw-semibold">{{ $patient?->phone ?: '—' }}</div></div>
                    <div class="col-md-3"><div class="text-muted small">{{ localize('global.branch') }}</div><div class="fw-semibold">{{ $order->branch?->name ?: '—' }}</div></div>
                    <div class="col-md-3"><div class="text-muted small">{{ localize('global.appointment_date') }}</div><div class="fw-semibold">{{ $order->appointment?->date ? verta($order->appointment->date)->format('Y/m/d') : '—' }}</div></div>
                </div>
                <div class="mt-3 d-flex flex-wrap gap-2">
                    @if ($patient?->id)
                        <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-primary">{{ localize('global.patient') }}</a>
                    @endif
                    @if ($order->appointment_id)
                        <a href="{{ route('appointments.show', $order->appointment_id) }}" class="btn btn-sm btn-outline-primary">{{ localize('global.appointment') }}</a>
                    @endif
                    @if ($order->ophthalmology_registration_id)
                        <a href="{{ route('ophthalmology-registrations.show', $order->ophthalmology_registration_id) }}" class="btn btn-sm btn-outline-primary">
                            {{ localize('global.ophthalmology_registration') }}
                            @if ($order->ophthalmologyRegistration?->ref_no)
                                ({{ $order->ophthalmologyRegistration->ref_no }})
                            @endif
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('eye-glasses-orders.update', $order) }}">
            @csrf
            @method('PUT')
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.eye_glasses_request') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ localize('global.examiner') }}</label>
                            <select name="examiner_id" class="form-select" @disabled(! $canEdit)>
                                <option value="">{{ localize('global.please_select') }}</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected((string) old('examiner_id', $order->examiner_id) === (string) $doctor->id)>{{ $doctor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ localize('global.eye_glasses_request_date') }}</label>
                            <input type="text" autocomplete="off" name="request_date" class="form-control datepicker_dari pdp-el"
                                   value="{{ old('request_date', $order->request_date ? verta($order->request_date)->format('Y-m-d') : '') }}"
                                   @disabled(! $canEdit)>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ localize('global.eye_glasses_quantity') }}</label>
                            <input type="number" min="1" max="10" name="quantity" class="form-control"
                                   value="{{ old('quantity', $order->quantity ?? 1) }}" @disabled(! $canEdit)>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ localize('global.eye_glasses_frame_type') }}</label>
                            <select name="frame_type" class="form-select" @disabled(! $canEdit)>
                                <option value="">{{ localize('global.please_select') }}</option>
                                @foreach (\App\Models\EyeGlassesOrder::FRAME_TYPES as $value)
                                    <option value="{{ $value }}" @selected(old('frame_type', $order->frame_type) === $value)>
                                        {{ localize('global.eye_glasses_frame_' . $value) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ localize('global.eye_glasses_lens_type') }}</label>
                            <select name="lens_type" class="form-select" @disabled(! $canEdit)>
                                <option value="">{{ localize('global.please_select') }}</option>
                                @foreach (\App\Models\EyeGlassesOrder::LENS_TYPES as $value)
                                    <option value="{{ $value }}" @selected(old('lens_type', $order->lens_type) === $value)>
                                        {{ localize('global.eye_glasses_lens_' . $value) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ localize('global.eye_glasses_lens_material') }}</label>
                            <select name="lens_material" class="form-select" @disabled(! $canEdit)>
                                <option value="">{{ localize('global.please_select') }}</option>
                                @foreach (\App\Models\EyeGlassesOrder::LENS_MATERIALS as $value)
                                    <option value="{{ $value }}" @selected(old('lens_material', $order->lens_material) === $value)>
                                        {{ localize('global.eye_glasses_material_' . $value) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ localize('global.eye_glasses_tint') }}</label>
                            <input type="text" name="tint" class="form-control" value="{{ old('tint', $order->tint) }}" @disabled(! $canEdit)>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">{{ localize('global.notes') }}</label>
                            <textarea name="notes" rows="2" class="form-control" @disabled(! $canEdit)>{{ old('notes', $order->notes) }}</textarea>
                        </div>
                    </div>

                    <h6 class="mt-4 mb-3">{{ localize('global.oph_glasses_rx') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.oph_measurement') }}</th>
                                    <th>OD</th>
                                    <th>OS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rxFields as [$key, $labelKey])
                                    <tr>
                                        <th class="w-25">{{ localize('global.' . $labelKey) }}</th>
                                        @foreach (['od', 'os'] as $side)
                                            <td>
                                                <input type="text" class="form-control"
                                                       name="prescription[{{ $side }}][{{ $key }}]"
                                                       value="{{ $rxVal($side, $key) }}"
                                                       @disabled(! $canEdit)>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                <tr>
                                    <th>IPD</th>
                                    <td colspan="2">
                                        <input type="text" class="form-control" name="prescription[ipd]"
                                               value="{{ data_get($prescription, 'ipd', '') }}" @disabled(! $canEdit)>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if ($canEdit)
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i>{{ localize('global.save') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </form>

        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0">{{ localize('global.eye_glasses_process') }}</h5></div>
                    <div class="card-body">
                        @if ($order->processed_at)
                            <div class="text-muted small">{{ localize('global.date') }}</div>
                            <div class="mb-2">{{ verta($order->processed_at)->format('Y/m/d H:i') }}</div>
                            <div class="text-muted small">{{ localize('global.user') }}</div>
                            <div class="mb-2">{{ $order->processedByUser?->name ?: '—' }}</div>
                            <div class="text-muted small">{{ localize('global.notes') }}</div>
                            <div>{{ $order->process_notes ?: '—' }}</div>
                        @else
                            <form method="POST" action="{{ route('eye-glasses-orders.process', $order) }}">
                                @csrf
                                <label class="form-label">{{ localize('global.notes') }}</label>
                                <textarea name="process_notes" rows="3" class="form-control mb-3" @disabled(! $permissions['process'])>{{ old('process_notes', $order->process_notes) }}</textarea>
                                @if ($permissions['process'])
                                    <button type="submit" class="btn btn-primary">{{ localize('global.eye_glasses_mark_processing') }}</button>
                                @endif
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0">{{ localize('global.eye_glasses_payment') }}</h5></div>
                    <div class="card-body">
                        @if ($order->paid_at)
                            <div class="text-muted small">{{ localize('global.amount') }}</div>
                            <div class="mb-2">{{ $order->amount ?? '—' }}</div>
                            <div class="text-muted small">{{ localize('global.eye_glasses_paid_amount') }}</div>
                            <div class="mb-2">{{ $order->paid_amount ?? '—' }}</div>
                            <div class="text-muted small">{{ localize('global.eye_glasses_payment_method') }}</div>
                            <div class="mb-2">{{ $order->payment_method ? localize('global.eye_glasses_pay_' . $order->payment_method) : '—' }}</div>
                            <div class="text-muted small">{{ localize('global.date') }}</div>
                            <div class="mb-2">{{ verta($order->paid_at)->format('Y/m/d H:i') }}</div>
                            <div class="text-muted small">{{ localize('global.user') }}</div>
                            <div>{{ $order->paidByUser?->name ?: '—' }}</div>
                        @else
                            <form method="POST" action="{{ route('eye-glasses-orders.payment', $order) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">{{ localize('global.amount') }} *</label>
                                    <input type="number" min="0" step="0.01" name="amount" class="form-control"
                                           value="{{ old('amount', $order->amount) }}" @disabled(! $permissions['pay'])>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ localize('global.eye_glasses_paid_amount') }}</label>
                                    <input type="number" min="0" step="0.01" name="paid_amount" class="form-control"
                                           value="{{ old('paid_amount', $order->paid_amount) }}" @disabled(! $permissions['pay'])>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ localize('global.eye_glasses_payment_method') }} *</label>
                                    <select name="payment_method" class="form-select" @disabled(! $permissions['pay'])>
                                        @foreach (\App\Models\EyeGlassesOrder::PAYMENT_METHODS as $value)
                                            <option value="{{ $value }}" @selected(old('payment_method', $order->payment_method ?? 'cash') === $value)>
                                                {{ localize('global.eye_glasses_pay_' . $value) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ localize('global.notes') }}</label>
                                    <textarea name="payment_notes" rows="2" class="form-control" @disabled(! $permissions['pay'])>{{ old('payment_notes', $order->payment_notes) }}</textarea>
                                </div>
                                @if ($permissions['pay'])
                                    <button type="submit" class="btn btn-primary">{{ localize('global.eye_glasses_record_payment') }}</button>
                                @endif
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0">{{ localize('global.eye_glasses_delivery') }}</h5></div>
                    <div class="card-body">
                        @if ($order->delivered_at)
                            <div class="text-muted small">{{ localize('global.date') }}</div>
                            <div class="mb-2">{{ verta($order->delivered_at)->format('Y/m/d H:i') }}</div>
                            <div class="text-muted small">{{ localize('global.user') }}</div>
                            <div class="mb-2">{{ $order->deliveredByUser?->name ?: '—' }}</div>
                            <div class="text-muted small">{{ localize('global.eye_glasses_received_by') }}</div>
                            <div class="mb-2">{{ $order->received_by ?: '—' }}</div>
                            <div class="text-muted small">{{ localize('global.notes') }}</div>
                            <div>{{ $order->delivery_notes ?: '—' }}</div>
                        @else
                            <form method="POST" action="{{ route('eye-glasses-orders.deliver', $order) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">{{ localize('global.eye_glasses_received_by') }}</label>
                                    <input type="text" name="received_by" class="form-control"
                                           value="{{ old('received_by', $order->received_by) }}" @disabled(! $permissions['deliver'])>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ localize('global.notes') }}</label>
                                    <textarea name="delivery_notes" rows="2" class="form-control" @disabled(! $permissions['deliver'])>{{ old('delivery_notes', $order->delivery_notes) }}</textarea>
                                </div>
                                @if ($permissions['deliver'])
                                    <button type="submit" class="btn btn-success">{{ localize('global.eye_glasses_mark_delivered') }}</button>
                                @endif
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($permissions['cancel'])
            <div class="card border-danger mb-4">
                <div class="card-header">
                    <h5 class="mb-0 text-danger">{{ localize('global.cancel') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('eye-glasses-orders.cancel', $order) }}"
                          onsubmit="return confirm('{{ localize('global.are_you_sure') }}');">
                        @csrf
                        <label class="form-label">{{ localize('global.eye_glasses_cancellation_reason') }}</label>
                        <textarea name="cancellation_reason" rows="2" class="form-control mb-3">{{ old('cancellation_reason', $order->cancellation_reason) }}</textarea>
                        <button type="submit" class="btn btn-danger">{{ localize('global.eye_glasses_cancel_order') }}</button>
                    </form>
                </div>
            </div>
        @endif

        @if ($order->status === 'cancelled')
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">{{ localize('global.status_cancelled') }}</h5></div>
                <div class="card-body">
                    <div class="text-muted small">{{ localize('global.date') }}</div>
                    <div class="mb-2">{{ $order->cancelled_at ? verta($order->cancelled_at)->format('Y/m/d H:i') : '—' }}</div>
                    <div class="text-muted small">{{ localize('global.user') }}</div>
                    <div class="mb-2">{{ $order->cancelledByUser?->name ?: '—' }}</div>
                    <div class="text-muted small">{{ localize('global.eye_glasses_cancellation_reason') }}</div>
                    <div>{{ $order->cancellation_reason ?: '—' }}</div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
