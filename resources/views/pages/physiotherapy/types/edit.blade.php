@extends('layouts.master')

@section('title', localize('global.edit_physiotherapy_type'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">{{ localize('global.edit_physiotherapy_type') }}</h4>
                        <a href="{{ route('physiotherapy-types.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ localize('global.back_to_list') }}
                        </a>
                    </div>
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

                    <form action="{{ route('physiotherapy-types.update', $physiotherapyType) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        {{ localize('global.name') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $physiotherapyType->name) }}" 
                                           required 
                                           maxlength="255"
                                           placeholder="{{ localize('global.enter_physiotherapy_type_name') }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">{{ localize('global.name_help_text') }}</div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        {{ localize('global.description') }}
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="4" 
                                              maxlength="1000"
                                              placeholder="{{ localize('global.enter_physiotherapy_type_description') }}">{{ old('description', $physiotherapyType->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        {{ localize('global.description_help_text') }} ({{ localize('global.max_1000_characters') }})
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h6 class="mb-0">{{ localize('global.information') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i> 
                                                {{ localize('global.physiotherapy_type_info') }}
                                            </small>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-exclamation-triangle"></i> 
                                                {{ localize('global.name_must_be_unique') }}
                                            </small>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> 
                                                {{ localize('global.created_at') }}: {{ $physiotherapyType->created_at->format('Y-m-d H:i') }}
                                            </small>
                                        </div>
                                        @if($physiotherapyType->updated_at != $physiotherapyType->created_at)
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-edit"></i> 
                                                    {{ localize('global.last_updated') }}: {{ $physiotherapyType->updated_at->format('Y-m-d H:i') }}
                                                </small>
                                            </div>
                                        @endif
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-list"></i> 
                                                {{ localize('global.total_procedures') }}: {{ $physiotherapyType->physiotherapyProcedures->count() }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ localize('global.update_physiotherapy_type') }}
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fas fa-undo"></i> {{ localize('global.reset') }}
                                    </button>
                                    <a href="{{ route('physiotherapy-types.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> {{ localize('global.cancel') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Character counter for description
    const description = document.getElementById('description');
    const maxLength = 1000;
    
    description.addEventListener('input', function() {
        const remaining = maxLength - this.value.length;
        const helpText = this.nextElementSibling.nextElementSibling;
        helpText.textContent = `${localize('global.description_help_text')} (${remaining} ${localize('global.characters_remaining')})`;
        
        if (remaining < 50) {
            helpText.classList.add('text-warning');
        } else {
            helpText.classList.remove('text-warning');
        }
    });
</script>
@endpush
