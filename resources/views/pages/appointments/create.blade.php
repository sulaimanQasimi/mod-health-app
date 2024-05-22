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
                        <h5 class="mb-0">{{ localize('global.create_new_permission') }}</h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('appointments.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="doctor_id">{{localize('global.doctor_name')}}</label>
                                        <select class="form-control select2" name="doctor_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($doctors as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                {{ $value->name }}

                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="date">{{localize('global.date')}}</label>
                                        <input type="date" class="form-control" name="date"/>
                                    </div>
                                    <div class="mb-3">
                                        <label for="time">{{localize('global.time')}}</label>
                                        <input type="time" class="form-control" name="time"/>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="diagnosed" value="1">

                            <button type="submit" class="btn btn-primary">{{localize('global.create')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
