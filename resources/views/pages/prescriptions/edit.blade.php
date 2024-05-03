@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.edit') }}</h5>
                    </div>
                    <div class="card-body">
                        <form role="form" class="form-horizontal" action="{{ route('recipients.update', $recipient->id) }}"
                            enctype="multipart/form-data" method="POST">
                          @method('PUT')
                          @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ localize('global.name') }}</label>
                                    <input type="text" class="form-control" name="name" value="{{$recipient->name }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">{{ localize('global.description') }}</label>
                                    <textarea name="description" class="form-control">{{ $recipient->description }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('recipients.index') }}"><button type="button"
                                                class="btn btn-danger">{{ localize('global.back') }}</button>
                                        <button type="submit"
                                                class="btn btn-primary">{{ localize('global.save') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endpush
