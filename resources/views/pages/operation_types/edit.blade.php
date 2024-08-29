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
                        <h5 class="mb-0">{{ localize('global.edit_operation_type') }}</h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('operation_types.update', $operationType->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name">{{ localize('global.name') }}</label>
                                        <input type="text" name="name" id="name" value="{{ old('name', $operationType->name) }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="department">{{ localize('global.department') }}</label>
                                        <select class="form-control select2" name="department_id" id="department">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($departments as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('department_id', $operationType->department_id) == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" name="branch_id" value="{{ Auth::user()->branch_id }}">
                            </div>
                            <button type="submit" class="btn btn-primary">{{ localize('global.update') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
