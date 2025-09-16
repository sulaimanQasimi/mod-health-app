<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNursingAssessmentRequest extends FormRequest
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
            'patient_name' => 'required|string|max:255',
            'nurse_id' => 'nullable|exists:nurses,id',
            'morphable_type' => 'required|string|in:App\Models\Hospitalization,App\Models\UnderReview',
            'morphable_id' => 'required|integer|min:1',
            
            // Admission Details
            'admitted_from_time' => 'nullable|date_format:H:i',
            'admitted_from_date' => 'nullable|date',
            'admitted_from_emergency' => 'boolean',
            'admitted_from_hospital' => 'boolean',
            'admitted_from_family_member' => 'boolean',
            'admitted_from_telephone' => 'nullable|string|max:255',
            'chief_complaint' => 'nullable|string',
            'information_provided_by_number' => 'nullable|string|max:255',
            'information_provided_by_patient' => 'boolean',
            'information_provided_by_family_member' => 'boolean',
            
            // Vital Signs
            'blood_pressure' => 'nullable|string|max:50',
            'pulse_rate' => 'nullable|integer|min:0|max:300',
            'respiratory_rate' => 'nullable|integer|min:0|max:100',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'oxygen_saturation' => 'nullable|integer|min:0|max:100',
            'weight' => 'nullable|numeric|min:0|max:500',
            'height' => 'nullable|numeric|min:0|max:300',
            'bmi' => 'nullable|numeric|min:0|max:100',
            
            // Pregnancy
            'pregnancy_yes' => 'boolean',
            'pregnancy_no' => 'boolean',
            'pregnancy_age' => 'nullable|integer|min:0|max:50',
            
            // Medical History - All boolean fields
            'underlying_disease_yes' => 'boolean',
            'underlying_disease_no' => 'boolean',
            'underlying_disease_dm' => 'boolean',
            'underlying_disease_ht' => 'boolean',
            'underlying_disease_other' => 'nullable|string|max:255',
            'hospitalization_history_yes' => 'boolean',
            'hospitalization_history_no' => 'boolean',
            'hospitalization_history_reasons' => 'nullable|string',
            'surgical_history_yes' => 'boolean',
            'surgical_history_no' => 'boolean',
            'surgical_history_reasons' => 'nullable|string',
            'allergy_history_yes' => 'boolean',
            'allergy_history_no' => 'boolean',
            'allergy_history_food' => 'boolean',
            'allergy_history_others' => 'nullable|string|max:255',
            'family_medical_history_yes' => 'boolean',
            'family_medical_history_no' => 'boolean',
            'follow_up_yes' => 'boolean',
            'follow_up_no' => 'boolean',
            'follow_up_never' => 'boolean',
            'drugs_yes' => 'boolean',
            'drugs_no' => 'boolean',
            'vaccination_yes' => 'boolean',
            'vaccination_no' => 'boolean',
            'physical_checkup_yes' => 'boolean',
            'physical_checkup_no' => 'boolean',
            
            // Pain Assessment
            'pain_no' => 'boolean',
            'pain_yes' => 'boolean',
            'pain_location' => 'nullable|string|max:255',
            'pain_intensity_score' => 'nullable|integer|min:0|max:10',
            'pain_pattern_intermittent' => 'boolean',
            'pain_pattern_constant' => 'boolean',
            'pain_pattern_other' => 'nullable|string|max:255',
            'pain_description_burning' => 'boolean',
            'pain_description_dull' => 'boolean',
            'pain_description_sharp' => 'boolean',
            'pain_description_electrical' => 'boolean',
            'pain_description_other' => 'nullable|string|max:255',
            
            // Administrative Details
            'assessment_initiated_by_rn' => 'nullable|string|max:255',
            'assessment_initiated_by_date' => 'nullable|date',
            'assessment_initiated_by_time' => 'nullable|date_format:H:i',
            'patient_age' => 'nullable|integer|min:0|max:150',
            'file_number' => 'nullable|string|max:255',
            'hn' => 'nullable|string|max:255',
            'sn' => 'nullable|string|max:255',
            'assessment_initiated_by_nurse' => 'nullable|string|max:255',
            'signature' => 'nullable|string|max:255',
            'department_management' => 'nullable|string|max:255',
        ];
    }
}
