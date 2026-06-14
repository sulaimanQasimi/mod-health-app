<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class UnderReview extends Model
{

    use SoftDeletes;
    
    protected $fillable = ['reason','remarks','appointment_id','doctor_id','patient_id','room_id','bed_id','is_discharged','branch_id','department_id','discharge_remark','operation_id','hospitalization_id','prescription_id','processed_by'];

    /**
     * Admin and super_admin bypass department filtering; other users only see rows
     * where under_review.department_id matches their profile department.
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
        });

        self::updating(function ($model) {
            $user = Auth::user();
            $model->updated_by = $user->id ?? 0;
        });

        self::updated(function (UnderReview $model) {
            if ($model->wasChanged('is_discharged') && (bool) $model->is_discharged && $model->bed_id) {
                Bed::query()->whereKey($model->bed_id)->update(['is_occupied' => 0]);
            }
        });

        self::deleting(function ($model) {
            if (! (bool) $model->is_discharged && $model->bed_id) {
                Bed::query()->whereKey($model->bed_id)->update(['is_occupied' => 0]);
            }

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
        return $this->belongsTo(Doctor::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function prescription()
    {
        return $this->hasMany(Prescription::class);
    }
    
    public function hospitalization()
    {
        return $this->hasMany(Hospitalization::class);
    }

    /**
     * Get the diabetes charts for this under review record.
     */
    public function diabetesCharts()
    {
        return $this->morphMany(DiabetesChart::class, 'diabetes_chartable');
    }

    /**
     * Get the nurse notes for this under review record.
     */
    public function nurseNotes()
    {
        return $this->morphMany(NurseNote::class, 'morphable');
    }

    /**
     * Get the medication administration records for this under review record.
     */
    public function medicationAdministrationRecords()
    {
        return $this->morphMany(MedicationAdministrationRecord::class, 'morphable');
    }

    /**
     * Get the vital signs for this under review record.
     */
    public function vitalSigns()
    {
        return $this->morphMany(VitalSign::class, 'morphable');
    }

    /**
     * Get the nutrition care records for this under review record.
     */
    public function nutritionCares()
    {
        return $this->morphMany(NutritionCare::class, 'morphable');
    }

    /**
     * Get the nursing assessments for this under review record.
     */
    public function nursingAssessments()
    {
        return $this->morphMany(NursingAssessment::class, 'morphable');
    }
}
