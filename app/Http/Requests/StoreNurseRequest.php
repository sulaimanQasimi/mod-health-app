<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNurseRequest extends FormRequest
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
            'user_id' => 'nullable|exists:users,id|unique:nurses,user_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'employee_id' => 'required|string|max:255|unique:nurses,employee_id',
            'department_id' => 'nullable|exists:departments,id',
            'branch_id' => 'nullable|exists:branches,id',
            'specialization' => 'nullable|string|max:255',
            'shift' => 'required|in:morning,evening,night',
            'employment_status' => 'required|in:active,inactive,on_leave',
            'date_of_joining' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'Selected user does not exist.',
            'user_id.unique' => 'This user is already associated with another nurse.',
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Gender must be either male or female.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'employee_id.required' => 'Employee ID is required.',
            'employee_id.unique' => 'Employee ID must be unique.',
            'shift.required' => 'Shift is required.',
            'shift.in' => 'Shift must be morning, evening, or night.',
            'employment_status.required' => 'Employment status is required.',
            'employment_status.in' => 'Employment status must be active, inactive, or on leave.',
            'department_id.exists' => 'Selected department does not exist.',
            'branch_id.exists' => 'Selected branch does not exist.',
            'email.email' => 'Email must be a valid email address.',
        ];
    }
}
