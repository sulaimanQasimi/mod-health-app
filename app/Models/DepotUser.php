<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepotUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'depot_id',
        'user_id',
        'role',
        'permissions',
        'is_active',
        'joined_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'joined_at' => 'datetime',
    ];

    public function depot()
    {
        return $this->belongsTo(Depot::class, 'depot_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
