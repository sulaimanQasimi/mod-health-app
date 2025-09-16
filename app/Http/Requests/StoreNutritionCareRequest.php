<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNutritionCareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_name' => 'required|string|max:255',
            'nurse_id' => 'nullable|integer|exists:nurses,id',
            // Observation fields
            'cough' => 'nullable|boolean',
            'sound' => 'nullable|boolean',
            'fluid_swallowing_ability' => 'nullable|boolean',
            'weight' => 'nullable|boolean',
            'amount_and_type_of_nutrition' => 'nullable|boolean',
            'diarrhea' => 'nullable|boolean',
            'heart_failure_and_kidney_disease' => 'nullable|boolean',
            'remaining_materials' => 'nullable|boolean',
            'type_of_tube' => 'nullable|boolean',
            // Intervention fields
            'constipation' => 'nullable|boolean',
            'nutrition_is_provided' => 'nullable|boolean',
            'mouth_hygiene' => 'nullable|boolean',
            'oral_nutrition_advices' => 'nullable|boolean',
            'voice_exercise' => 'nullable|boolean',
            'swallowing_exercise' => 'nullable|boolean',
            'aspiration_prevention_proceeded' => 'nullable|boolean',
            // Note field
            'nutrition_care_full_note' => 'nullable|string|max:5000',
            // Morphable relationship
            'morphable_id' => 'required|integer|min:1',
            'morphable_type' => [
                'required',
                'string',
                Rule::in(['App\\Models\\UnderReview', 'App\\Models\\Hospitalization'])
            ],
        ];
    }
}