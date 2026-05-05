<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class Prescription extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['branch_id', 'appointment_id', 'patient_id','doctor_id','is_completed','under_review_id','hospitalization_id','i_c_u_id','pharmacy_id'];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $user = Auth::user();
            $model->created_by = $user->id ?? 0;
        });

        self::updating(function ($model) {
            $user = Auth::user();
            $model->updated_by = $user->id;

        // Check if user belongs to a pharmacy
        $userPharmacy = auth()->user()->activePharmacies()->first();
        if ($userPharmacy) {
            $model->pharmacy_id = $userPharmacy->id;
        }
        });

        self::deleting(function ($model) {
            $user = Auth::user();
            $model->deleted_by = $user->id ?? 0;
            $model->save();
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

    public function prescriptionItems()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeVisibleToClinicType(Builder $query, ?string $viewerClinicType): Builder
    {
        if (!$viewerClinicType || $viewerClinicType === 'both') {
            return $query;
        }

        // Only prescriptions created by a user with the same clinic_type, or by a "both" user.
        // Prescriptions with missing/orphaned creator are excluded by default (privacy-safe).
        return $query->whereHas('creator', function (Builder $q) use ($viewerClinicType) {
            $q->whereIn('clinic_type', [$viewerClinicType, 'both']);
        });
    }

}
