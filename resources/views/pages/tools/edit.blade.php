@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="col-xl-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">{{ localize('global.depot.edit_tool') }}</h5>
                <a href="{{ route('tools.index') }}" class="btn btn-outline-secondary btn-sm">{{ localize('global.back') }}</a>
            </div>
            <div class="card-body">
                <form action="{{ route('tools.update', $tool) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('pages.tools.partials.form')
                    <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
