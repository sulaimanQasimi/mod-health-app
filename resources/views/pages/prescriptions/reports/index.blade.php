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
                            <form id="prescription-report-form">
                                @csrf
                                <input type="hidden" name="type" id="export_type" value="">
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label for="patient_name" class="form-label">{{ localize('global.patient_name') }}</label>
                                        <input type="text" class="form-control pager-search" name="patient_name"
                                            value="{{ old('patient_name') }}"
                                            placeholder="{{ localize('global.patient_name') }}" />
                                    </div>
                                    <div class="col-md-3">
                                        <label for="father_name" class="form-label">{{ localize('global.father_name') }}</label>
                                        <input type="text" class="form-control pager-search" name="father_name"
                                            value="{{ old('father_name') }}"
                                            placeholder="{{ localize('global.father_name') }}" />
                                    </div>
                                    <div class="col-md-3">
                                        <label for="is_completed" class="form-label">{{ localize('global.status') }}</label>

                                        <select class="form-control pager-search select2" name="is_completed">
                                            <option value="" selected>{{ localize('global.select') }}</option>
                                            <option value="1">{{ localize('global.delivered_prescriptions') }}</option>
                                            <option value="0">{{ localize('global.undelivered_prescriptions') }}</option>

                                        </select>

                                    </div>
                                    <div class="col-md-3">
                                        <label for="pharmacy_id" class="form-label">{{ localize('global.pharmacy') }}</label>
                                        <select class="form-control pager-search select2" name="pharmacy_id" id="pharmacy_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach(\App\Models\Pharmacy::all() as $pharmacy)
                                                <option value="{{ $pharmacy->id }}" {{ old('pharmacy_id') == $pharmacy->id ? 'selected' : '' }}>
                                                    {{ $pharmacy->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="processed_by_user_id" class="form-label">{{ localize('global.processed_by') }}</label>
                                        <select class="form-control pager-search select2" name="processed_by_user_id" id="processed_by_user_id" disabled>
                                            <option value="">{{ localize('global.select') }}</option>
                                            </select>
                                        <small class="text-muted">{{ localize('global.select_pharmacy_first_for_user') }}</small>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ localize('global.between_two_date') }}</label>
                                        <div class="input-group input-daterange">
                                            <input type="text" name="start" placeholder="{{ localize('global.from') }}"
                                                class="form-control form-control datepicker_dari pdp-el" />
                                            <span class="input-group-text">...</span>
                                            <input type="text" name="end" placeholder="{{ localize('global.to') }}"
                                                class="form-control form-control datepicker_dari pdp-el" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-md-6">
                                        <button type="button" id="btn-report-search" class="btn btn-label-primary">
                                            <i class="fa fa-search m-2"></i> <span>
                                                {{ localize('global.documents.search') }}</span>
                                        </button>
                                        <button type="reset" class="btn btn-label-secondary">
                                            <i class="fa fa-history m-2"></i>
                                            <span>{{ localize('global.reset') }}</span>
                                        </button>
                                        <button type="button" class="btn btn-success" id="btn-export-excel" title="{{ localize('global.export_excel') }}">
                                            <i class="fa fa-file-excel m-2"></i> <span>{{ localize('global.export_excel') }}</span>
                                        </button>
                                        <button type="button" class="btn btn-danger" id="btn-export-pdf" title="{{ localize('global.export_pdf') }}">
                                            <i class="fa fa-file-pdf m-2"></i> <span>{{ localize('global.export_pdf') }}</span>
                                        </button>
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
    <script>
        var reportPharmacyUsersUrl = "{{ route('prescriptions.report-pharmacy-users', ['pharmacy' => '__PHARMACY_ID__']) }}";
        var oldProcessedByUserId = "{{ old('processed_by_user_id', '') }}";

        $('#pharmacy_id').on('change', function () {
            var pharmacyId = $(this).val();
            var $userSelect = $('#processed_by_user_id');
            $userSelect.empty().append($('<option value="">').text("{{ localize('global.select') }}"));
            if (!pharmacyId) {
                $userSelect.prop('disabled', true);
                if ($userSelect.hasClass('select2-hidden-accessible')) {
                    $userSelect.select2('destroy');
                    $userSelect.select2();
                }
                return;
            }
            $userSelect.prop('disabled', true);
            $.get(reportPharmacyUsersUrl.replace('__PHARMACY_ID__', pharmacyId))
                .done(function (users) {
                    users.forEach(function (u) {
                        $userSelect.append($('<option value="' + u.id + '">').text(u.name || ('User #' + u.id)));
                    });
                    $userSelect.prop('disabled', false);
                    if (oldProcessedByUserId) {
                        $userSelect.val(oldProcessedByUserId);
                    }
                    if ($userSelect.hasClass('select2-hidden-accessible')) {
                        $userSelect.select2('destroy');
                    }
                    $userSelect.select2();
                })
                .fail(function () {
                    $userSelect.prop('disabled', false);
                    if ($userSelect.hasClass('select2-hidden-accessible')) {
                        $userSelect.select2('destroy');
                    }
                    $userSelect.select2();
                });
        });

        // Trigger change on load if pharmacy is pre-selected (e.g. old input)
        @if(old('pharmacy_id'))
        $('#pharmacy_id').trigger('change');
        @endif

        var reportSearchUrl = "{{ route('prescriptions.report-search') }}";
        var exportReportUrl = "{{ route('prescriptions.export-report') }}";

        $('#btn-report-search').on('click', function () {
            var $form = $('#prescription-report-form');
            $.ajax({
                type: 'post',
                url: reportSearchUrl,
                data: $form.serialize(),
                beforeSend: function () {
                    $('.search-document-data').html(
                        '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
                    );
                },
                success: function (resp) {
                    $('.search-document-data').html(resp);
                }
            });
        });

        $('#btn-export-excel').on('click', function () {
            var $form = $('#prescription-report-form');
            $('#export_type').val('excel');
            $form.attr('action', exportReportUrl).attr('method', 'post');
            $form.off('submit').submit();
        });

        $('#btn-export-pdf').on('click', function () {
            var $form = $('#prescription-report-form');
            $('#export_type').val('pdf');
            $form.attr('action', exportReportUrl).attr('method', 'post');
            $form.off('submit').submit();
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