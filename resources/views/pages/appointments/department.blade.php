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
                <div class="card-body">
                    <div id="appointments-table-wrapper">
                        @include('pages.appointments.department_table')
                    </div>
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
                <form id="assignDoctorForm" method="POST" action="#" onsubmit="return false;">
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
    <style>
        #appointments-table-wrapper table {
            text-align: right;
        }
        
        #appointments-table-wrapper table thead th {
            text-align: right;
            background-color: #f8f9fa;
        }
        
        #appointments-table-wrapper table tbody td {
            text-align: right;
            vertical-align: middle;
        }
        
        .pagination {
            margin-bottom: 0;
        }
    </style>
@endpush

@push('custom-js')
    <script>
        // Global function to load appointments table via AJAX
        window.loadAppointmentsTable = function(url) {
            if (!url) {
                // Get current page URL with query params to preserve pagination
                url = window.location.href;
                // If no query params, use the base route
                if (!url.includes('?')) {
                    url = '{{ route("appointments.departmentAppointments") }}';
                }
            }
            
            // Show loading state
            $('#appointments-table-wrapper').html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading appointments...</p></div>');
            
            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    // Update table content
                    if (response && response.html) {
                        $('#appointments-table-wrapper').html(response.html);
                        
                        // Reinitialize tooltips
                        $('[data-bs-toggle="tooltip"]').tooltip();
                        
                        // Scroll to top of table smoothly
                        $('html, body').animate({
                            scrollTop: $('#appointments-table-wrapper').offset().top - 100
                        }, 300);
                    } else {
                        console.error('Invalid response format:', response);
                        $('#appointments-table-wrapper').html('<div class="alert alert-danger">{{ localize("global.error_loading_data") }}</div>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading appointments:', xhr);
                    let errorMessage = '{{ localize("global.error_loading_data") }}';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    $('#appointments-table-wrapper').html(
                        '<div class="alert alert-danger">' + errorMessage + '</div>'
                    );
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMessage);
                    }
                }
            });
        };
        
        $(function() {
            // Handle pagination clicks with AJAX
            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                if (url) {
                    loadAppointmentsTable(url);
                }
            });

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

        // Handle form submission - prevent any page reload
        $('#assignDoctorForm').on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const form = $(this);
            const actionUrl = form.attr('action');
            
            // Validate action URL is set
            if (!actionUrl || actionUrl === '#' || actionUrl === '') {
                if (typeof toastr !== 'undefined') {
                    toastr.error('{{ localize("global.error_occurred") }}: Form action not set');
                } else {
                    alert('{{ localize("global.error_occurred") }}: Form action not set');
                }
                return false;
            }
            
            // Validate doctor is selected
            const doctorId = $('#doctor_id').val();
            if (!doctorId) {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('{{ localize("global.please_select_doctor") }}');
                } else {
                    alert('{{ localize("global.please_select_doctor") }}');
                }
                return false;
            }
            
            const formData = form.serialize();
            
            // Submit via AJAX only - no page reload
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
                    
                    // Hide modal first
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
                    
                    // Reload appointments table via AJAX after a short delay to ensure modal is closed
                    setTimeout(function() {
                        // Get current page URL or use default
                        const currentPageUrl = window.location.href.split('?')[0] + (window.location.search || '');
                        loadAppointmentsTable(currentPageUrl);
                    }, 300);
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

        // Handle change department form submission - prevent any page reload
        $('#changeDepartmentForm').on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const form = $(this);
            const formData = form.serialize();
            const actionUrl = form.attr('action');
            
            // Submit via AJAX only - no page reload
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
                    // Reload appointments table via AJAX (no page reload)
                    loadAppointmentsTable();
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

