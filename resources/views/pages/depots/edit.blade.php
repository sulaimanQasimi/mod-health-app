@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bx bx-edit me-1"></i>{{ localize('global.depot.edit') }}</h5>
            <a href="{{ route('depots.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
        <div class="card-body">
            <div class="alert alert-danger d-none" id="depot-form-errors"></div>
            <form action="{{ route('depots.update', $depot) }}" method="POST" autocomplete="off" class="js-depot-form">
                @csrf
                @method('PUT')
                @include('pages.depots.partials.form', ['depot' => $depot])
            </form>
        </div>
    </div>
</div>
@endsection

@include('pages.depots.partials.form-scripts')
