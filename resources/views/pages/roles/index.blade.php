@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="fw-bold py-3 mb-2"> {{ localize('global.roles_list') }}</h4>
            <div class="row g-4 mb-4">
                <div class="card">
                    <div class="card-header border-bottom">
                        <div class="pt-3 pt-md-0 text-end">
                            <a class="btn btn-secondary create-new btn-primary" href="{{ route('roles.create') }}"
                               type="button">
                                <span class="text-white"><i class="bx bx-plus me-sm-1"></i> <span
                                          class="d-none d-sm-inline-block  ">{{ localize('global.create') }}</span></span>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover border-top">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.name_en') }}</th>
                                        <th>{{ localize('global.name_dr') }}</th>
                                        <th style="width: 500px;">{{ localize('global.permissions') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roles as $role)
                                        <tr>
                                            <td>{{ $role->id }}</td>
                                            <td>{{ $role->name }}</td>
                                            <td>{{ $role->name_dr }}</td>
                                            <td>
                                                @foreach($role->permissions as $permission)
                                                    <span class="badge rounded-pill bg-label-primary">{{ $permission->name_dr }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <a href="{{ url('roles/edit/' . $role->id) }}"
                                                   class="btn btn-sm btn-icon item-edit text-warning">
                                                    <i class="bx bxs-edit"></i>
                                                </a>
                                                <a href="{{ url('roles/destroy/' . $role->id) }}"
                                                   class="btn btn-sm btn-icon item-delete text-danger">
                                                    <i class="bx bxs-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">{{ localize('global.no_data_found') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($roles->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $roles->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- / Content -->
        <div class="content-backdrop fade"></div>
    </div>
@endsection

@push('custom-css')
    <style>
        .table thead th {
            text-align: right;
        }

        .table tbody td {
            text-align: right;
        }
    </style>
@endpush
