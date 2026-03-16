@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">{{ localize('global.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dentist-registrations.index') }}" class="text-decoration-none">{{ localize('global.dentist_registrations') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dentist-registrations.show', $dentistRegistration) }}" class="text-decoration-none">{{ localize('global.registration_details') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ localize('global.edit') }}</li>
                        </ol>
                    </nav>
                    <h2 class="h4 mb-0">{{ localize('global.edit_dentist_registration') }}</h2>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.edit_registration') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('dentist-registrations.update', $dentistRegistration) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="dentist_id" class="form-label">{{ localize('global.dentist') }}</label>
                                <select class="form-select @error('dentist_id') is-invalid @enderror" id="dentist_id" name="dentist_id">
                                    <option value="">{{ localize('global.select_dentist') }}</option>
                                    @foreach($dentists as $dentist)
                                        <option value="{{ $dentist->id }}" {{ old('dentist_id', $dentistRegistration->dentist_id) == $dentist->id ? 'selected' : '' }}>
                                            {{ $dentist->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('dentist_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="registration_date" class="form-label">{{ localize('global.registration_date') }} <span class="text-danger">*</span></label>
                                <input type="date" autocomplete="off" class="form-control @error('registration_date') is-invalid @enderror" 
                                    id="registration_date" name="registration_date" 
                                    value="{{ old('registration_date', $dentistRegistration->registration_date->format('Y-m-d')) }}" required>
                                @error('registration_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">{{ localize('global.status') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="pending" {{ old('status', $dentistRegistration->status) == 'pending' ? 'selected' : '' }}>{{ localize('global.pending') }}</option>
                                    <option value="in_progress" {{ old('status', $dentistRegistration->status) == 'in_progress' ? 'selected' : '' }}>{{ localize('global.in_progress') }}</option>
                                    <option value="completed" {{ old('status', $dentistRegistration->status) == 'completed' ? 'selected' : '' }}>{{ localize('global.completed') }}</option>
                                    <option value="cancelled" {{ old('status', $dentistRegistration->status) == 'cancelled' ? 'selected' : '' }}>{{ localize('global.cancelled') }}</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">{{ localize('global.notes') }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                    id="notes" name="notes" rows="3">{{ old('notes', $dentistRegistration->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('dentist-registrations.show', $dentistRegistration) }}" class="btn btn-secondary">
                                {{ localize('global.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save"></i> {{ localize('global.update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
