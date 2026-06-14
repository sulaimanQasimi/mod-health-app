<?php

namespace App\Support;

class DepotRolePermissions
{
    public const ACTION_VIEW = 'view';

    public const ACTION_REPORT_EXPORT = 'report.export';

    public const ACTION_TRANSACTION_VIEW = 'transaction.view';

    public const ACTION_TRANSACTION_CREATE = 'transaction.create';

    public const ACTION_REQUEST_CREATE = 'request.create';

    public const ACTION_REQUEST_APPROVE = 'request.approve';

    public const ACTION_REQUEST_FULFILL = 'request.fulfill';

    public const ACTION_MOVEMENT_DEPOT_TO_PHARMACY = 'movement.depot_to_pharmacy';

    public const ACTION_MOVEMENT_DEPOT_TO_DEPOT = 'movement.depot_to_depot';

    /**
     * Depot manager (admin): view depot data and export reports only.
     *
     * @var array<string, list<string>>
     */
    public const ROLE_PERMISSIONS = [
        'manager' => [
            self::ACTION_VIEW,
            self::ACTION_REPORT_EXPORT,
        ],
        'staff' => [
            self::ACTION_VIEW,
            self::ACTION_TRANSACTION_VIEW,
            self::ACTION_TRANSACTION_CREATE,
            self::ACTION_REQUEST_CREATE,
            self::ACTION_REQUEST_APPROVE,
            self::ACTION_REQUEST_FULFILL,
            self::ACTION_MOVEMENT_DEPOT_TO_PHARMACY,
            self::ACTION_MOVEMENT_DEPOT_TO_DEPOT,
        ],
        'procurement' => [
            self::ACTION_VIEW,
            self::ACTION_TRANSACTION_VIEW,
            self::ACTION_TRANSACTION_CREATE,
            self::ACTION_REQUEST_CREATE,
            self::ACTION_REQUEST_FULFILL,
            self::ACTION_MOVEMENT_DEPOT_TO_PHARMACY,
            self::ACTION_MOVEMENT_DEPOT_TO_DEPOT,
        ],
        'viewer' => [
            self::ACTION_VIEW,
            self::ACTION_TRANSACTION_VIEW,
        ],
    ];

    public static function permissionToAction(string $permission): string
    {
        return match ($permission) {
            'depot.view' => self::ACTION_VIEW,
            'depot.report.export' => self::ACTION_REPORT_EXPORT,
            'depot.transaction.view' => self::ACTION_TRANSACTION_VIEW,
            'depot.transaction.create' => self::ACTION_TRANSACTION_CREATE,
            'depot.request.create' => self::ACTION_REQUEST_CREATE,
            'depot.request.approve' => self::ACTION_REQUEST_APPROVE,
            'depot.request.fulfill' => self::ACTION_REQUEST_FULFILL,
            'depot.movement.depot_to_pharmacy' => self::ACTION_MOVEMENT_DEPOT_TO_PHARMACY,
            'depot.movement.depot_to_depot' => self::ACTION_MOVEMENT_DEPOT_TO_DEPOT,
            default => str_starts_with($permission, 'depot.')
                ? substr($permission, strlen('depot.'))
                : $permission,
        };
    }

    /**
     * @return list<string>
     */
    public static function actionsForRole(?string $role): array
    {
        if (! $role) {
            return [];
        }

        return self::ROLE_PERMISSIONS[$role] ?? [];
    }

    public static function roleCan(?string $role, string $action): bool
    {
        return in_array($action, self::actionsForRole($role), true);
    }
}
