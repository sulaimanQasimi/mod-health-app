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
                        <h5 class="mb-0">{{ localize('global.edit_under_review') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-12">

                                    <form action="{{ route('visits.updateUnderReviewVisit', $visit) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label
                                                            for="description{{ $visit->id }}">{{ localize('global.description') }}</label>
                                                        <textarea class="form-control" name="description" rows="3">{{ $visit->description }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mt-2">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                            <button type="submit"
                                                class="btn btn-primary">{{ localize('global.save') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
