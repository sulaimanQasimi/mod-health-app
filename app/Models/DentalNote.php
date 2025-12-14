<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DentalNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'dentist_registration_id',
        'note_date',
        'note_type',
        'content',
    ];

    protected $casts = [
        'note_date' => 'date',
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

    public function scopeByType($query, $type)
    {
        return $query->where('note_type', $type);
    }
}
