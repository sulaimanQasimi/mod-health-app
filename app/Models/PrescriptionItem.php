<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class PrescriptionItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['prescription_id','dosage','frequency','amount','medicine_id','medicine_type_id', 'is_delivered','usage_type_id'];

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

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class);
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function medicineType()
    {
        return $this->belongsTo(MedicineType::class);
    }
    public function usageType()
    {
        return $this->belongsTo(MedicineUsageType::class);
    }

    public function alternativeItems()
    {
        return $this->hasMany(PrescriptionAlternativeItem::class);
    }

    public function selectedAlternative()
    {
        return $this->hasOne(PrescriptionAlternativeItem::class)->where('is_selected', 1);
    }

    public function prescriptionStock()
    {
        return $this->hasOne(PrescriptionStock::class, 'medicine_id', 'medicine_id');
    }

    public function outcomes()
    {
        return $this->hasMany(Outcome::class);
    }
}
