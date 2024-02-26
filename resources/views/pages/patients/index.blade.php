@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.patients_list') }}</h5>
                </div>
                <div class="card-body">


<table class="table table-striped">
    <thead>
        <tr>
            <th>{{localize('global.name')}}</th>
            <th>{{localize('global.last_name')}}</th>
            <th>{{localize('global.phone')}}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($patients as $patient)
            <tr>
                <td>{{ $patient->name }}</td>
                <td>{{ $patient->last_name }}</td>
                <td>{{ $patient->phone }}</td>
                <td>
                    <a href="{{ route('patients.show', $patient) }}"><i class="bx bx-show-alt"></i></a>
                    <a href="{{ route('patients.edit', $patient) }}"><i class="bx bx-message-square-edit"></i></a>
                    <!-- Using an <a> tag -->
                    {{-- <a href="{{ route('patients.destroy', $patient) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form').submit(); }">
                        <i class="bx bx-trash"></i>
                    </a>

                    <!-- Using a <form> element -->
                    <form id="delete-form" action="{{ route('patients.destroy', $patient) }}" method="POST" style="display: none;">
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
