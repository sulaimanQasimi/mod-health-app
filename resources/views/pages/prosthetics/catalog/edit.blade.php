@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ localize('global.edit') }} — <code>{{ $item->item_code }}</code></h5></div>
                <div class="card-body">
                    <form method="post" action="{{ route('prosthetics.catalog.update', $item) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Item code *</label>
                            <input type="text" name="item_code" class="form-control" value="{{ old('item_code', $item->item_code) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.name') }} *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $item->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.category') }}</label>
                            <input type="text" name="category" class="form-control" value="{{ old('category', $item->category) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.cost') ?? 'Standard cost' }}</label>
                            <input type="number" step="0.01" name="standard_cost" class="form-control" value="{{ old('standard_cost', $item->standard_cost) }}">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $item->is_active))>
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
