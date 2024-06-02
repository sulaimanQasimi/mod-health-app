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
                        <form action="{{ route('users.update', $user->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.name') }}</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name', $user->name) }}">
                                    </div>
                                    @if ($errors->first('name'))
                                        <div class="display-error">
                                            {{ $errors->first('name') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.last_name') }}</label>
                                        <input type="text" class="form-control" name="last_name"
                                            value="{{ old('last_name', $user->last_name) }}">
                                    </div>
                                    @if ($errors->first('last_name'))
                                        <div class="display-error">
                                            {{ $errors->first('last_name') }}
                                        </div>
                                    @endif
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.email') }}</label>
                                        <input type="text" class="form-control" name="email"
                                            value="{{ old('email', $user->email) }}">
                                    </div>
                                    @if ($errors->first('email'))
                                        <div class="display-error">
                                            {{ $errors->first('email') }}
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
                                            @foreach ($branches as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('branch_id', $user->branch_id) == $value->id ? 'selected' : '' }}>
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
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="department_id">{{ localize('global.department') }}</label>
                                        <select class="form-control select2" name="department_id" id="department_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($departments as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('department_id', $user->department_id) == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name }}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="section_id">{{ localize('global.section') }}</label>
                                        <select class="form-control select2" name="section_id" id="section_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($sections as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('section_id', $user->section_id) == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3 mt-3">
                                    <h5>{{ localize('global.roles') }}</h5>
                                    @foreach ($roles as $value)
                                        <div class="d-flex">
                                            <div class="form-check me-3 me-lg-5">
                                                {{ Form::checkbox('roles[]', $value->id, $user->roles->contains($value->id), ['class' => 'form-check-input']) }}
                                                {{ $value->name_dr }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="col-md-6 mb-3 mt-3">
                                    <h5>{{ localize('global.permissions') }}</h5>
                                    @foreach ($permissions as $value)
                                        <div class="form-check">
                                            {{ Form::checkbox('permissions[]', $value->id, $user->permissions->contains($value->id), ['class' => 'form-check-input']) }}
                                                {{ $value->name_dr }}
                                        </div>
                                    @endforeach
                                </div>
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

            $('#branch_id').on('change', function() {
                var branchID = $(this).val();
                if (branchID !== '') {
                    $.ajax({
                        url: '/get_departments/' + branchID,
                        type: 'GET',
                        success: function(response) {

                            $('#department_id').html(response);
                        }
                    })
                }
            })

            $('#department_id').on('change', function() {
                var depID = $(this).val();
                if (depID !== '') {
                    $.ajax({
                        url: '/get_sections/' + depID,
                        type: 'GET',
                        success: function(response) {

                            $('#section_id').html(response);
                        }
                    })
                }
            })
        });
    </script>
@endpush
