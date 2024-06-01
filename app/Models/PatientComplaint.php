<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientComplaint extends Model
{
    use HasFactory;

    protected $fillable  =['description','hospitalization_id'];
    
}
