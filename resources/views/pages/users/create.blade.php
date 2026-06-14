@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">{{ localize('global.users') }} /</span> {{ localize('global.create') }}
                </h4>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> {{ localize('global.back') }}
                </a>
            </div>

            <form action="{{ route('users.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <!-- Left Column: Avatar & Basic Info -->
                    <div class="col-md-4">
                        <!-- Avatar Card -->
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <div class="user-avatar-section mb-3">
                                    <div class="d-flex align-items-center justify-content-center flex-column">
                                        <img class="img-fluid rounded my-3" src="{{ asset('assets/img/avatars/1.png') }}" height="120" width="120" alt="User avatar" id="avatar-preview" />
                                        <div class="button-wrapper">
                                            <label for="upload" class="btn btn-primary me-2 mb-3" tabindex="0">
                                                <span class="d-none d-sm-block">{{ localize('global.upload_new_photo') }}</span>
                                                <i class="bx bx-upload d-block d-sm-none"></i>
                                                <input type="file" id="upload" class="account-file-input" hidden accept="image/png, image/jpeg" name="avatar" onchange="previewImage(this)"/>
                                            </label>
                                            <div class="text-muted small">Allowed JPG, GIF or PNG. Max size of 2MB</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Info Card -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">{{ localize('global.basic_information') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">{{ localize('global.name') }}</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                                    @error('name')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ localize('global.last_name') }}</label>
                                    <input type="text" class="form-control" name="last_name" value="{{ old('last_name') }}">
                                    @error('last_name')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ localize('global.email') }}</label>
                                    <input type="text" class="form-control" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Professional, Security, Access -->
                    <div class="col-md-8">
                        
                        <!-- Professional Details Card -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">{{ localize('global.professional_details') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">{{ localize('global.branch') }}</label>
                                        <select class="form-select select2" name="branch_id" id="branch_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($branches as $value)
                                                <option value="{{ $value->id }}" {{ old('branch_id') == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">{{ localize('global.department') }}</label>
                                        <select class="form-select select2" name="department_id" id="department_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($departments as $value)
                                                <option value="{{ $value->id }}" {{ old('department_id') == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">{{ localize('global.section') }}</label>
                                        <select class="form-select select2" name="section_id" id="section_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($sections as $value)
                                                <option value="{{ $value->id }}" {{ old('section_id') == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ localize('global.clinic_type') }}</label>
                                        <select name="clinic_type" class="form-select select2">
                                            <option value="">{{ localize('global.select') }}</option>
                                            <option value="hospital" {{ old('clinic_type') == 'hospital' ? 'selected' : '' }}>{{ localize('global.hospital') }}</option>
                                            <option value="clinic" {{ old('clinic_type') == 'clinic' ? 'selected' : '' }}>{{ localize('global.clinic') }}</option>
                                            <option value="both" {{ old('clinic_type') == 'both' ? 'selected' : '' }}>{{ localize('global.both') }}</option>
                                        </select>
                                        @error('clinic_type')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3 d-flex align-items-end">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" name="is_doctor" id="is_doctor" value="1" {{ old('is_doctor') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_doctor">
                                                {{ localize('global.is_doctor') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security Card -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">{{ localize('global.security') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ localize('global.password') }}</label>
                                        <input type="password" class="form-control" name="password" autocomplete="new-password">
                                        @error('password')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ localize('global.password_confirmation') }}</label>
                                        <input type="password" class="form-control" name="password_confirmation">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Access Control Card -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">{{ localize('global.access_control') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <h6 class="fw-semibold mb-3">{{ localize('global.roles') }}</h6>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input" id="select_all_roles">
                                            <label class="form-check-label fw-bold" for="select_all_roles">
                                                {{ localize('global.select_all') }}
                                            </label>
                                        </div>
                                        <div class="d-flex flex-column gap-2">
                                            @foreach ($roles as $value)
                                                <div class="form-check">
                                                    <input type="checkbox" name="roles[]" value="{{ $value->id }}" class="form-check-input role-checkbox" id="role_{{ $value->id }}">
                                                    <label class="form-check-label" for="role_{{ $value->id }}">
                                                        {{ $value->name_dr }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <h6 class="fw-semibold mb-3">{{ localize('global.permissions') }}</h6>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input" id="select_all_permissions">
                                            <label class="form-check-label fw-bold" for="select_all_permissions">
                                                {{ localize('global.select_all') }}
                                            </label>
                                        </div>
                                        <div class="d-flex flex-column gap-2" style="max-height: 300px; overflow-y: auto;">
                                            @foreach ($permissions as $value)
                                                <div class="form-check">
                                                    <input type="checkbox" name="permissions[]" value="{{ $value->id }}" class="form-check-input permission-checkbox" id="perm_{{ $value->id }}">
                                                    <label class="form-check-label" for="perm_{{ $value->id }}">
                                                        {{ $value->name_dr }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('users.index') }}" class="btn btn-label-secondary">{{ localize('global.cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('custom-js')
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $(document).ready(function() {
            $('.select2').select2();

            // Select All functionality for roles
            $('#select_all_roles').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('.role-checkbox').prop('checked', isChecked);
            });

            // Update Select All checkbox based on individual role checkboxes
            $('.role-checkbox').on('change', function() {
                var totalRoles = $('.role-checkbox').length;
                var checkedRoles = $('.role-checkbox:checked').length;
                
                if (checkedRoles === totalRoles) {
                    $('#select_all_roles').prop('checked', true);
                } else {
                    $('#select_all_roles').prop('checked', false);
                }
            });

            // Select All functionality for permissions
            $('#select_all_permissions').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('.permission-checkbox').prop('checked', isChecked);
            });

            // Update Select All checkbox based on individual permission checkboxes
            $('.permission-checkbox').on('change', function() {
                var totalPermissions = $('.permission-checkbox').length;
                var checkedPermissions = $('.permission-checkbox:checked').length;
                
                if (checkedPermissions === totalPermissions) {
                    $('#select_all_permissions').prop('checked', true);
                } else {
                    $('#select_all_permissions').prop('checked', false);
                }
            });

            $('#branch_id').on('change', function() {
                var branchID = $(this).val();
                if (branchID !== '') {
                    $.ajax({
                        url: '/get_departments/' + branchID,
                        type: 'GET',
                        success: function(response) {
                            $('#department_id').html(response);
                        }
                    })
                }
            })

            $('#department_id').on('change', function() {
                var depID = $(this).val();
                if (depID !== '') {
                    $.ajax({
                        url: '/get_sections/' + depID,
                        type: 'GET',
                        success: function(response) {
                            $('#section_id').html(response);
                        }
                    })
                }
            })
        });
    </script>
@endpush
