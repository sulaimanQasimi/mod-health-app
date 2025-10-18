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
                    <div class="row w-100">
                        <div class="col-md-6">
                            <h5 class="mb-0">{{ localize('global.list_departments') }}</h5>
                        </div>
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('departments.index') }}" class="d-flex">
                                <select name="category_id" class="form-control me-2">
                                    <option value="">{{ localize('global.all_categories') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary me-2">{{ localize('global.filter') }}</button>
                                @if(request('category_id'))
                                    <a href="{{ route('departments.index') }}" class="btn btn-secondary me-2">{{ localize('global.clear') }}</a>
                                @endif
                                @can('create-departments')
                                <a class="btn btn-secondary create-new btn-primary" href="{{ route('departments.create') }}"
                                   type="button">
                                    <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                              class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                                </a>
                                @endcan
                            </form>
                        </div>
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
