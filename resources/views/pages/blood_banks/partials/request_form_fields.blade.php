{{--
    Shared blood request fields.
    Design mirrored from React Appointments BloodBankSection + BloodFormSegmented
    (resources/js/Components/Appointments/Sections/BloodBankSection.tsx).
    Field names / payloads must stay compatible with blood_banks.store — do not rename inputs.
--}}
@php
    $fieldId = $fieldId ?? uniqid('blood_');
    $requireCore = $requireCore ?? false;
    // Same option lists as the React BLOOD_GROUPS / BLOOD_COMPONENT_TYPES constants.
    $bloodGroups = ['A', 'B', 'AB', 'O'];
    $bloodComponentTypes = ['RBC', 'PRBC', 'Fresh', 'Platelets', 'Plasma', 'Whole Blood'];
@endphp

{{-- Inline once: @push from @section content may miss head @stack('custom-css') --}}
@once
    <style>
        /* React BloodFormSegmented track (rose) → Blade */
        .blood-seg-track {
            display: grid;
            gap: 0.375rem;
            padding: 0.375rem;
            border-radius: 1rem;
            border: 1px solid rgba(251, 113, 133, 0.35);
            background: linear-gradient(to bottom, rgba(255, 241, 242, 0.95), rgba(255, 228, 230, 0.55));
            box-shadow: inset 0 1px 2px rgba(190, 18, 60, 0.06);
        }
        .blood-seg-track.cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .blood-seg-track.cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .blood-seg-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            padding: 0.75rem 0.5rem;
            border: 0;
            border-radius: 0.75rem;
            background: transparent;
            color: #4b5563;
            font-size: 0.8125rem;
            font-weight: 600;
            transition: all 0.15s ease;
        }
        .blood-seg-btn.row {
            flex-direction: row;
            gap: 0.375rem;
        }
        .blood-seg-btn:hover {
            background: rgba(255, 255, 255, 0.7);
            color: #9f1239;
        }
        .blood-seg-btn.is-active {
            background: linear-gradient(to bottom, #f43f5e, #e11d48);
            color: #fff;
            box-shadow: 0 4px 10px rgba(244, 63, 94, 0.3);
        }
        .blood-seg-btn i { font-size: 1.25rem; line-height: 1; }
        .blood-request-field-label .optional-hint {
            margin-inline-start: 0.25rem;
            font-size: 0.75rem;
            font-weight: 400;
            color: #9ca3af;
        }
        .blood-request-input {
            border-radius: 0.75rem;
        }
        .blood-tests-empty {
            border: 1px dashed #e5e7eb;
            border-radius: 0.75rem;
            padding: 1rem 0.75rem;
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }
        .blood-request-modal-body {
            max-height: min(72vh, 760px);
            overflow-y: auto;
        }
    </style>
@endonce

{{-- ABO group: segmented control (optional, matches React BloodFormSegmented) --}}
<div class="mb-3">
    <label class="form-label blood-request-field-label" for="group{{ $fieldId }}">
        {{ localize('global.blood_group') }}
        @unless ($requireCore)
            <span class="optional-hint">({{ localize('global.optional') }})</span>
        @endunless
    </label>
    <input type="hidden" name="group" id="group{{ $fieldId }}" value="" @if($requireCore) required @endif>
    <div class="blood-seg-track cols-4" role="group" aria-label="{{ localize('global.blood_group') }}" data-blood-seg-for="group{{ $fieldId }}">
        @foreach ($bloodGroups as $group)
            <button type="button"
                    class="blood-seg-btn"
                    data-blood-seg-value="{{ $group }}"
                    aria-pressed="false">
                <i class="bx bx-droplet"></i>
                <span>{{ $group }}</span>
            </button>
        @endforeach
    </div>
</div>

{{-- Rh: segmented Rh+ / Rh− (optional) --}}
<div class="mb-3">
    <label class="form-label blood-request-field-label" for="rh{{ $fieldId }}">
        {{ localize('global.blood_rh') }}
        @unless ($requireCore)
            <span class="optional-hint">({{ localize('global.optional') }})</span>
        @endunless
    </label>
    <input type="hidden" name="rh" id="rh{{ $fieldId }}" value="" @if($requireCore) required @endif>
    <div class="blood-seg-track cols-2" role="group" aria-label="{{ localize('global.blood_rh') }}" data-blood-seg-for="rh{{ $fieldId }}">
        <button type="button" class="blood-seg-btn row" data-blood-seg-value="+" aria-pressed="false">
            <i class="bx bx-plus-medical"></i>
            <span>Rh+</span>
        </button>
        <button type="button" class="blood-seg-btn row" data-blood-seg-value="-" aria-pressed="false">
            <i class="bx bx-minus"></i>
            <span>Rh−</span>
        </button>
    </div>
</div>

{{-- Component type — same name="type" as before --}}
<div class="mb-3">
    <label class="form-label blood-request-field-label" for="type{{ $fieldId }}">
        {{ localize('global.blood_type') }}
        @unless ($requireCore)
            <span class="optional-hint">({{ localize('global.optional') }})</span>
        @endunless
    </label>
    <select class="form-select blood-request-input" name="type" id="type{{ $fieldId }}" @if($requireCore) required @endif>
        <option value="">{{ localize('global.select') }}</option>
        @foreach ($bloodComponentTypes as $componentType)
            <option value="{{ $componentType }}">{{ $componentType }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label blood-request-field-label" for="quantity{{ $fieldId }}">
        {{ localize('global.quantity') }}
        @unless ($requireCore)
            <span class="optional-hint">({{ localize('global.optional') }})</span>
        @endunless
    </label>
    <input type="number" min="1" class="form-control blood-request-input" name="quantity" id="quantity{{ $fieldId }}">
</div>

{{-- Hb / Hct side-by-side like React sm:grid-cols-2 --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6">
        <label class="form-label blood-request-field-label" for="hemoglobin{{ $fieldId }}">
            {{ localize('global.hemoglobin') }}
            <span class="optional-hint">({{ localize('global.optional') }})</span>
        </label>
        <input type="number" step="0.1" min="0" max="999" class="form-control blood-request-input"
               name="hemoglobin" id="hemoglobin{{ $fieldId }}" placeholder="g/dL">
    </div>
    <div class="col-sm-6">
        <label class="form-label blood-request-field-label" for="hematocrit{{ $fieldId }}">
            {{ localize('global.hematocrit') }}
            <span class="optional-hint">({{ localize('global.optional') }})</span>
        </label>
        <input type="number" step="0.1" min="0" max="100" class="form-control blood-request-input"
               name="hematocrit" id="hematocrit{{ $fieldId }}" placeholder="%">
    </div>
</div>

<div class="mb-3">
    <label class="form-label blood-request-field-label" for="factor{{ $fieldId }}">
        {{ localize('global.clotting_factor') }}
        <span class="optional-hint">({{ localize('global.optional') }})</span>
    </label>
    <input type="text" maxlength="255" class="form-control blood-request-input" name="factor" id="factor{{ $fieldId }}">
</div>

{{-- Requested tests — empty state first (React parity); payload still tests[n][test_name] --}}
<div class="mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
        <label class="form-label blood-request-field-label mb-0">
            {{ localize('global.blood_bank_requested_tests') }}
            <span class="optional-hint">({{ localize('global.optional') }})</span>
        </label>
        <button type="button" class="btn btn-sm btn-outline-secondary"
                onclick="addBloodRequestTestRow('{{ $fieldId }}')">
            <i class="bx bx-plus me-1"></i>{{ localize('global.add_test') }}
        </button>
    </div>
    <div id="bloodRequestTestsEmpty{{ $fieldId }}" class="blood-tests-empty">
        {{ localize('global.blood_bank_tests_empty') }}
    </div>
    <div id="bloodRequestTests{{ $fieldId }}" class="d-flex flex-column gap-2"></div>
</div>

<script>
    (function () {
        // Segmented control ↔ hidden input (allowEmpty like React BloodFormSegmented).
        if (!window.__bloodSegBound) {
            window.__bloodSegBound = true;
            document.addEventListener('click', function (event) {
                const btn = event.target.closest('[data-blood-seg-value]');
                if (!btn) return;
                const track = btn.closest('[data-blood-seg-for]');
                if (!track) return;
                const input = document.getElementById(track.getAttribute('data-blood-seg-for'));
                if (!input) return;
                const value = btn.getAttribute('data-blood-seg-value') || '';
                const already = input.value === value;
                input.value = already ? '' : value;
                track.querySelectorAll('[data-blood-seg-value]').forEach(function (el) {
                    const on = !already && el === btn;
                    el.classList.toggle('is-active', on);
                    el.setAttribute('aria-pressed', on ? 'true' : 'false');
                });
            });
        }

        if (typeof window.addBloodRequestTestRow !== 'function') {
            window.addBloodRequestTestRow = function (fieldId) {
                const container = document.getElementById('bloodRequestTests' + fieldId);
                const empty = document.getElementById('bloodRequestTestsEmpty' + fieldId);
                if (!container) return;
                if (empty) empty.classList.add('d-none');
                const index = container.querySelectorAll('.blood-request-test-row').length;
                const row = document.createElement('div');
                row.className = 'input-group blood-request-test-row';
                row.innerHTML =
                    '<input type="text" name="tests[' + index + '][test_name]" class="form-control blood-request-input" placeholder="{{ localize('global.test_name') }}">' +
                    '<button type="button" class="btn btn-outline-danger" onclick="removeBloodRequestTestRow(this, \'' + fieldId + '\')"><i class="bx bx-trash"></i></button>';
                container.appendChild(row);
            };
        }

        if (typeof window.removeBloodRequestTestRow !== 'function') {
            window.removeBloodRequestTestRow = function (btn, fieldId) {
                const row = btn.closest('.blood-request-test-row');
                if (row) row.remove();
                const container = document.getElementById('bloodRequestTests' + fieldId);
                const empty = document.getElementById('bloodRequestTestsEmpty' + fieldId);
                if (container && empty && container.querySelectorAll('.blood-request-test-row').length === 0) {
                    empty.classList.remove('d-none');
                }
            };
        }
    })();
</script>
