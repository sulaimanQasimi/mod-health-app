<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Patient extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'last_name',
        'phone',
        'age',
        'father_name',
        'nid',
        'province_id',
        'district_id',
        'referral_by',
        'image',
        'branch_id',
        'job',
        'rank',
        'relation_id',
        'job_type',
        'gender',
        'referral_name',
        'referral_last_name',
        'referral_father_name',
        'referral_nid',
        'referral_id_card',
        'referral_phone',
        'referral_recipient',
        'type',
        'id_card',
        'job_category',
        'referred_by',
        'registration_date',
        'militery_type_id',
    ];


    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $user = Auth::user();
            $model->registration_date = now();
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

    ############################################
    // probably add doctors relationship as well
    ############################################
    public function doctor()
    {
        return $this->belongsTo(User::class);
    }

    public function diagnoses()
    {
        return $this->hasMany(Diagnose::class);
    }

    /**
     * Get all patient test registrations through polymorphic relationships
     * This includes test registrations from appointments, hospitalizations, under_reviews, ICUs, etc.
     */
    public function getLabsAttribute()
    {
        // Get appointment IDs for this patient
        $appointmentIds = $this->appointments()->pluck('id')->toArray();
        
        // Get hospitalization IDs for this patient
        $hospitalizationIds = $this->hospitalizations()->pluck('id')->toArray();
        
        // Get under_review IDs through appointments
        $underReviewIds = UnderReview::whereIn('appointment_id', $appointmentIds)->pluck('id')->toArray();
        
        // Get ICU IDs through appointments and directly through patient_id
        $icuIdsFromAppointments = ICU::whereIn('appointment_id', $appointmentIds)->pluck('id')->toArray();
        $icuIdsFromPatient = ICU::where('patient_id', $this->id)->pluck('id')->toArray();
        $icuIds = array_unique(array_merge($icuIdsFromAppointments, $icuIdsFromPatient));
        
        // Return collection of PatientTestRegistration matching any of these testable relationships
        return PatientTestRegistration::where(function($query) use ($appointmentIds, $hospitalizationIds, $underReviewIds, $icuIds) {
            $query->where(function($q) use ($appointmentIds) {
                $q->where('testable_type', Appointment::class)
                  ->whereIn('testable_id', $appointmentIds);
            })
            ->orWhere(function($q) use ($hospitalizationIds) {
                $q->where('testable_type', Hospitalization::class)
                  ->whereIn('testable_id', $hospitalizationIds);
            })
            ->orWhere(function($q) use ($underReviewIds) {
                $q->where('testable_type', UnderReview::class)
                  ->whereIn('testable_id', $underReviewIds);
            })
            ->orWhere(function($q) use ($icuIds) {
                if (!empty($icuIds)) {
                    $q->where('testable_type', ICU::class)
                      ->whereIn('testable_id', $icuIds);
                }
            });
        })->get();
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function printedNumbers()
    {
        return $this->hasMany(PrintedNumber::class);
    }
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function relation()
    {
        return $this->belongsTo(Relation::class, 'relation_id');
    }

    public function recipient()
    {
        return $this->belongsTo(Recipient::class, 'referred_by');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function hospitalizations()
    {
        return $this->hasMany(Hospitalization::class);
    }

    public function icus()
    {
        return $this->hasMany(ICU::class);
    }

    public function militeryType()
    {
        return $this->belongsTo(MiliteryType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
