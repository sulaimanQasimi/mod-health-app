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
                                        <label for="operation_surgion_name"
                                            class="form-label">{{ localize('global.operation_surgion') }}</label>
                                        <input type="text" class="form-control pager-search" name="operation_surgion_name"
                                            value="{{ old('operation_surgion_name') }}"
                                            placeholder="{{ localize('global.operation_surgion') }}" />
                                    </div>
                                    <div class="col-md-3">
                                        <label for="operation_status"
                                            class="form-label">{{ localize('global.operation_status') }}</label>

                                        <select class="form-control pager-search select2" name="operation_status">
                                            <option value="" selected>{{ localize('global.select') }}</option>
                                            <option value="1">{{ localize('global.operation_complete') }}</option>
                                            <option value="0">{{ localize('global.operation_uncomplete') }}
                                            </option>
                                        </select>

                                    </div>

                                    <div class="col-md-3">
                                        <label for="operation_approval"
                                            class="form-label">{{ localize('global.operation_approval') }}</label>

                                        <select class="form-control pager-search select2" name="operation_approval">
                                            <option value="" selected>{{ localize('global.select') }}</option>
                                            <option value="1">{{ localize('global.operation_approved') }}</option>
                                            <option value="0">{{ localize('global.operation_not_approved') }}
                                            </option>
                                        </select>

                                    </div>

                                    <div class="col-md-3">
                                        <label for="status"
                                            class="form-label">{{ localize('global.reserve_status') }}</label>

                                        <select class="form-control pager-search select2" name="reserve_status">
                                            <option value="" selected>{{ localize('global.select') }}</option>
                                            <option value="1">{{ localize('global.reserved') }}</option>
                                            <option value="0">{{ localize('global.not_reserved') }}
                                            </option>
                                        </select>

                                    </div>

                                    <div class="col-md-3">
                                    <label for="operation_type_id"
                                                            class="mt-2 mb-2">{{ localize('global.operation_type') }}</label>
                                                        <select class="form-control select2" name="operation_type_id">
                                                            <option value="">{{ localize('global.select') }}</option>
                                                            @foreach ($operationTypes as $value)
                                                                <option value="{{ $value->id }}"
                                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                    {{ $value->name }}
        
                                                                </option>
                                                            @endforeach
                                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ localize('global.between_two_date') }}</label>
                                        <div class="input-group input-daterange" id="bs-datepicker-daterange">
                                            <input type="date" name="start" placeholder="{{ localize('global.from') }}"
                                                class="form-control" />
                                            <span class="input-group-text">...</span>
                                            <input type="date" name="end" placeholder="{{ localize('global.to') }}"
                                                class="form-control" />
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
$('form').submit(function(e) {
    e.preventDefault();
    $.ajax({
        type: 'post',
        url: "{{ route('operations.report-search') }}",
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