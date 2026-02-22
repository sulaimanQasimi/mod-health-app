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
                    <h5 class="mb-0">{{ localize('global.floors') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        @can('create-floors')
                        <a class="btn btn-secondary create-new btn-primary" href="{{ route('floors.create') }}"
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
                    <form method="GET" action="{{ route('floors.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label fw-semibold">
                                    <i class="bx bx-search me-1 text-primary"></i>{{ localize('global.search') }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white"><i class="bx bx-search"></i></span>
                                    <input type="text" class="form-control" id="search" name="search"
                                           value="{{ request('search') }}"
                                           placeholder="{{ localize('global.name') }}" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="branch_id" class="form-label fw-semibold">
                                    <i class="bx bx-building me-1 text-info"></i>{{ localize('global.branch') }}
                                </label>
                                <select class="form-select" id="branch_id" name="branch_id">
                                    <option value="">{{ localize('global.all') ?: 'All' }}</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-filter me-1"></i>{{ localize('global.filter') }}
                                </button>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="{{ route('floors.index') }}" class="btn btn-outline-secondary">
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
            <th>{{ localize('global.branch') }}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($floors as $floor)
            <tr>
                <td>{{ $floors->firstItem() ? ($loop->iteration + $floors->firstItem() - 1) : $loop->iteration }}</td>
                <td>{{ $floor->name }}</td>
                <td>{{ $floor->branch->name ?? '—' }}</td>
                <td>
                    {{-- <a href="{{ route('floors.show', $floor) }}"><i class="bx bx-show-alt"></i></a> --}}
                    @can('edit-floors')
                    <a href="{{ route('floors.edit', $floor) }}"><i class="bx bx-message-edit"></i></a>
                    @endcan
                    @can('delete-floors')
                    <!-- Using an <a> tag -->
                    <a href="{{ route('floors.destroy', $floor) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$floor->id}}').submit(); }">
                        <i class="bx bx-trash text-danger"></i>
                    </a>
                    @endcan
                    <!-- Using a <form> element -->
                    <form id="delete-form-{{$floor->id}}" action="{{ route('floors.destroy', $floor) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="d-flex justify-content-center mt-3">
    {{ $floors->links() }}
</div>
</div>
            </div>
        </div>
    </div>
</div>

@endsection
