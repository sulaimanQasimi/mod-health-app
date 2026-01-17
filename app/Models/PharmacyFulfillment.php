<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PharmacyFulfillment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'medicine_id',
        'unit_type',
        'amount',
        'form_no',
        'date',
        'form',
        'pharmacy_id',
        'user_id',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'date' => 'date',
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
            
            // Delete the physical file when model is deleted
            if ($model->form && Storage::disk('public')->exists($model->form)) {
                Storage::disk('public')->delete($model->form);
            }
        });
    }

    /**
     * Get the medicine that this fulfillment is for
     */
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * Get the pharmacy that this fulfillment belongs to
     */
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    /**
     * Get the user who created this fulfillment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who created this record
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated this record
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this record
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the full URL for the form PDF file
     */
    public function getFormUrlAttribute()
    {
        if ($this->form) {
            return Storage::disk('public')->url($this->form);
        }
        return null;
    }

    /**
     * Delete the physical form file
     */
    public function deleteFormFile()
    {
        if ($this->form && Storage::disk('public')->exists($this->form)) {
            Storage::disk('public')->delete($this->form);
        }
    }
}
