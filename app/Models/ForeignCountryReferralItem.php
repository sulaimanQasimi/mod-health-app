<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ForeignCountryReferralItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'foreign_country_referral_id',
        'doctor_id',
        'diagnosis',
        'doctor_comment',
        'issue_date',
        'expire_date',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expire_date' => 'date',
    ];

    public static function boot(): void
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

    public function referral()
    {
        return $this->belongsTo(ForeignCountryReferral::class, 'foreign_country_referral_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
