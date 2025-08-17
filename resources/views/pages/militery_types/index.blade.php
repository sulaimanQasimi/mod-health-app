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
                    <h5 class="mb-0">{{ localize('global.militery_types') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        <a class="btn btn-secondary create-new btn-primary" href="{{ route('militery_types.create') }}" type="button">
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
                            @foreach ($militeryTypes as $militeryType)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $militeryType->name }}</td>
                                    <td>
                                        <a href="{{ route('militery_types.edit', $militeryType) }}"><i class="bx bx-message-edit"></i></a>
                                        <a href="{{ route('militery_types.destroy', $militeryType) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$militeryType->id}}').submit(); }">
                                            <i class="bx bx-trash text-danger"></i>
                                        </a>
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
