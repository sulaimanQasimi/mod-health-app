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
                            <li class="breadcrumb-item"><a href="{{ route('under_reviews.index') }}"
                                    class="text-decoration-none">{{ localize('global.under_reviews') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ localize('global.under_review_details') }}
                            </li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0">
                            <i class="bx bx-user-detail me-2 text-primary"></i>
                            {{ localize('global.under_review_details') }}
                        </h2>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('under_reviews.edit', $underReview->id) }}" class="btn btn-primary btn-sm">
                                <i class="bx bx-edit me-1"></i>
                                {{ localize('global.edit') }}
                            </a>
                            <a href="{{ route('under_reviews.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-arrow-back me-1"></i>
                                {{ localize('global.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Under Review Details Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-body-secondary text-body">
                            <h5 class="mb-0 text-center">
                                <i class="bx bx-user-detail me-2 text-primary"></i>
                                {{ localize('global.under_review_details') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body small mb-1">{{ localize('global.patient_name') }}</div>
                                        <div class="fw-bold">
                                            <i class="bx bx-user me-1 text-primary"></i>
                                            {{ $underReview->patient->name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body-secondary small mb-1">{{ localize('global.referred_to') }}
                                        </div>
                                        <div class="fw-bold">
                                            <i class="bx bx-user-check me-1 text-primary"></i>
                                            {{ $underReview->doctor?->name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body-secondary small mb-1">{{ localize('global.date') }}</div>
                                        <div class="fw-bold">
                                            <i class="bx bx-calendar me-1 text-primary"></i>
                                            {{ \Hekmatinasser\Verta\Verta::instance($underReview->created_at)->format('Y/n/j') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body-secondary small mb-1">{{ localize('global.time') }}</div>
                                        <div class="fw-bold">
                                            <i class="bx bx-time me-1 text-primary"></i>
                                            {{ $underReview->created_at->format('H:i:s') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 border rounded bg-body-secondary">
                                        <div class="text-body-secondary small mb-1">{{ localize('global.reason') }}</div>
                                        <div class="fw-bold">
                                            <i class="bx bx-info-circle me-1 text-primary"></i>
                                            {{ $underReview->reason }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 border rounded bg-body-secondary">
                                        <div class="text-body-secondary small mb-1">{{ localize('global.remarks') }}</div>
                                        <div class="fw-bold">
                                            <i class="bx bx-note me-1 text-primary"></i>
                                            {{ $underReview->remarks }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    {{-- Visits Section --}}
                    <div class="accordion" id="visitsAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="visitsHeading">
                                <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#visitsCollapse" aria-expanded="false"
                                    aria-controls="visitsCollapse">
                                    <i class="bx bx-glasses me-2 text-info"></i>
                                    {{ localize('global.visits') }}
                                    @if($underReview->visits->count() > 0)
                                        <span class="badge bg-info ms-2">{{ $underReview->visits->count() }}</span>
                                    @endif
                                </button>
                            </h2>
                            <div id="visitsCollapse" class="accordion-collapse collapse" aria-labelledby="visitsHeading"
                                data-bs-parent="#visitsAccordion">
                                <div class="accordion-body">
                                    <div class="d-flex gap-2 mb-3">
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                            data-bs-target="#createVisitModal{{ $underReview->id }}">
                                            <i class="bx bx-plus"></i> {{localize('global.add_visit')}}
                                        </button>
                                    </div>

                                    <!-- Create visit Modal -->
                                    <div class="modal fade" id="createVisitModal{{ $underReview->id }}" tabindex="-1"
                                        aria-labelledby="createVisitModalLabel{{ $underReview->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"
                                                        id="createVisitModalLabel{{ $underReview->id }}">
                                                        {{localize('global.add_visit')}}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('visits.store') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" id="patient_id{{ $underReview->patient_id }}"
                                                            name="patient_id" value="{{ $underReview->patient_id }}">
                                                        <input type="hidden" id="under_review_id{{ $underReview->id }}"
                                                            name="under_review_id" value="{{ $underReview->id }}">
                                                        <input type="hidden" id="doctor_id{{ $underReview->id }}"
                                                            name="doctor_id" value="{{ $underReview->doctor->id }}">
                                                        <!-- Add other diagnosis form fields as needed -->
                                                        <div class="form-group">
                                                            <label
                                                                for="description{{ $underReview->id }}">{{localize('global.description')}}</label>
                                                            <textarea class="form-control"
                                                                id="description{{ $underReview->id }}" name="description"
                                                                rows="3"></textarea>
                                                        </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">{{localize('global.cancel')}}</button>
                                                    <button type="submit"
                                                        class="btn btn-primary">{{localize('global.save')}}</button>
                                                </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Create visit Modal -->

                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>{{localize('global.number')}}</th>
                                                    <th>{{localize('global.description')}}</th>
                                                    <th>{{localize('global.by')}}</th>
                                                    <th>{{localize('global.actions')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($underReview->visits as $visit)
                                                    <tr>
                                                        <td>{{$loop->iteration}}</td>
                                                        <td>{{$visit->description}}</td>
                                                        <td>{{$visit->doctor->name}}</td>
                                                        <td>
                                                            @can('edit-under-review-visit')
                                                                <a href="{{route('visits.edit', $visit->id)}}"><span><i
                                                                            class="bx bx-edit"></i></span></a>
                                                            @endcan
                                                            @can('delete-under-review-visit')
                                                                <a href="{{ route('visits.destroyUnderReviewVisit', $visit) }}"
                                                                    onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this item?')) { document.getElementById('delete-form-{{$visit->id}}').submit(); }">
                                                                    <i class="bx bx-trash text-danger"></i>
                                                                </a>
                                                            @endcan
                                                            <!-- Using a <form> element -->
                                                            <form id="delete-form-{{$visit->id}}"
                                                                action="{{ route('visits.destroyUnderReviewVisit', $visit) }}"
                                                                method="POST" style="display: none;">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">
                                                            <div class="badge bg-label-danger mt-4">
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
                    <!-- Prescription Section Accordion -->
                    <div class="col-12">
                        <div class="accordion" id="prescriptionAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="prescriptionHeading">
                                    <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#prescriptionCollapse"
                                        aria-expanded="false" aria-controls="prescriptionCollapse">
                                        <i class="bx bx-notepad me-2 text-success"></i>
                                        {{ localize('global.prescription') }}
                                        @if($underReview->appointment && $underReview->appointment->prescription && $underReview->appointment->prescription->count() > 0)
                                            <span
                                                class="badge bg-success ms-2">{{ $underReview->appointment->prescription->count() }}</span>
                                        @endif
                                    </button>
                                </h2>
                                <div id="prescriptionCollapse" class="accordion-collapse collapse"
                                    aria-labelledby="prescriptionHeading" data-bs-parent="#prescriptionAccordion">
                                    <div class="accordion-body">
                                        <!-- Prescription Section Vue Component -->
                                        <div id="prescription-section-container"
                                            data-appointment='@json($underReview->appointment ?? null)'
                                            data-under-review-id="{{ $underReview->id }}" data-permissions='@json([
                                                "canAddPrescription" => auth()->user()->can("add-prescription"),
                                                "canEditPrescription" => auth()->user()->can("edit-prescriptions"),
                                                "canDeletePrescription" => auth()->user()->can("delete-prescriptions")
                                            ])'>
                                            <!-- Fallback content while Vue loads -->
                                            <div class="text-center py-4">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="mt-2">{{ localize('global.loading_prescription_section') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Diabetes Charts Section --}}
                    <!-- Diabetes Charts Section Accordion -->
                    <div class="col-12">
                        <div class="accordion" id="diabetesChartsAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="diabetesChartsHeading">
                                    <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#diabetesChartsCollapse"
                                        aria-expanded="false" aria-controls="diabetesChartsCollapse">
                                        <i class="bx bx-bar-chart me-2 text-warning"></i>
                                        {{ localize('global.diabetes_charts') }}
                                        @if($diabetesCharts->count() > 0)
                                            <span class="badge bg-warning ms-2">{{ $diabetesCharts->count() }}</span>
                                        @endif
                                    </button>
                                </h2>
                                <div id="diabetesChartsCollapse" class="accordion-collapse collapse"
                                    aria-labelledby="diabetesChartsHeading" data-bs-parent="#diabetesChartsAccordion">
                                    <div class="accordion-body">
                                        <div class="d-flex gap-2 mb-3">
                                            <a href="{{ route('diabetes-charts.print', ['chartable_type' => 'App\\Models\\UnderReview', 'chartable_id' => $underReview->id]) }}"
                                                class="btn btn-info" target="_blank">
                                                <i class="fas fa-print"></i> {{ localize('global.print_chart') }}
                                            </a>
                                            <a href="{{ route('diabetes-charts.create', ['chartable_type' => 'App\\Models\\UnderReview', 'chartable_id' => $underReview->id]) }}"
                                                class="btn btn-success">
                                                <i class="bx bx-plus"></i> {{ localize('global.add_diabetes_chart') }}
                                            </a>
                                        </div>

                                        @if($diabetesCharts->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>{{ localize('global.date') }}</th>
                                                            <th>{{ localize('global.time') }}</th>
                                                            <th>{{ localize('global.rbs') }}</th>
                                                            <th>{{ localize('global.fbs') }}</th>
                                                            <th>{{ localize('global.insulin_dose') }}</th>
                                                            <th>{{ localize('global.unit') }}</th>
                                                            <th>{{ localize('global.nurse') }}</th>
                                                            <th>{{ localize('global.medicine') }}</th>
                                                            <th>{{ localize('global.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($diabetesCharts as $chart)
                                                            <tr>
                                                                <td>{{ $chart->id }}</td>
                                                                <td>
                                                                    @if($chart->date)
                                                                        <span
                                                                            class="badge bg-info">{{ $chart->date->format('Y-m-d') }}</span>
                                                                    @else
                                                                        <span class="text-muted">{{ localize('global.not_set') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($chart->time)
                                                                        <span
                                                                            class="badge bg-secondary">{{ $chart->formatted_time }}</span>
                                                                    @else
                                                                        <span class="text-muted">{{ localize('global.not_set') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($chart->rbs)
                                                                        <span class="badge bg-warning">{{ $chart->rbs }}
                                                                            {{ $chart->unit }}</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($chart->fbs)
                                                                        <span class="badge bg-success">{{ $chart->fbs }}
                                                                            {{ $chart->unit }}</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($chart->insulin_dose)
                                                                        <span class="badge bg-primary">{{ $chart->insulin_dose }}</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($chart->unit)
                                                                        <small>{{ $chart->unit }}</small>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($chart->nurse)
                                                                        <span
                                                                            class="badge bg-info">{{ $chart->nurse->full_name }}</span>
                                                                    @else
                                                                        <span
                                                                            class="text-muted">{{ localize('global.not_assigned') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($chart->medicine)
                                                                        <span
                                                                            class="badge bg-secondary">{{ $chart->medicine->name }}</span>
                                                                    @else
                                                                        <span
                                                                            class="text-muted">{{ localize('global.not_assigned') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <div class="btn-group" role="group">
                                                                        <a href="{{ route('diabetes-charts.show', $chart) }}"
                                                                            class="btn btn-sm btn-info"
                                                                            title="{{ localize('global.view') }}">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>
                                                                        <a href="{{ route('diabetes-charts.edit', $chart) }}"
                                                                            class="btn btn-sm btn-warning"
                                                                            title="{{ localize('global.edit') }}">
                                                                            <i class="fas fa-edit"></i>
                                                                        </a>
                                                                        <form
                                                                            action="{{ route('diabetes-charts.destroy', $chart) }}"
                                                                            method="POST" class="d-inline"
                                                                            onsubmit="return confirm('{{ localize('global.are_you_sure_delete') }}')">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="btn btn-sm btn-danger"
                                                                                title="{{ localize('global.delete') }}">
                                                                                <i class="fas fa-trash"></i>
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
                                            <div class="text-center py-4">
                                                <div class="mb-3">
                                                    <i class="bx bx-clipboard bx-lg text-muted"></i>
                                                </div>
                                                <h5 class="text-muted">{{ localize('global.no_diabetes_charts_found') }}
                                                </h5>
                                                <p class="text-muted">{{ localize('global.add_first_diabetes_chart') }}</p>
                                                <a href="{{ route('diabetes-charts.create', ['chartable_type' => 'App\\Models\\UnderReview', 'chartable_id' => $underReview->id]) }}"
                                                    class="btn btn-success">
                                                    <i class="bx bx-plus"></i> {{ localize('global.add_diabetes_chart') }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Nurse Notes Section --}}
                    <div class="col-12">
                        <div class="accordion" id="nurseNotesAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="nurseNotesHeading">
                                    <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#nurseNotesCollapse" aria-expanded="false"
                                        aria-controls="nurseNotesCollapse">
                                        <i class="bx bx-note me-2 text-primary"></i>
                                        {{ localize('global.nurse_notes') }}
                                        @if($nurseNotes->count() > 0)
                                            <span class="badge bg-primary ms-2">{{ $nurseNotes->count() }}</span>
                                        @endif
                                    </button>
                                </h2>
                                <div id="nurseNotesCollapse" class="accordion-collapse collapse"
                                    aria-labelledby="nurseNotesHeading" data-bs-parent="#nurseNotesAccordion">
                                    <div class="accordion-body">
                                        <div class="d-flex gap-2 mb-3">
                                            <a href="{{ route('nurse-notes.print', ['morphable_type' => 'App\\Models\\UnderReview', 'morphable_id' => $underReview->id]) }}"
                                                class="btn btn-info" target="_blank">
                                                <i class="fas fa-print"></i> {{ localize('global.print_notes') }}
                                            </a>
                                            @can('create', App\Models\NurseNote::class)
                                                <a href="{{ route('nurse-notes.create', ['morphable_type' => 'App\\Models\\UnderReview', 'morphable_id' => $underReview->id]) }}"
                                                    class="btn btn-success">
                                                    <i class="bx bx-plus"></i> {{ localize('global.add_nurse_note') }}
                                                </a>
                                            @endcan
                                        </div>

                                        @if($nurseNotes->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>{{ localize('global.date') }}</th>
                                                            <th>{{ localize('global.nurse') }}</th>
                                                            <th>{{ localize('global.am_time') }}</th>
                                                            <th>{{ localize('global.pm_time') }}</th>
                                                            <th>{{ localize('global.note') }}</th>
                                                            <th>{{ localize('global.created_by') }}</th>
                                                            <th>{{ localize('global.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($nurseNotes as $note)
                                                            <tr>
                                                                <td>{{ $note->id }}</td>
                                                                <td>
                                                                    @if($note->date)
                                                                        <span
                                                                            class="badge bg-info">{{ $note->date->format('Y-m-d') }}</span>
                                                                    @else
                                                                        <span
                                                                            class="text-muted">{{ localize('global.not_assigned') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($note->nurse)
                                                                        <span
                                                                            class="badge bg-primary">{{ $note->nurse->full_name }}</span>
                                                                    @else
                                                                        <span
                                                                            class="text-muted">{{ localize('global.not_assigned') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($note->time_am)
                                                                        <span
                                                                            class="badge bg-primary">{{ $note->time_am->format('H:i') }}</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($note->time_pm)
                                                                        <span
                                                                            class="badge bg-primary">{{ $note->time_pm->format('H:i') }}</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($note->note)
                                                                        <span class="text-truncate d-inline-block"
                                                                            style="max-width: 200px;" title="{{ $note->note }}">
                                                                            {{ Str::limit($note->note, 50) }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($note->createdBy)
                                                                        <span
                                                                            class="badge bg-secondary">{{ $note->createdBy->name }}</span>
                                                                    @else
                                                                        <span
                                                                            class="text-muted">{{ localize('global.not_assigned') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <div class="btn-group" role="group">
                                                                        @can('view', $note)
                                                                            <a href="{{ route('nurse-notes.show', $note) }}"
                                                                                class="btn btn-sm btn-info"
                                                                                title="{{ localize('global.view') }}">
                                                                                <i class="fas fa-eye"></i>
                                                                            </a>
                                                                        @endcan
                                                                        @can('update', $note)
                                                                            <a href="{{ route('nurse-notes.edit', $note) }}"
                                                                                class="btn btn-sm btn-warning"
                                                                                title="{{ localize('global.edit') }}">
                                                                                <i class="fas fa-edit"></i>
                                                                            </a>
                                                                        @endcan
                                                                        @can('delete', $note)
                                                                            <form action="{{ route('nurse-notes.destroy', $note) }}"
                                                                                method="POST" class="d-inline"
                                                                                onsubmit="return confirm('{{ localize('global.are_you_sure_delete') }}')">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit" class="btn btn-sm btn-danger"
                                                                                    title="{{ localize('global.delete') }}">
                                                                                    <i class="fas fa-trash"></i>
                                                                                </button>
                                                                            </form>
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
                                                <div class="mb-3">
                                                    <i class="bx bx-note bx-lg text-muted"></i>
                                                </div>
                                                <h5 class="text-muted">{{ localize('global.no_nurse_notes_found') }}</h5>
                                                <p class="text-muted">{{ localize('global.add_first_nurse_note') }}</p>
                                                @can('create', App\Models\NurseNote::class)
                                                    <a href="{{ route('nurse-notes.create', ['morphable_type' => 'App\\Models\\UnderReview', 'morphable_id' => $underReview->id]) }}"
                                                        class="btn btn-primary">
                                                        <i class="bx bx-plus"></i> {{ localize('global.add_nurse_note') }}
                                                    </a>
                                                @endcan
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Nutrition Care Section Accordion -->
                    <div class="col-12">
                        <div class="accordion" id="nutritionCareAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="nutritionCareHeading">
                                    <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#nutritionCareCollapse"
                                        aria-expanded="false" aria-controls="nutritionCareCollapse">
                                        <i class="bx bx-food-menu me-2 text-secondary"></i>
                                        {{ localize('global.nutrition_care') }}
                                        @if($underReview->nutritionCares->count() > 0)
                                            <span
                                                class="badge bg-secondary ms-2">{{ $underReview->nutritionCares->count() }}</span>
                                        @endif
                                    </button>
                                </h2>
                                <div id="nutritionCareCollapse" class="accordion-collapse collapse"
                                    aria-labelledby="nutritionCareHeading" data-bs-parent="#nutritionCareAccordion">
                                    <div class="accordion-body" id="nutrition-care-section">
                                        <div class="d-flex gap-2 mb-3">
                                            @can('create', \App\Models\NutritionCare::class)
                                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                    data-bs-target="#createNutritionCareModal">
                                                    <i class="bx bx-plus"></i>
                                                    {{ localize('global.create_nutrition_care') }}
                                                </button>
                                            @endcan
                                        </div>

                                        @if($underReview->nutritionCares->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ localize('global.id') }}</th>
                                                            <th>{{ localize('global.patient_name') }}</th>
                                                            <th>{{ localize('global.nurse') }}</th>
                                                            <th>{{ localize('global.observations') }}</th>
                                                            <th>{{ localize('global.interventions') }}</th>
                                                            <th>{{ localize('global.nutrition_care_full_note') }}</th>
                                                            <th>{{ localize('global.date_signature') }}</th>
                                                            <th>{{ localize('global.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($underReview->nutritionCares as $nutritionCare)
                                                            <tr>
                                                                <td>{{ $nutritionCare->id }}</td>
                                                                <td>{{ $nutritionCare->patient_name }}</td>
                                                                <td>{{ $nutritionCare->nurse->full_name ?? 'N/A' }}</td>
                                                                <td>
                                                                    @php
                                                                        $observations = [];
                                                                        if ($nutritionCare->cough)
                                                                            $observations[] = localize('global.cough');
                                                                        if ($nutritionCare->sound)
                                                                            $observations[] = localize('global.sound');
                                                                        if ($nutritionCare->fluid_swallowing_ability)
                                                                            $observations[] = localize('global.fluid_swallowing_ability');
                                                                        if ($nutritionCare->weight)
                                                                            $observations[] = localize('global.weight');
                                                                        if ($nutritionCare->amount_and_type_of_nutrition)
                                                                            $observations[] = localize('global.amount_and_type_of_nutrition');
                                                                        if ($nutritionCare->diarrhea)
                                                                            $observations[] = localize('global.diarrhea');
                                                                        if ($nutritionCare->heart_failure_and_kidney_disease)
                                                                            $observations[] = localize('global.heart_failure_and_kidney_disease');
                                                                        if ($nutritionCare->remaining_materials)
                                                                            $observations[] = localize('global.remaining_materials');
                                                                        if ($nutritionCare->type_of_tube)
                                                                            $observations[] = localize('global.type_of_tube');
                                                                    @endphp
                                                                    {{ implode(', ', $observations) ?: '-' }}
                                                                </td>
                                                                <td>
                                                                    @php
                                                                        $interventions = [];
                                                                        if ($nutritionCare->constipation)
                                                                            $interventions[] = localize('global.constipation');
                                                                        if ($nutritionCare->nutrition_is_provided)
                                                                            $interventions[] = localize('global.nutrition_is_provided');
                                                                        if ($nutritionCare->mouth_hygiene)
                                                                            $interventions[] = localize('global.mouth_hygiene');
                                                                        if ($nutritionCare->oral_nutrition_advices)
                                                                            $interventions[] = localize('global.oral_nutrition_advices');
                                                                        if ($nutritionCare->voice_exercise)
                                                                            $interventions[] = localize('global.voice_exercise');
                                                                        if ($nutritionCare->swallowing_exercise)
                                                                            $interventions[] = localize('global.swallowing_exercise');
                                                                        if ($nutritionCare->aspiration_prevention_proceeded)
                                                                            $interventions[] = localize('global.aspiration_prevention_proceeded');
                                                                    @endphp
                                                                    {{ implode(', ', $interventions) ?: '-' }}
                                                                </td>
                                                                <td>
                                                                    @if($nutritionCare->nutrition_care_full_note)
                                                                        <span class="text-truncate d-inline-block"
                                                                            style="max-width: 200px;"
                                                                            title="{{ $nutritionCare->nutrition_care_full_note }}">
                                                                            {{ Str::limit($nutritionCare->nutrition_care_full_note, 50) }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $nutritionCare->created_at->format('Y-m-d H:i') }}</td>
                                                                <td>
                                                                    <div class="btn-group" role="group">
                                                                        @can('view', $nutritionCare)
                                                                            <a href="{{ route('nutrition-cares.show', $nutritionCare) }}"
                                                                                class="btn btn-sm btn-info"
                                                                                title="{{ localize('global.view') }}">
                                                                                <i class="fas fa-eye"></i>
                                                                            </a>
                                                                            <a href="{{ route('nutrition-cares.print', $nutritionCare) }}"
                                                                                class="btn btn-sm btn-primary"
                                                                                title="{{ localize('global.print') }}" target="_blank">
                                                                                <i class="fas fa-print"></i>
                                                                            </a>
                                                                        @endcan
                                                                        @can('update', $nutritionCare)
                                                                            <a href="{{ route('nutrition-cares.edit', $nutritionCare) }}"
                                                                                class="btn btn-sm btn-warning"
                                                                                title="{{ localize('global.edit') }}">
                                                                                <i class="fas fa-edit"></i>
                                                                            </a>
                                                                        @endcan
                                                                        @can('delete', $nutritionCare)
                                                                            <form
                                                                                action="{{ route('nutrition-cares.destroy', $nutritionCare) }}"
                                                                                method="POST" class="d-inline"
                                                                                onsubmit="return confirm('{{ localize('global.are_you_sure_delete') }}')">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit" class="btn btn-sm btn-danger"
                                                                                    title="{{ localize('global.delete') }}">
                                                                                    <i class="fas fa-trash"></i>
                                                                                </button>
                                                                            </form>
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
                                                <div class="mb-3">
                                                    <i class="bx bx-food-menu bx-lg text-muted"></i>
                                                </div>
                                                <h5 class="text-muted">{{ localize('global.no_nutrition_care_found') }}</h5>
                                                <p class="text-muted">{{ localize('global.add_first_nutrition_care') }}</p>
                                                @can('create', \App\Models\NutritionCare::class)
                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#createNutritionCareModal">
                                                        <i class="bx bx-plus"></i>
                                                        {{ localize('global.create_nutrition_care') }}
                                                    </button>
                                                @endcan
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medication Administration Records Section Accordion -->
                <div class="col-12">
                    <div class="accordion" id="medicationAdministrationRecordsAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="medicationAdministrationRecordsHeading">
                                <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#medicationAdministrationRecordsCollapse"
                                    aria-expanded="false" aria-controls="medicationAdministrationRecordsCollapse">
                                    <i class="bx bx-capsule me-2 text-danger"></i>
                                    {{ localize('global.medication_administration_records') }}
                                    @if($medicationAdministrationRecords->count() > 0)
                                        <span
                                            class="badge bg-danger ms-2">{{ $medicationAdministrationRecords->count() }}</span>
                                    @endif
                                </button>
                            </h2>
                            <div id="medicationAdministrationRecordsCollapse" class="accordion-collapse collapse"
                                aria-labelledby="medicationAdministrationRecordsHeading"
                                data-bs-parent="#medicationAdministrationRecordsAccordion">
                                <div class="accordion-body">
                                    <div class="d-flex gap-2 mb-3">
                                        <a href="{{ route('medication-administration-records.print', ['morphable_type' => 'App\\Models\\UnderReview', 'morphable_id' => $underReview->id]) }}"
                                            class="btn btn-info" target="_blank">
                                            <i class="fas fa-print"></i> {{ localize('global.print_mars') }}
                                        </a>
                                        @can('create', App\Models\MedicationAdministrationRecord::class)
                                            <a href="{{ route('medication-administration-records.create', ['morphable_type' => 'App\\Models\\UnderReview', 'morphable_id' => $underReview->id]) }}"
                                                class="btn btn-success">
                                                <i class="bx bx-plus"></i> {{ localize('global.add_mar') }}
                                            </a>
                                        @endcan
                                    </div>

                                    @if($medicationAdministrationRecords->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>{{ localize('global.mar_id') }}</th>
                                                        <th>{{ localize('global.medicine') }}</th>
                                                        <th>{{ localize('global.nurse') }}</th>
                                                        <th>{{ localize('global.order_date') }}</th>
                                                        <th>{{ localize('global.signature_date') }}</th>
                                                        <th>{{ localize('global.administration_times') }}</th>
                                                        <th>{{ localize('global.mar_created_by') }}</th>
                                                        <th>{{ localize('global.mar_actions') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($medicationAdministrationRecords as $mar)
                                                        <tr>
                                                            <td>{{ $mar->id }}</td>
                                                            <td>
                                                                <strong>{{ $mar->medicine->name ?? 'N/A' }}</strong>
                                                            </td>
                                                            <td>{{ $mar->nurse->full_name ?? 'N/A' }}</td>
                                                            <td>
                                                                @if($mar->order_date)
                                                                    <span
                                                                        class="badge bg-info">{{ $mar->order_date->format('Y-m-d') }}</span>
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($mar->date_signature)
                                                                    <span
                                                                        class="badge bg-success">{{ $mar->date_signature->format('Y-m-d') }}</span>
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($mar->administrationTimes->count() > 0)
                                                                    <span class="badge badge-info">
                                                                        {{ $mar->administrationTimes->count() }}
                                                                        {{ localize('global.times_count') }}
                                                                    </span>
                                                                    <br>
                                                                    <small>
                                                                        @foreach($mar->administrationTimes as $time)
                                                                            {{ $time->formatted_time }}@if(!$loop->last), @endif
                                                                        @endforeach
                                                                    </small>
                                                                @else
                                                                    <span
                                                                        class="text-muted">{{ localize('global.no_times_recorded') }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $mar->createdBy->name ?? 'System' }}</td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    @can('view', $mar)
                                                                        <a href="{{ route('medication-administration-records.show', $mar) }}"
                                                                            class="btn btn-sm btn-info"
                                                                            title="{{ localize('global.mar_view') }}">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>
                                                                    @endcan
                                                                    @can('update', $mar)
                                                                        <a href="{{ route('medication-administration-records.edit', $mar) }}"
                                                                            class="btn btn-sm btn-warning"
                                                                            title="{{ localize('global.mar_edit') }}">
                                                                            <i class="fas fa-edit"></i>
                                                                        </a>
                                                                    @endcan
                                                                    @can('delete', $mar)
                                                                        <form
                                                                            action="{{ route('medication-administration-records.destroy', $mar) }}"
                                                                            method="POST" class="d-inline"
                                                                            onsubmit="return confirm('{{ localize('global.mar_confirm_delete') }}')">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="btn btn-sm btn-danger"
                                                                                title="{{ localize('global.mar_delete') }}">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </form>
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
                                            <div class="mb-3">
                                                <i class="bx bx-pills bx-lg text-muted"></i>
                                            </div>
                                            <h5 class="text-muted">{{ localize('global.no_mars_found') }}</h5>
                                            <p class="text-muted">{{ localize('global.add_first_mar') }}</p>
                                            @can('create', App\Models\MedicationAdministrationRecord::class)
                                                <a href="{{ route('medication-administration-records.create', ['morphable_type' => 'App\\Models\\UnderReview', 'morphable_id' => $underReview->id]) }}"
                                                    class="btn btn-primary">
                                                    <i class="bx bx-plus"></i> {{ localize('global.add_mar') }}
                                                </a>
                                            @endcan
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Vital Signs Section --}}
                <div class="col-12">
                    <div class="accordion" id="vitalSignsAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="vitalSignsHeading">
                                <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#vitalSignsCollapse" aria-expanded="false"
                                    aria-controls="vitalSignsCollapse">
                                    <i class="bx bx-heart me-2 text-dark"></i>
                                    {{ localize('global.vital_signs') }}
                                    @php $vitalCount = $underReview->vitalSigns?->count() ?? 0; @endphp
                                    @if($vitalCount > 0)
                                        <span class="badge bg-dark ms-2">{{ $vitalCount }}</span>
                                    @endif
                                </button>
                            </h2>
                            <div id="vitalSignsCollapse" class="accordion-collapse collapse"
                                aria-labelledby="vitalSignsHeading" data-bs-parent="#vitalSignsAccordion">
                                <div class="accordion-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            @can('create', App\Models\VitalSign::class)
                                                <a href="{{ route('vital-signs.create', ['morphable_type' => 'App\\Models\\UnderReview', 'morphable_id' => $underReview->id]) }}"
                                                    class="btn btn-primary">
                                                    <i class="bx bx-plus"></i> {{ localize('global.add_vital_sign') }}
                                                </a>
                                            @endcan
                                        </div>
                                        <div class="col-md-4 text-center">
                                            @if($vitalCount > 0)
                                                <a href="{{ route('vital-signs.print', ['App\\Models\\UnderReview', $underReview->id]) }}"
                                                    class="btn btn-info" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                    {{ localize('global.print_vital_signs_chart') }}
                                                </a>
                                            @endif
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <a href="{{ route('vital-signs.index', ['morphable_type' => 'App\\Models\\UnderReview', 'morphable_id' => $underReview->id]) }}"
                                                class="btn btn-outline-primary">
                                                <i class="bx bx-list-ul"></i>
                                                {{ localize('global.view_all_vital_signs') }}
                                            </a>
                                        </div>
                                    </div>

                                    @if($vitalCount > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>{{ localize('id') }}</th>
                                                        <th>{{ localize('vital_sign_type') }}</th>
                                                        <th>{{ localize('created_at') }}</th>
                                                        <th>{{ localize('schedules') }}</th>
                                                        <th>{{ localize('actions') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($underReview->vitalSigns->take(5) as $vitalSign)
                                                        <tr>
                                                            <td>{{ $vitalSign->id }}</td>
                                                            <td>
                                                                <span
                                                                    class="badge bg-info">{{ $vitalSign->vitalSignType->name ?? 'N/A' }}</span>
                                                            </td>
                                                            <td>{{ $vitalSign->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                                            <td>
                                                                <span class="badge bg-secondary">
                                                                    {{ $vitalSign->schedules ? $vitalSign->schedules->count() : 0 }}
                                                                    {{ localize('schedules') }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    @can('view', $vitalSign)
                                                                        <a href="{{ route('vital-signs.show', $vitalSign) }}"
                                                                            class="btn btn-info btn-sm"
                                                                            title="{{ localize('global.view') }}">
                                                                            <i class="bx bx-show"></i>
                                                                        </a>
                                                                    @endcan
                                                                    @can('create', App\Models\VitalSignSchedule::class)
                                                                        <a href="{{ route('vital-signs.show', $vitalSign) }}"
                                                                            class="btn btn-success btn-sm"
                                                                            title="{{ localize('global.add_schedule') }}">
                                                                            <i class="bx bx-time"></i>
                                                                        </a>
                                                                    @endcan
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            @if($vitalCount > 5)
                                                <div class="text-center mt-3">
                                                    <a href="{{ route('vital-signs.index', ['morphable_type' => 'App\\Models\\UnderReview', 'morphable_id' => $underReview->id]) }}"
                                                        class="btn btn-outline-primary">
                                                        {{ localize('global.view_all') }}
                                                        ({{ $vitalCount }}
                                                        {{ localize('global.vital_signs') }})
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <div class="mb-3">
                                                <i class="bx bx-heart bx-lg text-muted"></i>
                                            </div>
                                            <h5 class="text-muted">{{ localize('global.no_vital_signs_found') }}</h5>
                                            <p class="text-muted">{{ localize('global.add_first_vital_sign') }}</p>
                                            @can('create', App\Models\VitalSign::class)
                                                <a href="{{ route('vital-signs.create', ['morphable_type' => 'App\\Models\\UnderReview', 'morphable_id' => $underReview->id]) }}"
                                                    class="btn btn-primary">
                                                    <i class="bx bx-plus"></i> {{ localize('global.add_vital_sign') }}
                                                </a>
                                            @endcan
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Hospitalization Section Accordion -->
                <div class="col-12">
                    <div class="accordion" id="hospitalizationAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="hospitalizationHeading">
                                <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#hospitalizationCollapse" aria-expanded="false"
                                    aria-controls="hospitalizationCollapse">
                                    <i class="bx bx-bed me-2 text-secondary"></i>
                                    {{ localize('global.hospitalization') }}
                                    @if($underReview->hospitalization->count() > 0)
                                        <span class="badge bg-secondary ms-2">{{ $underReview->hospitalization->count() }}</span>
                                    @endif
                                </button>
                            </h2>
                            <div id="hospitalizationCollapse" class="accordion-collapse collapse"
                                aria-labelledby="hospitalizationHeading" data-bs-parent="#hospitalizationAccordion">
                                <div class="accordion-body">
                                    <div class="d-flex gap-2 mb-3">
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                            data-bs-target="#createHospitalizationModal{{ $underReview->id }}">
                                            <i class="bx bx-plus"></i> {{ localize('global.hospitalize_patient') }}
                                        </button>
                                    </div>

                                <!-- Create Hospitalization Modal -->
                                <div class="modal fade modal-xl" id="createHospitalizationModal{{ $underReview->id }}"
                                    tabindex="-1" aria-labelledby="createHospitalizationModalLabel{{ $underReview->id }}"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"
                                                    id="createHospitalizationModalLabel{{ $underReview->id }}">
                                                    {{ localize('global.hospitalize_patient') }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('hospitalizations.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" id="patient_id{{ $underReview->patient_id }}"
                                                        name="patient_id" value="{{ $underReview->patient_id }}">
                                                    <input type="hidden"
                                                        id="appointment_id{{ $underReview->appointment->id }}"
                                                        name="appointment_id" value="{{ $underReview->appointment->id }}">
                                                    <input type="hidden" id="doctor_id{{ $underReview->id }}"
                                                        name="doctor_id" value="{{ auth()->user()->id }}">
                                                    <input type="hidden" id="branch_id{{ $underReview->id }}"
                                                        name="branch_id" value="{{ auth()->user()->branch_id }}">
                                                    <input type="hidden" id="under_review_id{{ $underReview->id }}"
                                                        name="under_review_id" value="{{ $underReview->id }}">
                                                    <input type="hidden" id="is_discharged{{ $underReview->id }}"
                                                        name="is_discharged" value="0">

                                                    <div class="form-group">
                                                        <div class="form-group">
                                                            <label
                                                                for="reason{{ $underReview->id }}">{{ localize('global.reason') }}</label>
                                                            <textarea class="form-control" id="reason{{ $underReview->id }}"
                                                                name="reason" rows="3"></textarea>
                                                        </div>

                                                        <div class="form-group">
                                                            <label
                                                                for="remarks{{ $underReview->id }}">{{ localize('global.remarks') }}</label>
                                                            <textarea class="form-control"
                                                                id="remarks{{ $underReview->id }}" name="remarks"
                                                                rows="3"></textarea>
                                                        </div>

                                                        <div class="form-group">
                                                            <div class="row p-2">
                                                                <div class="col-md-4">
                                                                    <label
                                                                        for="room_id{{ $underReview->id }}">{{ localize('global.rooms') }}</label>
                                                                    <select class="form-control select2" name="room_id"
                                                                        id="room_id">
                                                                        <option value="">
                                                                            {{ localize('global.select') }}
                                                                        </option>
                                                                        @foreach ($rooms as $value)
                                                                            <option value="{{ $value->id }}" {{ old('name') == $value->id ? 'selected' : '' }}>
                                                                                {{ $value->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <label
                                                                        for="bed_id{{ $underReview->id }}">{{ localize('global.beds') }}</label>
                                                                    <select class="form-control select2" name="bed_id"
                                                                        id="bed_id">
                                                                        <option value="">
                                                                            {{ localize('global.select') }}
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
                                                                        for="food_type_id{{ $underReview->id }}">{{ localize('global.food_type') }}</label>
                                                                    <select class="form-control select2"
                                                                        name="food_type_id[]" id="food_type_id" multiple>
                                                                        <option value="">
                                                                            {{ localize('global.select') }}
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
                                                                        <select class="form-control select2"
                                                                            name="companion_card_type">
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
                                                <button type="submit"
                                                    class="btn btn-primary">{{ localize('global.save') }}</button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Create Hospitalization Modal -->

                                <div class="table-responsive">
                                    <table class="table">
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
                                            @forelse ($underReview->hospitalization as $hospitalization)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $hospitalization->reason }}</td>
                                                    <td>{{ $hospitalization->remarks }}</td>
                                                    <td>{{ $hospitalization->room->name }}</td>
                                                    <td>{{ $hospitalization->bed->number }}</td>
                                                    <td>
                                                        @if ($hospitalization->is_discharged == 0)
                                                            <span class="badge bg-danger">{{ localize('global.in_bed') }}</span>
                                                        @else
                                                            <span
                                                                class="badge bg-success">{{ localize('global.discharged') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('hospitalizations.edit', $hospitalization->id) }}">
                                                            <i class="bx bx-edit"></i>
                                                        </a>

                                                        <form id="delete-form-{{$hospitalization->id}}"
                                                            action="{{ route('hospitalizations.destroy', $hospitalization->id) }}"
                                                            method="POST" style="display: none;">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                        <a
                                                            onclick="event.preventDefault();
                                                                                                             if(confirm('{{ localize('global.are_you_sure_delete_hospitalization to delete this item?') }} ')) 
                                                                                                         { document.getElementById('delete-form-{{$hospitalization->id}}').submit(); }">
                                                            <i class="bx bx-trash text-danger"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">
                                                        <div class="badge bg-label-danger mt-4">
                                                            {{ localize('global.no_previous_hospitalizations') }}
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
                <!-- Nursing Notes Section Accordion -->
                <div class="col-12">
                    <div class="accordion" id="nursingNotesAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="nursingNotesHeading">
                                <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#nursingNotesCollapse" aria-expanded="false"
                                    aria-controls="nursingNotesCollapse">
                                    <i class="bx bx-note me-2 text-primary"></i>
                                    {{ localize('global.nursing_notes') }}
                                </button>
                            </h2>
                            <div id="nursingNotesCollapse" class="accordion-collapse collapse"
                                aria-labelledby="nursingNotesHeading" data-bs-parent="#nursingNotesAccordion">
                                <div class="accordion-body">
                                    <div id="nursing-note-section"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Nursing Assessment Section Accordion -->
                <div class="col-12">
                    <div class="accordion" id="nursingAssessmentAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="nursingAssessmentHeading">
                                <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#nursingAssessmentCollapse" aria-expanded="false"
                                    aria-controls="nursingAssessmentCollapse">
                                    <i class="bx bx-clipboard me-2 text-warning"></i>
                                    {{ localize('global.nursing_assessment') }}
                                    @if($underReview->nursingAssessments->count() > 0)
                                        <span class="badge bg-warning ms-2">{{ $underReview->nursingAssessments->count() }}</span>
                                    @endif
                                </button>
                            </h2>
                            <div id="nursingAssessmentCollapse" class="accordion-collapse collapse"
                                aria-labelledby="nursingAssessmentHeading" data-bs-parent="#nursingAssessmentAccordion">
                                <div class="accordion-body">
                                    <div id="nursing-assessment-section"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lab Test Registration Section Component -->
            <div class="row mb-4">
                <div class="col-12">
                    <x-lab-test-registration-section :entity="$underReview" entity-type="under_review"
                        :entity-id="$underReview->id" :can-add-test-registration="auth()->user()->can('register-patient-tests')"
                        :appointment-completed="false" />
                </div>
            </div>

                <!-- Discharge Section Accordion -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="accordion" id="dischargeAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="dischargeHeading">
                                    <button class="accordion-button collapsed bg-body-secondary text-body" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#dischargeCollapse" aria-expanded="false"
                                        aria-controls="dischargeCollapse">
                                        <i class="bx bx-log-out me-2 text-success"></i>
                                        {{ localize('global.discharge') }}
                                    </button>
                                </h2>
                                <div id="dischargeCollapse" class="accordion-collapse collapse"
                                    aria-labelledby="dischargeHeading" data-bs-parent="#dischargeAccordion">
                                    <div class="accordion-body">
                                        <div class="d-flex gap-2 mb-3">
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                data-bs-target="#createDischargeModal{{ $underReview->id }}">
                                                <i class="bx bx-plus"></i> {{ localize('global.discharge_patient') }}
                                            </button>
                                        </div>

                                        <!-- Create Discharge Modal -->
                                        <div class="modal fade" id="createDischargeModal{{ $underReview->id }}"
                                            tabindex="-1" aria-labelledby="createDischargeModalLabel{{ $underReview->id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="createDischargeModalLabel{{ $underReview->id }}">
                                                            {{ localize('global.discharge_patient') }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('under_reviews.update', $underReview) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" id="is_discharged{{ $underReview->id }}"
                                                                name="is_discharged" value="1">
                                                            <div class="form-group">
                                                                <label
                                                                    for="discharge_remark{{ $underReview->id }}">{{ localize('global.discharge_remark') }}</label>
                                                                <textarea class="form-control"
                                                                    id="discharge_remark{{ $underReview->id }}"
                                                                    name="discharge_remark" rows="3"></textarea>
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
                                        <!-- End Create Discharge Modal -->

                                        @if($underReview->discharge_remark)
                                            <div class="alert alert-info">
                                                <h6>{{ localize('global.discharge_remark') }}:</h6>
                                                <p>{{ $underReview->discharge_remark }}</p>
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <div class="mb-3">
                                                    <i class="bx bx-walk bx-lg text-muted"></i>
                                                </div>
                                                <h5 class="text-muted">{{ localize('global.no_discharge_remark') }}</h5>
                                                <p class="text-muted">{{ localize('global.add_discharge_remark') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Nutrition Care Modal -->
            <div class="modal fade modal-xl" id="createNutritionCareModal" tabindex="-1"
                aria-labelledby="createNutritionCareModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createNutritionCareModalLabel">
                                {{ localize('global.create_nutrition_care') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="createNutritionCareForm" action="{{ route('nutrition-cares.store') }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                @php
                                    $nurses = \App\Models\Nurse::all();
                                    $morphable_type = 'App\Models\UnderReview';
                                    $morphable_id = $underReview->id;
                                    $patient_name = $underReview->patient->first_name . ' ' . $underReview->patient->last_name;
                                @endphp
                                @include('pages.nutrition-cares.partials.form')
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                                <button type="submit" class="btn btn-primary"
                                    id="submitNutritionCareBtn">{{ localize('global.create') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- End Create Nutrition Care Modal -->


@endsection
        @section('scripts')

            <!-- Vue.js Prescription Section -->
            @vite(['public/assets/js/vue/appointment-prescription-app.js'])

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
                                checkbox.addEventListener('change', function () {
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
                    dosageInput.placeholder = '{{ localize('global.dosage') }}';

                    // Create the frequency input field
                    const frequencyInput = document.createElement('input');
                    frequencyInput.type = 'text';
                    frequencyInput.className = 'form-control mt-2';
                    frequencyInput.name = 'frequency[]';
                    frequencyInput.placeholder = '{{ localize('global.frequency') }}';

                    // Create the amount input field
                    const amountInput = document.createElement('input');
                    amountInput.type = 'text';
                    amountInput.className = 'form-control mt-2';
                    amountInput.name = 'amount[]';
                    amountInput.placeholder = '{{ localize('global.amount') }}';

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

                function getPrescriptionItems(id) {
                    $.ajax({
                        type: "GET",
                        url: "{{url('prescription_items/getItems/')}}/" + id,
                        dataType: "html",
                        success: function (data) {
                            $('#prescription_items_table').html(data);
                        },
                        error: function (xhr, status, error) {
                            // Handle the error response
                            console.error(error);
                        }
                    });
                }

                function deleteLabTest(id) {
                    if (confirm('{{ localize("global.are_you_sure_delete_lab_test") }}')) {
                        var form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("lab_tests.destroy", 0) }}'.replace(/0$/, id);

                        var tokenInput = document.createElement('input');
                        tokenInput.type = 'hidden';
                        tokenInput.name = '_token';
                        tokenInput.value = '{{ csrf_token() }}';

                        var methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'DELETE';

                        form.appendChild(tokenInput);
                        form.appendChild(methodInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                }

                // Delete Nursing Assessment function
                function deleteNursingAssessment(assessmentId) {
                    if (confirm('{{ localize("global.are_you_sure_delete_nursing_assessment") }}')) {
                        fetch(`/nursing-assessments/${assessmentId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.message) {
                                    // Show success message
                                    alert(data.message);
                                    // Reload the page to refresh the data
                                    location.reload();
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('{{ localize("global.error_deleting_nursing_assessment") }}');
                            });
                    }
                }

            </script>
            <script>
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

                // Handle Nutrition Care form submission with AJAX
                $(document).ready(function () {
                    $('#createNutritionCareForm').on('submit', function (e) {
                        e.preventDefault();

                        var form = $(this);
                        var submitBtn = $('#submitNutritionCareBtn');
                        var originalText = submitBtn.text();

                        // Disable submit button and show loading
                        submitBtn.prop('disabled', true).text('{{ localize("global.creating") }}...');

                        $.ajax({
                            type: 'POST',
                            url: form.attr('action'),
                            data: form.serialize(),
                            success: function (response) {
                                // Close modal
                                $('#createNutritionCareModal').modal('hide');

                                // Reload the nutrition care section
                                reloadNutritionCareSection();

                                // Show success message
                                toastr.success('{{ localize("global.nutrition_care_created_successfully") }}');

                                // Reset form
                                form[0].reset();
                            },
                            error: function (xhr) {
                                // Handle validation errors
                                if (xhr.status === 422) {
                                    var errors = xhr.responseJSON.errors;
                                    var errorMessages = [];

                                    for (var field in errors) {
                                        errorMessages.push(errors[field][0]);
                                    }

                                    toastr.error(errorMessages.join('<br>'));
                                } else {
                                    toastr.error('{{ localize("global.error_occurred") }}');
                                }
                            },
                            complete: function () {
                                // Re-enable submit button
                                submitBtn.prop('disabled', false).text(originalText);
                            }
                        });
                    });
                });

                // Function to reload nutrition care section
                function reloadNutritionCareSection() {
                    $.ajax({
                        type: "GET",
                        url: "{{ route('nutrition-cares.by-morphable', ['App\\Models\\UnderReview', $underReview->id]) }}",
                        dataType: "html",
                        success: function (data) {
                            // Find and replace the nutrition care section
                            var nutritionCareSection = $(data).find('#nutrition-care-section');
                            if (nutritionCareSection.length > 0) {
                                $('#nutrition-care-section').replaceWith(nutritionCareSection);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Error reloading nutrition care section:', error);
                            // Fallback: reload the entire page
                            location.reload();
                        }
                    });
                }


                $(document).ready(function () {
                    $('#nursing-assessment-section').load('{{ route('nursing-assessments.section', ['morphable_type' => 'App\\Models\\UnderReview', 'morphable_id' => $underReview->id]) }}');
                    $('#nursing-note-section').load('{{ route('nurse-notes.section', ['morphable_type' => 'App\\Models\\UnderReview', 'morphable_id' => $underReview->id]) }}');
                });

                // Ensure Vue prescription component initializes
                $(document).ready(function () {
                    // Check if Vue app is already mounted
                    const prescriptionContainer = document.getElementById('prescription-section-container');
                    if (prescriptionContainer && !prescriptionContainer.__vue_app__) {
                        // Wait a bit for the Vite script to load
                        setTimeout(function () {
                            const container = document.getElementById('prescription-section-container');
                            if (container && !container.__vue_app__) {
                                console.log('Prescription container found but Vue app not mounted. Check if appointment-prescription-app.js is loaded.');
                            }
                        }, 1000);
                    }
                });

                // Also initialize when accordion is shown
                $('#prescriptionCollapse').on('shown.bs.collapse', function () {
                    const prescriptionContainer = document.getElementById('prescription-section-container');
                    if (prescriptionContainer && !prescriptionContainer.__vue_app__) {
                        console.log('Prescription accordion opened but Vue app not mounted.');
                    }
                });
            </script>
        @endsection