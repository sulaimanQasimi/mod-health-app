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
                    <h5 class="mb-0">{{ localize('global.list_section') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        <a class="btn btn-secondary create-new btn-primary" href="{{ route('sections.create') }}"
                           type="button">
                            <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                      class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                        </a>
                    </div>
                </div>
                <div class="card-body">


<table class="table table-striped">
    <thead>
        <tr>
            <th>{{localize('global.number')}}</th>
            <th>{{localize('global.name')}}</th>
            <th>{{localize('global.departments')}}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sections as $section)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $section->name }}</td>
                <td>{{ $section->department->name }}</td>
                <td>
                    <a href="{{ route('sections.show', $section) }}"><i class="bx bx-show-alt"></i></a>
                    <a href="{{ route('sections.edit', $section) }}"><i class="bx bx-message-square-edit"></i></a>
                    <!-- Using an <a> tag -->
                    {{-- <a href="{{ route('sections.destroy', $section) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form').submit(); }">
                        <i class="bx bx-trash"></i>
                    </a>

                    <!-- Using a <form> element -->
                    <form id="delete-form" action="{{ route('sections.destroy', $section) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form> --}}
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
