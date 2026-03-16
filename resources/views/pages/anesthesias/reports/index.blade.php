@extends('layouts.master')
@section('title', ' گزارش')
@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <div class="card">
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
                            <form id="anesthesias-report-search-form">
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
                                        <label for="report_status"
                                            class="form-label">{{ localize('global.status') }}</label>
                                        <select class="form-control pager-search select2" name="status" id="report_status">
                                            <option value="" selected>{{ localize('global.select') }}</option>
                                            <option value="new">{{ localize('global.new_anesthesias') }}</option>
                                            <option value="approved">{{ localize('global.approved_anesthesias') }}</option>
                                            <option value="rejected">{{ localize('global.rejected_anesthesias') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="report_doctor_id" class="form-label">{{ localize('global.doctor_name') }}</label>
                                        <select class="form-control select2" name="doctor_id" id="report_doctor_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($doctors ?? [] as $doc)
                                                <option value="{{ $doc->id }}" {{ old('doctor_id') == $doc->id ? 'selected' : '' }}>{{ $doc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="anesthesia_type">{{ localize('global.anesthesia_type') }}</label>
                                        <select class="form-control select2" name="anesthesia_type" id="anesthesia_type">
                                            <option value="">{{ localize('global.select') }}</option>
                                            <option value="local">{{ localize('global.local') }}</option>
                                            <option value="spinal">{{ localize('global.spinal') }}</option>
                                            <option value="general">{{ localize('global.general') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="operation_type_id" class="form-label">{{ localize('global.operation_type') }}</label>
                                        <select class="form-control select2" name="operation_type_id" id="operation_type_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($operationTypes ?? [] as $type)
                                                <option value="{{ $type->id }}" {{ old('operation_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="department_id" class="form-label">{{ localize('global.department') }}</label>
                                        <select class="form-control select2" name="department_id" id="department_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($departments ?? [] as $dept)
                                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="report_time" class="form-label">{{ localize('global.time') }}</label>
                                        <input type="time" class="form-control pager-search" name="time" id="report_time"
                                            value="{{ old('time') }}" placeholder="{{ localize('global.time') }}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ localize('global.between_two_date') }}</label>
                                        <div class="input-group input-daterange">
                                            <input type="text" name="from" id="report_from" placeholder="{{ localize('global.from') }}"
                                                class="form-control datepicker_dari pdp-el" value="{{ old('from') }}" />
                                            <span class="input-group-text">...</span>
                                            <input type="text" name="to" id="report_to" placeholder="{{ localize('global.to') }}"
                                                class="form-control datepicker_dari pdp-el" value="{{ old('to') }}" />
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
$('#anesthesias-report-search-form').submit(function(e) {
    e.preventDefault();
    $.ajax({
        type: 'post',
        url: "{{ route('anesthesias.report-search') }}",
        data: $(this).serialize(),
        beforeSend: function() {
            // setting a timeout
            $('.search-document-data').html(
                '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
            );

        },
        success: function(resp) {
            $('.search-document-data').html(resp);
        }

    })
});

$('#anesthesias-report-search-form').on('reset', function() {
    var $form = $(this);
    $('.search-document-data').html('');
    setTimeout(function() {
        $form.find('select').val(null).trigger('change');
        $form.find('input[type="text"]').val('');
        $form.find('input[type="time"]').val('');
        $form.find('.datepicker_dari.pdp-el').val('');
    }, 0);
});
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