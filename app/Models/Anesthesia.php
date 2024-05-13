<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anesthesia extends Model
{
    use HasFactory;

    protected $fillable = ['description','patient_id','appointment_id','branch_id','date','time'];

}
