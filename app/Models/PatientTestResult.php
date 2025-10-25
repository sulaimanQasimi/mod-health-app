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
     * Get the result value (text_result if set, otherwise result)
     */
    public function getResultValueAttribute()
    {
        return $this->text_result ?? $this->result;
    }
}
