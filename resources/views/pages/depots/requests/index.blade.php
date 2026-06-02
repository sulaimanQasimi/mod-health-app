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
                    <select name="requesting_depot_id" class="form-select select2">
                        <option value="">Requesting depot</option>
                        @foreach($depots as $depot)
                            <option value="{{ $depot->id }}" @selected(request('requesting_depot_id') == $depot->id)>{{ $depot->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="source_depot_id" class="form-select select2">
                        <option value="">Source depot</option>
                        @foreach($depots as $depot)
                            <option value="{{ $depot->id }}" @selected(request('source_depot_id') == $depot->id)>{{ $depot->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control"></div>
                <div class="col-md-2"><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control"></div>
                <div class="col-md-2"><button class="btn btn-secondary w-100" type="submit">{{ localize('global.search') }}</button></div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Number</th>
                        <th>Status</th>
                        <th>Requesting</th>
                        <th>Source</th>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $item)
                        <tr>
                            <td>{{ $item->request_number }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($item->status) }}</span></td>
                            <td>{{ $item->requestingDepot?->name }}</td>
                            <td>{{ $item->sourceDepot?->name }}</td>
                            <td>{{ $item->itemName() }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->created_at?->format('Y-m-d') }}</td>
                            <td><a href="{{ route('depots.requests.show', $item) }}" class="btn btn-sm btn-primary"><i class="bx bx-show-alt"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">{{ localize('global.no_data_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $requests->links() }}</div>
    </div>
</div>
@endsection
