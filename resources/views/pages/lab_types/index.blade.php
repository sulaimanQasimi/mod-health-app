@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.lab_tests_management') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        @can('manage-lab-tests')
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLabTypeModal">
                            <i class="bx bx-plus me-sm-1"></i>
                            <span class="d-none d-sm-inline-block">{{ localize('global.add_lab_type') }}</span>
                        </button>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createParameterModal">
                            <i class="bx bx-plus me-sm-1"></i>
                            <span class="d-none d-sm-inline-block">{{ localize('global.add_parameter') }}</span>
                        </button>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form id="searchForm" class="mb-4" method="GET" action="{{ route('lab_types.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">{{ localize('global.search') }}</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="{{ localize('global.search_placeholder') }}" 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="section_filter" class="form-label">{{ localize('global.lab_type_section') }}</label>
                                <select class="form-select" id="section_filter" name="section_id">
                                    <option value="">{{ localize('global.all_sections') }}</option>
                                    @foreach($labTypeSections as $section)
                                        <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                                            {{ $section->section }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="branch_filter" class="form-label">{{ localize('global.branch') }}</label>
                                <select class="form-select" id="branch_filter" name="branch_id">
                                    <option value="">{{ localize('global.all_branches') }}</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="category_filter" class="form-label">{{ localize('global.category') }}</label>
                                <select class="form-select" id="category_filter" name="category_id">
                                    <option value="">{{ localize('global.all_categories') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="test_type_filter" class="form-label">{{ localize('global.test_type') }}</label>
                                <select class="form-select" id="test_type_filter" name="test_type">
                                    <option value="">{{ localize('global.all_test_types') }}</option>
                                    <option value="parametered">{{ localize('global.parametered') }}</option>
                                    <option value="text_based">{{ localize('global.text_based') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bx bx-search"></i> {{ localize('global.search') }}
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                                    <i class="bx bx-refresh"></i> {{ localize('global.clear') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Lab Types and Tests Table -->
                    <div class="table-responsive">
                        <table class="table table-striped" id="labTypesTable">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="18%">{{ localize('global.lab_type') }}</th>
                                    <th width="12%">{{ localize('global.section') }}</th>
                                    <th width="12%">{{ localize('global.branch') }}</th>
                                    <th width="12%">{{ localize('global.category') }}</th>
                                    <th width="8%">{{ localize('global.tests_count') }}</th>
                                    <th width="8%">{{ localize('global.parameters_count') }}</th>
                                    <th width="10%">{{ localize('global.created_date') }}</th>
                                    <th width="15%">{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody id="labTypesTableBody">
                                @forelse($labTypes as $index => $labType)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-light rounded">
                                                    <i class="bx bx-test-tube text-primary"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <h6 class="mb-0">{{ $labType->name }}</h6>
                                                    <small class="text-muted">{{ $labType->section->section ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $labType->section->section ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">{{ $labType->branch->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $labType->category->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $labType->directLabTestParameters->count() }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $labType->directLabTestParameters->count() }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ verta($labType->created_at)->format('Y-m-d') }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-info" onclick="viewLabType({{ $labType->id }})" title="{{ localize('global.view') }}">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning" onclick="editLabType({{ $labType->id }})" title="{{ localize('global.edit') }}">
                                                    <i class="bx bx-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteLabType({{ $labType->id }})" title="{{ localize('global.delete') }}">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <div class="py-4">
                                                <i class="bx bx-test-tube text-muted" style="font-size: 2rem;"></i>
                                                <h6 class="mt-2 text-muted">{{ localize('global.no_lab_types_found') }}</h6>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="paginationContainer" class="d-flex justify-content-center mt-4">
                        {{ $labTypes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Lab Type Modal -->
<div class="modal fade" id="createLabTypeModal" tabindex="-1" aria-labelledby="createLabTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createLabTypeModalLabel">{{ localize('global.add_lab_type') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createLabTypeForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="lab_type_name" class="form-label">{{ localize('global.name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="lab_type_name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="lab_type_branch_id" class="form-label">{{ localize('global.branch') }} <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="lab_type_branch_id" name="branch_id" required>
                                <option value="">{{ localize('global.select_branch') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="lab_type_section_id" class="form-label">{{ localize('global.section') }} <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="lab_type_section_id" name="section_id" required>
                                <option value="">{{ localize('global.select_section') }}</option>
                                @foreach($labTypeSections as $section)
                                    <option value="{{ $section->id }}">{{ $section->section }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="lab_type_category_id" class="form-label">{{ localize('global.category') }}</label>
                            <select class="form-select select2" id="lab_type_category_id" name="category_id">
                                <option value="">{{ localize('global.select_category') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ localize('global.create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Parameter Modal -->
<div class="modal fade" id="createParameterModal" tabindex="-1" aria-labelledby="createParameterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createParameterModalLabel">{{ localize('global.add_parameter') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createParameterForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="parameter_lab_type_id" class="form-label">{{ localize('global.lab_type') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="parameter_lab_type_id" name="lab_type_id" required>
                                <option value="">{{ localize('global.select_lab_type') }}</option>
                                @foreach($labTypes as $labType)
                                    <option value="{{ $labType->id }}">{{ $labType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="parameter_name" class="form-label">{{ localize('global.parameter_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="parameter_name" name="parameter_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="parameter_unit" class="form-label">{{ localize('global.unit') }}</label>
                            <input type="text" class="form-control" id="parameter_unit" name="unit">
                        </div>
                        <div class="col-md-6">
                            <label for="parameter_normal_range" class="form-label">{{ localize('global.normal_range') }}</label>
                            <input type="text" class="form-control" id="parameter_normal_range" name="normal_range">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ localize('global.create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Lab Type Modal -->
<div class="modal fade" id="editLabTypeModal" tabindex="-1" aria-labelledby="editLabTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLabTypeModalLabel">{{ localize('global.edit_lab_type') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editLabTypeForm">
                <input type="hidden" id="edit_lab_type_id" name="lab_type_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="edit_lab_type_name" class="form-label">{{ localize('global.name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_lab_type_name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_lab_type_branch_id" class="form-label">{{ localize('global.branch') }} <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="edit_lab_type_branch_id" name="branch_id" required>
                                <option value="">{{ localize('global.select_branch') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_lab_type_section_id" class="form-label">{{ localize('global.section') }} <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="edit_lab_type_section_id" name="section_id" required>
                                <option value="">{{ localize('global.select_section') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_lab_type_category_id" class="form-label">{{ localize('global.category') }}</label>
                            <select class="form-select select2" id="edit_lab_type_category_id" name="category_id">
                                <option value="">{{ localize('global.select_category') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Parameters Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">{{ localize('global.lab_test_parameters') }}</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addParameterRow()">
                                    <i class="fas fa-plus"></i> {{ localize('global.add_parameter') }}
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-sm" id="editParametersTable">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.parameter_name') }} <span class="text-danger">*</span></th>
                                            <th>{{ localize('global.unit') }}</th>
                                            <th>{{ localize('global.normal_range') }}</th>
                                            <th width="80">{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="editParametersTableBody">
                                        <!-- Parameters will be loaded here dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
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

<!-- View Lab Type Details Modal -->
<div class="modal fade" id="viewLabTypeModal" tabindex="-1" aria-labelledby="viewLabTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewLabTypeModalLabel">{{ localize('global.lab_type_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="viewLabTypeContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">{{ localize('global.loading') }}...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.close') }}</button>
                <button type="button" class="btn btn-primary" onclick="editLabTypeFromView()">{{ localize('global.edit') }}</button>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')
<script>
let parameterIndex = 1;
let currentLabTypeId = null;

// Define all functions globally first
window.viewLabType = function(labTypeId) {
    $('#viewLabTypeModal').modal('show');
    
    $.get(`/api/lab-types/${labTypeId}`, function(response) {
        if (response.success) {
            const labType = response.data;
            const content = document.getElementById('viewLabTypeContent');
            
            let html = '<div class="row">' +
                '<div class="col-md-6">' +
                    '<h6>{{ localize("global.basic_information") }}</h6>' +
                    '<table class="table table-sm">' +
                        '<tr><td><strong>{{ localize("global.name") }}:</strong></td><td>' + labType.name + '</td></tr>' +
                        '<tr><td><strong>{{ localize("global.branch") }}:</strong></td><td>' + (labType.branch ? labType.branch.name : '—') + '</td></tr>' +
                        '<tr><td><strong>{{ localize("global.section") }}:</strong></td><td>' + (labType.section ? labType.section.section : '—') + '</td></tr>' +
                        '<tr><td><strong>{{ localize("global.category") }}:</strong></td><td>' + (labType.category ? labType.category.name : '—') + '</td></tr>' +
                        '<tr><td><strong>{{ localize("global.parent") }}:</strong></td><td>' + (labType.parent ? labType.parent.name : '—') + '</td></tr>' +
                    '</table>' +
                '</div>' +
                '<div class="col-md-6">' +
                    '<h6>{{ localize("global.statistics") }}</h6>' +
                    '<table class="table table-sm">' +
                        '<tr><td><strong>{{ localize("global.tests_count") }}:</strong></td><td><span class="badge bg-primary">0</span></td></tr>' +
                        '<tr><td><strong>{{ localize("global.parameters_count") }}:</strong></td><td><span class="badge bg-info">' + (labType.direct_lab_test_parameters ? labType.direct_lab_test_parameters.length : 0) + '</span></td></tr>' +
                    '</table>' +
                '</div>' +
            '</div>';
            
            // Show direct parameters instead of lab_tests
            if (labType.direct_lab_test_parameters && labType.direct_lab_test_parameters.length > 0) {
                html += '<hr>' +
                    '<h6>{{ localize("global.lab_test_parameters") }}</h6>' +
                    '<div class="table-responsive">' +
                        '<table class="table table-sm">' +
                            '<thead>' +
                                '<tr>' +
                                    '<th>{{ localize("global.parameter_name") }}</th>' +
                                    '<th>{{ localize("global.unit") }}</th>' +
                                    '<th>{{ localize("global.normal_range") }}</th>' +
                                '</tr>' +
                            '</thead>' +
                            '<tbody>';
                
                labType.direct_lab_test_parameters.forEach(parameter => {
                    html += '<tr>' +
                        '<td>' + parameter.parameter_name + '</td>' +
                        '<td>' + (parameter.unit || '—') + '</td>' +
                        '<td>' + (parameter.normal_range || '—') + '</td>' +
                    '</tr>';
                });
                
                html += '</tbody>' +
                        '</table>' +
                    '</div>';
            }
            
            content.innerHTML = html;
        }
    });
};

window.editLabType = function(labTypeId) {
    $.get(`/api/lab-types/${labTypeId}`, function(response) {
        if (response.success) {
            const labType = response.data;
            
            // Set basic fields
            $('#edit_lab_type_id').val(labType.id);
            $('#edit_lab_type_name').val(labType.name);
            
            // Populate and initialize Select2 dropdowns
            populateEditDropdowns(labType);
            
            // Load parameters for this lab type
            loadParametersForEdit(labTypeId);
            
            $('#editLabTypeModal').modal('show');
        }
    });
};

// Function to populate edit modal dropdowns with data
function populateEditDropdowns(labType) {
    // Populate branches dropdown
    $.get('/api/select/branches', function(branches) {
        const branchSelect = $('#edit_lab_type_branch_id');
        branchSelect.empty().append('<option value="">{{ localize("global.select_branch") }}</option>');
        branches.forEach(branch => {
            branchSelect.append(`<option value="${branch.id}">${branch.name}</option>`);
        });
        branchSelect.val(labType.branch_id);
        branchSelect.select2({
            placeholder: '{{ localize("global.select_branch") }}',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#editLabTypeModal')
        });
    });
    
    // Populate sections dropdown
    $.get('/api/select/lab-type-sections', function(sections) {
        const sectionSelect = $('#edit_lab_type_section_id');
        sectionSelect.empty().append('<option value="">{{ localize("global.select_section") }}</option>');
        sections.forEach(section => {
            sectionSelect.append(`<option value="${section.id}">${section.section}</option>`);
        });
        sectionSelect.val(labType.section_id);
        sectionSelect.select2({
            placeholder: '{{ localize("global.select_section") }}',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#editLabTypeModal')
        });
    });
    
    // Initialize category dropdown (already populated from server)
    $('#edit_lab_type_category_id').val(labType.category_id);
    $('#edit_lab_type_category_id').select2({
        placeholder: '{{ localize("global.select_category") }}',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#editLabTypeModal')
    });
}

window.deleteLabType = function(labTypeId) {
    if (confirm('{{ localize("global.confirm_delete") }}')) {
        $.ajax({
            url: `/api/lab-types/${labTypeId}`,
            method: 'DELETE',
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload page to show updated data
                    showToast('success', response.message);
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr);
            }
        });
    }
};


// Parameter management functions
window.viewParameter = function(parameterId) {
    console.log('View parameter:', parameterId);
    // Implementation for viewing parameter details
    showToast('info', 'View parameter functionality coming soon');
};

window.editParameter = function(parameterId) {
    console.log('Edit parameter:', parameterId);
    // Implementation for editing parameter
    showToast('info', 'Edit parameter functionality coming soon');
};

window.deleteParameter = function(parameterId) {
    if (confirm('{{ localize("global.confirm_delete") }}')) {
        $.ajax({
            url: `/api/lab-types/parameters/${parameterId}`,
            method: 'DELETE',
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload page to show updated data
                    showToast('success', response.message);
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr);
            }
        });
    }
};

// Utility functions
window.showToast = function(type, message) {
    // Implementation for showing toast notifications
    console.log(`${type}: ${message}`);
    // You can implement actual toast notifications here
    alert(`${type.toUpperCase()}: ${message}`);
};

window.showError = function(message) {
    showToast('error', message);
};

window.showSuccess = function(message) {
    showToast('success', message);
};

window.handleAjaxError = function(xhr) {
    console.error('AJAX Error:', xhr);
    let message = 'An error occurred';
    
    if (xhr.responseJSON && xhr.responseJSON.message) {
        message = xhr.responseJSON.message;
    } else if (xhr.status === 404) {
        message = 'Resource not found';
    } else if (xhr.status === 500) {
        message = 'Server error occurred';
    } else if (xhr.status === 0) {
        message = 'Network error - please check your connection';
    }
    
    showError(message);
};

window.clearFilters = function() {
    window.location.href = '{{ route("lab_types.index") }}';
};

// Parameter management helper functions
window.loadParametersForEdit = function(labTypeId) {
    $.get(`/api/lab-types/${labTypeId}/parameters`, function(response) {
        if (response.success) {
            const tbody = $('#editParametersTableBody');
            tbody.empty();
            
            if (response.data && response.data.length > 0) {
                response.data.forEach((parameter, index) => {
                    addParameterRow(parameter);
                });
            } else {
                // Add one empty row if no parameters exist
                addParameterRow();
            }
        }
    }).fail(function() {
        // Add one empty row if API fails
        addParameterRow();
    });
};

window.addParameterRow = function(parameter = null) {
    const tbody = $('#editParametersTableBody');
    const rowId = 'param_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    
    const parameterId = parameter ? parameter.id : '';
    const parameterName = parameter ? parameter.parameter_name : '';
    const unit = parameter ? parameter.unit : '';
    const normalRange = parameter ? parameter.normal_range : '';
    
    const row = $(`
        <tr data-parameter-id="${parameterId}" data-row-id="${rowId}">
            <td>
                <input type="text" class="form-control form-control-sm" name="parameter_name" 
                       value="${parameterName}" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="unit" 
                       value="${unit}">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="normal_range" 
                       value="${normalRange}">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" 
                        onclick="removeParameterRow('${rowId}')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `);
    
    tbody.append(row);
};

window.removeParameterRow = function(rowId) {
    const row = $(`tr[data-row-id="${rowId}"]`);
    const parameterId = row.data('parameter-id');
    
    if (parameterId) {
        // Mark existing parameter for deletion
        row.addClass('deleted-row');
        row.hide();
    } else {
        // Remove new parameter row
        row.remove();
    }
};

window.validateParameters = function() {
    let isValid = true;
    const errors = [];
    
    $('#editParametersTableBody tr:not(.deleted-row)').each(function() {
        const row = $(this);
        const parameterName = row.find('input[name="parameter_name"]').val().trim();
        
        if (!parameterName) {
            row.addClass('table-danger');
            isValid = false;
            errors.push('Parameter name is required');
        } else {
            row.removeClass('table-danger');
        }
    });
    
    if (!isValid) {
        showError('Please fill in all required parameter names');
    }
    
    return isValid;
};

// Initialize page
$(document).ready(function() {
    console.log('Page loaded, initializing...');
    setupEventListeners();
    
    // Initialize Select2 for existing dropdowns
    initializeSelect2();
    
    // Initialize parameters section visibility
    if ($('#test_has_parameters').is(':checked')) {
        $('#testParametersSection').show();
    } else {
        $('#testParametersSection').hide();
    }
});

// Initialize Select2 for dropdowns
function initializeSelect2() {
    // Initialize Select2 for create modal dropdowns
    $('#create_lab_type_branch_id, #create_lab_type_section_id, #create_lab_type_category_id').select2({
        placeholder: function() {
            return $(this).find('option:first').text();
        },
        allowClear: true,
        width: '100%',
        dropdownParent: $('#createLabTypeModal')
    });
    
    // Initialize Select2 for parameter modal dropdowns
    $('#parameter_lab_type_id').select2({
        placeholder: '{{ localize("global.select_lab_type") }}',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#createParameterModal')
    });
    
    
    // Handle modal events for Select2 cleanup
    $('#editLabTypeModal').on('hidden.bs.modal', function() {
        // Destroy Select2 instances to prevent conflicts
        $('#edit_lab_type_branch_id, #edit_lab_type_section_id, #edit_lab_type_category_id').select2('destroy');
    });
    
    $('#editLabTypeModal').on('shown.bs.modal', function() {
        // Reinitialize Select2 when modal is shown with proper z-index
        $('#edit_lab_type_branch_id, #edit_lab_type_section_id, #edit_lab_type_category_id').select2({
            placeholder: function() {
                return $(this).find('option:first').text();
            },
            allowClear: true,
            width: '100%',
            dropdownParent: $('#editLabTypeModal')
        });
    });
    
    // Also handle create modal
    $('#createLabTypeModal').on('shown.bs.modal', function() {
        // Reinitialize Select2 for create modal with proper z-index
        $('#create_lab_type_branch_id, #create_lab_type_section_id, #create_lab_type_category_id').select2({
            placeholder: function() {
                return $(this).find('option:first').text();
            },
            allowClear: true,
            width: '100%',
            dropdownParent: $('#createLabTypeModal')
        });
    });
}

// Load lab types for parent dropdown

// Unused AJAX functions removed - using server-side rendering instead

// Setup event listeners
function setupEventListeners() {
    // Create lab type form
    $('#createLabTypeForm').on('submit', function(e) {
        e.preventDefault();
        createLabType();
    });

    // Edit lab type form
    $('#editLabTypeForm').on('submit', function(e) {
        e.preventDefault();
        updateLabType();
    });

    // Create parameter form
    $('#createParameterForm').on('submit', function(e) {
        e.preventDefault();
        createParameter();
    });

    // Toggle parameters section
    $('#test_has_parameters').on('change', function() {
        if ($(this).is(':checked')) {
            $('#testParametersSection').show();
        } else {
            $('#testParametersSection').hide();
        }
    });
}

// AJAX functions removed - using server-side rendering instead

// loadLabTypes function removed - using server-side rendering instead

// displayLabTypes function removed - using server-side rendering instead

// Create lab type
window.createLabType = function() {
    const formData = new FormData($('#createLabTypeForm')[0]);
    
    $.ajax({
        url: '/api/lab-types',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#createLabTypeModal').modal('hide');
                $('#createLabTypeForm')[0].reset();
                location.reload(); // Reload page to show updated data
                showToast('success', response.message);
            }
        },
        error: function(xhr) {
            handleAjaxError(xhr);
        }
    });
};

// editLabType function removed - now defined as global window function above

// Update lab type
window.updateLabType = function() {
    const labTypeId = $('#edit_lab_type_id').val();
    const formData = new FormData($('#editLabTypeForm')[0]);
    
    // Collect parameter data
    const parameters = [];
    const deletedParameterIds = [];
    
    $('#editParametersTableBody tr').each(function() {
        const row = $(this);
        const parameterId = row.data('parameter-id');
        const parameterName = row.find('input[name="parameter_name"]').val();
        const unit = row.find('input[name="unit"]').val();
        const normalRange = row.find('input[name="normal_range"]').val();
        const isDeleted = row.hasClass('deleted-row');
        
        if (isDeleted && parameterId) {
            deletedParameterIds.push(parameterId);
        } else if (parameterName.trim()) {
            const parameter = {
                parameter_name: parameterName,
                unit: unit,
                normal_range: normalRange
            };
            if (parameterId) {
                parameter.id = parameterId;
            }
            parameters.push(parameter);
        }
    });
    
    // Add parameter data to form
    formData.append('parameters', JSON.stringify(parameters));
    formData.append('deleted_parameter_ids', JSON.stringify(deletedParameterIds));
    
    $.ajax({
        url: `/api/lab-types/${labTypeId}`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-HTTP-Method-Override': 'PUT'
        },
        success: function(response) {
            if (response.success) {
                $('#editLabTypeModal').modal('hide');
                location.reload(); // Reload page to show updated data
                showToast('success', response.message);
            }
        },
        error: function(xhr) {
            handleAjaxError(xhr);
        }
    });
};

// viewLabType function removed - now defined as global window function above

// Edit lab type from view
window.editLabTypeFromView = function() {
    $('#viewLabTypeModal').modal('hide');
    if (currentLabTypeId) {
        editLabType(currentLabTypeId);
    }
};

// manageParameters function removed - now defined as global window function above

// Create parameter
window.createParameter = function() {
    const formData = new FormData($('#createParameterForm')[0]);
    const labTypeId = $('#parameter_lab_type_id').val();
    
    if (!labTypeId) {
        showError('Please select a lab type');
        return;
    }
    
    $.ajax({
        url: `/api/lab-types/${labTypeId}/parameters`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#createParameterModal').modal('hide');
                $('#createParameterForm')[0].reset();
                location.reload(); // Reload page to show updated data
                showToast('success', response.message);
            }
        },
        error: function(xhr) {
            handleAjaxError(xhr);
        }
    });
};

// deleteLabType function removed - now defined as global window function above

// Functions are now defined globally above

// Load parameters for a specific lab type

// clearFilters function removed - now defined as global window function above

// handleAjaxError function removed - now defined as global window function above

// Utility functions are now defined globally above

// Duplicate document.ready function removed - consolidated above
</script>
@endsection