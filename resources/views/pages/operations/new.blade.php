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
                        <h5 class="mb-0">{{ localize('global.new_operations') }}</h5>
                    </div>
                    <div class="card-body">


                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.card_number') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.father_name') }}</th>
                                    <th>{{ localize('global.operation_type') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($operations && $operations->count() > 0)
                                @foreach ($operations as $operation)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $operation->patient->id_card ?? '-' }}</span>
                                        </td>
                                        <td>{{ $operation->patient ? $operation->patient->name : 'No Patient' }}</td>
                                        <td>
                                            <span class="text-muted">{{ $operation->patient->father_name ?? '-' }}</span>
                                        </td>
                                        <td>{{ $operation->operationType ? $operation->operationType->name : 'No Operation Type' }}</td>
                                        <td>
                                            @if ($operation->is_operation_approved == 0)
                                                <span class="bx bx-plus-circle text-danger"></span>
                                            @else
                                                <span class="bx bx-check-circle text-success"></span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('operations.show', $operation) }}"><i
                                                    class="bx bx-expand"></i></a>

                                            @if($operation->patient)
                                            <a href="{{ route('patients.history', $operation->patient->id) }}"><i
                                                class="bx bx-history"></i></a>
                                            @else
                                            <span class="text-muted"><i class="bx bx-history"></i></span>
                                            @endif
                                            
                                        </td>
                                    </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="5" class="text-center">No new operations found</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if($operations && $operations->count() > 0)
                    <div class="d-flex justify-content-end">
                        {{ $operations->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
