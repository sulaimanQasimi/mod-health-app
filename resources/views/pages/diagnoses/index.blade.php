@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.current_diagnoses') }}</h5>
                    </div>

                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{localize('global.number')}}</th>
                                    <th>{{localize('global.patient_name')}}</th>
                                    <th>{{localize('global.description')}}</th>
                                    <th>{{localize('global.actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($diagnoses as $diagnose)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $diagnose->patient->name }}</td>
                                        <td>{{ $diagnose->description }}</td>
                                        <td>
                                            <a href="{{ route('diagnoses.show', $diagnose) }}"><i class="bx bx-show-alt"></i></a>
                                            <a href="{{ route('diagnoses.edit', $diagnose) }}"><i class="bx bx-message-square-edit"></i></a>
                                            <!-- Using an <a> tag -->
                                            {{-- <a href="{{ route('diagnoses.destroy', $diagnose) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form').submit(); }">
                                                <i class="bx bx-trash"></i>
                                            </a>

                                            <!-- Using a <form> element -->
                                            <form id="delete-form" action="{{ route('diagnoses.destroy', $diagnose) }}" method="POST" style="display: none;">
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
