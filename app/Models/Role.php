<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as ParentRole;

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

    /**
     * @param  array<int|string>|string|null  $permissions
     */
    public function syncPermissionIds(array|string|null $permissions): static
    {
        $ids = collect(is_array($permissions) ? $permissions : ($permissions ? [$permissions] : []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $this->syncPermissions(
            Permission::query()->whereIn('id', $ids)->get()
        );

        return $this;
    }
}
