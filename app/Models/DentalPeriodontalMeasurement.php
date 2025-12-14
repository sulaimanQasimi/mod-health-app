<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DentalPeriodontalMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'dental_chart_id',
        'measurement_point',
        'pocket_depth',
        'recession',
        'bleeding',
        'plaque',
        'measurement_date',
        'notes',
    ];

    protected $casts = [
        'pocket_depth' => 'decimal:2',
        'recession' => 'decimal:2',
        'bleeding' => 'boolean',
        'plaque' => 'boolean',
        'measurement_date' => 'date',
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

    /**
     * Get measurement point label
     */
    public function getMeasurementPointLabelAttribute()
    {
        $labels = [
            'mesial' => 'Mesial',
            'mid_mesial' => 'Mid-Mesial',
            'mid' => 'Mid',
            'mid_distal' => 'Mid-Distal',
            'distal' => 'Distal',
            'lingual' => 'Lingual',
            'palatal' => 'Palatal',
        ];

        return $labels[$this->measurement_point] ?? ucfirst($this->measurement_point);
    }

    /**
     * Get health status based on pocket depth
     */
    public function getHealthStatusAttribute()
    {
        if ($this->pocket_depth < 3) {
            return 'healthy';
        } elseif ($this->pocket_depth <= 5) {
            return 'moderate';
        } else {
            return 'severe';
        }
    }
}
