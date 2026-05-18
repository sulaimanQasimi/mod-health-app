<?php

namespace App\Http\Requests;

use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMultipleVitalSignsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Single vital sign (legacy)
            'vital_sign_type_id' => 'nullable|integer|exists:vital_sign_types,id',
            'morphable_id' => 'required|integer|min:1',
            'morphable_type' => [
                'required',
                'string',
                Rule::in(['App\\Models\\UnderReview', 'App\\Models\\Hospitalization'])
            ],
            // Multiple vital signs with schedules
            'vital_signs' => 'nullable|array',
            'vital_signs.*.vital_sign_type_id' => 'nullable|integer|exists:vital_sign_types,id',
            'vital_signs.*.schedules' => 'nullable|array',
            'vital_signs.*.schedules.*.date' => 'nullable|date|before_or_equal:today',
            'vital_signs.*.schedules.*.morning_time' => 'nullable|string|max:255',
            'vital_signs.*.schedules.*.evening_time' => 'nullable|string|max:255',
            'vital_signs.*.schedules.*.nurse_id' => 'nullable|integer|exists:nurses,id',
            // Existing vital signs (edit on manage page)
            'existing_vital_signs' => 'nullable|array',
            'existing_vital_signs.*.id' => 'required|integer|exists:vital_signs,id',
            'existing_vital_signs.*.vital_sign_type_id' => 'required|integer|exists:vital_sign_types,id',
            'existing_vital_signs.*.schedules' => 'nullable|array',
            'existing_vital_signs.*.schedules.*.id' => 'nullable|integer|exists:vital_sign_schedules,id',
            'existing_vital_signs.*.schedules.*.date' => 'nullable|date|before_or_equal:today',
            'existing_vital_signs.*.schedules.*.morning_time' => 'nullable|string|max:255',
            'existing_vital_signs.*.schedules.*.evening_time' => 'nullable|string|max:255',
            'delete_vital_sign_ids' => 'nullable|array',
            'delete_vital_sign_ids.*' => 'integer|exists:vital_signs,id',
            'delete_schedule_ids' => 'nullable|array',
            'delete_schedule_ids.*' => 'integer|exists:vital_sign_schedules,id',
        ];
    }

    /**
     * Filter out vital sign entries with no type selected so validation and controller see only filled rows.
     */
    protected function prepareForValidation(): void
    {
        $vitalSigns = $this->input('vital_signs', []);
        if (is_array($vitalSigns) && count($vitalSigns) > 0) {
            $filtered = array_values(array_filter($vitalSigns, function ($row) {
                return !empty($row['vital_sign_type_id']);
            }));
            $this->merge(['vital_signs' => $this->convertScheduleDates($filtered)]);
        }

        $existing = $this->input('existing_vital_signs', []);
        if (is_array($existing) && count($existing) > 0) {
            $this->merge(['existing_vital_signs' => $this->convertScheduleDates($existing)]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $vitalSigns
     * @return array<int, array<string, mixed>>
     */
    private function convertScheduleDates(array $vitalSigns): array
    {
        foreach ($vitalSigns as &$row) {
            $schedules = $row['schedules'] ?? [];
            if (!is_array($schedules)) {
                continue;
            }
            foreach ($schedules as &$schedule) {
                $date = $schedule['date'] ?? null;
                if ($date && is_string($date)) {
                    try {
                        $schedule['date'] = Verta::parse($date)->datetime()->format('Y-m-d');
                    } catch (\Exception $e) {
                        // leave as-is; validation may fail
                    }
                }
            }
        }
        unset($row, $schedule);

        return $vitalSigns;
    }

    /**
     * Configure the validator to require either single or multiple structure,
     * and require nurse for each schedule when user is not a nurse.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasSingle = $this->filled('vital_sign_type_id');
            $vitalSigns = $this->input('vital_signs', []);
            $hasMultiple = is_array($vitalSigns) && collect($vitalSigns)->contains(fn ($row) => !empty($row['vital_sign_type_id']));

            $hasExisting = is_array($this->input('existing_vital_signs')) && count($this->input('existing_vital_signs')) > 0;
            $hasDeletes = is_array($this->input('delete_vital_sign_ids')) && count($this->input('delete_vital_sign_ids')) > 0;
            $hasScheduleDeletes = is_array($this->input('delete_schedule_ids')) && count($this->input('delete_schedule_ids')) > 0;

            if (!$hasSingle && !$hasMultiple && !$hasExisting && !$hasDeletes && !$hasScheduleDeletes) {
                $validator->errors()->add('vital_signs', __('global.at_least_one_vital_sign_type_required'));
            }
        });
    }

    public function messages(): array
    {
        return [
            'vital_signs.*.vital_sign_type_id.required_with' => 'Each vital sign must have a type selected.',
            'vital_signs.*.vital_sign_type_id.exists' => 'The selected vital sign type does not exist.',
            'vital_signs.*.schedules.*.date.before_or_equal' => 'Schedule date may not be in the future.',
            'vital_signs.*.schedules.*.nurse_id.exists' => 'The selected nurse does not exist.',
        ];
    }

    public function attributes(): array
    {
        return [
            'vital_sign_type_id' => 'vital sign type',
            'morphable_id' => 'related record ID',
            'morphable_type' => 'related record type',
            'vital_signs.*.vital_sign_type_id' => 'vital sign type',
            'vital_signs.*.schedules.*.date' => 'date',
            'vital_signs.*.schedules.*.morning_time' => 'morning time',
            'vital_signs.*.schedules.*.evening_time' => 'evening time',
            'vital_signs.*.schedules.*.nurse_id' => 'nurse',
        ];
    }
}
