@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
    @endif

    <x-depot.nav-tabs active="index" :urls="$navUrls ?? []" :permissions="$navPermissions ?? null" />

    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1">{{ localize('global.depot.edit') }}</h5>
                <small class="text-muted">{{ $depot->name }}</small>
            </div>
            <a href="{{ route('depots.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
            </a>
        </div>
        <div class="card-body">
            <div class="alert alert-danger d-none" id="depot-form-errors"></div>
            <form action="{{ route('depots.update', $depot) }}" method="POST" autocomplete="off" class="js-depot-form">
                @csrf
                @method('PUT')
                <x-depot.depot-form
                    :depot="$depot"
                    :branches="$branches"
                    :departments="$departments"
                    :pharmacies="$pharmacies"
                    :depots="$depots"
                    :users="$users"
                />
            </form>
        </div>
    </div>
</div>
@endsection

@include('pages.depots.partials.form-scripts')
