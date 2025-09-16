<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class NutritionCare extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_name',
        'nurse_id',
        // Observation fields
        'cough',
        'sound',
        'fluid_swallowing_ability',
        'weight',
        'amount_and_type_of_nutrition',
        'diarrhea',
        'heart_failure_and_kidney_disease',
        'remaining_materials',
        'type_of_tube',
        // Intervention fields
        'constipation',
        'nutrition_is_provided',
        'mouth_hygiene',
        'oral_nutrition_advices',
        'voice_exercise',
        'swallowing_exercise',
        'aspiration_prevention_proceeded',
        // Note field
        'nutrition_care_full_note',
        // Morphable relationship
        'morphable_id',
        'morphable_type',
    ];

    protected $casts = [
        // Observation fields
        'cough' => 'boolean',
        'sound' => 'boolean',
        'fluid_swallowing_ability' => 'boolean',
        'weight' => 'boolean',
        'amount_and_type_of_nutrition' => 'boolean',
        'diarrhea' => 'boolean',
        'heart_failure_and_kidney_disease' => 'boolean',
        'remaining_materials' => 'boolean',
        'type_of_tube' => 'boolean',
        // Intervention fields
        'constipation' => 'boolean',
        'nutrition_is_provided' => 'boolean',
        'mouth_hygiene' => 'boolean',
        'oral_nutrition_advices' => 'boolean',
        'voice_exercise' => 'boolean',
        'swallowing_exercise' => 'boolean',
        'aspiration_prevention_proceeded' => 'boolean',
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
     * Get the nurse who created this nutrition care record.
     */
    public function nurse()
    {
        return $this->belongsTo(Nurse::class);
    }

    /**
     * Get the user who created this nutrition care record.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this nutrition care record.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this nutrition care record.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the parent model (under_review or hospitalization).
     */
    public function morphable()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include nutrition care records for a specific morphable type.
     */
    public function scopeForMorphableType($query, $type)
    {
        return $query->where('morphable_type', $type);
    }

    /**
     * Scope a query to only include nutrition care records for a specific morphable ID.
     */
    public function scopeForMorphableId($query, $id)
    {
        return $query->where('morphable_id', $id);
    }

    /**
     * Get the patient associated with this nutrition care record through the morphable relationship.
     */
    public function getPatientAttribute()
    {
        if ($this->morphable) {
            return $this->morphable->patient;
        }
        return null;
    }
}