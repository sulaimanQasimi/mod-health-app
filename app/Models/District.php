<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable  = ['id', 'province_id', 'name_en', 'name_dr', 'name_pa'];
}
