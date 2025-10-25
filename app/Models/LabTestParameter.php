<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Lab Test Parameter Model
 * 
 * Represents parameters for laboratory tests (e.g., WBC, RBC for CBC)
 */
class LabTestParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'lab_type_id',
        'parameter_name',
        'unit',
        'normal_range',
        'critical_low',
        'critical_high',
        'panic_low',
        'panic_high',
        'delta_check_enabled',
        'delta_check_threshold',
        'critical_comment',
        'panic_comment',
        'requires_verification',
        'verification_level',
        'result',
    ];

    /**
     * Get the lab test that owns this parameter
     */
    public function labTest()
    {
        return $this->belongsTo(LabTest::class, 'test_id');
    }

    /**
     * Get the lab type for this parameter through the lab test
     * 
     * Usage examples:
     * $parameter = LabTestParameter::find(1);
     * $labType = $parameter->labType; // Get the lab type for this parameter
     * $labTypeName = $parameter->labType->name;
     */
    public function labType()
    {
        return $this->hasOneThrough(LabType::class, LabTest::class, 'id', 'id', 'test_id', 'lab_type_id');
    }

    /**
     * Get the lab type directly linked to this parameter
     */
    public function directLabType()
    {
        return $this->belongsTo(LabType::class, 'lab_type_id');
    }

    /**
     * Get the patient test results for this parameter
     */
    public function patientTestResults()
    {
        return $this->hasMany(PatientTestResult::class, 'lab_parameter_id');
    }
}
