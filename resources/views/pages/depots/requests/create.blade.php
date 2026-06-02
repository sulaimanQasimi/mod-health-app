@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="col-xl-10 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">{{ localize('global.depot.new_request') }}</h5>
                <a href="{{ route('depots.requests.index') }}" class="btn btn-outline-secondary btn-sm">{{ localize('global.back') }}</a>
            </div>
            <div class="card-body">
                <form action="{{ route('depots.requests.store') }}" method="POST">
                    @csrf
                    @include('pages.depots.requests.partials.form')
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" name="submit_now" value="0" class="btn btn-outline-primary">Save Draft</button>
                        <button type="submit" name="submit_now" value="1" class="btn btn-primary">Save & Submit</button>
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
