<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DailyIcuProgress extends Model
{
    use HasFactory;

    protected $fillable = [
    'i_c_u_id','icu_day','icu_diagnose','daily_events','hr','bp','spo2','t','rr','gcs','cvs','pupils','s1s2','rs','gi','renal','musculoskeletal_system','extremities','lab_ids','assesment','plan'];
    
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
