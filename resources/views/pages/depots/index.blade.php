@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
    @endif

    <x-depot.nav-tabs active="index" :urls="$navUrls ?? []" :permissions="$navPermissions ?? null" />

    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0">{{ localize('global.depot.title') }}</h5>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('depots.transactions.index') }}">
                    <i class="bx bx-list-ul me-1"></i>{{ localize('global.depot.transactions') }}
                </a>
                @if($navPermissions['requests'] ?? true)
                    <a class="btn btn-outline-secondary" href="{{ route('depots.requests.create') }}">
                        <i class="bx bx-transfer me-1"></i>{{ localize('global.depot.depot_transfer') }}
                    </a>
                    <a class="btn btn-outline-danger" href="{{ route('depots.requests.create', ['destination' => 'pharmacy']) }}">
                        <i class="bx bx-clinic me-1"></i>{{ localize('global.depot.pharmacy_transfer') }}
                    </a>
                @endif
                @if($permissions['create'] ?? true)
                    <a class="btn btn-primary" href="{{ route('depots.create') }}">
                        <i class="bx bx-plus me-1"></i>{{ localize('global.depot.create') }}
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('depots.index') }}" class="row g-3 align-items-end mb-3">
                <div class="col-md-3">
                    <label class="form-label">{{ localize('global.search') }}</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="{{ localize('global.depot.search') }}"
                    >
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ localize('global.depot.branch') }}</label>
                    <select name="branch_id" class="form-select select2">
                        <option value="">{{ localize('global.all') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ localize('global.depot.department') }}</label>
                    <select name="department_id" class="form-select select2">
                        <option value="">{{ localize('global.all') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ localize('global.depot.pharmacy') }}</label>
                    <select name="pharmacy_id" class="form-select select2">
                        <option value="">{{ localize('global.all') }}</option>
                        @foreach($pharmacies as $pharmacy)
                            <option value="{{ $pharmacy->id }}" @selected((string) request('pharmacy_id') === (string) $pharmacy->id)>{{ $pharmacy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ localize('global.depot.parent_depot') }}</label>
                    <select name="parent_depot_id" class="form-select select2">
                        <option value="">{{ localize('global.all') }}</option>
                        @foreach($parentDepots as $parentDepot)
                            <option value="{{ $parentDepot->id }}" @selected((string) request('parent_depot_id') === (string) $parentDepot->id)>{{ $parentDepot->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ localize('global.depot.is_active') }}</label>
                    <select name="is_active" class="form-select">
                        <option value="">{{ localize('global.all') }}</option>
                        <option value="1" @selected(request('is_active') === '1')>{{ localize('global.active') }}</option>
                        <option value="0" @selected(request('is_active') === '0')>{{ localize('global.inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ localize('global.depot.is_base') }}</label>
                    <select name="is_base" class="form-select">
                        <option value="">{{ localize('global.all') }}</option>
                        <option value="1" @selected(request('is_base') === '1')>{{ localize('global.depot.base') }}</option>
                        <option value="0" @selected(request('is_base') === '0')>{{ localize('global.depot.child') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ localize('global.per_page') }}</label>
                    <select name="per_page" class="form-select">
                        @foreach([10, 15, 25, 50] as $size)
                            <option value="{{ $size }}" @selected((int) request('per_page', 15) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="bx bx-search me-1"></i>{{ localize('global.search') }}
                    </button>
                    <a href="{{ route('depots.index') }}" class="btn btn-outline-secondary">
                        {{ localize('global.reset') }}
                    </a>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ localize('global.depot.name') }}</th>
                            <th>{{ localize('global.depot.address') }}</th>
                            <th>{{ localize('global.depot.department') }}</th>
                            <th>{{ localize('global.depot.branch') }}</th>
                            <th>{{ localize('global.depot.pharmacy') }}</th>
                            <th>{{ localize('global.depot.parent_depot') }}</th>
                            <th>{{ localize('global.users') }}</th>
                            <th>{{ localize('global.depot.is_active') }}</th>
                            <th>{{ localize('global.depot.is_base') }}</th>
                            <th>{{ localize('global.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($depots as $depot)
                            <tr>
                                <td><strong>{{ $depot->name }}</strong></td>
                                <td>{{ $depot->address }}</td>
                                <td>{{ $depot->department?->name }}</td>
                                <td>{{ $depot->branch?->name }}</td>
                                <td>{{ $depot->pharmacy?->name }}</td>
                                <td>{{ $depot->parentDepot?->name }}</td>
                                <td>
                                    @forelse($depot->activeUsers->take(2) as $user)
                                        <span class="badge bg-info">{{ $user->name }}</span>
                                    @empty
                                        -
                                    @endforelse
                                    @if($depot->activeUsers->count() > 2)
                                        <span class="badge bg-secondary">+{{ $depot->activeUsers->count() - 2 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span @class(['badge', 'bg-success' => $depot->is_active, 'bg-secondary' => ! $depot->is_active])>
                                        {{ $depot->is_active ? localize('global.active') : localize('global.inactive') }}
                                    </span>
                                </td>
                                <td>
                                    <span @class(['badge', 'bg-warning text-dark' => $depot->is_base, 'bg-secondary' => ! $depot->is_base])>
                                        {{ $depot->is_base ? localize('global.depot.base') : localize('global.depot.child') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('depots.show', $depot->id) }}" title="{{ localize('global.view') }}">
                                            <i class="bx bx-show-alt"></i>
                                        </a>
                                        @if($permissions['edit'] ?? true)
                                            <a class="btn btn-sm btn-warning" href="{{ route('depots.edit', $depot->id) }}" title="{{ localize('global.edit') }}">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                        @endif
                                        @if($permissions['delete'] ?? true)
                                            <form action="{{ route('depots.destroy', $depot->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="{{ localize('global.delete') }}" onclick="return confirm('{{ localize('global.confirm_delete') }}')">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-muted text-center">{{ localize('global.no_data_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $depots->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
