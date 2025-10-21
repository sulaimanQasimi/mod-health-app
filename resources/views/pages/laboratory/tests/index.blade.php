@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ localize('global.lab_tests_management') }}</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="bx bx-plus me-2"></i>{{ localize('global.add_test') }}
                </button>
            </div>

            @if(Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="card-body">
                {{-- Search and Filter Bar --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bx bx-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchInput" 
                                   placeholder="{{ localize('global.search_tests') }}" 
                                   value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch" style="display: none;">
                                <i class="bx bx-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="categoryFilter">
                            <option value="">{{ localize('global.all_categories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Loading Overlay --}}
                <div id="loadingOverlay" class="loading-overlay" style="display: none;">
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">{{ localize('global.loading') }}...</span>
                        </div>
                    </div>
                </div>

                {{-- Tests Container --}}
                <div id="testsContainer">
                    @include('pages.laboratory.tests._tests_list', ['labTests' => $labTests, 'categories' => $categories])
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create Test Modal --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="createForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ localize('global.add_test') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="create_category_id" class="form-label">{{ localize('global.category') }}</label>
                            <select name="category_id" id="create_category_id" class="form-select" required>
                                <option value="">{{ localize('global.select_category') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="create_name" class="form-label">{{ localize('global.test_name') }}</label>
                            <input type="text" name="name" id="create_name" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label">{{ localize('global.test_parameters') }}</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addParameterBtn">
                                <i class="bx bx-plus"></i> {{ localize('global.add_parameter') }}
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="createParametersTable">
                                <thead class="table-bg-none">
                                    <tr>
                                        <th width="20%">{{ localize('global.parameter_name') }}</th>
                                        <th width="15%">{{ localize('global.unit') }}</th>
                                        <th width="15%">{{ localize('global.normal_range') }}</th>
                                        <th width="12%">Critical Low</th>
                                        <th width="12%">Critical High</th>
                                        <th width="12%">Panic Low</th>
                                        <th width="12%">Panic High</th>
                                        <th width="2%">{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="createParametersBody">
                                    <!-- Dynamic rows will be added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="createSubmitBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        {{ localize('global.save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Test Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="editForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ localize('global.edit_test') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_category_id" class="form-label">{{ localize('global.category') }}</label>
                            <select name="category_id" id="edit_category_id" class="form-select" required>
                                <option value="">{{ localize('global.select_category') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_name" class="form-label">{{ localize('global.test_name') }}</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label">{{ localize('global.test_parameters') }}</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addEditParameterBtn">
                                <i class="bx bx-plus"></i> {{ localize('global.add_parameter') }}
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="editParametersTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="20%">{{ localize('global.parameter_name') }}</th>
                                        <th width="15%">{{ localize('global.unit') }}</th>
                                        <th width="15%">{{ localize('global.normal_range') }}</th>
                                        <th width="12%">Critical Low</th>
                                        <th width="12%">Critical High</th>
                                        <th width="12%">Panic Low</th>
                                        <th width="12%">Panic High</th>
                                        <th width="2%">{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="editParametersBody">
                                    <!-- Dynamic rows will be added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="editSubmitBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        {{ localize('global.update') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- View Parameters Modal --}}
<div class="modal fade" id="viewParametersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ localize('global.test_parameters') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>{{ localize('global.parameter_name') }}</th>
                                <th>{{ localize('global.unit') }}</th>
                                <th>{{ localize('global.normal_range') }}</th>
                                <th>Critical Low</th>
                                <th>Critical High</th>
                                <th>Panic Low</th>
                                <th>Panic High</th>
                            </tr>
                        </thead>
                        <tbody id="viewParametersBody">
                            <!-- Parameters will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.close') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Test Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="deleteForm">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ localize('global.confirm_delete') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                    <button type="submit" class="btn btn-danger" id="deleteSubmitBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        {{ localize('global.delete') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('styles')
<style>
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        z-index: 1000;
        border-radius: 0.375rem;
    }
    
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .table-primary-dark {
        background-color: #7b57ff !important;
        color: white !important;
    }
    
    .table-primary-dark th {
        background-color: #7b57ff !important;
        color: white !important;
        border-color: #6b46c1 !important;
        font-weight: 600;
    }
    
    .parameter-row {
        transition: all 0.3s ease;
    }
    
    .parameter-row.removing {
        opacity: 0.5;
        background-color: #f8d7da;
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .badge {
        font-size: 0.75rem;
    }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let currentEditId = null;
    let currentDeleteId = null;
    let parameterRowIndex = 0;
    let editParameterRowIndex = 0;
    
    // CSRF token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Search functionality with debounce
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch();
        }, 500);
    });
    
    // Category filter
    $('#categoryFilter').on('change', function() {
        performSearch();
    });
    
    // Clear search
    $('#clearSearch').on('click', function() {
        $('#searchInput').val('');
        performSearch();
    });
    
    // Show/hide clear button
    $('#searchInput').on('input', function() {
        if ($(this).val().length > 0) {
            $('#clearSearch').show();
        } else {
            $('#clearSearch').hide();
        }
    });
    
    // Add parameter row for create modal
    $('#addParameterBtn').on('click', function() {
        addParameterRow('create');
    });
    
    // Add parameter row for edit modal
    $('#addEditParameterBtn').on('click', function() {
        addParameterRow('edit');
    });
    
    // Remove parameter row
    $(document).on('click', '.remove-parameter-btn', function() {
        $(this).closest('tr').remove();
    });
    
    // Create test
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $('#createSubmitBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        const formData = collectFormData('create');
        
        $.ajax({
            url: '{{ route("laboratory.tests.store") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                showToast('success', response.message);
                $('#createModal').modal('hide');
                $('#createForm')[0].reset();
                $('#createParametersBody').empty();
                loadTests();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    showFieldErrors('#createForm', errors);
                } else {
                    showToast('error', '{{ localize("global.error_occurred") }}');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });
    
    // Edit test
    $(document).on('click', '.edit-test-btn', function(e) {
        e.preventDefault();
        currentEditId = $(this).data('id');
        
        $.ajax({
            url: '{{ route("laboratory.tests.edit", ":id") }}'.replace(':id', currentEditId),
            method: 'GET',
            success: function(response) {
                populateEditForm(response.labTest);
                $('#editModal').modal('show');
            },
            error: function() {
                showToast('error', '{{ localize("global.error_loading_data") }}');
            }
        });
    });
    
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $('#editSubmitBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        const formData = collectFormData('edit');
        
        $.ajax({
            url: '{{ route("laboratory.tests.update", ":id") }}'.replace(':id', currentEditId),
            method: 'PUT',
            data: formData,
            success: function(response) {
                showToast('success', response.message);
                $('#editModal').modal('hide');
                loadTests();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    showFieldErrors('#editForm', errors);
                } else {
                    showToast('error', '{{ localize("global.error_occurred") }}');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });
    
    // View parameters
    $(document).on('click', '.view-parameters-btn', function(e) {
        e.preventDefault();
        const testId = $(this).data('id');
        const testName = $(this).data('name');
        
        $.ajax({
            url: '{{ route("laboratory.tests.edit", ":id") }}'.replace(':id', testId),
            method: 'GET',
            success: function(response) {
                populateViewParameters(response.labTest);
                $('#viewParametersModal .modal-title').text(testName + ' - {{ localize("global.parameters") }}');
                $('#viewParametersModal').modal('show');
            },
            error: function() {
                showToast('error', '{{ localize("global.error_loading_data") }}');
            }
        });
    });
    
    // Delete test
    $(document).on('click', '.delete-test-btn', function(e) {
        e.preventDefault();
        currentDeleteId = $(this).data('id');
        const testName = $(this).data('name');
        
        $('#deleteMessage').text('{{ localize("global.confirm_delete_test") }}: "' + testName + '"');
        $('#deleteModal').modal('show');
    });
    
    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $('#deleteSubmitBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: '{{ route("laboratory.tests.destroy", ":id") }}'.replace(':id', currentDeleteId),
            method: 'DELETE',
            success: function(response) {
                showToast('success', response.message);
                $('#deleteModal').modal('hide');
                loadTests();
            },
            error: function() {
                showToast('error', '{{ localize("global.error_occurred") }}');
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });
    
    // Pagination
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) {
            loadTests(url);
        }
    });
    
    // Functions
    function performSearch() {
        const searchTerm = $('#searchInput').val();
        const categoryId = $('#categoryFilter').val();
        let url = '{{ route("laboratory.tests.index") }}';
        const params = new URLSearchParams();
        
        if (searchTerm) params.append('search', searchTerm);
        if (categoryId) params.append('category_id', categoryId);
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        loadTests(url);
    }
    
    function loadTests(url = null) {
        if (!url) {
            const searchTerm = $('#searchInput').val();
            const categoryId = $('#categoryFilter').val();
            url = '{{ route("laboratory.tests.index") }}';
            const params = new URLSearchParams();
            
            if (searchTerm) params.append('search', searchTerm);
            if (categoryId) params.append('category_id', categoryId);
            
            if (params.toString()) {
                url += '?' + params.toString();
            }
        }
        
        showLoading();
        
        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                $('#testsContainer').html(response).addClass('fade-in');
                setTimeout(() => $('#testsContainer').removeClass('fade-in'), 300);
            },
            error: function() {
                showToast('error', '{{ localize("global.error_loading_data") }}');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    function addParameterRow(type) {
        const tbody = type === 'create' ? '#createParametersBody' : '#editParametersBody';
        const index = type === 'create' ? parameterRowIndex++ : editParameterRowIndex++;
        
        const row = `
            <tr class="parameter-row">
                <td>
                    <input type="text" name="parameters[${index}][parameter_name]" class="form-control" required>
                </td>
                <td>
                    <input type="text" name="parameters[${index}][unit]" class="form-control">
                </td>
                <td>
                    <input type="text" name="parameters[${index}][normal_range]" class="form-control">
                </td>
                <td>
                    <input type="text" name="parameters[${index}][critical_low]" class="form-control">
                </td>
                <td>
                    <input type="text" name="parameters[${index}][critical_high]" class="form-control">
                </td>
                <td>
                    <input type="text" name="parameters[${index}][panic_low]" class="form-control">
                </td>
                <td>
                    <input type="text" name="parameters[${index}][panic_high]" class="form-control">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-parameter-btn">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $(tbody).append(row);
    }
    
    function collectFormData(type) {
        const form = type === 'create' ? '#createForm' : '#editForm';
        const formData = new FormData($(form)[0]);
        
        // Convert FormData to object
        const data = {};
        for (let [key, value] of formData.entries()) {
            if (data[key]) {
                if (Array.isArray(data[key])) {
                    data[key].push(value);
                } else {
                    data[key] = [data[key], value];
                }
            } else {
                data[key] = value;
            }
        }
        
        return data;
    }
    
    function populateEditForm(labTest) {
        $('#edit_name').val(labTest.name);
        $('#edit_category_id').val(labTest.category_id);
        
        // Clear existing parameters
        $('#editParametersBody').empty();
        
        // Add existing parameters
        labTest.parameters.forEach((parameter, index) => {
            const row = `
                <tr class="parameter-row">
                    <td>
                        <input type="hidden" name="parameters[${index}][id]" value="${parameter.id}">
                        <input type="text" name="parameters[${index}][parameter_name]" class="form-control" value="${parameter.parameter_name}" required>
                    </td>
                    <td>
                        <input type="text" name="parameters[${index}][unit]" class="form-control" value="${parameter.unit || ''}">
                    </td>
                    <td>
                        <input type="text" name="parameters[${index}][normal_range]" class="form-control" value="${parameter.normal_range || ''}">
                    </td>
                    <td>
                        <input type="text" name="parameters[${index}][critical_low]" class="form-control" value="${parameter.critical_low || ''}">
                    </td>
                    <td>
                        <input type="text" name="parameters[${index}][critical_high]" class="form-control" value="${parameter.critical_high || ''}">
                    </td>
                    <td>
                        <input type="text" name="parameters[${index}][panic_low]" class="form-control" value="${parameter.panic_low || ''}">
                    </td>
                    <td>
                        <input type="text" name="parameters[${index}][panic_high]" class="form-control" value="${parameter.panic_high || ''}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-parameter-btn" data-parameter-id="${parameter.id}">
                            <i class="bx bx-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#editParametersBody').append(row);
        });
        
        editParameterRowIndex = labTest.parameters.length;
    }
    
    function populateViewParameters(labTest) {
        const tbody = $('#viewParametersBody');
        tbody.empty();
        
        if (labTest.parameters.length === 0) {
            tbody.append('<tr><td colspan="7" class="text-center text-muted">{{ localize("global.no_parameters_found") }}</td></tr>');
            return;
        }
        
        labTest.parameters.forEach(parameter => {
            const row = `
                <tr>
                    <td><strong>${parameter.parameter_name}</strong></td>
                    <td>${parameter.unit || '-'}</td>
                    <td>${parameter.normal_range || '-'}</td>
                    <td>${parameter.critical_low || '-'}</td>
                    <td>${parameter.critical_high || '-'}</td>
                    <td>${parameter.panic_low || '-'}</td>
                    <td>${parameter.panic_high || '-'}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function showLoading() {
        $('#loadingOverlay').show();
    }
    
    function hideLoading() {
        $('#loadingOverlay').hide();
    }
    
    function showToast(type, message) {
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        if (!$('#toastContainer').length) {
            $('body').append('<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>');
        }
        
        const $toast = $(toastHtml);
        $('#toastContainer').append($toast);
        
        const toast = new bootstrap.Toast($toast[0]);
        toast.show();
        
        $toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
    
    function showFieldErrors(formSelector, errors) {
        $(formSelector + ' .form-control, ' + formSelector + ' .form-select').removeClass('is-invalid');
        $(formSelector + ' .invalid-feedback').text('');
        
        $.each(errors, function(field, messages) {
            const input = $(formSelector + ' [name="' + field + '"]');
            input.addClass('is-invalid');
            input.siblings('.invalid-feedback').text(messages[0]);
        });
    }
    
    // Initialize
    if ($('#searchInput').val().length > 0) {
        $('#clearSearch').show();
    }
    
    // Add initial parameter row for create modal
    $('#createModal').on('show.bs.modal', function() {
        $('#createParametersBody').empty();
        parameterRowIndex = 0;
        addParameterRow('create');
    });
});
</script>
@endsection
