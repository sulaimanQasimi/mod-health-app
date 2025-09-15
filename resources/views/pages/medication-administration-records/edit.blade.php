@extends('layouts.master')

@section('title', localize('global.edit_mar'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">
                        <i class="fas fa-pills"></i> {{ localize('global.edit_mar') }}
                    </h4>
                    <a href="{{ route('medication-administration-records.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ localize('global.mar_back_to_list') }}
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

                    <form action="{{ route('medication-administration-records.update', $medicationAdministrationRecord) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="medicine_id" class="form-label">{{ localize('global.medicine') }} <span class="text-danger">*</span></label>
                                    <select name="medicine_id" id="medicine_id" class="form-control @error('medicine_id') is-invalid @enderror" required>
                                        <option value="">{{ localize('global.mar_select_medicine') }}</option>
                                        @foreach($medicines as $medicine)
                                            <option value="{{ $medicine->id }}" {{ old('medicine_id', $medicationAdministrationRecord->medicine_id) == $medicine->id ? 'selected' : '' }}>
                                                {{ $medicine->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('medicine_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nurse_id" class="form-label">{{ localize('global.nurse') }}</label>
                                    <input type="text" class="form-control" value="{{ $medicationAdministrationRecord->nurse->full_name ?? 'N/A' }}" readonly>
                                    <div class="form-text">{{ localize('global.mar_automatically_set_to_your_profile') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order_date" class="form-label">{{ localize('global.order_date') }}</label>
                                    <input type="date" name="order_date" id="order_date" class="form-control @error('order_date') is-invalid @enderror" 
                                           value="{{ old('order_date', $medicationAdministrationRecord->order_date?->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
                                    @error('order_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_signature" class="form-label">{{ localize('global.signature_date') }}</label>
                                    <input type="text" class="form-control" value="{{ $medicationAdministrationRecord->date_signature?->format('Y-m-d') ?? 'N/A' }}" readonly>
                                    <div class="form-text">{{ localize('global.automatically_set_from_context') }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Administration Times Section -->
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ localize('global.administration_times') }}</label>
                                    <div id="administration-times-container">
                                        @if($medicationAdministrationRecord->administrationTimes->count() > 0)
                                            @foreach($medicationAdministrationRecord->administrationTimes as $time)
                                                <div class="administration-time-row mb-2">
                                                    <div class="input-group">
                                                        <input type="time" name="administration_times[]" class="form-control" 
                                                               value="{{ $time->time?->format('H:i') }}" placeholder="Select time">
                                                        <button type="button" class="btn btn-outline-danger remove-time-btn">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="administration-time-row mb-2">
                                                <div class="input-group">
                                                    <input type="time" name="administration_times[]" class="form-control" placeholder="Select time">
                                                    <button type="button" class="btn btn-outline-danger remove-time-btn" style="display: none;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-time-btn">
                                        <i class="fas fa-plus"></i> {{ localize('global.add_time') }}
                                    </button>
                                    <div class="form-text">{{ localize('global.mar_add_multiple_times') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('medication-administration-records.index') }}" class="btn btn-secondary">{{ localize('global.mar_cancel') }}</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ localize('global.update_mar') }}
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
    const container = document.getElementById('administration-times-container');
    const addBtn = document.getElementById('add-time-btn');
    
    // Add new time row
    addBtn.addEventListener('click', function() {
        const newRow = document.createElement('div');
        newRow.className = 'administration-time-row mb-2';
        newRow.innerHTML = `
            <div class="input-group">
                <input type="time" name="administration_times[]" class="form-control" placeholder="Select time">
                <button type="button" class="btn btn-outline-danger remove-time-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(newRow);
        updateRemoveButtons();
    });
    
    // Remove time row
    container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-time-btn')) {
            e.target.closest('.administration-time-row').remove();
            updateRemoveButtons();
        }
    });
    
    // Update remove button visibility
    function updateRemoveButtons() {
        const rows = container.querySelectorAll('.administration-time-row');
        const removeBtns = container.querySelectorAll('.remove-time-btn');
        
        removeBtns.forEach((btn, index) => {
            btn.style.display = rows.length > 1 ? 'block' : 'none';
        });
    }
    
    // Initialize
    updateRemoveButtons();
});
</script>
@endsection
