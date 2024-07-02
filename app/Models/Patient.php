<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Patient extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','last_name','phone','age','father_name','nid','province_id','district_id','referral_by','image','branch_id','job','rank','relation_id','job_type','gender','referral_name','referral_last_name','referral_father_name','referral_nid','referral_id_card','referral_phone','referral_recipient','type','id_card','job_category'];
    
    
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

    ############################################
    // probably add doctors relationship as well
    ############################################
    public function doctor()
    {
        return $this->belongsTo(User::class);
    }

    public function diagnoses()
    {
        return $this->hasMany(Diagnose::class);
    }

    public function labs()
    {
        return $this->hasMany(LabItem::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    public function province()
    {
        return $this->hasOne(Province::class, 'id', 'province_id');
    }
    public function district()
    {
        return $this->hasOne(District::class, 'id', 'district_id');
    }

    public function recipient()
    {
        return $this->belongsTo(Recipient::class, 'referred_by');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function hospitalizations()
    {
        return $this->hasMany(Hospitalization::class);
    }

}
