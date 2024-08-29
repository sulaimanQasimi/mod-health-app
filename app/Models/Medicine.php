<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = ['name','medicine_type_id','disease_id'];


    public function medicineType()
    {
        return $this->hasOne(MedicineType::class, 'id','medicine_type_id');
    }

    public function getAssociatedDiseaseAttribute()
    {
        $diseases = array_map('intval', json_decode($this->disease_id, true));
        return Disease::whereIn('id', $diseases)->get();
    }
}
