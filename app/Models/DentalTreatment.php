<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DentalTreatment extends Model
{
    use HasFactory;

    protected $fillable = [
        'dentist_registration_id',
        'dental_chart_id',
        'treatment_type',
        'tooth_number',
        'treatment_description',
        'treatment_date',
        'status',
        'cost',
        'notes',
    ];

    protected $casts = [
        'treatment_date' => 'date',
        'cost' => 'decimal:2',
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

    public function dentalChart()
    {
        return $this->belongsTo(DentalChart::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForChart($query, $chartId)
    {
        return $query->where('dental_chart_id', $chartId);
    }

    public function markCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    public function markInProgress()
    {
        $this->update(['status' => 'in_progress']);
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }
}
