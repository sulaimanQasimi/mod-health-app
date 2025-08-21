@extends('layouts.master')
@section('title', 'گزارش خروجی دارو')
@section('content')
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">

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
                                        <label for="medicine_name"
                                            class="form-label">{{ localize('global.medicine_name') }}</label>
                                        <input type="text" class="form-control pager-search" name="medicine_name"
                                            value="{{ old('medicine_name') }}"
                                            placeholder="{{ localize('global.medicine_name') }}" />
                                    </div>
                                    <div class="col-md-3">
                                        <label for="patient_name"
                                            class="form-label">{{ localize('global.patient_name') }}</label>
                                        <input type="text" class="form-control pager-search" name="patient_name"
                                            value="{{ old('patient_name') }}"
                                            placeholder="{{ localize('global.patient_name') }}" />
                                    </div>
                                    <div class="col-md-3">
                                        <label for="outcome_type"
                                            class="form-label">{{ localize('global.outcome_type') }}</label>
                                        <select class="form-control pager-search select2" name="outcome_type">
                                            <option value="" selected>{{ localize('global.select') }}</option>
                                            <option value="prescription">{{ localize('global.prescription') }}</option>
                                            <option value="expired">{{ localize('global.expired') }}</option>
                                            <option value="damaged">{{ localize('global.damaged') }}</option>
                                            <option value="lost">{{ localize('global.lost') }}</option>
                                            <option value="return">{{ localize('global.return') }}</option>
                                        </select>
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
                                        </button>
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
    </div>
@endsection

@push('custom-js')
    <script src="{{ asset('assets/js/vue/vue.js') }}"></script>
    <script src="{{ asset('hijri/bootstrap-hijri-datetimepicker.js') }}"></script>
    <script>
        $(document).ready(function () {
            // Initialize Persian date pickers
            $('.persian-date').hijriDatePicker({
                format: 'YYYY/MM/DD',
                hijriFormat: 'iYYYY/iMM/iDD',
                dayViewHeaderFormat: 'MMMM iYYYY',
                hijriDayViewHeaderFormat: 'iMMMM iYYYY',
                showSwitcher: true,
                allowInputToggle: true,
                showTodayButton: true,
                useCurrent: false,
                isRTL: true,
                viewMode: 'days',
                keepOpen: false,
                hijri: true,
                debug: false,
                locale: 'fa-sa',
                icons: {
                    time: 'fa fa-clock-o',
                    date: 'fa fa-calendar',
                    up: 'fa fa-chevron-up',
                    down: 'fa fa-chevron-down',
                    previous: 'fa fa-chevron-right',
                    next: 'fa fa-chevron-left',
                    today: 'fa fa-screenshot',
                    clear: 'fa fa-trash',
                    close: 'fa fa-times'
                },
                tooltips: {
                    today: 'امروز',
                    clear: 'پاک کردن',
                    close: 'بستن',
                    selectMonth: 'انتخاب ماه',
                    prevMonth: 'ماه قبل',
                    nextMonth: 'ماه بعد',
                    selectYear: 'انتخاب سال',
                    prevYear: 'سال قبل',
                    nextYear: 'سال بعد',
                    selectDecade: 'انتخاب دهه',
                    prevDecade: 'دهه قبل',
                    nextDecade: 'دهه بعد',
                    prevCentury: 'قرن قبل',
                    nextCentury: 'قرن بعد',
                    pickHour: 'انتخاب ساعت',
                    incrementHour: 'افزایش ساعت',
                    decrementHour: 'کاهش ساعت',
                    pickMinute: 'انتخاب دقیقه',
                    incrementMinute: 'افزایش دقیقه',
                    decrementMinute: 'کاهش دقیقه',
                    pickSecond: 'انتخاب ثانیه',
                    incrementSecond: 'افزایش ثانیه',
                    decrementSecond: 'کاهش ثانیه',
                    togglePeriod: 'تغییر دوره',
                    selectTime: 'انتخاب زمان'
                }
            });

            // Form submission
            $('form').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    type: 'post',
                    url: "{{ route('outcomes.report-search') }}",
                    data: $(this).serialize(),
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
        });
    </script>
@endpush

@push('custom-css')
    <style>
        .persian-date {
            direction: rtl;
            text-align: right;
        }
    </style>
@endpush