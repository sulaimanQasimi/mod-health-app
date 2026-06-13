<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class LabType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'category_id', 'branch_id', 'department_id'];

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


    /**
     * Get the lab tests for this lab type
     */

    /**
     * Get the lab test parameters directly linked to this lab type
     */
    public function directLabTestParameters()
    {
        return $this->hasMany(LabTestParameter::class, 'lab_type_id');
    }

    /**
     * Get the category that owns this lab type
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the patient test registrations for this lab type
     */
    public function patientTestRegistrations()
    {
        return $this->hasMany(PatientTestRegistration::class);
    }

    /**
     * @param  Builder<LabType>  $query
     * @return Builder<LabType>
     */
    public function scopeForLaboratoryUser(Builder $query, User $user): Builder
    {
        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        if (! $user->department_id) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where('department_id', $user->department_id);
    }
}
