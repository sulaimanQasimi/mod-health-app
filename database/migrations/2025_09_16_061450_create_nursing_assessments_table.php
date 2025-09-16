<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nursing_assessments', function (Blueprint $table) {
            $table->id();
            
            // Admission Details
            $table->time('admitted_from_time')->nullable();
            $table->date('admitted_from_date')->nullable();
            $table->boolean('admitted_from_emergency')->default(false);
            $table->boolean('admitted_from_hospital')->default(false);
            $table->boolean('admitted_from_family_member')->default(false);
            $table->string('admitted_from_telephone')->nullable();
            $table->text('chief_complaint')->nullable();
            $table->string('information_provided_by_number')->nullable();
            $table->boolean('information_provided_by_patient')->default(false);
            $table->boolean('information_provided_by_family_member')->default(false);
            
            // Vital Signs
            $table->string('blood_pressure')->nullable();
            $table->integer('pulse_rate')->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->integer('oxygen_saturation')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('bmi', 4, 2)->nullable();
            
            // Pregnancy
            $table->boolean('pregnancy_yes')->default(false);
            $table->boolean('pregnancy_no')->default(false);
            $table->integer('pregnancy_age')->nullable();
            
            // History
            $table->boolean('underlying_disease_yes')->default(false);
            $table->boolean('underlying_disease_no')->default(false);
            $table->boolean('underlying_disease_dm')->default(false);
            $table->boolean('underlying_disease_ht')->default(false);
            $table->string('underlying_disease_other')->nullable();
            $table->boolean('hospitalization_history_yes')->default(false);
            $table->boolean('hospitalization_history_no')->default(false);
            $table->text('hospitalization_history_reasons')->nullable();
            $table->boolean('surgical_history_yes')->default(false);
            $table->boolean('surgical_history_no')->default(false);
            $table->text('surgical_history_reasons')->nullable();
            $table->boolean('allergy_history_yes')->default(false);
            $table->boolean('allergy_history_no')->default(false);
            $table->boolean('allergy_history_food')->default(false);
            $table->string('allergy_history_others')->nullable();
            $table->boolean('family_medical_history_yes')->default(false);
            $table->boolean('family_medical_history_no')->default(false);
            $table->boolean('follow_up_yes')->default(false);
            $table->boolean('follow_up_no')->default(false);
            $table->boolean('follow_up_never')->default(false);
            $table->boolean('drugs_yes')->default(false);
            $table->boolean('drugs_no')->default(false);
            $table->boolean('vaccination_yes')->default(false);
            $table->boolean('vaccination_no')->default(false);
            $table->boolean('physical_checkup_yes')->default(false);
            $table->boolean('physical_checkup_no')->default(false);
            
            // Nutrition and Metabolism
            $table->boolean('nutrition_problem_none')->default(false);
            $table->boolean('nutrition_problem_normal')->default(false);
            $table->boolean('nutrition_problem_decrease')->default(false);
            $table->boolean('nutrition_problem_vomiting')->default(false);
            $table->boolean('nutrition_problem_difficulty_swallowing')->default(false);
            $table->string('nutrition_problem_other')->nullable();
            $table->boolean('nutrition_appetite')->default(false);
            $table->boolean('nutrition_increase')->default(false);
            $table->boolean('nutrition_nausea')->default(false);
            $table->boolean('diet_npo')->default(false);
            $table->boolean('diet_normal')->default(false);
            $table->boolean('diet_liquid')->default(false);
            $table->boolean('diet_breast_feeding')->default(false);
            $table->string('diet_other')->nullable();
            $table->boolean('therapeutic_diet_dm')->default(false);
            $table->boolean('therapeutic_diet_low_na')->default(false);
            $table->boolean('therapeutic_diet_low_protein')->default(false);
            $table->boolean('therapeutic_diet_high_protein')->default(false);
            $table->string('therapeutic_diet_other')->nullable();
            $table->boolean('nutrition_state_normal')->default(false);
            $table->boolean('nutrition_state_abnormal')->default(false);
            $table->boolean('nutrition_state_over_nutrition')->default(false);
            $table->boolean('nutrition_state_unintentional_weight_loss')->default(false);
            $table->boolean('nutrition_state_decrease_intake')->default(false);
            $table->string('nutrition_state_other')->nullable();
            
            // Assistance with Feeding
            $table->boolean('feeding_assistance_self')->default(false);
            $table->boolean('feeding_assistance_tube')->default(false);
            $table->boolean('feeding_assistance_parenteral')->default(false);
            $table->boolean('feeding_assistance_ng_og')->default(false);
            
            // Skin Assessment
            $table->boolean('skin_elasticity_good')->default(false);
            $table->boolean('skin_elasticity_weak')->default(false);
            $table->boolean('skin_color_normal')->default(false);
            $table->boolean('skin_color_pale')->default(false);
            $table->boolean('skin_color_jaundice')->default(false);
            $table->boolean('skin_color_cyanosis')->default(false);
            $table->boolean('skin_dermatological_normal')->default(false);
            $table->boolean('skin_dermatological_abnormal')->default(false);
            $table->string('skin_wound_at')->nullable();
            $table->boolean('skin_mass')->default(false);
            $table->boolean('skin_hematoma')->default(false);
            $table->boolean('skin_petechiae')->default(false);
            $table->boolean('skin_rash')->default(false);
            $table->boolean('skin_abrasion')->default(false);
            $table->boolean('skin_contusion')->default(false);
            $table->boolean('skin_dermatitis')->default(false);
            $table->boolean('skin_laceration')->default(false);
            $table->boolean('skin_burn')->default(false);
            $table->boolean('skin_scratch')->default(false);
            $table->boolean('skin_ulcer')->default(false);
            $table->boolean('skin_loss_yes')->default(false);
            $table->boolean('skin_loss_no')->default(false);
            $table->boolean('skin_loss_intact_redness')->default(false);
            $table->boolean('skin_loss_abrasion_blister')->default(false);
            $table->boolean('skin_loss_shallow_deep_crater')->default(false);
            $table->boolean('skin_loss_deep_crater_exposed')->default(false);
            $table->string('skin_loss_other')->nullable();
            
            // General/Wound Assessment
            $table->boolean('ecchymosis')->default(false);
            $table->boolean('hematoma')->default(false);
            $table->boolean('laceration')->default(false);
            $table->boolean('mass')->default(false);
            $table->boolean('petechiae')->default(false);
            $table->boolean('rash')->default(false);
            $table->boolean('suture')->default(false);
            $table->string('other_wound')->nullable();
            
            // Wound Bed Assessment
            $table->boolean('unstageable_slough')->default(false);
            $table->boolean('unstageable_eschar')->default(false);
            $table->boolean('deep_tissue_injury')->default(false);
            
            // Respiratory Section
            $table->boolean('respiratory_rhythm_regular')->default(false);
            $table->boolean('respiratory_rhythm_irregular')->default(false);
            $table->boolean('respiratory_depth_deep')->default(false);
            $table->boolean('respiratory_depth_shallow')->default(false);
            $table->boolean('cough_yes')->default(false);
            $table->boolean('cough_dry')->default(false);
            $table->boolean('cough_productive')->default(false);
            $table->string('cough_other')->nullable();
            $table->boolean('sputum_have')->default(false);
            $table->boolean('sputum_doesnt_have')->default(false);
            $table->boolean('sputum_hemoptysis')->default(false);
            $table->boolean('sputum_frothy')->default(false);
            $table->string('sputum_color')->nullable();
            $table->boolean('breath_sound_normal')->default(false);
            $table->boolean('breath_sound_abnormal')->default(false);
            $table->boolean('breath_sound_wheeze')->default(false);
            $table->boolean('breath_sound_rhonchi')->default(false);
            $table->boolean('breath_sound_crepitation')->default(false);
            $table->string('breath_sound_other')->nullable();
            $table->boolean('current_treatment_no')->default(false);
            $table->string('current_treatment_oxygen_therapy')->nullable();
            $table->boolean('current_treatment_tracheostomy')->default(false);
            $table->boolean('current_treatment_ventilator')->default(false);
            $table->boolean('current_treatment_chest_tube')->default(false);
            $table->boolean('current_treatment_endotracheal')->default(false);
            $table->string('current_treatment_other')->nullable();
            $table->boolean('respiratory_effort_orthopnea')->default(false);
            $table->boolean('respiratory_effort_dyspnea')->default(false);
            $table->boolean('respiratory_effort_easy')->default(false);
            $table->string('respiratory_effort_other')->nullable();
            $table->boolean('respiratory_rate_apnea')->default(false);
            $table->boolean('respiratory_rate_bradypnea')->default(false);
            $table->boolean('respiratory_rate_tachypnea')->default(false);
            $table->boolean('respiratory_rate_eupnea')->default(false);
            
            // Cardiovascular Section
            $table->boolean('pulse_amplitude_strong')->default(false);
            $table->boolean('pulse_amplitude_weakness')->default(false);
            $table->boolean('pulse_amplitude_absent')->default(false);
            $table->boolean('neck_vein_engorged_no')->default(false);
            $table->boolean('neck_vein_engorged_yes')->default(false);
            $table->boolean('edema_no')->default(false);
            $table->boolean('edema_general')->default(false);
            $table->string('edema_location')->nullable();
            $table->boolean('pulse_rhythm_normal')->default(false);
            $table->boolean('pulse_rhythm_tachycardia')->default(false);
            $table->boolean('pulse_rhythm_bradycardia')->default(false);
            $table->boolean('pulse_rhythm_regular')->default(false);
            $table->boolean('pulse_rhythm_irregular')->default(false);
            $table->boolean('chest_pain_no')->default(false);
            $table->boolean('chest_pain_yes')->default(false);
            $table->string('chest_pain_location')->nullable();
            $table->string('chest_pain_referred')->nullable();
            $table->string('chest_pain_time')->nullable();
            $table->string('chest_pain_frequency')->nullable();
            
            // Neurological Section
            $table->boolean('vision_normal')->default(false);
            $table->boolean('vision_impaired')->default(false);
            $table->boolean('vision_right')->default(false);
            $table->boolean('hearing_normal')->default(false);
            $table->boolean('hearing_impaired')->default(false);
            $table->boolean('hearing_right')->default(false);
            $table->boolean('hearing_left')->default(false);
            $table->boolean('speech_normal')->default(false);
            $table->boolean('speech_impaired')->default(false);
            $table->boolean('smell_normal')->default(false);
            $table->boolean('smell_impaired')->default(false);
            $table->boolean('sensation_normal')->default(false);
            $table->boolean('sensation_numbness')->default(false);
            $table->boolean('sensation_tingling')->default(false);
            $table->integer('glasgow_coma_eye_opening')->nullable();
            $table->integer('glasgow_coma_motor_response')->nullable();
            $table->integer('glasgow_coma_verbal_response')->nullable();
            $table->integer('glasgow_coma_total_score')->nullable();
            
            // Musculoskeletal Section
            $table->boolean('musculoskeletal_normal')->default(false);
            $table->boolean('musculoskeletal_abnormal')->default(false);
            $table->boolean('musculoskeletal_movement_normal')->default(false);
            $table->boolean('musculoskeletal_movement_abnormal')->default(false);
            $table->boolean('musculoskeletal_weakness')->default(false);
            $table->boolean('musculoskeletal_deformity')->default(false);
            $table->boolean('musculoskeletal_fracture')->default(false);
            
            // EENT Section
            $table->boolean('hear_normal')->default(false);
            $table->boolean('hear_abnormal')->default(false);
            $table->boolean('nose_normal')->default(false);
            $table->boolean('nose_abnormal')->default(false);
            $table->boolean('eye_normal')->default(false);
            $table->boolean('eye_abnormal')->default(false);
            $table->boolean('pharynx_tonsil_not_injected')->default(false);
            $table->boolean('pharynx_tonsil_injected')->default(false);
            
            // Gastrointestinal Section
            $table->boolean('elimination_problem_no')->default(false);
            $table->boolean('elimination_problem_diarrhea')->default(false);
            $table->boolean('elimination_problem_constipation')->default(false);
            $table->boolean('elimination_problem_none')->default(false);
            $table->boolean('elimination_problem_incontinent')->default(false);
            $table->boolean('elimination_problem_ostomy')->default(false);
            $table->string('elimination_problem_other')->nullable();
            $table->boolean('oral_cavity_normal')->default(false);
            $table->boolean('oral_cavity_abnormal')->default(false);
            $table->boolean('oral_cavity_moist')->default(false);
            $table->boolean('oral_cavity_dry')->default(false);
            $table->boolean('oral_cavity_abrasion')->default(false);
            $table->boolean('oral_cavity_tumor')->default(false);
            $table->boolean('oral_cavity_dentures')->default(false);
            $table->string('oral_cavity_other')->nullable();
            $table->boolean('abdomen_normal')->default(false);
            $table->boolean('abdomen_soft')->default(false);
            $table->boolean('abdomen_tenderness')->default(false);
            $table->string('abdomen_other')->nullable();
            
            // Renal/Urinary
            $table->boolean('voiding_continent')->default(false);
            $table->boolean('voiding_incontinent')->default(false);
            $table->boolean('voiding_dysuria')->default(false);
            $table->boolean('voiding_catheter')->default(false);
            $table->string('voiding_other')->nullable();
            $table->boolean('urine_clear')->default(false);
            $table->boolean('urine_cloudy')->default(false);
            $table->boolean('urine_bloody')->default(false);
            $table->string('urine_other')->nullable();
            $table->boolean('bladder_full')->default(false);
            $table->boolean('bladder_empty')->default(false);
            $table->string('bladder_other')->nullable();
            
            // Genital (Female Only)
            $table->boolean('genital_organ_normal')->default(false);
            $table->boolean('genital_organ_abnormal')->default(false);
            $table->boolean('nipple_normal')->default(false);
            $table->boolean('nipple_abnormal')->default(false);
            $table->boolean('nipple_tip_normal')->default(false);
            $table->boolean('nipple_tip_abnormal')->default(false);
            $table->boolean('menstrual_not_required')->default(false);
            $table->boolean('menstrual_required')->default(false);
            $table->integer('menstrual_age_first_period')->nullable();
            $table->string('menstrual_lmp')->nullable();
            $table->integer('menstrual_interval_days')->nullable();
            $table->integer('menstrual_duration_days')->nullable();
            $table->integer('menopause_age')->nullable();
            $table->boolean('reproductive_tract_lochia_normal')->default(false);
            $table->boolean('reproductive_tract_lochia_abnormal')->default(false);
            $table->boolean('reproductive_tract_perineum_intact')->default(false);
            $table->boolean('reproductive_tract_perineum_episiotomy')->default(false);
            $table->boolean('contraception_no')->default(false);
            $table->boolean('contraception_yes')->default(false);
            $table->boolean('contraception_pill')->default(false);
            $table->boolean('contraception_injection')->default(false);
            $table->string('contraception_other')->nullable();
            
            // Pain Assessment
            $table->boolean('pain_no')->default(false);
            $table->boolean('pain_yes')->default(false);
            $table->string('pain_location')->nullable();
            $table->integer('pain_intensity_score')->nullable();
            $table->boolean('pain_pattern_intermittent')->default(false);
            $table->boolean('pain_pattern_constant')->default(false);
            $table->string('pain_pattern_other')->nullable();
            $table->boolean('pain_description_burning')->default(false);
            $table->boolean('pain_description_dull')->default(false);
            $table->boolean('pain_description_sharp')->default(false);
            $table->boolean('pain_description_electrical')->default(false);
            $table->string('pain_description_other')->nullable();
            
            // Religion
            $table->boolean('religion_islam')->default(false);
            $table->string('religion_other')->nullable();
            
            // Anxiety
            $table->boolean('anxiety_no')->default(false);
            $table->boolean('anxiety_yes')->default(false);
            $table->boolean('anxiety_illness')->default(false);
            $table->boolean('anxiety_family')->default(false);
            $table->boolean('anxiety_work')->default(false);
            $table->boolean('anxiety_finance')->default(false);
            $table->string('anxiety_other')->nullable();
            
            // Support System
            $table->boolean('support_system_no')->default(false);
            $table->boolean('support_system_yes')->default(false);
            $table->boolean('support_system_family')->default(false);
            $table->boolean('support_system_friend')->default(false);
            $table->string('support_system_other')->nullable();
            
            // Administrative Details
            $table->string('assessment_initiated_by_rn')->nullable();
            $table->date('assessment_initiated_by_date')->nullable();
            $table->time('assessment_initiated_by_time')->nullable();
            $table->string('patient_name')->nullable();
            $table->integer('patient_age')->nullable();
            $table->string('file_number')->nullable();
            $table->string('hn')->nullable();
            $table->string('sn')->nullable();
            $table->string('assessment_initiated_by_nurse')->nullable();
            $table->string('signature')->nullable();
            $table->string('department_management')->nullable();
            
            // Morphable relationship
            $table->unsignedBigInteger('morphable_id')->nullable();
            $table->string('morphable_type')->nullable();
            $table->unsignedBigInteger('nurse_id')->nullable();
            
            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index(['morphable_id', 'morphable_type']);
            $table->index('nurse_id');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nursing_assessments');
    }
};
