<div class="col-md-12 mt-4" id="nursing-assessment-section">
    <h5 class="mb-4 p-3 bg-label-primary">
        <i class="bx bx-clipboard p-1"></i>{{ localize('global.nursing_assessment') }}
    </h5>
    <div class="d-flex gap-2 mb-3">
        @can('create', \App\Models\NursingAssessment::class)
            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                data-bs-target="#createNursingAssessmentModal">
                <i class="bx bx-plus"></i>
            </button>
        @endcan
    </div>

    @if($nursingAssessments->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ localize('global.patient_name') }}</th>
                        <th>{{ localize('global.nurse') }}</th>
                        <th>{{ localize('global.assessment_date') }}</th>
                        <th>{{ localize('global.chief_complaint') }}</th>
                        <th>{{ localize('global.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nursingAssessments as $assessment)
                        <tr>
                            <td>{{ $assessment->morphable->patient->first_name . ' ' . $assessment->morphable->patient->last_name }}</td>
                            <td>{{ $assessment->nurse->full_name ?? 'N/A' }}</td>
                            <td>{{ $assessment->assessment_initiated_by_date ? $assessment->assessment_initiated_by_date->format('Y-m-d') : 'N/A' }}
                            </td>
                            <td>{{ Str::limit($assessment->chief_complaint, 50) }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('delete', $assessment)
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="deleteNursingAssessment({{ $assessment->id }})">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    @endcan
                                    @can('view', $assessment)
                                        <a href="{{ route('nursing-assessments.print', $assessment) }}"
                                            class="btn btn-sm btn-outline-info" target="_blank">
                                            <i class="bx bx-printer"></i>
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
        <div class="alert alert-info">
            <i class="bx bx-info-circle"></i> {{ localize('global.no_nursing_assessments_found') }}
        </div>
    @endif
</div>

<!-- Create Nursing Assessment Modal -->
<div class="modal fade modal-xl" id="createNursingAssessmentModal" tabindex="-1"
    aria-labelledby="createNursingAssessmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createNursingAssessmentModalLabel">
                    {{ localize('global.create_nursing_assessment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="createNursingAssessmentForm"
                action="{{ route('nursing-assessments.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @php
                        $nurses = \App\Models\Nurse::all();
                        $morphable_type = $morphable_type;
                        $morphable_id = $morphable_id;
                        $patient_name = $morphModel->patient->first_name . ' ' . $morphModel->patient->last_name;
                    @endphp
                    @include('pages.nursing-assessments.partials.form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                    <button type="submit" class="btn btn-primary"
                        id="submitNursingAssessmentBtn">{{ localize('global.create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>