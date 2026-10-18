@php
    $defaults = $defaults ?? [
        'destination_type' => 'depot',
        'requesting_depot_id' => '',
        'pharmacy_id' => '',
        'source_depot_id' => '',
    ];
    $destinationType = old('destination_type', $defaults['destination_type'] ?? 'depot');
    $isPharmacyDestination = $destinationType === 'pharmacy';
    $viewContext = $viewContext ?? 'depot';
    $lockRequestingDepot = $lockRequestingDepot ?? false;
    $formItems = $formItems ?? [['medicine_id' => '', 'tool_id' => '', 'unit_id' => '', 'quantity' => 1, 'batch_number' => '']];
    $pharmacyChoices = ($viewContext === 'pharmacy' && isset($userPharmacies) && count($userPharmacies))
        ? $userPharmacies
        : ($pharmacies ?? collect());
    $notesValue = old('notes', $depotRequest->notes ?? '');
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label" for="destination_type">{{ localize('global.depot.destination_type') ?? 'Destination Type' }}</label>
        <select name="destination_type" id="destination_type" class="form-select @error('destination_type') is-invalid @enderror" @if($viewContext === 'pharmacy') disabled @endif>
            <option value="depot" @selected($destinationType === 'depot')>{{ localize('global.depot.depot') ?? 'Depot' }}</option>
            <option value="pharmacy" @selected($destinationType === 'pharmacy')>{{ localize('global.pharmacy') ?? 'Pharmacy' }}</option>
        </select>
        @if($viewContext === 'pharmacy')
            <input type="hidden" name="destination_type" value="pharmacy">
        @endif
    </div>

    <div class="col-md-4" id="requesting-depot-wrap" @if($isPharmacyDestination) style="display:none" @endif>
        <label class="form-label" for="requesting_depot_id">{{ localize('global.depot.requesting_depot') }}</label>
        <select name="requesting_depot_id" id="requesting_depot_id" class="form-select select2 @error('requesting_depot_id') is-invalid @enderror" @if($lockRequestingDepot) disabled @endif>
            <option value="">{{ localize('global.select') ?? 'Select' }}</option>
            @foreach($depots as $depot)
                <option value="{{ $depot->id }}" @selected(old('requesting_depot_id', $defaults['requesting_depot_id'] ?? '') == $depot->id)>{{ $depot->name }}</option>
            @endforeach
        </select>
        @if($lockRequestingDepot)
            <input type="hidden" name="requesting_depot_id" value="{{ old('requesting_depot_id', $defaults['requesting_depot_id'] ?? '') }}">
        @endif
        @error('requesting_depot_id')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="col-md-4" id="pharmacy-wrap" @if(! $isPharmacyDestination) style="display:none" @endif>
        <label class="form-label" for="pharmacy_id">{{ localize('global.pharmacy') }}</label>
        <select name="pharmacy_id" id="pharmacy_id" class="form-select select2 @error('pharmacy_id') is-invalid @enderror">
            <option value="">{{ localize('global.select') ?? 'Select' }}</option>
            @foreach($pharmacyChoices as $pharmacy)
                <option value="{{ $pharmacy->id }}" @selected(old('pharmacy_id', $defaults['pharmacy_id'] ?? '') == $pharmacy->id)>{{ $pharmacy->name }}</option>
            @endforeach
        </select>
        @error('pharmacy_id')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="source_depot_id">{{ localize('global.depot.source_depot') }}</label>
        <select name="source_depot_id" id="source_depot_id" class="form-select select2 @error('source_depot_id') is-invalid @enderror">
            <option value="">{{ localize('global.select') ?? 'Select' }}</option>
            @php $sourceOptions = $sourceDepotOptions ?? []; @endphp
            @if(count($sourceOptions))
                @foreach($sourceOptions as $option)
                    <option value="{{ $option['id'] }}" @selected(old('source_depot_id', $defaults['source_depot_id'] ?? '') == $option['id'])>{{ $option['name'] }}</option>
                @endforeach
            @else
                @foreach($depots as $depot)
                    <option value="{{ $depot->id }}" @selected(old('source_depot_id', $defaults['source_depot_id'] ?? '') == $depot->id)>{{ $depot->name }}</option>
                @endforeach
            @endif
        </select>
        @error('source_depot_id')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label mb-0">{{ localize('global.depot.request_items') ?? 'Request Items' }}</label>
            <button type="button" class="btn btn-sm btn-outline-primary" id="add-request-item">
                <i class="bx bx-plus"></i> {{ localize('global.add') ?? 'Add' }}
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="request-items-table">
                <thead>
                    <tr>
                        <th style="min-width:180px">{{ localize('global.medicine') }}</th>
                        <th style="min-width:180px" class="tool-col">{{ localize('global.depot.tool') }}</th>
                        <th style="min-width:100px">{{ localize('global.quantity') }}</th>
                        <th style="min-width:120px">{{ localize('global.unit') }}</th>
                        <th style="min-width:120px">{{ localize('global.batch_number') }}</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($formItems as $index => $item)
                        <tr class="request-item-row">
                            <td>
                                <select name="items[{{ $index }}][medicine_id]" class="form-select select2 item-medicine">
                                    <option value="">{{ localize('global.select_medicine') }}</option>
                                    @foreach($medicines as $medicine)
                                        <option value="{{ $medicine->id }}" @selected(($item['medicine_id'] ?? '') == $medicine->id)>{{ $medicine->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="tool-col">
                                <select name="items[{{ $index }}][tool_id]" class="form-select select2 item-tool">
                                    <option value="">{{ localize('global.select') ?? 'Select' }}</option>
                                    @foreach($tools as $tool)
                                        <option value="{{ $tool->id }}" @selected(($item['tool_id'] ?? '') == $tool->id)>{{ method_exists($tool, 'displayName') ? $tool->displayName() : $tool->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" min="1" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" class="form-control" required>
                            </td>
                            <td>
                                <select name="items[{{ $index }}][unit_id]" class="form-select select2">
                                    <option value="">{{ localize('global.select_unit') }}</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" @selected(($item['unit_id'] ?? '') == $unit->id)>{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="items[{{ $index }}][batch_number]" value="{{ $item['batch_number'] ?? '' }}" class="form-control">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-request-item" title="Remove"><i class="bx bx-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @error('items')<small class="text-danger">{{ $message }}</small>@enderror
        @error('items.*')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="notes">{{ localize('global.notes') }}</label>
        <textarea name="notes" id="notes" class="form-control" rows="3">{{ $notesValue }}</textarea>
    </div>
</div>

<template id="request-item-template">
    <tr class="request-item-row">
        <td>
            <select name="items[__INDEX__][medicine_id]" class="form-select item-medicine">
                <option value="">{{ localize('global.select_medicine') }}</option>
                @foreach($medicines as $medicine)
                    <option value="{{ $medicine->id }}">{{ $medicine->name }}</option>
                @endforeach
            </select>
        </td>
        <td class="tool-col">
            <select name="items[__INDEX__][tool_id]" class="form-select item-tool">
                <option value="">{{ localize('global.select') ?? 'Select' }}</option>
                @foreach($tools as $tool)
                    <option value="{{ $tool->id }}">{{ method_exists($tool, 'displayName') ? $tool->displayName() : $tool->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" min="1" name="items[__INDEX__][quantity]" value="1" class="form-control" required>
        </td>
        <td>
            <select name="items[__INDEX__][unit_id]" class="form-select">
                <option value="">{{ localize('global.select_unit') }}</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" name="items[__INDEX__][batch_number]" value="" class="form-control">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-request-item" title="Remove"><i class="bx bx-trash"></i></button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const destination = document.getElementById('destination_type');
    const requestingWrap = document.getElementById('requesting-depot-wrap');
    const pharmacyWrap = document.getElementById('pharmacy-wrap');
    const requestingDepot = document.getElementById('requesting_depot_id');
    const sourceDepot = document.getElementById('source_depot_id');
    const pharmacySelect = document.getElementById('pharmacy_id');
    const parentMap = @json($depots->mapWithKeys(fn($d) => [$d->id => $d->parent_depot_id])->all());
    const tbody = document.querySelector('#request-items-table tbody');
    const template = document.getElementById('request-item-template');
    let nextIndex = {{ count($formItems) }};

    function syncDestinationUi() {
        const isPharmacy = (destination?.value || 'depot') === 'pharmacy';
        if (requestingWrap) requestingWrap.style.display = isPharmacy ? 'none' : '';
        if (pharmacyWrap) pharmacyWrap.style.display = isPharmacy ? '' : 'none';
        document.querySelectorAll('.tool-col').forEach(el => {
            el.style.display = isPharmacy ? 'none' : '';
        });
        if (isPharmacy) {
            if (requestingDepot) {
                requestingDepot.removeAttribute('required');
                requestingDepot.value = '';
                if (window.jQuery) $('#requesting_depot_id').val('').trigger('change.select2');
            }
            document.querySelectorAll('.item-tool').forEach(el => {
                el.value = '';
                if (window.jQuery) $(el).val('').trigger('change.select2');
            });
        } else if (pharmacySelect) {
            pharmacySelect.value = '';
            if (window.jQuery) $('#pharmacy_id').val('').trigger('change.select2');
        }
    }

    function bindRow(row) {
        const medicine = row.querySelector('.item-medicine');
        const tool = row.querySelector('.item-tool');
        medicine?.addEventListener('change', function () {
            if (this.value && tool) {
                tool.value = '';
                if (window.jQuery) $(tool).val('').trigger('change.select2');
            }
        });
        tool?.addEventListener('change', function () {
            if (this.value && medicine) {
                medicine.value = '';
                if (window.jQuery) $(medicine).val('').trigger('change.select2');
            }
        });
        row.querySelector('.remove-request-item')?.addEventListener('click', function () {
            if (tbody.querySelectorAll('.request-item-row').length <= 1) return;
            if (window.jQuery) {
                $(row).find('select').select2('destroy');
            }
            row.remove();
        });
    }

    function initSelect2(scope) {
        if (!window.jQuery) return;
        $(scope).find('select.select2, select.item-medicine, select.item-tool').each(function () {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).addClass('select2').select2({ width: '100%' });
            }
        });
    }

    destination?.addEventListener('change', syncDestinationUi);

    requestingDepot?.addEventListener('change', function () {
        const parentId = parentMap[this.value];
        if (parentId && sourceDepot) {
            sourceDepot.value = parentId;
            if (window.jQuery) $('#source_depot_id').val(parentId).trigger('change.select2');
        }
    });

    document.getElementById('add-request-item')?.addEventListener('click', function () {
        const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
        const wrapper = document.createElement('tbody');
        wrapper.innerHTML = html.trim();
        const row = wrapper.firstElementChild;
        tbody.appendChild(row);
        bindRow(row);
        initSelect2(row);
        syncDestinationUi();
    });

    tbody.querySelectorAll('.request-item-row').forEach(bindRow);
    syncDestinationUi();
});
</script>
