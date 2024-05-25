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

    protected $fillable = ['patient_id','doctor_id','branch_id','date','time','is_completed','status_remark','refferal_remarks'];

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
        return $this->belongsTo(User::class);
    }

    public function diagnose()
    {
        return $this->hasMany(Diagnose::class);
    }

    public function labs()
    {
        return $this->hasMany(Lab::class)->whereNull('hospitalization_id');
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

    public function anesthesia()
    {
        return $this->hasMany(Anesthesia::class);
    }

    public function unapproved_anesthesias()
    {
        return $this->hasMany(Anesthesia::class)->where('status', '0');
    }

    public function approved_anesthesias()
    {
        return $this->hasMany(Anesthesia::class)->where('status', '1');
    }

    public function icu()
    {
        return $this->hasMany(ICU::class);
    }
}
