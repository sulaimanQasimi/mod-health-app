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
                        <h5 class="mb-0">{{ localize('global.medicine_usage_types') }}</h5>
                        <a href="{{ route('medicine_usage_types.create') }}" class="btn btn-primary">
                            {{ localize('global.create_medicine_usage_type') }}
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
                                @foreach ($medicineUsageTypes as $medicineUsageType)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $medicineUsageType->name }}</td>
                                        <td>{{ $medicineUsageType->description }}</td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="{{ route('medicine_usage_types.edit', $medicineUsageType) }}" class="btn btn-primary btn-sm me-2">
                                                    {{ localize('global.edit') }}
                                                </a>
                                                <form action="{{ route('medicine_usage_types.destroy', $medicineUsageType) }}" method="POST">
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