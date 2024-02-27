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
                        <form action="{{ route('appointments.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="department_id">Doctor</label>
                                        <select class="form-control select2" name="department_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($doctors as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                {{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Create</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
