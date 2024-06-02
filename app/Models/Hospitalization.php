<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Hospitalization extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['reason','remarks','appointment_id','doctor_id','patient_id','room_id','bed_id',
    'food_type_id','is_discharged','branch_id','discharge_remark','discharge_status',
    'patinet_companion','companion_father_name','relation_to_patient','companion_card_type'];

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

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function labs()
    {
        return $this->hasMany(LabItem::class);
    }

    public function icu()
    {
        return $this->hasMany(ICU::class);
    }

    public function anesthesias()
    {
        return $this->hasMany(Anesthesia::class);
    }

    public function complaints()
    {
        return $this->hasMany(PatientComplaint::class);
    }


}
