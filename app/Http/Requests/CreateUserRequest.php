<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
            'name_en' => 'required|max:120',
            'name_dr' => 'required|max:120',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ];
    }

    public function messages(): array
    {

        return [
            'name_en.required' => localize('global.user_name_en_required'),
            'name_dr.required' => localize('global.user_name_dr_required'),
            'email.required' => localize('global.user_email_required'),
            'password.required' => localize('global.user_password_required'),
        ];
    }
}
