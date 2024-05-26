<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en','description','hospitalization_id','patient_id','doctor_id','i_c_u_id','under_review_id'
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

    public function hospitalization()
    {
        return $this->belongsTo(Hospitalization::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class);
    }

    public function icu()
    {
        return $this->belongsTo(ICU::class);
    }

    public function under_review()
    {
        return $this->belongsTo(UnderReview::class);
    }
}
