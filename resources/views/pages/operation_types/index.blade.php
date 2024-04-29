@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.operation_types') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        <a class="btn btn-secondary create-new btn-primary" href="{{ route('operation_types.create') }}"
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
            <th>{{localize('global.actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($operationTypes as $operationType)
            <tr>
                <td>{{ $loop->iteration}}</td>
                <td>{{ $operationType->name }}</td>
                <td>
                    <a href="{{ route('operation_types.show', $operationType) }}"><i class="bx bx-show-alt"></i></a>
                    <a href="{{ route('operation_types.edit', $operationType) }}"><i class="bx bx-message-square-edit"></i></a>
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
</div>
            </div>
        </div>
    </div>
</div>

@endsection
