@php
    $rolePermissionIds = $rolePermissionIds ?? old('permission', []);
@endphp

<div class="col-12 mt-3 mb-3">
    <div class="select-all-container bg-white mb-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h5 class="mb-0 text-dark">{{ localize('global.permissions_list') }}</h5>
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleAllFolders">
                    <i class="bx bx-folder-open me-1"></i>{{ localize('global.expand_all') }}
                </button>
                <div class="form-check mb-0">
                    <input type="checkbox" class="form-check-input bg-info" id="selectAllPermissions">
                    <label class="form-check-label fw-bold text-primary" for="selectAllPermissions">
                        {{ localize('global.select_all') }}
                    </label>
                </div>
            </div>
        </div>
        <div class="input-group">
            <span class="input-group-text">
                <i class="bx bx-search"></i>
            </span>
            <input type="text"
                   id="permissionFilter"
                   class="form-control"
                   placeholder="{{ localize('global.search') }}..."
                   autocomplete="off">
            <button type="button" class="btn btn-outline-secondary" id="clearPermissionFilter">
                <i class="bx bx-x"></i>
            </button>
        </div>
    </div>

    <div id="permission-folders" class="row g-3 align-items-start">
        @foreach ($permissionFolders as $index => $folder)
            @php
                $folderId = 'folder-' . $index;
            @endphp
            <div class="col-md-6 permission-folder-col"
                 data-folder-col
                 data-search="{{ strtolower($folder['title']) }}">
                <div class="permission-folder" data-folder="{{ $folderId }}">
                    <div class="permission-folder-header">
                        <button type="button"
                                class="permission-folder-toggle"
                                data-folder-toggle="{{ $folderId }}"
                                aria-controls="{{ $folderId }}-body"
                                aria-expanded="false">
                            <i class="bx bx-folder folder-icon"></i>
                            <span class="folder-title">{{ $folder['title'] }}</span>
                            <span class="badge bg-label-primary folder-count">{{ $folder['permissions']->count() }}</span>
                            <i class="bx bx-chevron-down chevron"></i>
                        </button>
                        <div class="form-check folder-select-all mb-0">
                            <input type="checkbox"
                                   class="form-check-input folder-select-all-checkbox"
                                   id="select-all-{{ $folderId }}"
                                   data-folder-select-all="{{ $folderId }}">
                            <label class="form-check-label" for="select-all-{{ $folderId }}">
                                {{ localize('global.select_all') }}
                            </label>
                        </div>
                    </div>
                    <div class="permission-folder-body"
                         id="{{ $folderId }}-body"
                         hidden>
                        @foreach ($folder['permissions'] as $permission)
                            @php
                                $permissionLabel = permission_label($permission);
                            @endphp
                            <label class="permission-check-item"
                                   data-permission-item
                                   data-search="{{ strtolower($permissionLabel . ' ' . $permission->name . ' ' . ($permission->name_dr ?? '')) }}">
                                <input type="checkbox"
                                       class="form-check-input permission-checkbox"
                                       name="permission[]"
                                       value="{{ $permission->id }}"
                                       {{ in_array($permission->id, $rolePermissionIds) ? 'checked' : '' }}>
                                <span class="permission-name">{{ $permissionLabel }}</span>
                                <small class="permission-slug text-muted">{{ $permission->name }}</small>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div id="permissionFilterEmpty" class="text-center text-muted py-4" hidden>
        {{ localize('global.no_data_found') }}
    </div>
</div>

