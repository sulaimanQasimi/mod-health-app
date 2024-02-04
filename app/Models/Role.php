<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as ParentRole;
use App\Models\Permission;

class Role extends ParentRole
{
    use HasFactory;
    protected $fillable = [
        'id', 'name', 'name_dr', 'name_pa', 'guard_name', 'recipients', 'sector_id', 'created_at', 'updated_at'
    ];

    protected $casts = [
        'recipients' => 'array',
    ];

    public function scopeUserBasedRole($query)
    {
        $user = currentUser();
        if ($user->sector_id) {
            $query->where('sector_id', $user->sector_id);
        } else {
            return $query;
        }
    }
}
