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
                    <h5 class="mb-0">{{ localize('global.operation_types') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        @can('create-operation-types')
                        <a class="btn btn-secondary create-new btn-primary" href="{{ route('operation_types.create') }}"
                           type="button">
                            <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                      class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                        </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">

            {{-- Filter --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-none border-0">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bx bx-filter-alt text-primary me-2"></i>{{ localize('global.filter') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('operation_types.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label fw-semibold">
                                    <i class="bx bx-search me-1 text-primary"></i>{{ localize('global.name') }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white"><i class="bx bx-search"></i></span>
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="{{ request('search') }}"
                                           placeholder="{{ localize('global.search_by_name') }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="department_id" class="form-label fw-semibold">
                                    <i class="bx bx-building me-1 text-info"></i>{{ localize('global.department') }}
                                </label>
                                <select class="form-select" id="department_id" name="department_id">
                                    <option value="">{{ localize('global.all') ?: 'All' }}</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bx bx-filter me-1"></i>{{ localize('global.filter') }}
                                </button>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="{{ route('operation_types.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="bx bx-refresh me-1"></i>{{ localize('global.reset') ?: 'Reset' }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>{{localize('global.number')}}</th>
            <th>{{localize('global.name')}}</th>
            <th>{{localize('global.department')}}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($operationTypes as $operationType)
            <tr>
                <td>{{ $loop->iteration}}</td>
                <td>{{ $operationType->name }}</td>
                <td>{{ $operationType->department->name ?? '—' }}</td>
                <td>
                    {{-- <a href="{{ route('operation_types.show', $operationType) }}"><i class="bx bx-show-alt"></i></a> --}}
                    @can('edit-operation-types')
                    <a href="{{ route('operation_types.edit', $operationType) }}"><i class="bx bx-message-edit"></i></a>
                    @endcan
                    @can('delete-operation-types')
                    <a href="{{ route('operation_types.destroy', $operationType) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$operationType->id}}').submit(); }">
                        <i class="bx bx-trash text-danger"></i>
                    </a>
                    @endcan
                    <!-- Using a <form> element -->
                    <form id="delete-form-{{$operationType->id}}" action="{{ route('operation_types.destroy', $operationType) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">{{ localize('global.no_data_found') ?: 'No data found' }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>
            </div>
        </div>
    </div>
</div>

@endsection
