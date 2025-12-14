<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DentalChartMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'dental_chart_id',
        'measurement_type',
        'measurement_value',
        'measurement_unit',
        'measurement_date',
        'notes',
    ];

    protected $casts = [
        'measurement_value' => 'decimal:2',
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
}
