<form id="patient-form-tab3" action="{{ isset($patient) ? route('patients.update', $patient) : route('patients.store') }}" method="POST">
    @csrf
    @if(isset($patient))
        @method('PUT')
    @endif
    <div class="row">
        <div class="col-md-3">
            <div class="mb-3">
                <label for="name">{{ localize('global.name') }}</label>
                <input type="text" name="name" required id="name" value="{{ old('name', $patient->name ?? '') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="last_name">{{ localize('global.last_name') }}</label>
                <input type="text" name="last_name" id="last_name"
                    value="{{ old('last_name', isset($patient) ? $patient->last_name : '') }}" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="father_name">{{ localize('global.father_name') }}</label>
                <input type="text" name="father_name" id="father_name"
                    value="{{ old('father_name', $patient->father_name ?? '') }}" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="nid">{{ localize('global.nid') }}</label>
                <input type="text" name="nid" required id="nid" value="{{ old('nid', $patient->nid ?? '') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="job">{{ localize('global.job2') }}</label>
                <input type="text" name="job" id="job" value="{{ old('job', $patient->job ?? '') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="phone">{{ localize('global.phone') }}</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $patient->phone ?? '') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="age_tab3">{{ localize('global.age') }}</label>
                <div class="row g-0">
                    <div class="col-4">
                        <input type="number" class="form-control" name="age_year" id="age_year_tab3" placeholder="{{ localize('global.year') }}" min="0" max="150" onchange="updateAgeValue('tab3')" value="{{ old('age_year', $ageYear ?? '') }}" style="padding: 0;">
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control" name="age_day" id="age_day_tab3" placeholder="{{ localize('global.day') }}" min="0" max="31" onchange="updateAgeValue('tab3')" value="{{ old('age_day', $ageDay ?? '') }}" style="padding: 0;">
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control" name="age_month" id="age_month_tab3" placeholder="{{ localize('global.month') }}" min="0" max="11" onchange="updateAgeValue('tab3')" value="{{ old('age_month', $ageMonth ?? '') }}" style="padding: 0;">
                    </div>
                </div>
                <input type="hidden" name="age" id="age_tab3" value="{{ old('age', $patient->age ?? '') }}" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="gender">{{ localize('global.gender') }}</label>
                <select class="form-control select2" name="gender" required id="gender">
                    <option value="">{{ localize('global.select') }}</option>
                    <option  {{ old('gender', $patient->gender ?? '') == '0' ? 'selected' : ''}} value="0">{{localize('global.male')}}</option>
                    <option  {{ old('gender', $patient->gender ?? '') == '1' ? 'selected' : ''}} value="1">{{localize('global.female')}}</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="province_id">{{ localize('global.province') }}</label>
                <select class="form-control select2" name="province_id" required onchange="getDistricts(this.value)"
                    id="province_id">
                    <option value="">{{ localize('global.select') }}</option>
                    @foreach ($provinces as $value)
                    <option  {{ old('province_id', $patient->province_id ?? '') == $value->id ? 'selected' : ''}} value="{{ $value->id }}" >
                        {{ $value->name_dr }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="district_id">{{ localize('global.district') }}</label>
                <select class="form-control select2" name="district_id" required
                    id="district_id">
                    <option value="">{{ localize('global.select') }}</option>
                    @foreach ($districts as $value)
                    <option  {{ old('district_id', $patient->district_id ?? '') == $value->id ? 'selected' : ''}} value="{{ $value->id }}" >
                        {{ $value->name_dr }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <input type="hidden" name="branch_id" value="{{ Auth::user()->branch_id }}">
    <input type="hidden" name="type" value="2">

    <!-- Appointment Section -->
    <div class="col-12 mt-4">
        <h5 class="mb-3 bg-label-info p-2">{{ localize('global.create_appointment') }}</h5>
        <div class="row">
            @if(auth()->user()->clinic_type === 'both')
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="appointment_clinic_type_tab3">{{ localize('global.clinic_type') }} <span class="text-danger">*</span></label>
                    <select class="form-control select2" name="appointment_clinic_type" id="appointment_clinic_type_tab3" onchange="loadDoctorsByDepartment(document.getElementById('appointment_department_id').value, this.form)">
                        <option value="">{{ localize('global.select') }}...</option>
                        <option value="hospital">{{ localize('global.hospital') }}</option>
                        <option value="clinic">{{ localize('global.clinic') }}</option>
                    </select>
                </div>
            </div>
            @endif
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="appointment_department_id">{{ localize('global.department') }} <span class="text-danger">*</span></label>
                    <select class="form-control select2" name="appointment_department_id" id="appointment_department_id" required onchange="loadDoctorsByDepartment(this.value, this.form)">
                        <option value="">{{ localize('global.select_department') }}</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ old('appointment_department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="appointment_doctor_id">{{ localize('global.doctor') }}</label>
                    <select class="form-control select2" name="appointment_doctor_id" id="appointment_doctor_id" disabled>
                        <option value="">{{ localize('global.select_doctor_first') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <h5 class="mb-2 bg-label-primary p-2">{{ localize('global.referred_person') }}</h5>

    <div class="row">
        <div class="col-md-3">
            <div class="mb-3">
                <label for="name">{{ localize('global.name') }}</label>
                <input type="text" name="referral_name" required id="referral_name"
                    value="{{ old('referral_name', $patient->referral_name ?? '') }}" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="last_name">{{ localize('global.last_name') }}</label>
                <input type="text" name="referral_last_name" id="referral_last_name"
                    value="{{ old('referral_last_name', $patient->referral_last_name ?? '') }}" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="father_name">{{ localize('global.father_name') }}</label>
                <input type="text" name="referral_father_name" id="referral_father_name"
                    value="{{ old('referral_father_name', $patient->referral_father_name ?? '') }}" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="nid">{{ localize('global.nid') }}</label>
                <input type="text" name="referral_nid" required id="referral_nid"
                    value="{{ old('referral_nid', $patient->referral_nid ?? '') }}" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="referral_id_card">{{ localize('global.id_card') }}</label>
                <input type="text" name="referral_id_card" id="referral_id_card"
                    value="{{ old('referral_id_card', $patient->referral_id_card ?? '') }}" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="referred_phone">{{ localize('global.phone')
                    }}</label>
                <input type="text" name="referral_phone" id="referral_phone"
                    value="{{ old('referral_phone', $patient->referral_phone ?? '') }}" class="form-control">
            </div>
        </div>

        <div class="col-md-3">
            <div class="mb-3">
                <label for="referred_by">{{ localize('global.referred_by') }}</label>
                <select class="form-control select2" name="referral_recipient" required >
                    <option value="">{{ localize('global.select') }}</option>
                    @foreach ($recipients as $value)
                    <option  {{ old('referral_recipient', $patient->referral_recipient ?? '') == $value->id ? 'selected' : ''}} value="{{ $value->id }}" >
                        {{ $value->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="relation_id">{{ localize('global.relation') }}</label>
                <select class="form-control select2" name="relation_id" required >
                    <option value="">{{ localize('global.select') }}</option>
                    @foreach ($relations as $value)
                    <option  {{ old('relation_id', $patient->relation_id ?? '') == $value->id ? 'selected' : ''}} value="{{ $value->id }}" >
                        {{ $value->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">{{ isset($patient) ? localize('global.update') : localize('global.create') }}</button>
    <a class="btn btn-danger" href="{{ url()->previous() }}" type="button">
        <span class="text-white"> <span class="d-none d-sm-inline-block  ">{{
                localize('global.back') }}</span></span>
    </a>
</form>
