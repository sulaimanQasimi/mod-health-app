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
                <h5 class="mb-0">{{ localize('global.depot.new_transaction') }}</h5>
                <a href="{{ route('depots.transactions.index') }}" class="btn btn-outline-secondary btn-sm">{{ localize('global.back') }}</a>
            </div>
            <div class="card-body">
                <form action="{{ route('depots.transactions.store') }}" method="POST" autocomplete="off" onsubmit="return confirm('{{ localize('global.depot.confirm_submit_transaction') }}')">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="depot_id">{{ localize('global.depot.depot') }}</label>
                            <select name="depot_id" id="depot_id" class="form-select select2 @error('depot_id') is-invalid @enderror" required>
                                <option value="">{{ localize('global.depot.select_depot') }}</option>
                                @foreach($depots as $depotOption)
                                    <option value="{{ $depotOption->id }}" @selected((string) $selectedDepotId === (string) $depotOption->id)>{{ $depotOption->name }}</option>
                                @endforeach
                            </select>
                            @error('depot_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="medicine_id">{{ localize('global.medicine') }}</label>
                            <select name="medicine_id" id="medicine_id" class="form-select select2 @error('medicine_id') is-invalid @enderror">
                                <option value="">{{ localize('global.select_medicine') }}</option>
                                @foreach($medicines as $medicine)
                                    <option value="{{ $medicine->id }}" @selected(old('medicine_id') == $medicine->id)>{{ $medicine->name }}</option>
                                @endforeach
                            </select>
                            @error('medicine_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="tool_id">{{ localize('global.depot.tool') }}</label>
                            <select name="tool_id" id="tool_id" class="form-select select2 @error('tool_id') is-invalid @enderror">
                                <option value="">{{ localize('global.select') }}</option>
                                @foreach($tools as $tool)
                                    <option value="{{ $tool->id }}" @selected(old('tool_id') == $tool->id)>{{ $tool->displayName() }}</option>
                                @endforeach
                            </select>
                            @error('tool_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="type">{{ localize('global.type') }}</label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                @foreach($typeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type', $defaults['type'] ?? null) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="quantity">{{ localize('global.quantity') }}</label>
                            <input type="number" min="1" name="quantity" id="quantity" value="{{ old('quantity') }}" class="form-control @error('quantity') is-invalid @enderror" required>
                            @error('quantity')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="unit_id">{{ localize('global.unit') }}</label>
                            <select name="unit_id" id="unit_id" class="form-select select2 @error('unit_id') is-invalid @enderror">
                                <option value="">{{ localize('global.select_unit') }}</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }} {{ $unit->symbol ? "({$unit->symbol})" : '' }}</option>
                                @endforeach
                            </select>
                            @error('unit_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="batch_number">{{ localize('global.batch_number') }}</label>
                            <input type="text" name="batch_number" id="batch_number" value="{{ old('batch_number') }}" class="form-control @error('batch_number') is-invalid @enderror">
                            @error('batch_number')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="transaction_date">{{ localize('global.depot.transaction_date') }}</label>
                            <input type="date" name="transaction_date" id="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" class="form-control @error('transaction_date') is-invalid @enderror">
                            @error('transaction_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="expiry_date">{{ localize('global.depot.expiry_date') }}</label>
                            <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}" class="form-control @error('expiry_date') is-invalid @enderror">
                            @error('expiry_date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-12">
                            <div id="available-stock" class="alert alert-info py-2 d-none"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="notes">{{ localize('global.notes') }}</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="reset" class="btn btn-outline-secondary">{{ localize('global.reset') }}</button>
                        <button type="submit" class="btn btn-primary">{{ localize('global.depot.save_transaction') }}</button>
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
    const tool = document.getElementById('tool_id');
    const box = document.getElementById('available-stock');

    function refreshStock() {
        if (!depot.value) {
            box.classList.add('d-none');
            return;
        }

        let url = `{{ route('depots.stock.available') }}?depot_id=${depot.value}`;
        if (medicine.value) {
            url += `&item_type=medicine&medicine_id=${medicine.value}`;
        } else if (tool.value) {
            url += `&item_type=tool&tool_id=${tool.value}`;
        } else {
            box.classList.add('d-none');
            return;
        }

        fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(response => response.ok ? response.json() : null)
            .then(data => {
                if (!data) {
                    box.classList.add('d-none');
                    return;
                }
                box.textContent = `{{ localize('global.available_stock') }}: ${Number(data.available_stock).toLocaleString()}`;
                box.classList.remove('d-none');
            })
            .catch(() => box.classList.add('d-none'));
    }

    function onChange(el, handler) {
        if (!el) return;
        if (window.jQuery) {
            $(el).on('change', handler);
        } else {
            el.addEventListener('change', handler);
        }
    }

    onChange(medicine, function () {
        if (this.value && tool) {
            tool.value = '';
            if (window.jQuery) $('#tool_id').trigger('change.select2');
        }
        refreshStock();
    });
    onChange(tool, function () {
        if (this.value && medicine) {
            medicine.value = '';
            if (window.jQuery) $('#medicine_id').trigger('change.select2');
        }
        refreshStock();
    });
    onChange(depot, refreshStock);
    refreshStock();
});
</script>
@endsection
