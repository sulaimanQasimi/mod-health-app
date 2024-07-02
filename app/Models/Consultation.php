<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Consultation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['title','branch_id', 'appointment_id', 'patient_id', 'result', 'date', 'time','department_id','i_c_u_id','created_by','consultation_type','doctor_id'];

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

    public function comments()
    {
        return $this->hasMany(ConsultationComment::class);
    }

    public function getAssociatedDoctorsAttribute()
    {
        $userIds = array_map('intval', json_decode($this->doctor_id, true));
        return User::whereIn('id', $userIds)->get();
    }

    public function getAssociatedDepartmentsAttribute()
    {
        $departmentIds = array_map('intval', json_decode($this->department_id, true));
        return Department::whereIn('id', $departmentIds)->get();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

}
