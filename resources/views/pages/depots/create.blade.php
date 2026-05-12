@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="col-xl-8 mx-auto">
                <div class="card shadow-lg border-0 mb-5">
                    <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center py-3 px-4 rounded-top">
                        <h4 class="mb-0 fw-bold">
                            <i class="fas fa-warehouse me-2"></i>{{ localize('global.depot.create') }}
                        </h4>
                    </div>
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 80%;"></div>
                    </div>
                    <div class="card-body bg-light p-4 rounded-bottom">
                        <form action="{{ route('depots.store') }}" method="POST" autocomplete="off">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="text" name="name" id="name" value="{{ old('name') }}" 
                                            class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" 
                                            placeholder="{{ localize('global.depot.name') }}">
                                        <label for="name"><i class="fas fa-tag me-2"></i>{{ localize('global.depot.name') }}</label>
                                        @if($errors->has('name'))
                                            <small class="text-danger">{{ $errors->first('name') }}</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <label for="address"><i class="fas fa-map-marker-alt me-2"></i>{{ localize('global.depot.address') }}</label>
                                        <input type="text" name="address" id="address" value="{{ old('address') }}"
                                            class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                                            placeholder="{{ localize('global.depot.address') }}">
                                        @if($errors->has('address'))
                                            <small class="text-danger">{{ $errors->first('address') }}</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <label for="department_id"><i class="fas fa-building me-2"></i>{{ localize('global.depot.department') }}</label>
                                        <select name="department_id" id="department_id" class="form-select {{ $errors->has('department_id') ? 'is-invalid' : '' }}"
                                            aria-label="{{ localize('global.depot.select_department') }}">
                                            <option value="">{{ localize('global.depot.select_department') }}</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('department_id'))
                                            <small class="text-danger">{{ $errors->first('department_id') }}</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">  
                                    <div class="form-floating">
                                        <select name="pharmacy_id" id="pharmacy_id" class="form-select select2 {{ $errors->has('pharmacy_id') ? 'is-invalid' : '' }}"
                                            aria-label="{{ localize('global.depot.select_pharmacy') }}">
                                            <option value="">{{ localize('global.depot.select_pharmacy') }}</option>
                                            @foreach($pharmacies as $pharmacy)
                                                <option value="{{ $pharmacy->id }}" {{ old('pharmacy_id') == $pharmacy->id ? 'selected' : '' }}>
                                                    {{ $pharmacy->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('pharmacy_id'))
                                            <small class="text-danger">{{ $errors->first('pharmacy_id') }}</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">  
                                    <div class="form-floating">
                                    <label for="parent_depot_id"><i class="fas fa-network-wired me-2"></i>{{ localize('global.depot.parent_depot') }}</label>
                                        <select name="parent_depot_id" id="parent_depot_id" class="form-select select2 {{ $errors->has('parent_depot_id') ? 'is-invalid' : '' }}"
                                            aria-label="{{ localize('global.depot.select_parent_depot') }}">
                                            <option value="">{{ localize('global.depot.select_parent_depot') }}</option>
                                            @foreach($depots as $depot)
                                                <option value="{{ $depot->id }}" {{ old('parent_depot_id') == $depot->id ? 'selected' : '' }}>
                                                    {{ $depot->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('parent_depot_id'))
                                            <small class="text-danger">{{ $errors->first('parent_depot_id') }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <hr class="my-4">
                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-undo-alt"></i> {{ localize('global.reset') ?? 'Reset' }}
                                </button>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-plus me-1"></i>{{localize('global.add')}}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
