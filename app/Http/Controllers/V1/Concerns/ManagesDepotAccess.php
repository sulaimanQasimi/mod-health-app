<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\User;

trait ManagesDepotAccess
{
    protected function authorizeDepotPermission(string $permission): void
    {
        $user = request()->user();

        abort_unless(
            $user && ($user->hasRole(['super_admin', 'admin']) || $user->can($permission)),
            403
        );
    }

    protected function userCan(string $permission): bool
    {
        $user = request()->user();

        return $user && ($user->hasRole(['super_admin', 'admin']) || $user->can($permission));
    }

    /**
     * @return array<string, string>
     */
    protected function depotNavUrls(): array
    {
        return [
            'index' => route('react.depots.index'),
            'transactions' => route('react.depots.transactions.index'),
            'requests' => route('react.depots.requests.index'),
            'depotToDepot' => route('react.depots.movements.depot-to-depot'),
            'depotToPharmacy' => route('react.depots.movements.depot-to-pharmacy'),
            'reports' => route('react.depots.reports.index'),
            'tools' => route('react.tools.index'),
        ];
    }

    /**
     * @return array{view: bool, create: bool, edit: bool, delete: bool}
     */
    protected function depotCrudPermissions(?User $user = null): array
    {
        $user ??= request()->user();

        return [
            'view' => $this->userCan('depot.view'),
            'create' => $this->userCan('depot.create'),
            'edit' => $this->userCan('depot.update'),
            'delete' => $this->userCan('depot.delete'),
        ];
    }

    /**
     * @return array{view: bool, create: bool, cancel: bool}
     */
    protected function depotTransactionPermissions(?User $user = null): array
    {
        $user ??= request()->user();

        return [
            'view' => $this->userCan('depot.transaction.view'),
            'create' => $this->userCan('depot.transaction.create'),
            'cancel' => $this->userCan('depot.transaction.create'),
        ];
    }

    /**
     * @return array{view: bool, create: bool, approve: bool, fulfill: bool}
     */
    protected function depotRequestPermissions(?User $user = null): array
    {
        $user ??= request()->user();

        return [
            'view' => $this->userCanAny(['depot.request.create', 'depot.request.approve', 'depot.request.fulfill']),
            'create' => $this->userCan('depot.request.create'),
            'approve' => $this->userCan('depot.request.approve'),
            'fulfill' => $this->userCan('depot.request.fulfill'),
        ];
    }

    protected function userCanAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->userCan($permission)) {
                return true;
            }
        }

        return false;
    }
}
