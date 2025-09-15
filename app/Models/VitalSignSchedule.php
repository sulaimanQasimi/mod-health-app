<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class VitalSignSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vital_sign_id',
        'morning_time',
        'evening_time',
        'day',
        'date',
        'nurse_id',
    ];

    protected $casts = [
        'morning_time' => 'datetime:H:i',
        'evening_time' => 'datetime:H:i',
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
     * Get the vital sign that owns the schedule.
     */
    public function vitalSign()
    {
        return $this->belongsTo(VitalSign::class);
    }

    /**
     * Get the nurse responsible for this schedule.
     */
    public function nurse()
    {
        return $this->belongsTo(Nurse::class);
    }

    /**
     * Get the user who created the schedule.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the schedule.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the schedule.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
