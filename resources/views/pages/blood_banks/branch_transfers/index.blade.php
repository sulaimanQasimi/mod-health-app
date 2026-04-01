@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="mb-0">{{ localize('global.blood_branch_transfers') }}</h4>
                <a href="{{ route('blood_banks.branch_transfers.create') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-transfer me-1"></i>{{ localize('global.request_blood_from_branch') }}
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.blood_branch_transfers_incoming') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.from_branch') }}</th>
                                    <th>{{ localize('global.blood_group') }}</th>
                                    <th>{{ localize('global.rh') }}</th>
                                    <th>{{ localize('global.component_type') }}</th>
                                    <th>{{ localize('global.quantity') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($incoming as $row)
                                    <tr>
                                        <td>{{ $loop->iteration + ($incoming->currentPage() - 1) * $incoming->perPage() }}</td>
                                        <td>{{ $row->requestingBranch?->name ?? '—' }}</td>
                                        <td>{{ $row->blood_group }}</td>
                                        <td>{{ $row->rh }}</td>
                                        <td>{{ $row->component_type }}</td>
                                        <td>{{ $row->quantity }}</td>
                                        <td>{{ $row->status }}</td>
                                        <td>
                                            <a href="{{ route('blood_banks.branch_transfers.show', $row) }}"
                                                class="btn btn-sm btn-outline-primary"><i class="bx bx-show"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            {{ localize('global.no_item_is_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">{{ $incoming->appends(['out_page' => request('out_page')])->links('pagination::bootstrap-5') }}</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.blood_branch_transfers_outgoing') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.to_branch') }}</th>
                                    <th>{{ localize('global.blood_group') }}</th>
                                    <th>{{ localize('global.rh') }}</th>
                                    <th>{{ localize('global.component_type') }}</th>
                                    <th>{{ localize('global.quantity') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($outgoing as $row)
                                    <tr>
                                        <td>{{ $loop->iteration + ($outgoing->currentPage() - 1) * $outgoing->perPage() }}</td>
                                        <td>{{ $row->supplyingBranch?->name ?? '—' }}</td>
                                        <td>{{ $row->blood_group }}</td>
                                        <td>{{ $row->rh }}</td>
                                        <td>{{ $row->component_type }}</td>
                                        <td>{{ $row->quantity }}</td>
                                        <td>{{ $row->status }}</td>
                                        <td>
                                            <a href="{{ route('blood_banks.branch_transfers.show', $row) }}"
                                                class="btn btn-sm btn-outline-primary"><i class="bx bx-show"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            {{ localize('global.no_item_is_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">{{ $outgoing->appends(['in_page' => request('in_page')])->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
