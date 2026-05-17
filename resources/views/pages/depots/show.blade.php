@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bx bx-show-alt me-1"></i>{{ $depot->name }}</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('depots.edit', $depot) }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-edit me-1"></i>Edit
                </a>
                <a href="{{ route('depots.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">{{ localize('global.depot.name') }}</div>
                    <div class="fw-semibold">{{ $depot->name }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">{{ localize('global.depot.address') }}</div>
                    <div>{{ $depot->address ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">{{ localize('global.depot.branch') }}</div>
                    <div>{{ $depot->branch?->name ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">{{ localize('global.depot.department') }}</div>
                    <div>{{ $depot->department?->name ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">{{ localize('global.depot.pharmacy') }}</div>
                    <div>{{ $depot->pharmacy?->name ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">{{ localize('global.depot.parent_depot') }}</div>
                    <div>{{ $depot->parentDepot?->name ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">{{ localize('global.depot.is_active') }}</div>
                    <span class="badge bg-{{ $depot->is_active ? 'success' : 'secondary' }}">{{ $depot->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">{{ localize('global.depot.is_base') }}</div>
                    <span class="badge bg-{{ $depot->is_base ? 'primary' : 'secondary' }}">{{ $depot->is_base ? 'Base' : 'Child' }}</span>
                </div>
            </div>

            <div class="mt-4">
                <h6 class="mb-3">Depot Users</h6>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($depot->activeUsers as $user)
                                <tr>
                                    <td>{{ $user->name }} {{ $user->last_name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge bg-primary">{{ ucfirst($user->pivot->role) }}</span></td>
                                    <td>{{ $user->pivot->joined_at ? \Carbon\Carbon::parse($user->pivot->joined_at)->format('Y-m-d') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">{{ localize('global.no_data_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
