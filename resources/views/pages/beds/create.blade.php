@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.create_department') }}</h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('beds.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="number">{{ localize('global.room_number') }}</label>
                                        <input type="text" name="number" id="number" value="{{ old('number') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="room_id">{{ localize('global.related_department') }}</label>
                                        <select class="form-control select2" name="room_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($rooms as $value)
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
