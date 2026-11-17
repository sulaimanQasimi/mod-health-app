<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PaginatesInertiaIndex;
use App\Http\Controllers\Concerns\ProvidesFacilityLayoutNav;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    use PaginatesInertiaIndex;
    use ProvidesFacilityLayoutNav;

    private const FILTER_KEYS = ['search', 'branch_id', 'floor_id', 'department_id', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Room::class);

        $query = Room::query()
            ->with(['floor:id,name', 'branch:id,name', 'department:id,name'])
            ->withCount(['allBeds as beds_count']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('floor_id')) {
            $query->where('floor_id', $request->floor_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request, 25);

        return Inertia::render('Rooms/Index', [
            'rooms' => $this->paginationPayload($paginator, fn (Room $room) => [
                'id' => $room->id,
                'name' => $room->name,
                'floor_name' => $room->floor?->name,
                'branch_name' => $room->branch?->name,
                'department_name' => $room->department?->name,
                'beds_count' => (int) ($room->beds_count ?? 0),
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
                'floors' => Floor::query()->orderBy('name')->get(['id', 'name', 'branch_id']),
                'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            ],
            'permissions' => array_merge(
                $this->settingsPermissions(
                    $request->user(),
                    'create-rooms',
                    'edit-rooms',
                    'delete-rooms',
                ),
                [
                    'view' => $request->user()->hasRole(['super_admin', 'admin'])
                        || $request->user()->hasPermissionTo('show-rooms-menu')
                        || $request->user()->hasPermissionTo('show-rooms'),
                ],
            ),
            'urls' => [
                'index' => route('rooms.index'),
                'create' => route('rooms.create'),
                'show' => url('/rooms'),
                'edit' => url('/rooms'),
                'destroy' => url('/rooms'),
            ],
            'nav' => [
                'active' => 'rooms',
                'urls' => $this->facilityNavUrls(),
                'permissions' => $this->facilityNavPermissions($request->user()),
            ],
        ]);
    }

    public function show(Request $request, Room $room): Response
    {
        $this->authorize('view', $room);

        $room->load([
            'floor:id,name',
            'branch:id,name',
            'department:id,name',
            'allBeds:id,number,room_id,is_occupied',
        ]);

        return Inertia::render('Rooms/Show', [
            'room' => [
                'id' => $room->id,
                'name' => $room->name,
                'floor_name' => $room->floor?->name,
                'branch_name' => $room->branch?->name,
                'department_name' => $room->department?->name,
                'beds' => $room->allBeds->map(fn ($bed) => [
                    'id' => $bed->id,
                    'number' => $bed->number,
                    'is_occupied' => (bool) $bed->is_occupied,
                ])->values()->all(),
                'bed_count' => $room->allBeds->count(),
            ],
            'permissions' => [
                'edit' => $request->user()->can('update', $room),
                'delete' => $request->user()->can('delete', $room),
            ],
            'urls' => [
                'index' => route('rooms.index'),
                'edit' => route('rooms.edit', $room),
                'destroy' => route('rooms.destroy', $room),
                'bedShow' => url('/beds'),
            ],
            'nav' => [
                'active' => 'rooms',
                'urls' => $this->facilityNavUrls(),
                'permissions' => $this->facilityNavPermissions($request->user()),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Room::class);

        return Inertia::render('Rooms/Create', [
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls(),
            'nav' => [
                'active' => 'rooms',
                'urls' => $this->facilityNavUrls(),
                'permissions' => $this->facilityNavPermissions($request->user()),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Room::class);

        Room::create($this->validateRoom($request));

        return redirect()
            ->route('rooms.index')
            ->with('success', localize('global.room_created_successfully.'));
    }

    public function edit(Request $request, Room $room): Response
    {
        $this->authorize('update', $room);

        return Inertia::render('Rooms/Edit', [
            'room' => [
                'id' => $room->id,
                'name' => $room->name,
                'branch_id' => (string) $room->branch_id,
                'floor_id' => (string) $room->floor_id,
                'department_id' => $room->department_id ? (string) $room->department_id : '',
            ],
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls($room),
            'nav' => [
                'active' => 'rooms',
                'urls' => $this->facilityNavUrls(),
                'permissions' => $this->facilityNavPermissions($request->user()),
            ],
        ]);
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $room->update($this->validateRoom($request, $room));

        return redirect()
            ->route('rooms.index')
            ->with('success', localize('global.room_updated_successfully.'));
    }

    public function destroy(Request $request, Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);

        $room->delete();

        return redirect()
            ->route('rooms.index')
            ->with('success', localize('global.room_deleted_successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormData(): array
    {
        return [
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
            'floors' => Floor::query()->orderBy('name')->get(['id', 'name', 'branch_id']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Room $room = null): array
    {
        return [
            'index' => route('rooms.index'),
            'store' => route('rooms.store'),
            'update' => $room ? route('rooms.update', $room) : '',
            'back' => route('rooms.index'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRoom(Request $request, ?Room $room = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'branch_id' => 'required|exists:branches,id',
            'floor_id' => 'required|exists:floors,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        if (empty($data['department_id'])) {
            $data['department_id'] = null;
        }

        $floor = Floor::query()->findOrFail($data['floor_id']);

        if ((int) $floor->branch_id !== (int) $data['branch_id']) {
            abort(422, localize('global.floor_branch_mismatch') ?: 'Selected floor does not belong to the selected branch.');
        }

        $data['branch_id'] = (int) $floor->branch_id;

        return $data;
    }
}
