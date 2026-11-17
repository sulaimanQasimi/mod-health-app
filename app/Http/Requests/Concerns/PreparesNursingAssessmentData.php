<?php

namespace App\Http\Requests\Concerns;

use Hekmatinasser\Verta\Facades\Verta;

trait PreparesNursingAssessmentData
{
    protected function prepareForValidation(): void
    {
        foreach (['admitted_from_date', 'assessment_initiated_by_date'] as $field) {
            if ($this->filled($field)) {
                try {
                    $this->merge([
                        $field => Verta::parse($this->input($field))->datetime()->format('Y-m-d'),
                    ]);
                } catch (\Throwable $e) {
                    // Leave value as submitted; validation will handle invalid dates.
                }
            }
        }

        $checkboxFields = [
            'admitted_from_emergency',
            'admitted_from_hospital',
            'admitted_from_family_member',
            'information_provided_by_patient',
            'information_provided_by_family_member',
            'pregnancy_yes',
            'pregnancy_no',
            'underlying_disease_yes',
            'underlying_disease_no',
            'underlying_disease_dm',
            'underlying_disease_ht',
            'hospitalization_history_yes',
            'hospitalization_history_no',
            'surgical_history_yes',
            'surgical_history_no',
            'allergy_history_yes',
            'allergy_history_no',
            'allergy_history_food',
            'pain_no',
            'pain_yes',
        ];

        $merged = [];
        foreach ($checkboxFields as $field) {
            $merged[$field] = $this->boolean($field);
        }

        $this->merge($merged);
    }
}
