@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <!-- Filters Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.filters') }}</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('departments.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="category_id" class="form-label">{{ localize('global.category') }}</label>
                            <select class="form-select select2" id="category_id" name="category_id">
                                <option value="">{{ localize('global.all_categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="{{ route('departments.index') }}" class="btn btn-secondary">
                                <i class="bx bx-refresh"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.list_departments') }}</h5>
                        <div class="pt-3 pt-md-0 text-end">
                            @can('create-departments')
                            <a class="btn btn-primary btn-lg" href="{{ route('departments.create') }}">
                                <i class="bx bx-plus me-2"></i>{{ localize('global.create') }}
                            </a>
                            @endcan
                        </div>
                    </div>
                <div class="card-body">


<table class="table table-striped">
    <thead>
        <tr>
            <th>{{localize('global.number')}}</th>
            <th>{{localize('global.name')}}</th>
            <th>{{localize('global.category')}}</th>
            <th>{{localize('global.related_section')}}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($departments as $department)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $department->name }}</td>
                <td>{{ $department->category->name ?? '-' }}</td>
                <td>
                    @foreach ($department->sections as $section )
                    <span class="badge bg-primary">{{ $section->name }}</span>
                    @endforeach
                </td>
                <td>
                    {{-- <a href="{{ route('departments.show', $department) }}"><i class="bx bx-show-alt"></i></a> --}}
                    @can('edit-departments')
                    <a href="{{ route('departments.edit', $department) }}"><i class="bx bx-message-edit"></i></a>
                    @endcan
                    @can('delete-departments')
                    <!-- Using an <a> tag -->
                    <a href="{{ route('departments.destroy', $department) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$department->id}}').submit(); }">
                        <i class="bx bx-trash text-danger"></i>
                    </a>
                    @endcan
                    <!-- Using a <form> element -->
                    <form id="delete-form-{{ $department->id }}" action="{{ route('departments.destroy', $department) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        placeholder: '{{ localize("global.select_category") }}',
        allowClear: true,
        width: '100%'
    });
});
</script>
@endsection
