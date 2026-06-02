<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HemodialysisSession extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'nephrology_registration_id',
        'appointment_id',
        'doctor_id',
        'branch_id',
        'diagnosis',
        'dialysis_schedule',
        'session_date',
        'session_time',
        'duration_minutes',
        'vascular_access_type',
        'pre_blood_pressure',
        'pre_weight',
        'pre_pulse',
        'pre_temperature',
        'post_blood_pressure',
        'post_weight',
        'post_pulse',
        'post_temperature',
        'fluid_removed_ml',
        'dialyzer_type',
        'blood_type',
        'complications_notes',
        'status',
        'ref_no',
    ];

    protected $casts = [
        'session_date' => 'date',
        'pre_weight' => 'decimal:2',
        'pre_temperature' => 'decimal:1',
        'post_weight' => 'decimal:2',
        'post_temperature' => 'decimal:1',
        'fluid_removed_ml' => 'decimal:2',
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $user = Auth::user();
            $model->created_by = $user->id ?? 0;

            if (empty($model->ref_no)) {
                $ref = DB::table('ref_numbers')->lockForUpdate()->first();
                $newRefNo = $ref->last_ref_no + 1;
                DB::table('ref_numbers')->update(['last_ref_no' => $newRefNo]);
                $model->ref_no = $newRefNo;
            }
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

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function nephrologyRegistration()
    {
        return $this->belongsTo(NephrologyRegistration::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function markCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    public function markInProgress()
    {
        $this->update(['status' => 'in_progress']);
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }
}
