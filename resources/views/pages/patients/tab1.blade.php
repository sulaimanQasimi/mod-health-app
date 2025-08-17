
<form action="{{ isset($patient) ? route('patients.update', $patient->id) : route('patients.store') }}" method="POST">
    @csrf
    @isset($patient)
        @method('PUT')
    @endisset
    <div class="row">
        <div class="col-md-3">
            <div class="mb-3">
                <label for="name">{{ localize('global.name') }}</label>
                <input type="text" name="name" id="name" required value="{{ old('name', isset($patient) ? $patient->name : '') }}" class="form-control">
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
                <label for="id_card">{{ localize('global.id_card') }}</label>
                <input type="text" name="id_card" id="id_card"
                    value="{{ old('id_card', isset($patient) ? $patient->id_card : '') }}" class="form-control">
            </div>
        </div>

        <div class="col-md-3">
            <div class="mb-3">
                <label for="job">{{ localize('global.job') }}</label>
                <input type="text" name="job" id="job" value="{{ old('job', isset($patient) ? $patient->job : '') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="job_category">{{ localize('global.job_category') }}</label>
                <select class="form-control select2" name="job_category" required
                    id="job_category" onchange="changeType(this.value)">

                    <option  {{ old('job_category',  (isset($patient) && $patient->job_category == '0') ? 'selected' : '')}} value="0">{{localize('global.military')}}</option>
                    <option  {{ old('job_category',  (isset($patient) && $patient->job_category == '1') ? 'selected' : '')}} value="1">{{localize('global.civilian')}}</option>
                </select>
            </div>
        </div>
        
        @php
            $militeryTypes = \App\Models\MiliteryType::all();
        @endphp
        <div class="col-md-3" id="militery_type_div" style="display: {{ old('job_category', isset($patient) ? $patient->job_category : '0') == '0' ? 'block' : 'none' }};">
            <div class="mb-3">
                <label for="militery_type_id">{{ localize('global.militery_type') }}</label>
                <select class="form-control select2" name="militery_type_id" 
                    id="militery_type_id">
                    <option value="">{{ localize('global.select') }}</option>
                    @foreach ($militeryTypes as $militeryType)
                    <option  {{ old('militery_type_id',  (isset($patient) && $patient->militery_type_id == $militeryType->id) ? 'selected' : '')}} value="{{ $militeryType->id }}" >
                        {{ $militeryType->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3" id="rank_div" style="display: {{ old('job_category', isset($patient) ? $patient->job_category : '0') == '1' ? 'block' : 'none' }};">
            <div class="mb-3">
                <label for="rank" id="rank_label">{{ localize('global.rank') }}</label>
                <input type="text" name="rank" id="rank" value="{{ old('rank', isset($patient) ? $patient->rank : '') }}"
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
                <input type="text" name="age" required id="age" value="{{ old('age', isset($patient) ? $patient->age : '') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="gender">{{ localize('global.gender') }}</label>
                <select class="form-control select2" name="gender" id="gender" required>
                    <option  {{ old('gender',  (isset($patient) && $patient->gender == '0') ? 'selected' : '')}} value="0">{{localize('global.male')}}</option>
                    <option  {{ old('gender',  (isset($patient) && $patient->gender == '1') ? 'selected' : '')}} value="1">{{localize('global.female')}}</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="referred_by">{{ localize('global.referred_by') }}</label>
                <select class="form-control select2" name="referred_by">
                    @foreach ($recipients as $value)
                    <option  {{ old('referred_by',  (isset($patient) && $patient->referred_by == $value->id) ? 'selected' : '')}} value="{{ $value->id }}" >
                        {{ $value->name }}</option>
                    @endforeach
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
                <label for="district_id">{{ localize('global.district') }} </label>
                <select class="form-control select2" required name="district_id"
                    id="district_id">
                    <option value="">{{ localize('global.select') }}</option>
                    @foreach ($districts as $value)
                    <option  {{ old('district_id',  (isset($patient) && $patient->district_id == $value->id) ? 'selected' : '')}} value="{{ $value->id }}" >
                        {{ $value->name_dr }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <div class="mb-3">
                <label for="registration_date">{{ localize('global.registration_date') }}</label>
                <input type="text" name="registration_date" id="registration_date" 
                    value="{{ old('registration_date', isset($patient) ? $patient->registration_date : \Carbon\Carbon::now()->format('Y-m-d')) }}" 
                    class="form-control" readonly>
            </div>
        </div>

        <input type="hidden" name="branch_id" value="{{ Auth::user()->branch_id }}">
        <input type="hidden" name="type" value="0">


    </div>
    <button type="submit" class="btn btn-primary">{{ isset($patient) ? localize('global.update') : localize('global.create') }}</button>

    <a class="btn btn-danger" href="{{ url()->previous() }}" type="button">
        <span class="text-white"> <span class="d-none d-sm-inline-block  ">{{
                localize('global.back') }}</span></span>
    </a>
</form>

<script>
function changeType(value) {
    const militeryTypeDiv = document.getElementById('militery_type_div');
    const rankDiv = document.getElementById('rank_div');
    const militeryTypeSelect = document.getElementById('militery_type_id');
    const rankInput = document.getElementById('rank');
    
    if (value === '0') { // Military
        militeryTypeDiv.style.display = 'block';
        rankDiv.style.display = 'none';
        militeryTypeSelect.required = true;
        rankInput.required = false;
        rankInput.value = ''; // Clear rank value when switching to military
    } else { // Civilian
        militeryTypeDiv.style.display = 'none';
        rankDiv.style.display = 'block';
        militeryTypeSelect.required = false;
        rankInput.required = true;
        militeryTypeSelect.value = ''; // Clear military type when switching to civilian
    }
}

// Initialize the form state on page load
document.addEventListener('DOMContentLoaded', function() {
    const jobCategorySelect = document.getElementById('job_category');
    if (jobCategorySelect) {
        changeType(jobCategorySelect.value);
    }
    
    // Convert registration date to Persian format
    const registrationDateInput = document.getElementById('registration_date');
    if (registrationDateInput) {
        const gregorianDate = registrationDateInput.value;
        if (gregorianDate) {
            const persianDate = convertToPersianDate(gregorianDate);
            registrationDateInput.value = persianDate;
        }
    }
});

// Function to convert Gregorian date to Persian date
function convertToPersianDate(gregorianDate) {
    const date = new Date(gregorianDate);
    const year = date.getFullYear();
    const month = date.getMonth() + 1;
    const day = date.getDate();
    
    // Persian calendar constants
    const persianEpoch = 1948320.5;
    const gregorianEpoch = 1721425.5;
    
    // Convert to Julian Day Number
    let jd = gregorianToJulianDay(year, month, day);
    
    // Convert to Persian date
    let persianDate = julianDayToPersian(jd);
    
    // Format as Persian date (YYYY/MM/DD)
    return `${persianDate.year}/${persianDate.month.toString().padStart(2, '0')}/${persianDate.day.toString().padStart(2, '0')}`;
}

// Convert Gregorian date to Julian Day Number
function gregorianToJulianDay(year, month, day) {
    let jd = gregorianEpoch - 1;
    
    jd += 365 * (year - 1);
    jd += Math.floor((year - 1) / 4);
    jd -= Math.floor((year - 1) / 100);
    jd += Math.floor((year - 1) / 400);
    jd += Math.floor((367 * month - 362) / 12);
    
    if (month > 2) {
        jd -= isLeapYear(year) ? 1 : 2;
    }
    
    jd += day;
    return jd;
}

// Convert Julian Day Number to Persian date
function julianDayToPersian(jd) {
    jd = Math.floor(jd) + 0.5;
    
    let depoch = jd - persianEpoch;
    let cycle = Math.floor(depoch / 1029983);
    let cyear = depoch % 1029983;
    
    if (cyear < 0) {
        cyear += 1029983;
    }
    
    let ycycle;
    if (cyear == 1029982) {
        ycycle = 2820;
    } else {
        let aux1 = Math.floor(cyear / 366);
        let aux2 = cyear % 366;
        ycycle = Math.floor(((2134 * aux1) + (2816 * aux2) + 2815) / 1028522) + aux1 + 1;
    }
    
    let year = ycycle + (2820 * cycle) + 474;
    if (year <= 0) {
        year--;
    }
    
    let yday = (jd - persianToJulianDay(year, 1, 1)) + 1;
    let month = (yday <= 186) ? Math.ceil(yday / 31) : Math.ceil((yday - 6) / 30);
    let day = (jd - persianToJulianDay(year, month, 1)) + 1;
    
    return { year: year, month: month, day: day };
}

// Convert Persian date to Julian Day Number
function persianToJulianDay(year, month, day) {
    let epbase = year - (year >= 0 ? 474 : 473);
    let epyear = 474 + (epbase % 2820);
    
    let mdays = (month <= 7) ? ((month - 1) * 31) : ((month - 1) * 30 + 6);
    
    return day + mdays + Math.floor(((epyear * 682) - 110) / 2816) + (epyear - 1) * 365 + Math.floor(epbase / 2820) * 1029983 + (persianEpoch - 1);
}

// Check if year is leap year
function isLeapYear(year) {
    return (year % 4 == 0 && year % 100 != 0) || (year % 400 == 0);
}
</script>