<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission as ParentPermission;

class Permission extends ParentPermission
{
    use HasFactory;

    protected $fillable = [
        'id','name','name_dr','name_pa','parent_id','guard_name','created_at','updated_at'
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

}
