<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintedNumber extends Model
{
    use HasFactory;
    protected $fillable = ['patient_id', 'number', 'date'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
