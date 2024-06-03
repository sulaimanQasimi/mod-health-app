<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ICU extends Model
{
    use HasFactory;

    protected $fillable=['description','appointment_id','hospitalization_id','patient_id','doctor_id','branch_id','operation_id','status','icu_enterance_note','icu_reject_reason'];

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

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function hospitalization()
    {
        return $this->belongsTo(Hospitalization::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function labs()
    {
        return $this->hasMany(Lab::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }
}
