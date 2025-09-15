<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MedicationAdministrationTime extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medication_administration_record_id',
        'time',
    ];

    protected $casts = [
        'time' => 'datetime:H:i',
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
     * Get the medication administration record that owns this time entry.
     */
    public function medicationAdministrationRecord()
    {
        return $this->belongsTo(MedicationAdministrationRecord::class);
    }

    /**
     * Get the user who created this time entry.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this time entry.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this time entry.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope a query to only include times for a specific MAR.
     */
    public function scopeForMar($query, $marId)
    {
        return $query->where('medication_administration_record_id', $marId);
    }

    /**
     * Scope a query to only include times with actual time values.
     */
    public function scopeWithTime($query)
    {
        return $query->whereNotNull('time');
    }

    /**
     * Get the formatted time string.
     */
    public function getFormattedTimeAttribute()
    {
        return $this->time ? $this->time->format('H:i') : null;
    }
}
