<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Anesthesia extends Model
{
    use HasFactory;

    protected $fillable = ['plan','date','time','planned_duration','position_on_bed','estimated_blood_waste','other_problems','status','anesthesia_log_reply','patient_id','appointment_id','branch_id','doctor_id','operation_type_id','is_operation_done','operation_remark','operation_assistants_id','operation_result','operation_surgion_id','operation_anesthesia_log_id','operation_anesthesist_id',
'operation_scrub_nurse_id','operation_circulation_nurse_id','anesthesia_plan','hospitalization_id','is_operation_approved','operation_expense_remarks','room_id','bed_id'];

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

    public function operationType()
    {
        return $this->belongsTo(OperationType::class, 'operation_type_id','id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function surgion()
    {
        return $this->belongsTo(User::class, 'operation_surgion_id', 'id');
    }

    public function anesthesia_log()
    {
        return $this->belongsTo(User::class, 'operation_anesthesia_log_id', 'id');
    }

    public function anesthesist()
    {
        return $this->belongsTo(User::class, 'operation_anesthesist_id', 'id');
    }

    public function scrub_nurse()
    {
        return $this->belongsTo(User::class, 'operation_scrub_nurse_id', 'id');
    }

    public function circulation_nurse()
    {
        return $this->belongsTo(User::class, 'operation_circulation_nurse_id', 'id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function under_reviews()
    {
        return $this->hasMany(UnderReview::class, 'operation_id', 'id');
    }

    public function icu()
    {
        return $this->hasMany(ICU::class, 'operation_id', 'id');
    }

    public function getAssociatedAssistantsAttribute()
    {
        $userIds = array_map('intval', json_decode($this->operation_assistants_id, true));
        return User::whereIn('id', $userIds)->get();
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }


}
