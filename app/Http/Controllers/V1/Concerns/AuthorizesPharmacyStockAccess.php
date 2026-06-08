<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\Pharmacy;
use App\Models\PharmacyFulfillment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

trait AuthorizesPharmacyStockAccess
{
    protected function authorizePharmacyStockMenu(User $user): void
    {
        if (! $this->canAccessPharmacyStockMenu($user)) {
            throw new AuthorizationException;
        }
    }

    protected function authorizePharmacyManager(User $user): void
    {
        if (! $this->isPharmacyAdmin($user) && ! $user->hasActivePharmacyRole(['manager'])) {
            throw new AuthorizationException;
        }
    }

    protected function authorizePharmacyFulfillment(User $user): void
    {
        if (! $this->isPharmacyAdmin($user) && ! $user->hasActivePharmacyRole(['manager', 'procurement'])) {
            throw new AuthorizationException;
        }
    }

    protected function authorizeFulfillmentRecord(User $user, PharmacyFulfillment $fulfillment): void
    {
        $this->authorizePharmacyFulfillment($user);

        if ($this->isPharmacyAdmin($user)) {
            return;
        }

        $allowedIds = $user->activePharmacies->pluck('id')->all();

        if (! in_array($fulfillment->pharmacy_id, $allowedIds, true)) {
            throw new AuthorizationException;
        }
    }

    protected function canAccessPharmacyStockMenu(User $user): bool
    {
        return $this->isPharmacyAdmin($user)
            || $user->hasActivePharmacyRole(['manager', 'procurement']);
    }

    protected function isPharmacyAdmin(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * @return list<int>
     */
    protected function allowedPharmacyIds(User $user, ?int $filterPharmacyId = null): array
    {
        if ($this->isPharmacyAdmin($user)) {
            if ($filterPharmacyId) {
                return [$filterPharmacyId];
            }

            return Pharmacy::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return $user->activePharmacies->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * @return array{create: bool, edit: bool, delete: bool, view: bool}
     */
    protected function fulfillmentPermissions(User $user): array
    {
        $canManage = $this->isPharmacyAdmin($user)
            || $user->hasActivePharmacyRole(['manager', 'procurement']);

        return [
            'view' => $canManage,
            'create' => $canManage,
            'edit' => $canManage,
            'delete' => $canManage,
        ];
    }

    /**
     * @return array{create: bool}
     */
    protected function incomePermissions(User $user): array
    {
        $canManage = $this->isPharmacyAdmin($user)
            || $user->hasActivePharmacyRole(['manager']);

        return ['create' => $canManage];
    }

    protected function parseOptionalDate(?string $value): ?\DateTimeInterface
    {
        if (! $value) {
            return null;
        }

        return \Hekmatinasser\Verta\Facades\Verta::parse($value)->datetime();
    }

}
