<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicineCountry extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
