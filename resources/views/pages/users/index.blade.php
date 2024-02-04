@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <!-- Content -->
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-success">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{localize('global.active_users')}}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-success" style="font-size: xx-large;">{{ $users->where('status', 1)->count() }}</h4>
                                    </div>
                                </div>
                                <span class="badge bg-success rounded p-2">
                                    <i class="bx bx-group bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-danger">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{localize('global.deactive_users')}}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-danger" style="font-size: xx-large;">{{ $users->where('status', 0)->count() }}</h4>
                                    </div>
                                </div>
                                <span class="badge bg-danger rounded p-2">
                                    <i class="bx bx-user-voice bx-lg"></i>
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
                                    <span>{{localize('global.total_users')}}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-primary" style="font-size: xx-large;">{{ $users->count() }}</h4>
                                    </div>
                                </div>
                                <span class="badge bg-primary rounded p-2">
                                    <i class="bx bx-group bx-lg"></i>
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
                                    <span>{{localize('global.new_users')}}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        @php
                                            $currentMonth = \Carbon\Carbon::now()->format('Y-m');
                                        @endphp
                                        <h4 class="mb-0 me-2 badge badge-center bg-info" style="font-size: xx-large;">
                                            {{ $users->filter(function ($user) use ($currentMonth) {
                                                if($user->created_at == null)
                                                {
                                                    return null;
                                                }else
                                                    return $user->created_at->format('Y-m') == $currentMonth;
                                                })->count() }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge bg-info rounded p-2">
                                    <i class="bx bx-user-plus bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Users List Table -->
            <div class="card">
                <div class="card-header border-bottom">
                    <div class="pt-3 pt-md-0 text-end">

                        <a class="btn btn-secondary create-new btn-primary" href="{{ route('users.create') }}"
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
                                <th>{{ localize('global.avatar') }}</th>
                                <th>{{ localize('global.name_dr') }}</th>
                                <th>{{ localize('global.email') }}</th>
                                @can('deactivate-users')
                                <th>{{ localize('global.status') }}</th>
                                @endcan
                                <th>{{ localize('global.roles') }}</th>
                                {{-- <th>{{ localize('global.recipients') }}</th> --}}
                                <th>{{ localize('global.user_recipients') }}</th>
                                <th></th>
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

        .user-avatar{
            width: 40px;
            height: 40px;
            border-radius: 50%;
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
                    ajax: "{{ route('users.index') }}",
                    columns: [{
                            data: 'id'
                        },

                        {
                            data: 'id'
                        },
                        {
                            data: 'avatar',
                            render: function(data, type, full, meta) {
                                var avatarUrl = data ? '{{ asset("storage") }}' + '/' + data : '{{ asset("assets/img/avatars/1.png") }}';
                                var avatarHtml = '<img src="' + avatarUrl + '" alt="' + full['name_dr'] + '" class="user-avatar">';
                                return avatarHtml;
                            }
                        },
                        {
                            data: 'name_dr'
                        },
                        {
                            data: 'email'
                        },
                        {
                            data: 'status',
                            render: function(data, type, full, meta) {
                                var isChecked = data === 1;
                                var isCurrentUser = full['id'] === {{ auth()->user()->id }};
                                var disabledAttribute = isCurrentUser ? 'disabled' : '';

                                var checkbox = '<input type="checkbox" class="status-checkbox" data-user-id="' + full['id'] + '"' +
                                    (isChecked ? ' checked' : '') + ' ' + disabledAttribute + '>';

                                var statusText = isChecked ? '{{ localize('global.active') }}' : '{{ localize('global.deactive') }}';

                                return checkbox + ' ' + statusText;
                            }
                        },

                        {
                            data: 'roles',
                            render: function(data) {
                                var rolesHtml = '';

                                data.forEach(function(role) {
                                    rolesHtml += '<span class="badge rounded-pill bg-label-danger">' + role
                                        .name_dr + '</span>&nbsp;';
                                });

                                return rolesHtml;
                            }
                        },
                        {
                            data: 'recipients',
                            render: function(data) {
                                var recipientsHtml = '';

                                data.forEach(function(recipient) {
                                    recipientsHtml += '<span class="badge rounded-pill bg-label-primary">' + recipient.name_dr + '</span>&nbsp;';
                                });

                                return recipientsHtml;
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

                                    `<a href="{{ url('users/edit/') }}` + `/` + full['id'] +
                                    `" class="btn btn-sm btn-icon item-edit"><i class="bx bxs-edit"></i></a>`
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

                //Event Listener for checkbox change.
                $('.datatables-basic tbody').on('change', '.status-checkbox', function() {
                    var userId = $(this).data('user-id');
                    var status = $(this).prop('checked') ? 1 : 0;

                    $.ajax({
                        url: "{{ route('users.update-status') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            user_id: userId,
                            status: status
                        },
                        success: function(response) {
                            // Handle success response if needed
                            console.log(response);
                            window.location.href = "{{ route('users.index') }}";
                        },
                        error: function(xhr, status, error) {
                            // Handle error response if needed
                            console.log(xhr.responseText);
                        }
                    });
                });
            }


            // Delete Record
            $('.datatables-basic tbody').on('click', '.delete-record', function() {
                dt_basic.row($(this).parents('tr')).remove().draw();
            });



            // Filter form control to default size
            // ? setTimeout used for multilingual table initialization
            setTimeout(() => {
                $('.dataTables_filter .form-control').removeClass('form-control-sm');
                $('.dataTables_length .form-select').removeClass('form-select-sm');
            }, 300);
        });
    </script>
@endpush
