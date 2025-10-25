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
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                            <i class="bx bx-plus me-sm-1"></i>
                            <span class="d-none d-sm-inline-block">{{ localize('global.add_test') }}</span>
                        </button>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('lab_types.tests') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">{{ localize('global.search') }}</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="{{ localize('global.search_placeholder') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="lab_type_section_id" class="form-label">{{ localize('global.lab_type_section') }}</label>
                                <select class="form-select" id="lab_type_section_id" name="lab_type_section_id">
                                    <option value="">{{ localize('global.all_sections') }}</option>
                                    @foreach($labTypeSections as $section)
                                        <option value="{{ $section->id }}" {{ request('lab_type_section_id') == $section->id ? 'selected' : '' }}>
                                            {{ $section->section }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="lab_type_id" class="form-label">{{ localize('global.lab_type') }}</label>
                                <select class="form-select" id="lab_type_id" name="lab_type_id">
                                    <option value="">{{ localize('global.all_types') }}</option>
                                    @foreach($labTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('lab_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="test_type" class="form-label">{{ localize('global.test_type') }}</label>
                                <select class="form-select" id="test_type" name="test_type">
                                    <option value="">{{ localize('global.all_test_types') }}</option>
                                    <option value="parametered" {{ request('test_type') == 'parametered' ? 'selected' : '' }}>
                                        {{ localize('global.parametered') }}
                                    </option>
                                    <option value="text_based" {{ request('test_type') == 'text_based' ? 'selected' : '' }}>
                                        {{ localize('global.text_based') }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bx bx-search"></i> {{ localize('global.search') }}
                                </button>
                                <a href="{{ route('lab_types.tests') }}" class="btn btn-secondary">
                                    <i class="bx bx-refresh"></i> {{ localize('global.clear') }}
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Tests Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ localize('global.test_name') }}</th>
                                    <th>{{ localize('global.lab_type') }}</th>
                                    <th>{{ localize('global.lab_type_section') }}</th>
                                    <th>{{ localize('global.test_type') }}</th>
                                    <th>{{ localize('global.parameters_count') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($labTests as $test)
                                <tr>
                                    <td>{{ $loop->iteration + ($labTests->currentPage() - 1) * $labTests->perPage() }}</td>
                                    <td>{{ $test->name }}</td>
                                    <td>{{ $test->labType->name ?? '—' }}</td>
                                    <td>{{ $test->labTypeSection->section ?? '—' }}</td>
                                    <td>
                                        @if($test->has_parameters)
                                            <span class="badge bg-info">{{ localize('global.parametered') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ localize('global.text_based') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $test->parameters->count() }}</td>
                                    <td>
                                        @can('manage-lab-tests')
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="editTest({{ $test->id }})" title="{{ localize('global.edit') }}">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteTest({{ $test->id }})" title="{{ localize('global.delete') }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                        @endcan
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ localize('global.no_tests_found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($labTests->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $labTests->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Test Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">{{ localize('global.add_test') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="create_lab_type_section_id" class="form-label">{{ localize('global.lab_type_section') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="create_lab_type_section_id" name="lab_type_section_id" required>
                                <option value="">{{ localize('global.select_section') }}</option>
                                @foreach($labTypeSections as $section)
                                    <option value="{{ $section->id }}">{{ $section->section }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="create_lab_type_id" class="form-label">{{ localize('global.lab_type') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="create_lab_type_id" name="lab_type_id" required>
                                <option value="">{{ localize('global.select_type') }}</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="create_name" class="form-label">{{ localize('global.test_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_name" name="name" required>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="create_has_parameters" name="has_parameters" value="1" checked>
                                <label class="form-check-label" for="create_has_parameters">
                                    {{ localize('global.has_parameters') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div id="parametersSection">
                        <hr>
                        <h6>{{ localize('global.test_parameters') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.parameter_name') }} <span class="text-danger">*</span></th>
                                        <th>{{ localize('global.unit') }}</th>
                                        <th>{{ localize('global.normal_range') }}</th>
                                        <th width="100">{{ localize('global.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="parametersTableBody">
                                    <tr>
                                        <td><input type="text" class="form-control form-control-sm" name="parameters[0][parameter_name]" required></td>
                                        <td><input type="text" class="form-control form-control-sm" name="parameters[0][unit]"></td>
                                        <td><input type="text" class="form-control form-control-sm" name="parameters[0][normal_range]"></td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeParameterRow(this)"><i class="bx bx-trash"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addParameterRow()">
                            <i class="bx bx-plus"></i> {{ localize('global.add_parameter') }}
                        </button>
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

<!-- Edit Test Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">{{ localize('global.edit_test') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm">
                <input type="hidden" id="edit_test_id" name="test_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_lab_type_section_id" class="form-label">{{ localize('global.lab_type_section') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_lab_type_section_id" name="lab_type_section_id" required>
                                <option value="">{{ localize('global.select_section') }}</option>
                                @foreach($labTypeSections as $section)
                                    <option value="{{ $section->id }}">{{ $section->section }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_lab_type_id" class="form-label">{{ localize('global.lab_type') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_lab_type_id" name="lab_type_id" required>
                                <option value="">{{ localize('global.select_type') }}</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="edit_name" class="form-label">{{ localize('global.test_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_has_parameters" name="has_parameters" value="1">
                                <label class="form-check-label" for="edit_has_parameters">
                                    {{ localize('global.has_parameters') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div id="editParametersSection">
                        <hr>
                        <h6>{{ localize('global.test_parameters') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.parameter_name') }} <span class="text-danger">*</span></th>
                                        <th>{{ localize('global.unit') }}</th>
                                        <th>{{ localize('global.normal_range') }}</th>
                                        <th width="100">{{ localize('global.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="editParametersTableBody">
                                    <!-- Dynamic parameter rows will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addEditParameterRow()">
                            <i class="bx bx-plus"></i> {{ localize('global.add_parameter') }}
                        </button>
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
@endsection

@section('scripts')
<script>
let parameterIndex = 1;

// Toggle parameters section based on checkbox
$('#create_has_parameters, #edit_has_parameters').change(function() {
    const isChecked = $(this).is(':checked');
    const sectionId = $(this).attr('id').includes('create') ? 'parametersSection' : 'editParametersSection';
    
    if(isChecked) {
        $('#' + sectionId).show();
    } else {
        $('#' + sectionId).hide();
    }
});

// Load lab types based on section selection
$('#create_lab_type_section_id, #edit_lab_type_section_id').change(function() {
    const sectionId = $(this).val();
    const isCreate = $(this).attr('id').includes('create');
    const targetSelect = isCreate ? '#create_lab_type_id' : '#edit_lab_type_id';
    
    $(targetSelect).html('<option value="">{{ localize("global.select_type") }}</option>');
    
    if(sectionId) {
        $.get(`/lab-ajax/lab-types/${sectionId}`, function(data) {
            $.each(data, function(index, type) {
                $(targetSelect).append(`<option value="${type.id}">${type.name}</option>`);
            });
        });
    }
});

// Add parameter row for create form
function addParameterRow() {
    const row = `
        <tr>
            <td><input type="text" class="form-control form-control-sm" name="parameters[${parameterIndex}][parameter_name]" required></td>
            <td><input type="text" class="form-control form-control-sm" name="parameters[${parameterIndex}][unit]"></td>
            <td><input type="text" class="form-control form-control-sm" name="parameters[${parameterIndex}][normal_range]"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeParameterRow(this)"><i class="bx bx-trash"></i></button></td>
        </tr>
    `;
    $('#parametersTableBody').append(row);
    parameterIndex++;
}

// Add parameter row for edit form
function addEditParameterRow() {
    const row = `
        <tr>
            <td><input type="text" class="form-control form-control-sm" name="edit_parameters[${parameterIndex}][parameter_name]" required></td>
            <td><input type="text" class="form-control form-control-sm" name="edit_parameters[${parameterIndex}][unit]"></td>
            <td><input type="text" class="form-control form-control-sm" name="edit_parameters[${parameterIndex}][normal_range]"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeParameterRow(this)"><i class="bx bx-trash"></i></button></td>
        </tr>
    `;
    $('#editParametersTableBody').append(row);
    parameterIndex++;
}

// Remove parameter row
function removeParameterRow(button) {
    $(button).closest('tr').remove();
}

// Create form submission
$('#createForm').submit(function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('_token', '{{ csrf_token() }}');
    
    $.ajax({
        url: '{{ route("laboratory.tests.store") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if(response.success) {
                $('#createModal').modal('hide');
                location.reload();
            } else {
                alert(response.message || '{{ localize("global.error_occurred") }}');
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            if(errors) {
                let errorMessage = '';
                Object.values(errors).forEach(error => {
                    errorMessage += error[0] + '\n';
                });
                alert(errorMessage);
            } else {
                alert('{{ localize("global.error_occurred") }}');
            }
        }
    });
});

// Edit test function
function editTest(testId) {
    $.get(`/laboratory/tests/${testId}/edit`, function(data) {
        $('#edit_test_id').val(data.id);
        $('#edit_name').val(data.name);
        $('#edit_lab_type_section_id').val(data.lab_type_section_id);
        $('#edit_has_parameters').prop('checked', data.has_parameters);
        
        // Load lab types for the selected section
        if(data.lab_type_section_id) {
            $.get(`/lab-ajax/lab-types/${data.lab_type_section_id}`, function(types) {
                $('#edit_lab_type_id').html('<option value="">{{ localize("global.select_type") }}</option>');
                $.each(types, function(index, type) {
                    $('#edit_lab_type_id').append(`<option value="${type.id}">${type.name}</option>`);
                });
                $('#edit_lab_type_id').val(data.lab_type_id);
            });
        }
        
        // Load parameters
        $('#editParametersTableBody').empty();
        if(data.parameters && data.parameters.length > 0) {
            $.each(data.parameters, function(index, param) {
                const row = `
                    <tr>
                        <td><input type="text" class="form-control form-control-sm" name="edit_parameters[${index}][parameter_name]" value="${param.parameter_name}" required></td>
                        <td><input type="text" class="form-control form-control-sm" name="edit_parameters[${index}][unit]" value="${param.unit || ''}"></td>
                        <td><input type="text" class="form-control form-control-sm" name="edit_parameters[${index}][normal_range]" value="${param.normal_range || ''}"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeParameterRow(this)"><i class="bx bx-trash"></i></button></td>
                    </tr>
                `;
                $('#editParametersTableBody').append(row);
            });
        }
        
        // Toggle parameters section
        if(data.has_parameters) {
            $('#editParametersSection').show();
        } else {
            $('#editParametersSection').hide();
        }
        
        $('#editModal').modal('show');
    });
}

// Edit form submission
$('#editForm').submit(function(e) {
    e.preventDefault();
    
    const testId = $('#edit_test_id').val();
    const formData = new FormData(this);
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'PUT');
    
    $.ajax({
        url: `/laboratory/tests/${testId}`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if(response.success) {
                $('#editModal').modal('hide');
                location.reload();
            } else {
                alert(response.message || '{{ localize("global.error_occurred") }}');
            }
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            if(errors) {
                let errorMessage = '';
                Object.values(errors).forEach(error => {
                    errorMessage += error[0] + '\n';
                });
                alert(errorMessage);
            } else {
                alert('{{ localize("global.error_occurred") }}');
            }
        }
    });
});

// Delete test function
function deleteTest(testId) {
    if(confirm('{{ localize("global.confirm_delete") }}')) {
        $.ajax({
            url: `/laboratory/tests/${testId}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    alert(response.message || '{{ localize("global.error_occurred") }}');
                }
            },
            error: function() {
                alert('{{ localize("global.error_occurred") }}');
            }
        });
    }
}

// Initialize parameters section visibility
$(document).ready(function() {
    if($('#create_has_parameters').is(':checked')) {
        $('#parametersSection').show();
    } else {
        $('#parametersSection').hide();
    }
});
</script>
@endsection
