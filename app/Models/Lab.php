<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Lab extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['result','result_file','status','appointment_id','lab_type_id','patient_id','doctor_id','branch_id','hospitalization_id'];

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

    public function labType()
    {
        return $this->belongsTo(LabType::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function labs()
    {
        return $this->belongsTo(Appointment::class);
    }

}
