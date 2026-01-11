<form id="patient-form-tab2" action="{{ isset($patient) ? route('patients.update', $patient) : route('patients.store') }}" method="POST">
    @csrf
    @if(isset($patient))
        @method('PUT')
    @endif
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
                    value="{{ old('last_name', $patient->last_name ?? '') }}" class="form-control">
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
                <input type="text" required name="nid" id="nid" value="{{ old('nid', $patient->nid ?? '') }}"
                    class="form-control">
            </div>
        </div>

        <div class="col-md-3">
            <div class="mb-3">
                <label for="job">{{ localize('global.job') }}</label>
                <input type="text" name="job" id="job" value="{{ old('job', $patient->job ?? '') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="job_category">{{ localize('global.job_category') }}</label>
                <select class="form-control select2" name="job_category" required
                    id="job_category" onchange="changeType(this.value)">
                    <option  {{ old('job_category', $patient->job_category ?? '') == '0' ? 'selected' : ''}} value="0">{{localize('global.military')}}</option>
                    <option  {{ old('job_category', $patient->job_category ?? '') == '1' ? 'selected' : ''}} value="1">{{localize('global.civilian')}}</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="rank" id="rank_label">------</label>
                <input type="text" name="rank" id="rank" value="{{ old('rank', $patient->rank ?? '') }}"
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
                <label for="age_tab2">{{ localize('global.age') }}</label>
                <div class="row g-0">
                    <div class="col-4">
                        <input type="number" class="form-control" name="age_year" id="age_year_tab2" placeholder="{{ localize('global.year') }}" min="0" max="150" onchange="updateAgeValue('tab2')" value="{{ old('age_year', $ageYear ?? '') }}" style="padding: 0; margin: 0; direction: rtl; text-align: right;">
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control" name="age_month" id="age_month_tab2" placeholder="{{ localize('global.month') }}" min="0" max="11" onchange="updateAgeValue('tab2')" value="{{ old('age_month', $ageMonth ?? '') }}" style="padding: 0; margin: 0; direction: rtl; text-align: right;">
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control" name="age_day" id="age_day_tab2" placeholder="{{ localize('global.day') }}" min="0" max="31" onchange="updateAgeValue('tab2')" value="{{ old('age_day', $ageDay ?? '') }}" style="padding: 0; margin: 0; direction: rtl; text-align: right;">
                    </div>
                </div>
                <input type="hidden" name="age" id="age_tab2" value="{{ old('age', $patient->age ?? '') }}" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="gender">{{ localize('global.gender') }}</label>
                <select class="form-control select2" name="gender" required id="gender">
                    <option  {{ old('gender', $patient->gender ?? '') == '0' ? 'selected' : ''}} value="0">{{localize('global.male')}}</option>
                    <option  {{ old('gender', $patient->gender ?? '') == '1' ? 'selected' : ''}} value="1">{{localize('global.female')}}</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="referred_by">{{ localize('global.referred_by') }}</label>
                <select class="form-control select2" name="referred_by" required>
                    <option value="">{{ localize('global.select') }}</option>
                    @foreach ($recipients as $value)
                    <option  {{ old('referred_by', $patient->referred_by ?? '') == $value->id ? 'selected' : ''}} value="{{ $value->id }}" >
                        {{ $value->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="province_id">{{ localize('global.province') }}</label>
                <select class="form-control select2" name="province_id"  required onchange="getDistricts(this.value)"
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
                <label for="district_id">{{ localize('global.district') }} </label>
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

        <input type="hidden" name="branch_id" value="{{ Auth::user()->branch_id }}">
        <input type="hidden" name="type" value="1">

        <!-- Appointment Section -->
        <div class="col-12 mt-4">
            <h5 class="mb-3 bg-label-info p-2">{{ localize('global.create_appointment') }}</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="appointment_department_id">{{ localize('global.department') }} <span class="text-danger">*</span></label>
                        <select class="form-control select2" name="appointment_department_id" id="appointment_department_id" required onchange="loadDoctorsByDepartment(this.value)">
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

    </div>
    <button type="submit" class="btn btn-primary">{{ isset($patient) ? localize('global.update') : localize('global.create') }}</button>

    <a class="btn btn-danger" href="{{ url()->previous() }}" type="button">
        <span class="text-white"> <span class="d-none d-sm-inline-block  ">{{
                localize('global.back') }}</span></span>
    </a>
</form>
