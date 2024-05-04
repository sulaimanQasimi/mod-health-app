<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Appointment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['patient_id','doctor_id','branch_id','date','time'];

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

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function diagnose()
    {
        return $this->hasMany(Diagnose::class);
    }

    public function labs()
    {
        return $this->hasMany(Lab::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function prescription()
    {
        return $this->hasMany(Prescription::class);
    }

    public function hospitalization()
    {
        return $this->hasMany(Hospitalization::class);
    }
}
