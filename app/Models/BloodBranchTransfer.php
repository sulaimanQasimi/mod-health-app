<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class BloodBranchTransfer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'requesting_branch_id',
        'supplying_branch_id',
        'blood_group',
        'rh',
        'component_type',
        'quantity',
        'status',
        'notes',
        'reject_reason',
        'fulfilled_at',
        'fulfilled_by',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'fulfilled_at' => 'datetime',
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

    public function requestingBranch()
    {
        return $this->belongsTo(Branch::class, 'requesting_branch_id');
    }

    public function supplyingBranch()
    {
        return $this->belongsTo(Branch::class, 'supplying_branch_id');
    }

    public function fulfilledByUser()
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
