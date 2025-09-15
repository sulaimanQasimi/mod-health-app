<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class VitalSign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vital_sign_type_id',
        'morphable_id',
        'morphable_type',
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
     * Get the vital sign type that owns the vital sign.
     */
    public function vitalSignType()
    {
        return $this->belongsTo(VitalSignType::class);
    }

    /**
     * Get the parent morphable model (under_reviews or hospitalizations).
     */
    public function morphable()
    {
        return $this->morphTo();
    }

    /**
     * Get the schedules for the vital sign.
     */
    public function schedules()
    {
        return $this->hasMany(VitalSignSchedule::class);
    }

    /**
     * Get the user who created the vital sign.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the vital sign.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted the vital sign.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
