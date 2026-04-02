@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="mb-0">{{ localize('global.prosthetics_catalog') }}</h4>
                @can('manage-prosthetics-catalog')
                    <a href="{{ route('prosthetics.catalog.create') }}" class="btn btn-primary btn-sm">{{ localize('global.add') ?? 'Add' }}</a>
                @endcan
            </div>

            <form method="get" class="mb-3">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm d-inline-block" style="max-width:280px" placeholder="{{ localize('global.search') }}">
                <button type="submit" class="btn btn-sm btn-outline-secondary">{{ localize('global.search') }}</button>
            </form>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>{{ localize('global.name') ?? 'Name' }}</th>
                                <th>{{ localize('global.category') ?? 'Category' }}</th>
                                <th>{{ localize('global.cost') ?? 'Cost' }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td><code>{{ $item->item_code }}</code></td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->category ?? '—' }}</td>
                                    <td>{{ number_format($item->standard_cost, 2) }}</td>
                                    <td>
                                        @can('manage-prosthetics-catalog')
                                            <a href="{{ route('prosthetics.catalog.edit', $item) }}" class="btn btn-sm btn-outline-primary">{{ localize('global.edit') }}</a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $items->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
