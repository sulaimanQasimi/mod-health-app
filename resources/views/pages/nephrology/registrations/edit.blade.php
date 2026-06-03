@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="h4 mb-0">{{ localize('global.edit_nephrology_registration') }}</h2>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('nephrology-registrations.update', $nephrologyRegistration) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('pages.nephrology.registrations._form', [
                            'nephrologyRegistration' => $nephrologyRegistration,
                            'doctors' => $doctors,
                            'diseaseCategories' => $diseaseCategories,
                            'nephrologyDiseases' => $nephrologyDiseases,
                        ])
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('nephrology-registrations.show', $nephrologyRegistration) }}" class="btn btn-secondary">{{ localize('global.cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ localize('global.update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
