@extends('layouts.master')

@section('title', localize('global.create_vital_sign'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus"></i> {{ localize('global.create_vital_sign') }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('vital-signs.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ localize('global.back_to_list') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('vital-signs.store') }}" method="POST">
                        @csrf
                        <!-- Read-only morphable information -->
                        @if($morphableType && $morphableId)
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ localize('global.related_record') }}</label>
                                    <div class="form-control-plaintext bg-light p-2 rounded">
                                        <i class="fas fa-link text-primary"></i> 
                                        {{ class_basename($morphableType) }} #{{ $morphableId }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vital_sign_type_id">{{ localize('global.vital_sign_type_id') }} <span class="text-danger">*</span></label>
                                    <select class="form-control @error('vital_sign_type_id') is-invalid @enderror" 
                                            id="vital_sign_type_id" name="vital_sign_type_id" required>
                                        <option value="">{{ localize('global.select_vital_sign_type') }}</option>
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
                        </div>
                        
                        <!-- Hidden fields for morphable data -->
                        @if($morphableType && $morphableId)
                        <input type="hidden" name="morphable_type" value="{{ $morphableType }}">
                        <input type="hidden" name="morphable_id" value="{{ $morphableId }}">
                        @endif
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ localize('global.create_vital_sign') }}
                                </button>
                                <a href="{{ route('vital-signs.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> {{ localize('global.cancel') }}
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
