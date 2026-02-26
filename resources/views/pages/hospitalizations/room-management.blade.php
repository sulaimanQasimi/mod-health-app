@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="fw-bold mb-0">
                        <i class="bx bx-bed me-2 text-primary"></i>
                        {{ localize('global.room_management') ?: 'Room Management' }}
                    </h4>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bx bx-building me-2 text-primary"></i>
                        {{ localize('global.select_room') ?: 'Select Room' }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('hospitalizations.roomManagement') }}" id="roomSelectForm">
                        <div class="row align-items-end">
                            <div class="col-md-6">
                                <label for="room_id" class="form-label fw-semibold">{{ localize('global.room') }}</label>
                                <select class="form-select select2" name="room_id">
                                    <option value="">{{ localize('global.select') }}...</option>
                                    @foreach (\App\Models\Room::all() as $room)
                                        <option value="{{ $room->id }}" {{ $selectedRoom && $selectedRoom->id == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-show me-1"></i>{{ localize('global.show') ?: 'Show' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if ($selectedRoom)
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bx bx-door-open me-2 text-info"></i>
                            {{ localize('global.room') }}: {{ $selectedRoom->name }}
                        </h5>
                        <span class="badge bg-label-primary">{{ localize('global.bed_occupancy') ?: 'Bed occupancy' }}</span>
                    </div>
                    <div class="card-body">
                        @if ($bedsWithOccupation->isEmpty())
                            <p class="text-muted mb-0">{{ localize('global.no_beds_in_room') ?: 'No beds in this room.' }}</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.bed_number') ?: 'Bed number' }}</th>
                                            <th>{{ localize('global.patient') }}</th>
                                            <th>{{ localize('global.status') ?: 'Status' }}</th>
                                            @can('edit-hospitalizations')
                                                <th class="text-end">{{ localize('global.actions') ?: 'Actions' }}</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($bedsWithOccupation as $bed)
                                            <tr>
                                                <td class="fw-semibold">{{ $bed->number }}</td>
                                                <td>
                                                    @if ($bed->active_hospitalization && $bed->active_hospitalization->patient)
                                                        {{ $bed->active_hospitalization->patient->name }}
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($bed->active_hospitalization)
                                                        <span class="badge bg-label-warning">{{ localize('global.occupied') ?: 'Occupied' }}</span>
                                                    @else
                                                        <span class="badge bg-label-success">{{ localize('global.empty_bed') ?: 'Empty' }}</span>
                                                    @endif
                                                </td>
                                                @can('edit-hospitalizations')
                                                    <td class="text-end">
                                                        @if ($bed->active_hospitalization)
                                                            <div class="btn-group btn-group-sm">
                                                                <button type="button" class="btn btn-outline-primary"
                                                                    data-bs-toggle="modal" data-bs-target="#movePatientModal"
                                                                    data-move-action="{{ route('hospitalizations.updateRoomBed', $bed->active_hospitalization->id) }}"
                                                                    data-patient-name="{{ $bed->active_hospitalization->patient->name ?? '' }}"
                                                                    data-current-room="{{ $selectedRoom->name }}"
                                                                    data-current-bed="{{ $bed->number }}"
                                                                    data-hospitalization-id="{{ $bed->active_hospitalization->id }}">
                                                                    <i class="bx bx-transfer me-1"></i>{{ localize('global.change_room_bed') ?: 'Move to room/bed' }}
                                                                </button>
                                                                <button type="button" class="btn btn-outline-danger"
                                                                    data-bs-toggle="modal" data-bs-target="#unoccupyBedModal"
                                                                    data-action="{{ route('hospitalizations.unoccupyBed', $bed->active_hospitalization) }}"
                                                                    data-patient-name="{{ $bed->active_hospitalization->patient->name ?? '' }}">
                                                                    <i class="bx bx-log-out me-1"></i>{{ localize('global.unoccupy_bed') ?: 'Unoccupy' }}
                                                                </button>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                @endcan
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Move patient to another room/bed modal --}}
    <div class="modal fade" id="movePatientModal" tabindex="-1" aria-labelledby="movePatientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="movePatientModalLabel">
                        <i class="bx bx-transfer me-2"></i>{{ localize('global.change_room_and_bed') ?: 'Move patient to another room/bed' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="movePatientForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="return_to_room_id" id="moveReturnToRoomId" value="{{ $selectedRoom->id ?? '' }}">
                    <div class="modal-body">
                        <p class="mb-3 text-muted" id="movePatientInfo"></p>
                        <div class="mb-3">
                            <label for="move_room_id" class="form-label fw-semibold">{{ localize('global.select_room') ?: 'Select Room' }} <span class="text-danger">*</span></label>
                            <select class="form-select" name="room_id" id="move_room_id" required>
                                <option value="">{{ localize('global.select') }}...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="move_bed_id" class="form-label fw-semibold">{{ localize('global.select_bed') ?: 'Select Bed' }} <span class="text-danger">*</span></label>
                            <select class="form-select" name="bed_id" id="move_bed_id" required>
                                <option value="">{{ localize('global.select') }}...</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-check me-1"></i>{{ localize('global.update') ?: 'Update' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Unoccupy bed modal (one form, action set by JS) --}}
    <div class="modal fade" id="unoccupyBedModal" tabindex="-1" aria-labelledby="unoccupyBedModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="unoccupyBedModalLabel">
                        <i class="bx bx-log-out me-2"></i>{{ localize('global.unoccupy_bed') ?: 'Unoccupy bed' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="unoccupyBedForm" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-3 text-muted" id="unoccupyPatientName"></p>
                        <div class="mb-3">
                            <label for="discharge_status" class="form-label fw-semibold">{{ localize('global.discharge_status') }} <span class="text-danger">*</span></label>
                            <select class="form-select" name="discharge_status" id="discharge_status" required>
                                <option value="">{{ localize('global.select') }}...</option>
                                <option value="recovered">{{ localize('global.recovered') }}</option>
                                <option value="died">{{ localize('global.died') }}</option>
                                <option value="moved">{{ localize('global.moved') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="discharge_remark" class="form-label fw-semibold">{{ localize('global.discharge_remark') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="discharge_remark" id="discharge_remark" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="discharged_at_date" class="form-label fw-semibold">{{ localize('global.discharged_at') ?: 'Discharged at' }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control datepicker_dari pdp-el" name="discharged_at_date" id="discharged_at_date" required placeholder="1403/01/01" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="discharged_at_time" class="form-label fw-semibold">{{ localize('global.time') ?: 'Time' }}</label>
                            <input type="time" class="form-control" name="discharged_at_time" id="discharged_at_time" value="{{ date('H:i') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bx bx-check me-1"></i>{{ localize('global.unoccupy_bed') ?: 'Unoccupy' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script>
        (function() {
            var roomsApiUrl = '{{ url("/api/select/rooms") }}';
            var bedsApiUrl = '{{ url("/get_related_beds") }}';

            document.addEventListener('DOMContentLoaded', function() {
                var roomSelect = document.getElementById('room_id');
                if (roomSelect) {
                    roomSelect.addEventListener('change', function() {
                        document.getElementById('roomSelectForm').submit();
                    });
                }

                // Select2 AJAX for main room filter (#room_id)
                if (typeof $ !== 'undefined' && $.fn.select2) {
                    var $roomSelect = $('#room_id');
                    if ($roomSelect.length) {
                        $roomSelect.select2({
                            placeholder: '{{ localize("global.select") }}...',
                            allowClear: true,
                            width: '100%',
                            minimumInputLength: 0,
                            ajax: {
                                url: roomsApiUrl,
                                dataType: 'json',
                                delay: 250,
                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                                data: function(params) {
                                    return { search: params.term || '', page: params.page || 1 };
                                },
                                processResults: function(data) {
                                    if (data && data.results) {
                                        return { results: data.results, pagination: data.pagination || { more: false } };
                                    }
                                    return { results: [], pagination: { more: false } };
                                },
                                cache: true
                            },
                            language: {
                                noResults: function() { return '{{ localize("global.no_results_found") ?: "No results found" }}'; },
                                searching: function() { return '{{ localize("global.searching") ?: "Searching" }}...'; }
                            }
                        });
                    }

                    // Select2 AJAX for move modal room (#move_room_id)
                    var $moveRoomSelect = $('#move_room_id');
                    if ($moveRoomSelect.length) {
                        $moveRoomSelect.select2({
                            placeholder: '{{ localize("global.select_room") ?: "Select Room" }}...',
                            allowClear: true,
                            width: '100%',
                            minimumInputLength: 0,
                            dropdownParent: $('#movePatientModal'),
                            ajax: {
                                url: roomsApiUrl,
                                dataType: 'json',
                                delay: 250,
                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                                data: function(params) {
                                    return { search: params.term || '', page: params.page || 1 };
                                },
                                processResults: function(data) {
                                    if (data && data.results) {
                                        return { results: data.results, pagination: data.pagination || { more: false } };
                                    }
                                    return { results: [], pagination: { more: false } };
                                },
                                cache: true
                            },
                            language: {
                                noResults: function() { return '{{ localize("global.no_results_found") ?: "No results found" }}'; },
                                searching: function() { return '{{ localize("global.searching") ?: "Searching" }}...'; }
                            }
                        });

                        // Load empty beds when room changes (jQuery so it fires after Select2 updates)
                        $(document).on('change', '#move_room_id', function() {
                            var roomId = $(this).val();
                            var $bedSelect = $('#move_bed_id');
                            if (roomId) {
                                $bedSelect.prop('disabled', true).html('<option value="">{{ localize("global.searching") ?: "Loading" }}...</option>');
                                $.get(bedsApiUrl + '/' + roomId)
                                    .done(function(html) {
                                        $bedSelect.html(html || '<option value="">{{ localize("global.select") }}...</option>').prop('disabled', false);
                                    })
                                    .fail(function() {
                                        $bedSelect.html('<option value="">{{ localize("global.select") }}...</option>').prop('disabled', false);
                                    });
                            } else {
                                $bedSelect.html('<option value="">{{ localize("global.select") }}...</option>');
                            }
                        });
                    }
                }

                var unoccupyModal = document.getElementById('unoccupyBedModal');
            if (unoccupyModal) {
                unoccupyModal.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    var action = button.getAttribute('data-action');
                    var patientName = button.getAttribute('data-patient-name') || '';
                    var form = document.getElementById('unoccupyBedForm');
                    var nameEl = document.getElementById('unoccupyPatientName');
                    form.action = action;
                    nameEl.textContent = patientName ? ('{{ localize("global.patient") ?: "Patient" }}: ' + patientName) : '';
                    form.querySelector('#discharge_remark').value = '';
                    form.querySelector('#discharge_status').value = '';
                    form.querySelector('#discharged_at_date').value = '';
                    var now = new Date();
                    var h = String(now.getHours()).padStart(2, '0');
                    var m = String(now.getMinutes()).padStart(2, '0');
                    form.querySelector('#discharged_at_time').value = h + ':' + m;
                });
            }

            var moveModal = document.getElementById('movePatientModal');
            if (moveModal) {
                moveModal.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    var action = button.getAttribute('data-move-action');
                    var patientName = button.getAttribute('data-patient-name') || '';
                    var currentRoom = button.getAttribute('data-current-room') || '';
                    var currentBed = button.getAttribute('data-current-bed') || '';
                    var form = document.getElementById('movePatientForm');
                    var infoEl = document.getElementById('movePatientInfo');
                    form.action = action;
                    infoEl.textContent = (patientName ? ('{{ localize("global.patient") ?: "Patient" }}: ' + patientName + '. ') : '') +
                        (currentRoom || currentBed ? ('{{ localize("global.current_room") ?: "Current" }}: ' + currentRoom + ' / {{ localize("global.current_bed") ?: "Bed" }}: ' + currentBed) : '');
                    var moveRoomEl = document.getElementById('move_room_id');
                    var moveBedEl = document.getElementById('move_bed_id');
                    if (moveRoomEl) {
                        moveRoomEl.value = '';
                        if (typeof $ !== 'undefined' && $(moveRoomEl).hasClass('select2-hidden-accessible')) {
                            $(moveRoomEl).val(null).trigger('change');
                        }
                    }
                    if (moveBedEl) {
                        moveBedEl.innerHTML = '<option value="">{{ localize("global.select") }}...</option>';
                    }
                });
            }
        });
        })();
    </script>
@endpush
