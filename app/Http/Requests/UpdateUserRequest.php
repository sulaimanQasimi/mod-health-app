<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {

        return [
            'name' => 'required|max:120',
            'last_name' => 'required|max:120',
            'email' => 'required|email',
        ];
    }

    public function messages(): array
    {

        return [
            'name.required' => localize('global.user_name_en_required'),
            'last_name.required' => localize('global.user_name_dr_required'),
            'email.required' => localize('global.user_email_required'),
        ];
    }
}
