<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class PrescriptionStock extends Model
{
       protected $fillable = [
        'medicine_id',
        'medicine_name',
        'medicine_type_name',
        'total_income',
        'total_outcome',
        'current_stock',
        'reserved_stock',
        'available_stock',
        'minimum_stock',
        'maximum_stock',
        'valid_stock',
        'expired_stock',
        'expiring_soon_stock',
        'total_batches',
        'valid_batches',
        'expired_batches',
        'earliest_expiry',
        'last_purchase_date',
        'last_outcome_date',
        'last_updated',
        'notes'
    ];

    protected $casts = [
        'last_updated' => 'datetime',
        'last_purchase_date' => 'date',
        'last_outcome_date' => 'date',
        'earliest_expiry' => 'date',
        'total_income' => 'integer',
        'total_outcome' => 'integer',
        'current_stock' => 'integer',
        'reserved_stock' => 'integer',
        'available_stock' => 'integer',
        'minimum_stock' => 'integer',
        'maximum_stock' => 'integer',
        'valid_stock' => 'integer',
        'expired_stock' => 'integer',
        'expiring_soon_stock' => 'integer',
        'total_batches' => 'integer',
        'valid_batches' => 'integer',
        'expired_batches' => 'integer'
    ];

    // Since this is a view, we can't create/update/delete records
    public $incrementing = false;
    protected $primaryKey = 'medicine_id';

    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function incomes()
    {
        return $this->hasMany(Income::class, 'medicine_id', 'medicine_id');
    }

    public function outcomes()
    {
        return $this->hasMany(Outcome::class, 'medicine_id', 'medicine_id');
    }

    // Helper method to check if stock is low
    public function isLowStock()
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    // Helper method to check if stock is overstocked
    public function isOverstocked()
    {
        return $this->current_stock >= $this->maximum_stock;
    }

    // Helper method to get stock status
    public function getStockStatus()
    {
        if ($this->current_stock <= 0) {
            return 'out_of_stock';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        } elseif ($this->isOverstocked()) {
            return 'overstocked';
        } else {
            return 'normal';
        }
    }

    // Helper method to get stock percentage
    public function getStockPercentage()
    {
        if ($this->maximum_stock <= 0) {
            return 0;
        }
        return round(($this->current_stock / $this->maximum_stock) * 100, 2);
    }

    // Helper method to check if medicine has expired stock
    public function hasExpiredStock()
    {
        return $this->expired_stock > 0;
    }

    // Helper method to check if medicine has stock expiring soon
    public function hasExpiringSoonStock()
    {
        return $this->expiring_soon_stock > 0;
    }

    // Helper method to get valid stock percentage
    public function getValidStockPercentage()
    {
        if ($this->current_stock <= 0) {
            return 0;
        }
        return round(($this->valid_stock / $this->current_stock) * 100, 2);
    }

    // Helper method to get days until earliest expiry
    public function getDaysUntilEarliestExpiry()
    {
        if (!$this->earliest_expiry) {
            return null;
        }
        return now()->diffInDays($this->earliest_expiry, false);
    }

    // Helper method to get comprehensive stock status
    public function getComprehensiveStockStatus()
    {
        if ($this->current_stock <= 0) {
            return 'out_of_stock';
        } elseif ($this->hasExpiredStock()) {
            return 'has_expired_stock';
        } elseif ($this->hasExpiringSoonStock()) {
            return 'expiring_soon';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        } elseif ($this->isOverstocked()) {
            return 'overstocked';
        } else {
            return 'normal';
        }
    }
}
