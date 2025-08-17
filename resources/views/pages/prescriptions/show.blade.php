@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.prescription_details') }}</h5>
                        <div class="pt-3 pt-md-0 text-end">
                            <a class="btn btn-danger" href="{{ url()->previous() }}" type="button">
                                <span class="text-white"> <span
                                        class="d-none d-sm-inline-block  ">{{ localize('global.back') }}</span></span>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">


                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>

                                    <tr>
                                        <td>{{ $prescription->id }}</td>
                                        <td>{{ $prescription->patient->name }}</td>
                                        <td>
                                            @if ($prescription->is_completed == '0')
                                                <span
                                                    class="badge bg-danger">{{ localize('global.not_delivered') }}</span>
                                            @else
                                                <span
                                                    class="badge bg-success">{{ localize('global.delivered') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($prescription->is_completed == '0')
                                            <div class="d-flex justify-content-center text-center mt-2">
                                                <form action="{{ route('prescriptions.changeStatus', $prescription) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="is_completed" value="1">
                                                    <button type="submit" class="btn btn-success">
                                                        <span><i class="bx bx-check-shield"></i></span>
                                                    </button>
                                                </form>
                                            </div>
                                            @endif
                                        </td>
                                    </tr>

                            </tbody>
                        </table>
                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                            class="bx bx-notepad p-1"></i>{{ localize('global.prescription_details') }}</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.type') }}</th>
                                    <th>{{ localize('global.name') }}</th>
                                    <th>{{ localize('global.usage_type') }}</th>
                                    <th>{{ localize('global.dosage') }}</th>
                                    <th>{{ localize('global.frequency') }}</th>
                                    <th>{{ localize('global.amount') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.alternatives') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($prescription->prescriptionItems as $item)
                                <!-- Original Prescription Item -->
                                <tr class="{{ $item->selectedAlternative ? 'table-secondary' : '' }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->medicineType->type }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-pill me-2 text-primary"></i>
                                            {{ $item->medicine->name }}
                                            @if($item->selectedAlternative)
                                                <span class="badge bg-warning ms-2">{{ localize('global.original') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $item->usageType->name }}</td>
                                    <td>{{ $item->dosage }}</td>
                                    <td>{{ $item->frequency }}</td>
                                    <td>{{ $item->amount }}</td>
                                    <td>
                                        @if($item->selectedAlternative)
                                            <span class="text-muted">
                                                <i class="bx bx-x-circle"></i> {{ localize('global.not_used') }}
                                            </span>
                                        @else
                                            <a href="{{ route('prescription_items.changeStatus', $item) }}" class="btn btn-sm btn-{{ $item->is_delivered == '0' ? 'danger' : 'success' }}">
                                                @if ($item->is_delivered == '1')
                                                    <span class="bx bx-check"></span>
                                                @else
                                                    <span class="bx bx-x"></span>
                                                @endif
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#alternativeModal{{ $item->id }}">
                                            <i class="bx bx-list-ul"></i> {{ localize('global.alternatives') }}
                                        </button>
                                    </td>
                                </tr>

                                <!-- Selected Alternative Item (if exists) -->
                                @if($item->selectedAlternative)
                                <tr class="table-success">
                                    <td>{{ $loop->iteration }}.1</td>
                                    <td>{{ $item->selectedAlternative->medicineType->type }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-check-circle me-2 text-success"></i>
                                            {{ $item->selectedAlternative->medicine->name }}
                                            <span class="badge bg-success ms-2">{{ localize('global.selected_alternative') }}</span>
                                        </div>
                                        @if($item->selectedAlternative->notes)
                                            <small class="text-muted d-block mt-1">
                                                <i class="bx bx-note me-1"></i>{{ $item->selectedAlternative->notes }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>{{ $item->selectedAlternative->usageType->name }}</td>
                                    <td>{{ $item->selectedAlternative->dosage }}</td>
                                    <td>{{ $item->selectedAlternative->frequency }}</td>
                                    <td>{{ $item->selectedAlternative->amount }}</td>
                                    <td>
                                        <a href="{{ route('prescription_alternative_items.changeStatus', $item->selectedAlternative) }}" class="btn btn-sm btn-{{ $item->selectedAlternative->is_delivered == '0' ? 'danger' : 'success' }}">
                                            @if ($item->selectedAlternative->is_delivered == '1')
                                                <span class="bx bx-check"></span>
                                            @else
                                                <span class="bx bx-x"></span>
                                            @endif
                                        </a>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('prescription_alternative_items.select', $item->selectedAlternative) }}" 
                                               class="btn btn-warning" 
                                               title="{{ localize('global.deselect_alternative') }}">
                                                <i class="bx bx-x"></i>
                                            </a>
                                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#alternativeModal{{ $item->id }}">
                                                <i class="bx bx-list-ul"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>

                        </form>
                        </button>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alternative Modals -->
    @foreach ($prescription->prescriptionItems as $item)
    <div class="modal fade" id="alternativeModal{{ $item->id }}" tabindex="-1" aria-labelledby="alternativeModalLabel{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alternativeModalLabel{{ $item->id }}">
                        {{ localize('global.alternatives_for') }}: {{ $item->medicine->name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add Alternative Form -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">{{ localize('global.add_alternative') }}</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('prescription_alternative_items.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="prescription_id" value="{{ $prescription->id }}">
                                <input type="hidden" name="prescription_item_id" value="{{ $item->id }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="medicine_id_{{ $item->id }}" class="form-label">{{ localize('global.medicine') }}</label>
                                            <select class="form-select" name="medicine_id" id="medicine_id_{{ $item->id }}" required>
                                                <option value="">{{ localize('global.select_medicine') }}</option>
                                                @foreach(\App\Models\Medicine::all() as $medicine)
                                                    <option value="{{ $medicine->id }}">{{ $medicine->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="medicine_type_id_{{ $item->id }}" class="form-label">{{ localize('global.medicine_type') }}</label>
                                            <select class="form-select" name="medicine_type_id" id="medicine_type_id_{{ $item->id }}" required>
                                                <option value="">{{ localize('global.select_type') }}</option>
                                                @foreach(\App\Models\MedicineType::all() as $type)
                                                    <option value="{{ $type->id }}">{{ $type->type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="usage_type_id_{{ $item->id }}" class="form-label">{{ localize('global.usage_type') }}</label>
                                            <select class="form-select" name="usage_type_id" id="usage_type_id_{{ $item->id }}" required>
                                                <option value="">{{ localize('global.select_usage_type') }}</option>
                                                @foreach(\App\Models\MedicineUsageType::all() as $usageType)
                                                    <option value="{{ $usageType->id }}">{{ $usageType->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="dosage_{{ $item->id }}" class="form-label">{{ localize('global.dosage') }}</label>
                                            <input type="text" class="form-control" name="dosage" id="dosage_{{ $item->id }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="frequency_{{ $item->id }}" class="form-label">{{ localize('global.frequency') }}</label>
                                            <input type="text" class="form-control" name="frequency" id="frequency_{{ $item->id }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="amount_{{ $item->id }}" class="form-label">{{ localize('global.amount') }}</label>
                                            <input type="text" class="form-control" name="amount" id="amount_{{ $item->id }}" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="notes_{{ $item->id }}" class="form-label">{{ localize('global.notes') }}</label>
                                            <textarea class="form-control" name="notes" id="notes_{{ $item->id }}" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-plus"></i> {{ localize('global.add_alternative') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Existing Alternatives -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">{{ localize('global.existing_alternatives') }}</h6>
                        </div>
                        <div class="card-body">
                            @if($item->alternativeItems->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>{{ localize('global.medicine') }}</th>
                                                <th>{{ localize('global.dosage') }}</th>
                                                <th>{{ localize('global.frequency') }}</th>
                                                <th>{{ localize('global.amount') }}</th>
                                                <th>{{ localize('global.status') }}</th>
                                                <th>{{ localize('global.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($item->alternativeItems as $alternative)
                                            <tr class="{{ $alternative->is_selected ? 'table-success' : '' }}">
                                                <td>
                                                    {{ $alternative->medicine->name }}
                                                    @if($alternative->is_selected)
                                                        <span class="badge bg-success ms-1">{{ localize('global.selected') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $alternative->dosage }}</td>
                                                <td>{{ $alternative->frequency }}</td>
                                                <td>{{ $alternative->amount }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $alternative->is_delivered ? 'success' : 'danger' }}">
                                                        {{ $alternative->is_delivered ? localize('global.delivered') : localize('global.not_delivered') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @if(!$alternative->is_selected)
                                                            <a href="{{ route('prescription_alternative_items.select', $alternative) }}" 
                                                               class="btn btn-success" 
                                                               title="{{ localize('global.select_alternative') }}">
                                                                <i class="bx bx-check"></i>
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('prescription_alternative_items.changeStatus', $alternative) }}" 
                                                           class="btn btn-{{ $alternative->is_delivered ? 'warning' : 'info' }}"
                                                           title="{{ $alternative->is_delivered ? localize('global.mark_not_delivered') : localize('global.mark_delivered') }}">
                                                            <i class="bx bx-{{ $alternative->is_delivered ? 'x' : 'check' }}"></i>
                                                        </a>
                                                        <form action="{{ route('prescription_alternative_items.destroy', $alternative) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger" 
                                                                    onclick="return confirm('{{ localize('global.are_you_sure_delete_alternative') }}')"
                                                                    title="{{ localize('global.delete_alternative') }}">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center">{{ localize('global.no_alternatives_found') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.close') }}</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endsection

