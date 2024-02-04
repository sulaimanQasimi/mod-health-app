@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-2"> {{ localize('global.roles_list') }}</h4>
            <div class="row g-4 mb-4">
                <!-- Users List Table -->
                <div class="card">
                    <div class="card-header border-bottom">
                        <div class="pt-3 pt-md-0 text-end">
                            <a class="btn btn-secondary create-new btn-primary" href="{{ route('roles.create') }}"
                               type="button">
                                <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                          class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                            </a>
                        </div>
                    </div>
                    <div class="card-datatable table-responsive">
                        <table class="datatables-basic table border-top">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.name_en') }}</th>
                                    <th>{{ localize('global.name_dr') }}</th>
                                    <th style="width: 500px;">{{ localize('global.permissions') }}</th>
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
                    ajax: "{{ route('roles.index') }}",
                    columns: [
                        {
                            data: ''
                        },
                        {
                            data: 'id'
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'name_dr'
                        },
                        {
                            data: 'permissions',
                            render: function(data) {
                                        var rolesHtml = '';

                                        data.forEach(function(permission) {
                                            rolesHtml += '<span class="badge rounded-pill bg-label-primary">' + permission
                                                .name_dr + '</span>&nbsp;';
                                        });

                                        return rolesHtml;
                                    }
                        },
                        {
                            data: ''
                        }
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
                            orderable: false,
                            searchable: false,
                            render: function(data, type, full, meta) {
                                return (

                                    `<a href="{{ url('roles/edit/') }}` + `/` + full[
                                        'id'] +
                                    `" class="btn btn-sm btn-icon item-edit text-warning"><i class="bx bxs-edit"></i></a>`+
                                    `<a href="{{ url('roles/destroy/') }}` + `/` + full['id'] +
                                    `" class="btn btn-sm btn-icon item-delete text-danger"><i class="bx bxs-trash"></i></a>`
                                );
                            }
                        }
                    ],
                    order: [
                        [0, 'asc']
                    ],
                    dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 7,
                    lengthMenu: [7, 10, 25, 50, 75, 100],
                    buttons: [

                    ],
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
