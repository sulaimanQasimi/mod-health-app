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
                        <h5 class="mb-0">{{ localize('global.medicines') }}</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('medicines.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">{{ localize('global.name') }}</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="medicine_type_id">{{ localize('global.medicine_type') }}</label>
                                        <select class="form-control select2 @error('medicine_type_id') is-invalid @enderror"
                                            id="medicine_type_id" name="medicine_type_id" required>
                                            <option value="">{{ localize('global.medicine_type') }}</option>
                                            @foreach ($medicineTypes as $medicineType)
                                                <option value="{{ $medicineType->id }}"
                                                    {{ old('medicine_type_id') == $medicineType->id ? 'selected' : '' }}>
                                                    {{ $medicineType->type }}</option>
                                            @endforeach
                                        </select>
                                        @error('medicine_type_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="submit"
                                        class="btn btn-primary mt-2">{{ localize('global.create') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
