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
                                <th>{{localize('global.card_number')}}</th>
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
            </div>
        </div>
    </div>

    <!-- Select Doctor Modal -->
    <div class="modal fade" id="selectDoctorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ localize('global.select_doctor') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignDoctorForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="appointment_id" name="appointment_id">
                        <div class="mb-3">
                            <label for="doctor_id" class="form-label">{{ localize('global.select_doctor') }}</label>
                            <select class="form-control select2" id="doctor_id" name="doctor_id" required>
                                <option value="">{{ localize('global.select_doctor') }}</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ localize('global.assign') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Department Modal -->
    <div class="modal fade" id="changeDepartmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ localize('global.change_department') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="changeDepartmentForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" id="change_dept_appointment_id" name="appointment_id">
                        <div class="mb-3">
                            <label for="department_id" class="form-label">{{ localize('global.select_department') }}</label>
                            <select class="form-control select2" id="department_id" name="department_id" required>
                                <option value="">{{ localize('global.select_department') }}</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ localize('global.update') }}</button>
                    </div>
                </form>
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
                                
                                // Show change department button
                                actions += '<button type="button" class="btn btn-sm btn-warning" onclick="openChangeDepartmentModal(' + full['id'] + ', ' + (full['department'] ? full['department'].id : 'null') + ')" title="{{ localize("global.change_department") }}">' +
                                    '<i class="bx bx-transfer"></i> {{ localize("global.change_department") }}' +
                                '</button>';
                                
                                // Show select doctor button only if no doctor is assigned
                                if (!full['doctor_id']) {
                                    actions += '<button type="button" class="btn btn-sm btn-success ms-1" onclick="openSelectDoctorModal(' + full['id'] + ')">' +
                                        '<i class="bx bx-user-plus"></i> {{ localize("global.select_doctor") }}' +
                                    '</button>';
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

            // Initialize Select2 when modal opens
            $('#selectDoctorModal').on('shown.bs.modal', function() {
                $('#doctor_id').select2({
                    dropdownParent: $('#selectDoctorModal'),
                    width: '100%'
                });
            });

            // Reset modal when closed
            $('#selectDoctorModal').on('hidden.bs.modal', function() {
                const form = $('#assignDoctorForm');
                const doctorSelect = $('#doctor_id');
                
                // Reset form
                form[0].reset();
                
                // Destroy select2 if it exists
                if (doctorSelect.hasClass('select2-hidden-accessible')) {
                    doctorSelect.select2('destroy');
                }
                
                // Reset select
                doctorSelect.empty().append('<option value="">{{ localize("global.select_doctor") }}</option>');
                
                // Reset form action
                form.attr('action', '#');
            });

            // Initialize Select2 for department modal
            $('#changeDepartmentModal').on('shown.bs.modal', function() {
                $('#department_id').select2({
                    dropdownParent: $('#changeDepartmentModal'),
                    width: '100%'
                });
            });

            // Reset department modal when closed
            $('#changeDepartmentModal').on('hidden.bs.modal', function() {
                $('#changeDepartmentForm')[0].reset();
                $('#department_id').empty().append('<option value="">{{ localize("global.select_department") }}</option>');
            });
        });

        function openSelectDoctorModal(appointmentId) {
            // Set appointment ID
            $('#appointment_id').val(appointmentId);
            
            // Set form action using the assign-doctor route
            const actionUrl = '{{ url("appointments/assign-doctor") }}/' + appointmentId;
            $('#assignDoctorForm').attr('action', actionUrl);
            
            // Load doctors
            loadDoctorsForAppointment();
            
            // Show modal
            $('#selectDoctorModal').modal('show');
        }

        function loadDoctorsForAppointment() {
            const doctorSelect = $('#doctor_id');
            
            // Destroy existing select2 if it exists
            if (doctorSelect.hasClass('select2-hidden-accessible')) {
                doctorSelect.select2('destroy');
            }
            
            // Show loading state
            doctorSelect.empty().append('<option value="">{{ localize("global.loading") }}...</option>').prop('disabled', true);
            
            $.ajax({
                url: '{{ route("appointments.get-doctors-by-clinic-type") }}',
                type: 'GET',
                success: function(response) {
                    doctorSelect.empty().append('<option value="">{{ localize("global.select_doctor") }}</option>');
                    
                    if (response.success && response.doctors && response.doctors.length > 0) {
                        response.doctors.forEach(function(doctor) {
                            const option = new Option(doctor.name, doctor.id, false, false);
                            doctorSelect.append(option);
                        });
                        doctorSelect.prop('disabled', false);
                    } else {
                        doctorSelect.append('<option value="">{{ localize("global.no_doctors_available") }}</option>');
                        doctorSelect.prop('disabled', true);
                    }
                    
                    // Initialize select2
                    doctorSelect.select2({
                        dropdownParent: $('#selectDoctorModal'),
                        width: '100%'
                    });
                },
                error: function(xhr) {
                    console.error('Error loading doctors:', xhr);
                    doctorSelect.empty().append('<option value="">{{ localize("global.error_loading_doctors") }}</option>');
                    doctorSelect.prop('disabled', true);
                    
                    // Initialize select2 even on error
                    doctorSelect.select2({
                        dropdownParent: $('#selectDoctorModal'),
                        width: '100%'
                    });
                }
            });
        }

        // Handle form submission
        $('#assignDoctorForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const actionUrl = form.attr('action');
            
            // Validate action URL is set
            if (!actionUrl || actionUrl === '#' || actionUrl === '') {
                alert('{{ localize("global.error_occurred") }}: Form action not set');
                return;
            }
            
            // Validate doctor is selected
            const doctorId = $('#doctor_id').val();
            if (!doctorId) {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('{{ localize("global.please_select_doctor") }}');
                } else {
                    alert('{{ localize("global.please_select_doctor") }}');
                }
                return;
            }
            
            const formData = form.serialize();
            
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                dataType: 'json',
                success: function(response) {
                    // Check if response indicates success
                    if (response && response.success === false) {
                        // Handle error response
                        const errorMessage = response.message || '{{ localize("global.error_occurred") }}';
                        if (typeof toastr !== 'undefined') {
                            toastr.error(errorMessage);
                        } else {
                            alert(errorMessage);
                        }
                        return;
                    }
                    
                    $('#selectDoctorModal').modal('hide');
                    
                    // Show success message
                    const successMessage = (response && response.message) 
                        ? response.message 
                        : '{{ localize("global.doctor_assigned_successfully") }}';
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.success(successMessage);
                    } else {
                        alert(successMessage);
                    }
                    
                    // Reload datatable via AJAX
                    if (typeof dt_basic !== 'undefined') {
                        dt_basic.ajax.reload(null, false); // false = keep current page
                    } else {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    console.error('Error submitting form:', xhr);
                    
                    let errorMessage = '{{ localize("global.error_occurred") }}';
                    
                    // Handle different response types
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            const errorMessages = [];
                            for (const field in errors) {
                                errorMessages.push(errors[field][0]);
                            }
                            errorMessage = errorMessages.join('<br>');
                        }
                    } else if (xhr.responseText) {
                        // Try to parse HTML response for error messages
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(xhr.responseText, 'text/html');
                        const errorElement = doc.querySelector('.alert-danger, .error, [class*="error"]');
                        if (errorElement) {
                            errorMessage = errorElement.textContent.trim();
                        }
                    }
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMessage);
                    } else {
                        alert(errorMessage);
                    }
                }
            });
        });

        function openChangeDepartmentModal(appointmentId, currentDepartmentId) {
            // Set appointment ID
            $('#change_dept_appointment_id').val(appointmentId);
            
            // Set form action
            $('#changeDepartmentForm').attr('action', '{{ url("appointments/change-department/") }}/' + appointmentId);
            
            // Load departments
            loadDepartmentsForAppointment(currentDepartmentId);
            
            // Show modal
            $('#changeDepartmentModal').modal('show');
        }

        function loadDepartmentsForAppointment(currentDepartmentId) {
            const departmentSelect = $('#department_id');
            
            // Show loading state
            departmentSelect.empty().append('<option value="">{{ localize("global.loading") }}...</option>').prop('disabled', true);
            
            $.ajax({
                url: '{{ route("appointments.get-departments") }}',
                type: 'GET',
                success: function(response) {
                    departmentSelect.empty().append('<option value="">{{ localize("global.select_department") }}</option>');
                    
                    if (response.success && response.departments && response.departments.length > 0) {
                        response.departments.forEach(function(department) {
                            const selected = (currentDepartmentId && department.id == currentDepartmentId) ? 'selected' : '';
                            const option = new Option(department.name, department.id, false, selected);
                            departmentSelect.append(option);
                        });
                        departmentSelect.prop('disabled', false);
                    } else {
                        departmentSelect.append('<option value="">{{ localize("global.no_departments_available") }}</option>');
                        departmentSelect.prop('disabled', true);
                    }
                    
                    // Reinitialize select2
                    departmentSelect.select2({
                        dropdownParent: $('#changeDepartmentModal'),
                        width: '100%'
                    });
                },
                error: function(xhr) {
                    console.error('Error loading departments:', xhr);
                    departmentSelect.empty().append('<option value="">{{ localize("global.error_loading_departments") }}</option>');
                    departmentSelect.prop('disabled', true);
                }
            });
        }

        // Handle change department form submission
        $('#changeDepartmentForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const formData = form.serialize();
            const actionUrl = form.attr('action');
            
            $.ajax({
                url: actionUrl,
                type: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#changeDepartmentModal').modal('hide');
                    // Show success message
                    if (response.message) {
                        toastr.success(response.message);
                    }
                    // Reload datatable
                    if (typeof dt_basic !== 'undefined') {
                        dt_basic.ajax.reload();
                    } else {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessages = [];
                        for (var field in errors) {
                            errorMessages.push(errors[field][0]);
                        }
                        toastr.error(errorMessages.join('<br>'));
                    } else {
                        toastr.error('{{ localize("global.error_occurred") }}');
                    }
                }
            });
        });
    </script>
@endpush
