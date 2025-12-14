<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DentalChart extends Model
{
    use HasFactory;

    protected $fillable = [
        'dentist_registration_id',
        'tooth_number',
        'tooth_condition',
        'gum_health',
        'oral_hygiene_score',
        'pocket_depth',
        'bleeding',
        'mobility',
        'treatment_history',
        'measurements',
        'chart_date',
        'notes',
    ];

    protected $casts = [
        'oral_hygiene_score' => 'decimal:1',
        'pocket_depth' => 'decimal:2',
        'bleeding' => 'boolean',
        'chart_date' => 'date',
        'measurements' => 'array',
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

    public function measurements()
    {
        return $this->hasMany(DentalChartMeasurement::class);
    }

    public function images()
    {
        return $this->hasMany(DentalChartImage::class);
    }

    public function periodontalMeasurements()
    {
        return $this->hasMany(DentalPeriodontalMeasurement::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get tooth name based on number
     */
    public function getToothNameAttribute()
    {
        $toothNames = [
            11 => 'Upper Right Central Incisor',
            12 => 'Upper Right Lateral Incisor',
            13 => 'Upper Right Canine',
            14 => 'Upper Right First Premolar',
            15 => 'Upper Right Second Premolar',
            16 => 'Upper Right First Molar',
            17 => 'Upper Right Second Molar',
            18 => 'Upper Right Third Molar',
            21 => 'Upper Left Central Incisor',
            22 => 'Upper Left Lateral Incisor',
            23 => 'Upper Left Canine',
            24 => 'Upper Left First Premolar',
            25 => 'Upper Left Second Premolar',
            26 => 'Upper Left First Molar',
            27 => 'Upper Left Second Molar',
            28 => 'Upper Left Third Molar',
            31 => 'Lower Left Central Incisor',
            32 => 'Lower Left Lateral Incisor',
            33 => 'Lower Left Canine',
            34 => 'Lower Left First Premolar',
            35 => 'Lower Left Second Premolar',
            36 => 'Lower Left First Molar',
            37 => 'Lower Left Second Molar',
            38 => 'Lower Left Third Molar',
            41 => 'Lower Right Central Incisor',
            42 => 'Lower Right Lateral Incisor',
            43 => 'Lower Right Canine',
            44 => 'Lower Right First Premolar',
            45 => 'Lower Right Second Premolar',
            46 => 'Lower Right First Molar',
            47 => 'Lower Right Second Molar',
            48 => 'Lower Right Third Molar',
        ];

        return $toothNames[$this->tooth_number] ?? "Tooth {$this->tooth_number}";
    }

    /**
     * Scope to get charts by date
     */
    public function scopeByDate($query, $date)
    {
        return $query->where('chart_date', $date);
    }

    /**
     * Scope to get latest chart per tooth
     */
    public function scopeLatestPerTooth($query)
    {
        return $query->whereIn('id', function ($subquery) {
            $subquery->selectRaw('MAX(id)')
                ->from('dental_charts')
                ->groupBy('dentist_registration_id', 'tooth_number');
        });
    }
}