@push('custom-css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <style>
        .select-all-container {
            background-color: var(--bs-light, #f8f9fa);
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-radius: 8px;
            padding: 10px 15px;
        }

        .permission-folder {
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-radius: 10px;
            background: #fff;
            overflow: hidden;
        }

        .permission-folder-body[hidden] {
            display: none !important;
        }

        .permission-folder-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 10px 12px;
            background: linear-gradient(180deg, #f8f9ff 0%, #f3f4f8 100%);
            border-bottom: 1px solid transparent;
        }

        .permission-folder.is-open .permission-folder-header {
            border-bottom-color: var(--bs-border-color, #dee2e6);
        }

        .permission-folder-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            border: 0;
            background: transparent;
            padding: 0;
            flex: 1;
            text-align: right;
            min-width: 0;
        }

        .permission-folder-toggle .folder-icon {
            color: #696cff;
            font-size: 1.25rem;
        }

        .permission-folder-toggle .folder-title {
            font-weight: 600;
            color: #566a7f;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .permission-folder-toggle .chevron {
            margin-inline-start: auto;
            transition: transform 0.2s ease;
        }

        .permission-folder.is-open .permission-folder-toggle .chevron {
            transform: rotate(180deg);
        }

        .folder-select-all {
            white-space: nowrap;
        }

        .permission-folder-body {
            padding: 8px 12px 12px;
            max-height: 260px;
            overflow-y: auto;
        }

        .permission-check-item {
            display: grid;
            grid-template-columns: auto 1fr;
            grid-template-rows: auto auto;
            column-gap: 10px;
            row-gap: 0;
            align-items: center;
            padding: 8px 6px;
            border-radius: 6px;
            margin: 0;
            cursor: pointer;
        }

        .permission-check-item:hover {
            background: #f5f5f9;
        }

        .permission-check-item .permission-checkbox {
            grid-row: 1 / span 2;
            margin-top: 0;
        }

        .permission-check-item .permission-name {
            font-size: 0.925rem;
            color: #566a7f;
        }

        .permission-check-item .permission-slug {
            grid-column: 2;
            font-size: 0.75rem;
        }

        #selectAllPermissions,
        .folder-select-all-checkbox {
            transform: scale(1.1);
        }

        #selectAllPermissions:indeterminate,
        .folder-select-all-checkbox:indeterminate {
            background-color: var(--bs-primary, #696cff);
            border-color: var(--bs-primary, #696cff);
        }

        [data-bs-theme="dark"] .permission-folder,
        [data-bs-theme="dark"] .select-all-container {
            background-color: var(--bs-dark, #343a40);
            border-color: var(--bs-border-color-translucent, #495057);
        }

        [data-bs-theme="dark"] .permission-folder-header {
            background: #2b3035;
        }

        [data-bs-theme="dark"] .permission-folder-toggle .folder-title,
        [data-bs-theme="dark"] .permission-check-item .permission-name,
        [data-bs-theme="dark"] .select-all-container h5 {
            color: var(--bs-light, #f8f9fa) !important;
        }

        [data-bs-theme="dark"] .permission-check-item:hover {
            background: #3a4046;
        }
    </style>
@endpush

@push('custom-js')
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script>
        $(document).ready(function() {
            function getFolder(folderId) {
                return $('[data-folder="' + folderId + '"]');
            }

            function updateFolderSelectAll($folder) {
                var $boxes = $folder.find('.permission-checkbox');
                var total = $boxes.length;
                var checked = $boxes.filter(':checked').length;
                var $selectAll = $folder.find('[data-folder-select-all]');

                if (checked === 0) {
                    $selectAll.prop({ indeterminate: false, checked: false });
                } else if (checked === total) {
                    $selectAll.prop({ indeterminate: false, checked: true });
                } else {
                    $selectAll.prop({ indeterminate: true, checked: false });
                }
            }

            function updateGlobalSelectAll() {
                var total = $('#permission-folders .permission-checkbox').length;
                var checked = $('#permission-folders .permission-checkbox:checked').length;

                if (checked === 0) {
                    $('#selectAllPermissions').prop({ indeterminate: false, checked: false });
                } else if (checked === total) {
                    $('#selectAllPermissions').prop({ indeterminate: false, checked: true });
                } else {
                    $('#selectAllPermissions').prop({ indeterminate: true, checked: false });
                }
            }

            function setFolderOpen($folder, open) {
                var folderId = $folder.attr('data-folder');
                var $body = $('#' + folderId + '-body');
                var $toggle = $folder.find('[data-folder-toggle]');
                var $icon = $folder.find('.folder-icon');

                $folder.toggleClass('is-open', open);
                $toggle.attr('aria-expanded', open ? 'true' : 'false');
                $icon.toggleClass('bx-folder-open', open).toggleClass('bx-folder', !open);

                if (open) {
                    $body.prop('hidden', false);
                } else {
                    $body.prop('hidden', true);
                }
            }

            $('#permission-folders').on('click', '[data-folder-toggle]', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var folderId = $(this).attr('data-folder-toggle');
                var $folder = getFolder(folderId);
                setFolderOpen($folder, !$folder.hasClass('is-open'));
            });

            $('#permission-folders').on('change', '[data-folder-select-all]', function() {
                var folderId = $(this).attr('data-folder-select-all');
                var $folder = getFolder(folderId);
                var checked = $(this).is(':checked');
                $folder.find('.permission-checkbox').prop('checked', checked);
                setFolderOpen($folder, true);
                updateGlobalSelectAll();
            });

            $('#permission-folders').on('change', '.permission-checkbox', function() {
                var $folder = $(this).closest('[data-folder]');
                updateFolderSelectAll($folder);
                updateGlobalSelectAll();
            });

            $('#selectAllPermissions').on('change', function() {
                var checked = $(this).is(':checked');
                $('#permission-folders .permission-checkbox').prop('checked', checked);
                $('#permission-folders [data-folder]').each(function() {
                    var $folder = $(this);
                    updateFolderSelectAll($folder);
                    if (checked) {
                        setFolderOpen($folder, true);
                    }
                });
            });

            var allExpanded = false;
            $('#toggleAllFolders').on('click', function() {
                allExpanded = !allExpanded;
                $('#permission-folders [data-folder]').each(function() {
                    setFolderOpen($(this), allExpanded);
                });
                $(this).html(
                    allExpanded
                        ? '<i class="bx bx-folder me-1"></i>{{ localize("global.collapse_all") }}'
                        : '<i class="bx bx-folder-open me-1"></i>{{ localize("global.expand_all") }}'
                );
            });

            function applyPermissionFilter() {
                var term = ($('#permissionFilter').val() || '').toString().trim().toLowerCase();
                var visibleFolders = 0;

                $('#permission-folders [data-folder-col]').each(function() {
                    var $col = $(this);
                    var $folder = $col.find('[data-folder]');
                    var folderTitle = ($col.attr('data-search') || '').toLowerCase();
                    var folderMatch = !term || folderTitle.indexOf(term) !== -1;
                    var visibleItems = 0;

                    $col.find('[data-permission-item]').each(function() {
                        var $item = $(this);
                        var itemText = ($item.attr('data-search') || '').toLowerCase();
                        var itemMatch = !term || folderMatch || itemText.indexOf(term) !== -1;
                        $item.toggle(itemMatch);
                        if (itemMatch) {
                            visibleItems++;
                        }
                    });

                    var showFolder = !term || folderMatch || visibleItems > 0;
                    $col.toggle(showFolder);

                    if (showFolder) {
                        visibleFolders++;
                        if (term) {
                            setFolderOpen($folder, true);
                            if (folderMatch && term) {
                                $col.find('[data-permission-item]').show();
                            }
                        } else {
                            setFolderOpen($folder, false);
                            $col.find('[data-permission-item]').show();
                        }
                    }
                });

                $('#permissionFilterEmpty').prop('hidden', visibleFolders > 0);
            }

            $('#permissionFilter').on('input', applyPermissionFilter);

            $('#clearPermissionFilter').on('click', function() {
                $('#permissionFilter').val('');
                applyPermissionFilter();
                $('#permissionFilter').focus();
            });

            $('#permission-folders [data-folder]').each(function() {
                updateFolderSelectAll($(this));
            });
            updateGlobalSelectAll();
        });
    </script>
@endpush
