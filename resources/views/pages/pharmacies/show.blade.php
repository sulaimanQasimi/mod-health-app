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
                    <h5 class="mb-0">{{ localize('global.pharmacy_details') }}</h5>
                    <div class="pt-3 pt-md-0 text-end">
                        <a href="{{ route('pharmacies.index') }}" class="btn btn-secondary me-2">
                            <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
                        </a>
                        @can('pharmacy.edit')
                            <a href="{{ route('pharmacies.edit', $pharmacy->id) }}" class="btn btn-primary">
                                <i class="bx bx-edit me-1"></i>{{ localize('global.edit') }}
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.pharmacy_name') }}</label>
                                <p class="form-control-plaintext">{{ $pharmacy->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.pharmacy_phone') }}</label>
                                <p class="form-control-plaintext">{{ $pharmacy->phone }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.pharmacy_address') }}</label>
                                <p class="form-control-plaintext">{{ $pharmacy->address }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.pharmacy_users') }}</label>
                                <div>
                                    @if($pharmacy->activeUsers->count() > 0)
                                        @foreach($pharmacy->activeUsers as $user)
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                                        {{ $user->name[0] }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="fw-semibold">{{ $user->name }} {{ $user->last_name }}</span>
                                                    <small class="text-muted d-block">{{ $user->email }}</small>
                                                    <span class="badge bg-{{ $user->pivot->role == 'manager' ? 'danger' : ($user->pivot->role == 'staff' ? 'primary' : 'secondary') }} mt-1">
                                                        {{ ucfirst($user->pivot->role) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                        @can('pharmacy.manage_users')
                                            <a href="{{ route('pharmacies.manage-users', $pharmacy->id) }}" class="btn btn-sm btn-outline-primary mt-2">
                                                <i class="bx bx-user-plus me-1"></i>{{ localize('global.manage_users') }}
                                            </a>
                                        @endcan
                                    @else
                                        <p class="form-control-plaintext text-muted">{{ localize('global.no_users_assigned') }}</p>
                                        @can('pharmacy.manage_users')
                                            <a href="{{ route('pharmacies.manage-users', $pharmacy->id) }}" class="btn btn-sm btn-primary">
                                                <i class="bx bx-user-plus me-1"></i>{{ localize('global.assign_users') }}
                                            </a>
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(isset($statistics))
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.total_users') }}</label>
                                <p class="form-control-plaintext">{{ $statistics['total_users'] }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.managers') }}</label>
                                <p class="form-control-plaintext">{{ $statistics['managers_count'] }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.total_incomes') }}</label>
                                <p class="form-control-plaintext">{{ $statistics['total_incomes'] }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.total_outcomes') }}</label>
                                <p class="form-control-plaintext">{{ $statistics['total_outcomes'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.created_at') }}</label>
                                <p class="form-control-plaintext">{{ $pharmacy->created_at ? verta($pharmacy->created_at)->format('Y-m-d H:i:s') : localize('global.not_available') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.updated_at') }}</label>
                                <p class="form-control-plaintext">{{ $pharmacy->updated_at ? verta($pharmacy->updated_at)->format('Y-m-d H:i:s') : localize('global.not_available') }}</p>
                            </div>
                        </div>
                    </div>
                    @if($pharmacy->createdBy)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.created_by') }}</label>
                                <p class="form-control-plaintext">{{ $pharmacy->createdBy->name }} {{ $pharmacy->createdBy->last_name }}</p>
                            </div>
                        </div>
                        @if($pharmacy->updatedBy)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">{{ localize('global.updated_by') }}</label>
                                <p class="form-control-plaintext">{{ $pharmacy->updatedBy->name }} {{ $pharmacy->updatedBy->last_name }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
