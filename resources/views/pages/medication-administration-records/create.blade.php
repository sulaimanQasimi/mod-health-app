@extends('layouts.master')

@section('title', localize('global.create_new_mar'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">
                            <i class="fas fa-pills"></i> {{ localize('global.create_new_mar') }}
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

                        <form action="{{ route('medication-administration-records.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="medicine_id" class="form-label">{{ localize('global.medicine') }} <span
                                                class="text-danger">*</span></label>
                                        <select name="medicine_id" id="medicine_id"
                                            class="form-control @error('medicine_id') is-invalid @enderror" required>
                                            <option value="">{{ localize('global.mar_select_medicine') }}</option>
                                            @foreach($medicines as $medicine)
                                                <option value="{{ $medicine->id }}" {{ old('medicine_id') == $medicine->id ? 'selected' : '' }}>
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
                                        @if(isset($nurse))
                                            <input type="text" class="form-control" value="{{ $nurse->full_name }}" readonly>
                                            <input type="hidden" name="nurse_id" value="{{ $nurse->id }}">
                                            <div class="form-text">
                                                {{ localize('global.mar_automatically_set_to_your_profile') }}</div>
                                        @else
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ localize('global.mar_no_nurse_profile_warning') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="order_date"
                                            class="form-label">{{ localize('global.order_date') }}</label>
                                        <input type="date" name="order_date" id="order_date"
                                            class="form-control @error('order_date') is-invalid @enderror"
                                            value="{{ old('order_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
                                        @error('order_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date_signature"
                                            class="form-label">{{ localize('global.signature_date') }}</label>
                                        <input type="text" class="form-control" value="{{ date('Y-m-d') }}" readonly>
                                        <div class="form-text">{{ localize('global.automatically_set_from_context') }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Patient Record Information -->
                            @if(isset($morphableRecord))
                                <div class="row">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-user-injured"></i>
                                                {{ localize('global.patient_record_information') }}</h6>
                                            <p><strong>{{ localize('global.record_type') }}:</strong>
                                                @if($morphableType == 'App\Models\UnderReview')
                                                    {{ localize('global.under_review') }}
                                                @elseif($morphableType == 'App\Models\Hospitalization')
                                                    {{ localize('global.hospitalization') }}
                                                @endif
                                            </p>
                                            <p><strong>{{ localize('global.record_id') }}:</strong> {{ $morphableRecord->id }}
                                            </p>
                                            @if(isset($morphableRecord->patient))
                                                <p><strong>{{ localize('global.patient') }}:</strong>
                                                    {{ $morphableRecord->patient->full_name ?? 'N/A' }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="morphable_type"
                                            class="form-label">{{ localize('global.select_record_type') }} <span
                                                class="text-danger">*</span></label>
                                        @if(isset($morphableType))
                                            <input type="text" class="form-control"
                                                value="{{ $morphableType == 'App\Models\UnderReview' ? localize('global.under_review') : localize('global.hospitalization') }}"
                                                readonly>
                                            <input type="hidden" name="morphable_type" value="{{ $morphableType }}">
                                            <div class="form-text">{{ localize('global.automatically_set_from_context') }}</div>
                                        @else
                                            <select name="morphable_type" id="morphable_type"
                                                class="form-control @error('morphable_type') is-invalid @enderror" required>
                                                <option value="">{{ localize('global.select_record_type') }}</option>
                                                <option value="App\Models\UnderReview" {{ old('morphable_type') == 'App\Models\UnderReview' ? 'selected' : '' }}>
                                                    {{ localize('global.under_review') }}</option>
                                                <option value="App\Models\Hospitalization" {{ old('morphable_type') == 'App\Models\Hospitalization' ? 'selected' : '' }}>
                                                    {{ localize('global.hospitalization') }}</option>
                                            </select>
                                        @endif
                                        @error('morphable_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="morphable_id" class="form-label">{{ localize('global.record_id') }}
                                            <span class="text-danger">*</span></label>
                                        @if(isset($morphableId))
                                            <input type="text" class="form-control" value="{{ $morphableId }}" readonly>
                                            <input type="hidden" name="morphable_id" value="{{ $morphableId }}">
                                            <div class="form-text">{{ localize('global.automatically_set_from_context') }}</div>
                                        @else
                                            <input type="number" name="morphable_id" id="morphable_id"
                                                class="form-control @error('morphable_id') is-invalid @enderror"
                                                value="{{ old('morphable_id') }}" min="1" required>
                                        @endif
                                        @error('morphable_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Administration Times Section -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">{{ localize('global.administration_times') }}</label>
                                        <div id="administration-times-container">
                                            <div class="administration-time-row mb-2">
                                                <div class="input-group">
                                                    <input type="time" name="administration_times[]" class="form-control"
                                                        placeholder="Select time">
                                                    <button type="button" class="btn btn-outline-danger remove-time-btn"
                                                        style="display: none;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
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
                                        <a href="{{ route('medication-administration-records.index') }}"
                                            class="btn btn-secondary">{{ localize('global.mar_cancel') }}</a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> {{ localize('global.save_mar') }}
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
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('administration-times-container');
            const addBtn = document.getElementById('add-time-btn');

            // Add new time row
            addBtn.addEventListener('click', function () {
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
            container.addEventListener('click', function (e) {
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