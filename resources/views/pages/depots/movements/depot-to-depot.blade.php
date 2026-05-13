@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
    @endif

    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ localize('global.depot.depot_to_depot_movement') }}</h5>
                <a href="{{ route('depots.transactions.index') }}" class="btn btn-outline-secondary btn-sm">{{ localize('global.depot.back') }}</a>
            </div>
            <div class="card-body">
                <form action="{{ route('depots.movements.depot_to_depot.store') }}" method="POST" autocomplete="off" onsubmit="return confirm('{{ localize('global.move_stock_between_depots') }}')">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="from_depot_id">{{ localize('global.depot.source_depot') }}</label>
                            <select name="from_depot_id" id="from_depot_id" class="form-select select2 @error('from_depot_id') is-invalid @enderror" required>
                                <option value="">{{ localize('global.depot.select_source_depot') }}</option>
                                @foreach($depots as $depot)
                                    <option value="{{ $depot->id }}" @selected(old('from_depot_id') == $depot->id)>{{ $depot->name }}</option>
                                @endforeach
                            </select>
                            @error('from_depot_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="to_depot_id">{{ localize('global.depot.destination_depot') }}</label>
                            <select name="to_depot_id" id="to_depot_id" class="form-select select2 @error('to_depot_id') is-invalid @enderror" required>
                                <option value="">{{ localize('global.depot.select_destination_depot') }}</option>
                                @foreach($depots as $depot)
                                    <option value="{{ $depot->id }}" @selected(old('to_depot_id') == $depot->id)>{{ $depot->name }}</option>
                                @endforeach
                            </select>
                            @error('to_depot_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                                <label class="form-label" for="medicine_id">{{ localize('global.medicine') }}</label>
                            <select name="medicine_id" id="medicine_id" class="form-select select2 @error('medicine_id') is-invalid @enderror" required>
                                <option value="">{{ localize('global.select_medicine') }}</option>
                                @foreach($medicines as $medicine)
                                    <option value="{{ $medicine->id }}" @selected(old('medicine_id') == $medicine->id)>{{ $medicine->name }}</option>
                                @endforeach
                            </select>
                            @error('medicine_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="quantity">{{ localize('global.quantity') }}</label>
                            <input type="number" min="1" name="quantity" id="quantity" value="{{ old('quantity') }}" class="form-control @error('quantity') is-invalid @enderror" required>
                            @error('quantity')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="unit_id">{{ localize('global.unit') }}</label>
                            <select name="unit_id" id="unit_id" class="form-select select2 @error('unit_id') is-invalid @enderror">
                                <option value="">{{ localize('global.select_unit') }}</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }} {{ $unit->symbol ? "({$unit->symbol})" : '' }}</option>
                                @endforeach
                            </select>
                            @error('unit_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-12">
                            <div id="available-stock" class="alert alert-info py-2 d-none"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="batch_number">{{ localize('global.batch_number') }}</label>
                            <input type="text" name="batch_number" id="batch_number" value="{{ old('batch_number') }}" class="form-control @error('batch_number') is-invalid @enderror">
                            @error('batch_number')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="transaction_date">{{ localize('global.transaction_date') }}</label>
                            <input type="date" name="transaction_date" id="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" class="form-control @error('transaction_date') is-invalid @enderror">
                            @error('transaction_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="expiry_date">{{ localize('global.expiry_date') }}</label>
                            <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}" class="form-control @error('expiry_date') is-invalid @enderror">
                            @error('expiry_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="notes">{{ localize('global.notes') }}</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="reset" class="btn btn-outline-secondary">{{ localize('global.reset') }}</button>
                        <button type="submit" class="btn btn-primary">{{ localize('global.move_stock') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const depot = document.getElementById('from_depot_id');
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
                box.textContent = `Available stock in source depot: ${data.available_stock}`;
                box.classList.remove('d-none');
            });
    }

    depot.addEventListener('change', refreshStock);
    medicine.addEventListener('change', refreshStock);
    refreshStock();
});
</script>
@endsection
