@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="mb-0">{{ localize('global.depot.create') }}</h4>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bx bx-store-alt me-1"></i>{{ localize('global.depot.create') }}
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('depots.store') }}" method="POST" autocomplete="off">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="name" class="form-label">{{ localize('global.depot.name') }}</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}"
                                    class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                    placeholder="{{ localize('global.depot.name') }}">
                                @if($errors->has('name'))
                                    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="address" class="form-label">{{ localize('global.depot.address') }}</label>
                                <input type="text" name="address" id="address" value="{{ old('address') }}"
                                    class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                                    placeholder="{{ localize('global.depot.address') }}">
                                @if($errors->has('address'))
                                    <div class="invalid-feedback">{{ $errors->first('address') }}</div>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="department_id" class="form-label">{{ localize('global.depot.department') }}</label>
                                <select name="department_id" id="department_id"
                                    class="form-select {{ $errors->has('department_id') ? 'is-invalid' : '' }}">
                                    <option value="">{{ localize('global.depot.select_department') }}</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($errors->has('department_id'))
                                    <div class="invalid-feedback">{{ $errors->first('department_id') }}</div>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="pharmacy_id" class="form-label">{{ localize('global.depot.select_pharmacy') }}</label>
                                <select name="pharmacy_id" id="pharmacy_id"
                                    class="form-select select2 {{ $errors->has('pharmacy_id') ? 'is-invalid' : '' }}">
                                    <option value="">{{ localize('global.depot.select_pharmacy') }}</option>
                                    @foreach($pharmacies as $pharmacy)
                                        <option value="{{ $pharmacy->id }}" {{ old('pharmacy_id') == $pharmacy->id ? 'selected' : '' }}>
                                            {{ $pharmacy->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($errors->has('pharmacy_id'))
                                    <div class="invalid-feedback">{{ $errors->first('pharmacy_id') }}</div>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="parent_depot_id" class="form-label">{{ localize('global.depot.parent_depot') }}</label>
                                <select name="parent_depot_id" id="parent_depot_id"
                                    class="form-select select2 {{ $errors->has('parent_depot_id') ? 'is-invalid' : '' }}">
                                    <option value="">{{ localize('global.depot.select_parent_depot') }}</option>
                                    @foreach($depots as $depot)
                                        <option value="{{ $depot->id }}" {{ old('parent_depot_id') == $depot->id ? 'selected' : '' }}>
                                            {{ $depot->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($errors->has('parent_depot_id'))
                                    <div class="invalid-feedback">{{ $errors->first('parent_depot_id') }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="reset" class="btn btn-label-secondary">
                                {{ localize('global.reset') ?? 'Reset' }}
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i>{{ localize('global.add') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
```