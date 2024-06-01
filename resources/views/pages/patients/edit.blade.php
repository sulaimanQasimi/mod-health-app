@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.edit_patient') }}</h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('patients.update', $patient->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="name">{{ localize('global.name') }}</label>
                                        <input type="text" name="name" id="name" value="{{ $patient->name }}"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="last_name">{{ localize('global.last_name') }}</label>
                                        <input type="text" name="last_name" id="last_name"
                                            value="{{ $patient->last_name }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="father_name">{{ localize('global.father_name') }}</label>
                                        <input type="text" name="father_name" id="father_name"
                                            value="{{ $patient->father_name }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="nid">{{ localize('global.nid') }}</label>
                                        <input type="text" name="nid" id="nid" value="{{ $patient->nid }}"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="job">{{ localize('global.job') }}</label>
                                        <input type="text" name="job" id="job" value="{{ $patient->job }}"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="rank">{{ localize('global.rank') }}</label>
                                        <input type="text" name="rank" id="rank" value="{{ $patient->rank }}"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="phone">{{ localize('global.phone') }}</label>
                                        <input type="text" name="phone" id="phone" value="{{ $patient->phone }}"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="age">{{ localize('global.age') }}</label>
                                        <input type="text" name="age" id="age" value="{{ $patient->age }}"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="referred_by">{{ localize('global.referred_by') }}</label>
                                        <select class="form-control select2" name="referred_by">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($recipients as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ $patient->referred_by == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="province_id">{{ localize('global.province') }}</label>
                                        <select class="form-control select2" name="province_id" id="province_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($provinces as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ $patient->province_id == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name_dr }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="district_id">{{ localize('global.district') }}</label>
                                        <select class="form-control select2" name="district_id" id="district_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($districts as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ $patient->district_id == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name_dr }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="relation_id">{{ localize('global.relation') }}</label>
                                        <select class="form-control select2" name="relation_id" id="relation_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($relations as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ $patient->relation_id == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" name="branch_id" value="{{ $patient->branch_id }}">
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="gender">{{ localize('global.gender') }}</label>
                                        <select class="form-control select2" name="gender" id="gender">
                                            <option value="" selected>{{ localize('global.select') }}</option>
                                                <option value="0">{{localize('global.male')}}</option>
                                                <option value="1">{{localize('global.female')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="job_type">{{ localize('global.job_type') }}</label>
                                        <select class="form-control select2" name="job_type" id="job_type">
                                            <option value="">{{ localize('global.select') }}</option>
                                            <option value="civilian">{{localize('global.civilian')}}</option>
                                                <option value="militant">{{localize('global.militant')}}</option>
                                                <option value="retired">{{localize('global.retired')}}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">{{ localize('global.update') }}</button>
                            <a class="btn btn-danger" href="{{ url()->previous() }}" type="button">
                                <span class="text-white"> <span
                                        class="d-none d-sm-inline-block  ">{{ localize('global.back') }}</span></span>
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#province_id').on('change', function() {
                var provinceID = $(this).val();
                if (provinceID !== '') {
                    $.ajax({
                        url: '/get_districts/' + provinceID,
                        type: 'GET',
                        success: function(response) {

                            $('#district_id').html(response);
                        }
                    })
                }
            })
        })
    </script>
@endsection
