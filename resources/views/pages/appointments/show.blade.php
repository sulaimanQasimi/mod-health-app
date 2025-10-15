@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}"
                                class="text-decoration-none">{{ localize('global.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}"
                                class="text-decoration-none">{{ localize('global.appointments') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ localize('global.appointment_details') }}
                        </li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h4 mb-0">
                        <i class="bx bx-calendar-check me-2 text-primary"></i>
                        {{ localize('global.appointment_details') }}
                    </h2>
                    <div class="d-flex gap-2">
                        <a href="javascript:void(0);" onclick="window.open('/appointments/{{$appointment->id}}/printToken', '_blank');" class="btn btn-success btn-sm">
                            <i class="bx bx-printer me-1"></i>
                            {{ localize('global.token') }}
                        </a>
                        <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-edit me-1"></i>
                            {{ localize('global.edit') }}
                        </a>
                        <a href="{{ route('appointments.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bx bx-arrow-back me-1"></i>
                            {{ localize('global.back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointment Details Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-body-secondary text-body">
                        <h5 class="mb-0 text-center">
                            <i class="bx bx-calendar-check me-2 text-primary"></i>
                            {{ localize('global.appointment_details') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 border rounded bg-body-secondary">
                                    <div class="text-body small mb-1">{{ localize('global.patient_name') }}</div>
                                    <div class="fw-bold">
                                        <i class="bx bx-user me-1 text-primary"></i>
                                        {{ $appointment->patient->name }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 border rounded bg-body-secondary">
                                    <div class="text-body-secondary small mb-1">{{ localize('global.referred_to') }}</div>
                                    <div class="fw-bold">
                                        <i class="bx bx-user-check me-1 text-primary"></i>
                                        {{ $appointment->doctor?->name }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 border rounded bg-body-secondary">
                                    <div class="text-body-secondary small mb-1">{{ localize('global.date') }}</div>
                                    <div class="fw-bold">
                                        <i class="bx bx-calendar me-1 text-primary"></i>
                                        {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($appointment->date) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="text-center p-3 border rounded bg-body-secondary">
                                    <div class="text-body-secondary small mb-1">{{ localize('global.time') }}</div>
                                    <div class="fw-bold">
                                        <i class="bx bx-time me-1 text-primary"></i>
                                        {{ $appointment->time }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patient History Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-body-secondary text-body">
                        <h5 class="mb-0 text-center">
                            <i class="bx bx-history me-2 text-info"></i>
                            {{ localize('global.patient_history') }}
                        </h5>
                    </div>
                    <div class="card-body">
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
                                        <ul class="list-unstyled">
                                            @foreach ($primaryDiagnoses as $diagnose)
                                                <li
                                                    class="m-1 p-2 border-start border-warning border-3 bg-none border p-2 rounded">
                                                    <span
                                                        class="badge bg-warning text-dark me-2">{{$diagnose->created_at? verta($diagnose->created_at)->format('Y-m-d') : 'N/A' }}</span>
                                                    {{ $diagnose->description }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="list-unstyled">
                                            @foreach ($finalDiagnoses as $diagnose)
                                                <li
                                                    class="m-1 p-2 border-start border-success border-3 bg-none border p-2 rounded">
                                                    <span
                                                        class="badge bg-success text-white me-2">{{$diagnose->created_at? verta($diagnose->created_at)->format('Y-m-d') : 'N/A' }}</span>
                                                    {{ $diagnose->description }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointment Status Section -->
        @can('update-appointment-status')
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-body-secondary text-body d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bx bx-check-shield me-2 text-primary"></i>
                                {{ localize('global.appointment_status') }}
                            </h5>
                            @if ($appointment->is_completed == 0)
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#createStatusChangeModal{{ $appointment->id }}">
                                    <i class="bx bx-check-shield me-1"></i>
                                    {{ localize('global.complete_appointment') }}
                                </button>
                            @else
                                <span class="badge bg-success fs-6">
                                    <i class="bx bx-check-shield me-1"></i>
                                    {{ localize('global.appointment_completed') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="createStatusChangeModal{{ $appointment->id }}" tabindex="-1"
                aria-labelledby="createStatusChangeModalLabel{{ $appointment->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createStatusChangeModalLabel{{ $appointment->id }}">
                                {{ localize('global.make_appointment_completed') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('appointments.changeStatus', $appointment) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_completed" value="1">

                                <div class="form-group">

                                    <div class="form-group">
                                        <label
                                            for="status_remark{{ $appointment->id }}">{{ localize('global.status_remark') }}</label>
                                        <textarea class="form-control" id="status_remark{{ $appointment->id }}"
                                            name="status_remark" rows="3"></textarea>
                                    </div>

                                </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan


        <!-- Diagnose Section Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion" id="diagnoseAccordion{{ $appointment->id }}">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="diagnoseHeading{{ $appointment->id }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#diagnoseCollapse{{ $appointment->id }}" aria-expanded="false"
                                aria-controls="diagnoseCollapse{{ $appointment->id }}">
                                <i class="bx bx-popsicle me-2 text-warning"></i>
                                {{ localize('global.diagnose') }}
                                @if($appointment->diagnose->count() > 0)
                                    <span class="badge bg-primary ms-2">{{ $appointment->diagnose->count() }}</span>
                                @endif
                            </button>
                        </h2>
                        <div id="diagnoseCollapse{{ $appointment->id }}" class="accordion-collapse collapse"
                            aria-labelledby="diagnoseHeading{{ $appointment->id }}" data-bs-parent="#diagnoseAccordion{{ $appointment->id }}">
                            <div class="accordion-body">
                                <!-- Diagnosis Section Vue Component -->
                                <div id="diagnosis-section-container" 
                                     data-appointment='@json($appointment)'
                                     data-permissions='@json([
                                         "canAddDiagnosis" => auth()->user()->can("add-diagnose"),
                                         "canEditDiagnosis" => auth()->user()->can("edit-diagnoses"),
                                         "canDeleteDiagnosis" => auth()->user()->can("delete-diagnoses")
                                     ])'>
                                    <!-- Fallback content while Vue loads -->
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">{{ localize('global.loading_diagnosis_section') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prescription Section Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion" id="prescriptionAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="prescriptionHeading">
                            <button class="accordion-button collapsed bg-body-secondary text-body" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#prescriptionCollapse" 
                                aria-expanded="false" aria-controls="prescriptionCollapse">
                                <i class="bx bx-notepad me-2 text-success"></i>
                                {{ localize('global.prescription') }}
                                @if($appointment->prescription->count() > 0)
                                    <span class="badge bg-success ms-2">{{ $appointment->prescription->count() }}</span>
                                @endif
                            </button>
                        </h2>
                        <div id="prescriptionCollapse" class="accordion-collapse collapse" 
                            aria-labelledby="prescriptionHeading" data-bs-parent="#prescriptionAccordion">
                            <div class="accordion-body">
                                <!-- Prescription Section Vue Component -->
                                <div id="prescription-section-container" 
                                     data-appointment='@json($appointment)'
                                     data-permissions='@json([
                                         "canAddPrescription" => auth()->user()->can("add-prescription"),
                                         "canEditPrescription" => auth()->user()->can("edit-prescriptions"),
                                         "canDeletePrescription" => auth()->user()->can("delete-prescriptions")
                                     ])'>
                                    <!-- Fallback content while Vue loads -->
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">{{ localize('global.loading_prescription_section') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Advice Section Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion" id="adviceAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="adviceHeading">
                            <button class="accordion-button collapsed bg-body-secondary text-body" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#adviceCollapse" 
                                aria-expanded="false" aria-controls="adviceCollapse">
                                <i class="bx bx-command me-2 text-info"></i>
                                {{ localize('global.advice') }}
                                @if($appointment->advices->count() > 0)
                                    <span class="badge bg-info ms-2">{{ $appointment->advices->count() }}</span>
                                @endif
                            </button>
                        </h2>
                        <div id="adviceCollapse" class="accordion-collapse collapse" 
                            aria-labelledby="adviceHeading" data-bs-parent="#adviceAccordion">
                            <div class="accordion-body">
                                <!-- Advice Section Vue Component -->
                                <div id="advice-section-container" 
                                     data-appointment='@json($appointment)'
                                     data-permissions='@json([
                                         "canAddAdvice" => auth()->user()->can("add-advice"),
                                         "canEditAdvice" => auth()->user()->can("edit-advices"),
                                         "canDeleteAdvice" => auth()->user()->can("delete-advices")
                                     ])'>
                                    <!-- Fallback content while Vue loads -->
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">{{ localize('global.loading_advice_section') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Lab Section Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion" id="labAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="labHeading">
                            <button class="accordion-button collapsed bg-body-secondary text-body" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#labCollapse" 
                                aria-expanded="false" aria-controls="labCollapse">
                                <i class="bx bx-hard-hat me-2 text-warning"></i>
                                {{ localize('global.checkups') }}
                                @if($appointment->labs->count() > 0)
                                    <span class="badge bg-warning text-dark ms-2">{{ $appointment->labs->count() }}</span>
                                @endif
                            </button>
                        </h2>
                        <div id="labCollapse" class="accordion-collapse collapse" 
                            aria-labelledby="labHeading" data-bs-parent="#labAccordion">
                            <div class="accordion-body">
                                <!-- Lab Section Vue Component -->
                                <div id="lab-section-container" 
                                     data-appointment='@json($appointment)'
                                     data-permissions='@json([
                                         "canAddLab" => auth()->user()->can("add-patient-labs"),
                                         "canEditLab" => auth()->user()->can("edit-patient-labs"),
                                         "canDeleteLab" => auth()->user()->can("delete-patient-labs")
                                     ])'>
                                    <!-- Fallback content while Vue loads -->
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">{{ localize('global.loading_lab_section') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hospitalization Checkups Section Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion" id="hospitalizationAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="hospitalizationHeading">
                            <button class="accordion-button collapsed bg-body-secondary text-body" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#hospitalizationCollapse" 
                                aria-expanded="false" aria-controls="hospitalizationCollapse">
                                <i class="bx bx-hard-hat me-2 text-secondary"></i>
                                {{ localize('global.hospitalization_checkups') }}
                                @if($appointment->hospitalization->count() > 0)
                                    <span class="badge bg-secondary ms-2">{{ $appointment->hospitalization->count() }}</span>
                                @endif
                            </button>
                        </h2>
                        <div id="hospitalizationCollapse" class="accordion-collapse collapse" 
                            aria-labelledby="hospitalizationHeading" data-bs-parent="#hospitalizationAccordion">
                            <div class="accordion-body">
                                <!-- Hospitalization Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-body-secondary">
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
                                                        <td>
                                                            <span class="badge bg-secondary rounded-pill">{{ $loop->iteration }}</span>
                                                        </td>
                                                        <td>{{ $lab->labType->name }}</td>
                                                        <td>
                                                            @if ($lab->status == '0')
                                                                <span class="badge bg-danger">{{ localize('global.not_tested') }}</span>
                                                            @else
                                                                <span class="badge bg-success">{{ localize('global.tested') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $lab->result }}</td>
                                                        <td>
                                                            @isset($lab->result_file)
                                                                <a href="{{ asset('storage/' . $lab->result_file) }}" target="_blank"
                                                                    class="btn btn-outline-primary btn-sm">
                                                                    <i class="fa fa-file me-1"></i> {{ localize('global.file') }}
                                                                </a>
                                                            @endisset
                                                        </td>
                                                        <td>
                                                            <!-- Actions can be added here if needed -->
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">
                                                        <div class="alert alert-info">
                                                            <i class="bx bx-info-circle me-2"></i>
                                                            {{ localize('global.no_previous_labs') }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>




        <!-- Consultations Section Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion" id="consultationAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="consultationHeading">
                            <button class="accordion-button collapsed bg-body-secondary text-body" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#consultationCollapse" 
                                aria-expanded="false" aria-controls="consultationCollapse">
                                <i class="bx bx-chat me-2 text-primary"></i>
                                {{ localize('global.consultations') }}
                                @if($appointment->consultations->count() > 0)
                                    <span class="badge bg-primary ms-2">{{ $appointment->consultations->count() }}</span>
                                @endif
                            </button>
                        </h2>
                        <div id="consultationCollapse" class="accordion-collapse collapse" 
                            aria-labelledby="consultationHeading" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <!-- Consultation Section Vue Component -->
                                <div id="consultation-section-container" 
                                     data-appointment='@json($appointment)'
                                     data-permissions='@json([
                                         "canAddConsultation" => auth()->user()->can("add-consultations"),
                                         "canEditConsultation" => auth()->user()->can("edit-consultations"),
                                         "canDeleteConsultation" => auth()->user()->can("delete-consultations")
                                     ])'>
                                    <!-- Fallback content while Vue loads -->
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">{{ localize('global.loading_consultation_section') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Refer to Another Doctor Section Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion" id="referDoctorAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="referDoctorHeading">
                            <button class="accordion-button collapsed bg-body-secondary text-body" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#referDoctorCollapse" 
                                aria-expanded="false" aria-controls="referDoctorCollapse">
                                <i class="bx bx-transfer me-2 text-danger"></i>
                                {{ localize('global.refer_to_another_department') }}
                                @if($appointment->is_completed == 1)
                                    <span class="badge bg-danger ms-2">1</span>
                                @endif
                            </button>
                        </h2>
                        <div id="referDoctorCollapse" class="accordion-collapse collapse" 
                            aria-labelledby="referDoctorHeading" data-bs-parent="#referDoctorAccordion">
                            <div class="accordion-body">
                                <!-- Add Button -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div></div> <!-- Empty div for spacing -->
                                    <div>
                                        @if ($appointment->is_completed == 0)
                                            @can('refer-to-another-doctor')
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#createReferDoctorModal{{ $appointment->id }}">
                                                    <i class="bx bx-plus me-1"></i>
                                                    {{ localize('global.refer_patient') }}
                                                </button>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Referral Remarks Section -->
                                @if ($appointment->is_completed == 1)
                                    <div class="alert alert-success">
                                        <i class="bx bx-check-circle me-2"></i>
                                        <strong>{{ localize('global.referral_remarks') }}:</strong>
                                        {{ $appointment->refferal_remarks }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Create Refer Doctor Modal -->
        <div class="modal fade" id="createReferDoctorModal{{ $appointment->id }}" tabindex="-1"
            aria-labelledby="createReferDoctorModalLabel{{ $appointment->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createReferDoctorModalLabel{{ $appointment->id }}">
                            {{ localize('global.refere_patient_to_department') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('appointments.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <input type="hidden" name="patient_id" value="{{ $appointment->patient->id }}">
                                <input type="hidden" name="is_completed" value="0">
                                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                                <input type="hidden" name="date" value="{{ $appointment->date }}">
                                <input type="hidden" name="time" value="{{ $appointment->time }}">
                                
                                <label for="department_id">{{ localize('global.department') }}</label>
                                <select class="form-control select2" name="department_id" id="referral_department_id">
                                    <option value="">{{ localize('global.select') }}</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label
                                    for="refferal_remarks{{ $appointment->id }}">{{ localize('global.refferal_remarks') }}</label>
                                <textarea class="form-control" id="refferal_remarks{{ $appointment->id }}"
                                    name="refferal_remarks" rows="3" placeholder="{{ localize('global.enter_referral_remarks') }}"></textarea>
                            </div>

                            <input type="hidden" name="current_appointment_id" value="{{ $appointment->id }}">

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                <button type="submit" class="btn btn-primary">{{ localize('global.refer_patient') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <!-- Under Review Section Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion" id="underReviewAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="underReviewHeading">
                            <button class="accordion-button collapsed bg-body-secondary text-body" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#underReviewCollapse" 
                                aria-expanded="false" aria-controls="underReviewCollapse">
                                <i class="bx bx-revision me-2 text-dark"></i>
                                {{ localize('global.under_review') }}
                                @if($appointment->under_reviews->count() > 0)
                                    <span class="badge bg-dark ms-2">{{ $appointment->under_reviews->count() }}</span>
                                @endif
                            </button>
                        </h2>
                        <div id="underReviewCollapse" class="accordion-collapse collapse" 
                            aria-labelledby="underReviewHeading" data-bs-parent="#underReviewAccordion">
                            <div class="accordion-body">
                                <!-- Add Button -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div></div> <!-- Empty div for spacing -->
                                    <div>
                                        @if ($appointment->is_completed == 0)
                                            @can('patient-under-review')
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#createUnderReviewModal{{ $appointment->id }}">
                                                    <i class="bx bx-plus me-1"></i>
                                                    {{ localize('global.add') }}
                                                </button>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                                
        <!-- Create  Lab Modal -->
        <div class="modal fade" id="createUnderReviewModal{{ $appointment->id }}" tabindex="-1"
            aria-labelledby="createUnderReviewModalLabel{{ $appointment->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createUnderReviewModalLabel{{ $appointment->id }}">
                            {{ localize('global.refere_to_under_review') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('under_reviews.store') }}" method="POST">
                            @csrf
                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}" name="patient_id"
                                value="{{ $appointment->patient_id }}">
                            <input type="hidden" id="appointment_id{{ $appointment->id }}" name="appointment_id"
                                value="{{ $appointment->id }}">
                            <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                value="{{ auth()->user()->id }}">
                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                value="{{ auth()->user()->branch_id }}">
                            <input type="hidden" id="is_discharged{{ $appointment->id }}" name="is_discharged" value="0">

                            <div class="form-group">

                                <div class="form-group">
                                    <label for="reason{{ $appointment->id }}">{{ localize('global.reason') }}</label>
                                    <textarea class="form-control" id="reason{{ $appointment->id }}" name="reason"
                                        rows="3"></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="remarks{{ $appointment->id }}">{{ localize('global.remarks') }}</label>
                                    <textarea class="form-control" id="remarks{{ $appointment->id }}" name="remarks"
                                        rows="3"></textarea>
                                </div>


                                <label for="room_id{{ $appointment->id }}">{{ localize('global.rooms') }}</label>
                                <select class="form-control select2" name="room_id" id="under_review_room">
                                    <option value="">{{ localize('global.select') }}</option>
                                    @foreach ($rooms as $value)
                                        <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                            {{ $value->name }}

                                        </option>
                                    @endforeach
                                </select>

                                <label for="bed_id{{ $appointment->id }}">{{ localize('global.beds') }}</label>
                                <select class="form-control select2" name="bed_id" id="under_review_bed_id">
                                    <option value="">{{ localize('global.select') }}</option>
                                    @foreach ($beds as $value)
                                        <option value="{{ $value->id }}" {{ old('number') == $value->id ? 'selected' : '' }}>
                                            {{ $value->number }}

                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

                                <!-- Under Review Table -->
                                @if($appointment->under_reviews->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-body-secondary">
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
                                                        <td>
                                                            <span class="badge bg-dark rounded-pill">{{ $loop->iteration }}</span>
                                                        </td>
                                                        <td>{{ $underReview->reason }}</td>
                                                        <td>{{ $underReview->remarks }}</td>
                                                        <td>
                                                            <span class="badge bg-secondary">{{ $underReview->room->name }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info">{{ $underReview->bed->number }}</span>
                                                        </td>
                                                        <td>
                                                            @if ($underReview->is_discharged == '0')
                                                                <span class="badge bg-danger">
                                                                    <i class="bx bx-x-circle me-1"></i> {{ localize('global.under_review') }}
                                                                </span>
                                                            @else
                                                                <span class="badge bg-success">
                                                                    <i class="bx bx-check-circle me-1"></i> {{ localize('global.discharged') }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                @can('edit-under-reviews')
                                                                    <a href="{{ route('under_reviews.edit', $underReview->id) }}"
                                                                        class="btn btn-outline-primary btn-sm" title="Edit">
                                                                        <i class="bx bx-edit"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('delete-under-reviews')
                                                                    <a href="{{ route('under_reviews.destroy', $underReview->id) }}"
                                                                        class="btn btn-outline-danger btn-sm" title="Delete">
                                                                        <i class="bx bx-trash"></i>
                                                                    </a>
                                                                @endcan
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle me-2"></i>
                                            {{ localize('global.no_previous_under_reviews') }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Related Visits Section Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion" id="relatedVisitsAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="relatedVisitsHeading">
                            <button class="accordion-button collapsed bg-body-secondary text-body" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#relatedVisitsCollapse" 
                                aria-expanded="false" aria-controls="relatedVisitsCollapse">
                                <i class="bx bx-glasses me-2"></i>
                                {{ localize('global.related_visits') }}
                            </button>
                        </h2>
                        <div id="relatedVisitsCollapse" class="accordion-collapse collapse" 
                            aria-labelledby="relatedVisitsHeading" data-bs-parent="#relatedVisitsAccordion">
                            <div class="accordion-body">
                                <!-- Related Visits Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-body-secondary">
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
                                                        <td>
                                                            <span
                                                                class="badge bg-body-secondary text-body rounded-pill">{{ $loop->iteration }}</span>
                                                        </td>
                                                        <td>{{ $visit->description }}</td>
                                                        <td>
                                                            <span class="badge bg-secondary">{{ $visit->doctor->name }}</span>
                                                        </td>
                                                        <td>{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($visit->created_at->format('Y-m-d')) }}</td>
                                                    </tr>
                                                @endforeach
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">
                                                        <div class="alert alert-info">
                                                            <i class="bx bx-info-circle me-2"></i>
                                                            {{ localize('global.no_previous_visits') }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hospitalize Section Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion" id="hospitalizeAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="hospitalizeHeading">
                            <button class="accordion-button collapsed bg-body-secondary text-body" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#hospitalizeCollapse" 
                                aria-expanded="false" aria-controls="hospitalizeCollapse">
                                <i class="bx bx-bed me-2 text-success"></i>
                                {{ localize('global.hospitalize') }}
                                @if($appointment->hospitalization->count() > 0)
                                    <span class="badge bg-success ms-2">{{ $appointment->hospitalization->count() }}</span>
                                @endif
                            </button>
                        </h2>
                        <div id="hospitalizeCollapse" class="accordion-collapse collapse" 
                            aria-labelledby="hospitalizeHeading" data-bs-parent="#hospitalizeAccordion">
                            <div class="accordion-body">
                                <!-- Add Button -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div></div> <!-- Empty div for spacing -->
                                    <div>
                                        @if ($appointment->is_completed == 0)
                                            @can('patient-hospitalization')
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#createHospitalizationModal{{ $appointment->id }}">
                                                    <i class="bx bx-plus me-1"></i>
                                                    {{ localize('global.add') }}
                                                </button>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                                
        <!-- Create  Lab Modal -->
        <div class="modal fade modal-xl" id="createHospitalizationModal{{ $appointment->id }}" tabindex="-1"
            aria-labelledby="createHospitalizationModalLabel{{ $appointment->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createHospitalizationModalLabel{{ $appointment->id }}">
                            {{ localize('global.hospitalize_patient') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('hospitalizations.store') }}" method="POST">
                            @csrf
                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}" name="patient_id"
                                value="{{ $appointment->patient_id }}">
                            <input type="hidden" id="appointment_id{{ $appointment->id }}" name="appointment_id"
                                value="{{ $appointment->id }}">
                            <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                value="{{ auth()->user()->id }}">
                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                value="{{ auth()->user()->branch_id }}">
                            <input type="hidden" id="is_discharged{{ $appointment->id }}" name="is_discharged" value="0">

                            <div class="form-group">

                                <div class="form-group">
                                    <label for="reason{{ $appointment->id }}">{{ localize('global.reason') }}</label>
                                    <textarea class="form-control" id="reason{{ $appointment->id }}" name="reason"
                                        rows="3"></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="remarks{{ $appointment->id }}">{{ localize('global.remarks') }}</label>
                                    <textarea class="form-control" id="remarks{{ $appointment->id }}" name="remarks"
                                        rows="3"></textarea>
                                </div>

                                <div class="form-group">
                                    <div class="row p-2">
                                        <div class="col-md-4">
                                            <label
                                                for="room_id{{ $appointment->id }}">{{ localize('global.rooms') }}</label>
                                            <select class="form-control select2" name="room_id" id="room_id">
                                                <option value="">{{ localize('global.select') }}
                                                </option>
                                                @foreach ($rooms as $value)
                                                    <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                        {{ $value->name }}

                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="bed_id{{ $appointment->id }}">{{ localize('global.beds') }}</label>
                                            <select class="form-control select2" name="bed_id" id="bed_id">
                                                <option value="">{{ localize('global.select') }}
                                                </option>
                                                @foreach ($beds as $value)
                                                    <option value="{{ $value->id }}" {{ old('number') == $value->id ? 'selected' : '' }}>
                                                        {{ $value->number }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label
                                                for="food_type_id{{ $appointment->id }}">{{ localize('global.food_type') }}</label>
                                            <select class="form-control select2" name="food_type_id[]" id="food_type_id"
                                                multiple>
                                                <option value="">{{ localize('global.select') }}
                                                </option>
                                                @foreach ($foodTypes as $value)
                                                    <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
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
                                                <input type="text" class="form-control" name="patinet_companion">
                                            </div>
                                            <div class="col-md-3">
                                                <label>{{ localize('global.companion_father_name') }}</label>
                                                <input type="text" class="form-control" name="companion_father_name">
                                            </div>
                                            <div class="col-md-3">
                                                <label>{{ localize('global.relation_to_patient') }}</label>
                                                <select class="form-control select2" name="relation_to_patient">
                                                    <option value="">
                                                        {{ localize('global.select') }}
                                                    </option>
                                                    @foreach ($relations as $value)
                                                        <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                            {{ $value->name }}

                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label>{{ localize('global.companion_card_type') }}</label>
                                                <select class="form-control select2" name="companion_card_type">
                                                    <option value="">
                                                        {{ localize('global.select') }}
                                                    </option>
                                                    <option value="12">
                                                        {{ localize('global.12_hours') }}
                                                    </option>
                                                    <option value="24">
                                                        {{ localize('global.24_hours') }}
                                                    </option>
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
                        <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- End Create Lab Modal -->

                                <!-- Hospitalization Table -->
                                @if($appointment->hospitalization->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-body-secondary">
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
                                                        <td>
                                                            <span class="badge bg-success rounded-pill">{{ $loop->iteration }}</span>
                                                        </td>
                                                        <td>{{ $hospitalization->reason }}</td>
                                                        <td>{{ $hospitalization->remarks }}</td>
                                                        <td>
                                                            <span class="badge bg-secondary">{{ $hospitalization->room->name }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info">{{ $hospitalization->bed->number }}</span>
                                                        </td>
                                                        <td>
                                                            @if ($hospitalization->is_discharged == 0)
                                                                <span class="badge bg-danger">{{ localize('global.in_bed') }}</span>
                                                            @else
                                                                <span class="badge bg-success">{{ localize('global.discharged') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                @can('edit-hospitalizations')
                                                                    <a href="{{ route('hospitalizations.edit', $hospitalization->id) }}"
                                                                        class="btn btn-outline-primary btn-sm" title="Edit">
                                                                        <i class="bx bx-edit"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('delete-hospitalizations')
                                                                    <a href="{{ route('hospitalizations.destroy', $hospitalization) }}"
                                                                        onclick="event.preventDefault(); if(confirm('{{ localize('global.are_you_sure_delete') }}')) { document.getElementById('delete-form-{{$hospitalization->id}}').submit(); }"
                                                                        class="btn btn-outline-danger btn-sm" title="Delete">
                                                                        <i class="bx bx-trash"></i>
                                                                    </a>
                                                                @endcan
                                                                <!-- Using a <form> element -->
                                                                <form id="delete-form-{{$hospitalization->id}}"
                                                                    action="{{ route('hospitalizations.destroy', $hospitalization) }}"
                                                                    method="POST" style="display: none;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle me-2"></i>
                                            {{ localize('global.no_previous_hospitalizations') }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Visits Section (Hospitalization) Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion" id="relatedVisitsHospitalizationAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="relatedVisitsHospitalizationHeading">
                            <button class="accordion-button collapsed bg-body-secondary text-body" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#relatedVisitsHospitalizationCollapse" 
                                aria-expanded="false" aria-controls="relatedVisitsHospitalizationCollapse">
                                <i class="bx bx-glasses me-2"></i>
                                {{ localize('global.related_visits') }} ({{ localize('global.hospitalization') }})
                            </button>
                        </h2>
                        <div id="relatedVisitsHospitalizationCollapse" class="accordion-collapse collapse" 
                            aria-labelledby="relatedVisitsHospitalizationHeading" data-bs-parent="#relatedVisitsHospitalizationAccordion">
                            <div class="accordion-body">
                                <!-- Related Visits Table (Hospitalization) -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-body-secondary">
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
                                                        <td>
                                                            <span
                                                                class="badge bg-body-secondary text-body rounded-pill">{{ $loop->iteration }}</span>
                                                        </td>
                                                        <td>{{ $visit->description }}</td>
                                                        <td>
                                                            <span class="badge bg-secondary">{{ $visit->doctor->name }}</span>
                                                        </td>
                                                        <td>{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($visit->created_at->format('Y-m-d')) }}</td>
                                                        <td dir="ltr">
                                                            <div class="small">
                                                                <span class="badge bg-primary me-1">{{ localize('global.bp') }}</span>
                                                                {{ $visit->bp }}
                                                                <br>
                                                                <span class="badge bg-primary me-1">{{ localize('global.pr') }}</span>
                                                                {{ $visit->pr }}
                                                                <br>
                                                                <span class="badge bg-primary me-1">{{ localize('global.rr') }}</span>
                                                                {{ $visit->rr }}
                                                                <br>
                                                                <span class="badge bg-primary me-1">{{ localize('global.t') }}</span>
                                                                {{ $visit->t }}
                                                                <br>
                                                                <span class="badge bg-primary me-1">{{ localize('global.spo2') }}</span>
                                                                {{ $visit->spo2 }}
                                                                <br>
                                                                <span class="badge bg-primary me-1">{{ localize('global.pain') }}</span>
                                                                {{ $visit->pain }}
                                                            </div>
                                                        </td>
                                                        <td>{{$visit->antibiotic}}</td>
                                                        <td>
                                                            @foreach ($visit->getAssociatedFoodTypesAttribute() as $foodType)
                                                                <span class="badge bg-primary me-1">{{ $foodType->name }}</span>
                                                            @endforeach
                                                        </td>
                                                        <td>{{$visit->intake}}</td>
                                                        <td>{{$visit->output}}</td>
                                                    </tr>
                                                @endforeach
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center">
                                                        <div class="alert alert-info">
                                                            <i class="bx bx-info-circle me-2"></i>
                                                            {{ localize('global.no_previous_visits') }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Anesthesia Section Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion" id="anesthesiaAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="anesthesiaHeading">
                            <button class="accordion-button collapsed bg-body-secondary text-body" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#anesthesiaCollapse" 
                                aria-expanded="false" aria-controls="anesthesiaCollapse">
                                <i class="bx bx-first-aid me-2 text-danger"></i>
                                {{ localize('global.refere_to_anasthesia') }}
                                @if($appointment->anesthesias->count() > 0)
                                    <span class="badge bg-danger ms-2">{{ $appointment->anesthesias->count() }}</span>
                                @endif
                            </button>
                        </h2>
                        <div id="anesthesiaCollapse" class="accordion-collapse collapse" 
                            aria-labelledby="anesthesiaHeading" data-bs-parent="#anesthesiaAccordion">
                            <div class="accordion-body">
                                <!-- Add Button -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div></div> <!-- Empty div for spacing -->
                                    <div>
                                        @if ($appointment->is_completed == 0)
                                            @can('refer-to-anesthesia')
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#createAnasthesiaModal{{ $appointment->id }}">
                                                    <i class="bx bx-plus me-1"></i>
                                                    {{ localize('global.add') }}
                                                </button>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                                
        <!-- Create  Lab Modal -->
        <div class="modal fade modal-xl" id="createAnasthesiaModal{{ $appointment->id }}" tabindex="-1"
            aria-labelledby="createAnasthesiaModalLabel{{ $appointment->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createAnasthesiaModalLabel{{ $appointment->id }}">
                            {{ localize('global.refere_to_anasthesia') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('anesthesias.store') }}" method="POST">
                            @csrf
                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}" name="patient_id"
                                value="{{ $appointment->patient_id }}">
                            <input type="hidden" id="appointment_id{{ $appointment->id }}" name="appointment_id"
                                value="{{ $appointment->id }}">
                            <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                value="{{ auth()->user()->id }}">
                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                value="{{ auth()->user()->branch_id }}">

                            <div class="form-group">

                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="plan{{ $appointment->id }}">{{ localize('global.plan') }}</label>
                                        <textarea class="form-control" id="plan{{ $appointment->id }}" name="plan"
                                            rows="3"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            for="other_problems{{ $appointment->id }}">{{ localize('global.other_problems') }}</label>
                                        <textarea class="form-control" id="other_problems{{ $appointment->id }}"
                                            name="other_problems" rows="3"></textarea>
                                    </div>
                                </div>
                                <h5 class="mt-2">{{ localize('global.operation_team') }}</h5>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label
                                                for="operation_surgion_id{{ $appointment->id }}">{{ localize('global.operation_surgion') }}</label>
                                            <select class="form-control select2" name="operation_surgion_id"
                                                id="operation_surgion_id">
                                                <option value="">{{ localize('global.select') }}
                                                </option>
                                                @foreach ($operation_doctors as $value)
                                                    <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                        {{ $value->name }}

                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label
                                                for="operation_assistants_id{{ $appointment->id }}">{{ localize('global.operation_assistants') }}</label>
                                            <select class="form-control select2" name="operation_assistants_id[]"
                                                id="operation_assistants_id" multiple>
                                                <option value="">{{ localize('global.select') }}
                                                </option>
                                                @foreach ($operation_doctors as $value)
                                                    <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                        {{ $value->name }}

                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label
                                                for="anesthesia_type{{ $appointment->id }}">{{ localize('global.anesthesia_type') }}</label>
                                            <select class="form-control select2" name="anesthesia_type"
                                                id="anesthesia_type">
                                                <option value="">{{ localize('global.select') }}</option>
                                                <option value="local">{{localize('global.local')}}</option>
                                                <option value="spinal">{{localize('global.spinal')}}
                                                </option>
                                                <option value="general">{{localize('global.general')}}
                                                </option>
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
                                                <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name }}

                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="date" class="mt-2 mb-2">{{ localize('global.date') }}</label>
                                        <x-tools.dariDatePicker name="date" dir="ltr" withID="date"
                                            withPlaceHolder="{{ localize('global.date') }}" withSize="3" extraClasses="" />
                                    </div>
                                    <div class="col-md-4">
                                        <label for="time" class="mt-2 mb-2">{{ localize('global.time') }}</label>
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
                                        <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End Create Lab Modal -->
                        
                                <!-- Anesthesia Table -->
                                @if($appointment->anesthesias->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-body-secondary">
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
                                                        <td>
                                                            <span class="badge bg-danger rounded-pill">{{ $loop->iteration }}</span>
                                                        </td>
                                                        <td>{{ $anesthesia->operationType?->name }}</td>
                                                        <td>{{ $anesthesia->patient?->name ?? 'N/A' }}</td>
                                                        <td>
                                                            @if ($anesthesia->status == 'new')
                                                                <span class="badge bg-primary">
                                                                    <i class="bx bx-plus-circle me-1"></i> New
                                                                </span>
                                                            @elseif ($anesthesia->status == 'rejected')
                                                                <span class="badge bg-danger">
                                                                    <i class="bx bx-x-circle me-1"></i> Rejected
                                                                </span>
                                                            @else
                                                                <span class="badge bg-success">
                                                                    <i class="bx bx-check-circle me-1"></i> Approved
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td>{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($anesthesia->date) }}</td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                @can('edit-anesthesias')
                                                                    <a href="{{ route('anesthesias.edit', $anesthesia->id) }}"
                                                                        class="btn btn-outline-primary btn-sm" title="Edit">
                                                                        <i class="bx bx-edit"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('delete-anesthesias')
                                                                    <a href="{{ route('anesthesias.destroy', $anesthesia) }}"
                                                                        onclick="event.preventDefault(); if(confirm('{{ localize('global.are_you_sure_delete') }}')) { document.getElementById('delete-form-{{$anesthesia->id}}').submit(); }"
                                                                        class="btn btn-outline-danger btn-sm" title="Delete">
                                                                        <i class="bx bx-trash"></i>
                                                                    </a>
                                                                @endcan
                                                                <!-- Using a <form> element -->
                                                                <form id="delete-form-{{$anesthesia->id}}"
                                                                    action="{{ route('anesthesias.destroy', $anesthesia) }}" method="POST"
                                                                    style="display: none;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle me-2"></i>
                                            {{ localize('global.not_referred_to_anesthesia') }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operations Section Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion" id="operationAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="operationHeading">
                            <button class="accordion-button collapsed bg-body-secondary text-body" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#operationCollapse" 
                                aria-expanded="false" aria-controls="operationCollapse">
                                <i class="bx bx-cut me-2 text-warning"></i>
                                {{ localize('global.operations') }}
                                @if($appointment->approved_anesthesias->count() > 0)
                                    <span class="badge bg-warning text-dark ms-2">{{ $appointment->approved_anesthesias->count() }}</span>
                                @endif
                            </button>
                        </h2>
                        <div id="operationCollapse" class="accordion-collapse collapse" 
                            aria-labelledby="operationHeading" data-bs-parent="#operationAccordion">
                            <div class="accordion-body">
                                <!-- Operations Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-body-secondary">
                                            <tr>
                                                <th>{{ localize('global.number') }}</th>
                                                <th>{{ localize('global.operation_type') }}</th>
                                                <th>{{ localize('global.patient_name') }}</th>
                                                <th>{{ localize('global.status') }}</th>
                                                <th>{{ localize('global.date') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($appointment->approved_anesthesias as $anesthesia)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-warning text-dark rounded-pill">{{ $loop->iteration }}</span>
                                                    </td>
                                                    <td>{{ $anesthesia->operationType?->name }}</td>
                                                    <td>{{ $anesthesia->patient?->name ?? 'N/A' }}</td>
                                                    <td>
                                                        @if ($anesthesia->status == 'new')
                                                            <span class="badge bg-primary">
                                                                <i class="bx bx-plus-circle me-1"></i> New
                                                            </span>
                                                        @else
                                                            <span class="badge bg-success">
                                                                <i class="bx bx-check-circle me-1"></i> Approved
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $anesthesia->date }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">
                                                        <div class="alert alert-info">
                                                            <i class="bx bx-info-circle me-2"></i>
                                                            {{ localize('global.not_referred_to_operation') }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ICU Section Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion" id="icuAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="icuHeading">
                            <button class="accordion-button collapsed bg-body-secondary text-body" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#icuCollapse" 
                                aria-expanded="false" aria-controls="icuCollapse">
                                <i class="bx bx-tv me-2 text-info"></i>
                                {{ localize('global.refere_to_icu') }}
                                @if($appointment->icu->count() > 0)
                                    <span class="badge bg-info ms-2">{{ $appointment->icu->count() }}</span>
                                @endif
                            </button>
                        </h2>
                        <div id="icuCollapse" class="accordion-collapse collapse" 
                            aria-labelledby="icuHeading" data-bs-parent="#icuAccordion">
                            <div class="accordion-body">
                                <!-- Add Button -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div></div> <!-- Empty div for spacing -->
                                    <div>
                                        @if ($appointment->is_completed == 0)
                                            @can('refer-to-icu')
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#createICUModal{{ $appointment->id }}">
                                                    <i class="bx bx-plus me-1"></i>
                                                    {{ localize('global.add') }}
                                                </button>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                                
        <!-- Create  Lab Modal -->
        <div class="modal fade" id="createICUModal{{ $appointment->id }}" tabindex="-1"
            aria-labelledby="createICUModalLabel{{ $appointment->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createICUModalLabel{{ $appointment->id }}">
                            {{ localize('global.refere_to_icu') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('icus.store') }}" method="POST">
                            @csrf
                            <input type="hidden" id="patient_id{{ $appointment->patient_id }}" name="patient_id"
                                value="{{ $appointment->patient_id }}">
                            <input type="hidden" id="appointment_id{{ $appointment->id }}" name="appointment_id"
                                value="{{ $appointment->id }}">
                            <input type="hidden" id="doctor_id{{ $appointment->id }}" name="doctor_id"
                                value="{{ auth()->user()->id }}">
                            <input type="hidden" id="branch_id{{ $appointment->id }}" name="branch_id"
                                value="{{ auth()->user()->branch_id }}">

                            <div class="form-group">

                                                <div class="form-group">
                                                    <label
                                                        for="description{{ $appointment->id }}">{{ localize('global.description') }}</label>
                                                    <textarea class="form-control" id="description{{ $appointment->id }}"
                                                        name="description" rows="3"></textarea>
                                                </div>
                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                        <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End Create Lab Modal -->
                        
                                <!-- ICU Table -->
                                @if($appointment->icu->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-body-secondary">
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
                                                        <td>
                                                            <span class="badge bg-info rounded-pill">{{ $loop->iteration }}</span>
                                                        </td>
                                                        <td>{{ $icu->patient->name }}</td>
                                                        <td>{{ $icu->description }}</td>
                                                        <td>{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($icu->created_at->format('Y-m-d')) }}</td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                @can('edit-icus')
                                                                    <a href="{{ route('icus.edit', $icu->id) }}" 
                                                                       class="btn btn-outline-primary btn-sm" title="Edit">
                                                                        <i class="bx bx-edit"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('delete-icus')
                                                                    <a href="{{ route('icus.destroy', $icu) }}"
                                                                        onclick="event.preventDefault(); if(confirm('{{ localize('global.are_you_sure_delete') }}')) { document.getElementById('delete-form-{{$icu->id}}').submit(); }"
                                                                        class="btn btn-outline-danger btn-sm" title="Delete">
                                                                        <i class="bx bx-trash"></i>
                                                                    </a>
                                                                @endcan
                                                                <!-- Using a <form> element -->
                                                                <form id="delete-form-{{$icu->id}}" action="{{ route('icus.destroy', $icu) }}"
                                                                    method="POST" style="display: none;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle me-2"></i>
                                            {{ localize('global.not_referred_to_icu') }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related ICU Visits Section Accordion -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="accordion" id="relatedIcuVisitsAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="relatedIcuVisitsHeading">
                            <button class="accordion-button collapsed bg-body-secondary text-body" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#relatedIcuVisitsCollapse" 
                                aria-expanded="false" aria-controls="relatedIcuVisitsCollapse">
                                <i class="bx bx-glasses me-2"></i>
                                {{ localize('global.related_icu_visits') }}
                            </button>
                        </h2>
                        <div id="relatedIcuVisitsCollapse" class="accordion-collapse collapse" 
                            aria-labelledby="relatedIcuVisitsHeading" data-bs-parent="#relatedIcuVisitsAccordion">
                            <div class="accordion-body">
                                <!-- Related ICU Visits Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-body-secondary">
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
                                                        <td>
                                                            <span
                                                                class="badge bg-body-secondary text-body rounded-pill">{{ $loop->iteration }}</span>
                                                        </td>
                                                        <td>{{ $visit->description }}</td>
                                                        <td>
                                                            <span class="badge bg-secondary">{{ $visit->doctor->name }}</span>
                                                        </td>
                                                        <td>{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($visit->created_at->format('Y-m-d')) }}</td>
                                                        <td dir="ltr">
                                                            <div class="small">
                                                                <span class="badge bg-primary me-1">{{ localize('global.bp') }}</span>
                                                                {{ $visit->bp }}
                                                                <br>
                                                                <span class="badge bg-primary me-1">{{ localize('global.pr') }}</span>
                                                                {{ $visit->pr }}
                                                                <br>
                                                                <span class="badge bg-primary me-1">{{ localize('global.rr') }}</span>
                                                                {{ $visit->rr }}
                                                                <br>
                                                                <span class="badge bg-primary me-1">{{ localize('global.t') }}</span>
                                                                {{ $visit->t }}
                                                                <br>
                                                                <span class="badge bg-primary me-1">{{ localize('global.spo2') }}</span>
                                                                {{ $visit->spo2 }}
                                                                <br>
                                                                <span class="badge bg-primary me-1">{{ localize('global.pain') }}</span>
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
        </div>
        @can('show-physiotherapy-procedures')
            
            <!-- Physiotherapy Procedures Section -->
            @include('pages.appointments.physiotherapy_procedures_crud')
        @endcan

    </div>
@endsection

@section('scripts')
    <!-- Vue.js Lab Section -->
    @vite(['public/assets/js/vue/lab-app.js'])
    
    <!-- Vue.js Prescription Section -->
    @vite(['public/assets/js/vue/appointment-prescription-app.js'])
    
    <!-- Vue.js Diagnosis Section -->
    @vite(['public/assets/js/vue/diagnosis-app.js'])
    
    <!-- Vue.js Advice Section -->
    @vite(['public/assets/js/vue/appointment-advice-app.js'])
    
    <!-- Persian Datepicker Library -->
    <script src="{{ asset('assets/js/persian_datepicker.js') }}"></script>
    
    <!-- Vue.js Consultation Section -->
    @vite(['public/assets/js/vue/consultation-app.js'])
    <script>
        // Global Select2 fix for modal compatibility
        (function () {
            // Override Select2's _positionDropdown method to handle modal contexts
            if ($.fn.select2 && $.fn.select2.defaults && $.fn.select2.defaults.defaults && $.fn.select2.defaults.defaults.attachBody) {
                var originalPositionDropdown = $.fn.select2.defaults.defaults.attachBody._positionDropdown;
                
                // Only override if the original method exists
                if (originalPositionDropdown && typeof originalPositionDropdown === 'function') {
                    $.fn.select2.defaults.defaults.attachBody._positionDropdown = function () {
                        try {
                            return originalPositionDropdown.apply(this, arguments);
                        } catch (e) {
                            // If positioning fails, use a fallback
                            var $dropdown = this.$dropdown;
                            var $container = this.$container;

                            if ($dropdown && $container) {
                                var containerOffset = $container.offset();
                                $dropdown.css({
                                    position: 'absolute',
                                    top: containerOffset.top + $container.outerHeight(),
                                    left: containerOffset.left,
                                    width: $container.outerWidth()
                                });
                            }
                        }
                    };
                }
            }
        })();
    </script>

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

            // Initialize the select2 plugin for dynamically created elements
            const newSelects = newRow.querySelectorAll('select.select2');
            newSelects.forEach(function (select) {
                if (typeof $.fn.select2 !== 'undefined') {
                    $(select).select2({
                        dropdownParent: $(select).closest('.modal').find('.modal-body'),
                        placeholder: '--انتخاب--'
                    });
                }
            });

        }
    </script>

    <script>
        $(document).ready(function () {
            // Check if Select2 is available before initializing
            if (typeof $.fn.select2 === 'undefined') {
                console.warn('Select2 is not loaded. Skipping Select2 initialization.');
                return;
            }

            // Initialize Select2 for elements inside modals
            function initializeSelect2InModals() {
                $('.modal .select2').each(function () {
                    var $select = $(this);
                    var $modal = $select.closest('.modal');

                    // Destroy existing Select2 instance if it exists
                    if ($select.hasClass('select2-hidden-accessible')) {
                        $select.select2('destroy');
                    }

                    // Initialize Select2 with proper dropdown parent
                    if (typeof $.fn.select2 !== 'undefined') {
                        $select.select2({
                            dropdownParent: $modal.find('.modal-body'),
                            placeholder: '--انتخاب--'
                        });
                    }
                });
            }

            // Initialize Select2 when modals are shown
            $('.modal').on('shown.bs.modal', function () {
                initializeSelect2InModals();
            });

            // Initialize Select2 for elements outside modals
            $('.select2:not(.modal .select2)').each(function () {
                var $this = $(this);
                if (!$this.hasClass('select2-hidden-accessible') && typeof $.fn.select2 !== 'undefined') {
                    $this.select2({
                        placeholder: '--انتخاب--',
                        dropdownParent: $this.parent()
                    });
                }
            });

            $('#lab_type_section').on('change', function () {
                var labSectionID = $(this).val();
                if (labSectionID !== '') {
                    $.ajax({
                        url: '/get_labTypes/' + labSectionID,
                        type: 'GET',
                        success: function (response) {
                            $('#lab_type_id').html(response);
                            // Re-initialize Select2 for the updated dropdown
                            if (typeof $.fn.select2 !== 'undefined') {
                                $('#lab_type_id').select2({
                                    dropdownParent: $('#lab_type_id').closest('.modal').find('.modal-body'),
                                    placeholder: '--انتخاب--'
                                });
                            }
                        }
                    })
                } else {
                    // Clear the lab type dropdown if no section is selected
                    $('#lab_type_id').html('<option value="">{{ localize("global.select") }}</option>');
                    if (typeof $.fn.select2 !== 'undefined') {
                        $('#lab_type_id').select2({
                            dropdownParent: $('#lab_type_id').closest('.modal').find('.modal-body'),
                            placeholder: '--انتخاب--'
                        });
                    }
                }
            })

            $('#branch').on('change', function () {
                var branchId = $(this).val();
                if (branchId !== '') {
                    $.ajax({
                        url: '/get_departments/' + branchId,
                        type: 'GET',
                        success: function (response) {
                            $('#department').html(response);
                            // Re-initialize Select2 for the updated dropdown
                            if (typeof $.fn.select2 !== 'undefined') {
                                $('#department').select2({
                                    dropdownParent: $('#department').closest('.modal').find('.modal-body'),
                                    placeholder: '--انتخاب--'
                                });
                                // Clear the doctor dropdown when branch changes
                                $('#doctor_id').html('<option value="">{{ localize("global.select") }}</option>');
                                $('#doctor_id').select2({
                                    dropdownParent: $('#doctor_id').closest('.modal').find('.modal-body'),
                                    placeholder: '--انتخاب--'
                                });
                            }
                        }
                    })
                } else {
                    // Clear both department and doctor dropdowns if no branch is selected
                    $('#department').html('<option value="">{{ localize("global.select") }}</option>');
                    if (typeof $.fn.select2 !== 'undefined') {
                        $('#department').select2({
                            dropdownParent: $('#department').closest('.modal').find('.modal-body'),
                            placeholder: '--انتخاب--'
                        });
                        $('#doctor_id').html('<option value="">{{ localize("global.select") }}</option>');
                        $('#doctor_id').select2({
                            dropdownParent: $('#doctor_id').closest('.modal').find('.modal-body'),
                            placeholder: '--انتخاب--'
                        });
                    }
                }
            })


            $('#room_id').on('change', function () {
                var roomId = $(this).val();
                if (roomId !== '') {
                    $.ajax({
                        url: '/get_related_beds/' + roomId,
                        type: 'GET',
                        success: function (response) {

                            $('#bed_id').html(response);
                        }
                    })
                }
            })

            $('#under_review_room').on('change', function () {
                var roomId = $(this).val();
                if (roomId !== '') {
                    $.ajax({
                        url: '/get_related_beds/' + roomId,
                        type: 'GET',
                        success: function (response) {

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
                    data.forEach(function (test) {
                        var checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.name = 'lab_type_id[]'; // Use an array to submit multiple values
                        checkbox.value = test.id;

                        // Update the lab_type_id value when a checkbox is checked/unchecked
                        $('input').on('change', function () {
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
                success: function (data) {
                    $('#lab_items_table').html(data);
                    console.log(data);
                },
                error: function (xhr, status, error) {
                    // Handle the error response
                    console.error(error);
                }
            });

        }

        function getPrescriptionItems(id) {
            $.ajax({
                type: "GET", url: "{{url('prescription_items/getItems/')}}/" + id, dataType: "html", success: function (data) {
                    $('#prescription_items_table').html(data);
                }, error: function (xhr, status, error) {
                    // Handle the error response
                    console.error(error);
                }
            });
        }

        // Function to update accordion counters dynamically
        function updateAccordionCounter(accordionId, count) {
            const accordionButton = document.querySelector(`[data-bs-target="#${accordionId}"]`);
            if (accordionButton) {
                // Remove existing counter if it exists
                const existingCounter = accordionButton.querySelector('.badge');
                if (existingCounter) {
                    existingCounter.remove();
                }
                
                // Add new counter if count > 0
                if (count > 0) {
                    const counterSpan = document.createElement('span');
                    counterSpan.className = 'badge ms-2';
                    
                    // Set appropriate badge color based on accordion type
                    if (accordionId.includes('prescription')) {
                        counterSpan.className += ' bg-success';
                    } else if (accordionId.includes('advice')) {
                        counterSpan.className += ' bg-info';
                    } else if (accordionId.includes('lab')) {
                        counterSpan.className += ' bg-warning text-dark';
                    } else if (accordionId.includes('hospitalization')) {
                        counterSpan.className += ' bg-secondary';
                    } else if (accordionId.includes('consultation')) {
                        counterSpan.className += ' bg-primary';
                    } else if (accordionId.includes('underReview')) {
                        counterSpan.className += ' bg-dark';
                    } else if (accordionId.includes('hospitalize')) {
                        counterSpan.className += ' bg-success';
                    } else if (accordionId.includes('anesthesia')) {
                        counterSpan.className += ' bg-danger';
                    } else if (accordionId.includes('operation')) {
                        counterSpan.className += ' bg-warning text-dark';
                    } else if (accordionId.includes('icu')) {
                        counterSpan.className += ' bg-info';
                    }
                    
                    counterSpan.textContent = count;
                    accordionButton.appendChild(counterSpan);
                }
            }
        }

        // Function to increment counter for a specific accordion
        function incrementAccordionCounter(accordionId) {
            const accordionButton = document.querySelector(`[data-bs-target="#${accordionId}"]`);
            if (accordionButton) {
                const existingCounter = accordionButton.querySelector('.badge');
                let currentCount = 0;
                
                if (existingCounter) {
                    currentCount = parseInt(existingCounter.textContent) || 0;
                }
                
                updateAccordionCounter(accordionId, currentCount + 1);
            }
        }

        // Function to refresh counters after form submission
        function refreshCounters() {
            // This function will be called after successful form submissions
            // You can implement AJAX calls here to get updated counts from the server
            // For now, we'll use a simple approach that increments the counter
        }

        // Function to refresh all counters (useful for page load)
        function refreshAllCounters() {
            // This function can be used to refresh all counters from server data
            // For now, we'll just ensure all existing counters are properly displayed
            const accordionButtons = document.querySelectorAll('.accordion-button');
            accordionButtons.forEach(button => {
                const target = button.getAttribute('data-bs-target');
                if (target && target.includes('Collapse')) {
                    const existingBadge = button.querySelector('.badge');
                    if (existingBadge) {
                        // Counter already exists, no need to refresh
                        return;
                    }
                }
            });
        }

        // Listen for successful form submissions and update counters
        $(document).ready(function() {
            // Global AJAX success handler to update counters
            $(document).ajaxSuccess(function(event, xhr, settings) {
                // Check if the request was successful and update appropriate counter
                if (xhr.status === 200 || xhr.status === 201) {
                    const url = settings.url;
                    
                    // Update prescription counter
                    if (url.includes('prescriptions.store')) {
                        setTimeout(function() {
                            incrementAccordionCounter('prescriptionCollapse');
                        }, 500);
                    }
                    
                    // Update advice counter
                    if (url.includes('advices.store')) {
                        setTimeout(function() {
                            incrementAccordionCounter('adviceCollapse');
                        }, 500);
                    }
                    
                    // Update consultation counter
                    if (url.includes('consultations.store')) {
                        setTimeout(function() {
                            incrementAccordionCounter('consultationCollapse');
                        }, 500);
                    }
                    
                    // Update under review counter
                    if (url.includes('under_reviews.store')) {
                        setTimeout(function() {
                            incrementAccordionCounter('underReviewCollapse');
                        }, 500);
                    }
                    
                    // Update hospitalization counter
                    if (url.includes('hospitalizations.store')) {
                        setTimeout(function() {
                            incrementAccordionCounter('hospitalizeCollapse');
                        }, 500);
                    }
                    
                    // Update anesthesia counter
                    if (url.includes('anesthesias.store')) {
                        setTimeout(function() {
                            incrementAccordionCounter('anesthesiaCollapse');
                        }, 500);
                    }
                    
                    // Update ICU counter
                    if (url.includes('icus.store')) {
                        setTimeout(function() {
                            incrementAccordionCounter('icuCollapse');
                        }, 500);
                    }
                }
            });

            // Also listen for form submissions (for non-AJAX forms)
            $('form').on('submit', function() {
                const action = $(this).attr('action');
                
                if (action && action.includes('store')) {
                    // Store the form action in localStorage to track on page reload
                    const formType = getFormTypeFromAction(action);
                    if (formType) {
                        localStorage.setItem('pendingCounterIncrement', formType);
                    }
                }
            });

            // Listen for page load events to refresh counters
            $(window).on('load', function() {
                // Check for pending counter increments from form submissions
                const pendingIncrement = localStorage.getItem('pendingCounterIncrement');
                if (pendingIncrement) {
                    setTimeout(function() {
                        incrementAccordionCounter(pendingIncrement);
                        localStorage.removeItem('pendingCounterIncrement');
                    }, 1000);
                }
                
                // Refresh all counters on page load
                refreshAllCounters();
            });

            // Function to get accordion ID from form action
            function getFormTypeFromAction(action) {
                if (action.includes('prescriptions.store')) return 'prescriptionCollapse';
                if (action.includes('advices.store')) return 'adviceCollapse';
                if (action.includes('consultations.store')) return 'consultationCollapse';
                if (action.includes('under_reviews.store')) return 'underReviewCollapse';
                if (action.includes('hospitalizations.store')) return 'hospitalizeCollapse';
                if (action.includes('anesthesias.store')) return 'anesthesiaCollapse';
                if (action.includes('icus.store')) return 'icuCollapse';
                return null;
            }
        });
    </script>
        </div>
    </div>
@endsection