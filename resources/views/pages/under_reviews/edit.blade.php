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
                                
        <form action="{{ route('under_reviews.updateUnderReview',$underReview) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" id="patient_id{{ $underReview->appointment->patient_id }}" name="patient_id" value="{{ $underReview->appointment->patient_id }}">
            <input type="hidden" id="appointment_id{{ $underReview->appointment->id }}" name="appointment_id" value="{{ $underReview->appointment->id }}">
            <input type="hidden" id="doctor_id{{ $underReview->appointment->id }}" name="doctor_id" value="{{ auth()->user()->id }}">
            <input type="hidden" id="branch_id{{ $underReview->appointment->id }}" name="branch_id" value="{{ auth()->user()->branch_id }}">
            <input type="hidden" id="is_discharged{{ $underReview->appointment->id }}" name="is_discharged" value="0">

            <div class="form-group">
                <div class="form-group">
                    <label for="reason{{ $underReview->appointment->id }}">{{ localize('global.reason') }}</label>
                    <textarea class="form-control" id="reason{{ $underReview->appointment->id }}" name="reason" rows="3">{{$underReview->reason}}</textarea>
                </div>

                <div class="form-group">
                    <label for="remarks{{ $underReview->appointment->id }}">{{ localize('global.remarks') }}</label>
                    <textarea class="form-control" id="remarks{{ $underReview->appointment->id }}" name="remarks" rows="3">{{$underReview->remarks}}</textarea>
                </div>

                <div class="form-group">
                    <label for="room_id{{ $underReview->appointment->id }}">{{ localize('global.rooms') }}</label>
                    <select class="form-control select2" name="room_id" id="under_review_room">
                        <option value="">{{ localize('global.select') }}</option>
                        @foreach ($rooms as $value)
                            <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                {{ $value->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="bed_id{{ $underReview->appointment->id }}">{{ localize('global.beds') }}</label>
                    <select class="form-control select2" name="bed_id" id="under_review_bed_id">
                        <option value="">{{ localize('global.select') }}</option>
                        @foreach ($beds as $value)
                            <option value="{{ $value->id }}" {{ old('number') == $value->id ? 'selected' : '' }}>
                                {{ $value->number }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group mt-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
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
