@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.create') }} - {{ localize('global.recipient_parts') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('recipient-parts.store') }}" method="post">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ localize('global.recipient') }}</label>
                                    <select class="form-control select2" name="recipient_id">
                                        <option value="">{{ localize('global.select') }}</option>
                                        @foreach ($recipients as $recipient)
                                            <option value="{{ $recipient->id }}" {{ old('recipient_id') == $recipient->id ? 'selected' : '' }}>
                                                {{ $recipient->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->first('recipient_id'))
                                        <div class="display-error">
                                            {{ $errors->first('recipient_id') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ localize('global.name') }}</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                                    @if ($errors->first('name'))
                                        <div class="display-error">
                                            {{ $errors->first('name') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ localize('global.code') }}</label>
                                    <input type="text" class="form-control" name="code" value="{{ old('code') }}">
                                    @if ($errors->first('code'))
                                        <div class="display-error">
                                            {{ $errors->first('code') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
                                    <a href="{{ route('recipient-parts.index') }}"><button type="button"
                                        class="btn btn-danger">{{ localize('global.back') }}</button></a>
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
