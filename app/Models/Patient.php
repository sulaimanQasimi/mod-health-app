<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'is_vip',
        'id_card',
        'job_category',
        'referred_by',
        'commanded_by',
        'recipient_part_id',
        'referral_recipient_part_id',
        'registration_date',
        'militery_type_id',
    ];

    protected function casts(): array
    {
        return [
            'is_vip' => 'boolean',
        ];
    }

    public function isVipRecord(): bool
    {
        return (bool) ($this->attributes['is_vip'] ?? false);
    }

    public function currentUserCanAccessVip(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasRole(['super_admin', 'admin'])
            || $user->can('access-to-vip');
    }

    protected function shouldMaskVipIdentity(): bool
    {
        return $this->isVipRecord() && ! $this->currentUserCanAccessVip();
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->shouldMaskVipIdentity() ? '********' : $value,
        );
    }

    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->shouldMaskVipIdentity() ? '********' : $value,
        );
    }

    protected function fatherName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->shouldMaskVipIdentity() ? '********' : $value,
        );
    }

    protected function referralName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->shouldMaskVipIdentity() ? '********' : $value,
        );
    }

    protected function referralLastName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->shouldMaskVipIdentity() ? '********' : $value,
        );
    }

    protected function referralFatherName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->shouldMaskVipIdentity() ? '********' : $value,
        );
    }

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
     * Uses a single query with whereExists / patient_id instead of multiple pluck round-trips.
     */
    public function getLabsAttribute()
    {
        $patientId = $this->id;

        return PatientTestRegistration::query()
            ->where(function ($query) use ($patientId) {
                $query->where('patient_id', $patientId)
                    ->orWhere(function ($q) use ($patientId) {
                        $q->where('testable_type', Appointment::class)
                            ->whereExists(function ($sub) use ($patientId) {
                                $sub->selectRaw('1')
                                    ->from('appointments')
                                    ->whereColumn('appointments.id', 'patient_test_registrations.testable_id')
                                    ->where('appointments.patient_id', $patientId)
                                    ->whereNull('appointments.deleted_at');
                            });
                    })
                    ->orWhere(function ($q) use ($patientId) {
                        $q->where('testable_type', Hospitalization::class)
                            ->whereExists(function ($sub) use ($patientId) {
                                $sub->selectRaw('1')
                                    ->from('hospitalizations')
                                    ->whereColumn('hospitalizations.id', 'patient_test_registrations.testable_id')
                                    ->where('hospitalizations.patient_id', $patientId)
                                    ->whereNull('hospitalizations.deleted_at');
                            });
                    })
                    ->orWhere(function ($q) use ($patientId) {
                        $q->where('testable_type', UnderReview::class)
                            ->whereExists(function ($sub) use ($patientId) {
                                $sub->selectRaw('1')
                                    ->from('under_reviews')
                                    ->whereColumn('under_reviews.id', 'patient_test_registrations.testable_id')
                                    ->where(function ($inner) use ($patientId) {
                                        $inner->where('under_reviews.patient_id', $patientId)
                                            ->orWhereExists(function ($appt) use ($patientId) {
                                                $appt->selectRaw('1')
                                                    ->from('appointments')
                                                    ->whereColumn('appointments.id', 'under_reviews.appointment_id')
                                                    ->where('appointments.patient_id', $patientId)
                                                    ->whereNull('appointments.deleted_at');
                                            });
                                    })
                                    ->whereNull('under_reviews.deleted_at');
                            });
                    })
                    ->orWhere(function ($q) use ($patientId) {
                        $q->where('testable_type', ICU::class)
                            ->whereExists(function ($sub) use ($patientId) {
                                $sub->selectRaw('1')
                                    ->from('i_c_u_s')
                                    ->whereColumn('i_c_u_s.id', 'patient_test_registrations.testable_id')
                                    ->where(function ($inner) use ($patientId) {
                                        $inner->where('i_c_u_s.patient_id', $patientId)
                                            ->orWhereExists(function ($appt) use ($patientId) {
                                                $appt->selectRaw('1')
                                                    ->from('appointments')
                                                    ->whereColumn('appointments.id', 'i_c_u_s.appointment_id')
                                                    ->where('appointments.patient_id', $patientId)
                                                    ->whereNull('appointments.deleted_at');
                                            });
                                    })
                                    ->whereNull('i_c_u_s.deleted_at');
                            });
                    });
            })
            ->get();
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

    public function recipientPart()
    {
        return $this->belongsTo(RecipientPart::class, 'recipient_part_id');
    }

    public function referralRecipientPart()
    {
        return $this->belongsTo(RecipientPart::class, 'referral_recipient_part_id');
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

    public function prostheticReferrals()
    {
        return $this->hasMany(ProstheticReferral::class);
    }

    public function prostheticCases()
    {
        return $this->hasMany(ProstheticCase::class);
    }

    public function hemodialysisSessions()
    {
        return $this->hasMany(HemodialysisSession::class);
    }

    public function nephrologyRegistrations()
    {
        return $this->hasMany(NephrologyRegistration::class);
    }

    public function foreignCountryReferrals()
    {
        return $this->hasMany(ForeignCountryReferral::class);
    }
}
