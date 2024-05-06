@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.create_patient') }}</h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('patients.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="name">{{localize('global.name')}}</label>
                                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="last_name">{{localize('global.last_name')}}</label>
                                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="father_name">{{localize('global.father_name')}}</label>
                                        <input type="text" name="father_name" id="father_name" value="{{ old('father_name') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="nid">{{localize('global.nid')}}</label>
                                        <input type="text" name="nid" id="nid" value="{{ old('nid') }}" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="job">{{localize('global.job')}}</label>
                                        <input type="text" name="job" id="job" value="{{ old('job') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="rank">{{localize('global.rank')}}</label>
                                        <input type="text" name="rank" id="rank" value="{{ old('rank') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="phone">{{localize('global.phone')}}</label>
                                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="referred_by">{{localize('global.referred_by')}}</label>
                                        <select class="form-control select2" name="referred_by">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($recipients as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                {{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="province_id">{{localize('global.province')}}</label>
                                        <select class="form-control select2" name="province_id" id="province_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($provinces as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                {{ $value->name_dr }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="district_id">{{localize('global.district')}}</label>
                                        <select class="form-control select2" name="district_id" id="district_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($districts as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                {{ $value->name_dr }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="realtion_id">{{localize('global.relation')}}</label>
                                        <select class="form-control select2" name="realtion_id" id="realtion_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($relations as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                {{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" name="branch_id" value="{{Auth::user()->branch_id}}">
                            </div>
                            <button type="submit" class="btn btn-primary">{{localize('global.create')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

<script>
    $(document).ready(function()
{
    $('#province_id').on('change', function()
{
    var provinceID = $(this).val();
    if(provinceID !== '')
    {
        $.ajax({
            url: '/get_districts/' + provinceID,
            type: 'GET',
            success: function(response)
            {

                $('#district_id').html(response);
            }
        })
    }
})
})
</script>

@endsection
