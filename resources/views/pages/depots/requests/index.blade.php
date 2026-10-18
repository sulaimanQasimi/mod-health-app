@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
    @endif
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ localize('global.depot.requests') }}</h5>
            @can('depot.request.create')
            <a href="{{ route('depots.requests.create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i>{{ localize('global.depot.new_request') }}</a>
            @endcan
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <select name="destination_type" class="form-select">
                        <option value="">{{ localize('global.depot.destination_type') ?? 'Destination' }}</option>
                        <option value="depot" @selected(request('destination_type') === 'depot')>{{ localize('global.depot.depot') ?? 'Depot' }}</option>
                        <option value="pharmacy" @selected(request('destination_type') === 'pharmacy')>{{ localize('global.pharmacy') ?? 'Pharmacy' }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="requesting_depot_id" class="form-select select2">
                        <option value="">{{ localize('global.depot.requesting_depot') }}</option>
                        @foreach($depots as $depot)
                            <option value="{{ $depot->id }}" @selected(request('requesting_depot_id') == $depot->id)>{{ $depot->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="pharmacy_id" class="form-select select2">
                        <option value="">{{ localize('global.pharmacy') }}</option>
                        @foreach($pharmacies as $pharmacy)
                            <option value="{{ $pharmacy->id }}" @selected(request('pharmacy_id') == $pharmacy->id)>{{ $pharmacy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="source_depot_id" class="form-select select2">
                        <option value="">{{ localize('global.depot.source_depot') }}</option>
                        @foreach($depots as $depot)
                            <option value="{{ $depot->id }}" @selected(request('source_depot_id') == $depot->id)>{{ $depot->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">{{ localize('global.status') }}</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ localize('global.search') }}"></div>
                <div class="col-md-2"><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control"></div>
                <div class="col-md-2"><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control"></div>
                <div class="col-md-2"><button class="btn btn-secondary w-100" type="submit">{{ localize('global.search') }}</button></div>
                <div class="col-md-2">
                    <a href="{{ route('depots.requests.index') }}" class="btn btn-outline-secondary w-100">{{ localize('global.reset') }}</a>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ localize('global.number') }}</th>
                        <th>{{ localize('global.status') }}</th>
                        <th>{{ localize('global.depot.destination_type') ?? 'Destination' }}</th>
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
                            <td>{{ $item->request_number }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($item->status) }}</span></td>
                            <td>{{ $item->isPharmacyRequest() ? (localize('global.pharmacy') ?? 'Pharmacy') : (localize('global.depot.depot') ?? 'Depot') }}</td>
                            <td>{{ $item->destinationLabel() }}</td>
                            <td>{{ $item->sourceDepot?->name }}</td>
                            <td>{{ $item->itemsSummary() }}</td>
                            <td>{{ $item->totalQuantity() }} <small class="text-muted">({{ $item->items_count }})</small></td>
                            <td>{{ $item->created_at ? verta($item->created_at)->format('Y-m-d') : '-' }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('depots.requests.show', $item) }}" class="btn btn-sm btn-primary"><i class="bx bx-show-alt"></i></a>
                                <a href="{{ route('depots.requests.print', $item) }}" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bx bx-printer"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">{{ localize('global.no_data_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $requests->links() }}</div>
    </div>
</div>
@endsection
