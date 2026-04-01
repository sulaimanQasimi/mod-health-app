<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'blood_unit_id',
        'movement_type',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
    ];

    public function bloodUnit()
    {
        return $this->belongsTo(BloodUnit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
