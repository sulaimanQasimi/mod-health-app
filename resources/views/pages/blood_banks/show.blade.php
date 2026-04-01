@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            @php
                $reservedUnitIds = $reservedUnitIds ?? collect();
            @endphp
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
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
                                        <div class="fs-5 fw-bold">{{ $requestedQty }}</div>
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

                        <div class="border rounded p-2 mb-3 d-flex gap-2 flex-wrap align-items-center">
                            <span class="badge bg-label-secondary">{{ localize('global.workflow') }}</span>
                            <span class="badge bg-label-primary">1. {{ localize('global.save_sample') }}</span>
                            <span class="badge bg-label-primary">2. {{ localize('global.crossmatch_status') }}</span>
                            <span class="badge bg-label-primary">3. {{ localize('global.reserve_unit') }}</span>
                            <span class="badge bg-label-primary">4. {{ localize('global.complete') }}</span>
                        </div>

                        <div class="col-md-12">
                            <div class="border border-label-primary mb-4">
                                <h5 class="mb-4 p-3 bg-label-primary text-center">
                                    {{ localize('global.blood_request_details') }}</h5>

                                <div class="row p-2 text-center">
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.patient_name') }}</h5>
                                        <div>
                                            {{ $bloodBank->patient?->name ?? '—' }}
                                        </div>
                                    </div>
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.requested_department') }}</h5>
                                        <div>
                                            {{ $bloodBank->department?->name ?? '—' }}
                                        </div>
                                    </div>
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.blood_group') }}</h5>
                                        <div>
                                            @if ($bloodBank->group == 'A')
                                                <span class="text-danger"><i class="fa-solid fa-a"></i></span>
                                            @elseif($bloodBank->group == 'B')
                                                <span class="text-danger"><i class="fa-solid fa-b"></i></span>
                                            @elseif($bloodBank->group == 'AB')
                                                <span class="text-danger" dir="ltr"><i class="fa-solid fa-a"></i><i
                                                        class="fa-solid fa-b"></i></span>
                                            @elseif($bloodBank->group == 'O')
                                                <span class="text-danger"><i class="fa-solid fa-o"></i></span>
                                            @endif

                                        </div>
                                    </div>
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.blood_rh') }}</h5>
                                        <div>
                                            @if ($bloodBank->rh == '+')
                                                <span class="bx bx-plus-circle text-danger"></span>
                                            @else
                                                <span class="bx bx-minus-circle text-danger"></span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="row p-2 text-center">
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.quantity') }}</h5>
                                        <div>
                                            {{ $bloodBank->quantity }}
                                        </div>
                                    </div>
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.blood_type') }}</h5>
                                        <div>
                                            {{ $bloodBank->type }}
                                        </div>
                                    </div>
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.created_by') }}</h5>
                                        <div>
                                            
                                            {{ $bloodBank->createdBy?->name ?? '—' }}
                                        </div>
                                    </div>
                                    <div class="col-md-3 mt-2 mb-2">
                                        <h5 class="mb-4 bg-label-primary p-1">{{ localize('global.created_at') }}</h5>
                                        <div dir="ltr">
                                            {{ \Hekmatinasser\Verta\Verta::instance($bloodBank->created_at)->format('Y/n/j H:i:s') }}
                                        </div>
                                    </div>
                                </div>

                                @if ($bloodBank->appointment_id)
                                    <div class="row p-2 text-center">
                                        <div class="col-12 mt-2 mb-2">
                                            <h5 class="mb-2 bg-label-secondary p-1">{{ localize('global.appointments') }}
                                                #{{ $bloodBank->appointment_id }}</h5>
                                            @if ($bloodBank->appointment)
                                                <a href="{{ route('appointments.show', $bloodBank->appointment_id) }}"
                                                    class="btn btn-sm btn-outline-primary">{{ localize('global.appointment_details') }}</a>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>

                        @if ($bloodBank->status === 'approved')
                            <div class="col-md-12 mt-3">
                                <div class="card border-primary">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">{{ localize('global.crossmatch_workflow') }}</h6>
                                        <span class="badge bg-label-primary">
                                            {{ localize('global.requested_quantity') }}: {{ (int) $bloodBank->quantity }}
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        @canany(['receive-blood-units', 'manage-blood-inventory'])
                                            <form action="{{ route('blood_banks.crossmatch.samples.store', $bloodBank->id) }}"
                                                method="POST" class="row g-2 mb-3 border-bottom pb-3">
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
                                                        class="btn btn-outline-primary w-100">{{ localize('global.save_sample') }}</button>
                                                </div>
                                            </form>
                                        @endcanany

                                        @if ($bloodBank->patientSamples->isNotEmpty())
                                            <div class="mb-3">
                                                <div class="small text-muted mb-1">{{ localize('global.patient_samples') }}</div>
                                                @foreach ($bloodBank->patientSamples as $sample)
                                                    <span class="badge bg-label-secondary me-1 mb-1">
                                                        {{ $sample->sample_id ?: '#'.$sample->id }}
                                                        @if ($sample->collected_at)
                                                            — {{ $sample->collected_at->format('Y-m-d H:i') }}
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        @php
                                            $reservedCompatible = $bloodBank->crossmatches
                                                ->filter(fn ($cx) => in_array($cx->status, ['compatible', 'overridden'], true))
                                                ->filter(fn ($cx) => $reservedUnitIds->contains($cx->blood_unit_id))
                                                ->count();
                                        @endphp
                                        <div class="alert alert-info py-2">
                                            {{ localize('global.crossmatch_reserved_compatible_summary') }}:
                                            <strong>{{ $reservedCompatible }}</strong> / <strong>{{ (int) $bloodBank->quantity }}</strong>
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
                                                            $autoAbo = $crossmatchService->isAboCompatible($bloodBank->group, $u->blood_group);
                                                            $autoRh = $crossmatchService->isRhCompatible($bloodBank->rh, $u->rh);
                                                            $isReserved = $reservedUnitIds->contains($u->id);
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $u->bag_number }}</td>
                                                            <td>{{ $u->blood_group }}</td>
                                                            <td>{{ $u->rh }}</td>
                                                            <td dir="ltr">{{ $u->expires_at?->format('Y-m-d H:i') }}</td>
                                                            <td>
                                                                @if ($autoAbo && $autoRh)
                                                                    <span class="badge bg-label-success">{{ localize('global.compatible') }}</span>
                                                                @else
                                                                    <span class="badge bg-label-danger">{{ localize('global.incompatible') }}</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if ($cx)
                                                                    <span class="badge bg-label-{{ in_array($cx->status, ['compatible', 'overridden'], true) ? 'success' : ($cx->status === 'incompatible' ? 'danger' : 'warning') }}">
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
                                                                                    <option value="{{ $val }}" @selected(($cx->major_result ?? 'pending') === $val)>{{ $val }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <select name="minor_result" class="form-select form-select-sm" required>
                                                                                @foreach (\App\Models\BloodCrossmatch::RESULT_VALUES as $val)
                                                                                    <option value="{{ $val }}" @selected(($cx->minor_result ?? 'pending') === $val)>{{ $val }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-8">
                                                                            <select name="patient_sample_id" class="form-select form-select-sm">
                                                                                <option value="">{{ localize('global.select_sample') }}</option>
                                                                                @foreach ($bloodBank->patientSamples as $sample)
                                                                                    <option value="{{ $sample->id }}" @selected(($cx->patient_sample_id ?? null) == $sample->id)>{{ $sample->sample_id ?: '#'.$sample->id }}</option>
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
                            </div>
                        @endif

                        @if ($bloodBank->status === 'approved')
                            <div class="col-md-12 mt-3">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">{{ localize('global.inventory_preview') }}</h6>
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
                                                                <span class="badge bg-label-{{ ($u->test?->overall_status ?? 'pending') === 'passed' ? 'success' : (($u->test?->overall_status ?? 'pending') === 'failed' ? 'danger' : 'warning') }}">
                                                                    {{ $u->test?->overall_status ?? 'pending' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if ($cx)
                                                                    <span class="badge bg-label-{{ in_array($cx->status, ['compatible', 'overridden'], true) ? 'success' : ($cx->status === 'incompatible' ? 'danger' : 'warning') }}">
                                                                        {{ $cx->status }}
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-label-secondary">{{ localize('global.not_tested') }}</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted py-3">{{ localize('global.no_item_is_found') }}</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
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
                                                <td dir="ltr">{{ $u->pivot->issued_at ? \Carbon\Carbon::parse($u->pivot->issued_at)->format('Y-m-d H:i') : '—' }}</td>
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
                                @if ($bloodBank->status === 'approved')
                                    <div class="col-md-8 text-center">
                                        @php
                                            $deliverableIds = $bloodBank->crossmatches
                                                ->filter(fn ($cx) => in_array($cx->status, ['compatible', 'overridden'], true))
                                                ->filter(fn ($cx) => $reservedUnitIds->contains($cx->blood_unit_id))
                                                ->pluck('blood_unit_id')
                                                ->values();
                                            $hasCrossmatchFlow = $bloodBank->crossmatches->isNotEmpty();
                                        @endphp
                                        <form action="{{ route('blood_banks.deliver', $bloodBank->id) }}" method="POST"
                                            class="border rounded p-3 bg-body-secondary">
                                            @csrf
                                            <p class="small text-muted mb-2">
                                                {{ localize('global.deliver_blood_fifo_hint') }}</p>
                                            @if ($hasCrossmatchFlow)
                                                <p class="small text-danger mb-2">
                                                    {{ localize('global.crossmatch_delivery_uses_reserved_hint') }}
                                                </p>
                                            @endif
                                            @if ($availableUnits->isNotEmpty())
                                                <div class="text-start mb-2" style="max-height: 200px; overflow-y: auto;">
                                                    @foreach ($availableUnits as $u)
                                                        @php
                                                            $checked = $hasCrossmatchFlow ? $deliverableIds->contains($u->id) : false;
                                                            $disabled = $hasCrossmatchFlow && ! $checked;
                                                        @endphp
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="unit_ids[]" value="{{ $u->id }}"
                                                                id="u{{ $u->id }}" @checked($checked)
                                                                @disabled($disabled)>
                                                            <label class="form-check-label" for="u{{ $u->id }}">
                                                                {{ $u->bag_number }} — {{ $u->component_type }}
                                                                ({{ localize('global.expires_at') }}:
                                                                {{ $u->expires_at?->format('Y-m-d') }})
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <button type="submit" class="btn btn-success mt-2">
                                                <i class="bx bxs-check-circle"></i> {{ localize('global.complete') }}
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="modal fade" id="createRejectModal{{ $bloodBank->id }}" tabindex="-1"
                            aria-labelledby="createRejectModalLabel{{ $bloodBank->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createRejectModalLabel{{ $bloodBank->id }}">
                                            {{ localize('global.reject_request') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('blood_banks.reject', $bloodBank->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" id="is_reserved{{ $bloodBank->is_reserved }}"
                                                name="is_reserved" value="1">

                                            <div class="form-group">

                                                <div class="form-group">
                                                    <label
                                                        for="reject_reason{{ $bloodBank->id }}">{{ localize('global.reject_reason') }}</label>
                                                    <textarea class="form-control" id="reject_reason{{ $bloodBank->id }}" name="reject_reason" rows="3"></textarea>
                                                </div>
                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
