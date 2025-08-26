@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded-circle bg-label-warning">
                                    <i class="bx bx-edit fs-4"></i>
                                </span>
                            </div>
                            <div>
                                <h4 class="mb-0 text-white">{{ localize('global.edit_pharmacy') }}</h4>
                                <p class="text-white mb-0">{{ localize('global.update_pharmacy_description') }}</p>
                            </div>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ localize('global.dashboard') }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('pharmacies.index') }}">{{ localize('global.pharmacies') }}</a></li>
                                <li class="breadcrumb-item active">{{ localize('global.edit_pharmacy') }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-info">
                                        <i class="bx bx-store fs-4"></i>
                                    </span>
                                </div>
                                <div>
                                    <h5 class="mb-0">{{ localize('global.pharmacy_information') }}</h5>
                                    <small class="text-muted">{{ localize('global.update_the_pharmacy_details_below') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('pharmacies.update', $pharmacy->id) }}" method="post">
                                @csrf
                                @method('PUT')
                                
                                <!-- Basic Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3">
                                            <i class="bx bx-info-circle me-2"></i>
                                            {{ localize('global.basic_information') }}
                                        </h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                {{ localize('global.pharmacy_name') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   name="name" value="{{ old('name', $pharmacy->name) }}" 
                                                   placeholder="{{ localize('global.enter_pharmacy_name') }}" required>
                                            @error('name')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                {{ localize('global.pharmacy_phone') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                                   name="phone" value="{{ old('phone', $pharmacy->phone) }}" 
                                                   placeholder="{{ localize('global.enter_phone_number') }}" required>
                                            @error('phone')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Address Information -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3">
                                            <i class="bx bx-map me-2"></i>
                                            {{ localize('global.location_details') }}
                                        </h6>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                {{ localize('global.pharmacy_address') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                                      name="address" rows="3" 
                                                      placeholder="{{ localize('global.enter_pharmacy_address') }}" required>{{ old('address', $pharmacy->address) }}</textarea>
                                            @error('address')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- User Assignment -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3">
                                            <i class="bx bx-user me-2"></i>
                                            {{ localize('global.user_assignment') }}
                                        </h6>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                {{ localize('global.pharmacy_user') }}
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control select2 @error('user_id') is-invalid @enderror" 
                                                    name="user_id" required>
                                                <option value="">{{ localize('global.select_user') }}</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}"
                                                        {{ old('user_id', $pharmacy->user_id) == $user->id ? 'selected' : '' }}>
                                                        {{ $user->name }} {{ $user->last_name }} ({{ $user->email }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('user_id')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                {{ localize('global.select_user_description') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                            <div>
                                                <small class="text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    {{ localize('global.fields_marked_with_asterisk_are_required') }}
                                                </small>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('pharmacies.index') }}" class="btn btn-outline-secondary">
                                                    <i class="bx bx-arrow-back me-1"></i>
                                                    {{ localize('global.cancel') }}
                                                </a>
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="bx bx-save me-1"></i>
                                                    {{ localize('global.update_pharmacy') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .page-title-box {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .page-title-box .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }

        .page-title-box .breadcrumb-item a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
        }

        .page-title-box .breadcrumb-item.active {
            color: white;
        }

        .page-title-box .breadcrumb-item + .breadcrumb-item::before {
            color: rgba(255,255,255,0.6);
        }

        .card {
            border: none;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            padding: 1.5rem;
        }

        .form-label {
            color: #495057;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }

        .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            border: none;
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
            color: white;
        }

        .btn-outline-secondary {
            border: 2px solid #6c757d;
            color: #6c757d;
        }

        .btn-outline-secondary:hover {
            background: #6c757d;
            border-color: #6c757d;
            transform: translateY(-2px);
        }

        .select2-container--default .select2-selection--single {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            height: 48px;
            padding: 0.75rem 1rem;
        }

        .select2-container--default .select2-selection--single:focus {
            border-color: #ffc107;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5;
            padding-left: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px;
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .form-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .avatar-sm {
            width: 32px;
            height: 32px;
            font-size: 0.875rem;
        }

        .bg-label-warning {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .bg-label-info {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .border-top {
            border-top: 1px solid #e9ecef !important;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        @media (max-width: 768px) {
            .page-title-box {
                padding: 1rem;
            }
            
            .card-header {
                padding: 1rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
            }
        }
    </style>
@endpush

@push('custom-js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5',
                placeholder: '{{ localize("global.select_user") }}',
                allowClear: true
            });
        });
    </script>
@endpush
