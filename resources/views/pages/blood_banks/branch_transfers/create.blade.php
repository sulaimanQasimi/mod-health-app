@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">{{ localize('global.request_blood_from_branch') }}</h4>
                <a href="{{ route('blood_banks.branch_transfers.index') }}" class="btn btn-outline-secondary btn-sm">
                    {{ localize('global.back') }}
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <p class="text-muted small">{{ localize('global.blood_branch_transfer_intro') }}</p>
                    <form action="{{ route('blood_banks.branch_transfers.store') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">{{ localize('global.supplying_branch') }}</label>
                            <select name="supplying_branch_id" class="form-select @error('supplying_branch_id') is-invalid @enderror"
                                required>
                                <option value="">{{ localize('global.select') }}</option>
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}" @selected(old('supplying_branch_id') == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                            @error('supplying_branch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ localize('global.blood_group') }}</label>
                            <select name="blood_group" class="form-select" required>
                                @foreach (['A', 'B', 'AB', 'O'] as $g)
                                    <option value="{{ $g }}" @selected(old('blood_group') === $g)>{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ localize('global.blood_rh') }}</label>
                            <select name="rh" class="form-select" required>
                                <option value="+" @selected(old('rh', '+') === '+')>+</option>
                                <option value="-" @selected(old('rh') === '-')>-</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ localize('global.component_type') }}</label>
                            <select name="component_type" class="form-select" required>
                                @foreach (\App\Models\BloodUnit::COMPONENT_TYPES as $t)
                                    <option value="{{ $t }}" @selected(old('component_type') === $t)>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ localize('global.quantity') }}</label>
                            <input type="number" name="quantity" class="form-control" min="1" max="500"
                                value="{{ old('quantity', 1) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ localize('global.notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
