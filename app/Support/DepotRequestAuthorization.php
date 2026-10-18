<?php

namespace App\Support;

use App\Models\DepotRequest;
use App\Models\User;

class DepotRequestAuthorization
{
    /**
     * The depot that receives and processes submitted requests (source / from depot).
     */
    public static function processingDepotId(DepotRequest $depotRequest): ?int
    {
        return $depotRequest->source_depot_id ? (int) $depotRequest->source_depot_id : null;
    }

    public static function canProcess(User $user, DepotRequest $depotRequest, string $action): bool
    {
        $processingDepotId = self::processingDepotId($depotRequest);

        if (! $processingDepotId) {
            return false;
        }

        if ($user->hasRole(['super_admin', 'admin']) || $user->hasSpatieDepotPermission($action)) {
            return true;
        }

        return $user->hasDepotAccess($processingDepotId)
            && $user->canPerformDepotAction($processingDepotId, $action);
    }
}
