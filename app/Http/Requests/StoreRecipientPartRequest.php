<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecipientPartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient_id' => ['required', 'exists:recipients,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('recipient_parts', 'name')
                    ->where(fn ($query) => $query->where('recipient_id', $this->input('recipient_id'))),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('recipient_parts', 'code')
                    ->where(fn ($query) => $query->where('recipient_id', $this->input('recipient_id'))),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_id.required' => localize('global.recipient_name_required'),
            'name.required' => localize('global.recipient_name_required'),
        ];
    }
}
