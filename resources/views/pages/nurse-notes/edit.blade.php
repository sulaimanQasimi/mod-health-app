@extends('layouts.master')

@section('title', localize('global.edit_nurse_note'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ localize('global.edit_nurse_note') }}</h4>
                    <a href="{{ route('nurse-notes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ localize('global.back_to_list') }}
                    </a>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('nurse-notes.update', $nurseNote) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nurse_id" class="form-label">Nurse <span class="text-danger">*</span></label>
                                    <select name="nurse_id" id="nurse_id" class="form-control @error('nurse_id') is-invalid @enderror" required>
                                        <option value="">Select Nurse</option>
                                        @foreach($nurses as $nurse)
                                            <option value="{{ $nurse->id }}" {{ old('nurse_id', $nurseNote->nurse_id) == $nurse->id ? 'selected' : '' }}>
                                                {{ $nurse->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('nurse_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">{{ localize('global.date') }}</label>
                                    <input type="date" autocomplete="off" name="date" id="date" class="form-control @error('date') is-invalid @enderror" 
                                           value="{{ old('date', $nurseNote->date?->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="morphable_type" class="form-label">Record Type <span class="text-danger">*</span></label>
                                    <select name="morphable_type" id="morphable_type" class="form-control @error('morphable_type') is-invalid @enderror" required>
                                        <option value="">Select Record Type</option>
                                        <option value="App\Models\UnderReview" {{ old('morphable_type', $nurseNote->morphable_type) == 'App\Models\UnderReview' ? 'selected' : '' }}>Under Review</option>
                                        <option value="App\Models\Hospitalization" {{ old('morphable_type', $nurseNote->morphable_type) == 'App\Models\Hospitalization' ? 'selected' : '' }}>Hospitalization</option>
                                    </select>
                                    @error('morphable_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="morphable_id" class="form-label">Record ID <span class="text-danger">*</span></label>
                                    <input type="number" name="morphable_id" id="morphable_id" class="form-control @error('morphable_id') is-invalid @enderror" 
                                           value="{{ old('morphable_id', $nurseNote->morphable_id) }}" min="1" required>
                                    @error('morphable_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="time_am" class="form-label">{{ localize('global.nurse_note_am_time') }}</label>
                                    <input type="time" name="time_am" id="time_am" class="form-control @error('time_am') is-invalid @enderror" 
                                           value="{{ old('time_am', $nurseNote->time_am?->format('H:i')) }}" placeholder="Select AM time">
                                    <div class="form-text">{{ localize('global.select_am_time') }}</div>
                                    @error('time_am')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="time_pm" class="form-label">{{ localize('global.nurse_note_pm_time') }}</label>
                                    <input type="time" name="time_pm" id="time_pm" class="form-control @error('time_pm') is-invalid @enderror" 
                                           value="{{ old('time_pm', $nurseNote->time_pm?->format('H:i')) }}" placeholder="Select PM time">
                                    <div class="form-text">{{ localize('global.select_pm_time') }}</div>
                                    @error('time_pm')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="note" class="form-label">{{ localize('global.note') }}</label>
                                    <textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror" 
                                              rows="6" placeholder="{{ localize('global.enter_your_note') }}">{{ old('note', $nurseNote->note) }}</textarea>
                                    <div class="form-text">{{ localize('global.maximum_5000_characters') }}</div>
                                    @error('note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('nurse-notes.index') }}" class="btn btn-secondary">{{ localize('global.cancel') }}</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ localize('global.update_nurse_note') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter for note textarea
    const note = document.getElementById('note');
    
    function updateCharCount(textarea, maxLength = 5000) {
        const currentLength = textarea.value.length;
        const remaining = maxLength - currentLength;
        
        let counter = textarea.parentNode.querySelector('.char-counter');
        if (!counter) {
            counter = document.createElement('small');
            counter.className = 'char-counter text-muted';
            textarea.parentNode.appendChild(counter);
        }
        
        counter.textContent = `${currentLength}/${maxLength} {{ localize('global.characters') }}`;
        
        if (remaining < 100) {
            counter.className = 'char-counter text-warning';
        } else {
            counter.className = 'char-counter text-muted';
        }
    }
    
    if (note) {
        note.addEventListener('input', () => updateCharCount(note));
        // Initialize counter
        updateCharCount(note);
    }
});
</script>
@endsection
