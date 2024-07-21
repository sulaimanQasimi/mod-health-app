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
                                                <a href="{{ route('diseases.edit', $disease) }}" class="btn btn-primary btn-sm me-2">
                                                    {{ localize('global.edit') }}
                                                </a>
                                                <form action="{{ route('diseases.destroy', $disease) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        {{ localize('global.delete') }}
                                                    </button>
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