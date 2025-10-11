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
                    <h5 class="mb-0">{{ localize('global.lab_types') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        @can('create-lab-types')
                        <a class="btn btn-secondary create-new btn-primary" href="{{ route('lab_types.create') }}"
                           type="button">
                            <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                      class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                        </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('lab_types.index') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">{{ localize('global.search') }}</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="{{ localize('global.search_placeholder') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="branch_id" class="form-label">{{ localize('global.branch') }}</label>
                                <select class="form-select" id="branch_id" name="branch_id">
                                    <option value="">{{ localize('global.all_branches') }}</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="section_id" class="form-label">{{ localize('global.section') }}</label>
                                <select class="form-select" id="section_id" name="section_id">
                                    <option value="">{{ localize('global.all_sections') }}</option>
                                    @foreach($labTypeSections as $section)
                                        <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                                            {{ $section->section }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="parent_id" class="form-label">{{ localize('global.parent') }}</label>
                                <select class="form-select" id="parent_id" name="parent_id">
                                    <option value="">{{ localize('global.all_parents') }}</option>
                                    <option value="null" {{ request('parent_id') === 'null' ? 'selected' : '' }}>
                                        {{ localize('global.no_parent') }}
                                    </option>
                                    @foreach($parentLabTypes as $parent)
                                        <option value="{{ $parent->id }}" {{ request('parent_id') == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="sort_by" class="form-label">{{ localize('global.sort_by') }}</label>
                                <select class="form-select" id="sort_by" name="sort_by">
                                    <option value="name" {{ request('sort_by', 'name') == 'name' ? 'selected' : '' }}>
                                        {{ localize('global.name') }}
                                    </option>
                                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>
                                        {{ localize('global.created_at') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label for="sort_order" class="form-label">{{ localize('global.order') }}</label>
                                <select class="form-select" id="sort_order" name="sort_order">
                                    <option value="asc" {{ request('sort_order', 'asc') == 'asc' ? 'selected' : '' }}>
                                        {{ localize('global.asc') }}
                                    </option>
                                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>
                                        {{ localize('global.desc') }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bx bx-search"></i> {{ localize('global.search') }}
                                </button>
                                <a href="{{ route('lab_types.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-refresh"></i> {{ localize('global.clear') }}
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Results Summary -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="text-muted">
                                {{ localize('global.showing') }} {{ $labTypes->firstItem() ?? 0 }} 
                                {{ localize('global.to') }} {{ $labTypes->lastItem() ?? 0 }} 
                                {{ localize('global.of') }} {{ $labTypes->total() }} {{ localize('global.results') }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center">
                            <label for="per_page" class="form-label me-2 mb-0">{{ localize('global.per_page') }}:</label>
                            <select class="form-select form-select-sm" id="per_page" name="per_page" style="width: auto;" onchange="changePerPage(this.value)">
                                <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                                <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', 15) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{localize('global.number')}}</th>
                                <th>{{localize('global.name')}}</th>
                                <th>{{localize('global.branch')}}</th>
                                <th>{{localize('global.section')}}</th>
                                <th>{{localize('global.parent')}}</th>
                                <th>{{localize('global.actions')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($labTypes as $labType)
                                <tr>
                                    <td>{{ $labTypes->firstItem() + $loop->index }}</td>
                                    <td>{{ $labType->name }}</td>
                                    <td>{{ $labType->branch->name ?? '-' }}</td>
                                    <td>{{ $labType->section->section ?? '-' }}</td>
                                    <td>{{ $labType->parent->name ?? '-' }}</td>
                                    <td>
                                        {{-- <a href="{{ route('lab_types.show', $labType) }}"><i class="bx bx-show-alt"></i></a> --}}
                                        @can('edit-labs')
                                        <a href="{{ route('lab_types.edit', $labType) }}"><i class="bx bx-message-edit"></i></a>
                                        @endcan
                                        @can('create-lab-types')
                                        <a href="{{ route('lab_types.destroy', $labType) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$labType->id}}').submit(); }">
                                            <i class="bx bx-trash text-danger"></i>
                                        </a>
                                        @endcan
                                        <!-- Using a <form> element -->
                                        <form id="delete-form-{{$labType->id}}" action="{{ route('lab_types.destroy', $labType) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ localize('global.no_records_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            <small>
                                {{ localize('global.page') }} {{ $labTypes->currentPage() }} {{ localize('global.of') }} {{ $labTypes->lastPage() }}
                            </small>
                        </div>
                        <div>
                            {{ $labTypes->links('pagination::bootstrap-5') }}
                        </div>
                        <div class="text-muted">
                            <small>
                                {{ $labTypes->total() }} {{ localize('global.results') }} {{ localize('global.total') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function changePerPage(value) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', value);
    url.searchParams.delete('page'); // Reset to first page when changing per_page
    window.location.href = url.toString();
}

// Auto-submit form when sort options change
document.addEventListener('DOMContentLoaded', function() {
    const sortBy = document.getElementById('sort_by');
    const sortOrder = document.getElementById('sort_order');
    
    if (sortBy) {
        sortBy.addEventListener('change', function() {
            document.querySelector('form').submit();
        });
    }
    
    if (sortOrder) {
        sortOrder.addEventListener('change', function() {
            document.querySelector('form').submit();
        });
    }
});
</script>
@endpush
