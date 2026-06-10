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
        $depotsQuery = Depot::query()->orderBy('name');
        if ($excludeDepot) {
            $depotsQuery->whereKeyNot($excludeDepot->id);
        }

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
            'activeDepots' => Depot::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'pharmacy_id'])->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'pharmacy_id' => $item->pharmacy_id,
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
