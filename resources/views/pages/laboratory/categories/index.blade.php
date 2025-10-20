@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ localize('global.test_categories_management') }}</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="bx bx-plus me-2"></i>{{ localize('global.add_category') }}
                </button>
            </div>

            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="card-body">
                {{-- Search Bar --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bx bx-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchInput" 
                                   placeholder="{{ localize('global.search_categories') }}" 
                                   value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch" style="display: none;">
                                <i class="bx bx-x"></i>
                            </button>
                        </div>
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

                {{-- Categories Container --}}
                <div id="categoriesContainer">
                    @include('pages.laboratory.categories._categories_list', ['testCategories' => $testCategories])
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create Category Modal --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="createForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ localize('global.add_category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="create_name" class="form-label">{{ localize('global.test_category_name') }}</label>
                        <input type="text" name="name" id="create_name" class="form-control" required>
                        <div class="invalid-feedback"></div>
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

{{-- Edit Category Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ localize('global.edit_category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">{{ localize('global.test_category_name') }}</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                        <div class="invalid-feedback"></div>
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

{{-- Delete Category Modal --}}
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
    .category-card {
        transition: all 0.3s ease;
        border: 1px solid #e3e6f0;
    }
    
    .category-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border-color: #007bff;
    }
    
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
    
    .card-title {
        color: #2c3e50;
        font-weight: 600;
    }
    
    .dropdown-toggle::after {
        display: none;
    }
    
    .pagination .page-link {
        color: #007bff;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
    }
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let currentEditId = null;
    let currentDeleteId = null;
    
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
    
    // Create category
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $('#createSubmitBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: '{{ route("laboratory.categories.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                showToast('success', response.message);
                $('#createModal').modal('hide');
                $('#createForm')[0].reset();
                loadCategories();
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
    
    // Edit category
    $(document).on('click', '.edit-category-btn', function(e) {
        e.preventDefault();
        currentEditId = $(this).data('id');
        
        $.ajax({
            url: '{{ route("laboratory.categories.edit", ":id") }}'.replace(':id', currentEditId),
            method: 'GET',
            success: function(response) {
                $('#edit_name').val(response.category.name);
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
        
        $.ajax({
            url: '{{ route("laboratory.categories.update", ":id") }}'.replace(':id', currentEditId),
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                showToast('success', response.message);
                $('#editModal').modal('hide');
                loadCategories();
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
    
    // Delete category
    $(document).on('click', '.delete-category-btn', function(e) {
        e.preventDefault();
        currentDeleteId = $(this).data('id');
        const categoryName = $(this).data('name');
        
        $('#deleteMessage').text('{{ localize("global.confirm_delete_category") }}: "' + categoryName + '"');
        $('#deleteModal').modal('show');
    });
    
    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $('#deleteSubmitBtn');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: '{{ route("laboratory.categories.destroy", ":id") }}'.replace(':id', currentDeleteId),
            method: 'DELETE',
            success: function(response) {
                showToast('success', response.message);
                $('#deleteModal').modal('hide');
                loadCategories();
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
            loadCategories(url);
        }
    });
    
    // Functions
    function performSearch() {
        const searchTerm = $('#searchInput').val();
        const url = '{{ route("laboratory.categories.index") }}' + (searchTerm ? '?search=' + encodeURIComponent(searchTerm) : '');
        loadCategories(url);
    }
    
    function loadCategories(url = null) {
        if (!url) {
            const searchTerm = $('#searchInput').val();
            url = '{{ route("laboratory.categories.index") }}' + (searchTerm ? '?search=' + encodeURIComponent(searchTerm) : '');
        }
        
        showLoading();
        
        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                $('#categoriesContainer').html(response).addClass('fade-in');
                setTimeout(() => $('#categoriesContainer').removeClass('fade-in'), 300);
            },
            error: function() {
                showToast('error', '{{ localize("global.error_loading_data") }}');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    function showLoading() {
        $('#loadingOverlay').show();
    }
    
    function hideLoading() {
        $('#loadingOverlay').hide();
    }
    
    function showToast(type, message) {
        // Create toast element
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        // Add to container
        if (!$('#toastContainer').length) {
            $('body').append('<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>');
        }
        
        const $toast = $(toastHtml);
        $('#toastContainer').append($toast);
        
        // Show toast
        const toast = new bootstrap.Toast($toast[0]);
        toast.show();
        
        // Remove after hide
        $toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
    
    function showFieldErrors(formSelector, errors) {
        $(formSelector + ' .form-control').removeClass('is-invalid');
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
});
</script>
@endsection
