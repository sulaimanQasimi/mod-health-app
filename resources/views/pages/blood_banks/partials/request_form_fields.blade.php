{{-- Shared blood request fields (SPA clinical + requested tests parity) --}}
@php
    $fieldId = $fieldId ?? uniqid('blood_');
    $requireCore = $requireCore ?? false;
@endphp
<div class="mb-3">
    <label class="form-label" for="group{{ $fieldId }}">{{ localize('global.blood_group') }}</label>
    <select class="form-select" name="group" id="group{{ $fieldId }}" @if($requireCore) required @endif>
        <option value="">{{ localize('global.select') }}</option>
        <option value="A">A</option>
        <option value="B">B</option>
        <option value="AB">AB</option>
        <option value="O">O</option>
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="rh{{ $fieldId }}">{{ localize('global.blood_rh') }}</label>
    <select class="form-select" name="rh" id="rh{{ $fieldId }}" @if($requireCore) required @endif>
        <option value="">{{ localize('global.select') }}</option>
        <option value="+">+</option>
        <option value="-">-</option>
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="type{{ $fieldId }}">{{ localize('global.blood_type') }}</label>
    <select class="form-select" name="type" id="type{{ $fieldId }}" @if($requireCore) required @endif>
        <option value="">{{ localize('global.select') }}</option>
        <option value="RBC">RBC</option>
        <option value="PRBC">PRBC</option>
        <option value="Fresh">Fresh</option>
        <option value="Platelets">Platelets</option>
        <option value="Plasma">Plasma</option>
        <option value="Whole Blood">Whole Blood</option>
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="quantity{{ $fieldId }}">{{ localize('global.quantity') }}</label>
    <input type="number" min="1" class="form-control" name="quantity" id="quantity{{ $fieldId }}">
</div>
<div class="mb-3">
    <label class="form-label" for="hemoglobin{{ $fieldId }}">{{ localize('global.hemoglobin') }}</label>
    <input type="number" step="0.01" min="0" max="999" class="form-control" name="hemoglobin" id="hemoglobin{{ $fieldId }}">
</div>
<div class="mb-3">
    <label class="form-label" for="hematocrit{{ $fieldId }}">{{ localize('global.hematocrit') }}</label>
    <input type="number" step="0.01" min="0" max="100" class="form-control" name="hematocrit" id="hematocrit{{ $fieldId }}">
</div>
<div class="mb-3">
    <label class="form-label" for="factor{{ $fieldId }}">{{ localize('global.clotting_factor') }}</label>
    <input type="text" maxlength="255" class="form-control" name="factor" id="factor{{ $fieldId }}">
</div>
<div class="mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <label class="form-label mb-0">{{ localize('global.blood_bank_requested_tests') }}</label>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addBloodRequestTestRow('{{ $fieldId }}')">
            <i class="bx bx-plus"></i>
        </button>
    </div>
    <div id="bloodRequestTests{{ $fieldId }}">
        <div class="input-group mb-2 blood-request-test-row">
            <input type="text" name="tests[0][test_name]" class="form-control" placeholder="{{ localize('global.test_name') }}">
            <button type="button" class="btn btn-outline-danger" onclick="this.closest('.blood-request-test-row').remove()">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    </div>
</div>
<script>
    if (typeof window.addBloodRequestTestRow !== 'function') {
        window.addBloodRequestTestRow = function (fieldId) {
            const container = document.getElementById('bloodRequestTests' + fieldId);
            if (!container) return;
            const index = container.querySelectorAll('.blood-request-test-row').length;
            const row = document.createElement('div');
            row.className = 'input-group mb-2 blood-request-test-row';
            row.innerHTML =
                '<input type="text" name="tests[' + index + '][test_name]" class="form-control" placeholder="{{ localize('global.test_name') }}">' +
                '<button type="button" class="btn btn-outline-danger" onclick="this.closest(\'.blood-request-test-row\').remove()"><i class="bx bx-trash"></i></button>';
            container.appendChild(row);
        };
    }
</script>
