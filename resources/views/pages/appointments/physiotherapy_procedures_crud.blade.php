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
                            <select class="form-control select2-ajax" id="physiotherapy_type_id"
                                name="physiotherapy_type_id" data-url="/api/select/physiotherapy-types"
                                data-placeholder="{{ localize('global.select_physiotherapy_type') }}" required>
                                <option value="">{{ localize('global.select_physiotherapy_type') }}</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="physiotherapist_id" class="form-label">{{ localize('global.physiotherapist') }}
                                <span class="text-danger">*</span></label>
                            <select class="form-control select2-ajax" id="physiotherapist_id" name="physiotherapist_id"
                                data-url="/api/select/physiotherapists"
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

            /**
             * Initialize Select2 with AJAX for dynamic data loading
             * @param {jQuery} $select - The select element
             * @param {string} url - API endpoint URL
             * @param {string} placeholder - Placeholder text
             * @param {jQuery} dropdownParent - Parent element for dropdown
             */
            function initAjaxSelect2($select, url, placeholder, dropdownParent) {
                if (!$select.length) {
                    console.warn('Select2: Element not found for URL:', url);
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
                            console.log(`Select2 [${url}] Response:`, data);
                            
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
                                console.error(`Select2 [${url}] API Error:`, data.error);
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
                            console.error(`Select2 [${url}] AJAX Error:`, {
                                status: status,
                                error: error,
                                response: xhr.responseText,
                                statusCode: xhr.status
                            });
                            
                            // Show user-friendly error
                            if (xhr.status === 401) {
                                console.error('Authentication required for:', url);
                            } else if (xhr.status === 403) {
                                console.error('Access forbidden for:', url);
                            } else if (xhr.status === 404) {
                                console.error('API endpoint not found:', url);
                            }
                        },
                        cache: true
                    },
                    language: {
                        noResults: function() {
                            return "{{ localize('global.no_results_found') }}";
                        },
                        searching: function() {
                            return "{{ localize('global.searching') }}...";
                        },
                        errorLoading: function() {
                            return "{{ localize('global.error_loading_results') }}";
                        }
                    }
                };

                try {
                    $select.select2(select2Config);
                    console.log(`Select2 initialized successfully for: ${url}`);
                } catch (e) {
                    console.error(`Failed to initialize Select2 for ${url}:`, e);
                }
            }

            /**
             * Initialize Select2 elements in a modal
             * @param {jQuery} $modal - The modal element
             */
            function initModalSelect2($modal) {
                $modal.find('.select2-ajax').each(function() {
                    const $select = $(this);
                    const url = $select.data('url');
                    const placeholder = $select.data('placeholder');
                    
                    if (!url) {
                        console.warn('Select2: Missing data-url attribute for element:', $select);
                        return;
                    }
                    
                    // Wait a bit for modal to fully render
                    setTimeout(() => {
                        initAjaxSelect2($select, url, placeholder, $modal.find('.modal-body'));
                    }, 100);
                });
            }

            // Initialize create modal selects when shown
            $('#createPhysiotherapyProcedureModal{{ $appointment->id }}').on('shown.bs.modal', function () {
                console.log('Initializing Create Modal Select2 elements...');
                initModalSelect2($(this));
            });



                    // AJAX submit (store/update) with SweetAlert
        $(document).on('submit', '.ajax-physio-form', function (e) {
            e.preventDefault();
            var $form = $(this);
            var actionUrl = $form.attr('action');
            var method = ($form.find('input[name="_method"]').val() || $form.attr('method') || 'POST').toUpperCase();
            var formData = $form.serialize();
            var isUpdate = method === 'PUT';
            
            // Disable form during submission
            $form.find('button[type="submit"]').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> {{ localize('global.saving') }}...');

            $.ajax({
                url: actionUrl,
                type: method,
                data: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (resp) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: "{{ localize('global.success') }}",
                        text: isUpdate ? "{{ localize('global.physiotherapy_procedure_updated_successfully') }}" : "{{ localize('global.physiotherapy_procedure_created_successfully') }}",
                        customClass: {
                            confirmButton: 'btn btn-success'
                        },
                        buttonsStyling: false,
                        timer: 3000,
                        showConfirmButton: false
                    });
                    
                    // Reload table and close modal
                    if (physioTable) { physioTable.ajax.reload(null, false); }
                    var $modal = $form.closest('.modal');
                    if ($modal.length) {
                        $modal.modal('hide');
                    }
                },
                error: function (xhr) {
                    // Show error message
                    let errorMessage = "{{ localize('global.request_failed') }}";
                    
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        // Laravel validation errors
                        let errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: "{{ localize('global.error') }}",
                        html: errorMessage,
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                },
                complete: function() {
                    // Re-enable form
                    $form.find('button[type="submit"]').prop('disabled', false).html('<i class="bx bx-save me-1"></i>' + (isUpdate ? "{{ localize('global.update') }}" : "{{ localize('global.save') }}"));
                }
            });
        });

                    // AJAX delete with SweetAlert
        $(document).on('click', '.btn-ajax-physio-delete', function () {
            var url = $(this).data('url');
            
            Swal.fire({
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
            }).then(function (result) {
                if (result.value) {
                    // Show loading
                    Swal.fire({
                        title: "{{ localize('global.deleting') }}...",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        success: function (resp) {
                            Swal.fire({
                                icon: 'success',
                                title: "{{ localize('global.deleted') }}",
                                text: "{{ localize('global.physiotherapy_procedure_deleted_successfully') }}",
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                },
                                buttonsStyling: false
                            });
                            if (physioTable) { physioTable.ajax.reload(null, false); }
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: "{{ localize('global.error') }}",
                                text: xhr.responseJSON?.message || "{{ localize('global.delete_failed') }}",
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                },
                                buttonsStyling: false
                            });
                        }
                    });
                }
            });
        });

            function renderStatusBadge(status) {
                var map = { 'completed': 'success', 'in_progress': 'warning', 'cancelled': 'danger' };
                var cls = map[status] || 'secondary';
                var labelMap = {
                    'pending': "{{ localize('global.pending') }}",
                    'in_progress': "{{ localize('global.in_progress') }}",
                    'completed': "{{ localize('global.completed') }}",
                    'cancelled': "{{ localize('global.cancelled') }}"
                };
                var label = labelMap[status] || status;
                return '<span class="badge bg-' + cls + '">' + label + '</span>';
            }

            function renderProgress(counter, total, percentage) {
                return '<div class="progress" style="height: 20px;">'
                    + '<div class="progress-bar bg-info" role="progressbar" style="width: ' + percentage + '%">'
                    + counter + '/' + total
                    + '</div></div>';
            }

            function renderActions(row) {
                var viewBtn = '<button type="button" class="btn btn-outline-info btn-sm btn-physio-view" data-id="' + row.id + '" title="{{ localize('global.view') }}"><i class="bx bx-show"></i></button>';
                var editBtn = '';
                var delBtn = '';
                @if ($appointment->is_completed == 0)
                    editBtn = '<button type="button" class="btn btn-outline-primary btn-sm btn-physio-edit" data-id="' + row.id + '" title="{{ localize('global.edit') }}"><i class="bx bx-edit"></i></button>';
                    delBtn = '<button type="button" class="btn btn-outline-danger btn-sm btn-ajax-physio-delete" data-url="' + '{{ url('physiotherapy-procedures') }}' + '/' + row.id + '/destroy' + '" title="{{ localize('global.delete') }}"><i class="bx bx-trash"></i></button>';
                @endif
                return '<div class="btn-group" role="group">' + viewBtn + editBtn + delBtn + '</div>';
            }

            var physioTable;
            function initPhysioDataTable(appointmentId) {
                if (physioTable) {
                    physioTable.ajax.reload();
                    return;
                }

                // Wait for DataTables to be loaded
                if (typeof $.fn.DataTable === 'undefined') {
                    setTimeout(function () {
                        initPhysioDataTable(appointmentId);
                    }, 100);
                    return;
                }

                physioTable = $('#physio_procedures_table').DataTable({
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
                        data: function (d) {
                            d.appointment_id = appointmentId;
                        },
                        dataSrc: 'data'
                    },
                    columns: [
                        { data: null, render: function (data, type, row, meta) { return '<span class="badge bg-info rounded-pill">' + (meta.row + 1) + '</span>'; } },
                        { data: 'physiotherapy_type' },
                        { data: 'physiotherapist' },
                        { data: 'type' },
                        { data: 'duration', render: function (d) { return (d || '') + ' {{ localize('global.minutes') }}'; } },
                        { data: null, render: function (row) { return renderProgress(row.progress_counter, row.progress_total, row.progress_percentage); } },
                        { data: 'status', render: function (s) { return renderStatusBadge(s); } },
                        { data: 'start_date' },
                        { data: null, orderable: false, render: function (row) { return renderActions(row); } }
                    ],
                    ordering: false
                });
            }

            // Dynamic modal handlers
            $(document).on('click', '.btn-physio-view, .btn-physio-edit', function () {
                var procedureId = $(this).data('id');
                var isEdit = $(this).hasClass('btn-physio-edit');

                // Update modal title
                var title = isEdit ?
                    '<i class="bx bx-edit me-2"></i>{{ localize('global.edit_physiotherapy_procedure') }}' :
                    '<i class="bx bx-show me-2"></i>{{ localize('global.view_physiotherapy_procedure') }}';
                $('#dynamicPhysiotherapyModalTitle').html(title);

                // Show loading
                $('#dynamicPhysiotherapyModalBody').html('<div class="text-center"><i class="bx bx-loader-alt bx-spin"></i> {{ localize('global.loading') }}...</div>');
                $('#dynamicPhysiotherapyModal').modal('show');

                // Fetch data
                $.ajax({
                    url: '{{ url('physiotherapy-procedures') }}/' + procedureId,
                    type: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (resp) {
                        if (resp && resp.data) {
                            if (isEdit) {
                                renderEditForm(resp.data);
                            } else {
                                renderViewData(resp.data);
                            }
                        }
                    },
                    error: function () {
                        $('#dynamicPhysiotherapyModalBody').html('<div class="alert alert-danger">{{ localize('global.error_loading_data') }}</div>');
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
                console.log('Rendering edit form with data:', data);
                
                var html = '<form class="ajax-physio-form" data-action="update" data-id="' + data.id + '" method="POST">'
                    + '<input type="hidden" name="_method" value="PUT">'
                    + '<input type="hidden" name="_token" value="{{ csrf_token() }}">'
                    + '<input type="hidden" name="appointment_id" value="' + data.appointment_id + '">'
                    + '<div class="row">'
                    + '<div class="col-md-6 mb-3">'
                    + '<label class="form-label">{{ localize('global.physiotherapy_type') }} <span class="text-danger">*</span></label>'
                    + '<select class="form-control select2-ajax" name="physiotherapy_type_id" data-url="/api/select/physiotherapy-types" data-placeholder="{{ localize('global.select_physiotherapy_type') }}" data-current="' + (data.physiotherapy_type_id || '') + '" required>'
                    + '<option value="">{{ localize('global.select_physiotherapy_type') }}</option>'
                    + (data.physiotherapy_type_id && data.physiotherapy_type_name ? '<option value="' + data.physiotherapy_type_id + '" selected>' + data.physiotherapy_type_name + '</option>' : '')
                    + '</select>'
                    + '</div>'
                    + '<div class="col-md-6 mb-3">'
                    + '<label class="form-label">{{ localize('global.physiotherapist') }} <span class="text-danger">*</span></label>'
                    + '<select class="form-control select2-ajax" name="physiotherapist_id" data-url="/api/select/physiotherapists" data-placeholder="{{ localize('global.select_physiotherapist') }}" data-current="' + (data.physiotherapist_id || '') + '" required>'
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
            function initDynamicSelect2WithValues(data) {
                const $modal = $('#dynamicPhysiotherapyModal');
                
                console.log('Initializing dynamic Select2 with values:', data);
                
                // Load all data for both dropdowns first, then initialize Select2
                const promises = [];
                
                $modal.find('.select2-ajax').each(function() {
                    const $select = $(this);
                    const url = $select.data('url');
                    const placeholder = $select.data('placeholder');
                    const fieldName = $select.attr('name');
                    
                    if (!url) {
                        console.warn('Select2: Missing data-url attribute for dynamic element:', $select);
                        return;
                    }
                    
                    console.log(`Loading data for ${fieldName} from: ${url}`);
                    
                    // Create promise to load data
                    const promise = $.ajax({
                        url: url,
                        dataType: 'json',
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        data: { search: '', page: 1 }
                    }).then(function(response) {
                        console.log(`Loaded data for ${fieldName}:`, response);
                        
                        // Process the data
                        let results = [];
                        if (response && Array.isArray(response)) {
                            results = response.map(item => ({
                                id: item.key || item.id,
                                text: item.value || item.text || item.name
                            }));
                        } else if (response && response.results && Array.isArray(response.results)) {
                            results = response.results.map(item => ({
                                id: item.key || item.id,
                                text: item.value || item.text || item.name
                            }));
                        }
                        
                        // Get current selected value
                        const selectedValue = $select.find('option:selected').val();
                        
                        // Clear and rebuild options
                        $select.empty();
                        $select.append(new Option(placeholder, '', false, false));
                        
                        // Add all options
                        results.forEach(function(item) {
                            const isSelected = item.id == selectedValue;
                            $select.append(new Option(item.text, item.id, isSelected, isSelected));
                        });
                        
                        // Initialize regular Select2 (not AJAX)
                        $select.removeClass('select2-ajax'); // Remove AJAX class
                        $select.select2({
                            placeholder: placeholder,
                            allowClear: true,
                            dropdownParent: $modal.find('.modal-body'),
                            width: '100%',
                            language: {
                                noResults: function() {
                                    return "{{ localize('global.no_results_found') }}";
                                }
                            }
                        });
                        
                        console.log(`Initialized Select2 for ${fieldName} with ${results.length} options`);
                        
                        return { field: fieldName, success: true };
                    }).catch(function(xhr, status, error) {
                        console.error(`Failed to load data for ${fieldName}:`, error);
                        
                        // Initialize empty Select2 as fallback
                        $select.select2({
                            placeholder: placeholder,
                            allowClear: true,
                            dropdownParent: $modal.find('.modal-body'),
                            width: '100%'
                        });
                        
                        return { field: fieldName, success: false, error: error };
                    });
                    
                    promises.push(promise);
                });
                
                // Wait for all data to load
                Promise.all(promises).then(function(results) {
                    console.log('All Select2 dropdowns initialized:', results);
                }).catch(function(error) {
                    console.error('Error initializing some Select2 dropdowns:', error);
                });
            }

            /**
             * Initialize Select2 for dynamic modal (edit form) - Legacy function
             */
            function initDynamicSelect2() {
                const $modal = $('#dynamicPhysiotherapyModal');
                
                // Initialize all select2-ajax elements in the modal
                $modal.find('.select2-ajax').each(function() {
                    const $select = $(this);
                    const url = $select.data('url');
                    const placeholder = $select.data('placeholder');
                    const currentValue = $select.data('current');
                    
                    if (!url) {
                        console.warn('Select2: Missing data-url attribute for dynamic element:', $select);
                        return;
                    }
                    
                    initAjaxSelect2($select, url, placeholder, $modal.find('.modal-body'));
                    
                    // Set current value after initialization
                    if (currentValue) {
                        setTimeout(() => {
                            $select.val(currentValue).trigger('change');
                        }, 200);
                    }
                });
            }

            // Debug: Log when page is ready and test API endpoints
            $(document).ready(function() {
                console.log('🚀 Physiotherapy Procedures CRUD initialized');
                
                // Test API endpoints availability
                console.log('🔍 Testing API endpoints...');
                
                // Test physiotherapy types
                $.get('/api/select/physiotherapy-types')
                    .done(function(data) {
                        console.log('✅ Physiotherapy Types API working:', data.length || data.results?.length || 'Unknown count');
                    })
                    .fail(function(xhr) {
                        console.error('❌ Physiotherapy Types API failed:', xhr.status, xhr.responseText);
                        if (xhr.status === 401 || xhr.status === 403) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'API Access Issue',
                                text: 'Physiotherapy Types API is not accessible. Please check your authentication.',
                                customClass: {
                                    confirmButton: 'btn btn-warning'
                                },
                                buttonsStyling: false
                            });
                        }
                    });
                
                // Test physiotherapists
                $.get('/api/select/physiotherapists')
                    .done(function(data) {
                        console.log('✅ Physiotherapists API working:', data.length || data.results?.length || 'Unknown count');
                    })
                    .fail(function(xhr) {
                        console.error('❌ Physiotherapists API failed:', xhr.status, xhr.responseText);
                        if (xhr.status === 401 || xhr.status === 403) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'API Access Issue',
                                text: 'Physiotherapists API is not accessible. Please check your authentication.',
                                customClass: {
                                    confirmButton: 'btn btn-warning'
                                },
                                buttonsStyling: false
                            });
                        }
                    });
            });

            // Initial load with appointment id
            initPhysioDataTable({{ $appointment->id }});
        })();
    </script>
@endsection