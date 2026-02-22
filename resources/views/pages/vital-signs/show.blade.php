@extends('layouts.master')

@section('title', localize('global.vital_sign') . ' - ' . localize('global.details'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-heartbeat"></i> {{ localize('global.vital_sign') }} -
                            {{ localize('global.details') }}
                        </h3>
                        <div class="card-tools">
                            @if($vitalSign->morphable)
                                <a href="{{ $vitalSign->morphable_type == 'App\\Models\\Hospitalization' ? route('hospitalizations.show', $vitalSign->morphable) : route('under_reviews.show', $vitalSign->morphable) }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> {{ localize('global.back') }} ({{ class_basename($vitalSign->morphable_type) }})
                                </a>
                            @endif
                            <a href="{{ route('vital-signs.index', $vitalSign->morphable ? ['morphable_type' => $vitalSign->morphable_type, 'morphable_id' => $vitalSign->morphable_id] : []) }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-list"></i> {{ localize('global.view_all_vital_signs') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">{{ localize('global.id') }}:</th>
                                        <td>{{ $vitalSign->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ localize('global.vital_sign_type') }}:</th>
                                        <td>
                                            <span
                                                class="badge bg-info">{{ $vitalSign->vitalSignType->name ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ localize('global.morphable_type') }}:</th>
                                        <td>
                                            <span
                                                class="badge bg-primary">{{ class_basename($vitalSign->morphable_type) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ localize('global.morphable_id') }}:</th>
                                        <td>{{ $vitalSign->morphable_id }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ localize('global.related_record') }}:</th>
                                        <td>
                                            @if($vitalSign->morphable)
                                                <a href="{{ $vitalSign->morphable_type == 'App\\Models\\Hospitalization' ? route('hospitalizations.show', $vitalSign->morphable) : route('under_reviews.show', $vitalSign->morphable) }}"
                                                    class="btn btn-outline-primary btn-sm">
                                                    {{ class_basename($vitalSign->morphable_type) }}
                                                    #{{ $vitalSign->morphable_id }}
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ localize('global.created_at') }}:</th>
                                        <td>{{ verta($vitalSign->created_at)->format('Y/m/d H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ localize('global.updated_at') }}:</th>
                                        <td>{{ verta($vitalSign->updated_at)->format('Y/m/d H:i') }}</td>
                                    </tr>
                                    @if($vitalSign->createdBy)
                                        <tr>
                                            <th>{{ localize('global.created_by') }}:</th>
                                            <td>{{ $vitalSign->createdBy->name }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>

                        <!-- Associated Schedules -->
                        @if($vitalSign->schedules->count() > 0)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>{{ localize('global.associated') }} {{ localize('global.schedules') }}
                                        ({{ $vitalSign->schedules->count() }})</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>{{ localize('global.id') }}</th>
                                                    <th>{{ localize('global.day') }}</th>
                                                    <th>{{ localize('global.date') }}</th>
                                                    <th>{{ localize('global.morning_time') }}</th>
                                                    <th>{{ localize('global.evening_time') }}</th>
                                                    <th>{{ localize('global.nurse') }}</th>
                                                    <th>{{ localize('global.created_at') }}</th>
                                                    <th>{{ localize('global.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($vitalSign->schedules as $schedule)
                                                    <tr>
                                                        <td>{{ $schedule->id }}</td>
                                                        <td>{{ $schedule->day ?? 'N/A' }}</td>
                                                        <td>{{ $schedule->date ? verta($schedule->date)->format('Y/m/d') : 'N/A' }}</td>
                                                        <td>{{ $schedule->morning_time ?? 'N/A' }}</td>
                                                        <td>{{ $schedule->evening_time ?? 'N/A' }}</td>
                                                        <td>{{ $schedule->nurse->full_name ?? 'N/A' }}</td>
                                                        <td>{{ verta($schedule->created_at)->format('Y/m/d H:i') }}</td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                @can('update', $schedule)
                                                                    <button type="button" class="btn btn-warning btn-sm"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#updateScheduleModal{{ $schedule->id }}"
                                                                        title="{{ localize('global.edit') }}">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                @endcan
                                                                @can('delete', $schedule)
                                                                    <form
                                                                        action="{{ route('vital-sign-schedules.destroy', $schedule) }}"
                                                                        method="POST" class="d-inline"
                                                                        onsubmit="return confirm('{{ localize('global.confirm_delete') }} {{ localize('global.vital_sign_schedule') }}?')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger btn-sm"
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
                                </div>
                            </div>
                        @else
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="text-center py-4">
                                        <div class="mb-3">
                                            <i class="bx bx-time bx-lg text-muted"></i>
                                        </div>
                                        <h5 class="text-muted">{{ localize('global.no_schedules_found') }}</h5>
                                        <p class="text-muted">{{ localize('global.add_first_schedule') }}</p>
                                        @can('create', App\Models\VitalSignSchedule::class)
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#createScheduleModal">
                                                <i class="bx bx-plus"></i> {{ localize('global.add_schedule') }}
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                @can('view', $vitalSign)
                                    <a href="{{ route('vital-signs.print', ['morphable_type' => $vitalSign->morphable_type, 'morphable_id' => $vitalSign->morphable_id]) }}" class="btn btn-info" target="_blank">
                                        <i class="fas fa-print"></i> {{ localize('global.print_chart') }}
                                    </a>
                                @endcan
                                @can('create', App\Models\VitalSignSchedule::class)
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                        data-bs-target="#createScheduleModal">
                                        <i class="fas fa-plus"></i> {{ localize('global.add_schedule') }}
                                    </button>
                                @endcan
                                @can('update', $vitalSign)
                                    <a href="{{ route('vital-signs.edit', $vitalSign) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> {{ localize('global.edit') }}
                                    </a>
                                @endcan
                                @can('delete', $vitalSign)
                                    <form action="{{ route('vital-signs.destroy', $vitalSign) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('{{ localize('global.confirm_delete') }} {{ localize('global.vital_sign') }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> {{ localize('global.delete') }}
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Create Schedule Modal -->
    @can('create', App\Models\VitalSignSchedule::class)
        <div class="modal fade" id="createScheduleModal" tabindex="-1" aria-labelledby="createScheduleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createScheduleModalLabel">
                            <i class="fas fa-plus"></i> {{ localize('global.create_vital_sign_schedule') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('vital-sign-schedules.store') }}" method="POST" id="createScheduleForm">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="modal_vital_sign_id">{{ localize('global.vital_sign') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="hidden" name="vital_sign_id" value="{{ $vitalSign->id }}">
                                        <div class="form-control-plaintext bg-light p-2 rounded">
                                            <i class="fas fa-heartbeat text-primary"></i>
                                            {{ $vitalSign->vitalSignType->name ?? 'N/A' }} -
                                            {{ class_basename($vitalSign->morphable_type) }} #{{ $vitalSign->morphable_id }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>{{ localize('global.responsible_nurse') }}</label>
                                        @if($currentUserNurse)
                                            <input type="hidden" name="nurse_id" value="{{ $currentUserNurse->id }}">
                                            <div class="form-control-plaintext bg-light p-2 rounded">
                                                <i class="fas fa-user-nurse text-primary"></i>
                                                {{ $currentUserNurse->full_name }}
                                                <small
                                                    class="text-muted d-block">{{ localize('global.automatically_selected') }}</small>
                                            </div>
                                        @else
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ localize('global.no_nurse_profile_found') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>{{ localize('global.day') }}</label>
                                        <div class="form-control-plaintext bg-light p-2 rounded">
                                            <i class="fas fa-calendar-day text-primary"></i>
                                            <span id="autoDayNumber">{{ localize('global.auto_generated') }}</span>
                                            <small class="text-muted d-block">{{ localize('global.next_day_number') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="modal_date">{{ localize('global.date') }}</label>
                                        <input type="text" class="form-control datepicker_dari @error('date') is-invalid @enderror"
                                            id="modal_date" name="date" value="{{ old('date') }}"
                                            placeholder="1403/01/01" autocomplete="off">
                                        @error('date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="modal_morning_time">{{ localize('global.morning_time') }}</label>
                                        <input type="text" class="form-control @error('morning_time') is-invalid @enderror"
                                            id="modal_morning_time" name="morning_time" value="{{ old('morning_time') }}"
                                            placeholder="{{ localize('global.enter_morning_time') }}">
                                        @error('morning_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="modal_evening_time">{{ localize('global.evening_time') }}</label>
                                        <input type="text" class="form-control @error('evening_time') is-invalid @enderror"
                                            id="modal_evening_time" name="evening_time" value="{{ old('evening_time') }}"
                                            placeholder="{{ localize('global.enter_evening_time') }}">
                                        @error('evening_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> {{ localize('global.cancel') }}
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ localize('global.create_schedule') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    <!-- Update Modals for each schedule -->
    @foreach($vitalSign->schedules as $schedule)
        <!-- Update Schedule Modal -->
        @can('update', $schedule)
            <div class="modal fade" id="updateScheduleModal{{ $schedule->id }}" tabindex="-1"
                aria-labelledby="updateScheduleModalLabel{{ $schedule->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateScheduleModalLabel{{ $schedule->id }}">
                                <i class="fas fa-edit"></i> {{ localize('global.edit_vital_sign_schedule') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('vital-sign-schedules.update', $schedule) }}" method="POST"
                            id="updateScheduleForm{{ $schedule->id }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <!-- Read-only information -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label>{{ localize('global.vital_sign') }}</label>
                                            <div class="form-control-plaintext bg-light p-2 rounded">
                                                <i class="fas fa-heartbeat text-primary"></i>
                                                {{ $schedule->vitalSign->vitalSignType->name ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label>{{ localize('global.day') }}</label>
                                            <div class="form-control-plaintext bg-light p-2 rounded">
                                                <i class="fas fa-calendar-day text-primary"></i>
                                                {{ $schedule->day ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label>{{ localize('global.responsible_nurse') }}</label>
                                            <div class="form-control-plaintext bg-light p-2 rounded">
                                                <i class="fas fa-user-nurse text-primary"></i>
                                                {{ $schedule->nurse->full_name ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Editable fields -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label
                                                for="update_morning_time{{ $schedule->id }}">{{ localize('global.morning_time') }}</label>
                                            <input type="text" class="form-control @error('morning_time') is-invalid @enderror"
                                                id="update_morning_time{{ $schedule->id }}" name="morning_time"
                                                value="{{ old('morning_time', $schedule->morning_time ?? '') }}"
                                                placeholder="{{ localize('global.enter_morning_time') }}">
                                            @error('morning_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label
                                                for="update_evening_time{{ $schedule->id }}">{{ localize('global.evening_time') }}</label>
                                            <input type="text" class="form-control @error('evening_time') is-invalid @enderror"
                                                id="update_evening_time{{ $schedule->id }}" name="evening_time"
                                                value="{{ old('evening_time', $schedule->evening_time ?? '') }}"
                                                placeholder="{{ localize('global.enter_evening_time') }}">
                                            @error('evening_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Hidden fields to preserve data -->
                                <input type="hidden" name="vital_sign_id" value="{{ $schedule->vital_sign_id }}">
                                <input type="hidden" name="day" value="{{ $schedule->day }}">
                                <input type="hidden" name="date"
                                    value="{{ $schedule->date ? $schedule->date->format('Y-m-d') : '' }}">
                                <input type="hidden" name="nurse_id" value="{{ $schedule->nurse_id }}">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times"></i> {{ localize('global.cancel') }}
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ localize('global.update') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    @endforeach

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Handle Create Schedule Form
                const createScheduleForm = document.getElementById('createScheduleForm');
                const createScheduleModal = document.getElementById('createScheduleModal');

                // Update day number when modal opens
                if (createScheduleModal) {
                    createScheduleModal.addEventListener('show.bs.modal', function () {
                        updateNextDayNumber();
                    });
                }

                if (createScheduleForm) {
                    createScheduleForm.addEventListener('submit', function (e) {
                        e.preventDefault();
                        handleFormSubmission(this, createScheduleModal, '{{ localize("global.creating") }}...', '{{ localize("global.vital_sign_schedule_created_successfully") }}', '{{ localize("global.error_creating_schedule") }}');
                    });
                }

                // Handle Update Schedule Forms
                const updateScheduleForms = document.querySelectorAll('[id^="updateScheduleForm"]');
                updateScheduleForms.forEach(form => {
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        const modalId = this.id.replace('updateScheduleForm', 'updateScheduleModal');
                        const updateModal = document.getElementById(modalId);
                        handleFormSubmission(this, updateModal, '{{ localize("global.updating") }}...', '{{ localize("global.vital_sign_schedule_updated_successfully") }}', '{{ localize("global.error_updating_schedule") }}');
                    });
                });

                function handleFormSubmission(form, modal, loadingText, successMessage, errorMessage) {
                    const formData = new FormData(form);
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalText = submitButton.innerHTML;

                    // Disable submit button and show loading
                    submitButton.disabled = true;
                    submitButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${loadingText}`;

                    fetch(form.action, {
                        method: form.method,
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                        .then(response => {
                            if (response.ok) {
                                // Close modal (Bootstrap 5 or jQuery fallback)
                                try {
                                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal && typeof bootstrap.Modal.getInstance === 'function') {
                                        const modalInstance = bootstrap.Modal.getInstance(modal);
                                        if (modalInstance) modalInstance.hide();
                                    } else if (typeof window.$ !== 'undefined' && window.$.fn.modal) {
                                        window.$(modal).modal('hide');
                                    } else {
                                        modal.classList.remove('show');
                                        modal.style.display = 'none';
                                        document.body.classList.remove('modal-open');
                                        const backdrop = document.querySelector('.modal-backdrop');
                                        if (backdrop) backdrop.remove();
                                    }
                                } catch (e) {
                                    if (typeof window.$ !== 'undefined' && window.$.fn.modal) {
                                        window.$(modal).modal('hide');
                                    } else {
                                        modal.classList.remove('show');
                                        modal.style.display = 'none';
                                        document.body.classList.remove('modal-open');
                                    }
                                }

                                // Show success message
                                showAlert('success', successMessage);

                                // Redirect to vital-sign show page to show updated data
                                setTimeout(() => {
                                    window.location.href = '{{ route("vital-signs.show", $vitalSign) }}';
                                }, 1000);
                            } else {
                                throw new Error('Network response was not ok');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showAlert('error', errorMessage);
                        })
                        .finally(() => {
                            // Re-enable submit button
                            submitButton.disabled = false;
                            submitButton.innerHTML = originalText;
                        });
                }

                function updateNextDayNumber() {
                    // Get existing day numbers from the table
                    const existingDays = [];
                    const dayCells = document.querySelectorAll('tbody tr td:nth-child(2)'); // Day column
                    dayCells.forEach(cell => {
                        const dayText = cell.textContent.trim();
                        if (dayText && dayText.startsWith('Day ')) {
                            const dayNumber = parseInt(dayText.replace('Day ', ''));
                            if (!isNaN(dayNumber)) {
                                existingDays.push(dayNumber);
                            }
                        }
                    });

                    // Find the next available day number
                    let nextDayNumber = 1;
                    while (existingDays.includes(nextDayNumber)) {
                        nextDayNumber++;
                    }

                    // Update the display
                    const autoDayElement = document.getElementById('autoDayNumber');
                    if (autoDayElement) {
                        autoDayElement.textContent = `Day ${nextDayNumber}`;
                    }
                }

                function showAlert(type, message) {
                    // Create alert element
                    const alertDiv = document.createElement('div');
                    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
                    alertDiv.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;

                    // Insert at top of page
                    const container = document.querySelector('.container-fluid');
                    container.insertBefore(alertDiv, container.firstChild);

                    // Auto-dismiss after 5 seconds
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 5000);
                }
            });
        </script>
    @endpush

@endsection