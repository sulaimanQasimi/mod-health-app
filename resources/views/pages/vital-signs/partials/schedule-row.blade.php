@php
    $row = is_array($row) ? $row : [];
    $typeId = old("schedule_rows.{$index}.vital_sign_type_id", $row['vital_sign_type_id'] ?? '');
    $morning = old("schedule_rows.{$index}.morning_time", $row['morning_time'] ?? '');
    $evening = old("schedule_rows.{$index}.evening_time", $row['evening_time'] ?? '');
    $scheduleId = old("schedule_rows.{$index}.schedule_id", $row['schedule_id'] ?? '');
    $vitalSignId = old("schedule_rows.{$index}.vital_sign_id", $row['vital_sign_id'] ?? '');
@endphp
<tr class="schedule-row">
    <td>
        <input type="hidden" data-field="schedule_id" name="schedule_rows[{{ $index }}][schedule_id]"
            value="{{ $scheduleId }}">
        <input type="hidden" data-field="vital_sign_id" name="schedule_rows[{{ $index }}][vital_sign_id]"
            value="{{ $vitalSignId }}">
        <select data-field="vital_sign_type_id" name="schedule_rows[{{ $index }}][vital_sign_type_id]"
            class="form-select @error("schedule_rows.{$index}.vital_sign_type_id") is-invalid @enderror" required>
            <option value="">{{ localize('global.select_vital_sign_type') }}</option>
            @foreach ($vitalSignTypes as $vitalSignType)
                <option value="{{ $vitalSignType->id }}"
                    {{ (int) $typeId === (int) $vitalSignType->id ? 'selected' : '' }}>
                    {{ $vitalSignType->name }}
                </option>
            @endforeach
        </select>
        @error("schedule_rows.{$index}.vital_sign_type_id")
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </td>
    <td>
        <input type="text" data-field="morning_time" name="schedule_rows[{{ $index }}][morning_time]"
            class="form-control @error("schedule_rows.{$index}.morning_time") is-invalid @enderror"
            value="{{ $morning }}">
    </td>
    <td class="d-flex align-items-center gap-1">
        <input type="text" data-field="evening_time" name="schedule_rows[{{ $index }}][evening_time]"
            class="form-control @error("schedule_rows.{$index}.evening_time") is-invalid @enderror"
            value="{{ $evening }}">
        <button type="button" class="btn btn-sm btn-outline-danger remove-schedule-row flex-shrink-0"
            title="{{ localize('global.remove') }}">
            <i class="fas fa-times"></i>
        </button>
    </td>
</tr>
