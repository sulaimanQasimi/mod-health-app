<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class BloodDonation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'blood_donor_id',
        'phlebotomy_at',
        'donor_blood_group',
        'donor_rh',
        'phlebotomy_site',
        'phlebotomist_name',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'phlebotomy_at' => 'datetime',
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

    public function donor()
    {
        return $this->belongsTo(BloodDonor::class, 'blood_donor_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function samples()
    {
        return $this->hasMany(BloodSample::class, 'blood_donation_id');
    }

    public function units()
    {
        return $this->hasMany(BloodUnit::class, 'donation_id');
    }
}

