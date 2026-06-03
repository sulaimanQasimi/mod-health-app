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
                        <li class="breadcrumb-item active">{{ localize('global.depot.new_request') }}</li>
                    </ol>
                </nav>
                <h2 class="h4 mb-0">{{ localize('global.depot.new_request') }}</h2>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div class="fw-bold">{{ localize('global.depot.new_request') }}</div>
                <a href="{{ route('depots.requests.index') }}" class="btn btn-outline-secondary btn-sm">{{ localize('global.back') }}</a>
            </div>
            <div class="card-body">
                <form action="{{ route('depots.requests.store') }}" method="POST">
                    @csrf
                    @include('pages.depots.requests.partials.form')
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="submit" name="submit_now" value="0" class="btn btn-outline-primary">{{ localize('global.save_draft') ?? 'Save Draft' }}</button>
                        <button type="submit" name="submit_now" value="1" class="btn btn-primary">{{ localize('global.save_and_submit') ?? 'Save & Submit' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const requestingDepot = document.getElementById('requesting_depot_id');
    const sourceDepot = document.getElementById('source_depot_id');
    const parentMap = @json($depots->mapWithKeys(fn($d) => [$d->id => $d->parent_depot_id])->all());

    requestingDepot?.addEventListener('change', function () {
        const parentId = parentMap[this.value];
        if (parentId && sourceDepot) {
            sourceDepot.value = parentId;
            if (window.jQuery) $('#source_depot_id').trigger('change.select2');
        }
    });
});
</script>
@endsection
