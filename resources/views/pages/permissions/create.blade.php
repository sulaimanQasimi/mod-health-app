@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.create_new_permission') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('permissions.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row mb-3 mt-3">
                                <div class="col-md-4">
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
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.name_dr') }}</label>
                                        <input type="text" name="name_dr" class="form-control"
                                               value="{{ old('name') }}">
                                    </div>
                                    @if ($errors->first('name_dr'))
                                        <div class="display-error">
                                            {{ $errors->first('name_dr') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.parent') }}</label>
                                        <select class="form-control select2" name="parent_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($permissions as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('name_dr') == $value->id ? 'selected' : '' }}>
                                                {{ $value->name_dr }}</option>
                                            @endforeach
    
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('permissions.index') }}"><button type="button"
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
