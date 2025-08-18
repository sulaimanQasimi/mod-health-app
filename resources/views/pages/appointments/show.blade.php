@extends('layouts.master')

@section('content')
<style>
    /* Enhanced Table Styling */
    .enhanced-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin: 20px 0;
        background: white;
    }

    .enhanced-table thead th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        padding: 16px 12px;
        text-align: left;
        border-bottom: 2px solid #dee2e6;
        font-size: 14px;
        text-transform: none;
        letter-spacing: normal;
    }

    .enhanced-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid #e9ecef;
    }

    .enhanced-table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .enhanced-table tbody tr:last-child {
        border-bottom: none;
    }

    .enhanced-table tbody td {
        padding: 16px 12px;
        vertical-align: middle;
        border: none;
        font-size: 14px;
        line-height: 1.5;
    }

    .enhanced-table tbody td:first-child {
        font-weight: 600;
        color: #495057;
    }

    /* Badge Styling */
    .enhanced-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
        margin: 2px;
    }

    .badge-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
    }

    .badge-success {
        background: linear-gradient(135deg, #28a745, #1e7e34);
        color: white;
    }

    .badge-warning {
        background: linear-gradient(135deg, #ffc107, #e0a800);
        color: #212529;
    }

    .badge-danger {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }

    /* Action Buttons */
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        margin: 0 4px;
        transition: all 0.3s ease;
        text-decoration: none;
        color: white;
    }

    .action-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .action-btn.edit {
        background: linear-gradient(135deg, #17a2b8, #138496);
    }

    .action-btn.delete {
        background: linear-gradient(135deg, #dc3545, #c82333);
    }

    .action-btn.view {
        background: linear-gradient(135deg, #6f42c1, #5a32a3);
    }

    /* Section Headers */
    .section-header {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-left: 4px solid #007bff;
        padding: 16px 20px;
        margin: 30px 0 20px 0;
        border-radius: 0 8px 8px 0;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        color: #495057;
        font-size: 18px;
    }

    .section-header i {
        font-size: 20px;
        color: #007bff;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        background: #f8f9fa;
        border-radius: 8px;
        margin: 20px 0;
        border: 2px dashed #dee2e6;
    }

    .empty-state .badge {
        font-size: 14px;
        padding: 8px 16px;
    }

    /* Table Container */
    .table-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin: 20px 0;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .enhanced-table {
            font-size: 12px;
        }
        
        .enhanced-table thead th,
        .enhanced-table tbody td {
            padding: 12px 8px;
        }
        
        .section-header {
            font-size: 16px;
            padding: 12px 16px;
        }
    }
</style>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="col-xl">
                <div class=" mb-4">
                    <div>

                        <div class="col-md-12">
                            <div class="border border-label-primary mb-4">
                                <h5 class="mb-4 p-3 bg-label-primary text-center">
                                    {{ localize('global.appointment_details') }}</h5>

                                <div class="row p-2 text-center">
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.patient_name') }}</h5>
                                        <div>
                                            {{ $appointment->patient->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.referred_to') }}</h5>
                                        <div>
                                            {{ $appointment->doctor->name }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.date') }}</h5>
                                        <div>
                                            {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($appointment->date) }}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5 class="mb-2">{{ localize('global.time') }}</h5>
                                        <div>
                                            {{ $appointment->time }}
                                        </div>
                                    </div>
                                </div>
                                <div class="row p-4">
                                    <div class="mb-4">
                                        <div class="col-md-12 d-flex justify-content-center">
                                            <h5 class="mb-4 p-1 bg-label-primary mt-4"><i
                                                    class="bx bx-history p-1"></i>{{ localize('global.patient_history') }}
                                            </h5>
                                        </div>
                                        @php
                                            $primaryDiagnoses = $previousDiagnoses->where('type', 0);
                                            $finalDiagnoses = $previousDiagnoses->where('type', 1);
                                        @endphp

                                        <div class="container">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="col-md-12">
                                                            <h5 class="mb-4 p-1 bg-label-warning text-center"><i
                                                                    class="bx bx-popsicle p-1"></i>{{ localize('global.primary_diagnoses') }}
                                                            </h5>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="col-md-12">
                                                            <h5 class="mb-4 p-1 bg-label-success text-center"><i
                                                                    class="bx bx-popsicle p-1"></i>{{ localize('global.final_diagnoses') }}
                                                            </h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        @foreach ($primaryDiagnoses as $diagnose)
                                                            <li class="m-1 p-1">
                                                                <span
                                                                    class="bg-label-warning text-center p-1">{{ $diagnose->created_at->format('Y-m-d') }}</span>
                                                                {{ $diagnose->description }}
                                                            </li>
                                                        @endforeach
                                                    </div>
                                                    <div class="col-md-6">
                                                        @foreach ($finalDiagnoses as $diagnose)
                                                            <li class="m-1 p-1">
                                                                <span
                                                                    class="bg-label-success text-center p-1">{{ $diagnose->created_at->format('Y-m-d') }}</span>
                                                                {{ $diagnose->description }}
                                                            </li>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @can('update-appointment-status')
                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-check-shield p-1"></i>{{ localize('global.appointment_status') }}

                        </h5>

                        @if ($appointment->is_completed == 0)
                            <div class="d-flex justify-content-center text-center">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#createStatusChangeModal{{ $appointment->id }}"><span><i
                                            class="bx bx-check-shield"></i></span></button>
                            </div>
                        @else
                            <div class="d-flex justify-content-center text-center">
                                <span><i
                                        class="bx bx-check-shield text-success"></i>{{ localize('global.appointment_completed') }}</span>
                            </div>
                        @endif

                        <div class="modal fade" id="createStatusChangeModal{{ $appointment->id }}" tabindex="-1"
                            aria-labelledby="createStatusChangeModalLabel{{ $appointment->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createStatusChangeModalLabel{{ $appointment->id }}">
                                            {{ localize('global.make_appointment_completed') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('appointments.changeStatus', $appointment) }}"
                                            method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="is_completed" value="1">

                                            <div class="form-group">

                                                <div class="form-group">
                                                    <label
                                                        for="status_remark{{ $appointment->id }}">{{ localize('global.status_remark') }}</label>
                                                    <textarea class="form-control" id="status_remark{{ $appointment->id }}" name="status_remark" rows="3"></textarea>
                                                </div>

                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endcan


                        <div class="section-header">
                            <i class="bx bx-popsicle"></i>
                            {{ localize('global.diagnose') }}
                            @if ($appointment->is_completed == 0)
                            @can('add-diagnose')
                                <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal"
                                    data-bs-target="#createDiagnoseModal{{ $appointment->id }}">
                                    <i class="bx bx-plus"></i>
                                </button>
                            @endcan
                            @endif
                        </div>
                        <!-- Create Diagnose Modal -->
                        <div class="modal fade" id="createDiagnoseModal{{ $appointment->id }}" tabindex="-1"
                            aria-labelledby="createDiagnoseModalLabel{{ $appointment->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createDiagnoseModalLabel{{ $appointment->id }}">
                                            {{ localize('global.add_diagnose') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('diagnoses.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                                name="patient_id" value="{{ $appointment->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                                name="appointment_id" value="{{ $appointment->id }}">
                                            <!-- Add other diagnosis form fields as needed -->
                                            <div class="form-group">
                                                <label
                                                    for="type{{ $appointment->id }}">{{ localize('global.diagnose_type') }}</label>
                                                <select class="form-control select2" name="type">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    <option value="0">{{ localize('global.primary') }}</option>
                                                    <option value="1">{{ localize('global.final') }}</option>

                                                </select>
                                                <label
                                                    for="description{{ $appointment->id }}">{{ localize('global.description_with_diaseases') }}</label>
                                                <textarea class="form-control" id="description{{ $appointment->id }}" name="description" rows="3"></textarea>
                                                <h5 class="mt-2">{{ localize('global.vital_signs') }}</h5>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label
                                                                for="bp{{ $appointment->id }}">{{ localize('global.bp') }}</label>
                                                            <input type="text" class="form-control" name="bp" />
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label
                                                                for="pr{{ $appointment->id }}">{{ localize('global.pr') }}</label>
                                                            <input type="text" class="form-control" name="pr" />
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label
                                                                for="weight{{ $appointment->id }}">{{ localize('global.weight') }}</label>
                                                            <input type="text" class="form-control" name="weight" />
                                                        </div>
                                                    </div>
                                                    <div class="row mt-1 mb-1">
                                                        <div class="col-md-4">
                                                            <label
                                                                for="t{{ $appointment->id }}">{{ localize('global.t') }}</label>
                                                            <input type="text" class="form-control" name="t" />
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label
                                                                for="spo2{{ $appointment->id }}">{{ localize('global.spo2') }}</label>
                                                            <input type="text" class="form-control" name="spo2" />
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label
                                                                for="pain{{ $appointment->id }}">{{ localize('global.pain') }}</label>
                                                            <input type="text" class="form-control" name="pain" />
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End Create Diagnose Modal -->
                        <div class="table-container">
                            @if($appointment->diagnose->count() > 0)
                                <table class="enhanced-table">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.number') }}</th>
                                            <th>{{ localize('global.description') }}</th>
                                            <th>{{ localize('global.type') }}</th>
                                            <th>{{ localize('global.created_at') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($appointment->diagnose as $diagnose)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $diagnose->description }}</td>
                                                <td>
                                                    @if ($diagnose->type == '0')
                                                        <span class="enhanced-badge badge-warning">{{ localize('global.primary') }}</span>
                                                    @else
                                                        <span class="enhanced-badge badge-primary">{{ localize('global.final') }}</span>
                                                    @endif
                                                </td>
                                                <td dir="ltr">{{ $diagnose->created_at }}</td>
                                                <td>
                                                    @can('edit-diagnoses')
                                                    <a href="{{ route('diagnoses.edit', $diagnose->id) }}" class="action-btn edit" title="Edit">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                    @endcan
                                                    @can('delete-diagnoses')
                                                    <a href="{{ route('diagnoses.destroy', $diagnose->id) }}" class="action-btn delete" title="Delete">
                                                        <i class="bx bx-trash"></i>
                                                    </a>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-state">
                                    <div class="badge bg-label-danger">
                                        {{ localize('global.no_previous_diagnoses') }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="section-header">
                            <i class="bx bx-notepad"></i>
                            {{ localize('global.prescription') }}
                            @if ($appointment->is_completed == 0)
                            @can('add-prescription')
                                <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal"
                                    data-bs-target="#createPrescriptionModal{{ $appointment->id }}">
                                    <i class="bx bx-plus"></i>
                                </button>
                            @endcan
                            @endif
                        </div>
                        <!-- Create Diagnose Modal -->
                        <div class="modal fade modal-xl" id="createPrescriptionModal{{ $appointment->id }}"
                            tabindex="-1" aria-labelledby="createPrescriptionModalLabel{{ $appointment->id }}"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createPrescriptionModalLabel{{ $appointment->id }}">
                                            {{ localize('global.add_prescription') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('prescriptions.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                                name="patient_id" value="{{ $appointment->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                                name="appointment_id" value="{{ $appointment->id }}">
                                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                                value="{{ auth()->user()->branch_id }}">
                                            <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                                value="{{ auth()->user()->id }}">

                                            <!-- Add other diagnosis form fields as needed -->
                                            <div class="form-group" id="prescription-items">

                                                <div id="prescription-input-container">
                                                    <div class="row">
                                                        <div class="col-md-2">
                                                            <label class="form-group mb-2">{{ localize('global.medicine_type') }}</label>
                                                            <select class="form-control select2"
                                                                name="medicine_type_id[]" required>
                                                                <option value="">{{ localize('global.select') }}
                                                                </option>
                                                                @foreach ($medicineTypes as $value)
                                                                    <option value="{{ $value->id }}"
                                                                        {{ old('type') == $value->id ? 'selected' : '' }}>
                                                                        {{ $value->type }}

                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-group mb-2">{{ localize('global.medicine_name') }}</label>
                                                            <select class="form-control select2" name="medicine_id[]" required>
                                                                <option value="">{{ localize('global.select') }}
                                                                </option>
                                                                @foreach ($medicines as $value)
                                                                    <option value="{{ $value->id }}"
                                                                        {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                        {{ $value->name }}

                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-group mb-2">{{ localize('global.usage_type') }}</label>
                                                            <select class="form-control select2 mt-2" name="usage_type_id[]" required>
                                                                <option value="">{{ localize('global.select') }}
                                                                </option>
                                                                @foreach ($medicineUsageTypes as $value)
                                                                    <option value="{{ $value->id }}"
                                                                        {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                        {{ $value->name }}

                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-group">{{ localize('global.dosage') }}</label>
                                                            <input type="text" class="form-control mt-2"
                                                                name="dosage[]" placeholder="Dosage" required>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-group">{{ localize('global.frequency') }}</label>
                                                            <input type="text" class="form-control mt-2"
                                                                name="frequency[]" placeholder="Frequency" required>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-group">{{ localize('global.amount') }}</label>
                                                            <input type="text" class="form-control mt-2"
                                                                name="amount[]" placeholder="Amount" required>
                                                        </div>
                                                        <div class="col-md-2">                                                            <input type="hidden" class="form-control mt-2"
                                                                name="is_delivered[]" value="0">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="button" class="btn btn-primary mt-2" id="addPrescriptionInput"
                                                onclick="addRow()">
                                                <i class="bx bx-plus"></i>{{ localize('global.add_prescription_item') }}
                                            </button>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="table-container">
                            @if($appointment->prescription->count() > 0)
                                <table class="enhanced-table">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.number') }}</th>
                                            <th>{{ localize('global.patient_name') }}</th>
                                            <th>{{ localize('global.status') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($appointment->prescription as $prescription)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $prescription->patient->name }}</td>
                                                <td>
                                                    @if ($prescription->is_completed == '0')
                                                        <span class="enhanced-badge badge-danger">{{ localize('global.not_delivered') }}</span>
                                                    @else
                                                        <span class="enhanced-badge badge-success">{{ localize('global.delivered') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="#" data-bs-toggle="modal"
                                                        onclick="getPrescriptionItems({{ $prescription->id }})"
                                                        data-bs-target="#showPrescriptionItemModal" class="action-btn view" title="View Details">
                                                        <i class="bx bx-expand"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-state">
                                    <div class="badge bg-label-danger">
                                        {{ localize('global.no_previous_prescriptions') }}
                                    </div>
                                </div>
                            @endif
                            <div class="modal fade modal-xl" id="showPrescriptionItemModal" tabindex="-1"
                                aria-labelledby="showPrescriptionItemModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content" id="prescription_items_table">



                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="modal fade modal-xl" id="showPrescriptionModal{{ $appointment->id }}"
                            tabindex="-1" aria-labelledby="showPrescriptionModalLabel{{ $appointment->id }}"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="showPrescriptionModalLabel{{ $appointment->id }}">
                                            {{ localize('global.show_prescription_details') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>{{ localize('global.number') }}</th>
                                                    <th>{{ localize('global.date') }}</th>
                                                    {{-- <th>{{ localize('global.description') }}</th>
                                                    <th>{{ localize('global.dosage') }}</th>
                                                    <th>{{ localize('global.frequency') }}</th>
                                                    <th>{{ localize('global.amount') }}</th> --}}
                                                    <th>{{ localize('global.status') }}</th>
                                                    <th>{{ localize('global.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($appointment->prescription)
                                                    @foreach ($appointment->prescription as $pres_list)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $pres_list->created_at }}</td>
                                                            <td>{{ $pres_list->is_completed }}</td>
                                                            <td>
                                                                <a href="#" data-bs-toggle="modal"
                                                                    onclick="getPrescriptionItems({{ $pres_list->id }})"
                                                                    data-bs-target="#showPrescriptionItemModal"><span><i
                                                                            class="bx bx-expand"></i></span></a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="5">
                                                            <div class="container">
                                                                <div
                                                                    class="col-md-12 d-flex justify-content-center align-items-center">
                                                                    <div class="badge bg-label-danger mt-4">
                                                                        {{ localize('global.no_previous_prescriptions') }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="section-header">
                            <i class="bx bx-command"></i>
                            {{ localize('global.advice') }}
                            @if ($appointment->is_completed == 0)
                            @can('add-advice')
                                <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal"
                                    data-bs-target="#createAdviceModal{{ $appointment->id }}">
                                    <i class="bx bx-plus"></i>
                                </button>
                            @endcan
                            @endif
                        </div>
                    <!-- Create Diagnose Modal -->
                    <div class="modal fade" id="createAdviceModal{{ $appointment->id }}" tabindex="-1"
                        aria-labelledby="createAdviceModalLabel{{ $appointment->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="createAdviceModalLabel{{ $appointment->id }}">
                                        {{ localize('global.add_advice') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('advices.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                            name="patient_id" value="{{ $appointment->patient_id }}">
                                        <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                            name="appointment_id" value="{{ $appointment->id }}">
                                            <input type="hidden" id="doctor_id{{ $appointment->id }}"
                                            name="doctor_id" value="{{ auth()->user()->id }}">

                                        <!-- Add other diagnosis form fields as needed -->
                                        <div class="form-group">

                                            <label
                                                for="description{{ $appointment->id }}">{{ localize('global.description') }}</label>
                                            <textarea class="form-control" id="description{{ $appointment->id }}" name="description" rows="3"></textarea>

                                        </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                    <button type="submit"
                                        class="btn btn-primary">{{ localize('global.save') }}</button>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- End Create Diagnose Modal -->
                    <div class="table-container">
                        @if($appointment->advices->count() > 0)
                            <table class="enhanced-table">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.description') }}</th>
                                        <th>{{ localize('global.by') }}</th>
                                        <th>{{ localize('global.created_at') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($appointment->advices as $advice)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $advice->description }}</td>
                                            <td>
                                                {{$advice->doctor->name}}
                                            </td>
                                            <td dir="ltr">{{ $advice->created_at }}</td>
                                            <td>
                                                @can('edit-advices')
                                                <a href="{{ route('advices.edit', $advice->id) }}" class="action-btn edit" title="Edit">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                @endcan
                                                @can('delete-advices')
                                                <a href="{{ route('advices.destroy', $advice->id) }}" class="action-btn delete" title="Delete">
                                                    <i class="bx bx-trash"></i>
                                                </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="empty-state">
                                <div class="badge bg-label-danger">
                                    {{ localize('global.no_previous_advices') }}
                                </div>
                            </div>
                        @endif
                    </div>

                        <div class="section-header">
                            <i class="bx bx-hard-hat"></i>
                            {{ localize('global.checkups') }}
                            @if ($appointment->is_completed == 0)
                            @can('add-patient-labs')
                                <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal"
                                    data-bs-target="#createLabModal{{ $appointment->id }}">
                                    <i class="bx bx-plus"></i>
                                </button>
                            @endcan
                            @endif
                        </div>
                        <!-- Create  Lab Modal -->
                        <div class="modal fade" id="createLabModal{{ $appointment->id }}" tabindex="-1"
                            aria-labelledby="createLabModalLabel{{ $appointment->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createLabModalLabel{{ $appointment->id }}">
                                            {{ localize('global.add_lab_test') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('lab_tests.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                                name="patient_id" value="{{ $appointment->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                                name="appointment_id" value="{{ $appointment->id }}">
                                            <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                                value="{{ $appointment->doctor->id }}">
                                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                                value="{{ auth()->user()->branch_id }}">
                                            <input type="hidden" id="hospitalization_id{{ $appointment->id }}"
                                                name="hospitalization_id" value="">
                                            <input type="hidden" id="status{{ $appointment->id }}" name="status"
                                                value="0">

                                            <div class="form-group">

                                                <label
                                                    for="lab_type_section{{ $appointment->id }}">{{ localize('global.lab_type_section') }}</label>
                                                <select class="form-control select2" name="lab_type_section"
                                                    id="lab_type_section">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($labTypeSections as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->section }}

                                                        </option>
                                                    @endforeach
                                                </select>

                                                <label
                                                    for="lab_type_id{{ $appointment->id }}">{{ localize('global.lab_type') }}</label>
                                                <select class="form-control select2" name="lab_type_id[]"
                                                    id="lab_type_id" onchange="loadLabTypeTests()">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($labTypes as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->name }}

                                                        </option>
                                                    @endforeach
                                                </select>

                                                <div id="labTypeTestsContainer"></div>
                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End Create Lab Modal -->
                        <div class="table-container">
                            @if($appointment->labs->count() > 0)
                                <table class="enhanced-table">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.number') }}</th>
                                            <th>{{ localize('global.test_name') }}</th>
                                            <th>{{ localize('global.test_status') }}</th>
                                            <th>{{ localize('global.result') }}</th>
                                            <th>{{ localize('global.result_file') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($appointment->labs as $lab)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $lab->labType->name }}</td>
                                                <td>
                                                    @if ($lab->status == '0')
                                                        <span class="enhanced-badge badge-danger">{{ localize('global.not_tested') }}</span>
                                                    @else
                                                        <span class="enhanced-badge badge-success">{{ localize('global.tested') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $lab->result }}</td>
                                                <td>
                                                    @isset($lab->result_file)
                                                        <a href="{{ asset('storage/' . $lab->result_file) }}" target="_blank" class="action-btn view" title="View File">
                                                            <i class="fa fa-file"></i>
                                                        </a>
                                                    @endisset
                                                </td>
                                                <td>
                                                    <a href="#" data-bs-toggle="modal"
                                                        onclick="getLabItems({{ $lab->id }})"
                                                        data-bs-target="#showLabsItemModal" class="action-btn view" title="View Details">
                                                        <i class="bx bx-expand"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-state">
                                    <div class="badge bg-label-danger">
                                        {{ localize('global.no_previous_labs') }}
                                    </div>
                                </div>
                            @endif


                            <div class="modal fade modal-xl" id="showLabsItemModal" tabindex="-1"
                                aria-labelledby="showLabsItemModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content" id="lab_items_table">



                                    </div>
                                </div>
                            </div>


                            <div class="col-md-12 d-flex justify-content-center">
                                <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                        class="bx bx-hard-hat p-1"></i>{{ localize('global.hospitalization_checkups') }}
                                </h5>
                            </div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.test_name') }}</th>
                                        <th>{{ localize('global.test_status') }}</th>
                                        <th>{{ localize('global.result') }}</th>
                                        <th>{{ localize('global.result_file') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($appointment->hospitalization as $single_hospitalization)
                                        @foreach ($single_hospitalization->labs as $lab)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $lab->labType->name }}</td>
                                                <td>
                                                    @if ($lab->status == '0')
                                                        <span
                                                            class="badge bg-danger">{{ localize('global.not_tested') }}</span>
                                                    @else
                                                        <span
                                                            class="badge bg-success">{{ localize('global.tested') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $lab->result }}</td>
                                                <td>
                                                    @isset($lab->result_file)
                                                        <a href="{{ asset('storage/' . $lab->result_file) }}"
                                                            target="_blank">
                                                            <i class="fa fa-file"></i> {{ localize('global.file') }}
                                                        </a>
                                                    @endisset

                                                </td>
                                                <td>
                                                    {{-- <a href="{{ route('lab_tests.edit', $lab->id) }}"><span><i
                                                            class="bx bx-edit"></i></span></a>
                                                <a href="{{ route('lab_tests.destroy', $lab->id) }}"><span><i
                                                            class="bx bx-trash text-danger"></i></span></a> --}}

                                                </td>

                                            </tr>
                                        @endforeach
                                    @empty
                                        <div class="container">
                                            <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                <div class=" badge bg-label-danger mt-4">
                                                    {{ localize('global.no_previous_labs') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse

                                </tbody>
                            </table>

                        </div>




                        <div class="section-header">
                            <i class="bx bx-chat"></i>
                            {{ localize('global.consultations') }}
                            @if ($appointment->is_completed == 0)
                            @can('add-consultations')
                                <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal"
                                    data-bs-target="#createConsultationModal{{ $appointment->id }}">
                                    <i class="bx bx-plus"></i>
                                </button>
                            @endcan
                            @endif
                        </div>
                        <!-- Create  Lab Modal -->
                        <div class="modal fade" id="createConsultationModal{{ $appointment->id }}" tabindex="-1"
                            aria-labelledby="createConsultationModalLabel{{ $appointment->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createConsultationModalLabel{{ $appointment->id }}">
                                            {{ localize('global.add_consultation') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('consultations.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                                name="patient_id" value="{{ $appointment->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                                name="appointment_id" value="{{ $appointment->id }}">
                                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                                value="{{ auth()->user()->branch_id }}">

                                            <div class="form-group">

                                                <label
                                                    for="description{{ $appointment->id }}">{{ localize('global.description') }}</label>
                                                <input type="text" class="form-control" name="title">

                                                <label
                                                    for="branch{{ $appointment->id }}">{{ localize('global.branch') }}</label>
                                                <select class="form-control select2" name="branch" id="branch">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($branches as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->name }}

                                                        </option>
                                                    @endforeach
                                                </select>

                                                <label
                                                    for="department{{ $appointment->id }}">{{ localize('global.department') }}</label>
                                                <select class="form-control select2" name="department_id[]" id="department" multiple>
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($departments as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->name }}

                                                        </option>
                                                    @endforeach
                                                </select>

                                                <label
                                                    for="type{{ $appointment->id }}">{{ localize('global.type') }}</label>
                                                <select class="form-control select2" name="consultation_type" id="type">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    <option value="0">{{ localize('global.normal') }}</option>
                                                    <option value="1">{{ localize('global.emergency') }}</option>

                                                </select>

                                                {{-- <label
                                                        for="doctor_id{{ $appointment->id }}">{{ localize('global.doctors') }}</label>
                                                    <select class="form-control select2" name="doctor_id[]"
                                                        id="doctor_id" multiple>
                                                        <option value="">{{ localize('global.select') }}</option>
                                                        @foreach ($doctors as $value)
                                                            <option value="{{ $value->id }}"
                                                                {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                {{ $value->name_en }}

                                                            </option>
                                                        @endforeach
                                                    </select> --}}

                                                <div class="mb-3">
                                                    <label for="date">{{ localize('global.date') }}</label>
                                                    <input type="date" class="form-control" name="date" />
                                                </div>
                                                <div class="mb-3">
                                                    <label for="time">{{ localize('global.time') }}</label>
                                                    <input type="time" class="form-control" name="time" />
                                                </div>

                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End Create Lab Modal -->
                        <div class="table-container">
                            @if($appointment->consultations->count() > 0)
                                <table class="enhanced-table">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.number') }}</th>
                                            <th>{{ localize('global.title') }}</th>
                                            <th>{{ localize('global.department') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($appointment->consultations as $consultation)
                                            <tr>
                                                <td>
                                                    <div class="d-flex justify-content-center">
                                                        <span class="enhanced-badge badge-primary" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                                            {{ $loop->iteration }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>{{ $consultation->title }}</td>
                                                <td>
                                                    @foreach ($consultation->associated_departments as $department)
                                                        <span class="enhanced-badge badge-primary">
                                                            {{ $department->name }}
                                                        </span>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @can('edit-consultations')
                                                    <a href="{{ route('consultations.edit', $consultation->id) }}" class="action-btn edit" title="Edit">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                    @endcan
                                                    @can('delete-consultations')
                                                    <a href="{{ route('consultations.destroy', $consultation->id) }}" class="action-btn delete" title="Delete">
                                                        <i class="bx bx-trash"></i>
                                                    </a>
                                                    @endcan
                                                </td>
                                            </tr>
                                            @if ($consultation->comments->isNotEmpty())
                                                <tr>
                                                    <td colspan="4">
                                                        <div class="section-header" style="margin: 15px 0; padding: 10px 15px; font-size: 14px;">
                                                            <i class="bx bx-chat"></i>
                                                            {{ localize('global.related_comments') }}
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">
                                                        @foreach ($consultation->comments as $comment)
                                                            <div class="row mb-3 p-3" style="background: #f8f9fa; border-radius: 8px; margin: 10px 0;">
                                                                <div class="col-md-3">
                                                                    <span class="enhanced-badge badge-primary">{{ $comment->department->name }}</span>
                                                                </div>
                                                                <div class="col-md-1 d-flex align-items-center justify-content-center">
                                                                    <i class="bx bx-transfer text-success"></i>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <span class="enhanced-badge badge-primary">{{ $comment->doctor->name }}</span>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="p-3" style="text-align: justify; background: white; border-radius: 6px; border-left: 3px solid #007bff;">
                                                                        {{ $comment->comment }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-state">
                                    <div class="badge bg-label-danger">
                                        {{ localize('global.no_previous_consultations') }}
                                    </div>
                                </div>
                            @endif
                        </div>



                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-transfer p-1"></i>{{ localize('global.refer_to_another_doctor') }}</h5>
                        @if ($appointment->is_completed == 0)
                        @can('refer-to-another-doctor')
                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#createReferDoctorModal{{ $appointment->id }}"><span><i
                                        class="bx bx-plus"></i></span></button>
                        @endcan
                        @endif
                        <!-- Create  Lab Modal -->
                        <div class="modal fade" id="createReferDoctorModal{{ $appointment->id }}" tabindex="-1"
                            aria-labelledby="createReferDoctorModalLabel{{ $appointment->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createReferDoctorModalLabel{{ $appointment->id }}">
                                            {{ localize('global.refere_patient_to_another_doctor') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('appointments.store') }}" method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="branch">{{ localize('global.branch') }}</label>
                                                <select class="form-control select2" name="branch_id"
                                                    id="referral_branch">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($branches as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->name }}</option>
                                                    @endforeach
                                                </select>
                                                <label for="department">{{ localize('global.department') }}</label>
                                                <select class="form-control select2" name="department_id"
                                                    id="referral_department_id">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($departments as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->name }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="patient_id"
                                                    value="{{ $appointment->patient->id }}">
                                                <input type="hidden" name="is_completed" value="0">
                                                <input type="hidden" name="branch_id"
                                                    value="{{ auth()->user()->branch_id }}">
                                                <!-- Add other appointment form fields as needed -->
                                                <label for="doctor_name">{{ localize('global.doctor_name') }}</label>
                                                <select class="form-control select2" name="doctor_id"
                                                    id="appointment_doctor_id">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($doctors as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->name_dr }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="date">{{ localize('global.date') }}</label>
                                                <x-tools.dariDatePicker name="date" dir="ltr"
                                                withID="date" withPlaceHolder="{{ localize('global.date') }}"
                                                withSize="3" extraClasses="" />
                                            </div>
                                            <div class="mb-3">
                                                <label for="time">{{ localize('global.time') }}</label>
                                                <input type="time" class="form-control" name="time" />
                                            </div>

                                            <div class="form-group">
                                                <label
                                                    for="refferal_remarks{{ $appointment->id }}">{{ localize('global.refferal_remarks') }}</label>
                                                <textarea class="form-control" id="refferal_remarks{{ $appointment->id }}" name="refferal_remarks" rows="3"></textarea>
                                            </div>

                                            <input type="hidden" name="current_appointment_id"
                                                value="{{ $appointment->id }}">

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                                <button type="submit"
                                                    class="btn btn-primary">{{ localize('global.save') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="col-md-12">
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        @if ($appointment->is_completed == 1)
                                            <i class="bx bx-check-circle text-success"></i>
                                            <span
                                                class="bg-label-primary p-1 m-1">{{ $appointment->refferal_remarks }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-header">
                            <i class="bx bx-revision"></i>
                            {{ localize('global.under_review') }}
                            @if ($appointment->is_completed == 0)
                            @can('patient-under-review')
                                <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal"
                                    data-bs-target="#createUnderReviewModal{{ $appointment->id }}">
                                    <i class="bx bx-plus"></i>
                                </button>
                            @endcan
                            @endif
                        </div>
                        <!-- Create  Lab Modal -->
                        <div class="modal fade" id="createUnderReviewModal{{ $appointment->id }}" tabindex="-1"
                            aria-labelledby="createUnderReviewModalLabel{{ $appointment->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createUnderReviewModalLabel{{ $appointment->id }}">
                                            {{ localize('global.refere_to_under_review') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('under_reviews.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                                name="patient_id" value="{{ $appointment->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                                name="appointment_id" value="{{ $appointment->id }}">
                                            <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                                value="{{ auth()->user()->id }}">
                                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                                value="{{ auth()->user()->branch_id }}">
                                            <input type="hidden" id="is_discharged{{ $appointment->id }}"
                                                name="is_discharged" value="0">

                                            <div class="form-group">

                                                <div class="form-group">
                                                    <label
                                                        for="reason{{ $appointment->id }}">{{ localize('global.reason') }}</label>
                                                    <textarea class="form-control" id="reason{{ $appointment->id }}" name="reason" rows="3"></textarea>
                                                </div>

                                                <div class="form-group">
                                                    <label
                                                        for="remarks{{ $appointment->id }}">{{ localize('global.remarks') }}</label>
                                                    <textarea class="form-control" id="remarks{{ $appointment->id }}" name="remarks" rows="3"></textarea>
                                                </div>


                                                <label
                                                    for="room_id{{ $appointment->id }}">{{ localize('global.rooms') }}</label>
                                                <select class="form-control select2" name="room_id"
                                                    id="under_review_room">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($rooms as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->name }}

                                                        </option>
                                                    @endforeach
                                                </select>

                                                <label
                                                    for="bed_id{{ $appointment->id }}">{{ localize('global.beds') }}</label>
                                                <select class="form-control select2" name="bed_id"
                                                    id="under_review_bed_id">
                                                    <option value="">{{ localize('global.select') }}</option>
                                                    @foreach ($beds as $value)
                                                        <option value="{{ $value->id }}"
                                                            {{ old('number') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->number }}

                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                                <button type="submit"
                                                    class="btn btn-primary">{{ localize('global.save') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-container">
                            @if($appointment->under_reviews->count() > 0)
                                <table class="enhanced-table">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.number') }}</th>
                                            <th>{{ localize('global.reason') }}</th>
                                            <th>{{ localize('global.remarks') }}</th>
                                            <th>{{ localize('global.room') }}</th>
                                            <th>{{ localize('global.bed') }}</th>
                                            <th>{{ localize('global.status') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($appointment->under_reviews as $underReview)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $underReview->reason }}</td>
                                                <td>{{ $underReview->remarks }}</td>
                                                <td>{{ $underReview->room->name }}</td>
                                                <td>{{ $underReview->bed->number }}</td>
                                                <td>
                                                    @if ($underReview->is_discharged == '0')
                                                        <span class="enhanced-badge badge-danger">
                                                            <i class="bx bx-x-circle"></i> {{ localize('global.under_review') }}
                                                        </span>
                                                    @else
                                                        <span class="enhanced-badge badge-success">
                                                            <i class="bx bx-check-circle"></i> {{ localize('global.discharged') }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @can('edit-under-reviews')
                                                    <a href="{{ route('under_reviews.edit', $underReview->id) }}" class="action-btn edit" title="Edit">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                    @endcan
                                                    @can('delete-under-reviews')
                                                    <a href="{{ route('under_reviews.destroy', $underReview->id) }}" class="action-btn delete" title="Delete">
                                                        <i class="bx bx-trash"></i>
                                                    </a>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-state">
                                    <div class="badge bg-label-danger">
                                        {{ localize('global.no_previous_under_reviews') }}
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-12 d-flex justify-content-center">
                                <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                        class="bx bx-glasses p-1"></i>{{ localize('global.related_visits') }}</h5>
                            </div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.description') }}</th>
                                        <th>{{ localize('global.by') }}</th>
                                        <th>{{ localize('global.visit_date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($appointment->under_reviews as $single_hospitaliztion)
                                        @foreach ($single_hospitaliztion->visits as $visit)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $visit->description }}</td>
                                                <td>{{ $visit->doctor->name }}</td>
                                                <td>
                                                    {{ $visit->created_at }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <div class="container">
                                            <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                <div class=" badge bg-label-danger mt-4">
                                                    {{ localize('global.no_previous_visits') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="section-header">
                            <i class="bx bx-bed"></i>
                            {{ localize('global.hospitalize') }}
                            @if ($appointment->is_completed == 0)
                            @can('patient-hospitalization')
                                <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal"
                                    data-bs-target="#createHospitalizationModal{{ $appointment->id }}">
                                    <i class="bx bx-plus"></i>
                                </button>
                            @endcan
                            @endif
                        </div>
                        <!-- Create  Lab Modal -->
                        <div class="modal fade modal-xl" id="createHospitalizationModal{{ $appointment->id }}"
                            tabindex="-1" aria-labelledby="createHospitalizationModalLabel{{ $appointment->id }}"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"
                                            id="createHospitalizationModalLabel{{ $appointment->id }}">
                                            {{ localize('global.hospitalize_patient') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('hospitalizations.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                                name="patient_id" value="{{ $appointment->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                                name="appointment_id" value="{{ $appointment->id }}">
                                            <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                                value="{{ auth()->user()->id }}">
                                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                                value="{{ auth()->user()->branch_id }}">
                                            <input type="hidden" id="is_discharged{{ $appointment->id }}"
                                                name="is_discharged" value="0">

                                            <div class="form-group">

                                                <div class="form-group">
                                                    <label
                                                        for="reason{{ $appointment->id }}">{{ localize('global.reason') }}</label>
                                                    <textarea class="form-control" id="reason{{ $appointment->id }}" name="reason" rows="3"></textarea>
                                                </div>

                                                <div class="form-group">
                                                    <label
                                                        for="remarks{{ $appointment->id }}">{{ localize('global.remarks') }}</label>
                                                    <textarea class="form-control" id="remarks{{ $appointment->id }}" name="remarks" rows="3"></textarea>
                                                </div>

                                                <div class="form-group">
                                                    <div class="row p-2">
                                                        <div class="col-md-4">
                                                            <label
                                                                for="room_id{{ $appointment->id }}">{{ localize('global.rooms') }}</label>
                                                            <select class="form-control select2" name="room_id"
                                                                id="room_id">
                                                                <option value="">{{ localize('global.select') }}
                                                                </option>
                                                                @foreach ($rooms as $value)
                                                                    <option value="{{ $value->id }}"
                                                                        {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                        {{ $value->name }}

                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label
                                                                for="bed_id{{ $appointment->id }}">{{ localize('global.beds') }}</label>
                                                            <select class="form-control select2" name="bed_id"
                                                                id="bed_id">
                                                                <option value="">{{ localize('global.select') }}
                                                                </option>
                                                                @foreach ($beds as $value)
                                                                    <option value="{{ $value->id }}"
                                                                        {{ old('number') == $value->id ? 'selected' : '' }}>
                                                                        {{ $value->number }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label
                                                                for="food_type_id{{ $appointment->id }}">{{ localize('global.food_type') }}</label>
                                                            <select class="form-control select2" name="food_type_id[]"
                                                                id="food_type_id" multiple>
                                                                <option value="">{{ localize('global.select') }}
                                                                </option>
                                                                @foreach ($foodTypes as $value)
                                                                    <option value="{{ $value->id }}"
                                                                        {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                        {{ $value->name }}

                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                                            class="bx bx-info-circle p-1"></i>{{ localize('global.patient_companion_info') }}
                                                    </h5>
                                                    <div class="form-group">
                                                        <div class="row p-2">
                                                            <div class="col-md-3">
                                                                <label>{{ localize('global.companion_name') }}</label>
                                                                <input type="text" class="form-control"
                                                                    name="patinet_companion">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>{{ localize('global.companion_father_name') }}</label>
                                                                <input type="text" class="form-control"
                                                                    name="companion_father_name">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>{{ localize('global.relation_to_patient') }}</label>
                                                                <select class="form-control select2"
                                                                    name="relation_to_patient">
                                                                    <option value="">
                                                                        {{ localize('global.select') }}</option>
                                                                    @foreach ($relations as $value)
                                                                        <option value="{{ $value->id }}"
                                                                            {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                            {{ $value->name }}

                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>{{ localize('global.companion_card_type') }}</label>
                                                                <select class="form-control select2"
                                                                    name="companion_card_type">
                                                                    <option value="">
                                                                        {{ localize('global.select') }}</option>
                                                                    <option value="12">
                                                                        {{ localize('global.12_hours') }}</option>
                                                                    <option value="24">
                                                                        {{ localize('global.24_hours') }}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>





                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End Create Lab Modal -->
                        <div class="table-container">
                            @if($appointment->hospitalization->count() > 0)
                                <table class="enhanced-table">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.number') }}</th>
                                            <th class="text-wrap">{{ localize('global.reason') }}</th>
                                            <th>{{ localize('global.remarks') }}</th>
                                            <th>{{ localize('global.room') }}</th>
                                            <th>{{ localize('global.bed') }}</th>
                                            <th>{{ localize('global.status') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($appointment->hospitalization as $hospitalization)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $hospitalization->reason }}</td>
                                                <td>{{ $hospitalization->remarks }}</td>
                                                <td>{{ $hospitalization->room->name }}</td>
                                                <td>{{ $hospitalization->bed->number }}</td>
                                                <td>
                                                    @if ($hospitalization->is_discharged == 0)
                                                        <span class="enhanced-badge badge-danger">{{ localize('global.in_bed') }}</span>
                                                    @else
                                                        <span class="enhanced-badge badge-success">{{ localize('global.discharged') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @can('edit-hospitalizations')
                                                    <a href="{{ route('hospitalizations.edit', $hospitalization->id) }}" class="action-btn edit" title="Edit">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                    @endcan
                                                    @can('delete-hospitalizations')
                                                    <a href="{{ route('hospitalizations.destroy', $hospitalization) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$hospitalization->id}}').submit(); }" class="action-btn delete" title="Delete">
                                                        <i class="bx bx-trash"></i>
                                                    </a>
                                                    @endcan
                                                    <!-- Using a <form> element -->
                                                    <form id="delete-form-{{$hospitalization->id}}" action="{{ route('hospitalizations.destroy', $hospitalization) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-state">
                                    <div class="badge bg-label-danger">
                                        {{ localize('global.no_previous_hospitalizations') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-12 d-flex justify-content-center">
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-glasses p-1"></i>{{ localize('global.related_visits') }}</h5>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.description') }}</th>
                                        <th>{{ localize('global.by') }}</th>
                                        <th>{{ localize('global.visit_date') }}</th>
                                        <th>{{ localize('global.vital_signs') }}</th>
                                        <th>{{ localize('global.antibiotic') }}</th>
                                        <th>{{ localize('global.food_type') }}</th>
                                        <th>{{ localize('global.intake') }}</th>
                                        <th>{{ localize('global.output') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($appointment->hospitalization as $single_hospitaliztion)
                                    @foreach ($single_hospitaliztion->visits as $visit)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $visit->description }}</td>
                                            <td>{{ $visit->doctor->name }}</td>
                                            <td>{{ $visit->created_at }}</td>
                                            <td dir="ltr">
                                                <span class="badge bg-primary">{{ localize('global.bp') }}</span>
                                                {{ $visit->bp }}
                                                <br>
                                                <span class="badge bg-primary">{{ localize('global.pr') }}</span>
                                                {{ $visit->pr }}
                                                <br>
                                                <span class="badge bg-primary">{{ localize('global.rr') }}</span>
                                                {{ $visit->rr }}
                                                <br>
                                                <span class="badge bg-primary">{{ localize('global.t') }}</span>
                                                {{ $visit->t }}
                                                <br>
                                                <span class="badge bg-primary">{{ localize('global.spo2') }}</span>
                                                {{ $visit->spo2 }}
                                                <br>
                                                <span class="badge bg-primary">{{ localize('global.pain') }}</span>
                                                {{ $visit->pain }}

                                            </td>
                                            <td>{{$visit->antibiotic}}</td>
                                            <td>
                                                @foreach ($visit->getAssociatedFoodTypesAttribute() as $foodType)
                                                    <span class="badge bg-primary">{{ $foodType->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>{{$visit->intake}}</td>
                                            <td>{{$visit->output}}</td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <div class="container">
                                        <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                            <div class=" badge bg-label-danger mt-4">
                                                {{ localize('global.no_previous_visits') }}
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </tbody>
                        </table>


                        {{-- To anasthesia --}}

                        <div class="section-header">
                            <i class="bx bx-first-aid"></i>
                            {{ localize('global.refere_to_anasthesia') }}
                            @if ($appointment->is_completed == 0)
                            @can('refer-to-anesthesia')
                                <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal"
                                    data-bs-target="#createAnasthesiaModal{{ $appointment->id }}">
                                    <i class="bx bx-plus"></i>
                                </button>
                            @endcan
                            @endif
                        </div>
                        <!-- Create  Lab Modal -->
                        <div class="modal fade modal-xl" id="createAnasthesiaModal{{ $appointment->id }}" tabindex="-1"
                            aria-labelledby="createAnasthesiaModalLabel{{ $appointment->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createAnasthesiaModalLabel{{ $appointment->id }}">
                                            {{ localize('global.refere_to_anasthesia') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('anesthesias.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                                name="patient_id" value="{{ $appointment->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                                name="appointment_id" value="{{ $appointment->id }}">
                                            <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                                value="{{ auth()->user()->id }}">
                                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                                value="{{ auth()->user()->branch_id }}">

                                            <div class="form-group">

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label
                                                        for="plan{{ $appointment->id }}">{{ localize('global.plan') }}</label>
                                                    <textarea class="form-control" id="plan{{ $appointment->id }}" name="plan" rows="3"></textarea>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="other_problems{{ $appointment->id }}">{{ localize('global.other_problems') }}</label>
                                                        <textarea class="form-control" id="other_problems{{ $appointment->id }}" name="other_problems" rows="3"></textarea>
                                                    </div>
                                                </div>
                                                <h5 class="mt-2">{{ localize('global.operation_team') }}</h5>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label
                                                                for="operation_surgion_id{{ $appointment->id }}">{{ localize('global.operation_surgion') }}</label>
                                                            <select class="form-control select2"
                                                                name="operation_surgion_id" id="operation_surgion_id">
                                                                <option value="">{{ localize('global.select') }}
                                                                </option>
                                                                @foreach ($operation_doctors as $value)
                                                                    <option value="{{ $value->id }}"
                                                                        {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                        {{ $value->name }}

                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <label
                                                                for="operation_assistants_id{{ $appointment->id }}">{{ localize('global.operation_assistants') }}</label>
                                                            <select class="form-control select2"
                                                                name="operation_assistants_id[]"
                                                                id="operation_assistants_id" multiple>
                                                                <option value="">{{ localize('global.select') }}
                                                                </option>
                                                                @foreach ($operation_doctors as $value)
                                                                    <option value="{{ $value->id }}"
                                                                        {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                        {{ $value->name }}

                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label
                                                                for="anesthesia_type{{ $appointment->id }}">{{ localize('global.anesthesia_type') }}</label>
                                                            <select class="form-control select2"
                                                                name="anesthesia_type"
                                                                id="anesthesia_type">
                                                                <option value="">{{ localize('global.select') }}</option>
                                                                <option value="local">{{localize('global.local')}}</option>
                                                                <option value="spinal">{{localize('global.spinal')}}</option>
                                                                <option value="general">{{localize('global.general')}}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label for="operation_type_id{{ $appointment->id }}"
                                                            class="mt-2 mb-2">{{ localize('global.operation_type') }}</label>
                                                        <select class="form-control select2" name="operation_type_id">
                                                            <option value="">{{ localize('global.select') }}</option>
                                                            @foreach ($operationTypes as $value)
                                                                <option value="{{ $value->id }}"
                                                                    {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                    {{ $value->name }}

                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="date"
                                                        class="mt-2 mb-2">{{ localize('global.date') }}</label>
                                                        <x-tools.dariDatePicker name="date" dir="ltr"
                                                        withID="date" withPlaceHolder="{{ localize('global.date') }}"
                                                        withSize="3" extraClasses="" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="time"
                                                        class="mt-2 mb-2">{{ localize('global.time') }}</label>
                                                    <input type="time" class="form-control" name="time" />
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label for="planned_duration"
                                                        class="mt-2 mb-2">{{ localize('global.planned_duration') }}</label>
                                                    <input type="text" class="form-control" name="planned_duration" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="position_on_bed"
                                                        class="mt-2 mb-2">{{ localize('global.position_on_bed') }}</label>
                                                    <input type="text" class="form-control" name="position_on_bed" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="estimated_blood_waste"
                                                        class="mt-2 mb-2">{{ localize('global.estimated_blood_waste') }}</label>
                                                    <input type="text" class="form-control"
                                                        name="estimated_blood_waste" />
                                                    </div>
                                                </div>
                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End Create Lab Modal -->
                        <div class="table-container">
                            @if($appointment->anesthesias->count() > 0)
                                <table class="enhanced-table">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.number') }}</th>
                                            <th>{{ localize('global.operation_type') }}</th>
                                            <th>{{ localize('global.patient_name') }}</th>
                                            <th>{{ localize('global.status') }}</th>
                                            <th>{{ localize('global.date') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($appointment->anesthesias as $anesthesia)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $anesthesia->operationType->name }}</td>
                                                <td>{{ $anesthesia->patient->name }}</td>
                                                <td>
                                                    @if ($anesthesia->status == 'new')
                                                        <span class="enhanced-badge badge-primary">
                                                            <i class="bx bx-plus-circle"></i> New
                                                        </span>
                                                    @elseif ($anesthesia->status == 'rejected')
                                                        <span class="enhanced-badge badge-danger">
                                                            <i class="bx bx-x-circle"></i> Rejected
                                                        </span>
                                                    @else
                                                        <span class="enhanced-badge badge-success">
                                                            <i class="bx bx-check-circle"></i> Approved
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>{{ $anesthesia->date }}</td>
                                                <td>
                                                    @can('edit-anesthesias')
                                                    <a href="{{ route('anesthesias.edit', $anesthesia->id) }}" class="action-btn edit" title="Edit">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                    @endcan
                                                    @can('delete-anesthesias')
                                                    <a href="{{ route('anesthesias.destroy', $anesthesia) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$anesthesia->id}}').submit(); }" class="action-btn delete" title="Delete">
                                                        <i class="bx bx-trash"></i>
                                                    </a>
                                                    @endcan
                                                    <!-- Using a <form> element -->
                                                    <form id="delete-form-{{$anesthesia->id}}" action="{{ route('anesthesias.destroy', $anesthesia) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-state">
                                    <div class="badge bg-label-danger">
                                        {{ localize('global.not_referred_to_anesthesia') }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                class="bx bx-cut p-1"></i>{{ localize('global.operations') }}</h5>

                        <div class="col-md-12 mt-4">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.operation_type') }}</th>
                                        <th>{{ localize('global.patient_name') }}</th>
                                        <th>{{ localize('global.status') }}</th>
                                        <th>{{ localize('global.date') }}</th>
                                        {{-- <th>{{ localize('global.actions') }}</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($appointment->approved_anesthesias as $anesthesia)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $anesthesia->operationType->name }}</td>
                                            <td>
                                                {{ $anesthesia->patient->name }}
                                            </td>
                                            <td>
                                                @if ($anesthesia->status == 'new')
                                                    <span class="bx bx-plus-circle text-primary"></span>
                                                @else
                                                    <span class="bx bx-check-circle text-success"></span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $anesthesia->date }}
                                            </td>
                                            {{-- <td>
                                                <a href="{{ route('anesthesias.edit', $anesthesia->id) }}"><span><i
                                                            class="bx bx-edit"></i></span></a>
                                                <a href="{{ route('anesthesias.destroy', $anesthesia->id) }}"><span><i
                                                            class="bx bx-trash text-danger"></i></span></a>

                                            </td> --}}
                                        </tr>
                                    @empty
                                        <div class="container">
                                            <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                <div class=" badge bg-label-danger mt-4">
                                                    {{ localize('global.not_referred_to_operation') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>



                        {{-- icu starts here  --}}
                        <div class="section-header">
                            <i class="bx bx-tv"></i>
                            {{ localize('global.refere_to_icu') }}
                            @if ($appointment->is_completed == 0)
                            @can('refer-to-icu')
                                <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal"
                                    data-bs-target="#createICUModal{{ $appointment->id }}">
                                    <i class="bx bx-plus"></i>
                                </button>
                            @endcan
                            @endif
                        </div>
                        <!-- Create  Lab Modal -->
                        <div class="modal fade" id="createICUModal{{ $appointment->id }}" tabindex="-1"
                            aria-labelledby="createICUModalLabel{{ $appointment->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="createICUModalLabel{{ $appointment->id }}">
                                            {{ localize('global.refere_to_icu') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('icus.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}"
                                                name="patient_id" value="{{ $appointment->patient_id }}">
                                            <input type="hidden" id="appointment_id{{ $appointment->id }}"
                                                name="appointment_id" value="{{ $appointment->id }}">
                                            <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                                value="{{ auth()->user()->id }}">
                                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                                value="{{ auth()->user()->branch_id }}">

                                            <div class="form-group">

                                                <div class="form-group">
                                                    <label
                                                        for="description{{ $appointment->id }}">{{ localize('global.description') }}</label>
                                                    <textarea class="form-control" id="description{{ $appointment->id }}" name="description" rows="3"></textarea>
                                                </div>
                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ localize('global.save') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End Create Lab Modal -->
                        <div class="table-container">
                            @if($appointment->icu->count() > 0)
                                <table class="enhanced-table">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.number') }}</th>
                                            <th>{{ localize('global.patient_name') }}</th>
                                            <th>{{ localize('global.description') }}</th>
                                            <th>{{ localize('global.date') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($appointment->icu as $icu)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $icu->patient->name }}</td>
                                                <td>{{ $icu->description }}</td>
                                                <td>{{ $icu->created_at }}</td>
                                                <td>
                                                    @can('edit-icus')
                                                    <a href="{{ route('icus.edit', $icu->id) }}" class="action-btn edit" title="Edit">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                    @endcan
                                                    @can('delete-icus')
                                                    <a href="{{ route('icus.destroy', $icu) }}" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$icu->id}}').submit(); }" class="action-btn delete" title="Delete">
                                                        <i class="bx bx-trash"></i>
                                                    </a>
                                                    @endcan
                                                    <!-- Using a <form> element -->
                                                    <form id="delete-form-{{$icu->id}}" action="{{ route('icus.destroy', $icu) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-state">
                                    <div class="badge bg-label-danger">
                                        {{ localize('global.not_referred_to_icu') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-12 d-flex justify-content-center">
                            <h5 class="mb-4 p-3 bg-label-primary mt-4"><i
                                    class="bx bx-glasses p-1"></i>{{ localize('global.related_icu_visits') }}</h5>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.description') }}</th>
                                        <th>{{ localize('global.by') }}</th>
                                        <th>{{ localize('global.visit_date') }}</th>
                                        <th>{{ localize('global.vital_signs') }}</th>
                                        <th>{{ localize('global.antibiotic') }}</th>
                                        <th>{{ localize('global.food_type') }}</th>
                                        <th>{{ localize('global.intake') }}</th>
                                        <th>{{ localize('global.output') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($appointment->icu as $icu)
                                    @forelse($icu->visits as $visit)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $visit->description }}</td>
                                            <td>{{ $visit->doctor->name }}</td>
                                            <td>{{ $visit->created_at }}</td>
                                            <td dir="ltr">
                                                <span class="badge bg-primary">{{ localize('global.bp') }}</span>
                                                {{ $visit->bp }}
                                                <br>
                                                <span class="badge bg-primary">{{ localize('global.pr') }}</span>
                                                {{ $visit->pr }}
                                                <br>
                                                <span class="badge bg-primary">{{ localize('global.rr') }}</span>
                                                {{ $visit->rr }}
                                                <br>
                                                <span class="badge bg-primary">{{ localize('global.t') }}</span>
                                                {{ $visit->t }}
                                                <br>
                                                <span class="badge bg-primary">{{ localize('global.spo2') }}</span>
                                                {{ $visit->spo2 }}
                                                <br>
                                                <span class="badge bg-primary">{{ localize('global.pain') }}</span>
                                                {{ $visit->pain }}

                                            </td>
                                            <td>{{$visit->antibiotic}}</td>
                                            <td>
                                                @foreach ($visit->getAssociatedFoodTypesAttribute() as $foodType)
                                                    <span class="badge bg-primary">{{ $foodType->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>{{$visit->intake}}</td>
                                            <td>{{$visit->output}}</td>
                                        </tr>
                                    @empty
                                        <div class="container">
                                            <div class="col-md-12 d-flex justify-content-center align-itmes-center">
                                                <div class=" badge bg-label-danger mt-4">
                                                    {{ localize('global.no_previous_visits') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Get the add button and prescription input container
        const addButton = document.getElementById('addPrescriptionInput');
        const prescriptionContainer = document.getElementById('prescription-input-container');

        // Add click event listener to the add button
        function addRow() {
                // Create a new row div
                const newRow = document.createElement('div');
    newRow.className = 'row';

    // Create the type dropdown
    const typeDropdown = document.createElement('select');
    typeDropdown.className = 'form-control select2';
    typeDropdown.name = 'medicine_type_id[]';

    // Append the options to the type dropdown
    @foreach ($medicineTypes as $value)
        typeOption = document.createElement('option');
        typeOption.value = '{{ $value->id }}';
        typeOption.textContent = '{{ $value->type }}';
        typeDropdown.appendChild(typeOption);
    @endforeach

    // Create the medicine dropdown
    const medicineDropdown = document.createElement('select');
    medicineDropdown.className = 'form-control select2';
    medicineDropdown.name = 'medicine_id[]';

    // Append the options to the medicine dropdown
    var medicineOption = '';
    @foreach ($medicines as $value)
            medicineOption = document.createElement('option');
            medicineOption.value = '{{ $value->id }}';
            medicineOption.textContent = '{{ $value->name }}';
            medicineDropdown.appendChild(medicineOption);
    @endforeach

    // Create the medicine dropdown
    const medicineUsageDropdown = document.createElement('select');
    medicineUsageDropdown.className = 'form-control select2';
    medicineUsageDropdown.name = 'usage_type_id[]';

    // Append the options to the medicine dropdown
    var medicineUsageOption = '';
    @foreach ($medicineUsageTypes as $value)
            medicineUsageOption = document.createElement('option');
            medicineUsageOption.value = '{{ $value->id }}';
            medicineUsageOption.textContent = '{{ $value->name }}';
            medicineUsageDropdown.appendChild(medicineUsageOption);
    @endforeach

    // Create the dosage input field
    const dosageInput = document.createElement('input');
    dosageInput.type = 'text';
    dosageInput.className = 'form-control mt-2';
    dosageInput.name = 'dosage[]';
    dosageInput.placeholder = 'Dosage';

    // Create the frequency input field
    const frequencyInput = document.createElement('input');
    frequencyInput.type = 'text';
    frequencyInput.className = 'form-control mt-2';
    frequencyInput.name = 'frequency[]';
    frequencyInput.placeholder = 'Frequency';

    // Create the amount input field
    const amountInput = document.createElement('input');
    amountInput.type = 'text';
    amountInput.className = 'form-control mt-2';
    amountInput.name = 'amount[]';
    amountInput.placeholder = 'Amount';

    // Create the delivery input field
    const deliveryInput = document.createElement('input');
    deliveryInput.type = 'hidden';
    deliveryInput.className = 'form-control mt-2';
    deliveryInput.name = 'is_delivered[]';
    deliveryInput.value = 0;

    // Create the column divs
    const typeCol = document.createElement('div');
    typeCol.className = 'col-md-2';
    const medicineCol = document.createElement('div');
    medicineCol.className = 'col-md-2';
    const medicineUsageCol = document.createElement('div');
    medicineUsageCol.className = 'col-md-2';
    const dosageCol = document.createElement('div');
    dosageCol.className = 'col-md-2';
    const frequencyCol = document.createElement('div');
    frequencyCol.className = 'col-md-2';
    const amountCol = document.createElement('div');
    amountCol.className = 'col-md-2';
    const deliveryCol = document.createElement('div');
    deliveryCol.className = 'col-md-2';

    // Append the input fields to their respective column divs
    typeCol.appendChild(typeDropdown);
    medicineCol.appendChild(medicineDropdown);
    medicineUsageCol.appendChild(medicineUsageDropdown);
    dosageCol.appendChild(dosageInput);
    frequencyCol.appendChild(frequencyInput);
    amountCol.appendChild(amountInput);
    deliveryCol.appendChild(deliveryInput);

    // Append the column divs to the new row div
    newRow.appendChild(typeCol);
    newRow.appendChild(medicineCol);
    newRow.appendChild(medicineUsageCol);
    newRow.appendChild(dosageCol);
    newRow.appendChild(frequencyCol);
    newRow.appendChild(amountCol);
    newRow.appendChild(deliveryCol);

    // Append the new row div to the prescription input container
    prescriptionContainer.appendChild(newRow);

    // Initialize the select2 plugin
            $('select').select2({
                dropdownParent: $('#createPrescriptionModal1')
            });

        }
    </script>

    <script>
        $(document).ready(function() {
            $('#lab_type_section').on('change', function() {
                var labSectionID = $(this).val();
                if (labSectionID !== '') {
                    $.ajax({
                        url: '/get_labTypes/' + labSectionID,
                        type: 'GET',
                        success: function(response) {

                            $('#lab_type_id').html(response);
                        }
                    })
                }
            })

            $('#branch').on('change', function() {
                var branchId = $(this).val();
                if (branchId !== '') {
                    $.ajax({
                        url: '/get_departments/' + branchId,
                        type: 'GET',
                        success: function(response) {

                            $('#department').html(response);
                        }
                    })
                }
            })

            $('#referral_branch').on('change', function() {
                var branchId = $(this).val();
                if (branchId !== '') {
                    $.ajax({
                        url: '/get_departments/' + branchId,
                        type: 'GET',
                        success: function(response) {

                            $('#referral_department_id').html(response);
                        }
                    })
                }
            })

            $('#department').on('change', function() {
                var departmentId = $(this).val();
                if (departmentId !== '') {
                    $.ajax({
                        url: '/get_doctors/' + departmentId,
                        type: 'GET',
                        success: function(response) {

                            $('#doctor_id').html(response);
                        }
                    })
                }
            })

            $('#referral_department_id').on('change', function() {
                var departmentID = $(this).val();
                if (departmentID !== '') {
                    $.ajax({
                        url: '/get_doctors/' + departmentID,
                        type: 'GET',
                        success: function(response) {

                            $('#appointment_doctor_id').html(response);
                        }
                    })
                }
            })

            $('#room_id').on('change', function() {
                var roomId = $(this).val();
                if (roomId !== '') {
                    $.ajax({
                        url: '/get_related_beds/' + roomId,
                        type: 'GET',
                        success: function(response) {

                            $('#bed_id').html(response);
                        }
                    })
                }
            })

            $('#under_review_room').on('change', function() {
                var roomId = $(this).val();
                if (roomId !== '') {
                    $.ajax({
                        url: '/get_related_beds/' + roomId,
                        type: 'GET',
                        success: function(response) {

                            $('#under_review_bed_id').html(response);
                        }
                    })
                }
            })
        })
    </script>

    <script>
        function loadLabTypeTests() {
            var labTypeId = document.getElementById('lab_type_id').value;
            var labTypeTestsContainer = document.getElementById('labTypeTestsContainer');
            labTypeTestsContainer.innerHTML = ''; // Clear previous checkboxes

            // Make an AJAX request to fetch the lab type tests based on the selected lab_type_id
            fetch('/lab-tests/' + labTypeId)
                .then(response => response.json())
                .then(data => {
                    // Create checkboxes for each lab type test
                    data.forEach(function(test) {
                        var checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.name = 'lab_type_id[]'; // Use an array to submit multiple values
                        checkbox.value = test.id;

                        // Update the lab_type_id value when a checkbox is checked/unchecked
                        $('input').on('change', function() {
                            if (this.checked) {
                                // Append the test id to the lab_type_id value
                                document.getElementById('lab_type_id').value += ',' + this.value;
                            } else {
                                // Remove the test id from the lab_type_id value
                                var labTypeIdValue = document.getElementById('lab_type_id').value;
                                labTypeIdValue = labTypeIdValue.replace(',' + this.value, '');
                                labTypeIdValue = labTypeIdValue.replace(this.value + ',', '');
                                labTypeIdValue = labTypeIdValue.replace(this.value, '');
                                document.getElementById('lab_type_id').value = labTypeIdValue;
                            }
                        });

                        // Create a label for the checkbox
                        var label = document.createElement('label');
                        label.appendChild(checkbox);
                        label.appendChild(document.createTextNode(test.name));

                        // Append the checkbox to the labTypeTestsContainer
                        labTypeTestsContainer.appendChild(label);
                    });
                })
                .catch(error => {
                    console.log(error);
                });
        }


        // get lab items ajax

        function getLabItems(id) {

            $.ajax({
                type: "GET",
                url: "{{ url('lab_items/getItems/') }}/" + id,
                dataType: "html",
                success: function(data) {
                    $('#lab_items_table').html(data);
                    console.log(data);
                },
                error: function(xhr, status, error) {
                    // Handle the error response
                    console.error(error);
                }
            });

        }

        function getPrescriptionItems(id){ $.ajax({ type: "GET", url: "{{url('prescription_items/getItems/')}}/"+id, dataType: "html", success: function(data)
        {
             $('#prescription_items_table').html(data); }, error: function(xhr, status, error) {
             // Handle the error response
             console.error(error); } }); }
    </script>
@endsection
