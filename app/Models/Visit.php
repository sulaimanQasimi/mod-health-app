<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en','description','hospitalization_id','patient_id','doctor_id'
    ];

    public function hospitalization()
    {
        return $this->belongsTo(Hospitalization::class);
    }
}
