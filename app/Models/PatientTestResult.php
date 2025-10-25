<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Patient Test Result Model
 * 
 * Represents individual parameter results for patient test registrations
 */
class PatientTestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'ref_no',
        'lab_parameter_id',
        'unit',
        'normal_range',
        'result',
        'text_result',
        'test_registration_id',
    ];

    /**
     * Get the patient that owns this result
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Get the lab test parameter for this result
     */
    public function parameter()
    {
        return $this->belongsTo(LabTestParameter::class, 'lab_parameter_id');
    }

    /**
     * Get the test registration that owns this result
     */
    public function testRegistration()
    {
        return $this->belongsTo(PatientTestRegistration::class, 'test_registration_id');
    }

    /**
     * Get the lab type through the parameter
     */
    public function labType()
    {
        return $this->hasOneThrough(
            LabType::class,
            LabTestParameter::class,
            'id', // Foreign key on LabTestParameter table
            'id', // Foreign key on LabType table
            'lab_parameter_id', // Local key on PatientTestResult
            'lab_type_id' // Local key on LabTestParameter table
        );
    }

    /**
     * Get the lab type directly through the parameter's directLabType relationship
     */
    public function labTypeThroughParameter()
    {
        return $this->hasOneThrough(
            LabType::class,
            LabTestParameter::class,
            'id', // Foreign key on LabTestParameter table
            'id', // Foreign key on LabType table
            'lab_parameter_id', // Local key on PatientTestResult table
            'lab_type_id' // Local key on LabTestParameter table
        );
    }

    /**
     * Get the result value (text_result if set, otherwise result)
     */
    public function getResultValueAttribute()
    {
        return $this->text_result ?? $this->result;
    }

    /**
     * Scope for filtering by lab type
     */
    public function scopeByLabType($query, $labTypeId)
    {
        return $query->whereHas('parameter', function ($q) use ($labTypeId) {
            $q->where('lab_type_id', $labTypeId);
        });
    }

    /**
     * Scope for filtering by parameter
     */
    public function scopeByParameter($query, $parameterId)
    {
        return $query->where('lab_parameter_id', $parameterId);
    }

    /**
     * Scope for filtering by test registration
     */
    public function scopeByTestRegistration($query, $registrationId)
    {
        return $query->where('test_registration_id', $registrationId);
    }

    /**
     * Check if result is within normal range
     */
    public function isWithinNormalRange()
    {
        if (!$this->parameter || !$this->parameter->normal_range) {
            return null; // Cannot determine
        }

        $normalRange = $this->parameter->normal_range;
        $result = $this->result;

        // Simple numeric range check (e.g., "10-20")
        if (preg_match('/(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)/', $normalRange, $matches)) {
            $min = (float) $matches[1];
            $max = (float) $matches[2];
            $value = (float) $result;
            return $value >= $min && $value <= $max;
        }

        return null; // Cannot determine for non-numeric ranges
    }
}
