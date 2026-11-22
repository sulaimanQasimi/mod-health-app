<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicineGramMl extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'gram', 'ml', 'is_active'];

    protected $casts = [
        'gram' => 'decimal:4',
        'ml' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
