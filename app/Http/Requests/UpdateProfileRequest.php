<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:120',
            'last_name' => 'required|max:120',
            'email' => 'required|email|unique:users,email,'.$this->user()->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => localize('global.user_name_en_required'),
            'last_name.required' => localize('global.user_name_dr_required'),
            'email.required' => localize('global.user_email_required'),
        ];
    }
}
