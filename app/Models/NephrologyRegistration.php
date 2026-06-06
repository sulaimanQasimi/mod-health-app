<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NephrologyRegistration extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'visit_date',
        'ref_no',
        'status',
        'chief_complaint',
        'diagnosis',
        'disease_id',
        'ckd_aki_stage',
        'dialysis_required',
        'dialysis_type',
        'access_type',
        'notes',
        'follow_up_plan',
        'branch_id',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'dialysis_required' => 'boolean',
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
            if ($model->isForceDeleting()) {
                return;
            }

            $model->updateQuietly(['deleted_by' => Auth::id() ?? 0]);
        });
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function disease()
    {
        return $this->belongsTo(Disease::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function hemodialysisSessions()
    {
        return $this->hasMany(HemodialysisSession::class);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function displayDiagnosis(): ?string
    {
        return $this->disease?->name ?? $this->diagnosis;
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

    public function assignToCurrentDoctorIfMissing(): bool
    {
        if (!empty($this->doctor_id)) {
            return false;
        }

        return $this->acceptByCurrentNephrologist();
    }

    public function acceptByCurrentNephrologist(): bool
    {
        $userId = Auth::id();
        if (empty($userId)) {
            return false;
        }

        $doctor = Doctor::where('user_id', $userId)->where('is_nephrologist', true)->first();
        if (!$doctor) {
            return false;
        }

        $status = in_array($this->status, ['completed', 'cancelled'], true)
            ? $this->status
            : 'in_progress';

        $this->update([
            'doctor_id' => $doctor->id,
            'status' => $status,
        ]);

        return true;
    }

    public function needsAcceptance(): bool
    {
        return empty($this->doctor_id)
            && !in_array($this->status, ['completed', 'cancelled'], true);
    }
}
