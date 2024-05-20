<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ICU extends Model
{
    use HasFactory;

    protected $fillable=['description','appointment_id','hospitalization_id','patient_id','doctor_id','branch_id'];

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

    public function doctor()
    {
        return $this->belongsTo(User::class);
    }
}
