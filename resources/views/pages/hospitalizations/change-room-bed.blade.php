@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-transfer me-2 text-primary"></i>
                    {{ localize('global.change_room_and_bed') ?: 'Change Room and Bed' }}
                </h5>
                <a href="{{ route('hospitalizations.show', $hospitalization->id) }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
                </a>
            </div>

            <div class="card-body">
                {{-- Current Information --}}
                <div class="alert alert-info mb-4">
                    <h6 class="alert-heading mb-2">
                        <i class="bx bx-info-circle me-2"></i>{{ localize('global.current_information') ?: 'Current Information' }}
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>{{ localize('global.patient') }}:</strong> {{ $hospitalization->patient->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-2">
                            <strong>{{ localize('global.department') }}:</strong>
                            <span class="badge bg-label-secondary">{{ $hospitalization->appointment?->department->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>{{ localize('global.current_room') }}:</strong> 
                            <span class="badge bg-label-info">{{ $hospitalization->room->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>{{ localize('global.current_bed') }}:</strong> 
                            <span class="badge bg-label-success">{{ $hospitalization->bed->number ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('hospitalizations.updateRoomBed', $hospitalization->id) }}" method="POST" id="change-room-bed-form">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="change_department" value="0" id="change_department_hidden">

                    <div class="row align-items-center mb-4 pb-3 border-bottom">
                        <div class="col-md-8">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="change_department_toggle" value="1"
                                    {{ old('change_department', '0') === '1' || old('change_department', '0') === 1 ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="change_department_toggle">
                                    {{ localize('global.transfer_to_another_department') ?: 'Transfer to another department' }}
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">
                                {{ localize('global.transfer_to_another_department_hint') ?: 'When enabled, the current admission is discharged as moved, a new appointment and hospitalization are created in the selected department, and the previous bed is freed.' }}
                            </small>
                        </div>
                    </div>

                    <div class="row" id="department_row" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label for="target_department_id" class="form-label fw-semibold">
                                <i class="bx bx-git-branch me-1 text-primary"></i>
                                {{ localize('global.select_department') ?: 'Select department' }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2" name="target_department_id" id="target_department_id">
                                <option value="">{{ localize('global.select') }}...</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ (string) old('target_department_id', $currentDepartmentId) === (string) $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('target_department_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="room_id" class="form-label fw-semibold">
                                <i class="bx bx-building me-1 text-primary"></i>
                                {{ localize('global.select_room') ?: 'Select Room' }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2" name="room_id" id="room_id" required>
                                <option value="">{{ localize('global.select') }}...</option>
                                @foreach ($rooms as $room)
                                    <option value="{{ $room->id }}" 
                                        {{ (string) old('room_id', $hospitalization->room_id) === (string) $room->id ? 'selected' : '' }}>
                                        {{ $room->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('room_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="bed_id" class="form-label fw-semibold">
                                <i class="bx bx-bed me-1 text-primary"></i>
                                {{ localize('global.select_bed') ?: 'Select Bed' }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2" name="bed_id" id="bed_id" required>
                                <option value="">{{ localize('global.select') }}...</option>
                                @if($hospitalization->room_id)
                                    {{-- Beds loaded via AJAX --}}
                                @endif
                            </select>
                            @error('bed_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-warning mt-4" id="same_dept_note">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>{{ localize('global.note') ?: 'Note' }}:</strong> 
                        {{ localize('global.changing_room_bed_note') ?: 'Changing the room and bed will automatically free the current bed and occupy the new bed.' }}
                    </div>
                    <div class="alert alert-warning mt-4 d-none" id="transfer_dept_note">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>{{ localize('global.note') ?: 'Note' }}:</strong> 
                        {{ localize('global.transfer_department_note') ?: 'The current admission will be closed as moved, a new appointment will be created for the target department, and a new active hospitalization will start in the room and bed you select.' }}
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('hospitalizations.show', $hospitalization->id) }}" class="btn btn-secondary">
                            <i class="bx bx-x me-1"></i>{{ localize('global.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-check me-1"></i>{{ localize('global.update') ?: 'Update' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custom-css')
    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-right: 20px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            right: 10px;
        }
    </style>
@endpush

@push('custom-js')
    <script>
        $(document).ready(function() {
            var $bedSelect = $('#bed_id');
            var roomsByDeptUrl = @json(route('hospitalizations.roomsByDepartment'));
            var initialRooms = @json($rooms->map(function ($r) { return ['id' => $r->id, 'name' => $r->name]; })->values());
            var currentRoomId = $('#room_id').val();
            var defaultRoomId = @json($hospitalization->room_id);
            var defaultBedId = @json($hospitalization->bed_id);
            var currentBedId = defaultBedId;
            var transferMode = false;

            function setTransferMode(on) {
                transferMode = on;
                $('#change_department_hidden').val(on ? '1' : '0');
                $('#department_row').toggle(on);
                $('#same_dept_note').toggleClass('d-none', on);
                $('#transfer_dept_note').toggleClass('d-none', !on);
                $('#target_department_id').prop('required', on);
            }

            function destroySelect2($el) {
                if (typeof $.fn.select2 !== 'undefined' && $el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
            }

            function initRoomSelect2() {
                if (typeof $.fn.select2 === 'undefined') return;
                destroySelect2($('#room_id'));
                $('#room_id').select2({
                    width: '100%',
                    placeholder: '{{ localize("global.select") }}...',
                    allowClear: true,
                    language: {
                        noResults: function() {
                            return '{{ localize("global.no_results_found") ?: "No results found" }}';
                        }
                    }
                });
            }

            function initBedSelect2() {
                if (typeof $.fn.select2 === 'undefined') return;
                destroySelect2($bedSelect);
                $bedSelect.select2({
                    width: '100%',
                    placeholder: '{{ localize("global.select") }}...',
                    allowClear: true,
                    language: {
                        noResults: function() {
                            return '{{ localize("global.no_results_found") ?: "No results found" }}';
                        }
                    }
                });
            }

            function initDeptSelect2() {
                if (typeof $.fn.select2 === 'undefined') return;
                destroySelect2($('#target_department_id'));
                $('#target_department_id').select2({
                    width: '100%',
                    placeholder: '{{ localize("global.select") }}...',
                    allowClear: true,
                    language: {
                        noResults: function() {
                            return '{{ localize("global.no_results_found") ?: "No results found" }}';
                        }
                    }
                });
            }

            function fillRoomOptions(rooms, selectedId) {
                var html = '<option value="">{{ localize("global.select") }}...</option>';
                rooms.forEach(function(r) {
                    var sel = selectedId && String(selectedId) === String(r.id) ? ' selected' : '';
                    html += '<option value="' + r.id + '"' + sel + '>' + $('<div>').text(r.name).html() + '</option>';
                });
                destroySelect2($('#room_id'));
                $('#room_id').html(html);
                initRoomSelect2();
            }

            function loadBeds(roomId, bedId) {
                if (roomId && roomId !== '') {
                    $.ajax({
                        url: '/get_related_beds/' + roomId,
                        type: 'GET',
                        data: {
                            bed_id: bedId || currentBedId
                        },
                        success: function (response) {
                            destroySelect2($bedSelect);
                            $bedSelect.html(response);
                            initBedSelect2();
                            if (bedId || currentBedId) {
                                var bedToSelect = bedId || currentBedId;
                                $bedSelect.val(bedToSelect).trigger('change');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading beds:', error);
                            destroySelect2($bedSelect);
                            $bedSelect.html('<option value="">{{ localize("global.select") }}</option>');
                            initBedSelect2();
                        }
                    });
                } else {
                    destroySelect2($bedSelect);
                    $bedSelect.html('<option value="">{{ localize("global.select") }}</option>');
                    initBedSelect2();
                }
            }

            initRoomSelect2();
            initDeptSelect2();
            initBedSelect2();

            if ($('#change_department_toggle').is(':checked')) {
                setTransferMode(true);
            } else {
                setTransferMode(false);
            }

            @if(old('change_department') == '1' && old('target_department_id'))
                loadRoomsForDepartment(@json(old('target_department_id')), @json(old('room_id')), @json(old('bed_id')));
            @else
                if (currentRoomId && currentRoomId !== '') {
                    loadBeds(currentRoomId, currentBedId);
                }
            @endif

            $('#change_department_toggle').on('change', function () {
                var on = $(this).is(':checked');
                setTransferMode(on);
                currentBedId = null;
                if (!on) {
                    fillRoomOptions(initialRooms, defaultRoomId);
                    $('#room_id').val(defaultRoomId).trigger('change');
                    loadBeds($('#room_id').val(), defaultBedId);
                } else {
                    var deptId = $('#target_department_id').val();
                    if (deptId) {
                        loadRoomsForDepartment(deptId, null, null);
                    } else {
                        fillRoomOptions([], null);
                        destroySelect2($bedSelect);
                        $bedSelect.html('<option value="">{{ localize("global.select") }}</option>');
                        initBedSelect2();
                    }
                }
            });

            function loadRoomsForDepartment(departmentId, keepRoomId, keepBedId) {
                if (!departmentId) {
                    fillRoomOptions([], null);
                    return;
                }
                $.get(roomsByDeptUrl, { department_id: departmentId })
                    .done(function (res) {
                        fillRoomOptions(res.rooms || [], keepRoomId);
                        if (keepRoomId) {
                            loadBeds(keepRoomId, keepBedId != null ? keepBedId : null);
                        } else {
                            destroySelect2($bedSelect);
                            $bedSelect.html('<option value="">{{ localize("global.select") }}</option>');
                            initBedSelect2();
                        }
                    })
                    .fail(function () {
                        fillRoomOptions([], null);
                    });
            }

            $('#target_department_id').on('change', function () {
                if (!transferMode) return;
                currentBedId = null;
                loadRoomsForDepartment($(this).val(), null, null);
            });

            $('#room_id').on('change', function () {
                var roomId = $(this).val();
                currentBedId = null;
                loadBeds(roomId, null);
            });

            $('#change-room-bed-form').on('submit', function () {
                if ($('#change_department_toggle').is(':checked')) {
                    $('#change_department_hidden').val('1');
                } else {
                    $('#change_department_hidden').val('0');
                }
            });
        });
    </script>
@endpush
