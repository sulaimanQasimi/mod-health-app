<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anesthesia extends Model
{
    use HasFactory;

    protected $fillable = ['plan','date','time','planned_duration','position_on_bed','estimated_blood_waste','other_problems','status','anesthesia_log_reply','patient_id','appointment_id','branch_id','doctor_id','operation_type_id','is_operation_done','operation_remark','operation_doctor_id','operation_result'];

    public function operationType()
    {
        return $this->belongsTo(OperationType::class, 'operation_type_id','id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

}
