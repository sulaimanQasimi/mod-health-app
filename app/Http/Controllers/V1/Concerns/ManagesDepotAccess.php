<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\Depot;
use App\Models\DepotRequest;
use App\Models\DepotTransaction;
use App\Models\Pharmacy;
use App\Models\User;
use App\Support\DepotRequestAuthorization;
use App\Support\DepotRolePermissions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ManagesDepotAccess
{
    protected function isDepotSystemAdmin(?User $user = null): bool
    {
        $user ??= request()->user();

        return $user && $user->hasRole(['super_admin', 'admin']);
    }

    protected function authorizeDepotPermission(string $permission, ?int $depotId = null): void
    {
        $action = DepotRolePermissions::permissionToAction($permission);

        abort_unless($this->userCanDepotAction($action, $depotId), 403);
    }

    protected function userCan(string $permission): bool
    {
        return $this->userCanDepotAction(
            DepotRolePermissions::permissionToAction($permission),
        );
    }

    protected function userCanDepotAction(string $action, ?int $depotId = null, ?User $user = null): bool
    {
        $user ??= request()->user();

        if (! $user) {
            return false;
        }

        if ($this->isDepotSystemAdmin($user)) {
            return true;
        }

        // Spatie role permissions (legacy Blade parity) grant global access.
        if ($user->hasSpatieDepotPermission($action)) {
            return true;
        }

        if ($depotId !== null) {
            return $user->hasDepotAccess($depotId)
                && $user->canPerformDepotAction($depotId, $action);
        }

        return $user->canPerformDepotActionOnAny($action);
    }

    protected function authorizeDepotMembership(int $depotId, ?User $user = null): void
    {
        $user ??= request()->user();

        abort_unless($user, 403);

        if ($this->isDepotSystemAdmin($user) || $user->hasAnySpatieDepotPermission()) {
            return;
        }

        abort_unless($user->hasDepotAccess($depotId), 403);
    }

    protected function authorizeDepotRecord(Depot $depot, string $action): void
    {
        $this->authorizeDepotMembership($depot->id);
        abort_unless($this->userCanDepotAction($action, $depot->id), 403);
    }

    protected function authorizeDepotTransactionRecord(DepotTransaction $transaction, string $action): void
    {
        $depotId = $transaction->primaryDepotId();

        abort_unless($depotId, 403);
        $this->authorizeDepotMembership($depotId);
        abort_unless($this->userCanDepotAction($action, $depotId), 403);
    }

    protected function authorizeDepotRequestRecord(DepotRequest $depotRequest, string $action): void
    {
        $user = request()->user();
        abort_unless($user, 403);

        if (in_array($action, [
            DepotRolePermissions::ACTION_REQUEST_APPROVE,
            DepotRolePermissions::ACTION_REQUEST_FULFILL,
        ], true)) {
            abort_unless(DepotRequestAuthorization::canProcess($user, $depotRequest, $action), 403);

            return;
        }

        if ($this->isDepotSystemAdmin()) {
            return;
        }

        if ($depotRequest->isPharmacyRequest()) {
            if ($action === DepotRolePermissions::ACTION_REQUEST_CREATE) {
                $allowedPharmacyIds = $this->allowedPharmacyIdsForRequests($user);
                abort_unless(
                    $depotRequest->pharmacy_id && in_array((int) $depotRequest->pharmacy_id, $allowedPharmacyIds, true),
                    403
                );

                return;
            }

            abort_unless($this->userCanAccessDepotRequest($depotRequest, $user), 403);

            return;
        }

        $requiredDepotId = match ($action) {
            DepotRolePermissions::ACTION_REQUEST_CREATE => $depotRequest->requesting_depot_id,
            default => null,
        };

        if ($requiredDepotId) {
            abort_unless($user->canPerformDepotAction((int) $requiredDepotId, $action), 403);

            return;
        }

        abort_unless($this->userCanAccessDepotRequest($depotRequest, $user), 403);
    }

    protected function userCanProcessDepotRequest(DepotRequest $depotRequest, string $action, ?User $user = null): bool
    {
        $user ??= request()->user();

        if (! $user) {
            return false;
        }

        return DepotRequestAuthorization::canProcess($user, $depotRequest, $action);
    }

    protected function userCanAccessDepotRequest(DepotRequest $depotRequest, ?User $user = null): bool
    {
        $user ??= request()->user();

        if (! $user || $this->isDepotSystemAdmin($user)) {
            return (bool) $user;
        }

        if ($depotRequest->isPharmacyRequest()) {
            $allowedPharmacyIds = $this->allowedPharmacyIdsForRequests($user);

            if ($depotRequest->pharmacy_id && in_array((int) $depotRequest->pharmacy_id, $allowedPharmacyIds, true)) {
                return true;
            }
        }

        if ($user->hasAnySpatieDepotPermission()) {
            return true;
        }

        foreach ([$depotRequest->source_depot_id, $depotRequest->requesting_depot_id] as $depotId) {
            if ($depotId && $user->hasDepotAccess((int) $depotId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<int>
     */
    protected function allowedDepotIds(?User $user = null): array
    {
        $user ??= request()->user();

        if (! $user) {
            return [];
        }

        if ($this->isDepotSystemAdmin($user) || $user->hasAnySpatieDepotPermission()) {
            return Depot::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return $user->activeDepots->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * @return list<int>
     */
    protected function allowedDepotIdsForAction(string $action, ?User $user = null): array
    {
        $user ??= request()->user();

        if (! $user) {
            return [];
        }

        if ($this->isDepotSystemAdmin($user)) {
            return Depot::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return $user->allowedDepotIdsForAction($action);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Depot>  $query
     */
    protected function scopeQueryToUserDepots($query, ?User $user = null): void
    {
        if ($this->isDepotSystemAdmin($user)) {
            return;
        }

        $allowedIds = $this->allowedDepotIds($user);

        if (empty($allowedIds)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('id', $allowedIds);
    }

    /**
     * @return array<string, string>
     */
    protected function depotNavUrls(): array
    {
        return [
            'index' => route('depots.index'),
            'transactions' => route('depots.transactions.index'),
            'requests' => route('depots.requests.index'),
            'depotToDepot' => route('depots.requests.create'),
            'reports' => route('depots.reports.index'),
            'tools' => route('tools.index'),
        ];
    }

    /**
     * @return array<string, bool>
     */
    protected function depotNavPermissions(?User $user = null): array
    {
        return [
            'index' => $this->userCanDepotAction(DepotRolePermissions::ACTION_VIEW, null, $user),
            'requests' => $this->canViewDepotRequests($user),
            'reports' => $this->userCanDepotAction(DepotRolePermissions::ACTION_REPORT_EXPORT, null, $user),
            'tools' => $this->userCanDepotAction(DepotRolePermissions::ACTION_VIEW, null, $user),
        ];
    }

    /**
     * @return array{view: bool, create: bool, edit: bool, delete: bool}
     */
    protected function depotCrudPermissions(?User $user = null): array
    {
        return [
            'view' => $this->userCanDepotAction(DepotRolePermissions::ACTION_VIEW, null, $user),
            'create' => $this->userCanDepotAction(DepotRolePermissions::ACTION_CREATE, null, $user),
            'edit' => $this->userCanDepotAction(DepotRolePermissions::ACTION_UPDATE, null, $user),
            'delete' => $this->userCanDepotAction(DepotRolePermissions::ACTION_DELETE, null, $user),
        ];
    }

    /**
     * @return array{view: bool, create: bool, cancel: bool}
     */
    protected function depotTransactionPermissions(?int $depotId = null, ?User $user = null): array
    {
        return [
            'view' => $this->userCanDepotAction(DepotRolePermissions::ACTION_TRANSACTION_VIEW, $depotId, $user),
            'create' => $this->userCanDepotAction(DepotRolePermissions::ACTION_TRANSACTION_CREATE, $depotId, $user),
            'cancel' => $this->userCanDepotAction(DepotRolePermissions::ACTION_TRANSACTION_CREATE, $depotId, $user),
        ];
    }

    /**
     * @return array{view: bool, create: bool, approve: bool, fulfill: bool}
     */
    protected function depotRequestPermissions(?User $user = null): array
    {
        return [
            'view' => $this->canViewDepotRequests($user),
            'create' => $this->canCreateDepotRequest($user),
            'approve' => $this->userCanDepotAction(DepotRolePermissions::ACTION_REQUEST_APPROVE, null, $user),
            'fulfill' => $this->userCanDepotAction(DepotRolePermissions::ACTION_REQUEST_FULFILL, null, $user),
        ];
    }

    protected function canViewDepotRequests(?User $user = null): bool
    {
        $user ??= request()->user();

        return $this->userCanDepotAction(DepotRolePermissions::ACTION_VIEW, null, $user)
            || $this->userCanDepotAction(DepotRolePermissions::ACTION_REQUEST_CREATE, null, $user)
            || $this->userCanDepotAction(DepotRolePermissions::ACTION_REQUEST_APPROVE, null, $user)
            || $this->userCanDepotAction(DepotRolePermissions::ACTION_REQUEST_FULFILL, null, $user)
            || $this->canAccessPharmacyRequests($user);
    }

    protected function canCreateDepotRequest(?User $user = null): bool
    {
        $user ??= request()->user();

        return $this->userCanDepotAction(DepotRolePermissions::ACTION_REQUEST_CREATE, null, $user)
            || $this->canAccessPharmacyRequests($user);
    }

    protected function canAccessPharmacyRequests(?User $user = null): bool
    {
        $user ??= request()->user();

        return $user && (
            $this->isDepotSystemAdmin($user)
            || $user->hasActivePharmacyRole(['manager', 'procurement'])
        );
    }

    protected function isPharmacyRequestContext(?User $user = null): bool
    {
        $user ??= request()->user();

        return $this->canAccessPharmacyRequests($user)
            && ! $this->userCanDepotAction(DepotRolePermissions::ACTION_REQUEST_CREATE, null, $user)
            && ! $this->userCanDepotAction(DepotRolePermissions::ACTION_REQUEST_APPROVE, null, $user)
            && ! $this->userCanDepotAction(DepotRolePermissions::ACTION_REQUEST_FULFILL, null, $user);
    }

    /**
     * @return list<int>
     */
    protected function allowedPharmacyIdsForRequests(?User $user = null, ?int $filterPharmacyId = null): array
    {
        $user ??= request()->user();

        if (! $user) {
            return [];
        }

        if ($this->isDepotSystemAdmin($user)) {
            if ($filterPharmacyId) {
                return [$filterPharmacyId];
            }

            return Pharmacy::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return $user->activePharmacies->pluck('id')->map(fn ($id) => (int) $id)->all();
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

    protected function authorizeDepotStockView(?int $depotId = null): void
    {
        abort_unless(
            $this->userCanDepotAction(DepotRolePermissions::ACTION_TRANSACTION_VIEW, $depotId)
            || $this->userCanDepotAction(DepotRolePermissions::ACTION_TRANSACTION_CREATE, $depotId)
            || $this->userCanDepotAction(DepotRolePermissions::ACTION_REQUEST_CREATE, $depotId)
            || $this->userCanDepotAction(DepotRolePermissions::ACTION_REQUEST_FULFILL, $depotId)
            || $this->canAccessPharmacyRequests(),
            403
        );

        if ($depotId !== null) {
            $this->authorizeDepotMembership($depotId);
        }
    }

    protected function validatedTransactionDepotHint(Request $request): ?int
    {
        if (! $request->filled('depot_id')) {
            return session('depot_transaction_depot_hint');
        }

        $depotId = (int) $request->query('depot_id');
        $this->authorizeDepotPermission('depot.transaction.create', $depotId);
        session(['depot_transaction_depot_hint' => $depotId]);

        return $depotId;
    }

    protected function resolveTransactionDepotId(?int $hintedDepotId = null, ?User $user = null): int
    {
        $user ??= request()->user();
        abort_unless($user, 403);

        $allowedIds = $this->isDepotSystemAdmin($user)
            ? Depot::query()->where('is_active', true)->orderBy('name')->pluck('id')->map(fn ($id) => (int) $id)->all()
            : $this->allowedDepotIdsForAction(DepotRolePermissions::ACTION_TRANSACTION_CREATE, $user);

        if ($allowedIds === []) {
            abort(403);
        }

        if ($hintedDepotId && in_array($hintedDepotId, $allowedIds, true)) {
            return $hintedDepotId;
        }

        if (count($allowedIds) === 1) {
            return $allowedIds[0];
        }

        throw ValidationException::withMessages([
            'depot_id' => [localize('global.depot.transaction_depot_required')],
        ]);
    }

    /**
     * @return array{id: int, name: string}|null
     */
    protected function transactionDepotPreview(?int $hintedDepotId = null, ?User $user = null): ?array
    {
        try {
            $depotId = $this->resolveTransactionDepotId($hintedDepotId, $user);
            $depot = Depot::query()->find($depotId);

            return $depot ? ['id' => $depotId, 'name' => $depot->name] : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
