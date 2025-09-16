<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class NursingAssessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Admission Details
        'admitted_from_time',
        'admitted_from_date',
        'admitted_from_emergency',
        'admitted_from_hospital',
        'admitted_from_family_member',
        'admitted_from_telephone',
        'chief_complaint',
        'information_provided_by_number',
        'information_provided_by_patient',
        'information_provided_by_family_member',
        
        // Vital Signs
        'blood_pressure',
        'pulse_rate',
        'respiratory_rate',
        'temperature',
        'oxygen_saturation',
        'weight',
        'height',
        'bmi',
        
        // Pregnancy
        'pregnancy_yes',
        'pregnancy_no',
        'pregnancy_age',
        
        // History
        'underlying_disease_yes',
        'underlying_disease_no',
        'underlying_disease_dm',
        'underlying_disease_ht',
        'underlying_disease_other',
        'hospitalization_history_yes',
        'hospitalization_history_no',
        'hospitalization_history_reasons',
        'surgical_history_yes',
        'surgical_history_no',
        'surgical_history_reasons',
        'allergy_history_yes',
        'allergy_history_no',
        'allergy_history_food',
        'allergy_history_others',
        'family_medical_history_yes',
        'family_medical_history_no',
        'follow_up_yes',
        'follow_up_no',
        'follow_up_never',
        'drugs_yes',
        'drugs_no',
        'vaccination_yes',
        'vaccination_no',
        'physical_checkup_yes',
        'physical_checkup_no',
        
        // Nutrition and Metabolism
        'nutrition_problem_none',
        'nutrition_problem_normal',
        'nutrition_problem_decrease',
        'nutrition_problem_vomiting',
        'nutrition_problem_difficulty_swallowing',
        'nutrition_problem_other',
        'nutrition_appetite',
        'nutrition_increase',
        'nutrition_nausea',
        'diet_npo',
        'diet_normal',
        'diet_liquid',
        'diet_breast_feeding',
        'diet_other',
        'therapeutic_diet_dm',
        'therapeutic_diet_low_na',
        'therapeutic_diet_low_protein',
        'therapeutic_diet_high_protein',
        'therapeutic_diet_other',
        'nutrition_state_normal',
        'nutrition_state_abnormal',
        'nutrition_state_over_nutrition',
        'nutrition_state_unintentional_weight_loss',
        'nutrition_state_decrease_intake',
        'nutrition_state_other',
        
        // Assistance with Feeding
        'feeding_assistance_self',
        'feeding_assistance_tube',
        'feeding_assistance_parenteral',
        'feeding_assistance_ng_og',
        
        // Skin Assessment
        'skin_elasticity_good',
        'skin_elasticity_weak',
        'skin_color_normal',
        'skin_color_pale',
        'skin_color_jaundice',
        'skin_color_cyanosis',
        'skin_dermatological_normal',
        'skin_dermatological_abnormal',
        'skin_wound_at',
        'skin_mass',
        'skin_hematoma',
        'skin_petechiae',
        'skin_rash',
        'skin_abrasion',
        'skin_contusion',
        'skin_dermatitis',
        'skin_laceration',
        'skin_burn',
        'skin_scratch',
        'skin_ulcer',
        'skin_loss_yes',
        'skin_loss_no',
        'skin_loss_intact_redness',
        'skin_loss_abrasion_blister',
        'skin_loss_shallow_deep_crater',
        'skin_loss_deep_crater_exposed',
        'skin_loss_other',
        
        // General/Wound Assessment
        'ecchymosis',
        'hematoma',
        'laceration',
        'mass',
        'petechiae',
        'rash',
        'suture',
        'other_wound',
        
        // Wound Bed Assessment
        'unstageable_slough',
        'unstageable_eschar',
        'deep_tissue_injury',
        
        // Respiratory Section
        'respiratory_rhythm_regular',
        'respiratory_rhythm_irregular',
        'respiratory_depth_deep',
        'respiratory_depth_shallow',
        'cough_yes',
        'cough_dry',
        'cough_productive',
        'cough_other',
        'sputum_have',
        'sputum_doesnt_have',
        'sputum_hemoptysis',
        'sputum_frothy',
        'sputum_color',
        'breath_sound_normal',
        'breath_sound_abnormal',
        'breath_sound_wheeze',
        'breath_sound_rhonchi',
        'breath_sound_crepitation',
        'breath_sound_other',
        'current_treatment_no',
        'current_treatment_oxygen_therapy',
        'current_treatment_tracheostomy',
        'current_treatment_ventilator',
        'current_treatment_chest_tube',
        'current_treatment_endotracheal',
        'current_treatment_other',
        'respiratory_effort_orthopnea',
        'respiratory_effort_dyspnea',
        'respiratory_effort_easy',
        'respiratory_effort_other',
        'respiratory_rate_apnea',
        'respiratory_rate_bradypnea',
        'respiratory_rate_tachypnea',
        'respiratory_rate_eupnea',
        
        // Cardiovascular Section
        'pulse_amplitude_strong',
        'pulse_amplitude_weakness',
        'pulse_amplitude_absent',
        'neck_vein_engorged_no',
        'neck_vein_engorged_yes',
        'edema_no',
        'edema_general',
        'edema_location',
        'pulse_rhythm_normal',
        'pulse_rhythm_tachycardia',
        'pulse_rhythm_bradycardia',
        'pulse_rhythm_regular',
        'pulse_rhythm_irregular',
        'chest_pain_no',
        'chest_pain_yes',
        'chest_pain_location',
        'chest_pain_referred',
        'chest_pain_time',
        'chest_pain_frequency',
        
        // Neurological Section
        'vision_normal',
        'vision_impaired',
        'vision_right',
        'hearing_normal',
        'hearing_impaired',
        'hearing_right',
        'hearing_left',
        'speech_normal',
        'speech_impaired',
        'smell_normal',
        'smell_impaired',
        'sensation_normal',
        'sensation_numbness',
        'sensation_tingling',
        'glasgow_coma_eye_opening',
        'glasgow_coma_motor_response',
        'glasgow_coma_verbal_response',
        'glasgow_coma_total_score',
        
        // Musculoskeletal Section
        'musculoskeletal_normal',
        'musculoskeletal_abnormal',
        'musculoskeletal_movement_normal',
        'musculoskeletal_movement_abnormal',
        'musculoskeletal_weakness',
        'musculoskeletal_deformity',
        'musculoskeletal_fracture',
        
        // EENT Section
        'hear_normal',
        'hear_abnormal',
        'nose_normal',
        'nose_abnormal',
        'eye_normal',
        'eye_abnormal',
        'pharynx_tonsil_not_injected',
        'pharynx_tonsil_injected',
        
        // Gastrointestinal Section
        'elimination_problem_no',
        'elimination_problem_diarrhea',
        'elimination_problem_constipation',
        'elimination_problem_none',
        'elimination_problem_incontinent',
        'elimination_problem_ostomy',
        'elimination_problem_other',
        'oral_cavity_normal',
        'oral_cavity_abnormal',
        'oral_cavity_moist',
        'oral_cavity_dry',
        'oral_cavity_abrasion',
        'oral_cavity_tumor',
        'oral_cavity_dentures',
        'oral_cavity_other',
        'abdomen_normal',
        'abdomen_soft',
        'abdomen_tenderness',
        'abdomen_other',
        
        // Renal/Urinary
        'voiding_continent',
        'voiding_incontinent',
        'voiding_dysuria',
        'voiding_catheter',
        'voiding_other',
        'urine_clear',
        'urine_cloudy',
        'urine_bloody',
        'urine_other',
        'bladder_full',
        'bladder_empty',
        'bladder_other',
        
        // Genital (Female Only)
        'genital_organ_normal',
        'genital_organ_abnormal',
        'nipple_normal',
        'nipple_abnormal',
        'nipple_tip_normal',
        'nipple_tip_abnormal',
        'menstrual_not_required',
        'menstrual_required',
        'menstrual_age_first_period',
        'menstrual_lmp',
        'menstrual_interval_days',
        'menstrual_duration_days',
        'menopause_age',
        'reproductive_tract_lochia_normal',
        'reproductive_tract_lochia_abnormal',
        'reproductive_tract_perineum_intact',
        'reproductive_tract_perineum_episiotomy',
        'contraception_no',
        'contraception_yes',
        'contraception_pill',
        'contraception_injection',
        'contraception_other',
        
        // Pain Assessment
        'pain_no',
        'pain_yes',
        'pain_location',
        'pain_intensity_score',
        'pain_pattern_intermittent',
        'pain_pattern_constant',
        'pain_pattern_other',
        'pain_description_burning',
        'pain_description_dull',
        'pain_description_sharp',
        'pain_description_electrical',
        'pain_description_other',
        
        // Religion
        'religion_islam',
        'religion_other',
        
        // Anxiety
        'anxiety_no',
        'anxiety_yes',
        'anxiety_illness',
        'anxiety_family',
        'anxiety_work',
        'anxiety_finance',
        'anxiety_other',
        
        // Support System
        'support_system_no',
        'support_system_yes',
        'support_system_family',
        'support_system_friend',
        'support_system_other',
        
        // Administrative Details
        'assessment_initiated_by_rn',
        'assessment_initiated_by_date',
        'assessment_initiated_by_time',
        'patient_name',
        'patient_age',
        'file_number',
        'hn',
        'sn',
        'assessment_initiated_by_nurse',
        'signature',
        'department_management',
        
        // Morphable relationship
        'morphable_id',
        'morphable_type',
        'nurse_id',
    ];

    protected $casts = [
        'admitted_from_date' => 'date',
        'assessment_initiated_by_date' => 'date',
        'assessment_initiated_by_time' => 'datetime:H:i',
        'pregnancy_age' => 'integer',
        'patient_age' => 'integer',
        'menstrual_age_first_period' => 'integer',
        'menstrual_interval_days' => 'integer',
        'menstrual_duration_days' => 'integer',
        'menopause_age' => 'integer',
        'pain_intensity_score' => 'integer',
        'glasgow_coma_eye_opening' => 'integer',
        'glasgow_coma_motor_response' => 'integer',
        'glasgow_coma_verbal_response' => 'integer',
        'glasgow_coma_total_score' => 'integer',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'bmi' => 'decimal:2',
        'pulse_rate' => 'integer',
        'respiratory_rate' => 'integer',
        'temperature' => 'decimal:1',
        'oxygen_saturation' => 'integer',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $user = Auth::user();
            $model->created_by = $user->id ?? 0;
        });

        self::updating(function ($model) {
            $user = Auth::user();
            $model->updated_by = $user->id ?? 0;
        });

        self::deleting(function ($model) {
            $user = Auth::user();
            $model->deleted_by = $user->id ?? 0;
            $model->save();
        });
    }

    /**
     * Get the nurse who conducted this assessment.
     */
    public function nurse()
    {
        return $this->belongsTo(Nurse::class);
    }

    /**
     * Get the parent morphable model (under_reviews or hospitalizations).
     */
    public function morphable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this assessment.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this assessment.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this assessment.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope a query to only include assessments for a specific morphable type.
     */
    public function scopeForMorphableType($query, $type)
    {
        return $query->where('morphable_type', $type);
    }

    /**
     * Scope a query to only include assessments for hospitalizations.
     */
    public function scopeForHospitalizations($query)
    {
        return $query->where('morphable_type', Hospitalization::class);
    }

    /**
     * Scope a query to only include assessments for under reviews.
     */
    public function scopeForUnderReviews($query)
    {
        return $query->where('morphable_type', UnderReview::class);
    }
}
