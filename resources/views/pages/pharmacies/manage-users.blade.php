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
                                <span class="avatar-initial rounded-circle bg-label-info">
                                    <i class="bx bx-user-plus fs-4"></i>
                                </span>
                            </div>
                            <div>
                                <h4 class="mb-0 text-white">{{ localize('global.manage_pharmacy_users') }}</h4>
                                <p class="text-white mb-0">{{ localize('global.manage_users_for_pharmacy') }}: {{ $pharmacy->name }}</p>
                            </div>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ localize('global.dashboard') }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('pharmacies.index') }}">{{ localize('global.pharmacies') }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('pharmacies.show', $pharmacy->id) }}">{{ $pharmacy->name }}</a></li>
                                <li class="breadcrumb-item active">{{ localize('global.manage_users') }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Current Users -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        <i class="bx bx-user fs-4"></i>
                                    </span>
                                </div>
                                <div>
                                    <h5 class="mb-0">{{ localize('global.current_users') }}</h5>
                                    <small class="text-muted">{{ localize('global.users_assigned_to_pharmacy') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($pharmacy->activeUsers->count() > 0)
                                <div class="row">
                                    @foreach($pharmacy->activeUsers as $user)
                                        <div class="col-md-6 mb-3">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-3">
                                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                                {{ $user->name[0] }}
                                                            </span>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="fw-semibold">{{ $user->name }} {{ $user->last_name }}</div>
                                                            <small class="text-muted">{{ $user->email }}</small>
                                                            <div class="mt-1">
                                                                <span class="badge bg-{{ $user->pivot->role == 'manager' ? 'danger' : ($user->pivot->role == 'staff' ? 'primary' : 'secondary') }}">
                                                                    {{ ucfirst($user->pivot->role) }}
                                                                </span>
                                                                <small class="text-muted ms-2">
                                                                    {{ localize('global.joined') }}: {{ $user->pivot->joined_at ? \Carbon\Carbon::parse($user->pivot->joined_at)->format('M d, Y') : 'N/A' }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                                                       data-user-id="{{ $user->id }}" 
                                                                       data-user-name="{{ $user->name }} {{ $user->last_name }}"
                                                                       data-user-role="{{ $user->pivot->role }}">
                                                                        <i class="bx bx-edit me-2"></i>{{ localize('global.edit_role') }}
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <form action="{{ route('pharmacies.remove-user', $pharmacy->id) }}" method="POST" class="d-inline">
                                                                        @csrf
                                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                                        <button type="submit" class="dropdown-item text-danger" 
                                                                                onclick="return confirm('{{ localize('global.are_you_sure_remove_user') }}')">
                                                                            <i class="bx bx-trash me-2"></i>{{ localize('global.remove_user') }}
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <div class="avatar avatar-lg mx-auto mb-3">
                                        <span class="avatar-initial rounded-circle bg-label-secondary">
                                            <i class="bx bx-user-x fs-2"></i>
                                        </span>
                                    </div>
                                    <h5 class="text-muted">{{ localize('global.no_users_assigned') }}</h5>
                                    <p class="text-muted">{{ localize('global.add_users_to_pharmacy') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Add New User -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <span class="avatar-initial rounded-circle bg-label-success">
                                        <i class="bx bx-user-plus fs-4"></i>
                                    </span>
                                </div>
                                <div>
                                    <h5 class="mb-0">{{ localize('global.add_user') }}</h5>
                                    <small class="text-muted">{{ localize('global.assign_new_user_to_pharmacy') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('pharmacies.add-user', $pharmacy->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        {{ localize('global.select_user') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control select2 @error('user_id') is-invalid @enderror" 
                                            name="user_id" required>
                                        <option value="">{{ localize('global.select_user') }}</option>
                                        @foreach($availableUsers as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->name }} {{ $user->last_name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        {{ localize('global.role') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('role') is-invalid @enderror" 
                                            name="role" required>
                                        <option value="staff">{{ localize('global.staff') }}</option>
                                        <option value="manager">{{ localize('global.manager') }}</option>
                                        <option value="procurement">{{ localize('global.procurement') }}</option>
                                        <option value="viewer">{{ localize('global.viewer') }}</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        {{ localize('global.permissions') }}
                                    </label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="view" id="perm_view">
                                        <label class="form-check-label" for="perm_view">{{ localize('global.view') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="create" id="perm_create">
                                        <label class="form-check-label" for="perm_create">{{ localize('global.create') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="update" id="perm_update">
                                        <label class="form-check-label" for="perm_update">{{ localize('global.update') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="delete" id="perm_delete">
                                        <label class="form-check-label" for="perm_delete">{{ localize('global.delete') }}</label>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-user-plus me-1"></i>
                                        {{ localize('global.add_user') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="card mt-3">
                        <div class="card-body text-center">
                            <a href="{{ route('pharmacies.show', $pharmacy->id) }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i>
                                {{ localize('global.back_to_pharmacy') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ localize('global.edit_user_role') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('pharmacies.update-user-role', $pharmacy->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                {{ localize('global.user') }}
                            </label>
                            <input type="text" class="form-control" id="edit_user_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                {{ localize('global.role') }}
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" name="role" id="edit_user_role" required>
                                <option value="staff">{{ localize('global.staff') }}</option>
                                <option value="manager">{{ localize('global.manager') }}</option>
                                <option value="procurement">{{ localize('global.procurement') }}</option>
                                <option value="viewer">{{ localize('global.viewer') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ localize('global.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ localize('global.update_role') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('custom-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .page-title-box {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
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
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 1.5rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #17a2b8;
            box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
        }

        .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.4);
        }

        .select2-container--default .select2-selection--single {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            height: 48px;
            padding: 0.75rem 1rem;
        }

        .select2-container--default .select2-selection--single:focus {
            border-color: #17a2b8;
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

        .avatar-lg {
            width: 80px;
            height: 80px;
            font-size: 2rem;
        }

        .bg-label-info {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .bg-label-primary {
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }

        .bg-label-success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .bg-label-secondary {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        .badge {
            font-size: 0.75rem;
        }

        .dropdown-toggle::after {
            display: none;
        }

        .form-check-input:checked {
            background-color: #17a2b8;
            border-color: #17a2b8;
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
    <script>
        $(document).ready(function() {
            // Initialize Select2 for user select (uses global Select2 from layout)
            $('select[name="user_id"].select2').select2({
                placeholder: '{{ localize("global.select_user") }}',
                allowClear: true,
                width: '100%'
            });

            // Edit user modal
            $('#editUserModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const userId = button.data('user-id');
                const userName = button.data('user-name');
                const userRole = button.data('user-role');

                $('#edit_user_id').val(userId);
                $('#edit_user_name').val(userName);
                $('#edit_user_role').val(userRole);
            });
        });
    </script>
@endpush
