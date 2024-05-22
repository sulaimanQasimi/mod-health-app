<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'status',
        'avatar',
        'branch_id',
        'department_id',
        'section_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'recipients' => 'array',
    ];

    public function branch()
    {
        return $this->hasOne(Branch::class, 'id', 'branch_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'id');
    }

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'doctor_id', 'id');
    }

    public function anesthesias()
    {
        return $this->hasMany(Anesthesia::class, 'operation_doctor_id', 'id');
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

    public function labs()
    {
        return $this->hasMany(Lab::class, 'doctor_id', 'id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'doctor_id', 'id');
    }

    public function visits()
    {
        return $this->hasMany(Visit::class, 'doctor_id', 'id');
    }



}
