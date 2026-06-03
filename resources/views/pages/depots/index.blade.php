@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.depot.title') }}</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-outline-primary" href="{{ route('depots.transactions.index') }}">
                                <i class="bx bx-list-ul me-2"></i>{{ localize('global.depot.transactions') }}
                            </a>
                            <a class="btn btn-outline-primary" href="{{ route('depots.movements.depot_to_depot') }}">
                                <i class="bx bx-transfer me-2"></i>{{ localize('global.depot.depot_transfer') }}
                            </a>
                            <a class="btn btn-outline-primary" href="{{ route('depots.movements.depot_to_pharmacy') }}">
                                <i class="bx bx-clinic me-2"></i>{{ localize('global.depot.pharmacy_transfer') }}
                            </a>
                            <a class="btn btn-primary" href="{{ route('depots.create') }}">
                                <i class="bx bx-plus me-2"></i>{{ localize('global.depot.create') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card mt-2">
                    <div class="card-body">
                        <form method="GET" action="{{ route('depots.index') }}" class="row g-3 align-items-end mb-3">
                            <div class="col-md-3">
                                <label class="form-label mb-1">{{ localize('global.search') }}</label>
                                <input
                                    type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    class="form-control"
                                    placeholder="{{ localize('global.depot.search') }}"
                                >
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">{{ localize('global.depot.branch') }}</label>
                                <select name="branch_id" class="form-select select2">
                                    <option value="">{{ localize('global.all') }}</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">{{ localize('global.depot.department') }}</label>
                                <select name="department_id" class="form-select select2">
                                    <option value="">{{ localize('global.all') }}</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">{{ localize('global.depot.pharmacy') }}</label>
                                <select name="pharmacy_id" class="form-select select2">
                                    <option value="">{{ localize('global.all') }}</option>
                                    @foreach($pharmacies as $pharmacy)
                                        <option value="{{ $pharmacy->id }}" @selected((string) request('pharmacy_id') === (string) $pharmacy->id)>{{ $pharmacy->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1">{{ localize('global.depot.parent_depot') }}</label>
                                <select name="parent_depot_id" class="form-select select2">
                                    <option value="">{{ localize('global.all') }}</option>
                                    @foreach($parentDepots as $parentDepot)
                                        <option value="{{ $parentDepot->id }}" @selected((string) request('parent_depot_id') === (string) $parentDepot->id)>{{ $parentDepot->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">{{ localize('global.depot.is_active') }}</label>
                                <select name="is_active" class="form-select">
                                    <option value="">{{ localize('global.all') }}</option>
                                    <option value="1" @selected(request('is_active') === '1')>{{ localize('global.active') }}</option>
                                    <option value="0" @selected(request('is_active') === '0')>{{ localize('global.inactive') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">{{ localize('global.depot.is_base') }}</label>
                                <select name="is_base" class="form-select">
                                    <option value="">{{ localize('global.all') }}</option>
                                    <option value="1" @selected(request('is_base') === '1')>{{ localize('global.depot.base') }}</option>
                                    <option value="0" @selected(request('is_base') === '0')>{{ localize('global.depot.child') }}</option>
                                </select>
                            </div>
                            <div class="col-md-1 d-grid">
                                <button class="btn btn-secondary" type="submit">
                                    <i class="bx bx-search"></i>
                                </button>
                            </div>
                            <div class="col-md-2 d-grid">
                                <a href="{{ route('depots.index') }}" class="btn btn-outline-secondary">
                                    {{ localize('global.reset') }}
                                </a>
                            </div>
                        </form>

                        <div class="table-responsive text-nowrap">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.depot.name') }}</th>
                                    <th>{{ localize('global.depot.address') }}</th>
                                    <th>{{ localize('global.depot.department') }}</th>
                                    <th>{{ localize('global.depot.branch') }}</th>
                                    <th>{{ localize('global.depot.pharmacy') }}</th>
                                    <th>{{ localize('global.depot.parent_depot') }}</th>
                                    <th>{{localize('global.users') }}</th>
                                    <th>{{ localize('global.depot.is_active') }}</th>
                                    <th>{{ localize('global.depot.is_base') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($depots as $depot)
                                <tr>
                                    <td>{{ $depot->name }}</td>
                                    <td>{{ $depot->address }}</td>
                                    <td>{{ $depot->department?->name }}</td>
                                    <td>{{ $depot->branch?->name }}</td>
                                    <td>{{ $depot->pharmacy?->name }}</td>
                                    <td>{{ $depot->parentDepot?->name }}</td>
                                    <td>
                                        @forelse($depot->activeUsers->take(2) as $user)
                                            <span class="badge bg-label-primary">{{ $user->name }}</span>
                                        @empty
                                            -
                                        @endforelse
                                        @if($depot->activeUsers->count() > 2)
                                            <span class="badge bg-label-secondary">+{{ $depot->activeUsers->count() - 2 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $depot->is_active ? 'success' : 'secondary' }}">
                                            {{ $depot->is_active ? localize('global.active') : localize('global.inactive') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $depot->is_base ? 'primary' : 'secondary' }}">
                                            {{ $depot->is_base ? localize('global.depot.base') : localize('global.depot.child') }}
                                        </span>
                                    </td>
                                    <td>
                                        <a class="btn btn-primary btn-sm" href="{{ route('depots.edit', $depot->id) }}" title="{{ localize('global.edit') }}">
                                            <i class="bx bx-edit"></i> 
                                        </a>
                                        <a class="btn btn-primary btn-sm" href="{{ route('depots.show', $depot->id) }}" title="{{ localize('global.view') }}">
                                            <i class="bx bx-show-alt"></i>
                                        </a>
                                        <form action="{{ route('depots.destroy', $depot->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="{{ localize('global.delete') }}" onclick="return confirm('{{ localize('global.confirm_delete') }}')">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">{{ localize('global.no_data_found') }}</td>
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
        </div>
    </div>
</div>
@endsection
