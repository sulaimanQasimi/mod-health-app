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
                                    <th>Patient's Name</th>
                                    <th>Discription</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($diagnoses as $diagnose)
                                    <tr>
                                        <td>{{ $diagnose->patient->name }}</td>
                                        <td>{{ $diagnose->description }}</td>
                                        <td>
                                            <a href="{{ route('diagnoses.show', $diagnose) }}">View</a>
                                            <a href="{{ route('diagnoses.edit', $diagnose) }}">Edit</a>
                                            <form action="{{ route('diagnoses.destroy', $diagnose) }}" method="POST">
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
