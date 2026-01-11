@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <!-- Content -->
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-success">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.active_users') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-success" style="font-size: xx-large;">
                                            {{ $allUsers->where('status', 1)->count() }}</h4>
                                    </div>
                                </div>
                                <span class="badge bg-success rounded p-2">
                                    <i class="bx bx-group bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-danger">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.deactive_users') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-danger" style="font-size: xx-large;">
                                            {{ $allUsers->where('status', 0)->count() }}</h4>
                                    </div>
                                </div>
                                <span class="badge bg-danger rounded p-2">
                                    <i class="bx bx-user-x bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.total_users') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-primary" style="font-size: xx-large;">
                                            {{ $allUsers->count() }}</h4>
                                    </div>
                                </div>
                                <span class="badge bg-primary rounded p-2">
                                    <i class="bx bx-group bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-info">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.new_users') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        @php
                                            $currentMonth = \Carbon\Carbon::now()->format('Y-m');
                                            $newUsersCount = $allUsers->filter(function ($user) use ($currentMonth) {
                                                if($user->created_at == null) {
                                                    return null;
                                                } else {
                                                    return $user->created_at->format('Y-m') == $currentMonth;
                                                }
                                            })->count();
                                        @endphp
                                        <h4 class="mb-0 me-2 badge badge-center bg-info" style="font-size: xx-large;">
                                            {{ $newUsersCount }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge bg-info rounded p-2">
                                    <i class="bx bx-user-plus bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Users List Table -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-3">
                        <i class="bx bx-user me-2"></i>{{ localize('global.users') ?? 'Users Management' }}
                    </h5>
                    <div class="row g-3 align-items-start">
                        <div class="col-md-12">
                            <div class="filter-panel bg-none">
                                <button class="btn btn-label-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="true" aria-controls="filterCollapse">
                                    <i class="bx bx-filter-alt me-1"></i>{{ localize('global.filters') ?? 'Filters' }}
                                    @if(request()->hasAny(['category_id', 'status', 'role_id', 'is_doctor', 'clinic_type', 'search']))
                                        <span class="badge bg-primary ms-2">Active</span>
                                    @endif
                                </button>
                                
                                <div class="collapse show" id="filterCollapse">
                                    <form method="GET" action="{{ route('users.index') }}" id="filterForm">
                                        <div class="row g-3">
                                            <!-- Search -->
                                            <div class="col-md-4">
                                                <label class="form-label">{{ localize('global.search') ?? 'Search' }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                                                    <input type="text" name="search" class="form-control" 
                                                           placeholder="{{ localize('global.search_name_email') ?? 'Search by name or email...' }}" 
                                                           value="{{ request('search') }}">
                                                </div>
                                            </div>

                                            <!-- Category Filter -->
                                            <div class="col-md-4">
                                                <label class="form-label">{{ localize('global.category') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-category"></i></span>
                                                    <select name="category_id" class="form-select">
                                                        <option value="">{{ localize('global.all_categories') }}</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                                {{ $category->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Status Filter -->
                                            <div class="col-md-4">
                                                <label class="form-label">{{ localize('global.status') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-check-circle"></i></span>
                                                    <select name="status" class="form-select">
                                                        <option value="">{{ localize('global.all_status') ?? 'All Status' }}</option>
                                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>{{ localize('global.active') }}</option>
                                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>{{ localize('global.deactive') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Role Filter -->
                                            <div class="col-md-4">
                                                <label class="form-label">{{ localize('global.role') ?? 'Role' }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-shield"></i></span>
                                                    <select name="role_id" class="form-select">
                                                        <option value="">{{ localize('global.all_roles') ?? 'All Roles' }}</option>
                                                        @foreach($roles as $role)
                                                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                                                {{ $role->name_dr ?? $role->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Doctor Status Filter -->
                                            <div class="col-md-4">
                                                <label class="form-label">{{ localize('global.is_doctor') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-plus-medical"></i></span>
                                                    <select name="is_doctor" class="form-select">
                                                        <option value="">{{ localize('global.all') ?? 'All' }}</option>
                                                        <option value="1" {{ request('is_doctor') === '1' ? 'selected' : '' }}>{{ localize('global.yes') }}</option>
                                                        <option value="0" {{ request('is_doctor') === '0' ? 'selected' : '' }}>{{ localize('global.no') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Clinic Type Filter -->
                                            <div class="col-md-4">
                                                <label class="form-label">{{ localize('global.clinic_type') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bx bx-building"></i></span>
                                                    <select name="clinic_type" class="form-select">
                                                        <option value="">{{ localize('global.all_types') ?? 'All Types' }}</option>
                                                        <option value="hospital" {{ request('clinic_type') == 'hospital' ? 'selected' : '' }}>{{ localize('global.hospital') }}</option>
                                                        <option value="clinic" {{ request('clinic_type') == 'clinic' ? 'selected' : '' }}>{{ localize('global.clinic') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Filter Actions -->
                                            <div class="col-md-12">
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="bx bx-filter-alt me-1"></i>{{ localize('global.apply_filters') ?? 'Apply Filters' }}
                                                    </button>
                                                    @if(request()->hasAny(['category_id', 'status', 'role_id', 'is_doctor', 'clinic_type', 'search']))
                                                        <a href="{{ route('users.index') }}" class="btn btn-label-secondary">
                                                            <i class="bx bx-reset me-1"></i>{{ localize('global.clear_all') ?? 'Clear All' }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 text-end">
                            <a class="btn btn-primary waves-effect waves-light" href="{{ route('users.create') }}">
                                <i class="bx bx-plus me-1"></i>
                                <span>{{ localize('global.create') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive p-2">
                    <table class="table table-hover border-top">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ localize('global.avatar') }}</th>
                                <th>{{ localize('global.name') }}</th>
                                <th>{{ localize('global.email') }}</th>
                                <th>{{ localize('global.category') }}</th>
                                <th>{{ localize('global.is_doctor') }}</th>
                                <th>{{ localize('global.clinic_type') }}</th>
                                <th>{{ localize('global.status') }}</th>
                                <th>{{ localize('global.roles') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $index => $user)
                                <tr>
                                    <td>{{ $users->firstItem() + $index }}</td>
                                    <td>
                                        <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('assets/img/avatars/1.png') }}" 
                                             alt="{{ $user->name }}" 
                                             class="user-avatar">
                                    </td>
                                    <td>{{ $user->name }} {{ $user->last_name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->category->name ?? '-' }}</td>
                                    <td>
                                        @if($user->is_doctor)
                                            <span class="badge rounded-pill bg-label-success">{{ localize('global.yes') }}</span>
                                        @else
                                            <span class="badge rounded-pill bg-label-secondary">{{ localize('global.no') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->clinic_type)
                                            <span class="badge rounded-pill {{ $user->clinic_type == 'hospital' ? 'bg-label-primary' : 'bg-label-info' }}">
                                                {{ $user->clinic_type == 'hospital' ? localize('global.hospital') : localize('global.clinic') }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <label class="status-switch">
                                                <input type="checkbox" 
                                                       class="status-checkbox" 
                                                       data-user-id="{{ $user->id }}"
                                                       {{ $user->status == 1 ? 'checked' : '' }}
                                                       {{ $user->id == auth()->user()->id ? 'disabled' : '' }}>
                                                <span class="status-slider"></span>
                                            </label>
                                            <span class="badge {{ $user->status == 1 ? 'bg-success' : 'bg-secondary' }} ms-2">
                                                {{ $user->status == 1 ? localize('global.active') : localize('global.deactive') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @foreach($user->roles as $role)
                                            <span class="badge rounded-pill bg-label-danger">{{ $role->name_dr ?? $role->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('users.edit', $user->id) }}" 
                                               class="btn btn-sm btn-icon btn-primary"
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top" 
                                               title="Edit User">
                                                <i class="bx bxs-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bx bx-info-circle bx-lg"></i>
                                            <p class="mt-2">{{ localize('global.no_users_found') ?? 'No users found' }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($users->hasPages() || $users->total() > 0)
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="text-muted">
                                    {{ localize('global.showing') ?? 'Showing' }} 
                                    {{ $users->firstItem() }} 
                                    {{ localize('global.to') ?? 'to' }} 
                                    {{ $users->lastItem() }} 
                                    {{ localize('global.of') ?? 'of' }} 
                                    {{ $users->total() }} 
                                    {{ localize('global.entries') ?? 'entries' }}
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <label class="form-label mb-0">{{ localize('global.per_page') ?? 'Per Page' }}:</label>
                                    <select name="per_page" id="perPageSelect" class="form-select form-select-sm" style="width: auto;">
                                        <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                                        <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                {{ $users->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- / Content -->

    <div class="content-backdrop fade"></div>
    </div>
@endsection

@push('custom-css')
    <style>
        /* Statistics Cards Styling */
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .bg-label-success {
            background: var(--bs-success-bg-subtle);
            border: 1px solid var(--bs-success-border-subtle);
        }

        .bg-label-danger {
            background: var(--bs-danger-bg-subtle);
            border: 1px solid var(--bs-danger-border-subtle);
        }

        .bg-label-primary {
            background: var(--bs-primary-bg-subtle);
            border: 1px solid var(--bs-primary-border-subtle);
        }

        .bg-label-info {
            background: var(--bs-info-bg-subtle);
            border: 1px solid var(--bs-info-border-subtle);
        }

        /* Dark mode specific adjustments */
        [data-bs-theme="dark"] .bg-label-success {
            background: rgba(25, 135, 84, 0.1);
            border-color: rgba(25, 135, 84, 0.2);
        }

        [data-bs-theme="dark"] .bg-label-danger {
            background: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.2);
        }

        [data-bs-theme="dark"] .bg-label-primary {
            background: rgba(13, 110, 253, 0.1);
            border-color: rgba(13, 110, 253, 0.2);
        }

        [data-bs-theme="dark"] .bg-label-info {
            background: rgba(13, 202, 240, 0.1);
            border-color: rgba(13, 202, 240, 0.2);
        }

        /* Table Styling */
        .card-datatable table.dataTable thead th {
            text-align: right;
            background-color: #f8f9fa;
            font-weight: 600;
            padding: 1rem 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }

        .card-datatable table.dataTable tbody td {
            text-align: right;
            padding: 0.875rem 0.5rem;
            vertical-align: middle;
        }

        .card-datatable table.dataTable tbody tr {
            transition: all 0.2s ease;
        }

        .card-datatable table.dataTable tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Avatar Styling */
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        .user-avatar:hover {
            transform: scale(1.15);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* Toggle Switch Styling */
        .status-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .status-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .status-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.3s;
            border-radius: 24px;
        }

        .status-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }

        input:checked + .status-slider {
            background-color: #28c76f;
        }

        input:disabled + .status-slider {
            opacity: 0.5;
            cursor: not-allowed;
        }

        input:checked + .status-slider:before {
            transform: translateX(20px);
        }

        /* Badge Enhancements */
        .badge {
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
        }

        /* Action Buttons */
        .btn-icon {
            transition: all 0.2s ease;
        }

        .btn-icon:hover {
            transform: scale(1.1);
        }

        /* Filter Section */
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }

        .form-select {
            border-left: none;
        }

        .form-select:focus {
            border-color: #ced4da;
            box-shadow: none;
        }

        /* Card Header */
        .card-header {
            background-color: #fff;
            padding: 1.5rem;
        }

        .card-title {
            color: #5e5873;
            font-weight: 600;
        }

        /* Filter Panel Styling */
        .filter-panel {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .filter-panel.bg-none {
            background-color: transparent;
        }

        .filter-panel .form-label {
            font-weight: 500;
            font-size: 0.875rem;
            color: #5e5873;
            margin-bottom: 0.5rem;
        }

        .filter-panel .collapse {
            margin-top: 0.5rem;
        }

        .filter-panel .input-group-text {
            background-color: #fff;
        }
    </style>
@endpush

@push('custom-js')
    <script>
        $(function() {
            // Status toggle functionality
            $('.status-checkbox').on('change', function() {
                var userId = $(this).data('user-id');
                var status = $(this).prop('checked') ? 1 : 0;
                var checkbox = $(this);

                $.ajax({
                    url: "{{ route('users.update-status') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        user_id: userId,
                        status: status
                    },
                    success: function(response) {
                        console.log(response);
                        window.location.href = "{{ route('users.index') }}";
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                        // Revert checkbox on error
                        checkbox.prop('checked', !checkbox.prop('checked'));
                    }
                });
            });

            // Per page selector functionality
            $('#perPageSelect').on('change', function() {
                var perPage = $(this).val();
                var url = new URL(window.location.href);
                url.searchParams.set('per_page', perPage);
                url.searchParams.delete('page'); // Reset to first page when changing per page
                window.location.href = url.toString();
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endpush
