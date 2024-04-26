<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['description','branch_id', 'appointment_id', 'patient_id','doctor_id'];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

}
