<!-- Physiotherapy Procedures Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-health me-2 text-info"></i>
                    {{ localize('global.physiotherapy_procedures') }}
                </h5>
                @if ($appointment->is_completed == 0)
                    @can('create-physiotherapy-procedures')
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#createPhysiotherapyProcedureModal{{ $appointment->id }}">
                            <i class="bx bx-plus me-1"></i>
                            {{ localize('global.add') }}
                        </button>
                    @endcan
                @endif
            </div>
            <div class="card-body" id="physio_procedures_table_container">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="physio_procedures_table">
                        <thead class="table-light">
                            <tr>
                                <th>{{ localize('global.number') }}</th>
                                <th>{{ localize('global.physiotherapy_type') }}</th>
                                <th>{{ localize('global.physiotherapist') }}</th>
                                <th>{{ localize('global.type') }}</th>
                                <th>{{ localize('global.duration') }}</th>
                                <th>{{ localize('global.progress') }}</th>
                                <th>{{ localize('global.status') }}</th>
                                <th>{{ localize('global.start_date') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="physio_procedures_tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Physiotherapy Procedure Modal -->
<div class="modal fade" id="createPhysiotherapyProcedureModal{{ $appointment->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-plus me-2"></i>
                    {{ localize('global.add_physiotherapy_procedure') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('physiotherapy-procedures.store') }}" method="POST" class="ajax-physio-form"
                    data-action="store">
                    @csrf
                    <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="physiotherapy_type_id"
                                class="form-label">{{ localize('global.physiotherapy_type') }} <span
                                    class="text-danger">*</span></label>
                            <select class="form-control select2-ajax physio-custom-select2" id="physiotherapy_type_id"
                                name="physiotherapy_type_id"
                                data-placeholder="{{ localize('global.select_physiotherapy_type') }}" required>
                                <option value="">{{ localize('global.select_physiotherapy_type') }}</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="physiotherapist_id" class="form-label">{{ localize('global.physiotherapist') }}
                                <span class="text-danger">*</span></label>
                            <select class="form-control select2-ajax physio-custom-select2" id="physiotherapist_id"
                                name="physiotherapist_id" data-url="/api/select/physiotherapists"
                                data-placeholder="{{ localize('global.select_physiotherapist') }}" required>
                                <option value="">{{ localize('global.select_physiotherapist') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">{{ localize('global.type') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="type" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="duration" class="form-label">{{ localize('global.duration') }}
                                ({{ localize('global.minutes') }}) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="duration" min="1" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="days_count" class="form-label">{{ localize('global.total_sessions') }} <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="days_count" min="1" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">{{ localize('global.start_date') }} <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label">{{ localize('global.end_date') }}</label>
                        <input type="date" class="form-control" name="end_date">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">{{ localize('global.description') }}</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ localize('global.notes') }}</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                            {{ localize('global.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i>
                            {{ localize('global.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Modal for View/Edit -->
<div class="modal fade" id="dynamicPhysiotherapyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dynamicPhysiotherapyModalTitle">
                    <i class="bx bx-edit me-2"></i>
                    {{ localize('global.edit_physiotherapy_procedure') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="dynamicPhysiotherapyModalBody">
                <div class="text-center">
                    <i class="bx bx-loader-alt bx-spin"></i>
                    {{ localize('global.loading') }}...
                </div>
            </div>
        </div>
    </div>
</div>

@push('custom-css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endpush

@push('custom-js')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endpush

@section('scripts')
    @parent
    <script>
        (function () {
            function refreshPhysioTable(html) {
                $('#physio_procedures_table_container').html(html);
            }

            // Global data storage and configuration
            const DATA_CONFIG = {
                physiotherapists: [],
                physiotherapyTypes: [],
                loaded: false,
                endpoints: {
                    physiotherapists: '{{ route('api.select.physiotherapists') }}',
                    physiotherapyTypes: '{{ route('api.select.physiotherapy-types') }}'
                }
            };

            // Optimized data loader with Promise.all
            async function loadSelectData() {
                try {
                    const [physioResp, typesResp] = await Promise.all([
                        fetch(DATA_CONFIG.endpoints.physiotherapists),
                        fetch(DATA_CONFIG.endpoints.physiotherapyTypes)
                    ]);

                    const [physioData, typesData] = await Promise.all([
                        physioResp.json(),
                        typesResp.json()
                    ]);

                    DATA_CONFIG.physiotherapists = physioData.results || physioData || [];
                    DATA_CONFIG.physiotherapyTypes = typesData.results || typesData || [];
                    DATA_CONFIG.loaded = true;
                } catch (error) {
                    DATA_CONFIG.physiotherapists = [];
                    DATA_CONFIG.physiotherapyTypes = [];
                    DATA_CONFIG.loaded = true;
                }
            }

            // Load data on page ready
            $(document).ready(function () {
                loadSelectData();
                
                // Also initialize create modal if it's already open (edge case)
                const $createModal = $('#createPhysiotherapyProcedureModal{{ $appointment->id }}');
                if ($createModal.hasClass('show')) {
                    setTimeout(() => {
                        if (DATA_CONFIG.loaded) {
                            initCreateModalSelect2($createModal);
                        }
                    }, 100);
                }
            });

            /**
             * Initialize Select2 with AJAX for dynamic data loading
             * @param {jQuery} $select - The select element
             * @param {string} url - API endpoint URL
             * @param {string} placeholder - Placeholder text
             * @param {jQuery} dropdownParent - Parent element for dropdown
             */
            // Legacy function - not used anymore, using global data approach
            function initAjaxSelect2($select, url, placeholder, dropdownParent) {
                if (!$select.length) {
                    return;
                }

                // Destroy existing instance to avoid conflicts
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                const select2Config = {
                    placeholder: placeholder || 'Select an option...',
                    allowClear: true,
                    minimumInputLength: 0,
                    dropdownParent: dropdownParent || $select.closest('.modal').find('.modal-body') || $('body'),
                    width: '100%',
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 300,
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        data: function (params) {
                            return {
                                search: params.term || '',
                                page: params.page || 1
                            };
                        },
                        processResults: function (data, params) {
                            let results = [];

                            // Handle different response formats
                            if (data && Array.isArray(data)) {
                                // Legacy format: direct array
                                results = data.map(item => ({
                                    id: item.key || item.id,
                                    text: item.value || item.text || item.name
                                }));
                            } else if (data && data.results && Array.isArray(data.results)) {
                                // New format: { results: [], pagination: {} }
                                results = data.results.map(item => ({
                                    id: item.key || item.id,
                                    text: item.value || item.text || item.name
                                }));
                            } else if (data && data.error) {
                                results = [];
                            }

                            return {
                                results: results,
                                pagination: {
                                    more: (data.pagination && data.pagination.more) || false
                                }
                            };
                        },
                        error: function (xhr, status, error) {
                            // Show user-friendly error
                            if (xhr.status === 401) {
                                // Authentication required
                            } else if (xhr.status === 403) {
                                // Access forbidden
                            } else if (xhr.status === 404) {
                                // API endpoint not found
                            }
                        },
                        cache: true
                    },
                    language: {
                        noResults: function () {
                            return "{{ localize('global.no_results_found') }}";
                        },
                        searching: function () {
                            return "{{ localize('global.searching') }}...";
                        },
                        errorLoading: function () {
                            return "{{ localize('global.error_loading_results') }}";
                        }
                    }
                };

                try {
                    $select.select2(select2Config);
                } catch (e) {
                    // Silent fail
                }
            }

            /**
             * Initialize Select2 elements in a modal using fetch-loaded data
             * @param {jQuery} $modal - The modal element
             */
            const initCreateModalSelect2 = $modal => {
                if (!DATA_CONFIG.loaded) {
                    setTimeout(() => initCreateModalSelect2($modal), 500);
                    return;
                }

                // Find all select elements in the create modal
                $modal.find('select[name="physiotherapy_type_id"], select[name="physiotherapist_id"]').each(function () {
                    const $select = $(this);
                    const fieldName = $select.attr('name');
                    let placeholder = '';

                    // Set appropriate placeholder based on field
                    if (fieldName === 'physiotherapy_type_id') {
                        placeholder = "{{ localize('global.select_physiotherapy_type') }}";
                    } else if (fieldName === 'physiotherapist_id') {
                        placeholder = "{{ localize('global.select_physiotherapist') }}";
                    }

                    // Get the appropriate data based on field name
                    let data = [];
                    if (fieldName === 'physiotherapy_type_id') {
                        data = DATA_CONFIG.physiotherapyTypes;
                    } else if (fieldName === 'physiotherapist_id') {
                        data = DATA_CONFIG.physiotherapists;
                    }

                    // Clear and populate options
                    $select.empty();
                    $select.append(new Option(placeholder, '', false, false));

                    data.forEach(function (item) {
                        const text = item.text || item.value || item.name;
                        const id = item.id || item.key;
                        $select.append(new Option(text, id, false, false));
                    });

                    // Destroy existing Select2 if any
                    if ($select.hasClass('select2-hidden-accessible')) {
                        $select.select2('destroy');
                    }

                    // Initialize regular Select2
                    $select.select2({
                        placeholder: placeholder,
                        allowClear: true,
                        dropdownParent: $modal.find('.modal-body'),
                        width: '100%',
                        language: {
                            noResults: function () {
                                return "{{ localize('global.no_results_found') }}";
                            }
                        }
                    });
                });
            };

            // Initialize create modal selects when shown
            $('#createPhysiotherapyProcedureModal{{ $appointment->id }}').on('shown.bs.modal', function () {
                const $modal = $(this);
                
                // Add a small delay to ensure modal is fully rendered
                setTimeout(() => {
                    if (!DATA_CONFIG.loaded) {
                        setTimeout(() => {
                            initCreateModalSelect2($modal);
                        }, 500);
                        return;
                    }
                    
                    initCreateModalSelect2($modal);
                }, 100);
            });



            // Form submission and CRUD operations
            const SWAL_CONFIG = {
                success: {
                    icon: 'success',
                    customClass: { confirmButton: 'btn btn-success' },
                    buttonsStyling: false,
                    timer: 3000,
                    showConfirmButton: false
                },
                error: {
                    icon: 'error',
                    customClass: { confirmButton: 'btn btn-danger' },
                    buttonsStyling: false
                }
            };

            $(document).on('submit', '.ajax-physio-form', function (e) {
                e.preventDefault();
                const $form = $(this);
                const actionUrl = $form.attr('action');
                const method = ($form.find('input[name="_method"]').val() || $form.attr('method') || 'POST').toUpperCase();
                const formData = $form.serialize();
                const isUpdate = method === 'PUT';

                // Disable form during submission
                const $submitBtn = $form.find('button[type="submit"]');
                $submitBtn.prop('disabled', true)
                    .html(`<i class="bx bx-loader-alt bx-spin"></i> {{ localize('global.saving') }}...`);

                $.ajax({
                    url: actionUrl,
                    type: method,
                    data: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: resp => {
                        // Show success message
                        Swal.fire({
                            ...SWAL_CONFIG.success,
                            title: "{{ localize('global.success') }}",
                            text: isUpdate ? 
                                "{{ localize('global.physiotherapy_procedure_updated_successfully') }}" : 
                                "{{ localize('global.physiotherapy_procedure_created_successfully') }}"
                        });

                        // Reload table and close modal
                        physioTable?.ajax.reload(null, false);
                        $form.closest('.modal')?.modal('hide');
                    },
                    error: xhr => {
                        // Show error message
                        let errorMessage = "{{ localize('global.request_failed') }}";

                        if (xhr.responseJSON?.errors) {
                            errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                            } else if (xhr.responseJSON?.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            ...SWAL_CONFIG.error,
                            title: "{{ localize('global.error') }}",
                            html: errorMessage
                        });
                    },
                    complete: () => {
                        // Re-enable form
                        $submitBtn.prop('disabled', false)
                            .html(`<i class="bx bx-save me-1"></i>${isUpdate ? "{{ localize('global.update') }}" : "{{ localize('global.save') }}"}`);
                    }
                });
            });

            // Delete operation with SweetAlert
            const DELETE_CONFIG = {
                confirm: {
                    title: "{{ localize('global.are_you_sure') }}",
                    text: "{{ localize('global.delete_warning_text') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "{{ localize('global.yes_delete') }}",
                    cancelButtonText: "{{ localize('global.cancel') }}",
                    customClass: {
                        confirmButton: 'btn btn-danger me-3',
                        cancelButton: 'btn btn-label-secondary'
                    },
                    buttonsStyling: false
                },
                loading: {
                    title: "{{ localize('global.deleting') }}...",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                }
            };

            $(document).on('click', '.btn-ajax-physio-delete', function () {
                const url = $(this).data('url');

                Swal.fire(DELETE_CONFIG.confirm).then(result => {
                    if (result.value) {
                        // Show loading
                        Swal.fire(DELETE_CONFIG.loading);

                        $.ajax({
                            url,
                            type: 'POST',
                            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            success: resp => {
                                Swal.fire({
                                    ...SWAL_CONFIG.success,
                                    title: "{{ localize('global.deleted') }}",
                                    text: "{{ localize('global.physiotherapy_procedure_deleted_successfully') }}"
                                });
                                physioTable?.ajax.reload(null, false);
                            },
                            error: xhr => {
                                Swal.fire({
                                    ...SWAL_CONFIG.error,
                                    title: "{{ localize('global.error') }}",
                                    text: xhr.responseJSON?.message || "{{ localize('global.delete_failed') }}"
                                });
                            }
                        });
                    }
                });
            });

            // Utility functions and constants
            const UTILS = {
                statusMap: { 'completed': 'success', 'in_progress': 'warning', 'cancelled': 'danger' },
                labels: {
                    pending: "{{ localize('global.pending') }}",
                    in_progress: "{{ localize('global.in_progress') }}",
                    completed: "{{ localize('global.completed') }}",
                    cancelled: "{{ localize('global.cancelled') }}"
                },
                urls: {
                    base: '{{ url('physiotherapy-procedures') }}',
                    canEdit: {{ $appointment->is_completed == 0 ? 'true' : 'false' }}
                }
            };

            const renderStatusBadge = status => {
                const cls = UTILS.statusMap[status] || 'secondary';
                const label = UTILS.labels[status] || status;
                return `<span class="badge bg-${cls}">${label}</span>`;
            };

            const renderProgress = (counter, total, percentage) => 
                `<div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: ${percentage}%">
                        ${counter}/${total}
                    </div>
                </div>`;

            const renderActions = row => {
                const viewBtn = `<button type="button" class="btn btn-outline-info btn-sm btn-physio-view" data-id="${row.id}" title="{{ localize('global.view') }}"><i class="bx bx-show"></i></button>`;
                let editBtn = '', delBtn = '';
                
                if (UTILS.urls.canEdit) {
                    editBtn = `<button type="button" class="btn btn-outline-primary btn-sm btn-physio-edit" data-id="${row.id}" title="{{ localize('global.edit') }}"><i class="bx bx-edit"></i></button>`;
                    delBtn = `<button type="button" class="btn btn-outline-danger btn-sm btn-ajax-physio-delete" data-url="${UTILS.urls.base}/${row.id}/destroy" title="{{ localize('global.delete') }}"><i class="bx bx-trash"></i></button>`;
                }
                
                return `<div class="btn-group" role="group">${viewBtn}${editBtn}${delBtn}</div>`;
            };

            // DataTable configuration and initialization
            let physioTable;
            
            const initPhysioDataTable = appointmentId => {
                if (physioTable) {
                    physioTable.ajax.reload();
                    return;
                }

                // Wait for DataTables to be loaded
                if (typeof $.fn.DataTable === 'undefined') {
                    setTimeout(() => initPhysioDataTable(appointmentId), 100);
                    return;
                }

                const tableConfig = {
                    processing: true,
                    serverSide: false,
                    searching: false,
                    paging: false,
                    info: false,
                    language: {
                        emptyTable: "{{ localize('global.no_physiotherapy_procedures') }}",
                        processing: "{{ localize('global.loading') }}..."
                    },
                    ajax: {
                        url: '{{ route('physiotherapy-procedures.index') }}',
                        data: d => { d.appointment_id = appointmentId; },
                        dataSrc: 'data'
                    },
                    columns: [
                        { 
                            data: null, 
                            render: (data, type, row, meta) => 
                                `<span class="badge bg-info rounded-pill">${meta.row + 1}</span>` 
                        },
                        { data: 'physiotherapy_type' },
                        { data: 'physiotherapist' },
                        { data: 'type' },
                        { 
                            data: 'duration', 
                            render: d => `${d || ''} {{ localize('global.minutes') }}` 
                        },
                        { 
                            data: null, 
                            render: row => renderProgress(row.progress_counter, row.progress_total, row.progress_percentage) 
                        },
                        { 
                            data: 'status', 
                            render: s => renderStatusBadge(s) 
                        },
                        { data: 'start_date' },
                        { 
                            data: null, 
                            orderable: false, 
                            render: row => renderActions(row) 
                        }
                    ],
                    ordering: false
                };

                physioTable = $('#physio_procedures_table').DataTable(tableConfig);
            };

            // Modal handlers and AJAX operations
            const MODAL_CONFIG = {
                selectors: {
                    title: '#dynamicPhysiotherapyModalTitle',
                    body: '#dynamicPhysiotherapyModalBody',
                    modal: '#dynamicPhysiotherapyModal'
                },
                icons: {
                    edit: '<i class="bx bx-edit me-2"></i>',
                    view: '<i class="bx bx-show me-2"></i>'
                }
            };

            $(document).on('click', '.btn-physio-view, .btn-physio-edit', function () {
                const procedureId = $(this).data('id');
                const isEdit = $(this).hasClass('btn-physio-edit');

                // Update modal title
                const title = isEdit ?
                    `${MODAL_CONFIG.icons.edit}{{ localize('global.edit_physiotherapy_procedure') }}` :
                    `${MODAL_CONFIG.icons.view}{{ localize('global.view_physiotherapy_procedure') }}`;
                
                $(MODAL_CONFIG.selectors.title).html(title);

                // Show loading
                $(MODAL_CONFIG.selectors.body).html(
                    '<div class="text-center"><i class="bx bx-loader-alt bx-spin"></i> {{ localize('global.loading') }}...</div>'
                );
                $(MODAL_CONFIG.selectors.modal).modal('show');

                // Fetch data
                $.ajax({
                    url: `${UTILS.urls.base}/${procedureId}`,
                    type: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: resp => {
                        if (resp?.data) {
                            isEdit ? renderEditForm(resp.data) : renderViewData(resp.data);
                        }
                    },
                    error: () => {
                        $(MODAL_CONFIG.selectors.body).html(
                            '<div class="alert alert-danger">{{ localize('global.error_loading_data') }}</div>'
                        );
                    }
                });
            });

            function renderViewData(data) {
                var percentage = data.days_count > 0 ? (data.counter / data.days_count) * 100 : 0;
                var html = '<div class="row">'
                    + '<div class="col-md-6 mb-3"><label class="form-label fw-bold">{{ localize('global.physiotherapy_type') }}:</label><p class="form-control-plaintext">' + (data.physiotherapy_type_name || 'N/A') + '</p></div>'
                    + '<div class="col-md-6 mb-3"><label class="form-label fw-bold">{{ localize('global.physiotherapist') }}:</label><p class="form-control-plaintext">' + (data.physiotherapist_name || 'N/A') + '</p></div>'
                    + '</div><div class="row">'
                    + '<div class="col-md-6 mb-3"><label class="form-label fw-bold">{{ localize('global.type') }}:</label><p class="form-control-plaintext">' + (data.type || '') + '</p></div>'
                    + '<div class="col-md-6 mb-3"><label class="form-label fw-bold">{{ localize('global.duration') }}:</label><p class="form-control-plaintext">' + (data.duration || '') + ' {{ localize('global.minutes') }}</p></div>'
                    + '</div><div class="row">'
                    + '<div class="col-md-6 mb-3"><label class="form-label fw-bold">{{ localize('global.progress') }}:</label>' + renderProgress(data.counter, data.days_count, percentage.toFixed(1)) + '</div>'
                    + '<div class="col-md-6 mb-3"><label class="form-label fw-bold">{{ localize('global.status') }}:</label><p class="form-control-plaintext">' + renderStatusBadge(data.status) + '</p></div>'
                    + '</div><div class="row">'
                    + '<div class="col-md-6 mb-3"><label class="form-label fw-bold">{{ localize('global.start_date') }}:</label><p class="form-control-plaintext">' + (data.start_date || 'N/A') + '</p></div>'
                    + '<div class="col-md-6 mb-3"><label class="form-label fw-bold">{{ localize('global.end_date') }}:</label><p class="form-control-plaintext">' + (data.end_date || 'N/A') + '</p></div>'
                    + '</div>';

                if (data.description) {
                    html += '<div class="mb-3"><label class="form-label fw-bold">{{ localize('global.description') }}:</label><p class="form-control-plaintext">' + data.description + '</p></div>';
                }
                if (data.notes) {
                    html += '<div class="mb-3"><label class="form-label fw-bold">{{ localize('global.notes') }}:</label><p class="form-control-plaintext">' + data.notes + '</p></div>';
                }

                html += '<div class="row">'
                    + '<div class="col-md-6 mb-3"><label class="form-label fw-bold">{{ localize('global.created_by') }}:</label><p class="form-control-plaintext">' + (data.created_by_name || 'N/A') + '</p></div>'
                    + '<div class="col-md-6 mb-3"><label class="form-label fw-bold">{{ localize('global.created_at') }}:</label><p class="form-control-plaintext">' + (data.created_at || 'N/A') + '</p></div>'
                    + '</div>';

                $('#dynamicPhysiotherapyModalBody').html(html);
            }

            function renderEditForm(data) {
                var html = '<form class="ajax-physio-form" data-action="update" data-id="' + data.id + '" method="POST">'
                    + '<input type="hidden" name="_method" value="PUT">'
                    + '<input type="hidden" name="_token" value="{{ csrf_token() }}">'
                    + '<input type="hidden" name="appointment_id" value="' + data.appointment_id + '">'
                    + '<div class="row">'
                    + '<div class="col-md-6 mb-3">'
                    + '<label class="form-label">{{ localize('global.physiotherapy_type') }} <span class="text-danger">*</span></label>'
                    + '<select class="form-control select2-ajax physio-custom-select2" name="physiotherapy_type_id" data-url="/api/select/physiotherapy-types" data-placeholder="{{ localize('global.select_physiotherapy_type') }}" data-current="' + (data.physiotherapy_type_id || '') + '" required>'
                    + '<option value="">{{ localize('global.select_physiotherapy_type') }}</option>'
                    + (data.physiotherapy_type_id && data.physiotherapy_type_name ? '<option value="' + data.physiotherapy_type_id + '" selected>' + data.physiotherapy_type_name + '</option>' : '')
                    + '</select>'
                    + '</div>'
                    + '<div class="col-md-6 mb-3">'
                    + '<label class="form-label">{{ localize('global.physiotherapist') }} <span class="text-danger">*</span></label>'
                    + '<select class="form-control select2-ajax physio-custom-select2" name="physiotherapist_id" data-url="/api/select/physiotherapists" data-placeholder="{{ localize('global.select_physiotherapist') }}" data-current="' + (data.physiotherapist_id || '') + '" required>'
                    + '<option value="">{{ localize('global.select_physiotherapist') }}</option>'
                    + (data.physiotherapist_id && data.physiotherapist_name ? '<option value="' + data.physiotherapist_id + '" selected>' + data.physiotherapist_name + '</option>' : '')
                    + '</select>'
                    + '</div>'
                    + '</div><div class="row">'
                    + '<div class="col-md-6 mb-3"><label class="form-label">{{ localize('global.type') }} <span class="text-danger">*</span></label><input type="text" class="form-control" name="type" value="' + (data.type || '') + '" required></div>'
                    + '<div class="col-md-6 mb-3"><label class="form-label">{{ localize('global.duration') }} ({{ localize('global.minutes') }}) <span class="text-danger">*</span></label><input type="number" class="form-control" name="duration" value="' + (data.duration || '') + '" min="1" required></div>'
                    + '</div><div class="row">'
                    + '<div class="col-md-6 mb-3"><label class="form-label">{{ localize('global.total_sessions') }} <span class="text-danger">*</span></label><input type="number" class="form-control" name="days_count" value="' + (data.days_count || '') + '" min="1" required></div>'
                    + '<div class="col-md-6 mb-3"><label class="form-label">{{ localize('global.status') }} <span class="text-danger">*</span></label><select class="form-control" name="status" required>'
                    + '<option value="pending"' + (data.status === 'pending' ? ' selected' : '') + '>{{ localize('global.pending') }}</option>'
                    + '<option value="in_progress"' + (data.status === 'in_progress' ? ' selected' : '') + '>{{ localize('global.in_progress') }}</option>'
                    + '<option value="completed"' + (data.status === 'completed' ? ' selected' : '') + '>{{ localize('global.completed') }}</option>'
                    + '<option value="cancelled"' + (data.status === 'cancelled' ? ' selected' : '') + '>{{ localize('global.cancelled') }}</option>'
                    + '</select></div>'
                    + '</div><div class="row">'
                    + '<div class="col-md-6 mb-3"><label class="form-label">{{ localize('global.start_date') }} <span class="text-danger">*</span></label><input type="date" class="form-control" name="start_date" value="' + (data.start_date || '') + '" required></div>'
                    + '<div class="col-md-6 mb-3"><label class="form-label">{{ localize('global.end_date') }}</label><input type="date" class="form-control" name="end_date" value="' + (data.end_date || '') + '"></div>'
                    + '</div>'
                    + '<div class="mb-3"><label class="form-label">{{ localize('global.description') }}</label><textarea class="form-control" name="description" rows="3">' + (data.description || '') + '</textarea></div>'
                    + '<div class="mb-3"><label class="form-label">{{ localize('global.notes') }}</label><textarea class="form-control" name="notes" rows="2">' + (data.notes || '') + '</textarea></div>'
                    + '<div class="d-flex justify-content-end">'
                    + '<button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>'
                    + '<button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>{{ localize('global.update') }}</button>'
                    + '</div></form>';

                $('#dynamicPhysiotherapyModalBody').html(html);

                // Set up the form action URL
                var $form = $('#dynamicPhysiotherapyModalBody form');
                $form.attr('action', '{{ url('physiotherapy-procedures') }}/' + data.id + '/update');

                // Initialize Select2 for the dynamic selects with pre-selected values
                initDynamicSelect2WithValues(data);
            }

            /**
             * Initialize Select2 for dynamic modal (edit form) with pre-loaded data
             * @param {Object} data - The procedure data containing current values
             */
            const initDynamicSelect2WithValues = data => {
                const $modal = $('#dynamicPhysiotherapyModal');

                if (!DATA_CONFIG.loaded) {
                    setTimeout(() => initDynamicSelect2WithValues(data), 500);
                    return;
                }

                // Add a delay to ensure any conflicting Select2 initializations are done
                setTimeout(() => {
                    $modal.find('.select2-ajax.physio-custom-select2').each(function () {
                        const $select = $(this);
                        const placeholder = $select.data('placeholder');
                        const fieldName = $select.attr('name');

                        // Get current selected value before clearing
                        const selectedValue = $select.find('option:selected').val();
                        const selectedText = $select.find('option:selected').text();

                        // Get the appropriate data based on field name
                        let optionsData = [];
                        if (fieldName === 'physiotherapy_type_id') {
                            optionsData = DATA_CONFIG.physiotherapyTypes;
                        } else if (fieldName === 'physiotherapist_id') {
                            optionsData = DATA_CONFIG.physiotherapists;
                        }

                        // Force destroy any existing Select2 instance
                        if ($select.hasClass('select2-hidden-accessible')) {
                            try {
                                $select.select2('destroy');
                            } catch (e) {
                                // Silent fail
                            }
                        }

                        // Remove all Select2 classes
                        $select.removeClass('select2-hidden-accessible select2-ajax');

                        // Clear existing options and rebuild
                        $select.empty();

                        // Add placeholder option
                        $select.append(new Option(placeholder, '', false, false));

                        // Add all options from fetch-loaded data
                        optionsData.forEach(function (item) {
                            const text = item.text || item.value || item.name;
                            const id = item.id || item.key;
                            const isSelected = id == selectedValue;
                            const option = new Option(text, id, isSelected, isSelected);
                            $select.append(option);
                        });

                        // Wait a bit more before initializing Select2
                        setTimeout(() => {
                            // Initialize regular Select2 (not AJAX-based)
                            $select.select2({
                                placeholder: placeholder,
                                allowClear: true,
                                dropdownParent: $modal.find('.modal-body'),
                                width: '100%',
                                language: {
                                    noResults: function () {
                                        return "{{ localize('global.no_results_found') }}";
                                    }
                                }
                            });

                            // Trigger change to update Select2 display
                            $select.trigger('change');
                        }, 100);
                    });
                }, 300); // Wait 300ms for any conflicting initializations
            };



            // Load all Select2 data on page ready
            $(document).ready(function () {
                // Prevent conflicting Select2 initialization from show.blade.php
                $('#dynamicPhysiotherapyModal').on('shown.bs.modal', function () {
                    // This modal is handled by our custom functions
                    return false;
                });

                // Override the generic Select2 initialization for our custom elements
                $(document).on('DOMNodeInserted', function (e) {
                    const $target = $(e.target);
                    if ($target.hasClass('physio-custom-select2') || $target.find('.physio-custom-select2').length > 0) {
                        // Remove any generic Select2 classes that might be added
                        $target.find('.physio-custom-select2').removeClass('select2-hidden-accessible');
                    }
                });
            });





            // Initial load with appointment id
            initPhysioDataTable({{ $appointment->id }});
        })();
    </script>
@endsection