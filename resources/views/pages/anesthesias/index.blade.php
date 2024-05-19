@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.anesthesias') }}</h5>
                </div>
                <div class="card-body">


<table class="table table-striped">
    <thead>
        <tr>
            <th>{{localize('global.number')}}</th>
            <th>{{localize('global.patient_name')}}</th>
            <th>{{localize('global.operation_type')}}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($anesthesias as $anesthesia)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $anesthesia->patient->name }}</td>
                <td>{{ $anesthesia->operationType->name }}</td>
                <td>
                    <a href="{{ route('anesthesias.show', $anesthesia) }}"><i class="bx bx-show-alt"></i></a>
                    {{-- <a href="{{ route('anesthesias.edit', $anesthesia) }}"><i class="bx bx-message-square-edit"></i></a> --}}
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
