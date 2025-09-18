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
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="select-all-container bg-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0 text-dark">{{ localize('global.permissions_list') }}</h5>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input bg-info" id="selectAllPermissions">
                                                    <label class="form-check-label fw-bold text-primary" for="selectAllPermissions">
                                                        {{ localize('global.select_all') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <ul id="permissions-tree">
                                            @foreach ($permissions as $value)
                                                @include('pages.permissions.sub_permissions', [
                                                    'permission' => $value,
                                                ])
                                            @endforeach
                                        </ul>
                                    </div>
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
        .main-folder-icon:before {
            content: "\eae2";
            font-family: 'boxicons';
            color: #696cff;
        }

        .sub-folder-icon:before {
            content: "\eae0";
            font-family: 'boxicons';
            color: #068a48;
        }

        .permission-item ul {
            display: none;
            list-style-type: none;
        }


        .permission-item.open ul {
            display: block;
        }

        li {
            list-style-type: none;
        }

        .form-check {
            padding-right: 0.7em !important;
        }

        .permission-item {
            position: relative;
        }

        .main-folder-icon.open:before {
            content: "\eae0";
        }

        /* Select All checkbox styling */
        #selectAllPermissions {
            transform: scale(1.2);
            margin-right: 8px;
        }

        #selectAllPermissions:indeterminate {
            background-color: var(--bs-primary, #696cff);
            border-color: var(--bs-primary, #696cff);
        }

        /* Dark mode checkbox styling */
        @media (prefers-color-scheme: dark) {
            #selectAllPermissions {
                background-color: var(--bs-dark, #343a40);
                border-color: var(--bs-border-color, #6c757d);
            }
            
            #selectAllPermissions:checked {
                background-color: var(--bs-primary, #696cff);
                border-color: var(--bs-primary, #696cff);
            }
        }

        [data-bs-theme="dark"] #selectAllPermissions {
            background-color: var(--bs-dark, #343a40);
            border-color: var(--bs-border-color, #6c757d);
        }
        
        [data-bs-theme="dark"] #selectAllPermissions:checked {
            background-color: var(--bs-primary, #696cff);
            border-color: var(--bs-primary, #696cff);
        }

        .select-all-container {
            background-color: var(--bs-light, #f8f9fa);
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        /* Dark mode compatibility */
        @media (prefers-color-scheme: dark) {
            .select-all-container {
                background-color: var(--bs-dark, #343a40);
                border-color: var(--bs-border-color-translucent, #495057);
            }
        }

        /* Bootstrap dark mode support */
        [data-bs-theme="dark"] .select-all-container {
            background-color: var(--bs-dark, #343a40);
            border-color: var(--bs-border-color-translucent, #495057);
        }

        [data-bs-theme="dark"] .select-all-container h5 {
            color: var(--bs-light, #f8f9fa) !important;
        }
    </style>
@endpush

@push('custom-js')
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Handle folder icon clicks
            $('.main-folder-icon').click(function() {
                var ulElement = $(this).parent().parent().find('ul');
                ulElement.toggle();

                if (!ulElement.is(':visible')) {
                    ulElement.find('ul').hide();
                    $(this).removeClass('open'); // Remove 'open' class when closing
                } else {
                    $(this).addClass('open'); // Add 'open' class when opening
                }
            });

            // Initialize with folders open
            $('.main-folder-icon').click();

            // Handle Select All functionality
            $('#selectAllPermissions').change(function() {
                var isChecked = $(this).is(':checked');
                
                // Select/deselect all permission checkboxes
                $('input[name="permission[]"]').prop('checked', isChecked);
                
                // Update the select all checkbox state based on individual checkboxes
                updateSelectAllState();
            });

            // Handle individual checkbox changes
            $('input[name="permission[]"]').change(function() {
                updateSelectAllState();
            });

            // Function to update the select all checkbox state
            function updateSelectAllState() {
                var totalCheckboxes = $('input[name="permission[]"]').length;
                var checkedCheckboxes = $('input[name="permission[]"]:checked').length;
                
                if (checkedCheckboxes === 0) {
                    $('#selectAllPermissions').prop('indeterminate', false);
                    $('#selectAllPermissions').prop('checked', false);
                } else if (checkedCheckboxes === totalCheckboxes) {
                    $('#selectAllPermissions').prop('indeterminate', false);
                    $('#selectAllPermissions').prop('checked', true);
                } else {
                    $('#selectAllPermissions').prop('indeterminate', true);
                }
            }

            // Initialize select all state on page load
            updateSelectAllState();
        });
    </script>
@endpush
