<?php

namespace App\Models;

use App\Blood\BloodCheck;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class BloodBank extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['group','branch_id', 'appointment_id', 'patient_id', 'rh', 'type', 'under_review_id', 'operation_id','i_c_u_id','anesthesia_id','hospitalization_id','status','quantity','department_id','reject_reason','created_by','updated_by','deleted_by'];

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

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }

    public function hospitalization()
    {
        return $this->belongsTo(Hospitalization::class);
    }

    public function anesthesia()
    {
        return $this->belongsTo(Anesthesia::class, 'anesthesia_id');
    }

    public function icu()
    {
        return $this->belongsTo(ICU::class, 'i_c_u_id');
    }

    public function underReview()
    {
        return $this->belongsTo(UnderReview::class, 'under_review_id');
    }

    public function bloodUnits()
    {
        return $this->belongsToMany(BloodUnit::class, 'blood_bank_unit', 'blood_bank_id', 'blood_unit_id')
            ->withPivot(['reserved_at', 'reserved_by', 'crossmatch_id', 'issued_at', 'issued_by'])
            ->withTimestamps();
    }

    public function patientSamples()
    {
        return $this->hasMany(BloodPatientSample::class)->orderByDesc('created_at');
    }

    public function crossmatches()
    {
        return $this->hasMany(BloodCrossmatch::class)->orderByDesc('updated_at');
    }

    /**
     * Optional persisted lab blood check (typing / verification) linked to this request.
     */
    public function bloodCheckRecord()
    {
        return $this->hasOne(BloodCheckRecord::class, 'blood_bank_id');
    }

    /**
     * Rich snapshot of this blood request for UI and services (patient need, ABO/Rh, component, linked IDs).
     */
    public function bloodCheck(): BloodCheck
    {
        return BloodCheck::fromBloodBank($this);
    }

    /**
     * Convert a stored quantity value to a number of blood units (bags).
     * Large values are assumed to be total ml (common data-entry mistake).
     */
    public static function normalizeRawQuantityToUnits(?int $raw): int
    {
        if ($raw === null || $raw < 1) {
            return 0;
        }

        $maxUnitBeforeMl = (int) config('blood_bank.max_unit_order_before_volume_assumption', 100);
        $mlPerBag = max(1, (int) config('blood_bank.ml_per_bag_for_qty_inference', 450));

        if ($raw > $maxUnitBeforeMl) {
            return max(1, (int) ceil($raw / $mlPerBag));
        }

        return $raw;
    }

    /**
     * Bag count for workflow, delivery, and crossmatch progress. Prefers lab blood-check
     * quantity when set; otherwise normalizes {@see $quantity} (ml heuristic).
     */
    public function orderedUnitsForWorkflow(): int
    {
        if ($this->bloodCheckRecord && (int) $this->bloodCheckRecord->quantity >= 1) {
            return self::normalizeRawQuantityToUnits((int) $this->bloodCheckRecord->quantity);
        }

        return self::normalizeRawQuantityToUnits((int) $this->quantity);
    }

    public function approve()
    {
        $this->status = 'approved';
        $this->save();
    }

    public function reject()
    {
        $this->status = 'rejected';
        $this->save();
    }

    public function deliver()
    {
        $this->status = 'delivered';
        $this->save();
    }
}
