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
                    <div>
                        <h5 class="mb-0">{{ localize('global.department_appointments') }}</h5>
                        <small class="text-muted">
                            <i class="bx bx-info-circle me-1"></i>
                            {{ localize('global.appointments_referred_by_doctors') }}
                        </small>
                    </div>
                </div>
                <div class="card-datatable table-responsive">
                    <table class="datatables-basic table border-top">
                        <thead>
                            <tr>
                                <th></th>
                                <th>{{localize('global.id')}}</th>
                                <th>{{localize('global.patient_name')}}</th>
                                <th>{{localize('global.father_name')}}</th>
                                <th>{{localize('global.department')}}</th>
                                <th>{{localize('global.date')}}</th>
                                <th>{{localize('global.time')}}</th>
                                <th>{{localize('global.status')}}</th>
                                <th></th>
                            </tr>
                        </thead>
                    </table>
                </div>
                
                <!-- Legend Section -->
                <div class="card-footer bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-2">{{ localize('global.legend') }}:</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-info">
                                    <i class="bx bx-user me-1"></i>
                                    {{ localize('global.introduced_by_doctor') }}
                                </span>
                                <span class="badge bg-secondary">
                                    <i class="bx bx-plus me-1"></i>
                                    {{ localize('global.direct_appointment') }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2">{{ localize('global.actions') }}:</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-sm btn-success" disabled>
                                    <i class="bx bx-check me-1"></i>
                                    {{ localize('global.accept') }}
                                </button>
                                <button class="btn btn-sm btn-info" disabled>
                                    <i class="bx bx-info-circle me-1"></i>
                                    {{ localize('global.referral_remarks') }}
                                </button>
                                <button class="btn btn-sm btn-outline-info" disabled>
                                    <i class="bx bx-user me-1"></i>
                                    {{ localize('global.referring_doctor') }}
                                </button>
                            </div>
                        </div>
                    </div>
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
                    ajax: "{{ route('appointments.departmentAppointments') }}",
                    columns: [{
                            data: 'id'
                        },
                        {
                            data: 'id'
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
                            data: 'department',
                            render: function(data) {
                                return data ? data.name : '';
                            }
                        },
                        {
                            data: 'jalali_date',
                        },
                        {
                            data: 'time'
                        },
                        {
                            data: 'doctor_id',
                            render: function(data) {
                                if (data) {
                                    return '<span class="badge bg-success">{{ localize("global.assigned") }}</span>';
                                } else {
                                    return '<span class="badge bg-warning">{{ localize("global.pending") }}</span>';
                                }
                            }
                        },
                        {
                            data: null,
                            defaultContent: ''
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
                            title: '{{ localize('global.actions') }}',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, full, meta) {
                                var actions = '';
                                
                                // Show accept button only if no doctor is assigned
                                if (!full['doctor_id']) {
                                    actions += '<form method="POST" action="{{ url("appointments/accept/") }}/' + full['id'] + '" style="display: inline;">' +
                                        '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                                        '<button type="submit" class="btn btn-sm btn-success" onclick="return confirm(\'{{ localize("global.confirm_accept_appointment") }}\')">' +
                                            '<i class="bx bx-check"></i> {{ localize("global.accept") }}' +
                                        '</button>' +
                                    '</form>';
                                }
                                
                                // Show referral remarks if available
                                if (full['refferal_remarks']) {
                                    actions += '<button type="button" class="btn btn-sm btn-info ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="' + full['refferal_remarks'] + '">' +
                                        '<i class="bx bx-info-circle"></i>' +
                                    '</button>';
                                }
                                
                                // Show referring doctor info if available
                                if (full['referring_doctor'] && full['referring_doctor'].name) {
                                    actions += '<button type="button" class="btn btn-sm btn-outline-info ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ localize("global.introduced_by") }}: ' + full['referring_doctor'].name + '">' +
                                        '<i class="bx bx-user"></i>' +
                                    '</button>';
                                }
                                
                                return actions;
                            }
                        }
                    ],
                    order: [
                        [0, 'asc']
                    ],
                    dom: 'Bfrtip',
                    displayLength: 25,
                    lengthMenu: [7, 10, 25, 50, 75, 100],
                    buttons: [],
                    responsive: true
                });
            }
        });
    </script>
@endpush
