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
                                        <h5 class="mb-0 text-white">{{ localize('global.pharmacy_information') }}</h5>
                                        <small class="text-white">{{ localize('global.pharmacy_details_description') }}</small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    @can('pharmacy.edit')
                                    <a href="{{ route('pharmacies.edit', $pharmacy->id) }}" class="btn btn-success btn-sm">
                                        <i class="bx bx-edit me-1"></i>
                                        {{ localize('global.edit') }}
                                    </a>
                                    @endcan
                                    <a href="{{ route('pharmacies.index') }}" class="btn btn-outline-success btn-sm">
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
                                            {{ localize('global.pharmacy_users') }}
                                        </label>
                                        <div class="info-value">
                                            @if($pharmacy->activeUsers->count() > 0)
                                                <div class="row">
                                                    @foreach($pharmacy->activeUsers as $user)
                                                        <div class="col-md-6 mb-2">
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar avatar-sm me-2">
                                                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                                                        {{ $user->name[0] }}
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <div class="fw-semibold">{{ $user->name }} {{ $user->last_name }}</div>
                                                                    <small class="text-muted">{{ $user->email }}</small>
                                                                    <span class="badge bg-{{ $user->pivot->role == 'manager' ? 'danger' : ($user->pivot->role == 'staff' ? 'primary' : 'secondary') }} ms-2">
                                                                        {{ ucfirst($user->pivot->role) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="mt-2">
                                                    @can('pharmacy.manage_users')
                                                        <a href="{{ route('pharmacies.manage-users', $pharmacy->id) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bx bx-user-plus me-1"></i>
                                                            {{ localize('global.manage_users') }}
                                                        </a>
                                                    @endcan
                                                </div>
                                            @else
                                                <div class="text-muted">
                                                    <i class="bx bx-user-x me-2"></i>
                                                    {{ localize('global.no_users_assigned') }}
                                                </div>
                                                @can('pharmacy.manage_users')
                                                    <div class="mt-2">
                                                        <a href="{{ route('pharmacies.manage-users', $pharmacy->id) }}" class="btn btn-sm btn-primary">
                                                            <i class="bx bx-user-plus me-1"></i>
                                                            {{ localize('global.assign_users') }}
                                                        </a>
                                                    </div>
                                                @endcan
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Statistics -->
                            @if(isset($statistics))
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="bx bx-bar-chart me-2"></i>
                                        {{ localize('global.pharmacy_statistics') }}
                                    </h6>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">
                                            {{ localize('global.total_users') }}
                                        </label>
                                        <div class="info-value">
                                            <i class="bx bx-user me-2 text-primary"></i>
                                            {{ $statistics['total_users'] }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">
                                            {{ localize('global.managers') }}
                                        </label>
                                        <div class="info-value">
                                            <i class="bx bx-user-check me-2 text-primary"></i>
                                            {{ $statistics['managers_count'] }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">
                                            {{ localize('global.total_incomes') }}
                                        </label>
                                        <div class="info-value">
                                            <i class="bx bx-trending-up me-2 text-primary"></i>
                                            {{ $statistics['total_incomes'] }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">
                                            {{ localize('global.total_outcomes') }}
                                        </label>
                                        <div class="info-value">
                                            <i class="bx bx-trending-down me-2 text-primary"></i>
                                            {{ $statistics['total_outcomes'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

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
                                            <a href="{{ route('pharmacies.edit', $pharmacy->id) }}" class="btn">
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
            color: rgba(255, 255, 255, 0.8);
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
            box-shadow: 0 2px 20px rgba(0, 187, 50, 0.08);
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-bottom: 1px solid #dee2e6;
            padding: 1.5rem;
        }

        .info-item {
            padding: 1rem;
            border: 1px solid #007bff;
        }
        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color:rgb(248, 251, 255);
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
        }

        .form-label {
            color:rgb(255, 255, 255);
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
            background: linear-gradient(135deg,rgb(0, 171, 17) 0%,rgba(0, 167, 47, 0.7) 100%);
            border: 1px solid white;
            color: white;
        }
        .btn:hover {
            background: linear-gradient(white,rgb(233, 233, 233));
            border: 1px solid white;
            color: black;
            transition: all 0.3s ease;
        }

        .btn-warning {
            background: linear-gradient(135deg,rgb(7, 255, 32) 0%,rgba(0, 134, 38, 0.7) 100%);
            border: 1px solid white;
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
