<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class DiabetesChart extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nurse_id',
        'medicine_id',
        'insulin_dose',
        'rbs',
        'fbs',
        'unit',
        'time',
        'date',
        'diabetes_chartable_id',
        'diabetes_chartable_type',
    ];

    protected $casts = [
        'insulin_dose' => 'decimal:2',
        'rbs' => 'decimal:2',
        'fbs' => 'decimal:2',
        'time' => 'datetime:H:i',
        'date' => 'date',
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
     * Get the nurse who performed the test/administration.
     */
    public function nurse()
    {
        return $this->belongsTo(Nurse::class);
    }

    /**
     * Get the medicine that was administered.
     */
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * Get the parent model (under_review or hospitalization).
     */
    public function diabetesChartable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created the record.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the record.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the record.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by nurse.
     */
    public function scopeByNurse($query, $nurseId)
    {
        return $query->where('nurse_id', $nurseId);
    }

    /**
     * Scope to filter by medicine.
     */
    public function scopeByMedicine($query, $medicineId)
    {
        return $query->where('medicine_id', $medicineId);
    }

    /**
     * Scope to filter by chartable type.
     */
    public function scopeByChartableType($query, $type)
    {
        return $query->where('diabetes_chartable_type', $type);
    }

    /**
     * Get the blood sugar reading (RBS or FBS).
     */
    public function getBloodSugarReadingAttribute()
    {
        if ($this->rbs) {
            return $this->rbs . ' ' . $this->unit . ' (RBS)';
        }
        if ($this->fbs) {
            return $this->fbs . ' ' . $this->unit . ' (FBS)';
        }
        return null;
    }

    /**
     * Get formatted time.
     */
    public function getFormattedTimeAttribute()
    {
        return $this->time ? $this->time->format('H:i') : null;
    }

    /**
     * Get the patient through the chartable relationship.
     */
    public function getPatientAttribute()
    {
        if ($this->diabetesChartable) {
            return $this->diabetesChartable->patient ?? null;
        }
        return null;
    }
}