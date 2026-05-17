@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
    @endif

    <div class="col-xl-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">New Depot Transaction</h5>
                <a href="{{ route('depots.transactions.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
            </div>
            <div class="card-body">
                <form action="{{ route('depots.transactions.store') }}" method="POST" autocomplete="off" onsubmit="return confirm('Submit this depot transaction?')">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="depot_id">Depot</label>
                            <select name="depot_id" id="depot_id" class="form-select select2 @error('depot_id') is-invalid @enderror" required>
                                <option value="">Select depot</option>
                                @foreach($depots as $depot)
                                    <option value="{{ $depot->id }}" @selected(old('depot_id') == $depot->id)>{{ $depot->name }}</option>
                                @endforeach
                            </select>
                            @error('depot_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="medicine_id">Medicine</label>
                            <select name="medicine_id" id="medicine_id" class="form-select select2 @error('medicine_id') is-invalid @enderror" required>
                                <option value="">Select medicine</option>
                                @foreach($medicines as $medicine)
                                    <option value="{{ $medicine->id }}" @selected(old('medicine_id') == $medicine->id)>{{ $medicine->name }}</option>
                                @endforeach
                            </select>
                            @error('medicine_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="type">Type</label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                @foreach($types as $type)
                                    <option value="{{ $type }}" @selected(old('type') === $type)>{{ str_replace('_', ' ', $type) }}</option>
                                @endforeach
                            </select>
                            @error('type')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="quantity">Quantity</label>
                            <input type="number" min="1" name="quantity" id="quantity" value="{{ old('quantity') }}" class="form-control @error('quantity') is-invalid @enderror" required>
                            @error('quantity')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="unit_id">Unit</label>
                            <select name="unit_id" id="unit_id" class="form-select select2 @error('unit_id') is-invalid @enderror">
                                <option value="">Select unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }} {{ $unit->symbol ? "({$unit->symbol})" : '' }}</option>
                                @endforeach
                            </select>
                            @error('unit_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="tool_id">Tool</label>
                            <select name="tool_id" id="tool_id" class="form-select select2 @error('tool_id') is-invalid @enderror">
                                <option value="">Select tool</option>
                                @foreach($tools as $tool)
                                    <option value="{{ $tool->id }}" @selected(old('tool_id') == $tool->id)>Tool #{{ $tool->id }}</option>
                                @endforeach
                            </select>
                            @error('tool_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="batch_number">Batch Number</label>
                            <input type="text" name="batch_number" id="batch_number" value="{{ old('batch_number') }}" class="form-control @error('batch_number') is-invalid @enderror">
                            @error('batch_number')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="transaction_date">Transaction Date</label>
                            <input type="date" name="transaction_date" id="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" class="form-control @error('transaction_date') is-invalid @enderror">
                            @error('transaction_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="expiry_date">Expiry Date</label>
                            <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}" class="form-control @error('expiry_date') is-invalid @enderror">
                            @error('expiry_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-12">
                            <div id="available-stock" class="alert alert-info py-2 d-none"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="notes">Notes</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        <button type="submit" class="btn btn-primary">Save Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const depot = document.getElementById('depot_id');
    const medicine = document.getElementById('medicine_id');
    const box = document.getElementById('available-stock');

    function refreshStock() {
        if (!depot.value || !medicine.value) {
            box.classList.add('d-none');
            return;
        }

        fetch(`{{ route('depots.stock.available') }}?depot_id=${depot.value}&medicine_id=${medicine.value}`)
            .then(response => response.json())
            .then(data => {
                box.textContent = `Available stock: ${data.available_stock}`;
                box.classList.remove('d-none');
            });
    }

    depot.addEventListener('change', refreshStock);
    medicine.addEventListener('change', refreshStock);
    refreshStock();
});
</script>
@endsection
