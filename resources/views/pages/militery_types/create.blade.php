@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="d-flex justify-content-center align-items-center" style="min-height: 70vh;">
                <div class="col-md-8">
                    <div class="card shadow-lg mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bx bx-plus me-2"></i>{{ localize('global.create') }}</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('militery_types.store') }}" method="post" autocomplete="off">
                                @csrf
                                <div class="mb-4">
                                    <label class="form-label fw-bold" for="name">{{ localize('global.name') }}</label>
                                    <input 
                                        type="text" 
                                        class="form-control @if($errors->has('name')) is-invalid @endif" 
                                        id="name" 
                                        name="name" 
                                        value="{{ old('name') }}" 
                                        placeholder="{{ localize('global.enter_name') }}"
                                        autofocus
                                    >
                                    @if ($errors->first('name'))
                                        <div class="invalid-feedback d-block">
                                            <i class="bx bx-error-circle"></i> {{ $errors->first('name') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('militery_types.index') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-arrow-back"></i> {{ localize('global.back') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save"></i> {{ localize('global.save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
