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
        'testcategory_id',
        'test_id',
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
     * Get the test category that owns this parameter
     */
    public function testCategory()
    {
        return $this->belongsTo(TestCategory::class, 'testcategory_id');
    }

    /**
     * Get the patient test results for this parameter
     */
    public function patientTestResults()
    {
        return $this->hasMany(PatientTestResult::class, 'lab_parameter_id');
    }
}
