<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Depot;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\Tool;
use App\Models\Unit;
use App\Models\User;

trait ProvidesDepotFormData
{
    /**
     * @return list<string>
     */
    protected function depotUserRoles(): array
    {
        return ['manager', 'staff', 'procurement', 'viewer'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function depotFormOptions(?Depot $excludeDepot = null): array
    {
        $user = request()->user();
        $depotsQuery = Depot::query()->orderBy('name');

        if ($excludeDepot) {
            $depotsQuery->whereKeyNot($excludeDepot->id);
        }

        if ($user && ! $user->hasRole(['super_admin', 'admin'])) {
            $allowedIds = $user->activeDepots->pluck('id')->all();
            $depotsQuery->whereIn('id', $allowedIds ?: [0]);
        }

        $activeDepotsQuery = (clone $depotsQuery)
            ->where('is_active', true)
            ->with(['branch:id,name', 'department:id,name', 'pharmacy:id,name']);

        return [
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name'])->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
            ])->values()->all(),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name'])->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
            ])->values()->all(),
            'pharmacies' => Pharmacy::query()->orderBy('name')->get(['id', 'name'])->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
            ])->values()->all(),
            'depots' => $depotsQuery->get(['id', 'name'])->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
            ])->values()->all(),
            'activeDepots' => $activeDepotsQuery->get(['id', 'name', 'pharmacy_id', 'parent_depot_id', 'branch_id', 'department_id'])->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'pharmacy_id' => $item->pharmacy_id,
                'parent_depot_id' => $item->parent_depot_id,
                'branch_id' => $item->branch_id,
                'department_id' => $item->department_id,
                'branch_name' => $item->branch?->name,
                'department_name' => $item->department?->name,
                'pharmacy_name' => $item->pharmacy?->name,
            ])->values()->all(),
            'medicines' => Medicine::query()->whereNull('deleted_at')->orderBy('name')->get(['id', 'name'])->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
            ])->values()->all(),
            'tools' => Tool::query()->where('is_active', true)->orderBy('name')->get(['id', 'name'])->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
            ])->values()->all(),
            'units' => Unit::query()->where('is_active', true)->orderBy('name')->get(['id', 'name'])->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
            ])->values()->all(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'last_name', 'email'])->map(fn (User $user) => [
                'id' => $user->id,
                'full_name' => trim("{$user->name} {$user->last_name}"),
                'email' => $user->email,
            ])->values()->all(),
            'roles' => $this->depotUserRoles(),
        ];
    }
}
