@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="col-xl-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Depot Transaction Details</h5>
                <a href="{{ route('depots.transactions.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Transaction Number</div>
                        <div class="fw-semibold">{{ $depotTransaction->transaction_number }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Status</div>
                        <span class="badge bg-{{ $depotTransaction->status === 'completed' ? 'success' : ($depotTransaction->status === 'cancelled' ? 'danger' : 'warning') }}">
                            {{ ucfirst($depotTransaction->status) }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Type</div>
                        <div>{{ str_replace('_', ' ', $depotTransaction->type) }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Date</div>
                        <div>{{ optional($depotTransaction->transaction_date)->format('Y-m-d') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Source</div>
                        <div>{{ $depotTransaction->fromDepot?->name ?? $depotTransaction->depot?->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Destination</div>
                        <div>{{ $depotTransaction->toDepot?->name ?? $depotTransaction->pharmacy?->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Medicine</div>
                        <div>{{ $depotTransaction->medicine?->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Tool</div>
                        <div>{{ $depotTransaction->tool ? 'Tool #' . $depotTransaction->tool->id : '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Quantity</div>
                        <div>{{ number_format($depotTransaction->quantity) }} {{ $depotTransaction->unit?->symbol ?? $depotTransaction->unit?->name }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Batch</div>
                        <div>{{ $depotTransaction->batch_number ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Expiry Date</div>
                        <div>{{ optional($depotTransaction->expiry_date)->format('Y-m-d') ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Created By</div>
                        <div>{{ $depotTransaction->createdBy?->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Created At</div>
                        <div>{{ optional($depotTransaction->created_at)->format('Y-m-d H:i') }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Notes</div>
                        <div>{{ $depotTransaction->notes ?? '-' }}</div>
                    </div>
                </div>

                @if($depotTransaction->status !== 'cancelled')
                    <form action="{{ route('depots.transactions.cancel', $depotTransaction) }}" method="POST" class="mt-4">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-danger" onclick="return confirm('Cancel this depot transaction?')" type="submit">
                            Cancel Transaction
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
