@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
    @endif

    <x-depot.nav-tabs active="transactions" :urls="$navUrls ?? []" :permissions="$navPermissions ?? null" />

    <div class="col-xl-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ localize('global.depot.transaction_details') }}</h5>
                <a href="{{ route('depots.transactions.index') }}" class="btn btn-outline-secondary btn-sm">{{ localize('global.back') }}</a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">{{ localize('global.number') }}</div>
                        <div class="fw-semibold">{{ $depotTransaction->transaction_number }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">{{ localize('global.status') }}</div>
                        <x-depot.status-badge :status="$depotTransaction->status" type="transaction" />
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">{{ localize('global.type') }}</div>
                        <div><x-depot.type-label :type="$depotTransaction->type" /></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">{{ localize('global.date') }}</div>
                        <div>{{ optional($depotTransaction->transaction_date)->format('Y-m-d') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">{{ localize('global.source') }}</div>
                        <div>{{ $depotTransaction->fromDepot?->name ?? $depotTransaction->depot?->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">{{ localize('global.destination') }}</div>
                        <div>{{ $depotTransaction->toDepot?->name ?? $depotTransaction->pharmacy?->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">{{ localize('global.medicine') }}</div>
                        <div>{{ $depotTransaction->medicine?->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">{{ localize('global.depot.tool') }}</div>
                        <div>{{ $depotTransaction->tool?->displayName() ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">{{ localize('global.quantity') }}</div>
                        <div>{{ number_format($depotTransaction->quantity) }} {{ $depotTransaction->unit?->symbol ?? $depotTransaction->unit?->name }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">{{ localize('global.batch_number') }}</div>
                        <div>{{ $depotTransaction->batch_number ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">{{ localize('global.depot.expiry_date') }}</div>
                        <div>{{ optional($depotTransaction->expiry_date)->format('Y-m-d') ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">{{ localize('global.created_by') }}</div>
                        <div>{{ $depotTransaction->createdBy?->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">{{ localize('global.created_at') }}</div>
                        <div>{{ optional($depotTransaction->created_at)->format('Y-m-d H:i') }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">{{ localize('global.notes') }}</div>
                        <div>{{ $depotTransaction->notes ?? '-' }}</div>
                    </div>
                </div>

                @if($depotTransaction->depotRequest)
                    <div class="mt-3">
                        <div class="text-muted small">{{ localize('global.depot.request') }}</div>
                        <a href="{{ route('depots.requests.show', $depotTransaction->depotRequest) }}">{{ $depotTransaction->depotRequest->request_number }}</a>
                    </div>
                @endif

                @if(($permissions['cancel'] ?? true) && $depotTransaction->status !== 'cancelled')
                    <form action="{{ route('depots.transactions.cancel', $depotTransaction) }}" method="POST" class="mt-4">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-danger" onclick="return confirm('{{ localize('global.depot.confirm_cancel_transaction') }}')" type="submit">
                            {{ localize('global.depot.cancel_transaction') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
