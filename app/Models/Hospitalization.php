<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Hospitalization extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['reason','remarks','appointment_id','doctor_id','patient_id','room_id','bed_id',
    'food_type_id','is_discharged','branch_id','department_id','discharge_remark','discharge_status',
    'patinet_companion','companion_father_name','relation_to_patient','companion_card_type','discharged_at','under_review_id','i_c_u_id'];

    /**
     * Admin and super_admin bypass department filtering; other users only see rows
     * where hospitalization.department_id matches their profile department.
     */
    public function scopeVisibleForAuthUserDepartment($query)
    {
        $user = Auth::user();
        if (! $user) {
            return $query->whereRaw('0 = 1');
        }

        if (static::userBypassesDepartmentScope($user)) {
            return $query;
        }

        if ($user->department_id === null) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where('department_id', $user->department_id);
    }

    public static function userBypassesDepartmentScope(?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        return $user?->hasRole(['admin', 'super_admin']) ?? false;
    }

    /**
     * Whether the given user may view this hospitalization (same branch; admin roles see all in branch).
     */
    public function userCanView(?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        if (! $user) {
            return false;
        }
        if ((int) $this->branch_id !== (int) $user->branch_id) {
            return false;
        }
        if (static::userBypassesDepartmentScope($user)) {
            return true;
        }
        if ($user->department_id === null || $this->department_id === null) {
            return false;
        }

        return (int) $this->department_id === (int) $user->department_id;
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $user = Auth::user();
            $model->created_by = $user->id ?? 0;

            if (empty($model->department_id) && ! empty($model->appointment_id)) {
                $appointment = Appointment::find($model->appointment_id);
                if ($appointment?->department_id) {
                    $model->department_id = $appointment->department_id;
                }
            }

            if (empty($model->department_id) && ! empty($model->room_id)) {
                $room = Room::find($model->room_id);
                if ($room?->department_id) {
                    $model->department_id = $room->department_id;
                }
            }

            // Validate doctor_id exists in doctors table if provided
            if (!empty($model->doctor_id) && !Doctor::where('id', $model->doctor_id)->exists()) {
                $model->doctor_id = null;
            }
            
            // Automatically get doctor_id from appointment if not provided
            if (empty($model->doctor_id) && !empty($model->appointment_id)) {
                // Try to get from relationship if already loaded, otherwise query
                if ($model->relationLoaded('appointment') && $model->appointment) {
                    $appointmentDoctorId = $model->appointment->doctor_id;
                    // Verify the doctor exists in doctors table
                    if ($appointmentDoctorId && Doctor::where('id', $appointmentDoctorId)->exists()) {
                        $model->doctor_id = $appointmentDoctorId;
                    }
                } else {
                    $appointment = Appointment::find($model->appointment_id);
                    if ($appointment && $appointment->doctor_id) {
                        // Verify the doctor exists in doctors table
                        if (Doctor::where('id', $appointment->doctor_id)->exists()) {
                            $model->doctor_id = $appointment->doctor_id;
                        }
                    }
                }
            }
            
            // If still no doctor_id and user has a doctor relationship, use it
            if (empty($model->doctor_id) && $user && $user->doctor) {
                $model->doctor_id = $user->doctor->id;
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

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function bloodBanks()
    {
        return $this->hasMany(BloodBank::class, 'hospitalization_id');
    }

    public function labs()
    {
        return $this->morphMany(PatientTestRegistration::class, 'testable');
    }

    public function icu()
    {
        return $this->hasMany(ICU::class);
    }

    public function anesthesias()
    {
        return $this->hasMany(Anesthesia::class);
    }

    public function complaints()
    {
        return $this->hasMany(PatientComplaint::class);
    }

    public function advices()
    {
        return $this->hasMany(Advice::class);
    }

    public function prescription()
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get the diabetes charts for this hospitalization record.
     */
    public function diabetesCharts()
    {
        return $this->morphMany(DiabetesChart::class, 'diabetes_chartable');
    }

    /**
     * Get the nurse notes for this hospitalization record.
     */
    public function nurseNotes()
    {
        return $this->morphMany(NurseNote::class, 'morphable');
    }

    /**
     * Get the medication administration records for this hospitalization record.
     */
    public function medicationAdministrationRecords()
    {
        return $this->morphMany(MedicationAdministrationRecord::class, 'morphable');
    }

    /**
     * Get the vital signs for this hospitalization record.
     */
    public function vitalSigns()
    {
        return $this->morphMany(VitalSign::class, 'morphable');
    }

    /**
     * Get the nutrition care records for this hospitalization record.
     */
    public function nutritionCares()
    {
        return $this->morphMany(NutritionCare::class, 'morphable');
    }

    /**
     * Get the nursing assessments for this hospitalization record.
     */
    public function nursingAssessments()
    {
        return $this->morphMany(NursingAssessment::class, 'morphable');
    }
}
