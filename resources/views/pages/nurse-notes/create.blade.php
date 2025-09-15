@extends('layouts.master')

@section('title', localize('global.create_new_nurse_note'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ localize('global.create_new_nurse_note') }}</h4>
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

                    @if(!isset($nurse))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong>{{ localize('global.nurse_profile_required') }}!</strong> {{ localize('global.no_nurse_profile_warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('nurse-notes.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nurse_id" class="form-label">{{ localize('global.nurse') }}</label>
                                    @if(isset($nurse))
                                        <input type="text" class="form-control" value="{{ $nurse->full_name }}" readonly>
                                        <input type="hidden" name="nurse_id" value="{{ $nurse->id }}">
                                        <div class="form-text">{{ localize('global.automatically_set_to_your_profile') }}</div>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> {{ localize('global.no_nurse_profile_found') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">{{ localize('global.date') }}</label>
                                    <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" 
                                           value="{{ old('date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="morphable_type" class="form-label">{{ localize('global.record_type') }} <span class="text-danger">*</span></label>
                                    @if(isset($morphableType))
                                        <input type="text" class="form-control" value="{{ $morphableType == 'App\Models\UnderReview' ? localize('global.under_review') : localize('global.hospitalization') }}" readonly>
                                        <input type="hidden" name="morphable_type" value="{{ $morphableType }}">
                                        <div class="form-text">{{ localize('global.automatically_set_from_context') }}</div>
                                    @else
                                        <select name="morphable_type" id="morphable_type" class="form-control @error('morphable_type') is-invalid @enderror" required>
                                            <option value="">{{ localize('global.select_record_type') }}</option>
                                            <option value="App\Models\UnderReview" {{ old('morphable_type') == 'App\Models\UnderReview' ? 'selected' : '' }}>{{ localize('global.under_review') }}</option>
                                            <option value="App\Models\Hospitalization" {{ old('morphable_type') == 'App\Models\Hospitalization' ? 'selected' : '' }}>{{ localize('global.hospitalization') }}</option>
                                        </select>
                                    @endif
                                    @error('morphable_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="morphable_id" class="form-label">{{ localize('global.record_id') }} <span class="text-danger">*</span></label>
                                    @if(isset($morphableId))
                                        <input type="text" class="form-control" value="{{ $morphableId }}" readonly>
                                        <input type="hidden" name="morphable_id" value="{{ $morphableId }}">
                                        <div class="form-text">{{ localize('global.automatically_set_from_context') }}</div>
                                    @else
                                        <input type="number" name="morphable_id" id="morphable_id" class="form-control @error('morphable_id') is-invalid @enderror" 
                                               value="{{ old('morphable_id') }}" min="1" required>
                                    @endif
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
                                           value="{{ old('time_am') }}" placeholder="Select AM time">
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
                                           value="{{ old('time_pm') }}" placeholder="Select PM time">
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
                                              rows="6" placeholder="{{ localize('global.enter_your_note') }}">{{ old('note') }}</textarea>
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
                                        <i class="fas fa-save"></i> {{ localize('global.save_nurse_note') }}
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
