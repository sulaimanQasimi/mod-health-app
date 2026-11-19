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
                        <h5 class="mb-0">{{ localize('global.edit') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('roles.update', $role->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.name_en') }}</label>
                                        <input type="text" class="form-control" name="name"
                                               value="{{ $role->name }}">
                                    </div>
                                    @error('name')
                                        <div class="display-error">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.name_dr') }}</label>
                                        <input type="text" name="name_dr" class="form-control"
                                               value="{{ $role->name_dr }}">
                                    </div>
                                    @error('name_dr')
                                        <div class="display-error">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12 mt-3 mb-3">
                                <div class="select-all-container bg-white mb-3">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
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
                                </div>

                                <div id="permission-folders" class="row g-3">
                                    @foreach ($permissionFolders as $index => $folder)
                                        @php
                                            $folderId = 'folder-' . $index;
                                            $folderPermissionIds = $folder['permissions']->pluck('id')->all();
                                            $checkedCount = count(array_intersect($folderPermissionIds, $rolePermissionIds));
                                            $isOpen = $checkedCount > 0;
                                        @endphp
                                        <div class="col-md-6">
                                            <div class="permission-folder {{ $isOpen ? 'is-open' : '' }}" data-folder>
                                                <div class="permission-folder-header">
                                                    <button type="button" class="permission-folder-toggle" data-folder-toggle aria-expanded="{{ $isOpen ? 'true' : 'false' }}">
                                                        <i class="bx bx-folder{{ $isOpen ? '-open' : '' }} folder-icon"></i>
                                                        <span class="folder-title">{{ $folder['title'] }}</span>
                                                        <span class="badge bg-label-primary folder-count">{{ $folder['permissions']->count() }}</span>
                                                        <i class="bx bx-chevron-down chevron"></i>
                                                    </button>
                                                    <div class="form-check folder-select-all mb-0">
                                                        <input type="checkbox"
                                                               class="form-check-input folder-select-all-checkbox"
                                                               id="select-all-{{ $folderId }}"
                                                               data-folder-select-all>
                                                        <label class="form-check-label" for="select-all-{{ $folderId }}">
                                                            {{ localize('global.select_all') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="permission-folder-body" @if(!$isOpen) style="display: none;" @endif>
                                                    @foreach ($folder['permissions'] as $permission)
                                                        <label class="permission-check-item">
                                                            <input type="checkbox"
                                                                   class="form-check-input permission-checkbox"
                                                                   name="permission[]"
                                                                   value="{{ $permission->id }}"
                                                                   {{ in_array($permission->id, $rolePermissionIds) ? 'checked' : '' }}>
                                                            <span class="permission-name">{{ permission_label($permission) }}</span>
                                                            <small class="permission-slug text-muted">{{ $permission->name }}</small>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <a href="{{ route('roles.index') }}"><button type="button"
                                        class="btn btn-danger">{{ localize('global.back') }}</button>
                                <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

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
            height: 100%;
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
                var total = $('.permission-checkbox').length;
                var checked = $('.permission-checkbox:checked').length;

                if (checked === 0) {
                    $('#selectAllPermissions').prop({ indeterminate: false, checked: false });
                } else if (checked === total) {
                    $('#selectAllPermissions').prop({ indeterminate: false, checked: true });
                } else {
                    $('#selectAllPermissions').prop({ indeterminate: true, checked: false });
                }
            }

            function setFolderOpen($folder, open) {
                $folder.toggleClass('is-open', open);
                $folder.find('[data-folder-toggle]').attr('aria-expanded', open ? 'true' : 'false');
                $folder.find('.folder-icon').toggleClass('bx-folder-open', open).toggleClass('bx-folder', !open);
                $folder.find('.permission-folder-body').toggle(open);
            }

            $('[data-folder-toggle]').on('click', function() {
                var $folder = $(this).closest('[data-folder]');
                setFolderOpen($folder, !$folder.hasClass('is-open'));
            });

            $('[data-folder-select-all]').on('change', function() {
                var $folder = $(this).closest('[data-folder]');
                var checked = $(this).is(':checked');
                $folder.find('.permission-checkbox').prop('checked', checked);
                setFolderOpen($folder, true);
                updateGlobalSelectAll();
            });

            $('.permission-checkbox').on('change', function() {
                var $folder = $(this).closest('[data-folder]');
                updateFolderSelectAll($folder);
                updateGlobalSelectAll();
            });

            $('#selectAllPermissions').on('change', function() {
                var checked = $(this).is(':checked');
                $('.permission-checkbox').prop('checked', checked);
                $('[data-folder]').each(function() {
                    updateFolderSelectAll($(this));
                    if (checked) {
                        setFolderOpen($(this), true);
                    }
                });
            });

            var allExpanded = false;
            $('#toggleAllFolders').on('click', function() {
                allExpanded = !allExpanded;
                $('[data-folder]').each(function() {
                    setFolderOpen($(this), allExpanded);
                });
                $(this).html(
                    allExpanded
                        ? '<i class="bx bx-folder me-1"></i>{{ localize("global.collapse_all") }}'
                        : '<i class="bx bx-folder-open me-1"></i>{{ localize("global.expand_all") }}'
                );
            });

            $('[data-folder]').each(function() {
                updateFolderSelectAll($(this));
            });
            updateGlobalSelectAll();
        });
    </script>
@endpush
