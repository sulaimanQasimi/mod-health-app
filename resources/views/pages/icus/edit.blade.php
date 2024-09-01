@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
<div class="content-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ localize('global.edit_icu_referral') }}</h5>
        </div>

        <div class="card-body">



    <form action="{{ route('icus.updateICU', $icu->id) }}" method="POST">
        @csrf
        @method('PUT')

        <input type="hidden" name="patient_id" value="{{ $icu->patient_id }}">
        <input type="hidden" name="appointment_id" value="{{ $icu->appointment_id }}">
        <input type="hidden" name="doctor_id" value="{{ $icu->doctor_id }}">
        <input type="hidden" name="branch_id" value="{{ $icu->branch_id }}">

        <div class="form-group mb-3">
            <label for="description">{{ localize('global.description') }}</label>
            <textarea class="form-control" id="description" name="description" rows="4">{{ $icu->description }}</textarea>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
            <a href="{{ route('icus.index') }}" class="btn btn-secondary">{{ localize('global.cancel') }}</a>
        </div>
    </form>
        </div>
    </div>
</div>
</div>
@endsection
