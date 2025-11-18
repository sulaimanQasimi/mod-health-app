@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <!-- Content -->
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">

            {{-- Page Header --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">
                            <i class="bx bx-hospital me-2 text-primary"></i>
                            {{ localize('global.hospitalizations') ?: 'Hospitalizations' }}
                        </h4>
                    </div>
                </div>
            </div>

            {{-- Advanced Search and Filters --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-none border-0 collapsed" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-filter-alt text-primary me-2" style="font-size: 1.2rem;"></i>
                            <h6 class="mb-0 fw-semibold">{{ localize('global.advanced_filters') ?: 'Advanced Filters' }}</h6>
                        </div>
                        <i class="bx bx-chevron-down"></i>
                    </div>
                </div>
                <div class="collapse" id="filterCollapse">
                    <div class="card-body">
                        <form method="GET" action="{{ route('hospitalizations.index') }}" id="filterForm">
                            <div class="row g-3">
                                {{-- Search Input --}}
                                <div class="col-md-4">
                                    <label for="search" class="form-label fw-semibold">
                                        <i class="bx bx-search me-1 text-primary"></i>{{ localize('global.search') }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="bx bx-search"></i>
                                        </span>
                                        <input type="text" class="form-control" id="search" name="search" 
                                               value="{{ request('search') }}" 
                                               placeholder="{{ localize('global.search_by_patient_room_bed') ?: 'Search by patient, room, bed...' }}"
                                               autocomplete="off">
                                    </div>
                                </div>

                                {{-- Room Filter --}}
                                <div class="col-md-3">
                                    <label for="room_id" class="form-label fw-semibold">
                                        <i class="bx bx-building me-1 text-info"></i>{{ localize('global.room') }}
                                    </label>
                                    <select class="form-select select2" id="room_id" name="room_id">
                                        <option value="">{{ localize('global.all') ?: 'All' }}</option>
                                        @foreach($rooms ?? [] as $room)
                                            <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                                {{ $room->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Date From --}}
                                <div class="col-md-2">
                                    <label for="date_from" class="form-label fw-semibold">
                                        <i class="bx bx-calendar me-1 text-info"></i>{{ localize('global.date_from') ?: 'Date From' }}
                                    </label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" 
                                           value="{{ request('date_from') }}">
                                </div>

                                {{-- Date To --}}
                                <div class="col-md-2">
                                    <label for="date_to" class="form-label fw-semibold">
                                        <i class="bx bx-calendar me-1 text-info"></i>{{ localize('global.date_to') ?: 'Date To' }}
                                    </label>
                                    <input type="date" class="form-control" id="date_to" name="date_to" 
                                           value="{{ request('date_to') }}">
                                </div>

                                {{-- Filter Buttons --}}
                                <div class="col-md-1 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bx bx-filter me-1"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-12 d-flex justify-content-end gap-2">
                                    <a href="{{ route('hospitalizations.index') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-refresh me-1"></i>{{ localize('global.reset') ?: 'Reset' }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Active Filters Display --}}
            @if(request()->hasAny(['search', 'room_id', 'date_from', 'date_to']))
                <div class="card mb-3 shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 fw-semibold">
                                    <i class="bx bx-filter me-1 text-primary"></i>{{ localize('global.active_filters') ?: 'Active Filters' }}:
                                </h6>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @if(request('search'))
                                        <span class="badge bg-primary">
                                            {{ localize('global.search') }}: {{ request('search') }}
                                            <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('room_id'))
                                        <span class="badge bg-info">
                                            {{ localize('global.room') }}: {{ $rooms->find(request('room_id'))->name ?? '' }}
                                            <a href="{{ request()->fullUrlWithQuery(['room_id' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('date_from'))
                                        <span class="badge bg-secondary">
                                            {{ localize('global.from') }}: {{ request('date_from') }}
                                            <a href="{{ request()->fullUrlWithQuery(['date_from' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('date_to'))
                                        <span class="badge bg-secondary">
                                            {{ localize('global.to') }}: {{ request('date_to') }}
                                            <a href="{{ request()->fullUrlWithQuery(['date_to' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Results Card --}}
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-list-ul me-2 text-primary"></i>
                        {{ localize('global.patients_list') ?: 'Patients List' }}
                    </h5>
                </div>
                <div class="card-datatable table-responsive">
                    <table class="datatables-basic table border-top">
                        <thead>
                            <tr>
                                <th></th>
                                <th>{{localize('global.id')}}</th>
                                <th>{{localize('global.card_number')}}</th>
                                <th>{{localize('global.patient_name')}}</th>
                                <th>{{localize('global.father_name')}}</th>
                                <th>{{localize('global.room')}}</th>
                                <th>{{localize('global.bed')}}</th>
                                <th>{{localize('global.doctor')}}</th>
                                <th>{{localize('global.hospitalization_date')}}</th>
                                <th>{{localize('global.actions')}}</th>
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

        .card-header[data-bs-toggle="collapse"] {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .card-header[data-bs-toggle="collapse"]:hover {
            background-color: #f8f9fa;
        }

        .badge {
            font-weight: 500;
        }

        /* Select2 Styles */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 0;
            padding-right: 20px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            right: 10px;
        }

        .select2-dropdown {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }

        .select2-results__option {
            padding: 0.5rem 0.75rem;
        }

        .select2-results__option--highlighted {
            background-color: #0d6efd;
            color: white;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            margin-right: 20px;
        }
    </style>
@endpush

@push('custom-js')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        $(function() {
            var dt_basic_table = $('.datatables-basic'),
                dt_basic;

            // Build AJAX URL with filters
            var ajaxUrl = "{{ route('hospitalizations.index') }}";
            var searchParams = new URLSearchParams(window.location.search);
            if (searchParams.toString()) {
                ajaxUrl += '?' + searchParams.toString() + '&ajax=1';
            } else {
                ajaxUrl += '?ajax=1';
            }

            if (dt_basic_table.length) {
                dt_basic = dt_basic_table.DataTable({
                    ajax: ajaxUrl,
                    columns: [{
                            data: 'id'
                        },
                        {
                            data: 'id'
                        },
                        {
                            data: 'patient',
                            render: function(data) {
                                return data ? '<span class="badge bg-secondary">' + (data.id_card || '') + '</span>' : '';
                            }
                        },
                        {
                            data: 'patient',
                            render: function(data) {
                                return data ? '<div class="d-flex align-items-center"><i class="bx bx-user me-2 text-primary"></i><strong>' + (data.name || 'N/A') + '</strong></div>' : 'N/A';
                            }
                        },
                        {
                            data: 'patient',
                            render: function(data) {
                                return data ? '<span class="text-muted">' + (data.father_name || '-') + '</span>' : '-';
                            }
                        },
                        {
                            data: 'room',
                            render: function(data) {
                                return data ? '<span class="badge bg-label-info"><i class="bx bx-building me-1"></i>' + data.name + '</span>' : 'N/A';
                            }
                        },
                        {
                            data: 'bed',
                            render: function(data) {
                                return data ? '<span class="badge bg-label-success"><i class="bx bx-bed me-1"></i>' + data.number + '</span>' : 'N/A';
                            }
                        },
                        {
                            data: 'doctor',
                            render: function(data) {
                                return data ? '<span class="text-primary"><i class="bx bx-user-md me-1"></i>' + data.name + '</span>' : 'N/A';
                            }
                        },
                        {
                            data: 'jalali_date',
                            defaultContent: 'Not set',
                            render: function(data) {
                                return data ? '<span class="text-muted"><i class="bx bx-calendar me-1"></i>' + data + '</span>' : 'Not set';
                            }
                        },
                        {
                            data: ''
                        },
                    ],
                    columnDefs: [{
                            // For Responsive
                            className: 'control',
                            orderable: false,
                            searchable: false,
                            responsivePriority: 2,
                            targets: 0,
                            render: function(data, type, full, meta) {
                                return '';
                            }
                        },
                        {
                            // Actions
                            targets: -1,
                            title: '{{ localize('global.actions') }}',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, full, meta) {
                                return (
                                    `<a href="{{ url('hospitalizations/show/') }}` + `/` + full['id'] +
                                    `" class="btn btn-sm btn-outline-primary" title="{{ localize('global.view') ?: 'View' }}"><i class="bx bx-show"></i></a>`
                                );
                            }
                        }
                    ],
                    order: [
                        [1, 'desc']
                    ],
                    dom: '<"flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 25,
                    lengthMenu: [7, 10, 25, 50, 75, 100],
                    buttons: [],
                    responsive: {
                        details: {
                            display: $.fn.dataTable.Responsive.display.modal({
                                header: function(row) {
                                    var data = row.data();
                                    return '{{ localize('global.hospitalization_details') ?: 'Hospitalization Details' }}';
                                }
                            }),
                            type: 'column',
                            renderer: function(api, rowIdx, columns) {
                                var data = $.map(columns, function(col, i) {
                                    return col.title !==
                                        '' // ? Do not show row in modal popup if title is blank (for check box)
                                        ?
                                        '<tr data-dt-row="' +
                                        col.rowIndex +
                                        '" data-dt-column="' +
                                        col.columnIndex +
                                        '">' +
                                        '<td>' +
                                        col.title +
                                        ':' +
                                        '</td> ' +
                                        '<td>' +
                                        col.data +
                                        '</td>' +
                                        '</tr>' :
                                        '';
                                }).join('');

                                return data ? $('<table class="table"/><tbody />').append(data) : false;
                            }
                        }
                    }
                });
            }

            // Filter form control to default size
            // ? setTimeout used for multilingual table initialization
            setTimeout(() => {
                $('.dataTables_filter .form-control').removeClass('form-control-sm');
                $('.dataTables_length .form-select').removeClass('form-select-sm');
            }, 300);

            // Initialize Select2
            if (typeof $.fn.select2 !== 'undefined') {
                $('.select2').each(function() {
                    var $select = $(this);
                    if (!$select.hasClass('select2-hidden-accessible')) {
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
                    }
                });
            }

            // Reinitialize Select2 when filter collapse is shown
            $('#filterCollapse').on('shown.bs.collapse', function() {
                if (typeof $.fn.select2 !== 'undefined') {
                    $('.select2').each(function() {
                        var $select = $(this);
                        if (!$select.hasClass('select2-hidden-accessible')) {
                            $select.select2({
                                width: '100%',
                                placeholder: '{{ localize("global.select") }}...',
                                allowClear: true
                            });
                        }
                    });
                }
            });

            // Reload DataTable when filters are applied
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                var newUrl = "{{ route('hospitalizations.index') }}?" + formData + '&ajax=1';
                dt_basic.ajax.url(newUrl).load();
            });
        });
    </script>
@endpush
