@extends('layouts.master')

@section('title', localize('create_vital_sign'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus"></i> {{ localize('create_vital_sign') }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vital-signs.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ localize('back_to_list') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('vital-signs.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vital_sign_type_id">{{ localize('vital_sign_type_id') }} <span class="text-danger">*</span></label>
                                    <select class="form-control @error('vital_sign_type_id') is-invalid @enderror" 
                                            id="vital_sign_type_id" name="vital_sign_type_id" required>
                                        <option value="">{{ localize('select_vital_sign_type') }}</option>
                                        @foreach($vitalSignTypes as $type)
                                            <option value="{{ $type->id }}" {{ old('vital_sign_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vital_sign_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="morphable_type">{{ localize('morphable_type') }} <span class="text-danger">*</span></label>
                                    <select class="form-control @error('morphable_type') is-invalid @enderror" 
                                            id="morphable_type" name="morphable_type" required>
                                        <option value="">{{ localize('select_morphable_type') }}</option>
                                        <option value="App\Models\UnderReview" {{ old('morphable_type') == 'App\Models\UnderReview' ? 'selected' : '' }}>
                                            {{ localize('under_review') }}
                                        </option>
                                        <option value="App\Models\Hospitalization" {{ old('morphable_type') == 'App\Models\Hospitalization' ? 'selected' : '' }}>
                                            {{ localize('hospitalization') }}
                                        </option>
                                    </select>
                                    @error('morphable_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="morphable_id">{{ localize('morphable_id') }} <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('morphable_id') is-invalid @enderror" 
                                           id="morphable_id" name="morphable_id" value="{{ old('morphable_id', $morphableId) }}" 
                                           placeholder="{{ localize('enter_morphable_id') }}" required>
                                    @error('morphable_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ localize('create_vital_sign') }}
                                </button>
                                <a href="{{ route('vital-signs.index') }}" class="btn btn-secondary">
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
@endsection
