@extends('layouts.master')
@section('title', ' گزارش')
@section('content')
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Basic Bootstrap Table -->
            <div class="accordion m-3" id="accordionWithIcon">
                <div class="card accordion-item active">
                    <h2 class="accordion-header d-flex align-items-center">
                        <button type="button" class="accordion-button" data-bs-toggle="collapse"
                            data-bs-target="#accordionWithIcon-1" aria-expanded="true">
                            <i class="bx bx-search"></i>
                            {{ localize('global.documents.search') }}
                        </button>
                    </h2>
                    <div id="accordionWithIcon-1" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <form>
                                @csrf
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label for="patient_name"
                                            class="form-label">{{ localize('global.patient_name') }}</label>
                                        <input type="text" class="form-control pager-search" name="patient_name"
                                            value="{{ old('patient_name') }}"
                                            placeholder="{{ localize('global.patient_name') }}" />
                                    </div>

                                    <div class="col-md-3">
                                        <label for="nid">{{ localize('global.nid') }}</label>
                                        <input type="text" name="nid" value="{{ old('nid') }}"
                                            class="form-control pager-search">
                                    </div>

                                    <div class="col-md-3">
                                        <label for="id_card">{{ localize('global.id_card') }}</label>
                                        <input type="text" name="id_card" id="id_card" value="{{ old('id_card') }}"
                                            class="form-control pager-search">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="referral_name"
                                            class="form-label">{{ localize('global.referral_name') }}</label>
                                        <input type="text" class="form-control pager-search" name="referral_name"
                                            value="{{ old('referral_name') }}"
                                            placeholder="{{ localize('global.referral_name') }}" />
                                    </div>

                                    <div class="col-md-3">
                                        <label for="age">{{ localize('global.age') }}</label>
                                        <input type="text" name="age" id="age" value="{{ old('age') }}"
                                            class="form-control pager-search">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="gender">{{ localize('global.gender') }}</label>
                                        <select class="form-control select2" name="gender" id="gender">
                                            <option value="">{{ localize('global.select') }}</option>
                                            <option value="0">{{localize('global.male')}}</option>
                                            <option value="1">{{localize('global.female')}}</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="job_category">{{ localize('global.job_category') }}</label>
                                        <select class="form-control select2 pager-search" name="job_category"
                                            id="job_category">
                                            <option value="" selected>{{ localize('global.select') }}
                                            </option>
                                            <option value="0">{{localize('global.military')}}</option>
                                            <option value="1">{{localize('global.civilian')}}</option>
                                        </select>
                                    </div>


                                    <div class="col-md-3">
                                        <label for="disease_type"
                                            class="form-label">{{ localize('global.disease_type') }}</label>
                                        <select class="form-control pager-search select2" name="type">
                                            <option value="" selected>{{ localize('global.select') }}</option>
                                            <option value="0">{{ localize('global.mod') }}</option>
                                            <option value="1">{{ localize('global.recipient') }}
                                            </option>
                                            <option value="2">{{ localize('global.family') }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="referred_by">{{ localize('global.referred_by') }}</label>
                                        <select class="form-control select2 pager-search" name="referred_by">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($recipients as $value)
                                                <option value="{{ $value->id }}"> {{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="province_id">{{ localize('global.province') }}</label>
                                            <select class="form-control select2 pager-search" name="province_id"
                                                onchange="getDistricts(this.value)" id="province_id">
                                                <option value="">{{ localize('global.select') }}</option>
                                                @foreach ($provinces as $value)
                                                    <option value="{{ $value->id }}"> {{ $value->name_dr }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="district_id">{{ localize('global.district') }}</label>
                                            <select class="form-control select2 pager-search" name="district_id"
                                                id="district_id">
                                                <option value="">{{ localize('global.select') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">{{ localize('global.between_two_date') }}</label>
                                        <div class="input-group input-daterange" id="bs-datepicker-daterange">
                                            <input type="text" name="from" id="from_date"
                                                placeholder="{{ localize('global.from') }}"
                                                class="form-control form-control datepicker_dari pdp-el" />
                                            <span class="input-group-text">...</span>
                                            <input type="text" name="to" id="to_date"
                                                placeholder="{{ localize('global.to') }}"
                                                class="form-control form-control datepicker_dari pdp-el" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-label-primary">
                                            <i class="fa fa-search m-2"></i> <span>
                                                {{ localize('global.documents.search') }}</span>
                                        </button>
                                        <button type="reset" class="btn btn-label-secondary">
                                            <i class="fa fa-history m-2"></i>
                                            <span>{{ localize('global.reset') }}</span>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">

                <div class="table-responsive m-1" id="app">
                    <div class="search-document-data">


                    </div>
                </div>
            </div>
            <!--/ Basic Bootstrap Table -->
        </div>
        <!-- / Content -->
    </div>
@endsection

@push('custom-js')
    <script src="{{ asset('assets/js/vue/vue.js') }}"></script>
    <script>
        function getDistricts(province_id) {
            var provinceID = province_id;
            if (provinceID !== '') {
                $.ajax({
                    url: '/get_districts/' + provinceID,
                    type: 'GET',
                    success: function (response) {
                        $('#district_id').html(response);
                    }
                })
            } else {
                $('#district_id').html('<option value="">{{ localize("global.select") }}</option>');
            }
        }

        $('form').submit(function (e) {
            e.preventDefault();
            $.ajax({
                type: 'post',
                url: "{{ route('patients.report-search') }}",
                data: $(this).serialize(),
                beforeSend: function () {
                    // setting a timeout
                    $('.search-document-data').html(
                        '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
                    );

                },
                success: function (resp) {
                    $('.search-document-data').html(resp);
                }

            })
        })
    </script>
@endpush
@push('custom-css')
    <style>
        .sadira_date_range,
        .wareda_date_range {
            display: none;
        }
    </style>
@endpush