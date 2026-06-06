<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\BloodDonation;
use App\Models\BloodUnitTest;

class BloodUnit extends Model
{
    use SoftDeletes;

    public const COMPONENT_TYPES = ['Fresh', 'RBC', 'PRBC', 'Platelets', 'Plasma', 'Whole Blood'];

    public const STATUSES = ['available', 'reserved', 'issued', 'discarded', 'quarantine'];

    protected $fillable = [
        'branch_id',
        'donation_id',
        'blood_group',
        'rh',
        'component_type',
        'bag_number',
        'volume_ml',
        'collected_at',
        'expires_at',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
        'expires_at' => 'datetime',
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

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function donation()
    {
        return $this->belongsTo(BloodDonation::class, 'donation_id');
    }

    public function test()
    {
        return $this->hasOne(BloodUnitTest::class, 'blood_unit_id')->latestOfMany('tested_at');
    }

    public function tests()
    {
        return $this->hasMany(BloodUnitTest::class, 'blood_unit_id')
            ->orderByDesc('tested_at')
            ->orderByDesc('id');
    }

    public function stockMovements()
    {
        return $this->hasMany(BloodStockMovement::class);
    }

    public function crossmatches()
    {
        return $this->hasMany(BloodCrossmatch::class, 'blood_unit_id')->orderByDesc('updated_at');
    }

    public function bloodBanks()
    {
        return $this->belongsToMany(BloodBank::class, 'blood_bank_unit', 'blood_unit_id', 'blood_bank_id')
            ->withPivot(['reserved_at', 'reserved_by', 'crossmatch_id', 'issued_at', 'issued_by'])
            ->withTimestamps();
    }
}
