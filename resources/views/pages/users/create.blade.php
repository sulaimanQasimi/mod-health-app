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
                        <form action="{{ route('users.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.name_en') }}</label>
                                        <input type="text" class="form-control" name="name_en"
                                               value="{{ old('name_en') }}">
                                    </div>
                                    @if ($errors->first('name_en'))
                                        <div class="display-error">
                                            {{ $errors->first('name_en') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.name_dr') }}</label>
                                        <input type="text" class="form-control" name="name_dr"
                                               value="{{ old('name_dr') }}">
                                    </div>
                                    @if ($errors->first('name_dr'))
                                        <div class="display-error">
                                            {{ $errors->first('name_dr') }}
                                        </div>
                                    @endif
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.email') }}</label>
                                        <input type="text" class="form-control" name="email"
                                               value="{{ old('email') }}">
                                    </div>
                                    @if ($errors->first('email'))
                                        <div class="display-error">
                                            {{ $errors->first('email') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.last_name_dr') }}</label>
                                        <input type="text" class="form-control" name="last_name_dr"
                                               value="{{ old('last_name_dr') }}">
                                    </div>
                                    @if ($errors->first('last_name_dr'))
                                        <div class="display-error">
                                            {{ $errors->first('last_name_dr') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.password') }}</label>
                                        <input type="password" class="form-control" name="password"
                                               value="{{ old('password') }}">
                                    </div>
                                    @if ($errors->first('password'))
                                        <div class="display-error">
                                            {{ $errors->first('password') }}
                                        </div>
                                    @endif
                                </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ localize('global.password_confirmation') }}</label>
                                    <input type="password" class="form-control" name="password_confirmation"
                                           value="{{ old('password_confirmation') }}">
                                </div>
                                @if ($errors->first('password_confirmation'))
                                    <div class="display-error">
                                        {{ $errors->first('password_confirmation') }}
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ localize('global.branch') }}</label>
                                    <select class="form-control select2" name="branch_id">
                                        <option value="">{{ localize('global.select') }}</option>
                                        @foreach($branches as $value)
                                            <option value="{{ $value->id }}"
                                                {{ old('name') == $value->id ? 'selected' : '' }}>
                                            {{ $value->name }}</option>
                                        @endforeach

                                    </select>
                                </div>
                                @if ($errors->first('branch'))
                                    <div class="display-error">
                                        {{ $errors->first('branch') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                            <div class="col-12 mb-3 mt-3">
                                <h5>{{ localize('global.roles') }}</h5>
                                @foreach ($roles as $value)
                                    <div class="d-flex">
                                        <div class="form-check me-3 me-lg-5">
                                            {{ Form::checkbox('roles[]', $value->id, false, ['class' => 'form-check-input']) }}
                                            {{ $value->name_dr }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <a href="{{ route('users.index') }}"><button type="button"
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
@push('custom-js')
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endpush
