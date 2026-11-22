@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <x-depot.nav-tabs active="reports" :urls="$navUrls ?? []" :permissions="$navPermissions ?? null" />

    <div class="row g-3 mb-3">
        @foreach($summaryCards as $card)
            <div class="col-6 col-lg">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="badge bg-label-{{ $card['color'] }} p-2 rounded"><i class="bx {{ $card['icon'] }} fs-4"></i></span>
                        <div>
                            <div class="text-muted small">{{ $card['label'] }}</div>
                            <div class="fs-4 fw-bold">{{ number_format($card['value']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-3">
        @foreach($analyticsPanels as $panel)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header py-2"><h6 class="mb-0">{{ $panel['title'] }}</h6></div>
                    <ul class="list-group list-group-flush">
                        @forelse($panel['rows'] as $row)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $row['label'] }}</span>
                                <span class="badge bg-label-primary">{{ number_format($row['count']) }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">{{ localize('global.no_data_found') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">{{ localize('global.depot.reports') }}</h5></div>
        <div class="card-body">
            @foreach(['transactions' => localize('global.depot.report_transactions'), 'stock' => localize('global.depot.report_stock'), 'movements' => localize('global.depot.report_movements'), 'requests' => localize('global.depot.report_requests')] as $key => $label)
                <div class="border rounded p-3 mb-3">
                    <h6>{{ $label }}</h6>
                    <form method="GET" action="{{ route('depots.reports.export') }}" class="row g-2 align-items-end">
                        <input type="hidden" name="report" value="{{ $key }}">
                        @if(in_array($key, ['transactions', 'stock', 'movements']))
                        <div class="col-md-3">
                            <select name="depot_id" class="form-select select2">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($depots as $depot)
                                    <option value="{{ $depot->id }}" @selected((string) request('depot_id') === (string) $depot->id)>{{ $depot->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        @if(in_array($key, ['transactions', 'stock']))
                        <div class="col-md-2">
                            <select name="item_type" class="form-select">
                                <option value="">{{ localize('global.all') }}</option>
                                <option value="medicine" @selected(request('item_type') === 'medicine')>{{ localize('global.medicine') }}</option>
                                <option value="tool" @selected(request('item_type') === 'tool')>{{ localize('global.tool') }}</option>
                            </select>
                        </div>
                        @endif
                        @if($key === 'requests')
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($requestStatusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-2"><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control"></div>
                        <div class="col-md-2"><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control"></div>
                        <div class="col-md-2">
                            <button name="type" value="excel" class="btn btn-success w-100">{{ localize('global.export_excel') }}</button>
                        </div>
                        <div class="col-md-2">
                            <button name="type" value="pdf" class="btn btn-danger w-100">{{ localize('global.export_pdf') }}</button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
