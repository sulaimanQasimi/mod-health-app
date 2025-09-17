<div class="col-md-12 mt-4">
    <h5 class="mb-4 p-3 bg-label-primary">
        <i class="bx bx-pills p-1"></i>{{ localize('global.medication_administration_records') }}
        ({{ localize('global.mar') }})
    </h5>
    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('medication-administration-records.print', ['morphable_type' => $morphableType, 'morphable_id' => $morphableId]) }}"
            class="btn btn-info" target="_blank">
            <i class="fas fa-print"></i> {{ localize('global.print_mars') }}
        </a>
        @can('create', App\Models\MedicationAdministrationRecord::class)
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createMarModal">
                <i class="bx bx-plus"></i> {{ localize('global.add_mar') }}
            </button>
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
                                    <span class="badge bg-info">{{ $mar->order_date->format('Y-m-d') }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($mar->date_signature)
                                    <span class="badge bg-success">{{ $mar->date_signature->format('Y-m-d') }}</span>
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
                                    <span class="text-muted">{{ localize('global.no_times_recorded') }}</span>
                                @endif
                            </td>
                            <td>{{ $mar->createdBy->name ?? 'System' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('view', $mar)
                                        <button type="button" class="btn btn-sm btn-info view-mar-btn"
                                            data-mar-id="{{ $mar->id }}"
                                            data-medicine-name="{{ $mar->medicine->name ?? 'N/A' }}"
                                            data-nurse-name="{{ $mar->nurse->name ?? 'N/A' }}"
                                            data-order-date="{{ $mar->order_date ? $mar->order_date->format('Y-m-d') : 'N/A' }}"
                                            data-signature-date="{{ $mar->date_signature ? $mar->date_signature->format('Y-m-d') : 'N/A' }}"
                                            data-dosage="{{ $mar->dosage ?? 'N/A' }}"
                                            data-route="{{ $mar->route ?? 'N/A' }}"
                                            data-frequency="{{ $mar->frequency ?? 'N/A' }}"
                                            data-notes="{{ $mar->notes ?? 'N/A' }}"
                                            data-created-at="{{ $mar->created_at ? $mar->created_at->format('Y-m-d H:i:s') : 'N/A' }}"
                                            data-updated-at="{{ $mar->updated_at ? $mar->updated_at->format('Y-m-d H:i:s') : 'N/A' }}"
                                            data-created-by="{{ $mar->createdBy->name ?? 'System' }}"
                                            data-administration-times="{{ json_encode($mar->administrationTimes->map(function($time) { return ['time' => $time->time ? $time->time->format('H:i') : 'N/A', 'created_by' => $time->createdBy->name ?? 'System']; })) }}"
                                            title="{{ localize('global.mar_view') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endcan
                                    @can('update', $mar)
                                        <button type="button" class="btn btn-sm btn-warning edit-mar-btn"
                                            data-mar-id="{{ $mar->id }}"
                                            data-medicine-id="{{ $mar->medicine_id }}"
                                            data-nurse-id="{{ $mar->nurse_id }}"
                                            data-order-date="{{ $mar->order_date ? $mar->order_date->format('Y-m-d') : '' }}"
                                            data-signature-date="{{ $mar->date_signature ? $mar->date_signature->format('Y-m-d') : '' }}"
                                            data-dosage="{{ $mar->dosage }}"
                                            data-route="{{ $mar->route }}"
                                            data-frequency="{{ $mar->frequency }}"
                                            data-notes="{{ $mar->notes }}"
                                            data-administration-times="{{ json_encode($mar->administrationTimes->map(function($time) { return ['time' => $time->time, 'notes' => $time->notes]; })) }}"
                                            title="{{ localize('global.mar_edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endcan
                                    @can('create', App\Models\MedicationAdministrationRecord::class)
                                        <button type="button" class="btn btn-sm btn-success set-schedule-btn"
                                            data-mar-id="{{ $mar->id }}"
                                            data-medicine-name="{{ $mar->medicine->name }}"
                                            title="{{ localize('global.set_schedule') }}">
                                            <i class="bx bx-time"></i>
                                        </button>
                                    @endcan
                                    @can('delete', $mar)
                                        <form action="{{ route('medication-administration-records.destroy', $mar) }}"
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
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMarModal">
                    <i class="bx bx-plus"></i> {{ localize('global.add_mar') }}
                </button>
            @endcan
        </div>
    @endif
</div>

<!-- Create MAR Modal -->
<div class="modal fade" id="createMarModal" tabindex="-1" aria-labelledby="createMarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createMarModalLabel">
                    {{ localize('global.add_mar') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createMarForm" method="POST" action="{{ route('medication-administration-records.store') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="morphable_type" value="{{ $morphableType }}">
                    <input type="hidden" name="morphable_id" value="{{ $morphableId }}">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="medicine_id" class="form-label">{{ localize('global.medicine') }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="medicine_id" name="medicine_id" required>
                                    <option value="">{{ localize('global.select_medicine') }}</option>
                                    @foreach(\App\Models\Medicine::all() as $medicine)
                                        <option value="{{ $medicine->id }}">{{ $medicine->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nurse_id" class="form-label">{{ localize('global.nurse') }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="nurse_id" name="nurse_id" required>
                                    <option value="">{{ localize('global.select_nurse') }}</option>
                                    @foreach(\App\Models\Nurse::active()->get() as $nurse)
                                        <option value="{{ $nurse->id }}">{{ $nurse->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="order_date" class="form-label">{{ localize('global.order_date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="order_date" name="order_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_signature" class="form-label">{{ localize('global.signature_date') }}</label>
                                <input type="date" class="form-control" id="date_signature" name="date_signature">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dosage" class="form-label">{{ localize('global.dosage') }}</label>
                                <input type="text" class="form-control" id="dosage" name="dosage" placeholder="{{ localize('global.enter_dosage') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="route" class="form-label">{{ localize('global.route') }}</label>
                                <select class="form-select" id="route" name="route">
                                    <option value="">{{ localize('global.select_route') }}</option>
                                    <option value="oral">{{ localize('global.oral') }}</option>
                                    <option value="iv">{{ localize('global.iv') }}</option>
                                    <option value="im">{{ localize('global.im') }}</option>
                                    <option value="sc">{{ localize('global.sc') }}</option>
                                    <option value="topical">{{ localize('global.topical') }}</option>
                                    <option value="inhalation">{{ localize('global.inhalation') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="frequency" class="form-label">{{ localize('global.frequency') }}</label>
                        <select class="form-select" id="frequency" name="frequency">
                            <option value="">{{ localize('global.select_frequency') }}</option>
                            <option value="once_daily">{{ localize('global.once_daily') }}</option>
                            <option value="twice_daily">{{ localize('global.twice_daily') }}</option>
                            <option value="three_times_daily">{{ localize('global.three_times_daily') }}</option>
                            <option value="four_times_daily">{{ localize('global.four_times_daily') }}</option>
                            <option value="as_needed">{{ localize('global.as_needed') }}</option>
                            <option value="every_4_hours">{{ localize('global.every_4_hours') }}</option>
                            <option value="every_6_hours">{{ localize('global.every_6_hours') }}</option>
                            <option value="every_8_hours">{{ localize('global.every_8_hours') }}</option>
                            <option value="every_12_hours">{{ localize('global.every_12_hours') }}</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ localize('global.notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="{{ localize('global.enter_notes') }}"></textarea>
                    </div>

                    <!-- Administration Times Section -->
                    <div class="mb-3">
                        <label class="form-label">{{ localize('global.administration_times') }}</label>
                        <div id="administration-times-container">
                            <div class="administration-time-row mb-2">
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="time" class="form-control" name="administration_times[]" placeholder="{{ localize('global.time') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="administration_notes[]" placeholder="{{ localize('global.notes') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-sm btn-danger remove-time-row">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-time-row">
                            <i class="bx bx-plus"></i> {{ localize('global.add_time') }}
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ localize('global.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitMarBtn">
                        {{ localize('global.create_mar') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit MAR Modal -->
<div class="modal fade" id="editMarModal" tabindex="-1" aria-labelledby="editMarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMarModalLabel">
                    {{ localize('global.edit_mar') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMarForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="mar_id" name="mar_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_medicine_id" class="form-label">{{ localize('global.medicine') }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_medicine_id" name="medicine_id" required>
                                    <option value="">{{ localize('global.select_medicine') }}</option>
                                    @foreach(\App\Models\Medicine::all() as $medicine)
                                        <option value="{{ $medicine->id }}">{{ $medicine->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_nurse_id" class="form-label">{{ localize('global.nurse') }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_nurse_id" name="nurse_id" required>
                                    <option value="">{{ localize('global.select_nurse') }}</option>
                                    @foreach(\App\Models\Nurse::active()->get() as $nurse)
                                        <option value="{{ $nurse->id }}">{{ $nurse->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_order_date" class="form-label">{{ localize('global.order_date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_order_date" name="order_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_date_signature" class="form-label">{{ localize('global.signature_date') }}</label>
                                <input type="date" class="form-control" id="edit_date_signature" name="date_signature">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_dosage" class="form-label">{{ localize('global.dosage') }}</label>
                                <input type="text" class="form-control" id="edit_dosage" name="dosage" placeholder="{{ localize('global.enter_dosage') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_route" class="form-label">{{ localize('global.route') }}</label>
                                <select class="form-select" id="edit_route" name="route">
                                    <option value="">{{ localize('global.select_route') }}</option>
                                    <option value="oral">{{ localize('global.oral') }}</option>
                                    <option value="iv">{{ localize('global.iv') }}</option>
                                    <option value="im">{{ localize('global.im') }}</option>
                                    <option value="sc">{{ localize('global.sc') }}</option>
                                    <option value="topical">{{ localize('global.topical') }}</option>
                                    <option value="inhalation">{{ localize('global.inhalation') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_frequency" class="form-label">{{ localize('global.frequency') }}</label>
                        <select class="form-select" id="edit_frequency" name="frequency">
                            <option value="">{{ localize('global.select_frequency') }}</option>
                            <option value="once_daily">{{ localize('global.once_daily') }}</option>
                            <option value="twice_daily">{{ localize('global.twice_daily') }}</option>
                            <option value="three_times_daily">{{ localize('global.three_times_daily') }}</option>
                            <option value="four_times_daily">{{ localize('global.four_times_daily') }}</option>
                            <option value="as_needed">{{ localize('global.as_needed') }}</option>
                            <option value="every_4_hours">{{ localize('global.every_4_hours') }}</option>
                            <option value="every_6_hours">{{ localize('global.every_6_hours') }}</option>
                            <option value="every_8_hours">{{ localize('global.every_8_hours') }}</option>
                            <option value="every_12_hours">{{ localize('global.every_12_hours') }}</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">{{ localize('global.notes') }}</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3" placeholder="{{ localize('global.enter_notes') }}"></textarea>
                    </div>

                    <!-- Administration Times Section -->
                    <div class="mb-3">
                        <label class="form-label">{{ localize('global.administration_times') }}</label>
                        <div id="edit-administration-times-container">
                            <!-- Times will be populated dynamically -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-edit-time-row">
                            <i class="bx bx-plus"></i> {{ localize('global.add_time') }}
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ localize('global.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitEditMarBtn">
                        {{ localize('global.update_mar') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Set Schedule Modal -->
<div class="modal fade" id="setScheduleModal" tabindex="-1" aria-labelledby="setScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="setScheduleModalLabel">
                    {{ localize('global.set_schedule') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="setScheduleForm" method="POST">
                @csrf
                <input type="hidden" id="schedule_mar_id" name="medication_administration_record_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle"></i>
                        {{ localize('global.setting_schedule_for') }}: <strong class="medicine-name"></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ localize('global.schedule_times') }}</label>
                        <div id="schedule-times-container">
                            <!-- Schedule times will be populated dynamically -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-schedule-time-row">
                            <i class="bx bx-plus"></i> {{ localize('global.add_schedule_time') }}
                        </button>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ localize('global.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-success" id="submitScheduleBtn">
                        {{ localize('global.save_schedule') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View MAR Modal -->
<div class="modal fade" id="viewMarModal" tabindex="-1" aria-labelledby="viewMarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewMarModalLabel">
                    <i class="bx bx-show"></i> {{ localize('global.view_mar_details') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ localize('global.medicine') }}</label>
                            <p class="form-control-plaintext" id="view-medicine-name">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ localize('global.nurse') }}</label>
                            <p class="form-control-plaintext" id="view-nurse-name">-</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ localize('global.order_date') }}</label>
                            <p class="form-control-plaintext" id="view-order-date">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ localize('global.signature_date') }}</label>
                            <p class="form-control-plaintext" id="view-signature-date">-</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ localize('global.dosage') }}</label>
                            <p class="form-control-plaintext" id="view-dosage">-</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ localize('global.route') }}</label>
                            <p class="form-control-plaintext" id="view-route">-</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ localize('global.frequency') }}</label>
                            <p class="form-control-plaintext" id="view-frequency">-</p>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">{{ localize('global.notes') }}</label>
                    <p class="form-control-plaintext" id="view-notes">-</p>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">{{ localize('global.administration_times') }}</label>
                    <div id="view-administration-times">
                        <p class="text-muted">{{ localize('global.no_administration_times') }}</p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">{{ localize('global.created_by') }}</label>
                    <p class="form-control-plaintext" id="view-created-by">-</p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ localize('global.created_at') }}</label>
                            <p class="form-control-plaintext" id="view-created-at">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ localize('global.updated_at') }}</label>
                            <p class="form-control-plaintext" id="view-updated-at">-</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ localize('global.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Add time row functionality
    $('#add-time-row').click(function() {
        var timeRow = `
            <div class="administration-time-row mb-2">
                <div class="row">
                    <div class="col-md-4">
                        <input type="time" class="form-control" name="administration_times[]" placeholder="{{ localize('global.time') }}">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="administration_notes[]" placeholder="{{ localize('global.notes') }}">
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-sm btn-danger remove-time-row">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#administration-times-container').append(timeRow);
    });

    // Remove time row functionality
    $(document).on('click', '.remove-time-row', function() {
        $(this).closest('.administration-time-row').remove();
    });

    // Handle form submission with AJAX
    $('#createMarForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = $('#submitMarBtn');
        var originalText = submitBtn.text();
        
        // Disable submit button and show loading
        submitBtn.prop('disabled', true).text('{{ localize("global.creating") }}...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                // Close modal
                $('#createMarModal').modal('hide');
                
                // Reset form
                form[0].reset();
                
                // Reload the section
                $('#medication-administration-records-section').load('{{ route("hospitalizations.medication-administration-records-section", ["morphable_type" => $morphableType, "morphable_id" => $morphableId]) }}');
                
                // Show success message
                if (typeof showToast === 'function') {
                    showToast('success', '{{ localize("global.mar_created_successfully") }}');
                } else {
                    alert('{{ localize("global.mar_created_successfully") }}');
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                var errorMessage = '{{ localize("global.error_creating_mar") }}';
                
                if (Object.keys(errors).length > 0) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                
                if (typeof showToast === 'function') {
                    showToast('error', errorMessage);
                } else {
                    alert(errorMessage);
                }
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Edit MAR functionality
    $('.edit-mar-btn').click(function() {
        var marId = $(this).data('mar-id');
        var medicineId = $(this).data('medicine-id');
        var nurseId = $(this).data('nurse-id');
        var orderDate = $(this).data('order-date');
        var signatureDate = $(this).data('signature-date');
        var dosage = $(this).data('dosage');
        var route = $(this).data('route');
        var frequency = $(this).data('frequency');
        var notes = $(this).data('notes');
        var administrationTimes = $(this).data('administration-times');

        // Set the form action URL
        $('#editMarForm').attr('action', '{{ route("medication-administration-records.update", ":id") }}'.replace(':id', marId));

        // Populate the edit form
        $('#editMarModal #mar_id').val(marId);
        $('#editMarModal #edit_medicine_id').val(medicineId);
        $('#editMarModal #edit_nurse_id').val(nurseId);
        $('#editMarModal #edit_order_date').val(orderDate);
        $('#editMarModal #edit_date_signature').val(signatureDate);
        $('#editMarModal #edit_dosage').val(dosage);
        $('#editMarModal #edit_route').val(route);
        $('#editMarModal #edit_frequency').val(frequency);
        $('#editMarModal #edit_notes').val(notes);

        // Clear existing administration times
        $('#edit-administration-times-container').empty();

        // Add administration times
        if (administrationTimes && administrationTimes.length > 0) {
            administrationTimes.forEach(function(timeData) {
                addEditTimeRow(timeData.time, timeData.notes);
            });
        } else {
            addEditTimeRow();
        }

        // Show the modal
        $('#editMarModal').modal('show');
    });

    // Set Schedule functionality
    $('.set-schedule-btn').click(function() {
        var marId = $(this).data('mar-id');
        var medicineName = $(this).data('medicine-name');

        // Set the form action URL
        $('#setScheduleForm').attr('action', '{{ route("medication-administration-records.add-time", ":id") }}'.replace(':id', marId));

        // Populate the schedule form
        $('#setScheduleModal #schedule_mar_id').val(marId);
        $('#setScheduleModal .medicine-name').text(medicineName);

        // Clear existing schedule times
        $('#schedule-times-container').empty();
        addScheduleTimeRow();

        // Show the modal
        $('#setScheduleModal').modal('show');
    });

    // View MAR functionality
    $('.view-mar-btn').click(function() {
        var marId = $(this).data('mar-id');
        var medicineName = $(this).data('medicine-name');
        var nurseName = $(this).data('nurse-name');
        var orderDate = $(this).data('order-date');
        var signatureDate = $(this).data('signature-date');
        var dosage = $(this).data('dosage');
        var route = $(this).data('route');
        var frequency = $(this).data('frequency');
        var notes = $(this).data('notes');
        var createdAt = $(this).data('created-at');
        var updatedAt = $(this).data('updated-at');
        var createdBy = $(this).data('created-by');
        var administrationTimes = $(this).data('administration-times');

        // Populate modal fields
        $('#view-medicine-name').text(medicineName);
        $('#view-nurse-name').text(nurseName);
        $('#view-order-date').text(orderDate);
        $('#view-signature-date').text(signatureDate);
        $('#view-dosage').text(dosage);
        $('#view-route').text(route);
        $('#view-frequency').text(frequency);
        $('#view-notes').text(notes);
        $('#view-created-at').text(createdAt);
        $('#view-updated-at').text(updatedAt);
        $('#view-created-by').text(createdBy);

        // Populate administration times
        var timesContainer = $('#view-administration-times');
        if (administrationTimes && administrationTimes.length > 0) {
            var timesHtml = '<div class="list-group">';
            administrationTimes.forEach(function(timeObj) {
                timesHtml += '<div class="list-group-item d-flex justify-content-between align-items-center">';
                timesHtml += '<div>';
                timesHtml += '<span><i class="bx bx-time"></i> ' + timeObj.time + '</span>';
                timesHtml += '</div>';
                timesHtml += '<div class="text-muted small">';
                timesHtml += '<i class="bx bx-user"></i> ' + (timeObj.created_by || 'System');
                timesHtml += '</div>';
                timesHtml += '</div>';
            });
            timesHtml += '</div>';
            timesContainer.html(timesHtml);
        } else {
            timesContainer.html('<p class="text-muted">{{ localize("global.no_administration_times") }}</p>');
        }

        // Show modal
        $('#viewMarModal').modal('show');
    });

    // Add edit time row function
    function addEditTimeRow(time = '', notes = '') {
        var timeRow = `
            <div class="administration-time-row mb-2">
                <div class="row">
                    <div class="col-md-4">
                        <input type="time" class="form-control" name="edit_administration_times[]" value="${time}" placeholder="{{ localize('global.time') }}">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="edit_administration_notes[]" value="${notes}" placeholder="{{ localize('global.notes') }}">
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-sm btn-danger remove-edit-time-row">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#edit-administration-times-container').append(timeRow);
    }

    // Add schedule time row function
    function addScheduleTimeRow() {
        var timeRow = `
            <div class="schedule-time-row mb-2">
                <div class="row">
                    <div class="col-md-8">
                        <input type="time" class="form-control" name="schedule_times[]" required>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-sm btn-danger remove-schedule-time-row">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#schedule-times-container').append(timeRow);
    }

    // Remove edit time row functionality
    $(document).on('click', '.remove-edit-time-row', function() {
        $(this).closest('.administration-time-row').remove();
    });

    // Remove schedule time row functionality
    $(document).on('click', '.remove-schedule-time-row', function() {
        $(this).closest('.schedule-time-row').remove();
    });

    // Add edit time row button
    $('#add-edit-time-row').click(function() {
        addEditTimeRow();
    });

    // Add schedule time row button
    $('#add-schedule-time-row').click(function() {
        addScheduleTimeRow();
    });

    // Handle edit form submission with AJAX
    $('#editMarForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var marId = $('#mar_id').val();
        var submitBtn = $('#submitEditMarBtn');
        var originalText = submitBtn.text();
        
        // Disable submit button and show loading
        submitBtn.prop('disabled', true).text('{{ localize("global.updating") }}...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                // Close modal
                $('#editMarModal').modal('hide');
                
                // Reload the section
                $('#medication-administration-records-section').load('{{ route("hospitalizations.medication-administration-records-section", ["morphable_type" => $morphableType, "morphable_id" => $morphableId]) }}');
                
                // Show success message
                if (typeof showToast === 'function') {
                    showToast('success', '{{ localize("global.mar_updated_successfully") }}');
                } else {
                    alert('{{ localize("global.mar_updated_successfully") }}');
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                var errorMessage = '{{ localize("global.error_updating_mar") }}';
                
                if (Object.keys(errors).length > 0) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                
                if (typeof showToast === 'function') {
                    showToast('error', errorMessage);
                } else {
                    alert(errorMessage);
                }
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Handle schedule form submission with AJAX
    $('#setScheduleForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = $('#submitScheduleBtn');
        var originalText = submitBtn.text();
        var marId = $('#schedule_mar_id').val();
        
        // Get all schedule times
        var scheduleTimes = [];
        $('.schedule-time-row').each(function() {
            var time = $(this).find('input[name="schedule_times[]"]').val();
            
            if (time) {
                scheduleTimes.push({
                    time: time
                });
            }
        });
        
        if (scheduleTimes.length === 0) {
            alert('{{ localize("global.please_add_at_least_one_schedule_time") }}');
            return;
        }
        
        // Disable submit button and show loading
        submitBtn.prop('disabled', true).text('{{ localize("global.saving") }}...');
        
        // Submit each time individually
        var completedRequests = 0;
        var totalRequests = scheduleTimes.length;
        var hasError = false;
        
        scheduleTimes.forEach(function(scheduleTime) {
            $.ajax({
                url: '{{ route("medication-administration-records.add-time", ":id") }}'.replace(':id', marId),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    time: scheduleTime.time
                },
                success: function(response) {
                    completedRequests++;
                    if (completedRequests === totalRequests && !hasError) {
                        // All requests completed successfully
                        $('#setScheduleModal').modal('hide');
                        
                        // Reload the section
                        $('#medication-administration-records-section').load('{{ route("hospitalizations.medication-administration-records-section", ["morphable_type" => $morphableType, "morphable_id" => $morphableId]) }}');
                        
                        // Show success message
                        if (typeof showToast === 'function') {
                            showToast('success', '{{ localize("global.schedule_saved_successfully") }}');
                        } else {
                            alert('{{ localize("global.schedule_saved_successfully") }}');
                        }
                        
                        // Re-enable submit button
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                },
                error: function(xhr) {
                    hasError = true;
                    var errors = xhr.responseJSON?.errors || {};
                    var errorMessage = '{{ localize("global.error_saving_schedule") }}';
                    
                    if (Object.keys(errors).length > 0) {
                        errorMessage = Object.values(errors).flat().join('\n');
                    }
                    
                    if (typeof showToast === 'function') {
                        showToast('error', errorMessage);
                    } else {
                        alert(errorMessage);
                    }
                    
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });
    });
});
</script>
