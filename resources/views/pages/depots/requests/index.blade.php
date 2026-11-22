@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
    @endif
    @if(($viewContext ?? 'depot') !== 'pharmacy')
        <x-depot.nav-tabs active="requests" :urls="$navUrls ?? []" :permissions="$navPermissions ?? null" />
    @endif

    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1">{{ localize('global.depot.requests') }}</h5>
                <small class="text-muted">{{ localize('global.depot.request_items') }}</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($permissions['create'] ?? true)
                    <a href="{{ route('depots.requests.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i>{{ localize('global.depot.new_request') }}
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end mb-3">
                <div class="col-md-3">
                    <select name="destination_type" class="form-select">
                        <option value="">{{ localize('global.depot.destination_type') }}</option>
                        <option value="depot" @selected(request('destination_type') === 'depot')>{{ localize('global.depot.depot') }}</option>
                        <option value="pharmacy" @selected(request('destination_type') === 'pharmacy')>{{ localize('global.pharmacy') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="requesting_depot_id" class="form-select select2">
                        <option value="">{{ localize('global.depot.requesting_depot') }}</option>
                        @foreach($depots as $depot)
                            <option value="{{ $depot->id }}" @selected(request('requesting_depot_id') == $depot->id)>{{ $depot->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="pharmacy_id" class="form-select select2">
                        <option value="">{{ localize('global.pharmacy') }}</option>
                        @foreach($pharmacies as $pharmacy)
                            <option value="{{ $pharmacy->id }}" @selected(request('pharmacy_id') == $pharmacy->id)>{{ $pharmacy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="source_depot_id" class="form-select select2">
                        <option value="">{{ localize('global.depot.source_depot') }}</option>
                        @foreach($depots as $depot)
                            <option value="{{ $depot->id }}" @selected(request('source_depot_id') == $depot->id)>{{ $depot->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="medicine_id" class="form-select select2">
                        <option value="">{{ localize('global.medicine') }}</option>
                        @foreach($medicines as $medicine)
                            <option value="{{ $medicine->id }}" @selected(request('medicine_id') == $medicine->id)>{{ $medicine->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="tool_id" class="form-select select2">
                        <option value="">{{ localize('global.depot.tool') }}</option>
                        @foreach($tools as $tool)
                            <option value="{{ $tool->id }}" @selected(request('tool_id') == $tool->id)>{{ $tool->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">{{ localize('global.status') }}</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ localize('global.search') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="bx bx-search me-1"></i>{{ localize('global.search') }}
                    </button>
                    <a href="{{ route('depots.requests.index') }}" class="btn btn-outline-secondary">{{ localize('global.reset') }}</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ localize('global.number') }}</th>
                            <th>{{ localize('global.status') }}</th>
                            <th>{{ localize('global.depot.destination_type') }}</th>
                            <th>{{ localize('global.depot.requesting_depot') }} / {{ localize('global.pharmacy') }}</th>
                            <th>{{ localize('global.depot.source_depot') }}</th>
                            <th>{{ localize('global.item') }}</th>
                            <th>{{ localize('global.quantity') }}</th>
                            <th>{{ localize('global.date') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $item)
                            <tr>
                                <td><strong>{{ $item->request_number }}</strong></td>
                                <td><x-depot.status-badge :status="$item->status" /></td>
                                <td>{{ $item->isPharmacyRequest() ? (localize('global.pharmacy')) : (localize('global.depot.depot')) }}</td>
                                <td>{{ $item->destinationLabel() }}</td>
                                <td>{{ $item->sourceDepot?->name }}</td>
                                <td>{{ $item->itemsSummary() }}</td>
                                <td>{{ $item->totalQuantity() }} <small class="text-muted">({{ $item->items_count }})</small></td>
                                <td>{{ $item->created_at ? verta($item->created_at)->format('Y-m-d') : '-' }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('depots.requests.show', $item) }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-show-alt"></i></a>
                                    <a href="{{ route('depots.requests.print', $item) }}" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bx bx-printer"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-muted text-center">{{ localize('global.no_data_found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $requests->links() }}</div>
        </div>
    </div>
</div>
@endsection
