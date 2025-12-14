<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DentalXray extends Model
{
    use HasFactory;

    protected $fillable = [
        'dentist_registration_id',
        'xray_type',
        'xray_date',
        'file_path',
        'description',
        'notes',
    ];

    protected $casts = [
        'xray_date' => 'date',
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
    }

    public function dentistRegistration()
    {
        return $this->belongsTo(DentistRegistration::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
