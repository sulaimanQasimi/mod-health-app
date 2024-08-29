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
                        <h5 class="mb-0">{{ localize('global.edit_medicine_usage_type') }}</h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('medicine_usage_types.update', $medicineUsageType) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="name">{{localize('global.name')}}</label>
                                        <input type="text" name="name" id="name" value="{{ old('name', $medicineUsageType->name) }}" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label for="description">{{localize('global.description')}}</label>
                                        <textarea name="description" id="description" class="form-control">{{ old('description', $medicineUsageType->description) }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">{{localize('global.update')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection