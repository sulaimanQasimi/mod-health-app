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
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}">{{ localize('global.dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('depots.requests.index') }}">{{ localize('global.depot.requests') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('depots.requests.show', $depotRequest) }}">{{ $depotRequest->request_number }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ localize('global.edit') }}</li>
                    </ol>
                </nav>
                <h2 class="h4 mb-0">{{ localize('global.edit') }} — {{ $depotRequest->request_number }}</h2>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div class="fw-bold">{{ localize('global.edit') }}</div>
                <a href="{{ route('depots.requests.show', $depotRequest) }}" class="btn btn-outline-secondary btn-sm">{{ localize('global.back') }}</a>
            </div>
            <div class="card-body">
                <form action="{{ route('depots.requests.update', $depotRequest) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('pages.depots.requests.partials.form')
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">{{ localize('global.update') ?? 'Update' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
