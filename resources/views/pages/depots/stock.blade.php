@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $depot->name }} — Stock</h5>
            <a href="{{ route('depots.show', $depot) }}" class="btn btn-outline-secondary btn-sm">{{ localize('global.back') }}</a>
        </div>
        <div class="card-body border-bottom">
            <form method="GET" class="row g-3">
                <div class="col-md-4"><input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search item"></div>
                <div class="col-md-3">
                    <select name="item_type" class="form-select">
                        <option value="">All types</option>
                        <option value="medicine" @selected($itemType === 'medicine')>Medicine</option>
                        <option value="tool" @selected($itemType === 'tool')>Tool</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-primary" type="submit">{{ localize('global.search') }}</button></div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Type</th><th>Item</th><th>Available</th><th>Unit</th></tr></thead>
                <tbody>
                    @forelse($stockItems as $item)
                        <tr>
                            <td>{{ ucfirst($item['item_type']) }}</td>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ number_format($item['available']) }}</td>
                            <td>{{ $item['unit'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">{{ localize('global.no_data_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
