@extends('layouts.master')

@section('title', localize('edit_vital_sign_schedule'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i> {{ localize('edit_vital_sign_schedule') }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vital-sign-schedules.show', $schedule) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ localize('back_to_details') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('vital-sign-schedules.update', $schedule) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vital_sign_id">{{ localize('vital_sign') }} <span class="text-danger">*</span></label>
                                    <select class="form-control @error('vital_sign_id') is-invalid @enderror" 
                                            id="vital_sign_id" name="vital_sign_id" required>
                                        <option value="">{{ localize('select_vital_sign') }}</option>
                                        @foreach($vitalSigns as $vitalSign)
                                            <option value="{{ $vitalSign->id }}" {{ old('vital_sign_id', $schedule->vital_sign_id) == $vitalSign->id ? 'selected' : '' }}>
                                                {{ $vitalSign->vitalSignType->name ?? 'N/A' }} - {{ class_basename($vitalSign->morphable_type) }} #{{ $vitalSign->morphable_id }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vital_sign_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nurse_id">{{ localize('responsible_nurse') }}</label>
                                    @if($currentUserNurse)
                                        <!-- If user has a nurse profile, automatically select it and show as read-only -->
                                        <input type="hidden" name="nurse_id" value="{{ $currentUserNurse->id }}">
                                        <div class="form-control-plaintext bg-light p-2 rounded">
                                            <i class="fas fa-user-nurse text-primary"></i> 
                                            {{ $currentUserNurse->full_name }}
                                            <small class="text-muted d-block">{{ localize('automatically_selected') }}</small>
                                        </div>
                                    @else
                                        <!-- If user doesn't have a nurse profile, show dropdown -->
                                        <select class="form-control @error('nurse_id') is-invalid @enderror" 
                                                id="nurse_id" name="nurse_id">
                                            <option value="">{{ localize('select_nurse') }} ({{ localize('optional') }})</option>
                                            @foreach($nurses as $nurse)
                                                <option value="{{ $nurse->id }}" {{ old('nurse_id', $schedule->nurse_id) == $nurse->id ? 'selected' : '' }}>
                                                    {{ $nurse->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('nurse_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="day">{{ localize('day') }}</label>
                                    <input type="text" class="form-control @error('day') is-invalid @enderror" 
                                           id="day" name="day" value="{{ old('day', $schedule->day) }}" 
                                           placeholder="{{ localize('enter_day') }}">
                                    @error('day')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">{{ localize('date') }}</label>
                                    <input type="text" class="form-control datepicker_dari @error('date') is-invalid @enderror" 
                                           id="date" name="date" value="{{ old('date', $schedule->date ? verta($schedule->date)->format('Y/m/d') : '') }}" 
                                           placeholder="1403/01/01" autocomplete="off">
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="morning_time">{{ localize('morning_time') }}</label>
                                    <input type="text" class="form-control @error('morning_time') is-invalid @enderror" 
                                           id="morning_time" name="morning_time" value="{{ old('morning_time', $schedule->morning_time ?? '') }}" 
                                           placeholder="{{ localize('enter_morning_time') }}">
                                    @error('morning_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="evening_time">{{ localize('evening_time') }}</label>
                                    <input type="text" class="form-control @error('evening_time') is-invalid @enderror" 
                                           id="evening_time" name="evening_time" value="{{ old('evening_time', $schedule->evening_time ?? '') }}" 
                                           placeholder="{{ localize('enter_evening_time') }}">
                                    @error('evening_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ localize('update') }}
                                </button>
                                <a href="{{ route('vital-sign-schedules.show', $schedule) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> {{ localize('cancel') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
$(document).ready(function() {
    if (typeof $ !== 'undefined' && typeof $.fn.persianDatepicker === 'function') {
        var $dateInput = $('#date.datepicker_dari');
        if ($dateInput.length && !$dateInput.data('datepicker')) {
            $dateInput.persianDatepicker({
                months: ["حمل", "ثور", "جوزا", "سرطان", "اسد", "سنبله", "میزان", "عقرب", "قوس", "جدی", "دلو", "حوت"],
                dowTitle: ["شنبه", "یکشنبه", "دوشنبه", "سه شنبه", "چهارشنبه", "پنج شنبه", "جمعه"],
                shortDowTitle: ["ش", "ی", "د", "س", "چ", "پ", "ج"],
                showGregorianDate: false,
                persianNumbers: true,
                formatDate: 'YYYY/MM/DD',
                selectedBefore: false,
                theme: 'default'
            });
        }
    }
});
</script>
@endpush
@endsection
