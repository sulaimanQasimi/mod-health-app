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
                        <h5 class="mb-0">{{ localize('global.edit') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('roles.update', $role->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.name_en') }}</label>
                                        <input type="text" class="form-control" name="name"
                                               value="{{ $role->name }}">
                                    </div>
                                    @error('name')
                                        <div class="display-error">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.name_dr') }}</label>
                                        <input type="text" name="name_dr" class="form-control"
                                               value="{{ $role->name_dr }}">
                                    </div>
                                    @error('name_dr')
                                        <div class="display-error">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            @include('pages.roles.partials.permission_folders', [
                                'permissionFolders' => $permissionFolders,
                                'rolePermissionIds' => old('permission', $rolePermissionIds),
                            ])

                            <a href="{{ route('roles.index') }}"><button type="button"
                                        class="btn btn-danger">{{ localize('global.back') }}</button>
                                <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
