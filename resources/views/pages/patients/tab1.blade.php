<form action="{{ route('patients.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-3">
            <div class="mb-3">
                <label for="name">{{ localize('global.name') }}</label>
                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="last_name">{{ localize('global.last_name') }}</label>
                <input type="text" name="last_name" id="last_name"
                    value="{{ old('last_name') }}" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="father_name">{{ localize('global.father_name') }}</label>
                <input type="text" name="father_name" id="father_name"
                    value="{{ old('father_name') }}" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="nid">{{ localize('global.nid') }}</label>
                <input type="text" name="nid" required id="nid" value="{{ old('nid') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="id_card">{{ localize('global.id_card') }}</label>
                <input type="text" name="id_card" id="id_card"
                    value="{{ old('id_card') }}" class="form-control">
            </div>
        </div>

        <div class="col-md-3">
            <div class="mb-3">
                <label for="job">{{ localize('global.job') }}</label>
                <input type="text" name="job" id="job" value="{{ old('job') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="job_category">{{ localize('global.job_category') }}</label>
                <select class="form-control select2" name="job_category" required
                    id="job_category" onchange="changeType(this.value)">
                    <option value="" selected disabled>{{ localize('global.select') }}
                    </option>
                    <option value="0">{{localize('global.military')}}</option>
                    <option value="1">{{localize('global.civilian')}}</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="rank" id="rank_label">------</label>
                <input type="text" name="rank" id="rank" value="{{ old('rank') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="phone">{{ localize('global.phone') }}</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="age">{{ localize('global.age') }}</label>
                <input type="text" name="age" required id="age" value="{{ old('age') }}"
                    class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="gender">{{ localize('global.gender') }}</label>
                <select class="form-control select2" name="gender" id="gender" required>
                    <option value="">{{ localize('global.select') }}</option>
                    <option value="0">{{localize('global.male')}}</option>
                    <option value="1">{{localize('global.female')}}</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="referred_by">{{ localize('global.referred_by') }}</label>
                <select class="form-control select2" name="referred_by">
                    <option value="">{{ localize('global.select') }}</option>
                    @foreach ($recipients as $value)
                    <option value="{{ $value->id }}" {{ old('name')==$value->id ?
                        'selected' : ''
                        }}>
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
                    <option value="{{ $value->id }}" {{ old('name')==$value->id ?
                        'selected' : ''
                        }}>
                        {{ $value->name_dr }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="district_id">{{ localize('global.district') }}</label>
                <select class="form-control select2" required name="district_id"
                    id="district_id">
                    <option value="">{{ localize('global.select') }}</option>
                    @foreach ($districts as $value)
                    <option value="{{ $value->id }}" {{ old('name')==$value->id ?
                        'selected' : ''
                        }}>
                        {{ $value->name_dr }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <input type="hidden" name="branch_id" value="{{ Auth::user()->branch_id }}">
        <input type="hidden" name="type" value="0">


    </div>
    <button type="submit" class="btn btn-primary">{{ localize('global.create')
        }}</button>
    <a class="btn btn-danger" href="{{ url()->previous() }}" type="button">
        <span class="text-white"> <span class="d-none d-sm-inline-block  ">{{
                localize('global.back') }}</span></span>
    </a>
</form>