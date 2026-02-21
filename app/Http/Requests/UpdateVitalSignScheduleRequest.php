<?php

namespace App\Http\Requests;

use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVitalSignScheduleRequest extends FormRequest
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
            'vital_sign_id' => 'required|integer|exists:vital_signs,id',
            'morning_time' => 'nullable|string|max:255',
            'evening_time' => 'nullable|string|max:255',
            'day' => 'nullable|string|max:50', // Preserved from original
            'date' => 'nullable|date', // Preserved from original
            'nurse_id' => 'nullable|integer|exists:nurses,id', // Preserved from original
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('date')) {
            try {
                $this->merge(['date' => Verta::parse($this->date)->datetime()->format('Y-m-d')]);
            } catch (\Exception $e) {
                // leave as-is
            }
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'vital_sign_id.required' => 'The vital sign is required.',
            'vital_sign_id.exists' => 'The selected vital sign does not exist.',
            'morning_time.max' => 'The morning time may not be greater than 255 characters.',
            'evening_time.max' => 'The evening time may not be greater than 255 characters.',
            'day.max' => 'The day may not be greater than 50 characters.',
            'date.before_or_equal' => 'The date may not be in the future.',
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
            'vital_sign_id' => 'vital sign',
            'morning_time' => 'morning time',
            'evening_time' => 'evening time',
            'day' => 'day',
            'date' => 'date',
            'nurse_id' => 'nurse',
        ];
    }
}
