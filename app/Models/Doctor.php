<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Doctor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'gender',
        'father_name',
        'contact_number',
        'address',
        'specialization',
        'qualification',
        'room_no',
        'clinic_type',
        'join_date',
        'active_status',
        'is_dentist',
        'is_nephrologist',
        'is_eye_doctor',
        'branch_id',
        'department_id',
        'user_id'
    ];

    protected $casts = [
        'join_date' => 'date',
        'active_status' => 'boolean',
        'is_dentist' => 'boolean',
        'is_nephrologist' => 'boolean',
        'is_eye_doctor' => 'boolean',
    ];

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

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function consultation_comments()
    {
        return $this->hasMany(ConsultationComment::class, 'doctor_id', 'id');
    }

    public function hospitalizations()
    {
        return $this->hasMany(Hospitalization::class, 'doctor_id', 'id');
    }

    public function i_c_u_s()
    {
        return $this->hasMany(ICU::class, 'doctor_id', 'id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'doctor_id', 'id');
    }

    public function visits()
    {
        return $this->hasMany(Visit::class, 'doctor_id', 'id');
    }

    public function anesthesias()
    {
        return $this->hasMany(Anesthesia::class, 'doctor_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dentistRegistrations()
    {
        return $this->hasMany(DentistRegistration::class, 'dentist_id');
    }

    public function nephrologyRegistrations()
    {
        return $this->hasMany(NephrologyRegistration::class, 'doctor_id');
    }

    public function ophthalmologyRegistrations()
    {
        return $this->hasMany(OphthalmologyRegistration::class, 'examiner_id');
    }

    public function physiotherapyProcedures()
    {
        return $this->hasMany(PhysiotherapyProcedure::class, 'doctor_id');
    }

}
