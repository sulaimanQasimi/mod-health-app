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
                        <h5 class="mb-0">{{ localize('global.reserved_operations') }}</h5>
                    </div>
                    <div class="card-body">


                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.operation_type') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.reserve_reason') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reservedOperations as $operation)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $operation->patient->name }}</td>
                                        <td>{{ $operation->operationType->name }}</td>
                                        <td>
                                            @if ($operation->is_reserved == 0)
                                                <span class="badge bg-success">{{localize('global.unreserved')}}</span>
                                            @else
                                            <span class="badge bg-warning">{{localize('global.reserved')}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{$operation->reserve_reason}}
                                        </td>
                                        <td>
                                            <a href="{{ route('operations.show', $operation) }}"><i
                                                    class="bx bx-expand"></i></a>

                                            <a href="{{ route('patients.history', $operation->patient->id) }}"><i
                                                class="bx bx-history"></i></a>
                                            
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        {{ $reservedOperations->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
