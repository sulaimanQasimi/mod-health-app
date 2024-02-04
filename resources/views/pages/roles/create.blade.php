@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.create') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('roles.store') }}" method="post" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.name_en') }}</label>
                                        <input type="text" class="form-control" name="name"
                                               value="{{ old('name') }}">
                                    </div>
                                    @if ($errors->first('name'))
                                        <div class="display-error">
                                            {{ $errors->first('name') }}
                                        </div>
                                    @endif
                                    @if (isset($duplicateErrorMessage))
                                        <div class="alert alert-danger">
                                            {{ $duplicateErrorMessage }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.name_dr') }}</label>
                                        <input type="text" name="name_dr" class="form-control"
                                               value="{{ old('name_dr') }}">
                                    </div>
                                    @if ($errors->first('name_dr'))
                                        <div class="display-error">
                                            {{ $errors->first('name_dr') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-12 mt-3 mb-3">
                                <div class="row">

                                    <div class="col-md-6">
                                        <h5>{{ localize('global.permissions_list') }}</h5>
                                        @foreach ($permissions as $value)
                                            <div class="d-flex">
                                                <div class="form-check me-lg-5">
                                                    {{ Form::checkbox('permission[]', $value->id, false, ['class' => 'form-check-input']) }}
                                                    {{ $value->name_dr }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
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
