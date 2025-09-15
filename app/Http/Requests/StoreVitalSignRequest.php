<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVitalSignRequest extends FormRequest
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
            'vital_sign_type_id' => 'required|integer|exists:vital_sign_types,id',
            'morphable_id' => 'required|integer|min:1',
            'morphable_type' => [
                'required',
                'string',
                Rule::in(['App\\Models\\UnderReview', 'App\\Models\\Hospitalization'])
            ],
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
            'vital_sign_type_id.required' => 'The vital sign type is required.',
            'vital_sign_type_id.exists' => 'The selected vital sign type does not exist.',
            'morphable_id.required' => 'The related record ID is required.',
            'morphable_id.integer' => 'The related record ID must be an integer.',
            'morphable_type.required' => 'The related record type is required.',
            'morphable_type.in' => 'The related record type must be either UnderReview or Hospitalization.',
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
            'vital_sign_type_id' => 'vital sign type',
            'morphable_id' => 'related record ID',
            'morphable_type' => 'related record type',
        ];
    }
}
