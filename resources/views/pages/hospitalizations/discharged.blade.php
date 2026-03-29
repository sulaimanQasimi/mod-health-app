
@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <!-- Content -->
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">
                            <i class="bx bx-exit me-2 text-primary"></i>
                            {{ localize('global.discharged_hospitalizations') }}
                        </h4>
                    </div>
                </div>
            </div>

            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-none border-0">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bx bx-filter-alt text-primary me-2"></i>{{ localize('global.filter') }}
                    </h6>
                </div>
                <div class="card-body discharged-filters-card">
                    <form id="dischargedFilterForm">
                        <div class="discharged-filters-toolbar d-flex flex-column flex-md-row flex-md-wrap align-items-stretch align-items-md-end gap-3">
                            <div class="discharged-filter-field flex-grow-1" style="min-width: 12rem;">
                                <label for="filter_search" class="form-label fw-semibold small mb-1 text-truncate d-block" title="{{ localize('global.patient_name') }}">
                                    <i class="bx bx-user me-1 text-primary"></i>{{ localize('global.patient_name') }}
                                </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text bg-primary text-white border-0">
                                        <i class="bx bx-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="filter_search" name="filter_search"
                                           value=""
                                           placeholder="{{ localize('global.search_by_patient_name') ?: localize('global.search_patient_name') }}"
                                           autocomplete="off">
                                </div>
                            </div>
                            <div class="discharged-filter-field" style="min-width: 6.5rem; max-width: 8rem;">
                                <label for="filter_patient_id" class="form-label fw-semibold small mb-1">
                                    <i class="bx bx-id-card me-1 text-primary"></i>{{ localize('global.patient_id') }}
                                </label>
                                <input type="number" class="form-control" id="filter_patient_id" name="patient_id"
                                       min="1" step="1"
                                       placeholder="{{ localize('global.search_by_patient_id') ?: 'ID' }}"
                                       autocomplete="off">
                            </div>
                            <div class="discharged-filter-field flex-grow-1" style="min-width: 9rem;">
                                <label for="filter_room_id" class="form-label fw-semibold small mb-1">
                                    <i class="bx bx-building me-1 text-info"></i>{{ localize('global.room') }}
                                </label>
                                <select class="form-select select2" id="filter_room_id" name="room_id">
                                    <option value="">{{ localize('global.all') ?: 'All' }}</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="discharged-filter-field flex-grow-1" style="min-width: 9rem;">
                                <label for="filter_doctor_id" class="form-label fw-semibold small mb-1">
                                    <i class="bx bx-user-pin me-1 text-info"></i>{{ localize('global.doctor') }}
                                </label>
                                <select class="form-select select2" id="filter_doctor_id" name="doctor_id">
                                    <option value="">{{ localize('global.all') ?: 'All' }}</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="discharged-filter-field" style="min-width: 10rem;">
                                <label for="filter_discharge_date_from" class="form-label fw-semibold small mb-1">
                                    <i class="bx bx-calendar-check me-1 text-success"></i>{{ localize('global.discharge_date') }} — {{ localize('global.from') ?: 'From' }}
                                </label>
                                <input type="text" autocomplete="off" class="form-control datepicker_dari pdp-el" id="filter_discharge_date_from" name="discharge_date_from"
                                       placeholder="1403/01/01">
                            </div>
                            <div class="discharged-filter-field" style="min-width: 10rem;">
                                <label for="filter_discharge_date_to" class="form-label fw-semibold small mb-1">
                                    <i class="bx bx-calendar-check me-1 text-success"></i>{{ localize('global.discharge_date') }} — {{ localize('global.to') ?: 'To' }}
                                </label>
                                <input type="text" autocomplete="off" class="form-control datepicker_dari pdp-el" id="filter_discharge_date_to" name="discharge_date_to"
                                       placeholder="1403/01/01">
                            </div>
                            <div class="discharged-filter-actions d-flex flex-shrink-0 gap-2 ms-md-auto pt-1 pt-md-0">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-filter me-1"></i>{{ localize('global.filter') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="dischargedFilterReset">
                                    <i class="bx bx-refresh me-1"></i>{{ localize('global.reset') ?: 'Reset' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.patients_list') }}</h5>
                </div>
                <div class="card-datatable table-responsive">
                    <table class="datatables-basic table border-top">
                        <thead>
                            <tr>
                                <th>{{ localize('global.patient_id') }}</th>
                                <th>{{localize('global.card_number')}}</th>
                                <th>{{localize('global.patient_name')}}</th>
                                <th>{{localize('global.father_name')}}</th>
                                <th>{{localize('global.room')}}</th>
                                <th>{{localize('global.bed')}}</th>
                                <th>{{localize('global.doctor')}}</th>
                                <th>{{localize('global.hospitalization_date')}}</th>
                                <th>{{localize('global.discharge_date')}}</th>
                                <th></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="content-backdrop fade"></div>
    </div>
@endsection

@push('custom-css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <style>
        .card-datatable table.dataTable thead th {
            text-align: right;
        }

        .card-datatable table.dataTable tbody td {
            text-align: right;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }

        .discharged-filters-card .discharged-filters-toolbar {
            width: 100%;
        }

        @media (min-width: 768px) {
            .discharged-filters-card .discharged-filter-field {
                flex: 1 1 auto;
            }

            .discharged-filters-card .discharged-filter-field:first-child {
                flex: 1.35 1 12rem;
            }

            .discharged-filters-card .discharged-filter-actions {
                flex: 0 0 auto;
            }
        }

        @media (min-width: 1200px) {
            .discharged-filters-card .discharged-filters-toolbar {
                flex-wrap: nowrap;
                overflow-x: auto;
                overflow-y: visible;
                padding-bottom: 0.25rem;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
@endpush

@push('custom-js')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        $(function() {
            var dt_basic_table = $('.datatables-basic'),
                dt_basic;

            if (dt_basic_table.length) {
                dt_basic = dt_basic_table.DataTable({
                    searching: false,
                    ajax: {
                        url: "{{ route('hospitalizations.discharged') }}",
                        type: 'GET',
                        data: function(d) {
                            d.filter_search = $('#filter_search').val() || '';
                            d.patient_id = $('#filter_patient_id').val() || '';
                            d.room_id = $('#filter_room_id').val() || '';
                            d.doctor_id = $('#filter_doctor_id').val() || '';
                            d.discharge_date_from = $('#filter_discharge_date_from').val() || '';
                            d.discharge_date_to = $('#filter_discharge_date_to').val() || '';
                        }
                    },
                    columns: [{
                            data: 'patient.id',
                            render: function(data, type, row) {
                                var v = data != null && data !== '' ? data : (row && row.patient_id != null ? row.patient_id : null);
                                return v != null ? v : '—';
                            }
                        },
                        {
                            data: 'patient',
                            render: function(data) {
                                return data ? data.id_card : '';
                            }
                        },
                        {
                            data: 'patient',
                            render: function(data) {
                                return data ? data.name : '';
                            }
                        },
                        {
                            data: 'patient',
                            render: function(data) {
                                return data ? data.father_name : '';
                            }
                        },
                        {
                            data: 'room',
                            render: function(data) {
                                return data ? data.name : '';
                            }
                        },
                        {
                            data: 'bed',
                            render: function(data) {
                                return data ? data.number : '';
                            }
                        },
                        {
                            data: 'doctor',
                            render: function(data) {
                                return data ? data.name : '';
                            }
                        },
                        {
                            data: 'jalali_date',
                        },
                        {
                            data: 'jalali_discharged_at',
                        },

                        {
                            data: ''
                        },


                    ],
                    columnDefs: [{
                            // Actions
                            targets: -1,
                            title: '{{ localize('global.actions') }}',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, full, meta) {
                                return (
                                    `<a href="{{ url('hospitalizations/show/') }}` + `/` + full['id'] +
                                    `" class="btn btn-sm btn-icon text-primary"><i class="bx bx-expand"></i></a>`
                                );
                            }
                        }
                    ],
                    order: [
                        [0, 'desc']
                    ],
                    responsive: false,
                    dom: '<"flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-md-0"B>><"row"<"col-sm-12 col-md-12"l>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 25,
                    lengthMenu: [7, 10, 25, 50, 75, 100],
                    buttons: []
                });

                $('#dischargedFilterForm').on('submit', function(e) {
                    e.preventDefault();
                    dt_basic.ajax.reload();
                });

                $('#dischargedFilterReset').on('click', function() {
                    $('#filter_search').val('');
                    $('#filter_patient_id').val('');
                    $('#filter_room_id').val('').trigger('change');
                    $('#filter_doctor_id').val('').trigger('change');
                    $('#filter_discharge_date_from, #filter_discharge_date_to').val('');
                    dt_basic.ajax.reload();
                });

                function initDischargedSelect2() {
                    if (typeof $.fn.select2 === 'undefined') {
                        return;
                    }
                    $('#filter_room_id, #filter_doctor_id').each(function() {
                        var $select = $(this);
                        if ($select.find('option').length <= 1) {
                            return;
                        }
                        if ($select.hasClass('select2-hidden-accessible')) {
                            try {
                                $select.select2('destroy');
                            } catch (e) {
                                $select.removeClass('select2-hidden-accessible');
                                $select.next('.select2-container').remove();
                            }
                        }
                        $select.select2({
                            width: '100%',
                            placeholder: '{{ localize("global.select") }}...',
                            allowClear: true,
                            language: {
                                noResults: function() {
                                    return '{{ localize("global.no_results_found") ?: "No results found" }}';
                                }
                            }
                        });
                    });
                }
                setTimeout(initDischargedSelect2, 200);
            }

            // Filter form control to default size
            // ? setTimeout used for multilingual table initialization
            setTimeout(() => {
                $('.dataTables_filter .form-control').removeClass('form-control-sm');
                $('.dataTables_length .form-select').removeClass('form-select-sm');
            }, 300);
        });
    </script>
@endpush

