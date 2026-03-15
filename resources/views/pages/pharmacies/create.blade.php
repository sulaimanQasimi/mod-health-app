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
                        <h5 class="mb-0">{{ localize('global.create_pharmacy') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('pharmacies.store') }}" method="post">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">{{ localize('global.pharmacy_name') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               name="name" id="name" value="{{ old('name') }}"
                                               placeholder="{{ localize('global.enter_pharmacy_name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">{{ localize('global.pharmacy_phone') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                               name="phone" id="phone" value="{{ old('phone') }}"
                                               placeholder="{{ localize('global.enter_phone_number') }}" required>
                                        @error('phone')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">{{ localize('global.pharmacy_address') }} <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('address') is-invalid @enderror"
                                                  name="address" id="address" rows="3"
                                                  placeholder="{{ localize('global.enter_pharmacy_address') }}" required>{{ old('address') }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.pharmacy_users') }} <span class="text-danger">*</span></label>
                                        <div id="user-selection-container">
                                            <div class="user-selection-item mb-2">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <select class="form-control select2 user-select" name="user_ids[]" required>
                                                            <option value="">{{ localize('global.select_user') }}</option>
                                                            @foreach($users as $user)
                                                                <option value="{{ $user->id }}">{{ $user->name }} {{ $user->last_name }} ({{ $user->email }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <select class="form-control role-select" name="roles[]" required>
                                                            <option value="staff">{{ localize('global.staff') }}</option>
                                                            <option value="manager">{{ localize('global.manager') }}</option>
                                                            <option value="procurement">{{ localize('global.procurement') }}</option>
                                                            <option value="viewer">{{ localize('global.viewer') }}</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-outline-danger btn-sm remove-user" style="display: none;">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-user">
                                            <i class="bx bx-plus"></i> {{ localize('global.add_user') }}
                                        </button>
                                        @error('user_ids')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @error('roles')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted d-block mt-1">{{ localize('global.select_users_description') }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <a href="{{ route('pharmacies.index') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>{{ localize('global.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i>{{ localize('global.create_pharmacy') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script>
        $(document).ready(function() {
            var select2Options = {
                placeholder: '{{ localize("global.select_user") }}',
                allowClear: true,
                width: '100%'
            };

            // Initialize Select2 on existing user selects
            $('#user-selection-container .user-select').each(function() {
                var $select = $(this);
                if (!$select.hasClass('select2-hidden-accessible')) {
                    $select.select2(select2Options);
                }
            });

            $('#add-user').on('click', function() {
                var container = $('#user-selection-container');
                var firstItem = container.find('.user-selection-item').first();
                var newItem = firstItem.clone();

                // Remove Select2 markup from clone so we get a plain select
                newItem.find('.select2-container').remove();
                newItem.find('.user-select').removeClass('select2-hidden-accessible').show();

                newItem.find('.user-select').val('').trigger('change');
                newItem.find('.role-select').val('staff');

                if (container.find('.user-selection-item').length >= 1) {
                    container.find('.remove-user').show();
                }
                container.append(newItem);

                newItem.find('.user-select').select2(select2Options);
            });

            $(document).on('click', '.remove-user', function() {
                var item = $(this).closest('.user-selection-item');
                var container = $('#user-selection-container');
                if (container.find('.user-selection-item').length > 1) {
                    item.find('.user-select').select2('destroy');
                    item.remove();
                    if (container.find('.user-selection-item').length === 1) {
                        container.find('.remove-user').hide();
                    }
                }
            });

            if ($('#user-selection-container .user-selection-item').length === 1) {
                $('#user-selection-container .remove-user').hide();
            }
        });
    </script>
@endpush
