<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'medicine_type_id',
        'medicine_country_id',
        'medicine_formula_id',
        'medicine_gram_ml_id',
        'unit_id',
        'units_per_pack',
    ];

    protected $casts = [
        'units_per_pack' => 'integer',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function medicineType(): BelongsTo
    {
        return $this->belongsTo(MedicineType::class);
    }

    public function medicineCountry(): BelongsTo
    {
        return $this->belongsTo(MedicineCountry::class);
    }

    public function medicineFormula(): BelongsTo
    {
        return $this->belongsTo(MedicineFormula::class);
    }

    public function medicineGramMl(): BelongsTo
    {
        return $this->belongsTo(MedicineGramMl::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function unitsPerPack(): int
    {
        return max(1, (int) ($this->units_per_pack ?? 1));
    }

    public function prescriptionStocks(): HasMany
    {
        return $this->hasMany(PrescriptionStock::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function outcomes(): HasMany
    {
        return $this->hasMany(Outcome::class);
    }

    public function diabetesCharts(): HasMany
    {
        return $this->hasMany(DiabetesChart::class);
    }

    public function medicationAdministrationRecords(): HasMany
    {
        return $this->hasMany(MedicationAdministrationRecord::class);
    }

    public function pharmacyFulfillments(): HasMany
    {
        return $this->hasMany(PharmacyFulfillment::class);
    }
}
