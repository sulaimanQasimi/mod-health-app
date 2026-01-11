@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <!-- Content -->
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">

            <!-- Filters Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.filters') }}</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('patients.index') }}" id="filter-form" class="row g-3">
                        <div class="col-md-2">
                            <label for="name" class="form-label">{{ localize('global.name') }}</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                value="{{ request('name') }}" placeholder="{{ localize('global.search_by_name') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="father_name" class="form-label">{{ localize('global.father_name') }}</label>
                            <input type="text" class="form-control" id="father_name" name="father_name" 
                                value="{{ request('father_name') }}" placeholder="{{ localize('global.search_by_father_name') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="last_name" class="form-label">{{ localize('global.last_name') }}</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                value="{{ request('last_name') }}" placeholder="{{ localize('global.search_by_last_name') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="phone" class="form-label">{{ localize('global.phone') }}</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                value="{{ request('phone') }}" placeholder="{{ localize('global.search_by_phone') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="card_search" class="form-label">{{ localize('global.id_card') }} {{ localize('global.search') }}</label>
                            <input type="text" class="form-control" id="card_search" name="card_search" 
                                value="{{ request('card_search') }}" placeholder="{{ localize('global.search_by_card') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="militery_type_id" class="form-label">{{ localize('global.militery_type') }}</label>
                            <select class="form-select" id="militery_type_id" name="militery_type_id">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($militeryTypes as $militeryType)
                                    <option value="{{ $militeryType->id }}" {{ request('militery_type_id') == $militeryType->id ? 'selected' : '' }}>
                                        {{ $militeryType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="province_id" class="form-label">{{ localize('global.province') }}</label>
                            <select class="form-select" id="province_id" name="province_id">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province->id }}" {{ request('province_id') == $province->id ? 'selected' : '' }}>
                                        {{ $province->name_dr }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="gender" class="form-label">{{ localize('global.gender') }}</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">{{ localize('global.all') }}</option>
                                <option value="0" {{ request('gender') == '0' ? 'selected' : '' }}>{{ localize('global.male') }}</option>
                                <option value="1" {{ request('gender') == '1' ? 'selected' : '' }}>{{ localize('global.female') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="job_category" class="form-label">{{ localize('global.job_category') }}</label>
                            <select class="form-select" id="job_category" name="job_category">
                                <option value="">{{ localize('global.all') }}</option>
                                <option value="0" {{ request('job_category') == '0' ? 'selected' : '' }}>{{ localize('global.military') }}</option>
                                <option value="1" {{ request('job_category') == '1' ? 'selected' : '' }}>{{ localize('global.civilian') }}</option>
                            </select>
                        </div>
                        <div class="col-md-12 d-flex justify-content-end gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search"></i> {{ localize('global.search') }}
                            </button>
                            <a href="{{ route('patients.index') }}" class="btn btn-secondary">
                                <i class="bx bx-refresh"></i> {{ localize('global.reset') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.patients_list') }}</h5>
                    <div>
                        <a href="{{ route('patients.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus"></i> {{ localize('global.create') }}
                        </a>
                    </div>
                </div>
                <div class="card-datatable table-responsive">
                    <table class="datatables-basic table border-top">
                        <thead>
                            <tr>
                                <th>{{ localize('global.id') }}</th>
                                <th>{{ localize('global.id_card') }}</th>
                                <th>{{ localize('global.name') }}</th>
                                <th>{{ localize('global.last_name') }}</th>
                                <th>{{ localize('global.father_name') }}</th>
                                <th>{{ localize('global.province') }} / {{ localize('global.district') }}</th>
                                <th>{{ localize('global.age') }}</th>
                                <th>{{ localize('global.militery_type') }}</th>
                                <th>{{ localize('global.phone') }}</th>
                                <th>{{ localize('global.created_by') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($patients as $patient)
                                <tr>
                                    <td>{{ $patient->id }}</td>
                                    <td>{{ $patient->id_card ?? '-' }}</td>
                                    <td>{{ $patient->name }}</td>
                                    <td>{{ $patient->last_name }}</td>
                                    <td>{{ $patient->father_name ?? '-' }}</td>
                                    <td>
                                        @php
                                            $provinceName = $patient->province?->name_dr ?? '-';
                                            $districtName = $patient->district?->name_dr ?? '-';
                                            echo ($provinceName !== '-' && $districtName !== '-') ? $provinceName . ' / ' . $districtName : ($provinceName !== '-' ? $provinceName : ($districtName !== '-' ? $districtName : '-'));
                                        @endphp
                                    </td>
                                    <td>{{ $patient->age ?? '-' }}</td>
                                    <td>{{ $patient->militeryType?->name ?? '-' }}</td>
                                    <td>{{ $patient->phone }}</td>
                                    <td>
                                        @if($patient->creator)
                                            {{ $patient->creator->name }} {{ $patient->creator->last_name }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('patients.show', $patient->id) }}"
                                            class="btn btn-sm btn-icon text-primary"><i class="bx bx-expand"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="col-md-12 mt-4 mb-4">
                        {{ $patients->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-css')
<style>
    .filter-card {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
    }
    
    .filter-card .card-header {
        background-color: #e9ecef;
        border-bottom: 1px solid #dee2e6;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
    }
    
    .btn-filter {
        min-width: 40px;
    }
    
    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    
    .pagination {
        margin-bottom: 0;
    }
</style>
@endpush

@push('custom-js')
<script>
$(document).ready(function() {
    // Auto-submit form when select values change
    $('select[name="militery_type_id"], select[name="province_id"], select[name="gender"], select[name="job_category"]').change(function() {
        $(this).closest('form').submit();
    });
    
    // Clear search on refresh button click
    $('.btn-secondary').click(function() {
        $('input[name="name"]').val('');
        $('input[name="father_name"]').val('');
        $('input[name="last_name"]').val('');
        $('input[name="phone"]').val('');
        $('input[name="card_search"]').val('');
    });
    
    // Add loading state to search button
    $('form').submit(function() {
        $('.btn-primary').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i>');
    });
});
</script>
@endpush
