@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ localize('global.depot.title') }}</h5>
                        <div class="pt-3 pt-md-0 text-end">
                            <a class="btn btn-outline-primary btn-lg" href="{{ route('depots.transactions.index') }}">
                                <i class="bx bx-list-ul me-2"></i>Transactions
                            </a>
                            <a class="btn btn-outline-primary btn-lg" href="{{ route('depots.movements.depot_to_depot') }}">
                                <i class="bx bx-transfer me-2"></i>Depot Transfer
                            </a>
                            <a class="btn btn-outline-primary btn-lg" href="{{ route('depots.movements.depot_to_pharmacy') }}">
                                <i class="bx bx-clinic me-2"></i>Pharmacy Transfer
                            </a>
                            <a class="btn btn-primary btn-lg" href="{{ route('depots.create') }}">
                                <i class="bx bx-plus me-2"></i>{{ localize('global.depot.create') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.depot.name') }}</th>
                                    <th>{{ localize('global.depot.address') }}</th>
                                    <th>{{ localize('global.depot.department') }}</th>
                                    <th>{{ localize('global.depot.branch') }}</th>
                                    <th>{{ localize('global.depot.pharmacy') }}</th>
                                    <th>{{ localize('global.depot.parent_depot') }}</th>
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
                                    <td>{{ $depot->parent_depot?->name }}</td>
                                    <td>{{ $depot->is_active ? 'فعال' : 'غیرفعال' }}</td>
                                    <td>{{ $depot->is_base ? 'base' : 'child' }}</td>
                                    <td>
                                        <a class="btn btn-primary btn-sm" href="{{ route('depots.edit', $depot->id) }}" title="{{ localize('global.depot.edit') }}">
                                            <i class="bx bx-edit"></i> 
                                        </a>
                                        <a class="btn btn-primary btn-sm" href="{{ route('depots.show', $depot->id) }}" title="{{ localize('global.depot.view') }}">
                                            <i class="bx bx-show-alt"></i>
                                        </a>
                                        <form action="{{ route('depots.destroy', $depot->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="{{ localize('global.depot.delete') }}" onclick="return confirm('{{ localize('global.confirm_delete') }}')">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">{{ localize('global.no_data_found') }}</td>
                                </tr>
                                @endforelse
                           
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
