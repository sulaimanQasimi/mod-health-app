<?php

namespace App\Models;

use App\Blood\BloodCheck;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class BloodBank extends Model
{
    use SoftDeletes;

    protected $fillable = ['group','branch_id', 'appointment_id', 'patient_id', 'rh', 'type', 'under_review_id', 'operation_id','i_c_u_id','anesthesia_id','hospitalization_id','status','quantity','department_id','receiver_department_id','receiver_nurse_id','reject_reason','created_by','updated_by','deleted_by'];

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

    public function receiverDepartment()
    {
        return $this->belongsTo(Department::class, 'receiver_department_id');
    }

    public function receiverNurse()
    {
        return $this->belongsTo(Nurse::class, 'receiver_nurse_id');
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
     * Bag count for workflow, delivery, and crossmatch progress.
     * Uses the main request line ({@see $quantity}) first — that is the clinical order and
     * drives when the request is marked delivered. Lab blood-check quantity is only used
     * when the main line has no usable value (avoids delivery never completing because the
     * lab row was left higher than the real order).
     */
    public function orderedUnitsForWorkflow(): int
    {
        $rawMain = (int) $this->quantity;
        if ($rawMain >= 1) {
            return self::normalizeRawQuantityToUnits($rawMain);
        }

        if ($this->bloodCheckRecord && (int) $this->bloodCheckRecord->quantity >= 1) {
            return self::normalizeRawQuantityToUnits((int) $this->bloodCheckRecord->quantity);
        }

        return 0;
    }

    /**
     * Raw request line for UI: show ml when the stored value looks like volume, else unit count.
     *
     * @return array{mode: 'empty'|'units'|'volume_ml', ml?: int, units?: int}
     */
    public function orderQuantityDisplayParts(): array
    {
        $raw = (int) $this->quantity;
        if ($raw < 1) {
            return ['mode' => 'empty'];
        }

        $threshold = (int) config('blood_bank.max_unit_order_before_volume_assumption', 100);
        if ($raw > $threshold) {
            return [
                'mode' => 'volume_ml',
                'ml' => $raw,
            ];
        }

        return ['mode' => 'units', 'units' => $raw];
    }

    public function defaultUnitVolumeMl(): int
    {
        return max(1, (int) config('blood_bank.ml_per_bag_for_qty_inference', 450));
    }

    public function effectiveUnitVolumeMl(?BloodUnit $unit): int
    {
        if ($unit !== null && $unit->volume_ml !== null && (int) $unit->volume_ml > 0) {
            return (int) $unit->volume_ml;
        }

        return $this->defaultUnitVolumeMl();
    }

    /**
     * Total ordered volume in ml (raw ml orders, or unit count × default bag volume).
     */
    public function orderedVolumeMl(): int
    {
        $parts = $this->orderQuantityDisplayParts();
        if ($parts['mode'] === 'volume_ml') {
            return (int) $parts['ml'];
        }

        if ($parts['mode'] === 'units') {
            return (int) $parts['units'] * $this->defaultUnitVolumeMl();
        }

        $raw = $this->bloodCheckRecord && (int) $this->bloodCheckRecord->quantity >= 1
            ? (int) $this->bloodCheckRecord->quantity
            : 0;

        if ($raw < 1) {
            return 0;
        }

        $threshold = (int) config('blood_bank.max_unit_order_before_volume_assumption', 100);
        if ($raw > $threshold) {
            return $raw;
        }

        return self::normalizeRawQuantityToUnits($raw) * $this->defaultUnitVolumeMl();
    }

    public function usesVolumeMlTracking(): bool
    {
        return $this->orderQuantityDisplayParts()['mode'] === 'volume_ml';
    }

    /**
     * Sum of issued unit volumes (requires bloodUnits relation).
     */
    public function issuedVolumeMl(): int
    {
        return (int) $this->bloodUnits
            ->filter(fn ($u) => ! is_null($u->pivot?->issued_at))
            ->sum(fn ($u) => $this->effectiveUnitVolumeMl($u));
    }

    public function remainingVolumeMl(): int
    {
        return max(0, $this->orderedVolumeMl() - $this->issuedVolumeMl());
    }

    /**
     * Sum of reserved + compatible unit volumes (requires bloodUnits + crossmatches).
     *
     * @param  list<int>|null  $reservedUnitIds
     */
    public function reservedCompatibleVolumeMl(?array $reservedUnitIds = null): int
    {
        $reservedIds = $reservedUnitIds ?? $this->bloodUnits
            ->filter(fn ($u) => ! is_null($u->pivot?->reserved_at))
            ->pluck('id')
            ->all();

        $compatibleIds = $this->crossmatches
            ->filter(fn ($cx) => in_array($cx->status, ['compatible', 'overridden'], true))
            ->pluck('blood_unit_id')
            ->all();

        return (int) $this->bloodUnits
            ->filter(fn ($u) => in_array($u->id, $reservedIds, true) && in_array($u->id, $compatibleIds, true))
            ->sum(fn ($u) => $this->effectiveUnitVolumeMl($u));
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
