<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disease extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const NEPHROLOGY_DEPARTMENT_NAME = 'نفرولوژی';

    protected $fillable = ['name', 'description', 'department_id', 'disease_category_id'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function category()
    {
        return $this->belongsTo(DiseaseCategory::class, 'disease_category_id');
    }

    public function nephrologyRegistrations()
    {
        return $this->hasMany(NephrologyRegistration::class);
    }

    public function scopeForDepartmentName($query, string $name)
    {
        return $query->whereHas('department', fn ($q) => $q->where('name', $name));
    }

    public static function forNephrology()
    {
        return static::forDepartmentName(static::NEPHROLOGY_DEPARTMENT_NAME)->orderBy('name');
    }
}
