<form action="{{ isset($patient) ? route('patients.update', $patient->id) : route('patients.store') }}" method="POST">
    @csrf
    @isset($patient)
        @method('PUT')
    @endisset
    <div class="row">
        <div class="col-md-3">
            <div class="mb-3">
                <label for="name">{{ localize('global.name') }}</label>
                <input type="text" name="name" required id="name" value="{{ old('name', isset($patient) ? $patient->name : '') }}"
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
                    value="{{ old('father_name', isset($patient) ? $patient->father_name : '') }}" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="nid">{{ localize('global.nid') }}</label>
                <input type="text" name="nid" required id="nid" value="{{ old('nid', isset($patient) ? $patient->nid : '') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="job">{{ localize('global.job2') }}</label>
                <input type="text" name="job" id="job" value="{{ old('job', isset($patient) ? $patient->job : '') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="phone">{{ localize('global.phone') }}</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', isset($patient) ? $patient->phone : '') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="age">{{ localize('global.age') }}</label>
                <input type="text" name="age" id="age" required value="{{ old('age', isset($patient) ? $patient->age : '') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="gender">{{ localize('global.gender') }}</label>
                <select class="form-control select2" name="gender" required id="gender">
                    <option value="">{{ localize('global.select') }}</option>
                    <option  {{ old('gender',  (isset($patient) && $patient->gender == '0') ? 'selected' : '')}} value="0">{{localize('global.male')}}</option>
                    <option  {{ old('gender',  (isset($patient) && $patient->gender == '1') ? 'selected' : '')}} value="1">{{localize('global.female')}}</option>
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
                    <option  {{ old('province_id',  (isset($patient) && $patient->province_id == $value->id) ? 'selected' : '')}} value="{{ $value->id }}" >
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
                    <option  {{ old('district_id',  (isset($patient) && $patient->district_id == $value->id) ? 'selected' : '')}} value="{{ $value->id }}" >
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
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="appointment_doctor_id">{{ localize('global.doctor') }}</label>
                    <select class="form-control select2" name="appointment_doctor_id" id="appointment_doctor_id">
                        <option value="">{{ localize('global.select_doctor') }}</option>
                        @foreach ($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ old('appointment_doctor_id') == $doctor->id ? 'selected' : '' }}>
                                {{ $doctor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="appointment_date">{{ localize('global.appointment_date') }}</label>
                    <input type="text" name="appointment_date" id="appointment_date" 
                           value="{{ old('appointment_date', verta()->format('Y-m-d')) }}" 
                           class="form-control" placeholder="YYYY-MM-DD">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="appointment_time">{{ localize('global.appointment_time') }}</label>
                    <input type="time" name="appointment_time" id="appointment_time" 
                           value="{{ old('appointment_time') }}" 
                           class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="appointment_status_remark">{{ localize('global.status_remark') }}</label>
                    <textarea name="appointment_status_remark" id="appointment_status_remark" 
                              class="form-control" rows="2" 
                              placeholder="{{ localize('global.optional_remarks') }}">{{ old('appointment_status_remark') }}</textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="appointment_refferal_remarks">{{ localize('global.referral_remarks') }}</label>
                    <textarea name="appointment_refferal_remarks" id="appointment_refferal_remarks" 
                              class="form-control" rows="2" 
                              placeholder="{{ localize('global.optional_referral_remarks') }}">{{ old('appointment_refferal_remarks') }}</textarea>
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
                    value="{{ old('referral_name', isset($patient) ? $patient->referral_name : '') }}" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="last_name">{{ localize('global.last_name') }}</label>
                <input type="text" name="referral_last_name" id="referral_last_name"
                    value="{{ old('referral_last_name', isset($patient) ? $patient->referral_last_name : '') }}" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="father_name">{{ localize('global.father_name') }}</label>
                <input type="text" name="referral_father_name" id="referral_father_name"
                    value="{{ old('referral_father_name', isset($patient) ? $patient->referral_father_name : '') }}" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="nid">{{ localize('global.nid') }}</label>
                <input type="text" name="referral_nid" required id="referral_nid"
                    value="{{ old('referral_nid', isset($patient) ? $patient->referral_nid : '') }}" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="referral_id_card">{{ localize('global.id_card') }}</label>
                <input type="text" name="referral_id_card" id="referral_id_card"
                    value="{{ old('referral_id_card', isset($patient) ? $patient->referral_id_card : '') }}" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="referred_phone">{{ localize('global.phone')
                    }}</label>
                <input type="text" name="referral_phone" id="referral_phone"
                    value="{{ old('referral_phone', isset($patient) ? $patient->referral_phone : '') }}" class="form-control">
            </div>
        </div>

        <div class="col-md-3">
            <div class="mb-3">
                <label for="referred_by">{{ localize('global.referred_by') }}</label>
                <select class="form-control select2" name="referral_recipient" required >
                    <option value="">{{ localize('global.select') }}</option>
                    @foreach ($recipients as $value)
                    <option  {{ old('referral_recipient',  (isset($patient) && $patient->referral_recipient == $value->id) ? 'selected' : '')}} value="{{ $value->id }}" >
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
                    <option  {{ old('relation_id',  (isset($patient) && $patient->relation_id == $value->id) ? 'selected' : '')}} value="{{ $value->id }}" >
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
