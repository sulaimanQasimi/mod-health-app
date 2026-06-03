@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.diseases') }}</h5>
                        @can('create-diseases')
                        <a href="{{ route('diseases.create') }}" class="btn btn-primary">
                            {{ localize('global.create_disease') }}
                        </a>
                        @endcan
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.name') }}</th>
                                    <th>{{ localize('global.disease_category') }}</th>
                                    <th>{{ localize('global.department') }}</th>
                                    <th>{{ localize('global.description') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($diseases as $disease)
                                    <tr>
                                        <td>{{ $loop->iteration + ($diseases->currentPage() - 1) * $diseases->perPage() }}</td>
                                        <td>{{ $disease->name }}</td>
                                        <td>{{ $disease->category?->name ?? '—' }}</td>
                                        <td>{{ $disease->department->name ?? '—' }}</td>
                                        <td dir="ltr">{{ $disease->description }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                @can('edit-diseases')
                                                <a href="{{ route('diseases.edit', $disease) }}" title="{{ localize('global.edit') }}">
                                                    <i class="bx bx-message-edit"></i>
                                                </a>
                                                @endcan
                                                @can('delete-diseases')
                                                <a href="#"
                                                   onclick="event.preventDefault(); if(confirm('{{ localize('global.confirm_delete') }}')) { document.getElementById('delete-form-{{ $disease->id }}').submit(); }"
                                                   title="{{ localize('global.delete') }}">
                                                    <i class="bx bx-trash text-danger"></i>
                                                </a>
                                                <form id="delete-form-{{ $disease->id }}" action="{{ route('diseases.destroy', $disease) }}" method="POST" class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ localize('global.no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        {{ $diseases->links('pagination::bootstrap-5') }}
                    </div>
                </div>

                <div class="card" id="disease-categories">
                    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.disease_categories') }}</h5>
                        @can('create-diseases')
                        <button type="button" class="btn btn-primary btn-sm" onclick="showDiseaseCategoryModal()">
                            <i class="bx bx-plus me-1"></i>{{ localize('global.create_disease_category') }}
                        </button>
                        @endcan
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.name') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody id="disease-categories-table">
                                @forelse ($categories as $category)
                                    <tr data-category-id="{{ $category->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="category-name">{{ $category->name }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('edit-diseases')
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="editDiseaseCategory({{ $category->id }}, @json($category->name))"
                                                        title="{{ localize('global.edit') }}">
                                                    <i class="bx bx-edit"></i>
                                                </button>
                                                @endcan
                                                @can('delete-diseases')
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteDiseaseCategory({{ $category->id }})"
                                                        title="{{ localize('global.delete') }}">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="disease-categories-empty">
                                        <td colspan="3" class="text-center">{{ localize('global.no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="diseaseCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="diseaseCategoryModalTitle">{{ localize('global.create_disease_category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ localize('global.name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="diseaseCategoryName" required>
                        <div id="diseaseCategoryNameError" class="text-danger small mt-1"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="diseaseCategorySaveBtn" onclick="saveDiseaseCategory()">
                        <span id="diseaseCategorySaveSpinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
                        <span id="diseaseCategorySaveText">{{ localize('global.create') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
let editingDiseaseCategoryId = null;
const diseaseCategoryModalEl = document.getElementById('diseaseCategoryModal');
const diseaseCategoryModal = diseaseCategoryModalEl ? new bootstrap.Modal(diseaseCategoryModalEl) : null;

function showDiseaseCategoryModal() {
    editingDiseaseCategoryId = null;
    document.getElementById('diseaseCategoryModalTitle').textContent = @json(localize('global.create_disease_category'));
    document.getElementById('diseaseCategorySaveText').textContent = @json(localize('global.create'));
    document.getElementById('diseaseCategoryName').value = '';
    document.getElementById('diseaseCategoryNameError').textContent = '';
    diseaseCategoryModal?.show();
}

function editDiseaseCategory(id, name) {
    editingDiseaseCategoryId = id;
    document.getElementById('diseaseCategoryModalTitle').textContent = @json(localize('global.edit_disease_category'));
    document.getElementById('diseaseCategorySaveText').textContent = @json(localize('global.update'));
    document.getElementById('diseaseCategoryName').value = name;
    document.getElementById('diseaseCategoryNameError').textContent = '';
    diseaseCategoryModal?.show();
}

async function deleteDiseaseCategory(id) {
    if (!confirm(@json(localize('global.confirm_delete')))) {
        return;
    }

    try {
        const response = await fetch(`/api/disease-categories/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        });

        const data = await response.json();

        if (response.ok) {
            location.reload();
        } else {
            alert(data.message || @json(localize('global.error')));
        }
    } catch (error) {
        console.error(error);
        alert(@json(localize('global.error')));
    }
}

async function saveDiseaseCategory() {
    const name = document.getElementById('diseaseCategoryName').value.trim();
    const nameError = document.getElementById('diseaseCategoryNameError');
    const saveBtn = document.getElementById('diseaseCategorySaveBtn');
    const saveSpinner = document.getElementById('diseaseCategorySaveSpinner');

    nameError.textContent = '';
    saveBtn.disabled = true;
    saveSpinner.classList.remove('d-none');

    try {
        const url = editingDiseaseCategoryId
            ? `/api/disease-categories/${editingDiseaseCategoryId}`
            : '/api/disease-categories';
        const method = editingDiseaseCategoryId ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ name }),
        });

        const data = await response.json();

        if (response.ok) {
            diseaseCategoryModal?.hide();
            location.reload();
        } else if (data.errors?.name) {
            nameError.textContent = data.errors.name[0];
        } else {
            alert(data.message || @json(localize('global.error')));
        }
    } catch (error) {
        console.error(error);
        alert(@json(localize('global.error')));
    } finally {
        saveBtn.disabled = false;
        saveSpinner.classList.add('d-none');
    }
}
</script>
@endsection
