@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="mb-0">{{ localize('global.blood_branch_transfer') }} #{{ $branchTransfer->id }}</h4>
                <a href="{{ route('blood_banks.branch_transfers.index') }}" class="btn btn-outline-secondary btn-sm">
                    {{ localize('global.back') }}
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="small text-muted">{{ localize('global.requesting_branch') }}</div>
                            <div class="fw-semibold">{{ $branchTransfer->requestingBranch?->name ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">{{ localize('global.supplying_branch') }}</div>
                            <div class="fw-semibold">{{ $branchTransfer->supplyingBranch?->name ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">{{ localize('global.status') }}</div>
                            <div class="fw-semibold">{{ $branchTransfer->status }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">{{ localize('global.blood_group') }}</div>
                            <div>{{ $branchTransfer->blood_group }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">{{ localize('global.rh') }}</div>
                            <div>{{ $branchTransfer->rh }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">{{ localize('global.component_type') }}</div>
                            <div>{{ $branchTransfer->component_type }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">{{ localize('global.quantity') }}</div>
                            <div>{{ $branchTransfer->quantity }}</div>
                        </div>
                        @if ($branchTransfer->notes)
                            <div class="col-12">
                                <div class="small text-muted">{{ localize('global.notes') }}</div>
                                <div>{{ $branchTransfer->notes }}</div>
                            </div>
                        @endif
                        @if ($branchTransfer->reject_reason)
                            <div class="col-12">
                                <div class="small text-muted">{{ localize('global.reject_reason') }}</div>
                                <div>{{ $branchTransfer->reject_reason }}</div>
                            </div>
                        @endif
                        @if ($branchTransfer->fulfilled_at)
                            <div class="col-md-6">
                                <div class="small text-muted">{{ localize('global.fulfilled_at') }}</div>
                                <div dir="ltr">{{ $branchTransfer->fulfilled_at->format('Y-m-d H:i') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted">{{ localize('global.fulfilled_by') }}</div>
                                <div>{{ $branchTransfer->fulfilledByUser?->name ?? '—' }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @php
                $isSupplier = (int) auth()->user()->branch_id === (int) $branchTransfer->supplying_branch_id;
                $isRequester = (int) auth()->user()->branch_id === (int) $branchTransfer->requesting_branch_id;
            @endphp

            @if ($branchTransfer->status === 'pending' && $isRequester)
                <form action="{{ route('blood_banks.branch_transfers.cancel', $branchTransfer) }}" method="POST"
                    class="mb-3"
                    onsubmit="return confirm('{{ localize('global.are_you_sure') }}');">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning">{{ localize('global.cancel_request') }}</button>
                </form>
            @endif

            @if ($branchTransfer->status === 'pending' && $isSupplier)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ localize('global.fulfill_branch_transfer') }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted">{{ localize('global.deliver_blood_fifo_hint') }}</p>
                        <form action="{{ route('blood_banks.branch_transfers.fulfill', $branchTransfer) }}" method="POST">
                            @csrf
                            @if ($availableUnits->isNotEmpty())
                                <div class="mb-3" style="max-height: 220px; overflow-y: auto;">
                                    @foreach ($availableUnits as $u)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="unit_ids[]"
                                                value="{{ $u->id }}" id="tu{{ $u->id }}">
                                            <label class="form-check-label" for="tu{{ $u->id }}">
                                                {{ $u->bag_number }} — {{ localize('global.expires_at') }}:
                                                {{ $u->expires_at?->format('Y-m-d') }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-warning">{{ localize('global.insufficient_blood_stock') }}</p>
                            @endif
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-success" @if ($availableUnits->count() < $branchTransfer->quantity) disabled @endif>
                                    <i class="bx bx-check"></i> {{ localize('global.send_units_to_branch') }}
                                </button>
                            </div>
                        </form>

                        <hr>
                        <form action="{{ route('blood_banks.branch_transfers.reject', $branchTransfer) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-2">
                                <label class="form-label">{{ localize('global.reject_reason') }}</label>
                                <textarea name="reject_reason" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger">{{ localize('global.reject') }}</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
