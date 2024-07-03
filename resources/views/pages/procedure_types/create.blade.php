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
                        <h5 class="mb-0">{{ localize('global.create_procedure_type') }}</h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('procedure_types.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="name">{{ localize('global.name') }}</label>
                                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">{{localize('global.create')}}</button>
                            <a href="{{ route('procedure_types.index') }}"><button type="button"
                                class="btn btn-danger">{{ localize('global.back') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
