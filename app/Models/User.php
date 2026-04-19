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
        'category_id',
        'is_doctor',
        'clinic_type',
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
        'is_doctor' => 'boolean',
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

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'department_id', 'id');
    }

    public function anesthesias()
    {
        return $this->hasMany(Anesthesia::class, 'doctor_id', 'id');
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
        return $this->hasMany(LabItem::class, 'doctor_id', 'id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'doctor_id', 'id');
    }

    public function visits()
    {
        return $this->hasMany(Visit::class, 'doctor_id', 'id');
    }

    // Many-to-many relationship with pharmacies
    public function pharmacies()
    {
        return $this->belongsToMany(Pharmacy::class, 'pharmacy_users', 'user_id', 'pharmacy_id')
                    ->withPivot(['role', 'permissions', 'is_active', 'joined_at'])
                    ->withTimestamps();
    }

    // Get active pharmacies for this user
    public function activePharmacies()
    {
        return $this->pharmacies()->wherePivot('is_active', true);
    }

    // Get pharmacies where user is manager
    public function managedPharmacies()
    {
        return $this->pharmacies()->wherePivot('role', 'manager');
    }

    // Check if user has access to a specific pharmacy
    public function hasPharmacyAccess($pharmacyId)
    {
        return $this->pharmacies()->where('pharmacy_id', $pharmacyId)->wherePivot('is_active', true)->exists();
    }

    // Get user's role in a specific pharmacy
    public function getPharmacyRole($pharmacyId)
    {
        $pharmacy = $this->pharmacies()->where('pharmacy_id', $pharmacyId)->wherePivot('is_active', true)->first();
        return $pharmacy ? $pharmacy->pivot->role : null;
    }

    /**
     * Check if user has at least one active pharmacy with one of the given roles.
     *
     * @param  array<string>|string  $roles
     */
    public function hasActivePharmacyRole(array|string $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];
        $roles = array_values(array_filter(array_map('trim', $roles)));

        if (empty($roles)) {
            return false;
        }

        return $this->pharmacies()
            ->wherePivot('is_active', true)
            ->wherePivotIn('role', $roles)
            ->exists();
    }

    // Legacy method for backward compatibility (returns first active pharmacy)
    public function pharmacy()
    {
        return $this->activePharmacies();
    }

    public function physiotherapyProcedures()
    {
        return $this->hasManyThrough(
            PhysiotherapyProcedure::class,
            Doctor::class,
            'user_id',
            'doctor_id',
            'id',
            'id'
        );
    }

    public function nurse()
    {
        return $this->hasOne(Nurse::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function pharmacyFulfillments()
    {
        return $this->hasMany(PharmacyFulfillment::class);
    }
}
