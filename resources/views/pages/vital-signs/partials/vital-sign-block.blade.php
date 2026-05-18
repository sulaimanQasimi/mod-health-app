@php
    $isExisting = $isExisting ?? false;
    $prefix = $isExisting ? "existing_vital_signs[{$index}]" : "vital_signs[{$index}]";
    $blockClass = $isExisting ? 'existing-vital-sign-block' : 'new-vital-sign-block';
    $vitalSignId = $vitalSign->id ?? null;
    $schedules = $vitalSign->schedules ?? collect();
    if ($schedules->isEmpty()) {
        $schedules = collect([(object) ['id' => null, 'date' => null, 'morning_time' => null, 'evening_time' => null, 'day' => null]]);
    }
@endphp
<div class="vital-sign-block {{ $blockClass }} card border shadow-sm mb-4" data-index="{{ $index }}"
    data-is-existing="{{ $isExisting ? '1' : '0' }}"
    @if($vitalSignId) data-vital-sign-id="{{ $vitalSignId }}" @endif>
    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
        <span class="fw-semibold">
            <i class="fas fa-heartbeat text-primary me-1"></i>
            <span class="vital-sign-label">{{ localize('global.vital_sign') }} {{ $index + 1 }}</span>
            @if($isExisting)
                <span class="badge bg-secondary ms-2">#{{ $vitalSignId }}</span>
            @endif
        </span>
        <button type="button"
            class="btn btn-sm btn-outline-danger {{ $isExisting || $index > 0 ? '' : 'd-none' }} remove-vital-sign"
            title="{{ localize('global.remove_vital_sign') }}">
            <i class="fas fa-trash-alt"></i>
        </button>
    </div>
    <div class="card-body">
        @if($isExisting)
            <input type="hidden" name="{{ $prefix }}[id]" value="{{ $vitalSignId }}">
        @endif
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">{{ localize('global.vital_sign_type_id') }} <span class="text-danger">*</span></label>
                <select name="{{ $prefix }}[vital_sign_type_id]"
                    class="form-select vital-sign-type-select"
                    data-placeholder="{{ localize('global.select_vital_sign_type') }}" required>
                    <option value="">{{ localize('global.select_vital_sign_type') }}</option>
                    @foreach($vitalSignTypes as $type)
                        <option value="{{ $type->id }}"
                            {{ (int) old($isExisting ? "existing_vital_signs.{$index}.vital_sign_type_id" : "vital_signs.{$index}.vital_sign_type_id", $vitalSign->vital_sign_type_id ?? '') === (int) $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="schedules-section">
            <label class="form-label d-block">
                <i class="fas fa-calendar-alt me-1"></i> {{ localize('global.schedules') }}
            </label>
            <div class="table-responsive">
                <table class="table table-bordered table-sm schedules-table">
                    <thead class="table-light">
                        <tr>
                            @if($isExisting)
                                <th style="width: 80px;">{{ localize('global.day') }}</th>
                            @endif
                            <th style="width: 140px;">{{ localize('global.date') }}</th>
                            <th style="width: 140px;">{{ localize('global.morning_time') }}</th>
                            <th style="width: 140px;">{{ localize('global.evening_time') }}</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody class="schedule-rows">
                        @foreach($schedules as $schedIndex => $schedule)
                            <tr class="schedule-row" @if($schedule->id ?? null) data-schedule-id="{{ $schedule->id }}" @endif>
                                @if($isExisting)
                                    <td class="align-middle">
                                        @if($schedule->id ?? null)
                                            <input type="hidden" name="{{ $prefix }}[schedules][{{ $schedIndex }}][id]"
                                                value="{{ $schedule->id }}">
                                            <span class="text-muted small">{{ $schedule->day ?? '-' }}</span>
                                        @endif
                                    </td>
                                @endif
                                <td>
                                    <input type="text"
                                        name="{{ $prefix }}[schedules][{{ $schedIndex }}][date]"
                                        class="form-control form-control-sm datepicker_dari schedule-date"
                                        value="{{ old($isExisting ? "existing_vital_signs.{$index}.schedules.{$schedIndex}.date" : "vital_signs.{$index}.schedules.{$schedIndex}.date", !empty($schedule->date) ? verta($schedule->date)->format('Y/m/d') : '') }}"
                                        placeholder="1403/01/01" autocomplete="off">
                                </td>
                                <td>
                                    <input type="text"
                                        name="{{ $prefix }}[schedules][{{ $schedIndex }}][morning_time]"
                                        class="form-control form-control-sm"
                                        value="{{ old($isExisting ? "existing_vital_signs.{$index}.schedules.{$schedIndex}.morning_time" : "vital_signs.{$index}.schedules.{$schedIndex}.morning_time", $schedule->morning_time ?? '') }}"
                                        placeholder="{{ localize('global.enter_morning_time') }}">
                                </td>
                                <td>
                                    <input type="text"
                                        name="{{ $prefix }}[schedules][{{ $schedIndex }}][evening_time]"
                                        class="form-control form-control-sm"
                                        value="{{ old($isExisting ? "existing_vital_signs.{$index}.schedules.{$schedIndex}.evening_time" : "vital_signs.{$index}.schedules.{$schedIndex}.evening_time", $schedule->evening_time ?? '') }}"
                                        placeholder="{{ localize('global.enter_evening_time') }}">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-schedule-row"
                                        title="{{ localize('global.remove') }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary add-schedule-row mt-2">
                <i class="fas fa-plus me-1"></i> {{ localize('global.add_schedule_row') }}
            </button>
        </div>
    </div>
</div>
