@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.create_new_permission') }}</h5>
                </div>
                <div class="card-body">


<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Last Name</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sections as $section)
            <tr>
                <td>{{ $section->name }}</td>
                <td>{{ $section->department->name }}</td>
                <td>
                    <a href="{{ route('sections.show', $section) }}">View</a>
                    <a href="{{ route('sections.edit', $section) }}">Edit</a>
                    <form action="{{ route('sections.destroy', $section) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
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
