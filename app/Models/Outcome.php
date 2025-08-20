<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Outcome extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'medicine_id',
        'amount',
        'prescription_item_id',
        'patient_id',
        'doctor_id',
        'outcome_type', // 'prescription', 'expired', 'damaged', 'lost', 'return'
        'batch_number',
        'reason',
        'outcome_date',
        'notes'
    ];

    protected $casts = [
        'outcome_date' => 'date',
        'amount' => 'integer'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $user = Auth::user();
            $model->created_by = $user->id ?? 0;
            if (!$model->outcome_date) {
                $model->outcome_date = now();
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

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function prescriptionItem()
    {
        return $this->belongsTo(PrescriptionItem::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function prescriptionStock()
    {
        return $this->belongsTo(PrescriptionStock::class, 'medicine_id', 'medicine_id');
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

    // Helper method to check if outcome is prescription-related
    public function isPrescriptionOutcome()
    {
        return $this->outcome_type === 'prescription';
    }

    // Helper method to check if outcome is due to expiration
    public function isExpirationOutcome()
    {
        return $this->outcome_type === 'expired';
    }

    // Helper method to check if outcome is due to damage
    public function isDamageOutcome()
    {
        return $this->outcome_type === 'damaged';
    }

    // Helper method to get outcome description
    public function getOutcomeDescription()
    {
        $descriptions = [
            'prescription' => 'Prescribed to patient',
            'expired' => 'Expired and disposed',
            'damaged' => 'Damaged and disposed',
            'lost' => 'Lost or stolen',
            'return' => 'Returned to supplier'
        ];

        return $descriptions[$this->outcome_type] ?? 'Unknown outcome';
    }
}
