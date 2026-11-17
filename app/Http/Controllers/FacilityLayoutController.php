<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesFacilityLayoutNav;
use App\Models\Bed;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FacilityLayoutController extends Controller
{
    use ProvidesFacilityLayoutNav;

    public function index(Request $request): Response
    {
        $user = $request->user();
        $permissions = $this->facilityNavPermissions($user);

        abort_unless($permissions['dashboard'], 403);

        $floorCount = Floor::query()->count();
        $roomCount = Room::query()->count();
        $bedCount = Bed::query()->count();
        $occupiedBeds = Bed::query()->where('is_occupied', true)->count();
        $availableBeds = max(0, $bedCount - $occupiedBeds);

        return Inertia::render('FacilityLayout/Dashboard', [
            'stats' => [
                'floors' => $floorCount,
                'rooms' => $roomCount,
                'beds' => $bedCount,
                'occupied_beds' => $occupiedBeds,
                'available_beds' => $availableBeds,
            ],
            'quickLinks' => [
                'floors_create' => route('floors.create'),
                'rooms_create' => route('rooms.create'),
                'beds_create' => route('beds.create'),
            ],
            'createPermissions' => [
                'floors' => $user->can('create', Floor::class),
                'rooms' => $user->can('create', Room::class),
                'beds' => $user->can('create', Bed::class),
            ],
            'nav' => [
                'active' => 'dashboard',
                'urls' => $this->facilityNavUrls(),
                'permissions' => $permissions,
            ],
        ]);
    }
}
