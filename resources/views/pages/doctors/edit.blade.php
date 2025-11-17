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
                        <h5 class="mb-0">{{ localize('global.edit_doctor') }}</h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('doctors.update', $doctor->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name">{{localize('global.name')}} <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" value="{{ old('name', $doctor->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="father_name">{{localize('global.father_name')}}</label>
                                        <input type="text" name="father_name" id="father_name" value="{{ old('father_name', $doctor->father_name) }}" class="form-control @error('father_name') is-invalid @enderror" placeholder="{{ localize('global.enter_father_name') }}">
                                        @error('father_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gender">{{localize('global.gender')}} <span class="text-danger">*</span></label>
                                        <select class="form-control select2 @error('gender') is-invalid @enderror" name="gender" id="gender" required>
                                            <option value="">{{ localize('global.select') }}</option>
                                            <option value="Male" {{ old('gender', $doctor->gender) == 'Male' ? 'selected' : '' }}>{{ localize('global.male') }}</option>
                                            <option value="Female" {{ old('gender', $doctor->gender) == 'Female' ? 'selected' : '' }}>{{ localize('global.female') }}</option>
                                            <option value="Other" {{ old('gender', $doctor->gender) == 'Other' ? 'selected' : '' }}>{{ localize('global.other') }}</option>
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_number">{{localize('global.contact_number')}} <span class="text-danger">*</span></label>
                                        <input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number', $doctor->contact_number) }}" class="form-control @error('contact_number') is-invalid @enderror" placeholder="{{ localize('global.phone') }}" required>
                                        @error('contact_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="address">{{localize('global.address')}}</label>
                                        <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror" placeholder="{{ localize('global.enter_full_address') }}">{{ old('address', $doctor->address) }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="specialization">{{localize('global.specialization')}}</label>
                                        <input type="text" name="specialization" id="specialization" value="{{ old('specialization', $doctor->specialization) }}" class="form-control @error('specialization') is-invalid @enderror" placeholder="{{ localize('global.example_specialization') }}">
                                        @error('specialization')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="qualification">{{localize('global.qualification')}}</label>
                                        <input type="text" name="qualification" id="qualification" value="{{ old('qualification', $doctor->qualification) }}" class="form-control @error('qualification') is-invalid @enderror" placeholder="{{ localize('global.example_qualification') }}">
                                        @error('qualification')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="room_no">{{localize('global.room_no')}}</label>
                                        <input type="text" name="room_no" id="room_no" value="{{ old('room_no', $doctor->room_no) }}" class="form-control @error('room_no') is-invalid @enderror" placeholder="{{ localize('global.room_number') }}">
                                        @error('room_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="clinic_type">{{localize('global.clinic_type')}}</label>
                                        <select class="form-control select2 @error('clinic_type') is-invalid @enderror" name="clinic_type" id="clinic_type">
                                            <option value="">{{ localize('global.select') }}</option>
                                            <option value="hospital" {{ old('clinic_type', $doctor->clinic_type) == 'hospital' ? 'selected' : '' }}>{{ localize('global.hospital') }}</option>
                                            <option value="clinic" {{ old('clinic_type', $doctor->clinic_type) == 'clinic' ? 'selected' : '' }}>{{ localize('global.clinic') }}</option>
                                        </select>
                                        @error('clinic_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="join_date">{{localize('global.join_date')}}</label>
                                        <input type="text" name="join_date" id="join_date" value="{{ old('join_date', $doctor->join_date ? \Carbon\Carbon::parse($doctor->join_date)->format('Y-m-d') : '') }}" class="form-control datepicker_dari @error('join_date') is-invalid @enderror">
                                        @error('join_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="department_id">{{localize('global.department')}} <span class="text-danger">*</span></label>
                                        <select class="form-control select2 @error('department_id') is-invalid @enderror" name="department_id" id="department_id" required>
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($departments as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ old('department_id', $doctor->department_id) == $value->id ? 'selected' : '' }}>
                                                {{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('department_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="active_status">{{localize('global.active_status')}}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="active_status" id="active_status" value="1" {{ old('active_status', $doctor->active_status ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="active_status">
                                                {{localize('global.active')}}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('doctors.index') }}" class="btn btn-secondary">{{localize('global.cancel')}}</a>
                                <button type="submit" class="btn btn-primary">{{localize('global.update')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')

@endsection

