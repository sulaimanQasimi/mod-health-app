@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ localize('global.add') }} — {{ localize('global.prosthetics_catalog') }}</h5></div>
                <div class="card-body">
                    <form method="post" action="{{ route('prosthetics.catalog.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Item code *</label>
                            <input type="text" name="item_code" class="form-control @error('item_code') is-invalid @enderror" value="{{ old('item_code') }}" required>
                            @error('item_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.name') }} *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.category') }}</label>
                            <input type="text" name="category" class="form-control" value="{{ old('category') }}" placeholder="e.g. sockets, liners, feet">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.cost') ?? 'Standard cost' }}</label>
                            <input type="number" step="0.01" name="standard_cost" class="form-control" value="{{ old('standard_cost', 0) }}">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                        <a href="{{ route('prosthetics.catalog.index') }}" class="btn btn-outline-secondary">{{ localize('global.back') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
