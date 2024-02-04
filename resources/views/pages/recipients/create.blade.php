@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.create') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('recipients.store') }}" method="post">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ localize('global.name_dr') }}</label>
                                    <input type="text" class="form-control" name="name_dr" value="{{ old('name_dr') }}">
                                    @if ($errors->first('name_dr'))
                                        <div class="display-error">
                                            {{ $errors->first('name_dr') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ localize('global.name_en') }}</label>
                                    <input type="text" class="form-control" name="name_en" value="{{ old('name_en') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="sector_id">{{ localize('global.sector') }}</label>
                                    <select class="form-control select2" name="sector_id">
                                        <option value="">{{ localize('global.select') }}</option>
                                        @foreach ($sector as $value)
                                            <option value="{{ $value->id }}"
                                                    {{ old('sector_id') == $value->id ? 'selected' : '' }}>
                                                {{ $value->name_dr }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->first('sector_id'))
                                        <div class="display-error mb-3">
                                            {{ $errors->first('sector_id') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="type">{{ localize('global.type') }}</label>
                                    <select class="form-control select2" name="type">
                                        <option value="">{{ localize('global.select') }}</option>
                                        <option value="0" {{ old('type') == 0 ? 'selected' : '' }}>داخلی</option>
                                        <option value="1" {{ old('type') == 1 ? 'selected' : '' }}>خارجی</option>

                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"
                                           for="parent_id">{{ localize('global.recipient_parent') }}</label>
                                    <select class="form-control select2" name="parent_id">
                                        <option value="">{{ localize('global.select') }}</option>
                                        @foreach ($recipients as $value)
                                            <option value="{{ $value->id }}"
                                                    {{ old('parent_id') == $value->id ? 'selected' : '' }}>
                                                {{ $value->name_dr }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"
                                           for="parent_id">{{ localize('global.recipient_type') }}</label>
                                    <select class="form-control select2" name="category">
                                        <option value="">{{ localize('global.select') }}</option>
                                        @foreach($recipientTypes as $value)
                                            <option value="{{ $value->id }}"
                                                {{ old('name') == $value->id ? 'selected' : '' }}>
                                            {{ $value->name }}</option>
                                        @endforeach

                                    </select>
                                </div>
                                @if ($errors->first('recipient_type'))
                                    <div class="display-error mb-3">
                                        {{ $errors->first('recipient_type') }}
                                    </div>
                                @endif
                                <div class="col-md-12">
                                    <label class="form-label">{{ localize('global.description') }}</label>
                                    <textarea name="description" class="form-control">{{ old('description') }}</textarea>
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
