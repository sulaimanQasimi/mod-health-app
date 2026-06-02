@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
    @endif
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ localize('global.depot.tools') }}</h5>
            @can('depot.create')
            <a href="{{ route('tools.create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i>{{ localize('global.create') }}</a>
            @endcan
        </div>
        <div class="card-body border-bottom">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ localize('global.search') }}">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" type="submit">{{ localize('global.search') }}</button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ localize('global.name') }}</th>
                        <th>{{ localize('global.code') }}</th>
                        <th>{{ localize('global.unit') }}</th>
                        <th>{{ localize('global.status') }}</th>
                        <th>{{ localize('global.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tools as $tool)
                        <tr>
                            <td>{{ $tool->name }}</td>
                            <td>{{ $tool->code }}</td>
                            <td>{{ $tool->unit?->name ?? '-' }}</td>
                            <td><span class="badge bg-{{ $tool->is_active ? 'success' : 'secondary' }}">{{ $tool->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                @can('depot.update')
                                <a href="{{ route('tools.edit', $tool) }}"><i class="bx bx-edit"></i></a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">{{ localize('global.no_data_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $tools->links() }}</div>
    </div>
</div>
@endsection
