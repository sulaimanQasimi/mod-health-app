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
            @foreach($sourceOptions as $option)
                <option value="{{ $option['id'] }}" @selected(old('source_depot_id', $defaults['source_depot_id'] ?? '') == $option['id'])>{{ $option['name'] }}</option>
            @endforeach
        </select>
        @error('source_depot_id')<small class="text-danger">{{ $message }}</small>@enderror
    </div>

    <div class="col-12" id="depot-request-context">
        <x-depot.request-context :context="$initialContext" />
    </div>

    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label mb-0">{{ localize('global.depot.request_items') ?? 'Request Items' }}</label>
            <button type="button" class="btn btn-sm btn-outline-primary" id="add-request-item">
                <i class="bx bx-plus"></i> {{ localize('global.add') ?? 'Add' }}
            </button>
        </div>
        <div class="table-responsive depot-request-items-wrap">
            <table class="table table-bordered align-middle" id="request-items-table">
                <thead>
                    <tr>
                        <th style="min-width:260px">{{ localize('global.medicine') }}</th>
                        <th style="min-width:220px" class="tool-col">{{ localize('global.depot.tool') }}</th>
                        <th style="min-width:100px">{{ localize('global.quantity') }}</th>
                        <th style="min-width:110px" class="text-center">{{ localize('global.available_stock') }}</th>
                        <th style="min-width:140px">{{ localize('global.unit') }}</th>
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
                                        <option value="{{ $tool->id }}" @selected(($item['tool_id'] ?? '') == $tool->id)>{{ $tool->displayName() }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" min="1" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" class="form-control item-quantity" required>
                            </td>
                            <td class="text-center item-available"><span class="available-value text-muted">—</span></td>
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
                    <option value="{{ $tool->id }}">{{ $tool->displayName() }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" min="1" name="items[__INDEX__][quantity]" value="1" class="form-control item-quantity" required>
        </td>
        <td class="text-center item-available"><span class="available-value text-muted">—</span></td>
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
    const parentMap = @json($parentMap);
    const tbody = document.querySelector('#request-items-table tbody');
    const template = document.getElementById('request-item-template');
    let nextIndex = {{ $nextIndex }};
    const stockUrl = @json(route('depots.stock.available'));
    const contextDepots = @json($contextDepots);
    const contextPharmacies = @json($contextPharmacyNames);
    const contextUserName = @json($contextUserName);
    const contextRoot = document.getElementById('depot-request-context');
    const stockCache = {};

    // ---- Request context panel (port of resolveDepotRequestContext) ----
    function refreshContext() {
        if (!contextRoot) return;
        const isPharmacy = (destination?.value || 'depot') === 'pharmacy';
        let depot = null;
        let label = null;
        if (isPharmacy) {
            const pharmacyId = pharmacySelect?.value;
            depot = contextDepots.find(d => String(d.pharmacy_id || '') === String(pharmacyId || '')) || null;
            label = (pharmacyId && contextPharmacies[pharmacyId]) || depot?.name || null;
        } else {
            const depotId = requestingDepot?.value
                || document.querySelector('input[type="hidden"][name="requesting_depot_id"]')?.value;
            depot = contextDepots.find(d => String(d.id) === String(depotId || '')) || null;
            label = depot?.name || null;
        }
        const values = [depot?.branch_name, depot?.department_name, contextUserName, label];
        contextRoot.querySelectorAll('dd').forEach((dd, i) => {
            dd.textContent = values[i] || '—';
        });
    }

    // ---- Available stock per line (port of useAvailableStock) ----
    function setAvailable(row, value, loading) {
        const cell = row.querySelector('.available-value');
        if (!cell) return;
        row.dataset.available = value === null || value === undefined ? '' : String(value);
        if (loading) {
            cell.textContent = '…';
            cell.className = 'available-value text-muted';
            return;
        }
        if (value === null || value === undefined) {
            cell.textContent = '—';
            cell.className = 'available-value text-muted';
            return;
        }
        const qty = parseInt(row.querySelector('.item-quantity')?.value || '0', 10);
        cell.textContent = Number(value).toLocaleString();
        cell.className = 'available-value fw-semibold ' + (qty > value ? 'text-danger' : 'text-success');
    }

    async function loadAvailable(row) {
        const depotId = sourceDepot?.value;
        const medicineId = row.querySelector('.item-medicine')?.value;
        const toolId = row.querySelector('.item-tool')?.value;
        const kind = toolId ? 'tool' : 'medicine';
        const itemId = toolId || medicineId;

        if (!depotId || !itemId) {
            setAvailable(row, null);
            return;
        }

        const params = new URLSearchParams({ depot_id: depotId, item_type: kind });
        params.set(kind === 'tool' ? 'tool_id' : 'medicine_id', itemId);
        const key = params.toString();

        if (Object.prototype.hasOwnProperty.call(stockCache, key)) {
            setAvailable(row, stockCache[key]);
            return;
        }

        setAvailable(row, null, true);
        try {
            const response = await fetch(stockUrl + '?' + key, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!response.ok) {
                setAvailable(row, null);
                return;
            }
            const payload = await response.json();
            const available = payload.available_stock ?? null;
            stockCache[key] = available;
            // Ignore stale responses if the selection changed meanwhile.
            const currentItem = row.querySelector('.item-tool')?.value || row.querySelector('.item-medicine')?.value;
            if (sourceDepot?.value === depotId && currentItem === itemId) {
                setAvailable(row, available);
            }
        } catch (e) {
            setAvailable(row, null);
        }
    }

    function onChange(el, handler) {
        if (!el) return;
        if (window.jQuery) {
            $(el).on('change', handler);
        } else {
            el.addEventListener('change', handler);
        }
    }

    function bindAvailability(row) {
        const reload = () => loadAvailable(row);
        onChange(row.querySelector('.item-medicine'), reload);
        onChange(row.querySelector('.item-tool'), reload);
        row.querySelector('.item-quantity')?.addEventListener('input', function () {
            const raw = row.dataset.available;
            setAvailable(row, raw === '' || raw === undefined ? null : parseInt(raw, 10));
        });
        reload();
    }

    function reloadAllAvailable() {
        tbody.querySelectorAll('.request-item-row').forEach(loadAvailable);
    }

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

    function destroySelect2($select) {
        if (!$select.length || ! $select.hasClass('select2-hidden-accessible')) {
            return;
        }
        try {
            $select.select2('destroy');
        } catch (e) {
            $select.removeClass('select2-hidden-accessible').removeAttr('data-select2-id');
            $select.next('.select2-container').remove();
        }
    }

    function initSelect2(scope) {
        if (!window.jQuery || typeof $.fn.select2 === 'undefined') return;

        $(scope).find('select.select2, select.item-medicine, select.item-tool').each(function () {
            const $select = $(this);
            destroySelect2($select);

            if (!$select.parent().hasClass('position-relative')) {
                $select.wrap('<div class="position-relative"></div>');
            }

            $select.addClass('select2').select2({
                width: '100%',
                placeholder: $select.find('option[value=""]').text() || '--انتخاب--',
                allowClear: true,
                // Keep dropdown outside .table-responsive so options are fully visible.
                dropdownParent: $(document.body),
            });
        });
    }

    destination?.addEventListener('change', syncDestinationUi);
    onChange(destination, refreshContext);
    onChange(requestingDepot, refreshContext);
    onChange(pharmacySelect, refreshContext);
    onChange(sourceDepot, reloadAllAvailable);

    requestingDepot?.addEventListener('change', function () {
        const parentId = parentMap[this.value];
        if (parentId && sourceDepot) {
            sourceDepot.value = parentId;
            if (window.jQuery) $('#source_depot_id').val(parentId).trigger('change.select2');
            reloadAllAvailable();
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
        bindAvailability(row);
        syncDestinationUi();
    });

    tbody.querySelectorAll('.request-item-row').forEach(bindRow);
    tbody.querySelectorAll('.request-item-row').forEach(bindAvailability);
    // Re-init after global forms-selects.js so table selects use body as dropdown parent.
    if (window.jQuery) {
        $(function () {
            initSelect2(document.getElementById('request-items-table') || document);
            initSelect2(document.getElementById('requesting-depot-wrap') || document);
            initSelect2(document.getElementById('pharmacy-wrap') || document);
            const source = document.getElementById('source_depot_id');
            if (source) initSelect2(source.parentElement || document);
        });
    }
    syncDestinationUi();
    refreshContext();
});
</script>

<style>
    /* Keep selected Select2 text readable in request item rows */
    #request-items-table .select2-container {
        min-width: 100%;
    }
    #request-items-table .select2-selection__rendered {
        color: #566a7f !important;
        line-height: 2.25rem !important;
        padding-inline: .75rem !important;
    }
    #request-items-table .select2-selection--single {
        min-height: 2.5rem;
        display: flex;
        align-items: center;
    }
    .select2-container--open .select2-dropdown {
        z-index: 3000;
    }
</style>
