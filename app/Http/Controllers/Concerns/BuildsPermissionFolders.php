<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Permission;
use Illuminate\Support\Collection;

trait BuildsPermissionFolders
{
    /**
     * Group permissions into folders using parent_id when available,
     * otherwise by related module name (e.g. users, depot, roles).
     */
    public function getPermissionFolders(): Collection
    {
        $all = Permission::orderBy('id')->get();
        $byParent = $all->groupBy(fn ($permission) => $permission->parent_id);
        $folders = collect();
        $usedIds = collect();

        foreach ($byParent->get(null, collect()) as $parent) {
            $children = $byParent->get($parent->id, collect());

            if ($children->isEmpty()) {
                continue;
            }

            $items = collect([$parent])->merge($children)->values();
            $groupKey = $this->inferPermissionGroupKey($parent->name);
            $folders->push([
                'key' => 'parent-' . $parent->id,
                'title' => $this->inferPermissionGroupTitle($groupKey, $items, $parent),
                'permissions' => $items,
            ]);
            $usedIds = $usedIds->merge($items->pluck('id'));
        }

        $remaining = $all->whereNotIn('id', $usedIds->unique()->all())->values();

        foreach ($remaining->groupBy(fn ($permission) => $this->inferPermissionGroupKey($permission->name)) as $key => $permissions) {
            $folders->push([
                'key' => 'group-' . $key,
                'title' => $this->inferPermissionGroupTitle($key, $permissions),
                'permissions' => $permissions->values(),
            ]);
        }

        return $folders->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    protected function inferPermissionGroupKey(string $name): string
    {
        if (str_contains($name, '.')) {
            return explode('.', $name)[0];
        }

        $key = preg_replace('/^(show|create|edit|delete|manage|deactivate|issue|receive|add|update|view)-/', '', $name);
        $key = preg_replace('/-menu$/', '', $key);

        return $key ?: $name;
    }

    protected function inferPermissionGroupTitle(string $key, $permissions, $parent = null): string
    {
        $locale = current_locale();

        if ($locale !== 'en') {
            $dbTitle = $this->folderTitleFromPermissions($permissions, $parent);
            if ($dbTitle) {
                return $dbTitle;
            }
        }

        $fromLang = localize_from_key($key);
        if ($fromLang !== null) {
            return $fromLang;
        }

        if ($parent) {
            $fromParentName = localize_from_key($parent->name);
            if ($fromParentName !== null) {
                return $fromParentName;
            }
        }

        $dbTitle = $this->folderTitleFromPermissions($permissions, $parent);
        if ($dbTitle) {
            return $dbTitle;
        }

        return ucwords(str_replace(['-', '_'], ' ', $key));
    }

    protected function folderTitleFromPermissions($permissions, $parent = null): ?string
    {
        $menuPermission = $permissions->first(function ($permission) {
            return str_ends_with($permission->name, '-menu')
                || str_ends_with($permission->name, '.view')
                || str_starts_with($permission->name, 'show-');
        });

        if ($menuPermission) {
            $label = $this->dbPermissionLabel($menuPermission);
            if ($label) {
                return $label;
            }
        }

        if ($parent) {
            $label = $this->dbPermissionLabel($parent);
            if ($label) {
                return $label;
            }
        }

        foreach ($permissions as $permission) {
            $label = $this->dbPermissionLabel($permission);
            if ($label) {
                return $label;
            }
        }

        return null;
    }

    protected function dbPermissionLabel($permission): ?string
    {
        if (current_locale() === 'en') {
            return $permission->name ?: ($permission->name_dr ?: null);
        }

        return $permission->name_dr ?: ($permission->name ?: null);
    }
}
