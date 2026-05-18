<?php

namespace App\Http\Requests;

use App\Services\VitalSignManageService;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMultipleVitalSignsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $morphableRules = $this->isMorphableManageRequest()
            ? ['required']
            : ['nullable'];

        return [
            'vital_sign_type_id' => 'nullable|integer|exists:vital_sign_types,id',
            'morphable_id' => array_merge($morphableRules, ['integer', 'min:1']),
            'morphable_type' => array_merge($morphableRules, [
                'string',
                Rule::in(['App\\Models\\UnderReview', 'App\\Models\\Hospitalization']),
            ]),
            'vital_signs' => 'nullable|array',
            'vital_signs.*.vital_sign_type_id' => 'nullable|integer|exists:vital_sign_types,id',
            'vital_signs.*.schedules' => 'nullable|array',
            'vital_signs.*.schedules.*.date' => 'nullable|date|before_or_equal:today',
            'vital_signs.*.schedules.*.morning_time' => 'nullable|string|max:255',
            'vital_signs.*.schedules.*.evening_time' => 'nullable|string|max:255',
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

    protected function prepareForValidation(): void
    {
        if ($this->has('morphable_type') && !$this->filled('morphable_type')) {
            $this->merge(['morphable_type' => null, 'morphable_id' => null]);
        }

        $vitalSigns = $this->input('vital_signs', []);
        if (is_array($vitalSigns)) {
            $this->merge([
                'vital_signs' => $this->convertScheduleDates(
                    array_values(array_filter($vitalSigns, fn ($row) => !empty($row['vital_sign_type_id'])))
                ),
            ]);
        }

        $existing = $this->input('existing_vital_signs', []);
        if (is_array($existing)) {
            $deleteIds = array_map('intval', $this->input('delete_vital_sign_ids', []));
            $deleteSet = array_flip($deleteIds);

            $existing = array_values(array_filter($existing, function ($row) use ($deleteSet) {
                $id = (int) ($row['id'] ?? 0);

                return $id > 0 && !isset($deleteSet[$id]);
            }));

            $this->merge(['existing_vital_signs' => $this->convertScheduleDates($existing)]);
        }
    }

    public function withValidator($validator): void
    {
        if (!$this->isMorphableManageRequest()) {
            return;
        }

        $validator->after(function ($validator) {
            $service = new VitalSignManageService();
            $type = (string) $this->input('morphable_type');

            if (!$service->isAllowedMorphableType($type)) {
                $validator->errors()->add('morphable_type', 'Invalid related record type.');

                return;
            }

            if (!$service->resolveMorphable($type, (int) $this->input('morphable_id'))) {
                $validator->errors()->add('morphable_id', 'Related record was not found.');

                return;
            }

            $hasNew = count($this->input('vital_signs', [])) > 0;
            $hasExisting = count($this->input('existing_vital_signs', [])) > 0;
            $hasDeletes = count($this->input('delete_vital_sign_ids', [])) > 0;
            $hasScheduleDeletes = count($this->input('delete_schedule_ids', [])) > 0;

            if (!$hasNew && !$hasExisting && !$hasDeletes && !$hasScheduleDeletes) {
                $validator->errors()->add('vital_signs', __('global.at_least_one_vital_sign_type_required'));
            }
        });
    }

    public function isMorphableManageRequest(): bool
    {
        return $this->filled('morphable_type') && $this->filled('morphable_id');
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
                        // validation will catch invalid dates
                    }
                }
            }
        }
        unset($row, $schedule);

        return $vitalSigns;
    }

    public function messages(): array
    {
        return [
            'vital_signs.*.schedules.*.date.before_or_equal' => 'Schedule date may not be in the future.',
            'existing_vital_signs.*.schedules.*.date.before_or_equal' => 'Schedule date may not be in the future.',
        ];
    }
}
