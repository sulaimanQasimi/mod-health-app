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

    protected $fillable = ['title','branch_id', 'appointment_id', 'patient_id', 'result', 'date', 'time','doctor_id'];


    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
