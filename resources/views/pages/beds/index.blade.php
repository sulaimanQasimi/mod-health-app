@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.beds') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        <a class="btn btn-secondary create-new btn-primary" href="{{ route('beds.create') }}"
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
            <th>{{localize('global.bed_number')}}</th>
            <th>{{localize('global.related_room')}}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($beds as $bed)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $bed->number }}</td>
                <td>{{ $bed->room->name }}</td>
                <td>
                    <a href="{{ route('beds.show', $bed) }}"><i class="bx bx-show-alt"></i></a>
                    <a href="{{ route('beds.edit', $bed) }}"><i class="bx bx-message-square-edit"></i></a>
                    <!-- Using an <a> tag -->
                    {{-- <a href="{{ route('beds.destroy', $bed) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form').submit(); }">
                        <i class="bx bx-trash"></i>
                    </a>

                    <!-- Using a <form> element -->
                    <form id="delete-form" action="{{ route('beds.destroy', $bed) }}" method="POST" style="display: none;">
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
