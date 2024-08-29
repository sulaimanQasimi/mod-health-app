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
                        <h5 class="mb-0">{{ localize('global.diseases') }}</h5>
                        <a href="{{ route('diseases.create') }}" class="btn btn-primary">
                            {{ localize('global.create_disease') }}
                        </a>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.name') }}</th>
                                    <th>{{ localize('global.description') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($diseases as $disease)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $disease->name }}</td>
                                        <td>{{ $disease->description }}</td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="{{ route('diseases.edit', $disease) }}">
                                                    <i class="bx bx-message-edit"></i></a>
                                                    <a href="{{ route('diseases.destroy', $disease) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$disease->id}}').submit(); }">
                                                        <i class="bx bx-trash text-danger"></i>
                                                    </a>

                                                    <!-- Using a <form> element -->
                                                    <form id="delete-form-{{$disease->id}}" action="{{ route('diseases.destroy', $disease) }}" method="POST" style="display: none;">
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
