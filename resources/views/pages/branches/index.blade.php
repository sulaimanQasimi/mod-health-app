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
                    <h5 class="mb-0">{{ localize('global.branches_list') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        @can('create-branches')
                        <a class="btn btn-secondary create-new btn-primary" href="{{ route('branches.create') }}"
                           type="button">
                            <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                      class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{localize('global.number')}}</th>
                                <th>{{localize('global.name')}}</th>
                                <th>{{localize('global.address')}}</th>
                                <th>{{localize('global.actions')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($branches as $branch)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $branch->name }}</td>
                                    <td>{{ $branch->address }}</td>
                                    <td>
                                        <div class="d-flex">
                                            @can('edit-branches')
                                            <a href="{{ route('branches.edit', $branch) }}">
                                                <i class="bx bx-message-edit"></i></a>
                                                @endcan
                                                @can('delete-branches')
                                                <a href="{{ route('branches.destroy', $branch) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$branch->id}}').submit(); }">
                                                    <i class="bx bx-trash text-danger"></i>
                                                </a>
                                                @endcan
                                                <!-- Using a <form> element -->
                                                <form id="delete-form-{{$branch->id}}" action="{{ route('branches.destroy', $branch) }}" method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                        </div>
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
