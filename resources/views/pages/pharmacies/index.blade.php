@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card bg-label-success">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>{{ localize('global.total_pharmacies') }}</span>
                                <div class="d-flex align-items-end mt-2">
                                    <h4 class="mb-0 me-2 badge badge-center bg-success" style="font-size: xx-large;">
                                        {{ $pharmacies->count() }}</h4>
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
                                    <h4 class="mb-0 me-2 badge badge-center bg-primary" style="font-size: xx-large;">
                                        {{ $pharmacies->count() }}</h4>
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
                                            return $pharmacy->created_at && $pharmacy->created_at->format('Y-m') == $currentMonth;
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
                                    <h4 class="mb-0 me-2 badge badge-center bg-warning" style="font-size: xx-large;">
                                        {{ $pharmacies->unique('user_id')->count() }}</h4>
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

        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0">{{ localize('global.pharmacy_information') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        @can('pharmacy.create')
                            <a class="btn btn-primary btn-lg" href="{{ route('pharmacies.create') }}" type="button">
                                <i class="bx bx-plus me-2"></i>{{ localize('global.create') }}
                            </a>
                        @endcan
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
                                <th>{{ localize('global.pharmacy_users') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
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
        $(function () {
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
                        render: function (data, type, full, meta) {
                            if (type === 'display' && data.length > 50) {
                                return '<span title="' + data + '">' + data.substr(0, 50) + '...</span>';
                            }
                            return data;
                        }
                    },
                    {
                        data: 'active_users',
                        render: function (data, type, full, meta) {
                            if (data && data.length > 0) {
                                var usersHtml = '';
                                data.forEach(function(user, index) {
                                    if (index < 3) { // Show only first 3 users
                                        usersHtml += '<div class="d-flex align-items-center mb-1">' +
                                            '<div class="avatar avatar-xs me-2">' +
                                            '<span class="avatar-initial rounded-circle bg-label-primary">' +
                                            user.name.charAt(0).toUpperCase() + '</span>' +
                                            '</div>' +
                                            '<div class="flex-grow-1">' +
                                            '<div class="fw-semibold text-truncate" style="max-width: 120px;">' + user.name + ' ' + (user.last_name || '') + '</div>' +
                                            '<small class="text-muted">' + user.pivot.role + '</small>' +
                                            '</div>' +
                                            '</div>';
                                    }
                                });
                                if (data.length > 3) {
                                    usersHtml += '<small class="text-muted">+ ' + (data.length - 3) + ' more</small>';
                                }
                                return usersHtml;
                            }
                            return '<span class="text-muted">{{ localize("global.no_users_assigned") }}</span>';
                        }
                    },
                    {
                        data: 'id',
                        render: function (data, type, full, meta) {
                            var actions = '<div class="d-flex gap-1">';
                            @can('pharmacy.show')
                                actions += '<a href="{{ route("pharmacies.show", ":id") }}" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ localize("global.show") }}"><i class="bx bx-show"></i></a>'.replace(':id', data);
                            @endcan
                            @can('pharmacy.manage_users')
                                actions += '<a href="{{ route("pharmacies.manage-users", ":id") }}" class="btn btn-sm btn-icon btn-outline-info" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ localize("global.manage_users") }}"><i class="bx bx-user-plus"></i></a>'.replace(':id', data);
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