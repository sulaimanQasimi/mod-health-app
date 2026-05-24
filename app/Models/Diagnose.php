<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Diagnose extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['description', 'patient_id', 'appointment_id', 'department_id', 'type', 'bp', 'pr', 'weight', 't', 'spo2', 'pain'];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $user = Auth::user();
            $model->created_by = $user->id ?? 0;

            if (empty($model->department_id) && !empty($model->appointment_id)) {
                $appointment = Appointment::find($model->appointment_id);
                if ($appointment?->department_id) {
                    $model->department_id = $appointment->department_id;
                }
            }
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

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function scopeForDepartmentName($query, string $name)
    {
        return $query->whereHas('department', fn ($q) => $q->where('name', $name));
    }

    public static function forNephrology()
    {
        return static::forDepartmentName(Disease::NEPHROLOGY_DEPARTMENT_NAME)->orderBy('created_at', 'desc');
    }
}
