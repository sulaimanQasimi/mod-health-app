<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;

trait ProvidesFacilityLayoutNav
{
    /**
     * @return array{dashboard: string, floors: string, rooms: string, beds: string}
     */
    protected function facilityNavUrls(): array
    {
        return [
            'dashboard' => route('facility-layout.index'),
            'floors' => route('floors.index'),
            'rooms' => route('rooms.index'),
            'beds' => route('beds.index'),
        ];
    }

    /**
     * @return array{dashboard: bool, floors: bool, rooms: bool, beds: bool}
     */
    protected function facilityNavPermissions(User $user): array
    {
        $isAdmin = $user->hasRole(['super_admin', 'admin']);

        return [
            'dashboard' => $isAdmin
                || $user->can('show-floors-menu')
                || $user->can('show-rooms-menu')
                || $user->can('show-beds-menu'),
            'floors' => $isAdmin || $user->can('show-floors-menu') || $user->can('viewAny', \App\Models\Floor::class),
            'rooms' => $isAdmin || $user->can('show-rooms-menu') || $user->can('viewAny', \App\Models\Room::class),
            'beds' => $isAdmin || $user->can('show-beds-menu') || $user->can('viewAny', \App\Models\Bed::class),
        ];
    }
}
