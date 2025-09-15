<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MedicationAdministrationRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medicine_id',
        'order_date',
        'date_signature',
        'nurse_id',
        'morphable_id',
        'morphable_type',
    ];

    protected $casts = [
        'order_date' => 'date',
        'date_signature' => 'date',
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
     * Get the medicine for this MAR.
     */
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * Get the nurse who administered/verified this MAR.
     */
    public function nurse()
    {
        return $this->belongsTo(Nurse::class);
    }

    /**
     * Get the parent model (under_review or hospitalization).
     */
    public function morphable()
    {
        return $this->morphTo();
    }

    /**
     * Get the administration times for this MAR.
     */
    public function administrationTimes()
    {
        return $this->hasMany(MedicationAdministrationTime::class);
    }

    /**
     * Get the user who created this MAR.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this MAR.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this MAR.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope a query to only include MARs for a specific morphable type.
     */
    public function scopeForMorphableType($query, $type)
    {
        return $query->where('morphable_type', $type);
    }

    /**
     * Scope a query to only include MARs for a specific morphable ID.
     */
    public function scopeForMorphableId($query, $id)
    {
        return $query->where('morphable_id', $id);
    }

    /**
     * Scope a query to only include MARs by a specific nurse.
     */
    public function scopeByNurse($query, $nurseId)
    {
        return $query->where('nurse_id', $nurseId);
    }

    /**
     * Scope a query to only include MARs for a specific medicine.
     */
    public function scopeForMedicine($query, $medicineId)
    {
        return $query->where('medicine_id', $medicineId);
    }

    /**
     * Scope a query to only include MARs with order date.
     */
    public function scopeWithOrderDate($query)
    {
        return $query->whereNotNull('order_date');
    }

    /**
     * Scope a query to only include MARs with signature date.
     */
    public function scopeWithSignatureDate($query)
    {
        return $query->whereNotNull('date_signature');
    }

    /**
     * Get the patient associated with this MAR through the morphable relationship.
     */
    public function getPatientAttribute()
    {
        if ($this->morphable) {
            return $this->morphable->patient;
        }
        return null;
    }

    /**
     * Get the patient ID associated with this MAR.
     */
    public function getPatientIdAttribute()
    {
        if ($this->morphable && $this->morphable->patient) {
            return $this->morphable->patient->id;
        }
        return null;
    }
}
