<?php

namespace App\Models;

use App\Models\Concerns\HasBillableInvoiceItems;
use App\Models\Concerns\HasInvoices;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Appointment extends Model
{
    use HasBillableInvoiceItems;
    use HasInvoices;
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'department_id',
        'price',
        'branch_id',
        'date',
        'time',
        'is_completed',
        'status_remark',
        'refferal_remarks',
        'clinic_type',
        'processed_by',
        'doctor_reassigned',
    ];

    /**
     * Resolve the fee stored on an appointment from the selected department.
     */
    public static function resolvePriceFromDepartment(?int $departmentId): float
    {
        if (! $departmentId) {
            return 0;
        }

        $price = Department::query()->whereKey($departmentId)->value('price');

        return max(0, (float) ($price ?? 0));
    }

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

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function diagnose()
    {
        return $this->hasMany(Diagnose::class);
    }

    public function labItems()
    {
        return $this->hasMany(LabItem::class)->whereNull('hospitalization_id');
    }

    /**
     * Get patient test registrations for this appointment
     * Using the new PatientTestRegistration system
     */
    public function labs()
    {
        return $this->morphMany(PatientTestRegistration::class, 'testable');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function prescription()
    {
        return $this->hasMany(Prescription::class);
    }

    public function hospitalization()
    {
        return $this->hasMany(Hospitalization::class);
    }

    public function under_reviews()
    {
        return $this->hasMany(UnderReview::class);
    }

    public function anesthesia()
    {
        return $this->hasMany(Anesthesia::class);
    }

    public function anesthesias()
    {
        return $this->hasMany(Anesthesia::class);
    }

    public function new_anesthesias()
    {
        return $this->hasMany(Anesthesia::class)->where('status', 'new');
    }

    public function approved_anesthesias()
    {
        return $this->hasMany(Anesthesia::class)->where('status', 'approved');
    }

    public function rejected_anesthesias()
    {
        return $this->hasMany(Anesthesia::class)->where('status', 'rejected');
    }

    public function icu()
    {
        return $this->hasMany(ICU::class);
    }

    public function pacus()
    {
        return $this->hasMany(PACU::class);
    }

    public function advices()
    {
        return $this->hasMany(Advice::class);
    }

    public function physiotherapyProcedures()
    {
        return $this->hasMany(PhysiotherapyProcedure::class);
    }

    public function referringDoctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the patient test registrations for this appointment
     */
    public function patientTestRegistrations()
    {
        return $this->morphMany(PatientTestRegistration::class, 'testable');
    }

    /**
     * Get dentist registrations for this appointment
     */
    public function dentistRegistrations()
    {
        return $this->hasMany(DentistRegistration::class);
    }

    public function ophthalmologyRegistrations()
    {
        return $this->hasMany(OphthalmologyRegistration::class);
    }

    public function eyeGlassesOrders()
    {
        return $this->hasMany(EyeGlassesOrder::class);
    }

    public function nephrologyRegistrations()
    {
        return $this->hasMany(NephrologyRegistration::class);
    }

    public function bloodBanks()
    {
        return $this->hasMany(BloodBank::class, 'appointment_id');
    }

    public function canChangeDoctor(): bool
    {
        return ! (bool) $this->is_completed && ! (bool) $this->doctor_reassigned;
    }

    /**
     * Department/doctor queues: hide visits that still have an unpaid appointment fee.
     */
    public function scopeWhereVisitFeeCleared(Builder $query): Builder
    {
        return $query->whereDoesntHave('invoices', function ($invoiceQuery) {
            $invoiceQuery
                ->whereIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_ISSUED])
                ->whereHas('items', function ($itemQuery) {
                    $itemQuery
                        ->where('item_type', InvoiceItem::TYPE_APPOINTMENT_FEE)
                        ->where('payment_status', InvoiceItem::PAYMENT_UNPAID);
                });
        });
    }

    public function hasUnpaidVisitFee(): bool
    {
        return $this->invoices()
            ->whereIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_ISSUED])
            ->whereHas('items', function ($itemQuery) {
                $itemQuery
                    ->where('item_type', InvoiceItem::TYPE_APPOINTMENT_FEE)
                    ->where('payment_status', InvoiceItem::PAYMENT_UNPAID);
            })
            ->exists();
    }
}
