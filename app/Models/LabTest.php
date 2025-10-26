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
        'name',
        'lab_type_id',
        'has_parameters',
    ];


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

    /**
     * Get the lab type that owns this lab test
     */
    public function labType()
    {
        return $this->belongsTo(LabType::class);
    }


    /**
     * Scope for tests with parameters
     */
    public function scopeWithParameters($query)
    {
        return $query->where('has_parameters', true);
    }

    /**
     * Scope for tests without parameters
     */
    public function scopeWithoutParameters($query)
    {
        return $query->where('has_parameters', false);
    }
}
