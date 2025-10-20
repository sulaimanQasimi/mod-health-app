<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Patient Test Registration Model
 * 
 * Represents patient test registration records
 */
class PatientTestRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'registration_date',
        'ref_no',
        'lab_test_id',
        'status',
    ];

    protected $casts = [
        'registration_date' => 'datetime',
    ];

    /**
     * Get the patient that owns this registration
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Get the lab test for this registration
     */
    public function labTest()
    {
        return $this->belongsTo(LabTest::class, 'lab_test_id');
    }

    /**
     * Get the test results for this registration
     */
    public function results()
    {
        return $this->hasMany(PatientTestResult::class, 'test_registration_id');
    }
}
