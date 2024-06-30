<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationComment extends Model
{
    use HasFactory;

    protected  $fillable = ['comment','consultation_id','patient_id','doctor_id','appointment_id','department_id'];


    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
