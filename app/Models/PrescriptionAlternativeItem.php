<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class PrescriptionAlternativeItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'prescription_id',
        'prescription_item_id',
        'medicine_id',
        'medicine_type_id',
        'usage_type_id',
        'dosage',
        'frequency',
        'amount',
        'is_delivered',
        'is_selected',
        'notes'
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

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function prescriptionItem()
    {
        return $this->belongsTo(PrescriptionItem::class);
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
