@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.edit_icu_procedure') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('procedures.update', $iCUProcedure->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <input type="hidden" id="i_c_u_id" name="i_c_u_id" value="{{ $iCUProcedure->i_c_u_id }}">

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="icu_procedure_type_id">{{ localize('global.procedure_type') }}</label>
                                        <select class="form-control select2" name="icu_procedure_type_id"
                                            id="icu_procedure_type_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach ($procedure_types as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ $iCUProcedure->icu_procedure_type_id == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <label for="description">{{ localize('global.description') }}</label>
                                        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $iCUProcedure->description) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('procedures.index') }}"
                                    class="btn btn-secondary">{{ localize('global.cancel') }}</a>
                                <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
