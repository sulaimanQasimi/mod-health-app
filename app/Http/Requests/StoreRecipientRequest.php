<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecipientRequest extends FormRequest
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
            'name_dr'=>'required|unique:recipients,name_dr',
        ];
    }

    public function messages(): array
    {

        return [
            'name_dr.required'  => localize('global.recipient_name_required'),
        ];
    }
}
