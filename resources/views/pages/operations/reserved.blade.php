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
                    @include('pages.operations.partials.filter', ['filterRoute' => 'operations.reserved'])
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">
                                {{ localize('global.showing') }} {{ $reservedOperations->firstItem() ?? 0 }}
                                {{ localize('global.to') }} {{ $reservedOperations->lastItem() ?? 0 }}
                                {{ localize('global.of') }} {{ $reservedOperations->total() }}
                                {{ localize('global.results') }}
                            </span>
                        </div>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.card_number') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.father_name') }}</th>
                                    <th>{{ localize('global.operation_type') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.reserve_reason') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($reservedOperations && $reservedOperations->count() > 0)
                                @foreach ($reservedOperations as $operation)
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
                                            @if ($operation->is_reserved == 0)
                                                <span class="badge bg-success">{{ localize('global.unreserved') }}</span>
                                            @else
                                            <span class="badge bg-warning">{{ localize('global.reserved') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{$operation->reserve_reason ?? 'No Reason'}}
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
                                    <td colspan="6" class="text-center">No reserved operations found</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if($reservedOperations && $reservedOperations->hasPages())
                    <div class="card-footer d-flex justify-content-end">
                        {{ $reservedOperations->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
