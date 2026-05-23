@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        @if ($errors->any())
            <div class="alert alert-danger m-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row mb-4">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ localize('global.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('hemodialysis-sessions.index') }}">{{ localize('global.hemodialysis') }}</a></li>
                            <li class="breadcrumb-item active">{{ localize('global.create') }}</li>
                        </ol>
                    </nav>
                    <h2 class="h4 mb-0">{{ localize('global.add_hemodialysis_session') }}</h2>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('hemodialysis-sessions.store') }}" method="POST">
                        @csrf
                        @include('pages.nephrology.hemodialysis._form', [
                            'doctors' => $doctors,
                            'selectedPatient' => $selectedPatient,
                            'selectedRegistration' => $selectedRegistration,
                        ])
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('hemodialysis-sessions.index') }}" class="btn btn-secondary">{{ localize('global.cancel') }}</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save"></i> {{ localize('global.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
