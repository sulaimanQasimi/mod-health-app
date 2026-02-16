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
            // Convert Persian (Jalali) schedule dates to Gregorian for validation and storage
            foreach ($filtered as $i => &$row) {
                $schedules = $row['schedules'] ?? [];
                if (!is_array($schedules)) {
                    continue;
                }
                foreach ($schedules as $j => &$schedule) {
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
            $this->merge(['vital_signs' => $filtered]);
        }
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

            if (!$hasSingle && !$hasMultiple) {
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
