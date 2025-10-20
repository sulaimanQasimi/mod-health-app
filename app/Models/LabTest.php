<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Lab Test Model
 * 
 * Represents individual laboratory tests within categories
 */
class LabTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
    ];

    /**
     * Get the category that owns this lab test
     */
    public function category()
    {
        return $this->belongsTo(TestCategory::class, 'category_id');
    }

    /**
     * Get the parameters for this lab test
     */
    public function parameters()
    {
        return $this->hasMany(LabTestParameter::class, 'test_id');
    }

    /**
     * Get the patient test registrations for this lab test
     */
    public function patientTestRegistrations()
    {
        return $this->hasMany(PatientTestRegistration::class, 'lab_test_id');
    }
}
