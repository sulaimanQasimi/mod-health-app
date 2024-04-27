<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hospitalization extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['reason','remarks','appointment_id','doctor_id','patient_id','room_id','bed_id'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

}
