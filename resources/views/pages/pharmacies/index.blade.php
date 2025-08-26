@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <!-- Content -->
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Page Header -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">{{ localize('global.pharmacy_management') }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ localize('global.dashboard') }}</a></li>
                                <li class="breadcrumb-item active">{{ localize('global.pharmacies') }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-success">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.total_pharmacies') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-success" style="font-size: xx-large;">{{ $pharmacies->count() }}</h4>
                                    </div>
                                </div>
                                <span class="badge bg-success rounded p-2">
                                    <i class="bx bx-store bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.active_pharmacies') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-primary" style="font-size: xx-large;">{{ $pharmacies->count() }}</h4>
                                    </div>
                                </div>
                                <span class="badge bg-primary rounded p-2">
                                    <i class="bx bx-store-alt bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-info">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.new_this_month') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        @php
                                            $currentMonth = \Carbon\Carbon::now()->format('Y-m');
                                        @endphp
                                        <h4 class="mb-0 me-2 badge badge-center bg-info" style="font-size: xx-large;">
                                            {{ $pharmacies->filter(function ($pharmacy) use ($currentMonth) {
                                                if($pharmacy->created_at == null)
                                                {
                                                    return null;
                                                }else
                                                    return $pharmacy->created_at->format('Y-m') == $currentMonth;
                                                })->count() }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge bg-info rounded p-2">
                                    <i class="bx bx-plus-circle bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.total_users') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-warning" style="font-size: xx-large;">{{ $pharmacies->unique('user_id')->count() }}</h4>
                                    </div>
                                </div>
                                <span class="badge bg-warning rounded p-2">
                                    <i class="bx bx-user bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pharmacies List Table -->
            <div class="card">
                <div class="card-header border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.pharmacy_information') }}</h5>
                        <div class="text-end">
                            @can('pharmacy.create')
                            <a class="btn btn-primary" href="{{ route('pharmacies.create') }}" type="button">
                                <i class="bx bx-plus me-sm-1"></i> 
                                <span class="d-none d-sm-inline-block">{{ localize('global.create_pharmacy') }}</span>
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card-datatable table-responsive p-2">
                    <table class="datatables-basic table border-top">
                        <thead>
                            <tr>
                                <th></th>
                                <th>{{ localize('global.number') }}</th>
                                <th>{{ localize('global.pharmacy_name') }}</th>
                                <th>{{ localize('global.pharmacy_phone') }}</th>
                                <th>{{ localize('global.pharmacy_address') }}</th>
                                <th>{{ localize('global.pharmacy_user') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- / Content -->

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

        .page-title-box {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }

        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: ">";
            color: #6c757d;
        }

        .breadcrumb-item a {
            color: #007bff;
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: #6c757d;
        }

        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .card-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
        }

        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            border-radius: 6px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,123,255,0.3);
        }

        .bg-label-success {
            background: linear-gradient(45deg, #d4edda, #c3e6cb);
        }

        .bg-label-primary {
            background: linear-gradient(45deg, #cce7ff, #b3d9ff);
        }

        .bg-label-info {
            background: linear-gradient(45deg, #d1ecf1, #bee5eb);
        }

        .bg-label-warning {
            background: linear-gradient(45deg, #fff3cd, #ffeaa7);
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
                    ajax: "{{ route('pharmacies.index') }}",
                    columns: [{
                            data: 'id'
                        },
                        {
                            data: 'id'
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'phone'
                        },
                        {
                            data: 'address',
                            render: function(data, type, full, meta) {
                                if (type === 'display' && data.length > 50) {
                                    return '<span title="' + data + '">' + data.substr(0, 50) + '...</span>';
                                }
                                return data;
                            }
                        },
                        {
                            data: 'user',
                            render: function(data, type, full, meta) {
                                if (data) {
                                    return '<div class="d-flex align-items-center">' +
                                           '<div class="avatar avatar-sm me-2">' +
                                           '<span class="avatar-initial rounded-circle bg-label-primary">' +
                                           data.name.charAt(0).toUpperCase() + '</span>' +
                                           '</div>' +
                                           '<div>' +
                                           '<div class="fw-semibold">' + data.name + ' ' + (data.last_name || '') + '</div>' +
                                           '<small class="text-muted">' + data.email + '</small>' +
                                           '</div>' +
                                           '</div>';
                                }
                                return '<span class="text-muted">N/A</span>';
                            }
                        },
                        {
                            data: 'id',
                            render: function(data, type, full, meta) {
                                var actions = '<div class="d-flex gap-1">';
                                @can('pharmacy.show')
                                actions += '<a href="{{ route("pharmacies.show", ":id") }}" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ localize("global.show") }}"><i class="bx bx-show"></i></a>'.replace(':id', data);
                                @endcan
                                @can('pharmacy.edit')
                                actions += '<a href="{{ route("pharmacies.edit", ":id") }}" class="btn btn-sm btn-icon btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ localize("global.edit") }}"><i class="bx bx-edit"></i></a>'.replace(':id', data);
                                @endcan
                                @can('pharmacy.delete')
                                actions += '<button type="button" class="btn btn-sm btn-icon btn-outline-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ localize("global.delete") }}" onclick="deletePharmacy(' + data + ')"><i class="bx bx-trash"></i></button>';
                                @endcan
                                actions += '</div>';
                                return actions;
                            }
                        }
                    ],
                    order: [[1, 'desc']],
                    dom: '<"card-header border-bottom p-3"<"head-label text-center"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 10,
                    lengthMenu: [10, 25, 50, 75, 100],
                    buttons: [
                        {
                            extend: 'collection',
                            className: 'btn btn-primary dropdown-toggle me-2',
                            text: '<i class="bx bx-export me-sm-1"></i> <span class="d-none d-sm-inline-block">{{ localize("global.export") }}</span>',
                            buttons: [
                                {
                                    extend: 'print',
                                    text: '<i class="bx bx-printer me-1" ></i>{{ localize("global.print") }}',
                                    className: 'dropdown-item',
                                    exportOptions: { columns: [1, 2, 3, 4, 5] },
                                    customize: function (win) {
                                        $(win.document.body)
                                            .css('font-size', '10pt')
                                            .prepend('<img src="{{ asset("assets/img/logo.png") }}" style="position:absolute; top:0; left:0;" />');
                                        $(win.document.body).find('table')
                                            .addClass('compact')
                                            .css('font-size', 'inherit');
                                    }
                                },
                                {
                                    extend: 'pdf',
                                    text: '<i class="bx bxs-file-pdf me-1"></i>PDF',
                                    className: 'dropdown-item',
                                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                                },
                                {
                                    extend: 'excel',
                                    text: '<i class="bx bxs-file me-1"></i>Excel',
                                    className: 'dropdown-item',
                                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                                },
                                {
                                    extend: 'csv',
                                    text: '<i class="bx bxs-file-doc me-1"></i>CSV',
                                    className: 'dropdown-item',
                                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                                }
                            ]
                        }
                    ],
                    responsive: true,
                    language: {
                        sLengthMenu: '{{ localize("global.show") }} _MENU_ {{ localize("global.entries") }}',
                        search: '{{ localize("global.search") }}',
                        searchPlaceholder: '{{ localize("global.search_by_pharmacy_name") }}',
                        info: '{{ localize("global.showing") }} _START_ {{ localize("global.to") }} _END_ {{ localize("global.of") }} _TOTAL_ {{ localize("global.results") }}',
                        infoEmpty: '{{ localize("global.no_pharmacies_found") }}',
                        emptyTable: '{{ localize("global.no_pharmacies_found") }}',
                        zeroRecords: '{{ localize("global.no_pharmacies_found") }}'
                    },
                    initComplete: function () {
                        this.api()
                            .columns()
                            .every(function () {
                                var column = this;
                                var title = column.header().textContent;
                                var input = document.createElement('input');
                                input.placeholder = title;
                                $(input).appendTo($(column.footer()).empty())
                                    .on('change', function () {
                                        if (column.search() !== this.value) {
                                            column.search(this.value).draw();
                                        }
                                    });
                            });
                    }
                });
            }
        });

        function deletePharmacy(id) {
            if (confirm('{{ localize("global.are_you_sure_delete_pharmacy") }}')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("pharmacies.destroy", ":id") }}'.replace(':id', id);
                
                var tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = '{{ csrf_token() }}';
                
                var methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                
                form.appendChild(tokenInput);
                form.appendChild(methodInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
@endpush
