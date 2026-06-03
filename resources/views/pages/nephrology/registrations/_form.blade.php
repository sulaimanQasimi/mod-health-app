@php
    $registration = $nephrologyRegistration;
    $nephrologyDiseasesJson = ($nephrologyDiseases ?? collect())->map(function ($d) {
        return [
            'id' => $d->id,
            'name' => $d->name,
            'disease_category_id' => $d->disease_category_id,
        ];
    })->values();
    $hasUncategorizedDiseases = ($nephrologyDiseases ?? collect())->contains(function ($d) {
        return empty($d->disease_category_id);
    });
@endphp

<h6 class="text-primary border-bottom pb-2 mb-3">
    <i class="bx bx-calendar me-1"></i> {{ localize('global.registration_information') }}
</h6>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="doctor_id" class="form-label">{{ localize('global.doctor') }}</label>
        <select class="form-select @error('doctor_id') is-invalid @enderror" id="doctor_id" name="doctor_id">
            <option value="">{{ localize('global.select_doctor') }}</option>
            @foreach($doctors as $doctor)
                <option value="{{ $doctor->id }}" {{ old('doctor_id', $registration->doctor_id) == $doctor->id ? 'selected' : '' }}>
                    {{ $doctor->name }}
                </option>
            @endforeach
        </select>
        @error('doctor_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="visit_date" class="form-label">{{ localize('global.visit_date') }} <span class="text-danger">*</span></label>
        <input type="text" autocomplete="off" class="form-control datepicker_dari pdp-el @error('visit_date') is-invalid @enderror" id="visit_date" name="visit_date"
            value="{{ old('visit_date', $registration->visit_date ? verta($registration->visit_date)->format('Y/m/d') : verta()->format('Y/m/d')) }}" required>
        @error('visit_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="status" class="form-label">{{ localize('global.status') }} <span class="text-danger">*</span></label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
            @foreach(['pending', 'in_progress', 'completed', 'cancelled'] as $status)
                <option value="{{ $status }}" {{ old('status', $registration->status) == $status ? 'selected' : '' }}>
                    {{ localize('global.' . $status) }}
                </option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<h6 class="text-primary border-bottom pb-2 mb-3 mt-2">
    <i class="bx bx-health me-1"></i> {{ localize('global.clinical_findings') }}
</h6>
<div class="row">
    <div class="col-md-12 mb-3">
        <label for="chief_complaint" class="form-label">{{ localize('global.chief_complaint') }}</label>
        <textarea class="form-control @error('chief_complaint') is-invalid @enderror" id="chief_complaint" name="chief_complaint" rows="2">{{ old('chief_complaint', $registration->chief_complaint) }}</textarea>
        @error('chief_complaint')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="disease_category_filter" class="form-label">{{ localize('global.disease_category') }}</label>
        <select class="form-select" id="disease_category_filter">
            <option value="">{{ localize('global.select_category') }}</option>
            @foreach($diseaseCategories ?? [] as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
            @if($hasUncategorizedDiseases)
                <option value="none">{{ localize('global.uncategorized') }}</option>
            @endif
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="disease_id" class="form-label">{{ localize('global.diseases') }}</label>
        <select class="form-select @error('disease_id') is-invalid @enderror" id="disease_id" name="disease_id" disabled>
            <option value="">{{ localize('global.select_category_first') }}</option>
        </select>
        @error('disease_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="ckd_aki_stage" class="form-label">{{ localize('global.ckd_aki_stage') }}</label>
        <input type="text" class="form-control @error('ckd_aki_stage') is-invalid @enderror" id="ckd_aki_stage" name="ckd_aki_stage"
            value="{{ old('ckd_aki_stage', $registration->ckd_aki_stage) }}" placeholder="e.g. CKD 3, AKI 2">
        @error('ckd_aki_stage')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<h6 class="text-primary border-bottom pb-2 mb-3 mt-2">
    <i class="bx bx-water me-1"></i> {{ localize('global.hemodialysis') }}
</h6>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label d-block">{{ localize('global.dialysis_required') }}</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="dialysis_required" id="dialysis_required_yes" value="1"
                {{ old('dialysis_required', $registration->dialysis_required) ? 'checked' : '' }}>
            <label class="form-check-label" for="dialysis_required_yes">{{ localize('global.yes') }}</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="dialysis_required" id="dialysis_required_no" value="0"
                {{ !old('dialysis_required', $registration->dialysis_required) ? 'checked' : '' }}>
            <label class="form-check-label" for="dialysis_required_no">{{ localize('global.no') }}</label>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="dialysis_type" class="form-label">{{ localize('global.dialysis_type') }}</label>
        <select class="form-select @error('dialysis_type') is-invalid @enderror" id="dialysis_type" name="dialysis_type">
            <option value="">{{ localize('global.select') }}</option>
            @foreach(['HD', 'PD', 'CRRT'] as $type)
                <option value="{{ $type }}" {{ old('dialysis_type', $registration->dialysis_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
            @endforeach
        </select>
        @error('dialysis_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="access_type" class="form-label">{{ localize('global.access_type') }}</label>
        <select class="form-select @error('access_type') is-invalid @enderror" id="access_type" name="access_type">
            <option value="">{{ localize('global.select') }}</option>
            <option value="av_fistula" {{ old('access_type', $registration->access_type) == 'av_fistula' ? 'selected' : '' }}>{{ localize('global.av_fistula') }}</option>
            <option value="graft" {{ old('access_type', $registration->access_type) == 'graft' ? 'selected' : '' }}>{{ localize('global.graft') }}</option>
            <option value="catheter" {{ old('access_type', $registration->access_type) == 'catheter' ? 'selected' : '' }}>{{ localize('global.catheter') }}</option>
        </select>
        @error('access_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<h6 class="text-primary border-bottom pb-2 mb-3 mt-2">
    <i class="bx bx-note me-1"></i> {{ localize('global.notes') }}
</h6>
<div class="row">
    <div class="col-md-12 mb-3">
        <label for="notes" class="form-label">{{ localize('global.notes') }}</label>
        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $registration->notes) }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-12 mb-0">
        <label for="follow_up_plan" class="form-label">{{ localize('global.follow_up_plan') }}</label>
        <textarea class="form-control @error('follow_up_plan') is-invalid @enderror" id="follow_up_plan" name="follow_up_plan" rows="3">{{ old('follow_up_plan', $registration->follow_up_plan) }}</textarea>
        @error('follow_up_plan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@push('scripts')
<script>
(function () {
    const allDiseases = @json($nephrologyDiseasesJson);
    const selectedDiseaseId = @json(old('disease_id', $registration->disease_id));
    const selectLabel = @json(localize('global.select'));
    const selectCategoryFirstLabel = @json(localize('global.select_category_first'));

    const categorySelect = document.getElementById('disease_category_filter');
    const diseaseSelect = document.getElementById('disease_id');
    if (!categorySelect || !diseaseSelect) return;

    function diseasesForCategory(categoryValue) {
        if (categoryValue === 'none') {
            return allDiseases.filter(d => !d.disease_category_id);
        }
        return allDiseases.filter(d => String(d.disease_category_id) === String(categoryValue));
    }

    function fillDiseaseOptions(categoryValue, preselectId = null) {
        const list = categoryValue ? diseasesForCategory(categoryValue) : [];
        diseaseSelect.innerHTML = `<option value="">${selectLabel}</option>`;

        list.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = d.name;
            if (preselectId && String(preselectId) === String(d.id)) {
                opt.selected = true;
            }
            diseaseSelect.appendChild(opt);
        });

        diseaseSelect.disabled = !categoryValue;
        if (!categoryValue) {
            diseaseSelect.innerHTML = `<option value="">${selectCategoryFirstLabel}</option>`;
        }
    }

    categorySelect.addEventListener('change', function () {
        fillDiseaseOptions(this.value || null);
    });

    if (selectedDiseaseId) {
        const current = allDiseases.find(d => String(d.id) === String(selectedDiseaseId));
        if (current) {
            const catValue = current.disease_category_id ? String(current.disease_category_id) : 'none';
            categorySelect.value = catValue;
            fillDiseaseOptions(catValue, selectedDiseaseId);
        }
    }
})();
</script>
@endpush
