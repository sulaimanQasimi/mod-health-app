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
                                <span class="avatar-initial rounded-circle bg-label-success">
                                    <i class="bx bx-show fs-4"></i>
                                </span>
                            </div>
                            <div>
                                <h4 class="mb-0 text-white">{{ localize('global.pharmacy_details') }}</h4>
                                <p class="text-white mb-0">{{ localize('global.view_pharmacy_information') }}</p>
                            </div>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ localize('global.dashboard') }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('pharmacies.index') }}">{{ localize('global.pharmacies') }}</a></li>
                                <li class="breadcrumb-item active">{{ localize('global.pharmacy_details') }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <span class="avatar-initial rounded-circle bg-label-info">
                                            <i class="bx bx-store fs-4"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">{{ localize('global.pharmacy_information') }}</h5>
                                        <small class="text-muted">{{ localize('global.pharmacy_details_description') }}</small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    @can('pharmacy.edit')
                                    <a href="{{ route('pharmacies.edit', $pharmacy->id) }}" class="btn btn-warning btn-sm">
                                        <i class="bx bx-edit me-1"></i>
                                        {{ localize('global.edit') }}
                                    </a>
                                    @endcan
                                    <a href="{{ route('pharmacies.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bx bx-arrow-back me-1"></i>
                                        {{ localize('global.back') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Basic Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="bx bx-info-circle me-2"></i>
                                        {{ localize('global.basic_information') }}
                                    </h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">
                                            {{ localize('global.pharmacy_name') }}
                                        </label>
                                        <div class="info-value">
                                            <i class="bx bx-building me-2 text-primary"></i>
                                            {{ $pharmacy->name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">
                                            {{ localize('global.pharmacy_phone') }}
                                        </label>
                                        <div class="info-value">
                                            <i class="bx bx-phone me-2 text-primary"></i>
                                            {{ $pharmacy->phone }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Location Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="bx bx-map me-2"></i>
                                        {{ localize('global.location_details') }}
                                    </h6>
                                </div>
                                <div class="col-12">
                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">
                                            {{ localize('global.pharmacy_address') }}
                                        </label>
                                        <div class="info-value">
                                            <i class="bx bx-map-pin me-2 text-primary"></i>
                                            {{ $pharmacy->address }}
                                        </div>
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
                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">
                                            {{ localize('global.pharmacy_user') }}
                                        </label>
                                        <div class="info-value">
                                            <i class="bx bx-user-check me-2 text-primary"></i>
                                            {{ $pharmacy->user ? $pharmacy->user->name . ' ' . $pharmacy->user->last_name . ' (' . $pharmacy->user->email . ')' : localize('global.not_assigned') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Audit Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="bx bx-history me-2"></i>
                                        {{ localize('global.audit_information') }}
                                    </h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">
                                            {{ localize('global.created_at') }}
                                        </label>
                                        <div class="info-value">
                                            <i class="bx bx-calendar me-2 text-primary"></i>
                                            {{ $pharmacy->created_at ? $pharmacy->created_at->format('Y-m-d H:i:s') : localize('global.not_available') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">
                                            {{ localize('global.updated_at') }}
                                        </label>
                                        <div class="info-value">
                                            <i class="bx bx-calendar-check me-2 text-primary"></i>
                                            {{ $pharmacy->updated_at ? $pharmacy->updated_at->format('Y-m-d H:i:s') : localize('global.not_available') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">
                                            {{ localize('global.created_by') }}
                                        </label>
                                        <div class="info-value">
                                            <i class="bx bx-user-plus me-2 text-primary"></i>
                                            {{ $pharmacy->createdBy ? $pharmacy->createdBy->name . ' ' . $pharmacy->createdBy->last_name : localize('global.not_available') }}
                                        </div>
                                    </div>
                                </div>
                                @if($pharmacy->updatedBy)
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">
                                            {{ localize('global.updated_by') }}
                                        </label>
                                        <div class="info-value">
                                            <i class="bx bx-user-edit me-2 text-primary"></i>
                                            {{ $pharmacy->updatedBy->name . ' ' . $pharmacy->updatedBy->last_name }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-center pt-3 border-top">
                                        <div class="d-flex gap-2">
                                            @can('pharmacy.edit')
                                            <a href="{{ route('pharmacies.edit', $pharmacy->id) }}" class="btn btn-warning">
                                                <i class="bx bx-edit me-1"></i>
                                                {{ localize('global.edit_pharmacy') }}
                                            </a>
                                            @endcan
                                            <a href="{{ route('pharmacies.index') }}" class="btn btn-outline-secondary">
                                                <i class="bx bx-arrow-back me-1"></i>
                                                {{ localize('global.back_to_list') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-css')
    <style>
        .page-title-box {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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

        .info-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            border-left: 4px solid #007bff;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: #495057;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
        }

        .form-label {
            color: #6c757d;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
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

        .bg-label-success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
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

        .text-primary {
            color: #007bff !important;
        }

        /* Dark Mode Styles */
        [data-bs-theme="dark"] .info-item {
            background: #2b2c40;
            border-left-color: #696cff;
            color: #a3a4cc;
        }

        [data-bs-theme="dark"] .info-item:hover {
            background: #3a3b4d;
            transform: translateX(5px);
        }

        [data-bs-theme="dark"] .info-value {
            color: #a3a4cc;
        }

        [data-bs-theme="dark"] .form-label {
            color: #a3a4cc;
        }

        [data-bs-theme="dark"] .card {
            background: #2b2c40;
            border: 1px solid #444564;
        }

        [data-bs-theme="dark"] .card-header {
            background: #3a3b4d;
            border-bottom: 1px solid #444564;
            color: #a3a4cc;
        }

        [data-bs-theme="dark"] .card-body {
            background: #2b2c40;
            color: #a3a4cc;
        }

        [data-bs-theme="dark"] .text-primary {
            color: #696cff !important;
        }

        [data-bs-theme="dark"] .border-top {
            border-top: 1px solid #444564 !important;
        }

        [data-bs-theme="dark"] .btn-outline-secondary {
            border-color: #a3a4cc;
            color: #a3a4cc;
        }

        [data-bs-theme="dark"] .btn-outline-secondary:hover {
            background: #a3a4cc;
            border-color: #a3a4cc;
            color: #2b2c40;
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

            .info-item {
                padding: 0.75rem;
            }
        }
    </style>
@endpush
