<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class NurseNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'time_am',
        'time_pm',
        'note',
        'date',
        'morphable_id',
        'morphable_type',
        'nurse_id',
    ];

    protected $casts = [
        'date' => 'date',
        'time_am' => 'datetime:H:i',
        'time_pm' => 'datetime:H:i',
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
     * Get the nurse who created this note.
     */
    public function nurse()
    {
        return $this->belongsTo(Nurse::class);
    }

    /**
     * Get the user who created this note.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this note.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this note.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the parent model (under_review or hospitalization).
     */
    public function morphable()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include notes for a specific morphable type.
     */
    public function scopeForMorphableType($query, $type)
    {
        return $query->where('morphable_type', $type);
    }

    /**
     * Scope a query to only include notes for a specific morphable ID.
     */
    public function scopeForMorphableId($query, $id)
    {
        return $query->where('morphable_id', $id);
    }

    /**
     * Scope a query to only include notes by a specific nurse.
     */
    public function scopeByNurse($query, $nurseId)
    {
        return $query->where('nurse_id', $nurseId);
    }

    /**
     * Scope a query to only include notes for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope a query to only include notes with AM times.
     */
    public function scopeWithAmTimes($query)
    {
        return $query->whereNotNull('time_am');
    }

    /**
     * Scope a query to only include notes with PM times.
     */
    public function scopeWithPmTimes($query)
    {
        return $query->whereNotNull('time_pm');
    }

    /**
     * Get the patient associated with this note through the morphable relationship.
     */
    public function getPatientAttribute()
    {
        if ($this->morphable) {
            return $this->morphable->patient;
        }
        return null;
    }
}
