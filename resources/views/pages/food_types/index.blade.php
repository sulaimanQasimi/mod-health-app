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
                        <h5 class="mb-0">{{ localize('global.food_types') }}</h5>
                        <div class="pt-3 pt-md-0 text-end">
                            <a class="btn btn-secondary create-new btn-primary" href="{{ route('food_types.create') }}"
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
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.name') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($foodTypes as $foodType)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $foodType->name }}</td>
                                        <td>
                                            <a href="{{ route('food_types.edit', $foodType) }}"><i
                                                    class="bx bx-message-edit"></i></a>
                                                    <a href="{{ route('food_types.destroy', $foodType) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$foodType->id}}').submit(); }">
                                                        <i class="bx bx-trash text-danger"></i>
                                                    </a>

                                                    <!-- Using a <form> element -->
                                                    <form id="delete-form-{{$foodType->id}}" action="{{ route('food_types.destroy', $foodType) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="col-md-12 mt-4 mb-4">
                            {{ $foodTypes->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
