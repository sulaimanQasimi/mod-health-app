@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(($viewContext ?? 'depot') !== 'pharmacy')
        <x-depot.nav-tabs active="requests" :urls="$navUrls ?? []" :permissions="$navPermissions ?? null" />
    @endif

    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1">{{ localize('global.depot.new_request') }}</h5>
                <small class="text-muted">{{ localize('global.depot.requests') }}</small>
            </div>
            <a href="{{ route('depots.requests.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('depots.requests.store') }}" method="POST">
                @csrf
                <x-depot.request-form
                    :defaults="$defaults"
                    :depots="$depots"
                    :pharmacies="$pharmacies"
                    :medicines="$medicines"
                    :tools="$tools"
                    :units="$units"
                    :form-items="$formItems"
                    :source-depot-options="$sourceDepotOptions"
                    :view-context="$viewContext"
                    :lock-requesting-depot="$lockRequestingDepot"
                    :depot-request="$depotRequest"
                    :form-data="$formData"
                    :user-pharmacies="$userPharmacies ?? null"
                />
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="submit" name="submit_now" value="0" class="btn btn-outline-secondary">{{ localize('global.save_draft') }}</button>
                    <button type="submit" name="submit_now" value="1" class="btn btn-primary">{{ localize('global.save_and_submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
