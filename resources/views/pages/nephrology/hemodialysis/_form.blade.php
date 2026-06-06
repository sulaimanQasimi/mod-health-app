@php
    $session = $hemodialysisSession ?? null;
    $isEdit = $session && $session->exists;
@endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="patient_id" class="form-label">{{ localize('global.patient') }} <span class="text-danger">*</span></label>
        @if($isEdit || !empty($selectedPatient))
            <input type="hidden" name="patient_id" value="{{ old('patient_id', $session->patient_id ?? $selectedPatient->id) }}">
            <input type="text" class="form-control" readonly
                value="{{ ($session->patient ?? $selectedPatient)->name }} {{ ($session->patient ?? $selectedPatient)->last_name }}
                ({{ ($session->patient ?? $selectedPatient)->patient_id ?? $session->patient_id ?? $selectedPatient->id }})">
        @else
            <input type="number" min="1" class="form-control @error('patient_id') is-invalid @enderror"
                id="patient_id" name="patient_id" value="{{ old('patient_id') }}" required>
            @error('patient_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">{{ localize('global.enter_patient_database_id') }}</small>
        @endif
    </div>
    <div class="col-md-6 mb-3">
        <label for="nephrology_registration_id" class="form-label">{{ localize('global.nephrology_registration') }}</label>
        @if(!empty($selectedRegistration))
            <input type="hidden" name="nephrology_registration_id" value="{{ $selectedRegistration->id }}">
            <input type="text" class="form-control" readonly value="{{ localize('global.ref_no') }}: {{ $selectedRegistration->ref_no }}">
        @else
            <input type="number" class="form-control @error('nephrology_registration_id') is-invalid @enderror"
                id="nephrology_registration_id" name="nephrology_registration_id"
                value="{{ old('nephrology_registration_id', $session->nephrology_registration_id ?? '') }}"
                placeholder="{{ localize('global.optional') }}">
            @error('nephrology_registration_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="doctor_id" class="form-label">{{ localize('global.attending_nephrologist') }}</label>
        <select class="form-select @error('doctor_id') is-invalid @enderror" id="doctor_id" name="doctor_id">
            <option value="">{{ localize('global.select_doctor') }}</option>
            @foreach($doctors as $doctor)
                <option value="{{ $doctor->id }}" {{ old('doctor_id', $session->doctor_id ?? '') == $doctor->id ? 'selected' : '' }}>
                    {{ $doctor->name }}
                </option>
            @endforeach
        </select>
        @error('doctor_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="diagnosis" class="form-label">{{ localize('global.diagnosis') }}</label>
        <textarea class="form-control @error('diagnosis') is-invalid @enderror" id="diagnosis" name="diagnosis" rows="2">{{ old('diagnosis', $session->diagnosis ?? ($selectedRegistration?->displayDiagnosis() ?? '')) }}</textarea>
        @error('diagnosis')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="dialysis_schedule" class="form-label">{{ localize('global.dialysis_schedule') }}</label>
        <input type="text" class="form-control @error('dialysis_schedule') is-invalid @enderror" id="dialysis_schedule" name="dialysis_schedule"
            value="{{ old('dialysis_schedule', $session->dialysis_schedule ?? '') }}" placeholder="e.g. Mon, Wed, Fri">
        @error('dialysis_schedule')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="session_date" class="form-label">{{ localize('global.session_date') }} <span class="text-danger">*</span></label>
        <input type="text" autocomplete="off" class="form-control datepicker_dari pdp-el @error('session_date') is-invalid @enderror" id="session_date" name="session_date"
            value="{{ old('session_date', isset($session) && $session->session_date ? verta($session->session_date)->format('Y/m/d') : verta()->format('Y/m/d')) }}" required>
        @error('session_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="session_time" class="form-label">{{ localize('global.session_time') }}</label>
        <input type="time" class="form-control @error('session_time') is-invalid @enderror" id="session_time" name="session_time"
            value="{{ old('session_time', isset($session) && $session->session_time ? \Carbon\Carbon::parse($session->session_time)->format('H:i') : '') }}">
        @error('session_time')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-3">
        <label for="duration_minutes" class="form-label">{{ localize('global.duration_minutes') }}</label>
        <input type="number" min="1" max="720" class="form-control @error('duration_minutes') is-invalid @enderror"
            id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $session->duration_minutes ?? '') }}">
        @error('duration_minutes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="vascular_access_type" class="form-label">{{ localize('global.vascular_access_type') }}</label>
        <select class="form-select @error('vascular_access_type') is-invalid @enderror" id="vascular_access_type" name="vascular_access_type">
            <option value="">{{ localize('global.select') }}</option>
            <option value="av_fistula" {{ old('vascular_access_type', $session->vascular_access_type ?? '') == 'av_fistula' ? 'selected' : '' }}>{{ localize('global.av_fistula') }}</option>
            <option value="graft" {{ old('vascular_access_type', $session->vascular_access_type ?? '') == 'graft' ? 'selected' : '' }}>{{ localize('global.graft') }}</option>
            <option value="catheter" {{ old('vascular_access_type', $session->vascular_access_type ?? '') == 'catheter' ? 'selected' : '' }}>{{ localize('global.catheter') }}</option>
        </select>
        @error('vascular_access_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="dialyzer_type" class="form-label">{{ localize('global.dialyzer_type') }}</label>
        <input type="text" class="form-control @error('dialyzer_type') is-invalid @enderror" id="dialyzer_type" name="dialyzer_type"
            value="{{ old('dialyzer_type', $session->dialyzer_type ?? '') }}">
        @error('dialyzer_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="blood_type" class="form-label">{{ localize('global.blood_type') }}</label>
        <select class="form-select @error('blood_type') is-invalid @enderror" id="blood_type" name="blood_type">
            <option value="">{{ localize('global.select') }}</option>
            @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bloodTypeOption)
                <option value="{{ $bloodTypeOption }}" {{ old('blood_type', $session->blood_type ?? '') === $bloodTypeOption ? 'selected' : '' }}>
                    {{ $bloodTypeOption }}
                </option>
            @endforeach
        </select>
        @error('blood_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-12 mb-2">
        <h6 class="text-muted">{{ localize('global.pre_dialysis_vitals') }}</h6>
    </div>
    <div class="col-md-3 mb-3">
        <label for="pre_blood_pressure" class="form-label">{{ localize('global.blood_pressure') }}</label>
        <input type="text" class="form-control @error('pre_blood_pressure') is-invalid @enderror" id="pre_blood_pressure" name="pre_blood_pressure"
            value="{{ old('pre_blood_pressure', $session->pre_blood_pressure ?? '') }}" placeholder="120/80">
        @error('pre_blood_pressure')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="pre_weight" class="form-label">{{ localize('global.weight_kg') }}</label>
        <input type="number" step="0.01" min="0" class="form-control @error('pre_weight') is-invalid @enderror" id="pre_weight" name="pre_weight"
            value="{{ old('pre_weight', $session->pre_weight ?? '') }}">
        @error('pre_weight')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="pre_pulse" class="form-label">{{ localize('global.pulse') }}</label>
        <input type="number" min="0" max="300" class="form-control @error('pre_pulse') is-invalid @enderror" id="pre_pulse" name="pre_pulse"
            value="{{ old('pre_pulse', $session->pre_pulse ?? '') }}">
        @error('pre_pulse')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="pre_temperature" class="form-label">{{ localize('global.temperature') }}</label>
        <input type="number" step="0.1" min="30" max="45" class="form-control @error('pre_temperature') is-invalid @enderror"
            id="pre_temperature" name="pre_temperature" value="{{ old('pre_temperature', $session->pre_temperature ?? '') }}">
        @error('pre_temperature')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-12 mb-2">
        <h6 class="text-muted">{{ localize('global.post_dialysis_vitals') }}</h6>
    </div>
    <div class="col-md-3 mb-3">
        <label for="post_blood_pressure" class="form-label">{{ localize('global.blood_pressure') }}</label>
        <input type="text" class="form-control @error('post_blood_pressure') is-invalid @enderror" id="post_blood_pressure" name="post_blood_pressure"
            value="{{ old('post_blood_pressure', $session->post_blood_pressure ?? '') }}" placeholder="120/80">
        @error('post_blood_pressure')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="post_weight" class="form-label">{{ localize('global.weight_kg') }}</label>
        <input type="number" step="0.01" min="0" class="form-control @error('post_weight') is-invalid @enderror" id="post_weight" name="post_weight"
            value="{{ old('post_weight', $session->post_weight ?? '') }}">
        @error('post_weight')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="post_pulse" class="form-label">{{ localize('global.pulse') }}</label>
        <input type="number" min="0" max="300" class="form-control @error('post_pulse') is-invalid @enderror" id="post_pulse" name="post_pulse"
            value="{{ old('post_pulse', $session->post_pulse ?? '') }}">
        @error('post_pulse')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="post_temperature" class="form-label">{{ localize('global.temperature') }}</label>
        <input type="number" step="0.1" min="30" max="45" class="form-control @error('post_temperature') is-invalid @enderror"
            id="post_temperature" name="post_temperature" value="{{ old('post_temperature', $session->post_temperature ?? '') }}">
        @error('post_temperature')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="fluid_removed_ml" class="form-label">{{ localize('global.fluid_removed_ml') }}</label>
        <input type="number" step="0.01" min="0" class="form-control @error('fluid_removed_ml') is-invalid @enderror"
            id="fluid_removed_ml" name="fluid_removed_ml" value="{{ old('fluid_removed_ml', $session->fluid_removed_ml ?? '') }}">
        @error('fluid_removed_ml')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <label for="complications_notes" class="form-label">{{ localize('global.complications_notes') }}</label>
        <textarea class="form-control @error('complications_notes') is-invalid @enderror" id="complications_notes" name="complications_notes" rows="3">{{ old('complications_notes', $session->complications_notes ?? '') }}</textarea>
        @error('complications_notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="status" class="form-label">{{ localize('global.status') }} <span class="text-danger">*</span></label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
            @foreach(['pending', 'in_progress', 'completed', 'cancelled'] as $statusOption)
                <option value="{{ $statusOption }}" {{ old('status', $session->status ?? 'pending') == $statusOption ? 'selected' : '' }}>
                    {{ localize('global.' . $statusOption) }}
                </option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
