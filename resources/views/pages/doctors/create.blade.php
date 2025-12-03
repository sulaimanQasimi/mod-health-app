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
                        <h5 class="mb-0">{{ localize('global.create_doctor') }}</h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('doctors.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name">{{localize('global.name')}} <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="father_name">{{localize('global.father_name')}}</label>
                                        <input type="text" name="father_name" id="father_name" value="{{ old('father_name') }}" class="form-control @error('father_name') is-invalid @enderror">
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
                                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>{{localize('global.male')}}</option>
                                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>{{localize('global.female')}}</option>
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_number">{{localize('global.contact_number')}} <span class="text-danger">*</span></label>
                                        <input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number') }}" class="form-control @error('contact_number') is-invalid @enderror"  required>
                                        @error('contact_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="address">{{localize('global.address')}}</label>
                                        <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror" >{{ old('address') }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="specialization">{{localize('global.specialization')}}</label>
                                        <input type="text" name="specialization" id="specialization" value="{{ old('specialization') }}" class="form-control @error('specialization') is-invalid @enderror" placeholder="e.g., Cardiology">
                                        @error('specialization')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="qualification">{{localize('global.qualification')}}</label>
                                        <input type="text" name="qualification" id="qualification" value="{{ old('qualification') }}" class="form-control @error('qualification') is-invalid @enderror" >
                                        @error('qualification')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="room_no">{{localize('global.room_no')}}</label>
                                        <input type="text" name="room_no" id="room_no" value="{{ old('room_no') }}" class="form-control @error('room_no') is-invalid @enderror" >
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
                                            <option value="hospital" {{ old('clinic_type') == 'hospital' ? 'selected' : '' }}>{{localize('global.hospital')}}</option>
                                            <option value="clinic" {{ old('clinic_type') == 'clinic' ? 'selected' : '' }}>{{localize('global.clinic')}}</option>
                                        </select>
                                        @error('clinic_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="join_date">{{localize('global.join_date')}}</label>
                                        <input type="text" name="join_date" id="join_date" value="{{ old('join_date') }}" class="form-control datepicker_dari @error('join_date') is-invalid @enderror">
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
                                                    {{ old('department_id') == $value->id ? 'selected' : '' }}>
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
                                        <label for="user_id">{{localize('global.user')}}</label>
                                        <select class="form-control select2 @error('user_id') is-invalid @enderror" name="user_id" id="user_id">
                                            <option value="">{{ localize('global.select') }}</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} {{ $user->last_name ? $user->last_name : '' }} ({{ $user->email }})</option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="active_status">{{localize('global.active_status')}}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="active_status" id="active_status" value="1" {{ old('active_status', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="active_status">
                                                {{localize('global.active')}}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('doctors.index') }}" class="btn btn-secondary">{{localize('global.cancel')}}</a>
                                <button type="submit" class="btn btn-primary">{{localize('global.create_account')}}</button>
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
