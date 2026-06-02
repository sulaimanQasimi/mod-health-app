@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header"><h5 class="mb-0">{{ localize('global.depot.reports') }}</h5></div>
        <div class="card-body">
            @foreach(['transactions' => 'Transaction Report', 'stock' => 'Stock Report', 'movements' => 'Movement Report', 'requests' => 'Request Report'] as $key => $label)
                <div class="border rounded p-3 mb-3">
                    <h6>{{ $label }}</h6>
                    <form method="GET" action="{{ route('depots.reports.export') }}" class="row g-2 align-items-end">
                        <input type="hidden" name="report" value="{{ $key }}">
                        @if(in_array($key, ['transactions', 'stock', 'movements']))
                        <div class="col-md-3">
                            <select name="depot_id" class="form-select select2">
                                <option value="">All depots</option>
                                @foreach($depots as $depot)
                                    <option value="{{ $depot->id }}">{{ $depot->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        @if(in_array($key, ['transactions', 'stock']))
                        <div class="col-md-2">
                            <select name="item_type" class="form-select">
                                <option value="">All items</option>
                                <option value="medicine">Medicine</option>
                                <option value="tool">Tool</option>
                            </select>
                        </div>
                        @endif
                        @if($key === 'requests')
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All statuses</option>
                                @foreach($requestStatuses as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-2"><input type="date" name="date_from" class="form-control"></div>
                        <div class="col-md-2"><input type="date" name="date_to" class="form-control"></div>
                        <div class="col-md-2">
                            <button name="type" value="excel" class="btn btn-success w-100">Excel</button>
                        </div>
                        <div class="col-md-2">
                            <button name="type" value="pdf" class="btn btn-danger w-100">PDF</button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
