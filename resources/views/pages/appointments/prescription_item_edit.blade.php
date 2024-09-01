@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ localize('global.edit_prescription_details') }}</h4>
            </div>

    <form action="{{ route('prescription_items.updateItem', $item->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="medicineType" class="form-label">{{ localize('global.type') }}</label>
                <select name="medicine_type_id" id="medicineType" class="form-select" required>
                    @foreach($medicineTypes as $type)
                        <option value="{{ $type->id }}" {{ $type->id == $item->medicineType->id ? 'selected' : '' }}>
                            {{ $type->type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="medicineName" class="form-label">{{ localize('global.description') }}</label>
                <input type="text" name="medicine_name" id="medicineName" class="form-control" 
                       value="{{ $item->medicine->name }}" required>
            </div>

            <div class="col-md-4">
                <label for="dosage" class="form-label">{{ localize('global.dosage') }}</label>
                <input type="text" name="dosage" id="dosage" class="form-control" 
                       value="{{ $item->dosage }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="frequency" class="form-label">{{ localize('global.frequency') }}</label>
                <input type="text" name="frequency" id="frequency" class="form-control" 
                       value="{{ $item->frequency }}" required>
            </div>

            <div class="col-md-4">
                <label for="amount" class="form-label">{{ localize('global.amount') }}</label>
                <input type="number" name="amount" id="amount" class="form-control" 
                       value="{{ $item->amount }}" required>
            </div>

            <div class="col-md-4">
                <label for="status" class="form-label">{{ localize('global.status') }}</label>
                <select name="is_delivered" id="status" class="form-select">
                    <option value="0" {{ $item->is_delivered == 0 ? 'selected' : '' }}>
                        {{ localize('global.not_delivered') }}
                    </option>
                    <option value="1" {{ $item->is_delivered == 1 ? 'selected' : '' }}>
                        {{ localize('global.delivered') }}
                    </option>
                </select>
            </div>
        </div>

        <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-primary btn-sm m-2">
                <span class="bx bx-save"></span>{{ localize('global.save_changes') }}
            </button>
            <a href="{{ route('appointments.index') }}" class="btn btn-danger m-2">{{ localize('global.back') }}</a>
        </div>
    </div>
    </form>

    <div class="d-flex justify-content-center mt-4">
        
    </div>
        </div>
    </div>
</div>
@endsection