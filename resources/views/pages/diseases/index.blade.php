@extends('layouts.master')

@section('content')
@php
    $canCreate = auth()->user()->can('create-diseases');
    $canEdit = auth()->user()->can('edit-diseases');
    $canDelete = auth()->user()->can('delete-diseases');
@endphp
<div class="container-xxl flex-grow-1 container-p-y" id="diseases-app">
    <div id="ajaxToastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;"></div>

    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1"><i class="bx bx-pulse me-2 text-primary"></i>{{ localize('global.diseases') }}</h5>
            <p class="text-muted mb-0 small">{{ localize('global.disease_categories') }} · {{ localize('global.department') }}</p>
        </div>
        @if($canCreate)
        <button type="button" class="btn btn-primary" onclick="DiseasesApp.openDiseaseModal()">
            <i class="bx bx-plus me-1"></i>{{ localize('global.create_disease') }}
        </button>
        @endif
    </div>

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-diseases" type="button">
                {{ localize('global.diseases') }}
                <span class="badge bg-primary ms-1" id="tabDiseasesBadge">0</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-categories" type="button">
                {{ localize('global.disease_categories') }}
                <span class="badge bg-info ms-1" id="tabCategoriesBadge">0</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- Diseases --}}
        <div class="tab-pane fade show active" id="tab-diseases" role="tabpanel">
            <form id="diseaseFilterForm" class="row g-3 align-items-end mb-3">
                <div class="col-md-5">
                    <label class="form-label small">{{ localize('global.search') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" id="filterSearch" class="form-control" placeholder="{{ localize('global.search_by_name') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">{{ localize('global.disease_category') }}</label>
                    <select id="filterCategory" class="form-select">
                        <option value="">{{ localize('global.all') }}</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">{{ localize('global.search') }}</button>
                    <button type="button" class="btn btn-outline-secondary" id="filterResetBtn" title="{{ localize('global.reset') }}">
                        <i class="bx bx-revision"></i>
                    </button>
                </div>
            </form>

            <div class="table-responsive border rounded">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:4rem">#</th>
                            <th>{{ localize('global.name') }}</th>
                            <th>{{ localize('global.disease_category') }}</th>
                            <th>{{ localize('global.department') }}</th>
                            <th>{{ localize('global.description') }}</th>
                            <th class="text-end">{{ localize('global.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="diseasesTableBody">
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3" id="diseasesPaginationWrap">
                <span class="text-muted small" id="diseasesPaginationInfo"></span>
                <nav><ul class="pagination pagination-sm mb-0" id="diseasesPagination"></ul></nav>
            </div>
        </div>

        {{-- Categories --}}
        <div class="tab-pane fade" id="tab-categories" role="tabpanel">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <span class="text-muted small">{{ localize('global.disease_categories') }}</span>
                @if($canCreate)
                <button type="button" class="btn btn-primary btn-sm" onclick="DiseasesApp.openCategoryModal()">
                    <i class="bx bx-plus me-1"></i>{{ localize('global.create_disease_category') }}
                </button>
                @endif
            </div>

            <div class="table-responsive border rounded">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:4rem">#</th>
                            <th>{{ localize('global.name') }}</th>
                            <th>{{ localize('global.diseases') }}</th>
                            <th class="text-end">{{ localize('global.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="categoriesTableBody">
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="spinner-border spinner-border-sm text-info" role="status"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Disease modal --}}
<div class="modal fade" id="diseaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="diseaseModalTitle">{{ localize('global.create_disease') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="diseaseModalCloseBtn"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ localize('global.name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="diseaseName">
                        <div class="invalid-feedback d-block" data-error="name"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ localize('global.disease_category') }}</label>
                        <select class="form-select" id="diseaseCategoryId">
                            <option value="">{{ localize('global.select') }}</option>
                        </select>
                        <div class="invalid-feedback d-block" data-error="disease_category_id"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ localize('global.department') }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="diseaseDepartmentId">
                            <option value="">{{ localize('global.select') }}</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback d-block" data-error="department_id"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ localize('global.description') }}</label>
                        <textarea class="form-control" id="diseaseDescription" rows="3"></textarea>
                        <div class="invalid-feedback d-block" data-error="description"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="diseaseModalCancelBtn">{{ localize('global.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="diseaseSaveBtn" onclick="DiseasesApp.saveDisease()">
                    <span class="spinner-border spinner-border-sm me-1 d-none" id="diseaseSaveSpinner"></span>
                    <span id="diseaseSaveText">{{ localize('global.create') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Category modal --}}
<div class="modal fade" id="diseaseCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="diseaseCategoryModalTitle">{{ localize('global.create_disease_category') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="diseaseCategoryModalCloseBtn"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">{{ localize('global.name') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="diseaseCategoryName">
                <div class="invalid-feedback d-block" data-error="name"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="diseaseCategoryModalCancelBtn">{{ localize('global.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="diseaseCategorySaveBtn" onclick="DiseasesApp.saveCategory()">
                    <span class="spinner-border spinner-border-sm me-1 d-none" id="diseaseCategorySaveSpinner"></span>
                    <span id="diseaseCategorySaveText">{{ localize('global.create') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const DiseasesApp = (function () {
    const cfg = {
        csrf: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        canCreate: @json($canCreate),
        canEdit: @json($canEdit),
        canDelete: @json($canDelete),
        labels: {
            createDisease: @json(localize('global.create_disease')),
            editDisease: @json(localize('global.edit_disease')),
            createCategory: @json(localize('global.create_disease_category')),
            editCategory: @json(localize('global.edit_disease_category')),
            create: @json(localize('global.create')),
            update: @json(localize('global.update')),
            edit: @json(localize('global.edit')),
            delete: @json(localize('global.delete')),
            noData: @json(localize('global.no_data_found')),
            confirmDelete: @json(localize('global.confirm_delete')),
            select: @json(localize('global.select')),
            error: @json(localize('global.error')),
        },
    };

    let editingDiseaseId = null;
    let editingCategoryId = null;
    let categories = [];
    let currentPage = 1;
    let filters = { search: '', disease_category_id: '' };

    // Modal initialization moved to DOMContentLoaded to fix close button not working (modals must exist in DOM first)
    let diseaseModal = null;
    let categoryModal = null;
    function initModals() {
        diseaseModal = new bootstrap.Modal(document.getElementById('diseaseModal'));
        categoryModal = new bootstrap.Modal(document.getElementById('diseaseCategoryModal'));

        // Ensure close buttons manually close modals (defensive!)
        document.getElementById('diseaseModalCloseBtn').addEventListener('click', () => diseaseModal.hide());
        document.getElementById('diseaseModalCancelBtn').addEventListener('click', () => diseaseModal.hide());
        document.getElementById('diseaseCategoryModalCloseBtn').addEventListener('click', () => categoryModal.hide());
        document.getElementById('diseaseCategoryModalCancelBtn').addEventListener('click', () => categoryModal.hide());
    }

    function apiHeaders(json = true) {
        const h = {
            'X-CSRF-TOKEN': cfg.csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        };
        if (json) h['Content-Type'] = 'application/json';
        return h;
    }

    function showToast(message, type = 'success') {
        const id = 'toast-' + Date.now();
        const bg = type === 'success' ? 'bg-success' : 'bg-danger';
        document.getElementById('ajaxToastContainer').insertAdjacentHTML('beforeend', `
            <div id="${id}" class="toast align-items-center text-white ${bg} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${escapeHtml(message)}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>`);
        const el = document.getElementById(id);
        const t = new bootstrap.Toast(el, { delay: 3500 });
        t.show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    function escapeHtml(text) {
        const d = document.createElement('div');
        d.textContent = text ?? '';
        return d.innerHTML;
    }

    function clearErrors(prefix) {
        document.querySelectorAll(`#${prefix}Modal [data-error]`).forEach(el => {
            el.textContent = '';
            el.previousElementSibling?.classList.remove('is-invalid');
        });
    }

    function showErrors(prefix, errors) {
        clearErrors(prefix);
        if (!errors) return;
        Object.entries(errors).forEach(([key, msgs]) => {
            const el = document.querySelector(`#${prefix}Modal [data-error="${key}"]`);
            if (el) {
                el.textContent = msgs[0];
                const input = el.previousElementSibling;
                if (input?.classList) input.classList.add('is-invalid');
            }
        });
    }

    async function loadCategories() {
        const tbody = document.getElementById('categoriesTableBody');
        tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm text-info"></div></td></tr>`;

        try {
            const res = await fetch('/api/disease-categories', { headers: apiHeaders(false), credentials: 'same-origin' });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || cfg.labels.error);
            categories = json.data || [];
            renderCategories();
            refreshCategorySelects();
            document.getElementById('tabCategoriesBadge').textContent = categories.length;
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">${escapeHtml(e.message)}</td></tr>`;
        }
    }

    function refreshCategorySelects() {
        const opts = `<option value="">${escapeHtml(cfg.labels.select)}</option>` +
            categories.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
        document.getElementById('filterCategory').innerHTML = `<option value="">${escapeHtml(@json(localize('global.all')))}</option>` +
            categories.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
        document.getElementById('diseaseCategoryId').innerHTML = opts;
        if (filters.disease_category_id) {
            document.getElementById('filterCategory').value = filters.disease_category_id;
        }
    }

    function renderCategories() {
        const tbody = document.getElementById('categoriesTableBody');

        if (!categories.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        ${escapeHtml(cfg.labels.noData)}
                        ${cfg.canCreate ? `<br><button type="button" class="btn btn-primary btn-sm mt-2" onclick="DiseasesApp.openCategoryModal()"><i class="bx bx-plus me-1"></i>${escapeHtml(cfg.labels.createCategory)}</button>` : ''}
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = categories.map((cat, i) => `
            <tr>
                <td class="text-muted">${i + 1}</td>
                <td class="fw-semibold">${escapeHtml(cat.name)}</td>
                <td><span class="badge bg-primary">${cat.diseases_count ?? 0}</span></td>
                <td class="text-end">
                    <div class="btn-group btn-group-sm">
                        ${cfg.canEdit ? `<button type="button" class="btn btn-outline-primary" onclick="DiseasesApp.openCategoryModal(${cat.id})" title="${escapeHtml(cfg.labels.edit)}"><i class="bx bx-edit-alt"></i></button>` : ''}
                        ${cfg.canDelete ? `<button type="button" class="btn btn-outline-danger" onclick="DiseasesApp.deleteCategory(${cat.id})" title="${escapeHtml(cfg.labels.delete)}"><i class="bx bx-trash"></i></button>` : ''}
                    </div>
                </td>
            </tr>`).join('');
    }

    async function loadDiseases(page = 1) {
        currentPage = page;
        const params = new URLSearchParams({ page, ...filters });
        const tbody = document.getElementById('diseasesTableBody');
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>`;

        try {
            const res = await fetch(`/api/diseases?${params}`, { headers: apiHeaders(false), credentials: 'same-origin' });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || cfg.labels.error);

            renderDiseases(json.data, json.meta);
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${escapeHtml(e.message)}</td></tr>`;
        }
    }

    function renderDiseases(rows, meta) {
        const tbody = document.getElementById('diseasesTableBody');
        document.getElementById('tabDiseasesBadge').textContent = meta.total;

        if (!rows.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        ${escapeHtml(cfg.labels.noData)}
                        ${cfg.canCreate ? `<br><button type="button" class="btn btn-primary btn-sm mt-2" onclick="DiseasesApp.openDiseaseModal()"><i class="bx bx-plus me-1"></i>${escapeHtml(cfg.labels.createDisease)}</button>` : ''}
                    </td>
                </tr>`;
            document.getElementById('diseasesPaginationInfo').textContent = '';
            document.getElementById('diseasesPagination').innerHTML = '';
            return;
        }

        const start = meta.from ?? 1;
        tbody.innerHTML = rows.map((d, i) => `
            <tr>
                <td class="text-muted">${start + i}</td>
                <td class="fw-semibold">${escapeHtml(d.name)}</td>
                <td>${d.category_name ? `<span class="badge bg-info">${escapeHtml(d.category_name)}</span>` : '<span class="text-muted">—</span>'}</td>
                <td>${d.department_name ? `<span class="badge bg-secondary">${escapeHtml(d.department_name)}</span>` : '<span class="text-muted">—</span>'}</td>
                <td class="text-truncate" style="max-width:220px" title="${escapeHtml(d.description || '')}">${escapeHtml(d.description || '—')}</td>
                <td class="text-end">
                    <div class="btn-group btn-group-sm">
                        ${cfg.canEdit ? `<button type="button" class="btn btn-outline-primary" onclick="DiseasesApp.openDiseaseModal(${d.id})" title="${escapeHtml(cfg.labels.edit)}"><i class="bx bx-edit-alt"></i></button>` : ''}
                        ${cfg.canDelete ? `<button type="button" class="btn btn-outline-danger" onclick="DiseasesApp.deleteDisease(${d.id})" title="${escapeHtml(cfg.labels.delete)}"><i class="bx bx-trash"></i></button>` : ''}
                    </div>
                </td>
            </tr>`).join('');

        document.getElementById('diseasesPaginationInfo').textContent =
            meta.from && meta.to ? `${meta.from}–${meta.to} / ${meta.total}` : '';

        const pag = document.getElementById('diseasesPagination');
        pag.innerHTML = '';
        if (meta.last_page <= 1) return;

        const addItem = (label, page, disabled = false, active = false) => {
            pag.insertAdjacentHTML('beforeend', `
                <li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${page}">${label}</a>
                </li>`);
        };

        addItem('«', meta.current_page - 1, meta.current_page <= 1);
        for (let p = 1; p <= meta.last_page; p++) {
            if (meta.last_page > 7 && Math.abs(p - meta.current_page) > 2 && p !== 1 && p !== meta.last_page) {
                if (p === 2 || p === meta.last_page - 1) addItem('…', p, true);
                continue;
            }
            addItem(p, p, false, p === meta.current_page);
        }
        addItem('»', meta.current_page + 1, meta.current_page >= meta.last_page);

        pag.querySelectorAll('a[data-page]').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const p = parseInt(a.dataset.page, 10);
                if (!isNaN(p) && p >= 1 && p <= meta.last_page && !a.closest('.disabled')) loadDiseases(p);
            });
        });
    }

    async function openDiseaseModal(id = null) {
        editingDiseaseId = id;
        clearErrors('disease');
        const title = document.getElementById('diseaseModalTitle');
        const saveText = document.getElementById('diseaseSaveText');

        if (id) {
            title.textContent = cfg.labels.editDisease;
            saveText.textContent = cfg.labels.update;
            try {
                const res = await fetch(`/api/diseases/${id}`, { headers: apiHeaders(false), credentials: 'same-origin' });
                const json = await res.json();
                if (res.ok) fillDiseaseForm(json.data);
                else showToast(json.message || cfg.labels.error, 'danger');
            } catch (e) {
                showToast(cfg.labels.error, 'danger');
            }
        } else {
            title.textContent = cfg.labels.createDisease;
            saveText.textContent = cfg.labels.create;
            document.getElementById('diseaseName').value = '';
            document.getElementById('diseaseDescription').value = '';
            document.getElementById('diseaseDepartmentId').value = '';
            document.getElementById('diseaseCategoryId').value = '';
        }
        diseaseModal.show();
    }

    function fillDiseaseForm(d) {
        if (!d) return;
        document.getElementById('diseaseName').value = d.name || '';
        document.getElementById('diseaseDescription').value = d.description || '';
        document.getElementById('diseaseDepartmentId').value = d.department_id || '';
        document.getElementById('diseaseCategoryId').value = d.disease_category_id || '';
    }

    async function saveDisease() {
        const btn = document.getElementById('diseaseSaveBtn');
        const spinner = document.getElementById('diseaseSaveSpinner');
        clearErrors('disease');
        btn.disabled = true;
        spinner.classList.remove('d-none');

        const payload = {
            name: document.getElementById('diseaseName').value.trim(),
            description: document.getElementById('diseaseDescription').value.trim() || null,
            department_id: document.getElementById('diseaseDepartmentId').value,
            disease_category_id: document.getElementById('diseaseCategoryId').value || null,
        };

        try {
            const url = editingDiseaseId ? `/api/diseases/${editingDiseaseId}` : '/api/diseases';
            const method = editingDiseaseId ? 'PUT' : 'POST';
            const res = await fetch(url, { method, headers: apiHeaders(), credentials: 'same-origin', body: JSON.stringify(payload) });
            const json = await res.json();

            if (res.ok) {
                diseaseModal.hide();
                showToast(json.message);
                await loadDiseases(currentPage);
                await loadCategories();
            } else if (json.errors) {
                showErrors('disease', json.errors);
            } else {
                showToast(json.message || cfg.labels.error, 'danger');
            }
        } catch (e) {
            showToast(cfg.labels.error, 'danger');
        } finally {
            btn.disabled = false;
            spinner.classList.add('d-none');
        }
    }

    async function deleteDisease(id) {
        if (!confirm(cfg.labels.confirmDelete)) return;
        const res = await fetch(`/api/diseases/${id}`, { method: 'DELETE', headers: apiHeaders(false), credentials: 'same-origin' });
        const json = await res.json();
        if (res.ok) {
            showToast(json.message);
            await loadDiseases(currentPage);
            await loadCategories();
        } else {
            showToast(json.message || cfg.labels.error, 'danger');
        }
    }

    function openCategoryModal(id = null) {
        editingCategoryId = id;
        clearErrors('diseaseCategory');
        const title = document.getElementById('diseaseCategoryModalTitle');
        const saveText = document.getElementById('diseaseCategorySaveText');

        if (id) {
            const cat = categories.find(c => c.id === id);
            title.textContent = cfg.labels.editCategory;
            saveText.textContent = cfg.labels.update;
            document.getElementById('diseaseCategoryName').value = cat?.name || '';
        } else {
            title.textContent = cfg.labels.createCategory;
            saveText.textContent = cfg.labels.create;
            document.getElementById('diseaseCategoryName').value = '';
        }
        categoryModal.show();
    }

    async function saveCategory() {
        const btn = document.getElementById('diseaseCategorySaveBtn');
        const spinner = document.getElementById('diseaseCategorySaveSpinner');
        clearErrors('diseaseCategory');
        btn.disabled = true;
        spinner.classList.remove('d-none');

        const name = document.getElementById('diseaseCategoryName').value.trim();

        try {
            const url = editingCategoryId ? `/api/disease-categories/${editingCategoryId}` : '/api/disease-categories';
            const method = editingCategoryId ? 'PUT' : 'POST';
            const res = await fetch(url, { method, headers: apiHeaders(), credentials: 'same-origin', body: JSON.stringify({ name }) });
            const json = await res.json();

            if (res.ok) {
                categoryModal.hide();
                showToast(json.message);
                await loadCategories();
                await loadDiseases(currentPage);
            } else if (json.errors) {
                showErrors('diseaseCategory', json.errors);
            } else {
                showToast(json.message || cfg.labels.error, 'danger');
            }
        } catch (e) {
            showToast(cfg.labels.error, 'danger');
        } finally {
            btn.disabled = false;
            spinner.classList.add('d-none');
        }
    }

    async function deleteCategory(id) {
        if (!confirm(cfg.labels.confirmDelete)) return;
        const res = await fetch(`/api/disease-categories/${id}`, { method: 'DELETE', headers: apiHeaders(false), credentials: 'same-origin' });
        const json = await res.json();
        if (res.ok) {
            showToast(json.message);
            await loadCategories();
            await loadDiseases(currentPage);
        } else {
            showToast(json.message || cfg.labels.error, 'danger');
        }
    }

    function init() {
        initModals();

        document.getElementById('diseaseFilterForm').addEventListener('submit', e => {
            e.preventDefault();
            filters.search = document.getElementById('filterSearch').value.trim();
            filters.disease_category_id = document.getElementById('filterCategory').value;
            loadDiseases(1);
        });
        document.getElementById('filterResetBtn').addEventListener('click', () => {
            document.getElementById('filterSearch').value = '';
            document.getElementById('filterCategory').value = '';
            filters = { search: '', disease_category_id: '' };
            loadDiseases(1);
        });

        loadCategories().then(() => loadDiseases(1));
    }

    document.addEventListener('DOMContentLoaded', init);

    return {
        openDiseaseModal,
        openCategoryModal,
        saveDisease,
        saveCategory,
        deleteDisease,
        deleteCategory,
    };
})();
</script>
@endsection
