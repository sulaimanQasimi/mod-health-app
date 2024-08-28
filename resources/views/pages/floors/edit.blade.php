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
                        <h5 class="mb-0">{{ localize('global.edit_floor') }}</h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('floors.update', $floor->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="name">{{ localize('global.name') }}</label>
                                        <input type="text" name="name" id="name" value="{{ old('name', $floor->name) }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="branch_id">{{ localize('global.branch') }}</label>
                                        <select class="form-control select2" name="branch_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($branches as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('branch_id', $floor->branch_id) == $value->id ? 'selected' : '' }}>
                                                {{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">{{ localize('global.update') }}</button>
                            <a href="{{ route('recipients.index') }}" class="btn btn-danger">{{ localize('global.back') }}</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
