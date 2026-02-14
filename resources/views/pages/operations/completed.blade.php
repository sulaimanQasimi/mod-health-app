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
                        <h5 class="mb-0">{{ localize('global.completed_operations') }}</h5>
                    </div>
                    @include('pages.operations.partials.filter', ['filterRoute' => 'operations.completed'])
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">
                                {{ localize('global.showing') }} {{ $operations->firstItem() ?? 0 }}
                                {{ localize('global.to') }} {{ $operations->lastItem() ?? 0 }}
                                {{ localize('global.of') }} {{ $operations->total() }}
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
                                    <th>{{ localize('global.scrub_nurse') }}</th>
                                    <th>{{ localize('global.circulation_nurse') }}</th>
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
                                        <td>{{ $operation->scrub_nurse ? $operation->scrub_nurse->full_name : '-' }}</td>
                                        <td>{{ $operation->circulation_nurse ? $operation->circulation_nurse->full_name : '-' }}</td>
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
                                        </td>
                                    </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="9" class="text-center">No completed operations found</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if($operations && $operations->hasPages())
                    <div class="card-footer d-flex justify-content-end">
                        {{ $operations->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
