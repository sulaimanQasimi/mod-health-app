<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNurseNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'time_am' => 'nullable|date_format:H:i',
            'time_pm' => 'nullable|date_format:H:i',
            'note' => 'nullable|string|max:5000',
            'date' => 'nullable|date|before_or_equal:today',
            'morphable_id' => 'required|integer|min:1',
            'morphable_type' => [
                'required',
                'string',
                Rule::in(['App\\Models\\UnderReview', 'App\\Models\\Hospitalization'])
            ],
            'nurse_id' => 'nullable|integer|exists:nurses,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'time_am.date_format' => 'The AM time must be in HH:MM format.',
            'time_pm.date_format' => 'The PM time must be in HH:MM format.',
            'note.max' => 'The note may not be greater than 5000 characters.',
            'date.before_or_equal' => 'The date may not be in the future.',
            'morphable_id.required' => 'The related record ID is required.',
            'morphable_id.integer' => 'The related record ID must be an integer.',
            'morphable_type.required' => 'The related record type is required.',
            'morphable_type.in' => 'The related record type must be either UnderReview or Hospitalization.',
            'nurse_id.exists' => 'The selected nurse does not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'time_am' => 'AM time',
            'time_pm' => 'PM time',
            'note' => 'note',
            'date' => 'date',
            'morphable_id' => 'related record ID',
            'morphable_type' => 'related record type',
            'nurse_id' => 'nurse',
        ];
    }
}
