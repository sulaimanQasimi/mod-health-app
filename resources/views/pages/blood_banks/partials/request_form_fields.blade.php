

@once
    @push('scripts')
        <script>
            // Dynamic test rows only — group/rh use Bootstrap btn-check (no custom JS).
            window.addBloodRequestTestRow = window.addBloodRequestTestRow || function (fieldId) {
                const container = document.getElementById('bloodRequestTests' + fieldId);
                const empty = document.getElementById('bloodRequestTestsEmpty' + fieldId);
                if (!container) {
                    return;
                }
                if (empty) {
                    empty.classList.add('d-none');
                }
                const index = container.querySelectorAll('.blood-request-test-row').length;
                const row = document.createElement('div');
                row.className = 'input-group mb-2 blood-request-test-row';
                row.innerHTML =
                    '<input type="text" name="tests[' + index + '][test_name]" class="form-control rounded" placeholder="' + (container.dataset.placeholder || '') + '">' +
                    '<button type="button" class="btn btn-outline-danger" data-blood-test-remove="' + fieldId + '"><i class="bx bx-trash"></i></button>';
                container.appendChild(row);
            };

            document.addEventListener('click', function (event) {
                const removeBtn = event.target.closest('[data-blood-test-remove]');
                if (!removeBtn) {
                    return;
                }
                const fieldId = removeBtn.getAttribute('data-blood-test-remove');
                const row = removeBtn.closest('.blood-request-test-row');
                if (row) {
                    row.remove();
                }
                const container = document.getElementById('bloodRequestTests' + fieldId);
                const empty = document.getElementById('bloodRequestTestsEmpty' + fieldId);
                if (container && empty && container.querySelectorAll('.blood-request-test-row').length === 0) {
                    empty.classList.remove('d-none');
                }
            });
        </script>
    @endpush
@endonce

{{-- Blood group — Bootstrap btn-check segmented control --}}
<div class="mb-3">
    <label class="form-label">
        {{ localize('global.blood_group') }}
        @unless ($requireCore ?? false)
            <span class="text-muted small">({{ localize('global.optional') }})</span>
        @endunless
    </label>
    <div class="btn-group w-100" role="group" aria-label="{{ localize('global.blood_group') }}">
        @foreach (['A', 'B', 'AB', 'O'] as $group)
            <input type="radio"
                   class="btn-check"
                   name="group"
                   id="group{{ $fieldId }}_{{ $group }}"
                   value="{{ $group }}"
                   autocomplete="off"
                   @checked(old('group') === $group)
                   @if ($requireCore ?? false) required @endif>
            <label class="btn btn-outline-danger" for="group{{ $fieldId }}_{{ $group }}">
                <i class="bx bx-droplet me-1"></i>{{ $group }}
            </label>
        @endforeach
    </div>
</div>

{{-- Rh — Bootstrap btn-check --}}
<div class="mb-3">
    <label class="form-label">
        {{ localize('global.blood_rh') }}
        @unless ($requireCore ?? false)
            <span class="text-muted small">({{ localize('global.optional') }})</span>
        @endunless
    </label>
    <div class="btn-group w-100" role="group" aria-label="{{ localize('global.blood_rh') }}">
        <input type="radio" class="btn-check" name="rh" id="rh{{ $fieldId }}_pos" value="+" autocomplete="off"
               @checked(old('rh') === '+')
               @if ($requireCore ?? false) required @endif>
        <label class="btn btn-outline-danger" for="rh{{ $fieldId }}_pos">
            <i class="bx bx-plus-medical me-1"></i>Rh+
        </label>

        <input type="radio" class="btn-check" name="rh" id="rh{{ $fieldId }}_neg" value="-" autocomplete="off"
               @checked(old('rh') === '-')
               @if ($requireCore ?? false) required @endif>
        <label class="btn btn-outline-danger" for="rh{{ $fieldId }}_neg">
            <i class="bx bx-minus me-1"></i>Rh−
        </label>
    </div>
</div>

{{-- Component type --}}
<div class="mb-3">
    <label class="form-label" for="type{{ $fieldId }}">
        {{ localize('global.blood_type') }}
        @unless ($requireCore ?? false)
            <span class="text-muted small">({{ localize('global.optional') }})</span>
        @endunless
    </label>
    <select class="form-select rounded" name="type" id="type{{ $fieldId }}"
            @if ($requireCore ?? false) required @endif>
        <option value="">{{ localize('global.select') }}</option>
        @foreach (['RBC', 'PRBC', 'Fresh', 'Platelets', 'Plasma', 'Whole Blood'] as $componentType)
            <option value="{{ $componentType }}" @selected(old('type') === $componentType)>
                {{ $componentType }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label" for="quantity{{ $fieldId }}">
        {{ localize('global.quantity') }}
        @unless ($requireCore ?? false)
            <span class="text-muted small">({{ localize('global.optional') }})</span>
        @endunless
    </label>
    <input type="number" min="1" class="form-control rounded" name="quantity"
           id="quantity{{ $fieldId }}" value="{{ old('quantity') }}">
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6">
        <label class="form-label" for="hemoglobin{{ $fieldId }}">
            {{ localize('global.hemoglobin') }}
            <span class="text-muted small">({{ localize('global.optional') }})</span>
        </label>
        <input type="number" step="0.1" min="0" max="999" class="form-control rounded"
               name="hemoglobin" id="hemoglobin{{ $fieldId }}"
               value="{{ old('hemoglobin') }}" placeholder="g/dL">
    </div>
    <div class="col-sm-6">
        <label class="form-label" for="hematocrit{{ $fieldId }}">
            {{ localize('global.hematocrit') }}
            <span class="text-muted small">({{ localize('global.optional') }})</span>
        </label>
        <input type="number" step="0.1" min="0" max="100" class="form-control rounded"
               name="hematocrit" id="hematocrit{{ $fieldId }}"
               value="{{ old('hematocrit') }}" placeholder="%">
    </div>
</div>

<div class="mb-3">
    <label class="form-label" for="factor{{ $fieldId }}">
        {{ localize('global.clotting_factor') }}
        <span class="text-muted small">({{ localize('global.optional') }})</span>
    </label>
    <input type="text" maxlength="255" class="form-control rounded" name="factor"
           id="factor{{ $fieldId }}" value="{{ old('factor') }}">
</div>

{{-- Requested tests --}}
<div class="mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
        <label class="form-label mb-0">
            {{ localize('global.blood_bank_requested_tests') }}
            <span class="text-muted small">({{ localize('global.optional') }})</span>
        </label>
        <button type="button" class="btn btn-sm btn-outline-secondary"
                onclick="addBloodRequestTestRow(@json((string) $fieldId))">
            <i class="bx bx-plus me-1"></i>{{ localize('global.add_test') }}
        </button>
    </div>
    <div id="bloodRequestTestsEmpty{{ $fieldId }}" class="border border-dashed rounded p-3 text-muted small">
        {{ localize('global.blood_bank_tests_empty') }}
    </div>
    <div id="bloodRequestTests{{ $fieldId }}"
         class="d-flex flex-column"
         data-placeholder="{{ localize('global.test_name') }}"></div>
</div>
