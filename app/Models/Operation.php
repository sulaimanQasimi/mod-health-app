<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    use HasFactory;

    public function bloodBanks()
    {
        return $this->hasMany(BloodBank::class, 'operation_id');
    }
}
