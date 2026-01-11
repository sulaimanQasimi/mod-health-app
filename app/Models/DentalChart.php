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

    protected $appends = [
        'implant_details',
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

    public function treatments()
    {
        return $this->hasMany(DentalTreatment::class);
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
     * Implant details are stored under the JSON column `measurements['implant']`.
     * Expose a stable attribute name to avoid confusion with the `measurements()` relationship.
     */
    public function getImplantDetailsAttribute(): array
    {
        $measurements = $this->getAttributeValue('measurements');
        if (!is_array($measurements)) {
            return [];
        }
        $implant = $measurements['implant'] ?? [];
        return is_array($implant) ? $implant : [];
    }

    public function setImplantDetailsAttribute($value): void
    {
        $details = is_array($value) ? $value : [];
        $measurements = $this->getAttributeValue('measurements');
        $measurements = is_array($measurements) ? $measurements : [];

        if (empty($details)) {
            unset($measurements['implant']);
        } else {
            $measurements['implant'] = $details;
        }

        $this->setAttribute('measurements', $measurements);
    }

    /**
     * Get tooth name based on FDI notation number
     */
    public function getToothNameAttribute()
    {
        $toothNames = [
            11 => 'Upper Right Central Incisor', // FDI 11
            12 => 'Upper Right Lateral Incisor', // FDI 12
            13 => 'Upper Right Canine', // FDI 13
            14 => 'Upper Right First Premolar', // FDI 14
            15 => 'Upper Right Second Premolar', // FDI 15
            16 => 'Upper Right First Molar', // FDI 16
            17 => 'Upper Right Second Molar', // FDI 17
            18 => 'Upper Right Third Molar', // FDI 18
            21 => 'Upper Left Central Incisor', // FDI 21
            22 => 'Upper Left Lateral Incisor', // FDI 22
            23 => 'Upper Left Canine', // FDI 23
            24 => 'Upper Left First Premolar', // FDI 24
            25 => 'Upper Left Second Premolar', // FDI 25
            26 => 'Upper Left First Molar', // FDI 26
            27 => 'Upper Left Second Molar', // FDI 27
            28 => 'Upper Left Third Molar', // FDI 28
            31 => 'Lower Left Central Incisor', // FDI 31
            32 => 'Lower Left Lateral Incisor', // FDI 32
            33 => 'Lower Left Canine', // FDI 33
            34 => 'Lower Left First Premolar', // FDI 34
            35 => 'Lower Left Second Premolar', // FDI 35
            36 => 'Lower Left First Molar', // FDI 36
            37 => 'Lower Left Second Molar', // FDI 37
            38 => 'Lower Left Third Molar', // FDI 38
            41 => 'Lower Right Central Incisor', // FDI 41
            42 => 'Lower Right Lateral Incisor', // FDI 42
            43 => 'Lower Right Canine', // FDI 43
            44 => 'Lower Right First Premolar', // FDI 44
            45 => 'Lower Right Second Premolar', // FDI 45
            46 => 'Lower Right First Molar', // FDI 46
            47 => 'Lower Right Second Molar', // FDI 47
            48 => 'Lower Right Third Molar', // FDI 48
        ];

        return $toothNames[$this->tooth_number] ?? "FDI {$this->tooth_number}";
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

    /**
     * Get related treatments for this chart's tooth
     * Returns treatments linked to this chart and treatments for the same tooth
     */
    public function getRelatedTreatments()
    {
        return DentalTreatment::where(function ($query) {
            $query->where('dental_chart_id', $this->id)
                ->orWhere(function ($q) {
                    $q->where('dentist_registration_id', $this->dentist_registration_id)
                      ->where('tooth_number', $this->tooth_number);
                });
        })->get();
    }
}
