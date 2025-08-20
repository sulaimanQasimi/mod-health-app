<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Income extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'medicine_id',
        'amount',
        'batch_number',
        'expiry_date',
        'supplier_name',
        'supplier_contact',
        'purchase_price',
        'purchase_date',
        'invoice_number',
        'notes',
        'income_type', // 'purchase', 'return', 'donation', etc.
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'purchase_date' => 'date',
        'amount' => 'integer',
        'purchase_price' => 'decimal:2'
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

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
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

    // Helper method to check if medicine is expired
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    // Helper method to check if medicine is expiring soon (within 30 days)
    public function isExpiringSoon()
    {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= 30;
    }

    // Helper method to get total value of this income
    public function getTotalValue()
    {
        return $this->amount * $this->purchase_price;
    }
}
