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
                        </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="sector_id">{{ localize('global.sector') }}</label>
                                        <select class="form-control select2" name="sector">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($sectors as $value)
                                                <option value="{{ $value->id }}"
                                                        {{ old('sector') == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name_dr }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->first('sector_id'))
                                            <div class="display-error mb-3">
                                                {{ $errors->first('sector_id') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"
                                               for="recipients">{{ localize('global.recipients') }}</label>
                                        <select class="form-control select2" name="recipient_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($recipients as $value)
                                                <option value="{{ $value->id }}"
                                                        {{ old('recipient_id') == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name_dr }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->first('recipient_id'))
                                            <div class="display-error mb-3">
                                                {{ $errors->first('recipient_id') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"
                                               for="recipients">{{ localize('global.user_recipients') }}</label>
                                        <select class="form-control select2" name="recipients[]" multiple>
                                            <option value="" disabled >{{ localize('global.select') }}</option>
                                            @foreach ($recipients as $value)
                                                
                                                 <option value="{{$value->id}}" {{old('recipients') && in_array($value->id , old('recipients') ) ? 'selected' : '' }}>{{$value->name_dr}}</option>
                                           
                                            @endforeach
                                        </select>
                                        @if ($errors->first('recipients'))
                                            <div class="display-error mb-3">
                                                {{ $errors->first('recipients') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"
                                               for="document_type">{{ localize('global.document_type_columns') }}</label>
                                        <select class="form-control select2" name="document_type[]" multiple>
                                            <option value="" disabled >{{ localize('global.select') }}</option>
                                            @foreach ($document_type as $value)
                                                
                                                 <option value="{{$value->id}}" {{old('document_type') && in_array($value->id , old('document_type') ) ? 'selected' : '' }}>{{$value->document_type}}</option>
                                           
                                            @endforeach
                                        </select>
                                        @if ($errors->first('document_type'))
                                            <div class="display-error mb-3">
                                                {{ $errors->first('document_type') }}
                                            </div>
                                        @endif
                                    </div>
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
