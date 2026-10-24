@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bx bx-building-house me-2 text-primary"></i>
                        {{ localize('global.room_management') ?: 'Room Management' }}
                    </h4>
                    <p class="text-muted mb-0">
                        @if ($selectedRoom)
                            {{ $selectedRoom->name }}
                            @if ($selectedRoom->department?->name)
                                · {{ $selectedRoom->department->name }}
                            @endif
                        @else
                            {{ localize('global.rooms') }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('hospitalizations.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
                </a>
            </div>

            @if ($rooms->isNotEmpty())
                <div class="row g-3 mb-4">
                    <div class="col-6 col-lg">
                        <div class="card h-100 border-secondary border-opacity-25">
                            <div class="card-body d-flex align-items-center gap-3">
                                <span class="avatar avatar-md bg-secondary rounded">
                                    <i class="bx bx-building-house text-white"></i>
                                </span>
                                <div>
                                    <div class="text-muted small">{{ localize('global.rooms') }}</div>
                                    <div class="fw-bold fs-4 mb-0">{{ $overview['rooms_count'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg">
                        <div class="card h-100 border-primary border-opacity-25">
                            <div class="card-body d-flex align-items-center gap-3">
                                <span class="avatar avatar-md bg-primary rounded">
                                    <i class="bx bx-grid-alt text-white"></i>
                                </span>
                                <div>
                                    <div class="text-muted small">{{ localize('global.beds') ?: 'Beds' }}</div>
                                    <div class="fw-bold fs-4 mb-0 text-primary">{{ $overview['beds_count'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg">
                        <div class="card h-100 border-warning border-opacity-25">
                            <div class="card-body d-flex align-items-center gap-3">
                                <span class="avatar avatar-md bg-warning rounded">
                                    <i class="bx bx-user-pin text-white"></i>
                                </span>
                                <div>
                                    <div class="text-muted small">{{ localize('global.occupied') ?: 'Occupied' }}</div>
                                    <div class="fw-bold fs-4 mb-0 text-warning">{{ $overview['occupied_beds_count'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg">
                        <div class="card h-100 border-success border-opacity-25">
                            <div class="card-body d-flex align-items-center gap-3">
                                <span class="avatar avatar-md bg-success rounded">
                                    <i class="bx bx-check-circle text-white"></i>
                                </span>
                                <div>
                                    <div class="text-muted small">{{ localize('global.empty_bed') ?: 'Empty' }}</div>
                                    <div class="fw-bold fs-4 mb-0 text-success">{{ $overview['empty_beds_count'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg">
                        <div class="card h-100 border-info border-opacity-25">
                            <div class="card-body d-flex align-items-center gap-3">
                                <span class="avatar avatar-md bg-info rounded">
                                    <i class="bx bx-pie-chart-alt-2 text-white"></i>
                                </span>
                                <div>
                                    <div class="text-muted small">{{ localize('global.bed_occupancy') ?: 'Bed occupancy' }}</div>
                                    <div class="fw-bold fs-4 mb-0 text-info">{{ $overview['occupancy_rate'] }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($rooms->isEmpty())
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        {{ localize('global.no_records_found') }}
                    </div>
                </div>
            @else
                <div class="row g-4">
                    <div class="col-md-4 col-xl-3">
                        <div class="card room-list-card sticky-lg-top" style="top: 1rem;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="bx bx-building-house me-1 text-primary"></i>
                                    {{ localize('global.rooms') }}
                                </h6>
                                <span class="badge bg-label-secondary" id="roomListCount">{{ $rooms->count() }} / {{ $rooms->count() }}</span>
                            </div>
                            <div class="card-body border-bottom py-3">
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                                    <input type="search" class="form-control" id="roomSearchInput"
                                           placeholder="{{ localize('global.search') }}...">
                                </div>
                            </div>
                            <div class="card-body p-3 room-list-scroll" style="max-height: min(70vh, 36rem); overflow-y: auto;">
                                @foreach ($rooms as $room)
                                    @php
                                        $isSelected = $selectedRoom && $selectedRoom->id == $room->id;
                                        $rate = $room->occupancy_rate;
                                        $badgeClass = $rate >= 85 ? 'bg-label-danger' : ($rate >= 55 ? 'bg-label-warning' : 'bg-label-success');
                                        $barClass = $rate >= 85 ? 'bg-danger' : ($rate >= 55 ? 'bg-warning' : 'bg-success');
                                    @endphp
                                    <a href="{{ route('hospitalizations.roomManagement', ['room_id' => $room->id]) }}"
                                       class="room-list-item text-decoration-none d-block mb-2 rounded border p-3 {{ $isSelected ? 'border-primary bg-label-primary' : 'border-light bg-lighter' }}"
                                       data-room-name="{{ strtolower($room->name) }}"
                                       data-department-name="{{ strtolower($room->department_name ?? '') }}">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div class="min-w-0">
                                                <div class="fw-semibold text-heading text-truncate">{{ $room->name }}</div>
                                                @if ($room->department_name)
                                                    <div class="small text-muted text-truncate">{{ $room->department_name }}</div>
                                                @endif
                                            </div>
                                            <span class="badge {{ $badgeClass }} flex-shrink-0">{{ $rate }}%</span>
                                        </div>
                                        <div class="progress mt-2" style="height: 6px;">
                                            <div class="progress-bar {{ $barClass }}" role="progressbar"
                                                 style="width: {{ $rate }}%;" aria-valuenow="{{ $rate }}"
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="small text-muted mt-2">
                                            {{ $room->occupied_beds_count }} / {{ $room->beds_count }} {{ localize('global.occupied') ?: 'Occupied' }}
                                            · {{ $room->empty_beds_count }} {{ localize('global.empty_bed') ?: 'Empty' }}
                                        </div>
                                    </a>
                                @endforeach
                                <p class="text-center text-muted py-4 mb-0 d-none" id="roomListEmpty">
                                    {{ localize('global.no_records_found') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8 col-xl-9">
                        @if ($selectedRoom)
                            @php
                                $occupiedCount = $bedsWithOccupation->filter(fn ($b) => $b->active_hospitalization)->count();
                                $emptyCount = $bedsWithOccupation->count() - $occupiedCount;
                                $roomRate = $bedsWithOccupation->count() > 0
                                    ? (int) round(($occupiedCount / $bedsWithOccupation->count()) * 100)
                                    : 0;
                                $roomRateBadge = $roomRate >= 85 ? 'bg-label-danger' : ($roomRate >= 55 ? 'bg-label-warning' : 'bg-label-success');
                                $canEditRooms = auth()->user()->hasRole(['super_admin', 'admin']) || auth()->user()->can('edit-hospitalizations');
                            @endphp
                            <div class="card">
                                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div>
                                        <h5 class="mb-1">
                                            <i class="bx bx-bed me-2 text-info"></i>{{ $selectedRoom->name }}
                                        </h5>
                                        <div class="small text-muted">
                                            @if ($selectedRoom->department?->name)
                                                {{ $selectedRoom->department->name }} ·
                                            @endif
                                            {{ $occupiedCount }} / {{ $bedsWithOccupation->count() }}
                                            {{ localize('global.occupied') ?: 'Occupied' }}
                                            ({{ $roomRate }}%)
                                        </div>
                                    </div>
                                    <span class="badge {{ $roomRateBadge }}">
                                        {{ $roomRate }}% {{ localize('global.occupied') ?: 'Occupied' }}
                                    </span>
                                </div>

                                <div class="card-body border-bottom py-3">
                                    <div class="d-flex flex-wrap gap-2" id="bedFilterButtons">
                                        <button type="button" class="btn btn-sm btn-outline-primary active bed-filter-btn" data-filter="all">
                                            {{ localize('global.all') ?: 'All' }}
                                            <span class="opacity-75">({{ $bedsWithOccupation->count() }})</span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning bed-filter-btn" data-filter="occupied">
                                            {{ localize('global.occupied') ?: 'Occupied' }}
                                            <span class="opacity-75">({{ $occupiedCount }})</span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success bed-filter-btn" data-filter="empty">
                                            {{ localize('global.empty_bed') ?: 'Empty' }}
                                            <span class="opacity-75">({{ $emptyCount }})</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="card-body">
                                    @if ($bedsWithOccupation->isEmpty())
                                        <p class="text-muted mb-0 text-center py-5">
                                            {{ localize('global.no_beds_in_room') ?: 'No beds in this room.' }}
                                        </p>
                                    @else
                                        <div class="row g-3" id="bedGrid">
                                            @foreach ($bedsWithOccupation as $bed)
                                                @php
                                                    $isOccupied = (bool) $bed->active_hospitalization;
                                                    $patient = $bed->active_hospitalization->patient ?? null;
                                                    $initials = $patient?->name
                                                        ? mb_strtoupper(mb_substr(preg_replace('/\s+/', '', $patient->name), 0, 2))
                                                        : '';
                                                    $otherOccupiedBeds = $bedsWithOccupation->filter(
                                                        fn ($b) => $b->id != $bed->id && $b->active_hospitalization
                                                    );
                                                @endphp
                                                <div class="col-12 col-sm-6 col-xxl-4 bed-card-col"
                                                     data-status="{{ $isOccupied ? 'occupied' : 'empty' }}">
                                                    <div class="card h-100 border {{ $isOccupied ? 'border-warning bg-label-warning' : 'border-success bg-label-success' }} bg-opacity-10">
                                                        <div class="card-body d-flex flex-column">
                                                            <div class="d-flex align-items-start gap-3">
                                                                <div class="avatar avatar-md {{ $isOccupied ? 'bg-warning' : 'bg-success' }} rounded-circle flex-shrink-0">
                                                                    @if ($isOccupied)
                                                                        <span class="avatar-initial text-white fw-semibold">{{ $initials ?: '?' }}</span>
                                                                    @else
                                                                        <span class="avatar-initial"><i class="bx bx-bed text-white"></i></span>
                                                                    @endif
                                                                </div>
                                                                <div class="flex-grow-1 min-w-0">
                                                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                                                        <div class="fw-semibold text-heading">
                                                                            {{ localize('global.bed') ?: 'Bed' }} {{ $bed->number }}
                                                                        </div>
                                                                        @if ($isOccupied)
                                                                            <span class="badge bg-label-danger flex-shrink-0">{{ localize('global.occupied') ?: 'Occupied' }}</span>
                                                                        @else
                                                                            <span class="badge bg-label-success flex-shrink-0">{{ localize('global.empty_bed') ?: 'Empty' }}</span>
                                                                        @endif
                                                                    </div>

                                                                    @if ($isOccupied)
                                                                        <div class="mt-2">
                                                                            <div class="text-heading text-truncate">{{ $patient->name ?? '—' }}</div>
                                                                            @if (!empty($patient?->father_name))
                                                                                <div class="small text-muted text-truncate">{{ $patient->father_name }}</div>
                                                                            @endif
                                                                            @if ($bed->admission_date)
                                                                                <div class="small text-muted" dir="ltr">{{ $bed->admission_date }}</div>
                                                                            @endif
                                                                        </div>
                                                                    @else
                                                                        <div class="small text-success mt-2">{{ localize('global.empty_bed') ?: 'Empty' }}</div>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="mt-auto pt-3 d-grid gap-2">
                                                                @if ($isOccupied)
                                                                    <a href="{{ route('hospitalizations.show', $bed->active_hospitalization) }}"
                                                                       class="btn btn-sm btn-outline-secondary">
                                                                        <i class="bx bx-show me-1"></i>{{ localize('global.view') }}
                                                                    </a>
                                                                    @if ($canEditRooms)
                                                                        <div class="btn-group btn-group-sm flex-wrap">
                                                                            <button type="button" class="btn btn-outline-primary"
                                                                                data-bs-toggle="modal" data-bs-target="#movePatientModal"
                                                                                data-move-action="{{ route('hospitalizations.updateRoomBed', $bed->active_hospitalization->id) }}"
                                                                                data-patient-name="{{ $patient->name ?? '' }}"
                                                                                data-current-room="{{ $selectedRoom->name }}"
                                                                                data-current-bed="{{ $bed->number }}"
                                                                                data-hospitalization-id="{{ $bed->active_hospitalization->id }}">
                                                                                <i class="bx bx-transfer me-1"></i>{{ localize('global.change_room_bed') ?: 'Move' }}
                                                                            </button>
                                                                            @if ($otherOccupiedBeds->isNotEmpty())
                                                                                <button type="button" class="btn btn-outline-info btn-swap-bed"
                                                                                    data-bs-toggle="modal" data-bs-target="#swapBedModal"
                                                                                    data-swap-action="{{ route('hospitalizations.swapBed', $bed->active_hospitalization) }}"
                                                                                    data-patient-name="{{ $patient->name ?? '' }}"
                                                                                    data-current-bed="{{ $bed->number }}"
                                                                                    data-other-beds="{{ $otherOccupiedBeds->map(fn($b) => ['id' => $b->id, 'number' => $b->number, 'patient' => $b->active_hospitalization->patient->name ?? '—'])->values()->toJson() }}">
                                                                                    <i class="bx bx-swap-horizontal me-1"></i>{{ localize('global.swap_bed') ?: 'Swap bed' }}
                                                                                </button>
                                                                            @endif
                                                                            <button type="button" class="btn btn-outline-secondary btn-swap-room"
                                                                                data-bs-toggle="modal" data-bs-target="#swapRoomModal"
                                                                                data-swap-action="{{ route('hospitalizations.swapRoom', $bed->active_hospitalization) }}"
                                                                                data-patient-name="{{ $patient->name ?? '' }}"
                                                                                data-current-room-id="{{ $selectedRoom->id }}"
                                                                                data-current-room-name="{{ $selectedRoom->name }}"
                                                                                data-current-bed="{{ $bed->number }}"
                                                                                data-return-room-id="{{ $selectedRoom->id }}">
                                                                                <i class="bx bx-building me-1"></i>{{ localize('global.swap_room') ?: 'Swap room' }}
                                                                            </button>
                                                                            <button type="button" class="btn btn-outline-danger"
                                                                                data-bs-toggle="modal" data-bs-target="#unoccupyBedModal"
                                                                                data-action="{{ route('hospitalizations.unoccupyBed', $bed->active_hospitalization) }}"
                                                                                data-patient-name="{{ $patient->name ?? '' }}">
                                                                                <i class="bx bx-log-out me-1"></i>{{ localize('global.unoccupy_bed') ?: 'Unoccupy' }}
                                                                            </button>
                                                                        </div>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <p class="text-center text-muted py-5 mb-0 d-none" id="bedGridEmpty">
                                            {{ localize('global.no_records_found') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="card">
                                <div class="card-body text-center text-muted py-5">
                                    {{ localize('global.select') }}
                                </div>
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

    {{-- Unoccupy bed modal --}}
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
                            <input type="text" autocomplete="off" class="form-control datepicker_dari pdp-el" name="discharged_at_date" id="discharged_at_date" required placeholder="1403/01/01">
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

    {{-- Swap bed (same room) modal --}}
    <div class="modal fade" id="swapBedModal" tabindex="-1" aria-labelledby="swapBedModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="swapBedModalLabel">
                        <i class="bx bx-swap-horizontal me-2"></i>{{ localize('global.swap_bed') ?: 'Swap bed' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="swapBedForm" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-3 text-muted" id="swapBedInfo"></p>
                        <div class="mb-3">
                            <label for="swap_bed_target_bed_id" class="form-label fw-semibold">{{ localize('global.swap_with_bed') ?: 'Swap with bed' }} <span class="text-danger">*</span></label>
                            <select class="form-select" name="target_bed_id" id="swap_bed_target_bed_id" required>
                                <option value="">{{ localize('global.select') }}...</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                        <button type="submit" class="btn btn-info">
                            <i class="bx bx-swap-horizontal me-1"></i>{{ localize('global.swap_bed') ?: 'Swap bed' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Swap room modal --}}
    <div class="modal fade" id="swapRoomModal" tabindex="-1" aria-labelledby="swapRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="swapRoomModalLabel">
                        <i class="bx bx-building me-2"></i>{{ localize('global.swap_room') ?: 'Swap room' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="swapRoomForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="return_to_room_id" id="swapRoomReturnToRoomId" value="{{ $selectedRoom->id ?? '' }}">
                    <div class="modal-body">
                        <p class="mb-3 text-muted" id="swapRoomInfo"></p>
                        <div class="mb-3">
                            <label for="swap_room_target_room_id" class="form-label fw-semibold">{{ localize('global.select_room') ?: 'Select Room' }} <span class="text-danger">*</span></label>
                            <select class="form-select" name="target_room_id" id="swap_room_target_room_id" required>
                                <option value="">{{ localize('global.select') }}...</option>
                                @if (isset($selectedRoom) && isset($roomsWithOccupiedBeds))
                                    @foreach ($roomsWithOccupiedBeds->where('id', '!=', $selectedRoom->id) as $room)
                                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="swap_room_target_bed_id" class="form-label fw-semibold">{{ localize('global.select_bed') ?: 'Select Bed' }} <span class="text-danger">*</span></label>
                            <select class="form-select" name="target_bed_id" id="swap_room_target_bed_id" required disabled>
                                <option value="">{{ localize('global.select_room_first') ?: 'Select room first' }}...</option>
                            </select>
                            <small class="text-muted">{{ localize('global.swap_room_select_occupied_bed') ?: 'Select an occupied bed in the other room to swap with.' }}</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                        <button type="submit" class="btn btn-secondary">
                            <i class="bx bx-building me-1"></i>{{ localize('global.swap_room') ?: 'Swap room' }}
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
            var totalRooms = {{ $rooms->count() }};

            document.addEventListener('DOMContentLoaded', function() {
                var roomSearchInput = document.getElementById('roomSearchInput');
                if (roomSearchInput) {
                    roomSearchInput.addEventListener('input', function() {
                        var query = (this.value || '').trim().toLowerCase();
                        var visible = 0;
                        document.querySelectorAll('.room-list-item').forEach(function(item) {
                            var name = item.getAttribute('data-room-name') || '';
                            var department = item.getAttribute('data-department-name') || '';
                            var match = !query || name.indexOf(query) !== -1 || department.indexOf(query) !== -1;
                            item.classList.toggle('d-none', !match);
                            if (match) visible++;
                        });
                        var countEl = document.getElementById('roomListCount');
                        if (countEl) countEl.textContent = visible + ' / ' + totalRooms;
                        var emptyEl = document.getElementById('roomListEmpty');
                        if (emptyEl) emptyEl.classList.toggle('d-none', visible > 0);
                    });
                }

                document.querySelectorAll('.bed-filter-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var filter = this.getAttribute('data-filter');
                        document.querySelectorAll('.bed-filter-btn').forEach(function(b) {
                            b.classList.remove('active');
                        });
                        this.classList.add('active');

                        var visible = 0;
                        document.querySelectorAll('.bed-card-col').forEach(function(col) {
                            var status = col.getAttribute('data-status');
                            var match = filter === 'all' || status === filter;
                            col.classList.toggle('d-none', !match);
                            if (match) visible++;
                        });
                        var emptyEl = document.getElementById('bedGridEmpty');
                        if (emptyEl) emptyEl.classList.toggle('d-none', visible > 0);
                    });
                });

                if (typeof $ !== 'undefined' && $.fn.select2) {
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

                document.addEventListener('click', function(e) {
                    var btn = e.target.closest('button[data-bs-target="#unoccupyBedModal"][data-action]');
                    if (btn) {
                        var modal = document.getElementById('unoccupyBedModal');
                        if (modal) {
                            modal.dataset.pendingAction = btn.getAttribute('data-action') || '';
                            modal.dataset.pendingPatientName = btn.getAttribute('data-patient-name') || '';
                        }
                    }
                }, true);

                var unoccupyModal = document.getElementById('unoccupyBedModal');
                if (unoccupyModal) {
                    unoccupyModal.addEventListener('show.bs.modal', function(event) {
                        var button = event.relatedTarget;
                        var action = '';
                        var patientName = '';
                        if (button && button.getAttribute('data-action')) {
                            action = button.getAttribute('data-action');
                            patientName = button.getAttribute('data-patient-name') || '';
                        } else if (unoccupyModal.dataset.pendingAction) {
                            action = unoccupyModal.dataset.pendingAction;
                            patientName = unoccupyModal.dataset.pendingPatientName || '';
                        }
                        var form = document.getElementById('unoccupyBedForm');
                        var nameEl = document.getElementById('unoccupyPatientName');
                        if (form && action) form.action = action;
                        if (nameEl) nameEl.textContent = patientName ? ('{{ localize("global.patient") ?: "Patient" }}: ' + patientName) : '';
                        if (form) {
                            var remark = form.querySelector('#discharge_remark');
                            var status = form.querySelector('#discharge_status');
                            var dateEl = form.querySelector('#discharged_at_date');
                            var timeEl = form.querySelector('#discharged_at_time');
                            if (remark) remark.value = '';
                            if (status) status.value = '';
                            if (dateEl) dateEl.value = '';
                            var now = new Date();
                            var h = String(now.getHours()).padStart(2, '0');
                            var m = String(now.getMinutes()).padStart(2, '0');
                            if (timeEl) timeEl.value = h + ':' + m;
                        }
                    });
                }

                var moveModal = document.getElementById('movePatientModal');
                if (moveModal) {
                    moveModal.addEventListener('show.bs.modal', function(event) {
                        var button = event.relatedTarget;
                        if (!button) return;
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

                var swapBedModal = document.getElementById('swapBedModal');
                if (swapBedModal) {
                    swapBedModal.addEventListener('show.bs.modal', function(event) {
                        var button = event.relatedTarget;
                        if (!button || !button.classList.contains('btn-swap-bed')) return;
                        var action = button.getAttribute('data-swap-action');
                        var patientName = button.getAttribute('data-patient-name') || '';
                        var currentBed = button.getAttribute('data-current-bed') || '';
                        var otherBedsJson = button.getAttribute('data-other-beds') || '[]';
                        var form = document.getElementById('swapBedForm');
                        var infoEl = document.getElementById('swapBedInfo');
                        var selectEl = document.getElementById('swap_bed_target_bed_id');
                        form.action = action;
                        infoEl.textContent = (patientName ? ('{{ localize("global.patient") ?: "Patient" }}: ' + patientName + '. {{ localize("global.current_bed") ?: "Current bed" }}: ' + currentBed) : '');
                        selectEl.innerHTML = '<option value="">{{ localize("global.select") }}...</option>';
                        try {
                            var otherBeds = JSON.parse(otherBedsJson);
                            otherBeds.forEach(function(b) {
                                var opt = document.createElement('option');
                                opt.value = b.id;
                                opt.textContent = b.number + (b.patient && b.patient !== '—' ? ' (' + b.patient + ')' : '');
                                selectEl.appendChild(opt);
                            });
                        } catch (e) {}
                    });
                }

                var swapRoomModal = document.getElementById('swapRoomModal');
                if (swapRoomModal) {
                    swapRoomModal.addEventListener('show.bs.modal', function(event) {
                        var button = event.relatedTarget;
                        if (!button || !button.classList.contains('btn-swap-room')) return;
                        var action = button.getAttribute('data-swap-action');
                        var patientName = button.getAttribute('data-patient-name') || '';
                        var currentRoomName = button.getAttribute('data-current-room-name') || '';
                        var currentBed = button.getAttribute('data-current-bed') || '';
                        var returnRoomId = button.getAttribute('data-return-room-id') || '';
                        var form = document.getElementById('swapRoomForm');
                        var infoEl = document.getElementById('swapRoomInfo');
                        var returnInput = document.getElementById('swapRoomReturnToRoomId');
                        var bedSelect = document.getElementById('swap_room_target_bed_id');
                        form.action = action;
                        if (returnInput) returnInput.value = returnRoomId;
                        infoEl.textContent = (patientName ? ('{{ localize("global.patient") ?: "Patient" }}: ' + patientName + '. {{ localize("global.current_room") ?: "Current" }}: ' + currentRoomName + ' / {{ localize("global.current_bed") ?: "Bed" }}: ' + currentBed) : '');
                        bedSelect.innerHTML = '<option value="">{{ localize("global.select_room_first") ?: "Select room first" }}...</option>';
                        bedSelect.disabled = true;
                    });
                }

                $(document).on('change', '#swap_room_target_room_id', function() {
                    var roomId = $(this).val();
                    var $bedSelect = $('#swap_room_target_bed_id');
                    if (roomId) {
                        $bedSelect.prop('disabled', true).html('<option value="">{{ localize("global.searching") ?: "Loading" }}...</option>');
                        $.get(bedsApiUrl + '/' + roomId + '?occupied_only=1')
                            .done(function(html) {
                                $bedSelect.html(html || '<option value="">{{ localize("global.select") }}...</option>').prop('disabled', false);
                            })
                            .fail(function() {
                                $bedSelect.html('<option value="">{{ localize("global.select") }}...</option>').prop('disabled', false);
                            });
                    } else {
                        $bedSelect.html('<option value="">{{ localize("global.select_room_first") ?: "Select room first" }}...</option>').prop('disabled', true);
                    }
                });
            });
        })();
    </script>
@endpush
