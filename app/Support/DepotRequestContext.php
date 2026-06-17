<?php

namespace App\Support;

use App\Models\Depot;
use App\Models\DepotRequest;
use App\Models\User;

class DepotRequestContext
{
    /**
     * @return array{
     *     branch_name: ?string,
     *     department_name: ?string,
     *     pharmacy_depot_label: ?string,
     *     request_user_name: ?string,
     * }
     */
    public static function forRequest(DepotRequest $request, ?User $requestUser = null): array
    {
        $requestUser ??= $request->requestedBy;

        if ($request->isPharmacyRequest()) {
            $linkedDepot = Depot::query()
                ->with(['branch:id,name', 'department:id,name', 'pharmacy:id,name'])
                ->where('pharmacy_id', $request->pharmacy_id)
                ->first();

            return [
                'branch_name' => $linkedDepot?->branch?->name,
                'department_name' => $linkedDepot?->department?->name,
                'pharmacy_depot_label' => $request->pharmacy?->name ?? $linkedDepot?->name,
                'request_user_name' => self::userDisplayName($requestUser),
            ];
        }

        $request->loadMissing([
            'requestingDepot.branch:id,name',
            'requestingDepot.department:id,name',
            'requestingDepot.pharmacy:id,name',
        ]);

        $depot = $request->requestingDepot;

        return [
            'branch_name' => $depot?->branch?->name,
            'department_name' => $depot?->department?->name,
            'pharmacy_depot_label' => $depot?->name,
            'request_user_name' => self::userDisplayName($requestUser),
        ];
    }

    /**
     * @return array{
     *     branch_name: ?string,
     *     department_name: ?string,
     *     pharmacy_depot_label: ?string,
     *     pharmacy_name: ?string,
     * }
     */
    public static function forDepot(?Depot $depot): array
    {
        if (! $depot) {
            return [
                'branch_name' => null,
                'department_name' => null,
                'pharmacy_depot_label' => null,
                'pharmacy_name' => null,
            ];
        }

        $depot->loadMissing(['branch:id,name', 'department:id,name', 'pharmacy:id,name']);

        return [
            'branch_name' => $depot->branch?->name,
            'department_name' => $depot->department?->name,
            'pharmacy_depot_label' => $depot->name,
            'pharmacy_name' => $depot->pharmacy?->name,
        ];
    }

    public static function userDisplayName(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        $fullName = trim("{$user->name} {$user->last_name}");

        return $fullName !== '' ? $fullName : ($user->name ?? null);
    }
}
