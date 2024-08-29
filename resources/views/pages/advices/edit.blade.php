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
                        <h5 class="mb-0">{{ localize('global.edit_advice') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <form action="{{ route('advices.update', $advice) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" id="patient_id{{ $advice->appointment->patient_id }}"
                                            name="patient_id" value="{{ $advice->appointment->patient_id }}">
                                        <input type="hidden" id="appointment_id{{ $advice->appointment->id }}"
                                            name="appointment_id" value="{{ $advice->appointment->id }}">
                                            <input type="hidden" id="doctor_id{{ $advice->appointment->id }}"
                                            name="doctor_id" value="{{ auth()->user()->id }}">

                                        <div class="form-group">

                                            <label
                                                for="description{{ $advice->appointment->id }}">{{ localize('global.description') }}</label>
                                            <textarea class="form-control" id="description{{ $advice->appointment->id }}" name="description" rows="3">{{$advice->description}}</textarea>

                                        </div>
                                        <div class="form-group mt-2">
                                            <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
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
