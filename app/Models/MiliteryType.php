<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiliteryType extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }
    
}
