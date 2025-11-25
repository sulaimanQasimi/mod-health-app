@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            {{-- Page Header --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0">
                            <i class="bx bx-user-md me-2 text-primary"></i>
                            {{ localize('global.list_doctors') }}
                        </h4>
                        <a class="btn btn-primary" href="{{ route('doctors.create') }}">
                            <i class="bx bx-plus me-1"></i>
                            <span class="d-none d-sm-inline-block">{{ localize('global.create') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Advanced Search and Filters --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-none border-0 collapsed" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-filter-alt text-primary me-2" style="font-size: 1.2rem;"></i>
                            <h6 class="mb-0 fw-semibold">{{ localize('global.advanced_filters') ?: 'Advanced Filters' }}</h6>
                        </div>
                        <i class="bx bx-chevron-down"></i>
                    </div>
                </div>
                <div class="collapse" id="filterCollapse">
                    <div class="card-body">
                        <form method="GET" action="{{ route('doctors.index') }}" id="filterForm">
                            <div class="row g-3">
                                {{-- Search Input --}}
                                <div class="col-md-4">
                                    <label for="search" class="form-label fw-semibold">
                                        <i class="bx bx-search me-1 text-primary"></i>{{ localize('global.search') }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="bx bx-search"></i>
                                        </span>
                                        <input type="text" class="form-control" id="search" name="search" 
                                               value="{{ request('search') }}" 
                                               placeholder="{{ localize('global.search_by_name_contact_specialization') ?: 'Search by name, contact, specialization...' }}"
                                               autocomplete="off">
                                    </div>
                                </div>

                                {{-- Department Filter --}}
                                <div class="col-md-3 border">
                                    <label for="department_id" class="form-label fw-semibold">
                                        <i class="bx bx-building me-1 text-info"></i>{{ localize('global.department') }}
                                    </label>
                                    <select class="form-select select2" id="department_id" name="department_id">
                                        <option value="">{{ localize('global.all_departments') ?: 'All Departments' }}</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Branch Filter --}}
                                <div class="col-md-3 border">
                                    <label for="branch_id" class="form-label fw-semibold">
                                        <i class="bx bx-map me-1 text-success"></i>{{ localize('global.branch') ?: 'Branch' }}
                                    </label>
                                    <select class="form-select select2" id="branch_id" name="branch_id">
                                        <option value="">{{ localize('global.all_branches') ?: 'All Branches' }}</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Gender Filter --}}
                                <div class="col-md-2">
                                    <label for="gender" class="form-label fw-semibold">
                                        <i class="bx bx-user me-1 text-warning"></i>{{ localize('global.gender') }}
                                    </label>
                                    <select class="form-select" id="gender" name="gender">
                                        <option value="">{{ localize('global.all') ?: 'All' }}</option>
                                        <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>{{ localize('global.male') }}</option>
                                        <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>{{ localize('global.female') }}</option>
                                        <option value="Other" {{ request('gender') == 'Other' ? 'selected' : '' }}>{{ localize('global.other') ?: 'Other' }}</option>
                                    </select>
                                </div>

                                {{-- Clinic Type Filter --}}
                                <div class="col-md-3">
                                    <label for="clinic_type" class="form-label fw-semibold">
                                        <i class="bx bx-hospital me-1 text-danger"></i>{{ localize('global.clinic_type') }}
                                    </label>
                                    <select class="form-select" id="clinic_type" name="clinic_type">
                                        <option value="">{{ localize('global.all') ?: 'All' }}</option>
                                        <option value="hospital" {{ request('clinic_type') == 'hospital' ? 'selected' : '' }}>{{ localize('global.hospital') }}</option>
                                        <option value="clinic" {{ request('clinic_type') == 'clinic' ? 'selected' : '' }}>{{ localize('global.clinic') }}</option>
                                    </select>
                                </div>

                                {{-- Active Status Filter --}}
                                <div class="col-md-3">
                                    <label for="active_status" class="form-label fw-semibold">
                                        <i class="bx bx-check-circle me-1 text-success"></i>{{ localize('global.active_status') }}
                                    </label>
                                    <select class="form-select" id="active_status" name="active_status">
                                        <option value="">{{ localize('global.all') ?: 'All' }}</option>
                                        <option value="1" {{ request('active_status') == '1' ? 'selected' : '' }}>{{ localize('global.active') }}</option>
                                        <option value="0" {{ request('active_status') == '0' ? 'selected' : '' }}>{{ localize('global.inactive') ?: 'Inactive' }}</option>
                                    </select>
                                </div>

                                {{-- Join Date From --}}
                                <div class="col-md-3">
                                    <label for="join_date_from" class="form-label fw-semibold">
                                        <i class="bx bx-calendar me-1 text-info"></i>{{ localize('global.join_date_from') ?: 'Join Date From' }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bx bx-calendar"></i>
                                        </span>
                                        <input type="text" class="form-control datepicker_dari" id="join_date_from" name="join_date_from" 
                                               value="{{ request('join_date_from') }}" placeholder="1403/01/01" autocomplete="off">
                                    </div>
                                </div>

                                {{-- Join Date To --}}
                                <div class="col-md-3">
                                    <label for="join_date_to" class="form-label fw-semibold">
                                        <i class="bx bx-calendar me-1 text-info"></i>{{ localize('global.join_date_to') ?: 'Join Date To' }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bx bx-calendar"></i>
                                        </span>
                                        <input type="text" class="form-control datepicker_dari" id="join_date_to" name="join_date_to" 
                                               value="{{ request('join_date_to') }}" placeholder="1403/01/01" autocomplete="off">
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="col-md-12">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-search me-1"></i>{{ localize('global.filter') ?: 'Filter' }}
                                        </button>
                                        <a href="{{ route('doctors.index') }}" class="btn btn-outline-secondary">
                                            <i class="bx bx-refresh me-1"></i>{{ localize('global.reset') ?: 'Reset' }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Active Filters Display --}}
            @if(request()->hasAny(['search', 'department_id', 'branch_id', 'gender', 'clinic_type', 'active_status', 'join_date_from', 'join_date_to']))
                <div class="card mb-3 shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 fw-semibold">
                                    <i class="bx bx-filter me-1 text-primary"></i>{{ localize('global.active_filters') ?: 'Active Filters' }}:
                                </h6>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @if(request('search'))
                                        <span class="badge bg-primary">
                                            {{ localize('global.search') }}: {{ request('search') }}
                                            <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('department_id'))
                                        <span class="badge bg-info">
                                            {{ localize('global.department') }}: {{ $departments->find(request('department_id'))->name ?? '' }}
                                            <a href="{{ request()->fullUrlWithQuery(['department_id' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('branch_id'))
                                        <span class="badge bg-success">
                                            {{ localize('global.branch') }}: {{ $branches->find(request('branch_id'))->name ?? '' }}
                                            <a href="{{ request()->fullUrlWithQuery(['branch_id' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('gender'))
                                        <span class="badge bg-warning">
                                            {{ localize('global.gender') }}: {{ request('gender') }}
                                            <a href="{{ request()->fullUrlWithQuery(['gender' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('clinic_type'))
                                        <span class="badge bg-danger">
                                            {{ localize('global.clinic_type') }}: {{ request('clinic_type') }}
                                            <a href="{{ request()->fullUrlWithQuery(['clinic_type' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('active_status') !== null && request('active_status') !== '')
                                        <span class="badge bg-success">
                                            {{ localize('global.active_status') }}: {{ request('active_status') == '1' ? localize('global.active') : localize('global.inactive') }}
                                            <a href="{{ request()->fullUrlWithQuery(['active_status' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                    @if(request('join_date_from') || request('join_date_to'))
                                        <span class="badge bg-info">
                                            {{ localize('global.join_date') }}: {{ request('join_date_from') ?: '...' }} - {{ request('join_date_to') ?: '...' }}
                                            <a href="{{ request()->fullUrlWithQuery(['join_date_from' => null, 'join_date_to' => null]) }}" class="text-white ms-1" style="text-decoration: none;">×</a>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('doctors.index') }}" class="btn btn-sm btn-outline-danger">
                                <i class="bx bx-x me-1"></i>{{ localize('global.clear_all') ?: 'Clear All' }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Doctors Table --}}
            <div class="card shadow-sm">
                <div class="card-header bg-none border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">
                            <i class="bx bx-list-ul me-2 text-primary"></i>
                            {{ localize('global.doctors_list') ?: 'Doctors List' }}
                            <span class="badge bg-primary ms-2">{{ $doctors->total() }}</span>
                        </h5>
                        @if($doctors->total() > 0)
                            <small class="text-muted">
                                {{ localize('global.showing') ?: 'Showing' }} {{ $doctors->firstItem() }} - {{ $doctors->lastItem() }} {{ localize('global.of') ?: 'of' }} {{ $doctors->total() }}
                            </small>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($doctors->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;" class="text-center">{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.name') }}</th>
                                        <th>{{ localize('global.contact_number') }}</th>
                                        <th>{{ localize('global.department') }}</th>
                                        <th>{{ localize('global.specialization') }}</th>
                                        <th>{{ localize('global.gender') }}</th>
                                        <th>{{ localize('global.clinic_type') }}</th>
                                        <th class="text-center">{{ localize('global.active_status') }}</th>
                                        <th class="text-center" style="width: 100px;">{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($doctors as $doctor)
                                        <tr>
                                            <td class="text-center fw-semibold">{{ $doctors->firstItem() + $loop->index }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            {{ strtoupper(substr($doctor->name, 0, 1)) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $doctor->name }}</div>
                                                        @if($doctor->qualification)
                                                            <small class="text-muted">{{ $doctor->qualification }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($doctor->contact_number)
                                                    <i class="bx bx-phone me-1 text-muted"></i>{{ $doctor->contact_number }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($doctor->department)
                                                    <span class="badge bg-label-info">{{ $doctor->department->name }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                                @if($doctor->branch)
                                                    <br><small class="text-muted"><i class="bx bx-map me-1"></i>{{ $doctor->branch->name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($doctor->specialization)
                                                    <span class="text-wrap">{{ $doctor->specialization }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($doctor->gender)
                                                    @if($doctor->gender == 'Male')
                                                        <span class="badge bg-label-primary">{{ localize('global.male') }}</span>
                                                    @elseif($doctor->gender == 'Female')
                                                        <span class="badge bg-label-danger">{{ localize('global.female') }}</span>
                                                    @else
                                                        <span class="badge bg-label-secondary">{{ localize('global.other') ?: 'Other' }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($doctor->clinic_type)
                                                    <span class="badge bg-label-{{ $doctor->clinic_type == 'hospital' ? 'success' : 'warning' }}">
                                                        {{ $doctor->clinic_type == 'hospital' ? localize('global.hospital') : localize('global.clinic') }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($doctor->active_status)
                                                    <span class="badge bg-label-success">
                                                        <i class="bx bx-check-circle me-1"></i>{{ localize('global.active') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-label-danger">
                                                        <i class="bx bx-x-circle me-1"></i>{{ localize('global.inactive') ?: 'Inactive' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="{{ route('doctors.edit', $doctor) }}" 
                                                       class="btn btn-sm btn-icon btn-label-primary" 
                                                       data-bs-toggle="tooltip" 
                                                       title="{{ localize('global.edit') }}">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="card-footer bg-none border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">
                                        {{ localize('global.showing') ?: 'Showing' }} {{ $doctors->firstItem() }} - {{ $doctors->lastItem() }} {{ localize('global.of') ?: 'of' }} {{ $doctors->total() }} {{ localize('global.results') ?: 'results' }}
                                    </small>
                                </div>
                                <div>
                                    {{ $doctors->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="bx bx-user-x" style="font-size: 4rem; color: #d1d5db;"></i>
                            </div>
                            <h5 class="text-muted">{{ localize('global.no_doctors_found') ?: 'No doctors found' }}</h5>
                            <p class="text-muted mb-4">{{ localize('global.try_adjusting_filters') ?: 'Try adjusting your filters or create a new doctor.' }}</p>
                            <a href="{{ route('doctors.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i>{{ localize('global.create_doctor') ?: 'Create Doctor' }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 if available
        if ($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        }

        // Initialize tooltips
        if ($.fn.tooltip) {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }

        // Auto-submit form on filter change (optional)
        // Uncomment if you want auto-submit on filter change
        // $('#department_id, #branch_id, #gender, #clinic_type, #active_status').on('change', function() {
        //     $('#filterForm').submit();
        // });
    });
</script>
@endsection
