@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>{{ localize('global.edit_prescription_details') }}</h4>
            </div>

            <form action="{{ route('lab_items.updateItem', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="testName" class="form-label">{{ localize('global.test_name') }}</label>
                            <select name="lab_type_id" id="testName" class="form-select select2" required>
                                @foreach($lab_types as $labType)
                                    <option value="{{ $labType->id }}" {{ $labType->id == $item->labType->id ? 'selected' : '' }}>
                                        {{ $labType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- <div class="col-md-4">
                            <label for="status" class="form-label">{{ localize('global.test_status') }}</label>
                            <select name="status" id="status" class="form-select">
                                <option value="0" {{ $item->status == 0 ? 'selected' : '' }}>
                                    {{ localize('global.not_tested') }}
                                </option>
                                <option value="1" {{ $item->status == 1 ? 'selected' : '' }}>
                                    {{ localize('global.tested') }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="isDelivered" class="form-label">{{ localize('global.delivery_status') }}</label>
                            <select name="is_delivered" id="isDelivered" class="form-select">
                                <option value="0" {{ $item->is_delivered == 0 ? 'selected' : '' }}>
                                    {{ localize('global.not_delivered') }}
                                </option>
                                <option value="1" {{ $item->is_delivered == 1 ? 'selected' : '' }}>
                                    {{ localize('global.delivered') }}
                                </option>
                            </select>
                        </div> --}}
                    </div>

                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary btn-sm m-2">
                            <span class="bx bx-save"></span>{{ localize('global.save_changes') }}
                        </button>
                        <a href="{{ route('appointments.doctorAppointments') }}" class="btn btn-danger m-2">{{ localize('global.back') }}</a>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection