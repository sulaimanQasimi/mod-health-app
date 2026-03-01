@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <!-- Content -->
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.icu') }}</h5>
                </div>
                <div class="card-body">
                    {{-- Advanced Filters (same design as anesthesias approved) --}}
                    @php
                        $hasActiveFiltersIndex = request()->hasAny(['patient_name', 'card_number', 'father_name', 'search']);
                    @endphp
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-none border-0 py-3 cursor-pointer d-flex align-items-center justify-content-between" data-bs-toggle="collapse" data-bs-target="#icuIndexFilterCollapse" aria-expanded="{{ $hasActiveFiltersIndex ? 'true' : 'false' }}" aria-controls="icuIndexFilterCollapse" role="button">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-filter-alt text-primary me-2" style="font-size: 1.2rem;"></i>
                                <h6 class="mb-0 fw-semibold">{{ localize('global.advanced_filters') ?: 'Advanced Filters' }}</h6>
                                @if($hasActiveFiltersIndex)
                                    <span class="badge bg-label-primary ms-2">{{ count(array_filter(request()->only(['patient_name', 'card_number', 'father_name', 'search']))) }}</span>
                                @endif
                            </div>
                            <i class="bx bx-chevron-down transition-transform collapse-icon"></i>
                        </div>
                        <div class="collapse {{ $hasActiveFiltersIndex ? 'show' : '' }}" id="icuIndexFilterCollapse">
                            <div class="card-body">
                                <form method="GET" action="{{ route('icus.index') }}" id="icuIndexFilterForm">
                                    <div class="row g-3">
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
                                                    placeholder="{{ localize('global.search_patient_placeholder') }}"
                                                    autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="patient_name" class="form-label fw-semibold">
                                                <i class="bx bx-user me-1 text-success"></i>{{ localize('global.patient_name') }}
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-success text-white">
                                                    <i class="bx bx-user"></i>
                                                </span>
                                                <input type="text" class="form-control" id="patient_name" name="patient_name"
                                                    value="{{ request('patient_name') }}"
                                                    placeholder="{{ localize('global.search_by_patient_name') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="card_number" class="form-label fw-semibold">
                                                <i class="bx bx-id-card me-1 text-info"></i>{{ localize('global.card_number') }}
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-info text-white">
                                                    <i class="bx bx-id-card"></i>
                                                </span>
                                                <input type="text" class="form-control" id="card_number" name="card_number"
                                                    value="{{ request('card_number') }}"
                                                    placeholder="{{ localize('global.search_by_card_number') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="father_name" class="form-label fw-semibold">
                                                <i class="bx bx-user-circle me-1 text-secondary"></i>{{ localize('global.father_name') }}
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-secondary text-white">
                                                    <i class="bx bx-user-circle"></i>
                                                </span>
                                                <input type="text" class="form-control" id="father_name" name="father_name"
                                                    value="{{ request('father_name') }}"
                                                    placeholder="{{ localize('global.search_by_father_name') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-12 d-flex justify-content-end gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-filter me-1"></i>{{ localize('global.apply_filters') ?: 'Apply Filters' }}
                                            </button>
                                            <a href="{{ route('icus.index') }}" class="btn btn-outline-secondary">
                                                <i class="bx bx-refresh me-1"></i>{{ localize('global.reset') ?: 'Reset' }}
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @if($hasActiveFiltersIndex)
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <span class="text-muted small fw-semibold">{{ localize('global.active_filters') ?: 'Active Filters' }}:</span>
                            @if(request('patient_name'))
                                <a href="{{ request()->fullUrlWithQuery(['patient_name' => null]) }}" class="badge bg-primary py-2 px-2 text-decoration-none">{{ localize('global.patient_name') }}: {{ request('patient_name') }} <i class="bx bx-x ms-1"></i></a>
                            @endif
                            @if(request('card_number'))
                                <a href="{{ request()->fullUrlWithQuery(['card_number' => null]) }}" class="badge bg-info py-2 px-2 text-decoration-none">{{ localize('global.card_number') }}: {{ request('card_number') }} <i class="bx bx-x ms-1"></i></a>
                            @endif
                            @if(request('father_name'))
                                <a href="{{ request()->fullUrlWithQuery(['father_name' => null]) }}" class="badge bg-secondary py-2 px-2 text-decoration-none">{{ localize('global.father_name') }}: {{ request('father_name') }} <i class="bx bx-x ms-1"></i></a>
                            @endif
                            @if(request('search'))
                                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="badge bg-success py-2 px-2 text-decoration-none">{{ localize('global.search') }}: {{ request('search') }} <i class="bx bx-x ms-1"></i></a>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="card-datatable table-responsive">
                    <table class="datatables-basic table border-top">
                        <thead>
                            <tr>
                                <th></th>
                                <th>{{ localize('global.id') }}</th>
                                <th>{{ localize('global.card_number') }}</th>
                                <th>{{ localize('global.patient_name') }}</th>
                                <th>{{ localize('global.father_name') }}</th>
                                <th>{{ localize('global.room') }}</th>
                                <th>{{ localize('global.bed') }}</th>
                                <th>{{ localize('global.description') }}</th>
                                <th>{{ localize('global.register_date') }}</th>
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
        [data-bs-toggle="collapse"][aria-controls^="icu"] {
            cursor: pointer;
        }
        [data-bs-toggle="collapse"][aria-expanded="true"] .collapse-icon {
            transform: rotate(180deg);
        }
        .collapse-icon {
            transition: transform 0.2s ease;
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
                var filterParams = @json(request()->only(['patient_name', 'card_number', 'father_name', 'search']));
                dt_basic = dt_basic_table.DataTable({
                    ajax: {
                        url: "{{ route('icus.index') }}",
                        data: function(d) {
                            if (filterParams) {
                                d.patient_name = filterParams.patient_name || '';
                                d.card_number = filterParams.card_number || '';
                                d.father_name = filterParams.father_name || '';
                                d.search = filterParams.search || '';
                            }
                        }
                    },
                    columns: [{
                            data: 'id'
                        },

                        {
                            data: 'id'
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
                            data: 'hospitalization',
                            render: function(data) {
                                return (data && data.room && data.room.name) ? data.room.name : '—';
                            }
                        },
                        {
                            data: 'hospitalization',
                            render: function(data) {
                                return (data && data.bed && data.bed.number) ? data.bed.number : '—';
                            }
                        },
                        {
                            data: 'description',
                        },
                        {
                            data: 'created_at',
                            render: function(data) {
                            var formattedDate = moment(data.created_at).format('YYYY-MM-DD HH:MM:SS');
                            return formattedDate;
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
                                    `<a href="{{ url('icus/show/') }}` + `/` +
                                    full['id'] +
                                    `" class="btn btn-sm btn-icon text-primary"><i class="bx bx-expand"></i></a>`
                                );
                            }
                        }
                    ],
                    order: [
                        [0, 'asc']
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
                                    return 'Details of ' + data['full_name'];
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
        });
    </script>
@endpush
