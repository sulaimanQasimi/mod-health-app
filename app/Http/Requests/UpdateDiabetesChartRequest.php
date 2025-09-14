<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiabetesChartRequest extends FormRequest
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
            'nurse_id' => 'nullable|exists:nurses,id',
            'medicine_id' => 'nullable|exists:medicines,id',
            'insulin_dose' => 'nullable|numeric|min:0|max:999.99',
            'rbs' => 'nullable|numeric|min:0|max:999.99',
            'fbs' => 'nullable|numeric|min:0|max:999.99',
            'unit' => 'nullable|string|max:20',
            'time' => 'nullable|date_format:H:i',
            'date' => 'required|date|before_or_equal:today',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nurse_id.exists' => 'Selected nurse does not exist.',
            'medicine_id.exists' => 'Selected medicine does not exist.',
            'insulin_dose.numeric' => 'Insulin dose must be a valid number.',
            'insulin_dose.min' => 'Insulin dose must be at least 0.',
            'insulin_dose.max' => 'Insulin dose cannot exceed 999.99.',
            'rbs.numeric' => 'RBS value must be a valid number.',
            'rbs.min' => 'RBS value must be at least 0.',
            'rbs.max' => 'RBS value cannot exceed 999.99.',
            'fbs.numeric' => 'FBS value must be a valid number.',
            'fbs.min' => 'FBS value must be at least 0.',
            'fbs.max' => 'FBS value cannot exceed 999.99.',
            'unit.max' => 'Unit cannot exceed 20 characters.',
            'time.date_format' => 'Time must be in HH:MM format.',
            'date.required' => 'Date is required.',
            'date.date' => 'Date must be a valid date.',
            'date.before_or_equal' => 'Date cannot be in the future.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nurse_id' => 'nurse',
            'medicine_id' => 'medicine',
            'insulin_dose' => 'insulin dose',
            'rbs' => 'RBS (Random Blood Sugar)',
            'fbs' => 'FBS (Fasting Blood Sugar)',
            'unit' => 'unit',
            'time' => 'time',
            'date' => 'date',
        ];
    }
}