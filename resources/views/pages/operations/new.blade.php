@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.approved_operations') }}</h5>
                    </div>
                    <div class="card-body">


                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.operation_type') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($operations as $operation)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $operation->patient->name }}</td>
                                        <td>{{ $operation->operationType->name }}</td>
                                        <td>
                                            @if ($operation->status == '0')
                                                <span class="bx bx-x-circle text-danger"></span>
                                            @else
                                                <span class="bx bx-check-circle text-success"></span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('operations.show', $operation) }}"><i
                                                    class="bx bx-expand"></i></a>
                                            {{-- <a href="{{ route('operations.edit', $operation) }}"><i class="bx bx-message-square-edit"></i></a> --}}
                                            <!-- Using an <a> tag -->
                                            {{-- <a href="{{ route('beds.destroy', $bed) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form').submit(); }">
                        <i class="bx bx-trash"></i>
                    </a>

                    <!-- Using a <form> element -->
                    <form id="delete-form" action="{{ route('beds.destroy', $bed) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form> --}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        {{ $operations->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
