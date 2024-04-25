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

    protected $fillable = ['result','result_file','appointment_id','lab_type_id','patient_id','doctor_id','branch_id'];

    public function labType()
    {
        return $this->belongsTo(LabType::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

}
