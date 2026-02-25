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
                                        <label for="patient_name" class="form-label">{{ localize('global.status') }}</label>

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
                                        <label for="updated_by" class="form-label">{{ localize('global.updated_by') }}</label>
                                        <select class="form-control pager-search" name="updated_by" id="updated_by" style="width: 100%;">
                                            <option value="">{{ localize('global.select') }}</option>
                                        </select>
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
    <script>
        $(function () {
            // Select2 with built-in AJAX for updated_by (pharmacy users)
            var updatedBySelectUrl = "{{ route('api.select.pharmacy-users') }}";
            $('#updated_by').select2({
                placeholder: "{{ localize('global.select') }}",
                allowClear: true,
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: updatedBySelectUrl,
                    dataType: 'json',
                    delay: 300,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    data: function (params) {
                        return {
                            search: params.term || '',
                            page: params.page || 1,
                            pharmacy_id: $('#pharmacy_id').val() || ''
                        };
                    },
                    processResults: function (data) {
                        if (data && data.results && Array.isArray(data.results)) {
                            return {
                                results: data.results.map(function (item) {
                                    return { id: item.id, text: item.text || item.value };
                                }),
                                pagination: { more: (data.pagination && data.pagination.more) || false }
                            };
                        }
                        return { results: [], pagination: { more: false } };
                    },
                    cache: true
                }
            });

            // When pharmacy changes, clear updated_by selection
            $('#pharmacy_id').on('change', function () {
                $('#updated_by').val(null).trigger('change');
            });

            $('form').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    type: 'post',
                    url: "{{ route('prescriptions.report-search') }}",
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
        .sadira_date_range,
        .wareda_date_range {
            display: none;
        }
    </style>
@endpush