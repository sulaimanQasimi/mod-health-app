<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Patient Test Registration Model
 * 
 * Represents patient test registration records with polymorphic relationship
 */
class PatientTestRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'testable_type',
        'testable_id',
        'registration_date',
        'ref_no',
        'lab_type_id',
        'category_id',
        'status',
        'doctor_id',
        'branch_id',
        'priority',
        'notes',
        'detailed_notes',
        'metadata',
        'completed_at',
        'completed_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'registration_date' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Boot method to auto-set created_by/updated_by
     */
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $user = Auth::user();
            $model->created_by = $user->id ?? 1;
            $model->updated_by = $user->id ?? 1;
        });

        static::updating(function ($model) {
            $user = Auth::user();
            $model->updated_by = $user->id ?? 1;
        });
    }

    /**
     * Get the polymorphic relationship (appointment)
     */
    public function testable()
    {
        return $this->morphTo();
    }

    /**
     * Get the patient through the testable relationship
     */
    public function getPatientAttribute()
    {
        if ($this->testable && method_exists($this->testable, 'patient')) {
            return $this->testable->patient;
        }
        return null;
    }

    /**
     * Get the lab type for this registration
     */
    public function labType()
    {
        return $this->belongsTo(LabType::class);
    }

    /**
     * Get the test results for this registration
     */
    public function results()
    {
        return $this->hasMany(PatientTestResult::class, 'test_registration_id');
    }

    /**
     * Get the doctor for this registration
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the branch for this registration
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Get the user who created this registration
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this registration
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who completed this registration
     */
    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Mark registration as in progress
     */
    public function markInProgress()
    {
        $this->update(['status' => 'in_progress']);
    }

    /**
     * Mark registration as completed
     */
    public function markCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => Auth::id(),
        ]);
    }

    /**
     * Cancel registration
     */
    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Get all parameters for this lab type
     */
    public function getParameters()
    {
        return $this->labType->directLabTestParameters;
    }

    /**
     * Create test results for all parameters of this lab type
     */
    public function createResultsForAllParameters()
    {
        $parameters = $this->getParameters();
        
        foreach ($parameters as $parameter) {
            PatientTestResult::create([
                'patient_id' => $this->testable?->patient_id,
                'ref_no' => $this->ref_no,
                'lab_parameter_id' => $parameter->id,
                'unit' => $parameter->unit,
                'normal_range' => $parameter->normal_range,
                'test_registration_id' => $this->id,
            ]);
        }
    }

    /**
     * Get all test results for this registration
     */
    public function getTestResults()
    {
        return $this->results()->with(['parameter.directLabType'])->get();
    }

    /**
     * Check if all parameters have results
     */
    public function hasAllResults()
    {
        $parameterCount = $this->getParameters()->count();
        $resultCount = $this->results()->count();
        
        return $parameterCount > 0 && $parameterCount === $resultCount;
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentage()
    {
        $parameterCount = $this->getParameters()->count();
        if ($parameterCount === 0) return 100;
        
        $resultCount = $this->results()->count();
        return round(($resultCount / $parameterCount) * 100, 2);
    }
}
