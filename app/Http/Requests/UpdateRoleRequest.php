<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
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
            'name' => 'required|unique:roles,name',
            'name_dr' => 'required',
        ];
    }

    public function messages(): array
    {

        return [
            'name_dr.required'  => localize('global.role_name_dr_required'),
            'name.required'  => localize('global.role_name_en_required'),
        ];
    }
}
