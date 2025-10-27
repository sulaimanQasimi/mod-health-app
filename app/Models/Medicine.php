<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = ['name'];



    public function prescriptionStocks()
    {
        return $this->hasMany(PrescriptionStock::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function outcomes()
    {
        return $this->hasMany(Outcome::class);
    }

    /**
     * Get the diabetes charts for this medicine.
     */
    public function diabetesCharts()
    {
        return $this->hasMany(DiabetesChart::class);
    }

    /**
     * Get the medication administration records for this medicine.
     */
    public function medicationAdministrationRecords()
    {
        return $this->hasMany(MedicationAdministrationRecord::class);
    }
}
