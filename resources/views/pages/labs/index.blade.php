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
                    <h5 class="mb-0">{{ localize('global.new_tests') }}</h5>
                    {{-- <div class="pt-3 pt-md-0 text-end">
                        <a class="btn btn-secondary create-new btn-primary" href="{{ route('lab_types.create') }}"
                           type="button">
                            <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                      class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                        </a>
                    </div> --}}
                </div>

                <div class="card-body">


<table class="table table-striped">
    <thead>
        <tr>
            <th>{{localize('global.number')}}</th>
            <th>{{localize('global.name')}}</th>
            <th>{{localize('global.patient_name')}}</th>
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($labs as $lab)
            <tr>
                <td>{{ $loop->iteration}}</td>
                <td>{{ $lab->labType->name }}</td>
                <td>{{ $lab->patient->name }}</td>
                <td>
                    {{-- <a href="{{ route('labs.show', $lab) }}"><i class="bx bx-show-alt"></i></a> --}}
                    <a href="{{ route('lab_tests.edit', $lab) }}"><i class="bx bx-message-square-edit"></i></a>
                    {{-- <form action="{{ route('doctors.destroy', $doctor) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
                    </form> --}}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="col-md-12 mt-4 mb-4">
    {{$labs->links('pagination::bootstrap-4')}}
</div>
</div>
            </div>
        </div>
    </div>
</div>

@endsection
