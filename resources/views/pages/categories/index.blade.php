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
                    <h5 class="mb-0">
                        <i class="bx bx-category me-2"></i>{{ localize('global.categories') }}
                    </h5>
                    <div class="pt-3 pt-md-0 text-end">
                        @can('create-categories')
                        <button class="btn btn-primary btn-lg" onclick="showCreateModal()">
                            <i class="bx bx-plus me-2"></i>{{ localize('global.create_category') }}
                        </button>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search Filter -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('categories.index') }}">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           value="{{ request('search') }}" 
                                           placeholder="{{ localize('global.search_by_name') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="bx bx-search"></i>
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('categories.index') }}" class="btn btn-outline-danger">
                                            <i class="bx bx-x"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    <div id="categories-table">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{localize('global.number')}}</th>
                                    <th>{{localize('global.name')}}</th>
                                    <th>{{localize('global.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $category)
                                    <tr>
                                        <td>{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                                        <td>{{ $category->name }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('edit-categories')
                                                <button class="btn btn-sm btn-outline-primary"
                                                        onclick="editCategory({{ $category->id }}, '{{ $category->name }}')"
                                                        title="{{ localize('global.edit') }}">
                                                    <i class="bx bx-edit"></i>
                                                </button>
                                                @endcan
                                                @can('delete-categories')
                                                <button class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteCategory({{ $category->id }})"
                                                        title="{{ localize('global.delete') }}">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">{{ localize('global.no_categories_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        @if($categories->hasPages())
                            <div class="d-flex justify-content-center">
                                {{ $categories->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">{{ localize('global.create_category') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    <div class="mb-3">
                        <label class="form-label">{{ localize('global.name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="categoryName" name="name" required>
                        <div id="nameError" class="text-danger"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('categoryModal').style.display='none'; document.querySelector('.modal-backdrop')?.remove();">{{ localize('global.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="saveBtn" onclick="saveCategory()">
                    <span id="saveSpinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
                    <span id="saveText">{{ localize('global.create') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let editingCategoryId = null;

function showCreateModal() {
    editingCategoryId = null;
    document.getElementById('modalTitle').textContent = '{{ localize("global.create_category") }}';
    document.getElementById('saveText').textContent = '{{ localize("global.create") }}';
    document.getElementById('categoryName').value = '';
    document.getElementById('nameError').textContent = '';
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

function editCategory(id, name) {
    editingCategoryId = id;
    document.getElementById('modalTitle').textContent = '{{ localize("global.edit_category") }}';
    document.getElementById('saveText').textContent = '{{ localize("global.update") }}';
    document.getElementById('categoryName').value = name;
    document.getElementById('nameError').textContent = '';
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

async function deleteCategory(id) {
    if (confirm('{{ localize("global.are_you_sure") }}')) {
        try {
            const response = await fetch(`/api/categories/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                location.reload();
            } else {
                const data = await response.json();
                alert(data.message || 'Error deleting category');
            }
        } catch (error) {
            console.error('Error deleting category:', error);
            alert('Error deleting category');
        }
    }
}

async function saveCategory() {
    const name = document.getElementById('categoryName').value;
    const saveBtn = document.getElementById('saveBtn');
    const saveSpinner = document.getElementById('saveSpinner');
    const nameError = document.getElementById('nameError');
    
    // Clear previous errors
    nameError.textContent = '';
    
    // Show loading state
    saveBtn.disabled = true;
    saveSpinner.classList.remove('d-none');
    
    try {
        const url = editingCategoryId ? `/api/categories/${editingCategoryId}` : '/api/categories';
        const method = editingCategoryId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ name: name })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            // Close modal and reload page
            const modalElement = document.getElementById('categoryModal');
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
            document.querySelector('.modal-backdrop')?.remove();
            location.reload();
        } else {
            // Show validation errors
            if (data.errors && data.errors.name) {
                nameError.textContent = data.errors.name[0];
            } else {
                alert(data.message || 'Error saving category');
            }
        }
    } catch (error) {
        console.error('Error saving category:', error);
        alert('Error saving category');
    } finally {
        // Hide loading state
        saveBtn.disabled = false;
        saveSpinner.classList.add('d-none');
    }
}
</script>
@endsection
