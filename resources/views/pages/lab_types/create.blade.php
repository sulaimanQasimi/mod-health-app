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
                        <h5 class="mb-0">{{ localize('global.create_lab_type') }}</h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('lab_types.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="name">{{localize('global.name')}}</label>
                                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="section">{{localize('global.parent_test')}}</label>
                                        <select class="form-control select2" name="parent_id" id="parent_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($labTypes as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                {{ $value->name }}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="section">{{localize('global.lab_type_sections')}}</label>
                                        <select class="form-control select2" name="section_id" id="section_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($labTypeSections as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                {{ $value->section }}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" name="branch_id" value="{{Auth::user()->branch_id}}">
                            </div>
                            <button type="submit" class="btn btn-primary">{{localize('global.create')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
