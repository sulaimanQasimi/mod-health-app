<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PaginatesInertiaIndex;
use App\Http\Controllers\Concerns\ProvidesFacilityLayoutNav;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BedController extends Controller
{
    use PaginatesInertiaIndex;
    use ProvidesFacilityLayoutNav;

    private const FILTER_KEYS = ['search', 'branch_id', 'floor_id', 'room_id', 'is_occupied', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Bed::class);

        $query = Bed::query()->with([
            'room:id,name,floor_id,branch_id',
            'room.floor:id,name',
            'room.branch:id,name',
        ]);

        if ($request->filled('search')) {
            $query->where('number', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('branch_id')) {
            $query->whereHas('room', fn ($q) => $q->where('branch_id', $request->branch_id));
        }

        if ($request->filled('floor_id')) {
            $query->whereHas('room', fn ($q) => $q->where('floor_id', $request->floor_id));
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('is_occupied') && in_array($request->is_occupied, ['0', '1'], true)) {
            $query->where('is_occupied', $request->is_occupied === '1');
        }

        $paginator = $this->paginateQuery($query->orderBy('number'), $request, 25);

        return Inertia::render('Beds/Index', [
            'beds' => $this->paginationPayload($paginator, fn (Bed $bed) => [
                'id' => $bed->id,
                'number' => $bed->number,
                'room_id' => $bed->room_id,
                'room_name' => $bed->room?->name,
                'floor_name' => $bed->room?->floor?->name,
                'branch_name' => $bed->room?->branch?->name,
                'is_occupied' => (bool) $bed->is_occupied,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
                'floors' => Floor::query()->orderBy('name')->get(['id', 'name', 'branch_id']),
                'rooms' => Room::query()->orderBy('name')->get(['id', 'name', 'floor_id', 'branch_id']),
            ],
            'permissions' => array_merge(
                $this->settingsPermissions(
                    $request->user(),
                    'create-beds',
                    'edit-beds',
                    'delete-beds',
                ),
                ['view' => true],
            ),
            'urls' => [
                'index' => route('beds.index'),
                'create' => route('beds.create'),
                'show' => url('/beds'),
                'edit' => url('/beds'),
                'destroy' => url('/beds'),
            ],
            'nav' => [
                'active' => 'beds',
                'urls' => $this->facilityNavUrls(),
                'permissions' => $this->facilityNavPermissions($request->user()),
            ],
        ]);
    }

    public function show(Request $request, Bed $bed): Response
    {
        $this->authorize('view', $bed);

        $bed->load([
            'room:id,name,floor_id,branch_id',
            'room.floor:id,name',
            'room.branch:id,name',
            'room.department:id,name',
        ]);

        return Inertia::render('Beds/Show', [
            'bed' => [
                'id' => $bed->id,
                'number' => $bed->number,
                'is_occupied' => (bool) $bed->is_occupied,
                'room_name' => $bed->room?->name,
                'floor_name' => $bed->room?->floor?->name,
                'branch_name' => $bed->room?->branch?->name,
                'department_name' => $bed->room?->department?->name,
            ],
            'permissions' => [
                'edit' => $request->user()->can('update', $bed),
                'delete' => $request->user()->can('delete', $bed),
            ],
            'urls' => [
                'index' => route('beds.index'),
                'edit' => route('beds.edit', $bed),
                'destroy' => route('beds.destroy', $bed),
            ],
            'nav' => [
                'active' => 'beds',
                'urls' => $this->facilityNavUrls(),
                'permissions' => $this->facilityNavPermissions($request->user()),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Bed::class);

        return Inertia::render('Beds/Create', [
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls(),
            'nav' => [
                'active' => 'beds',
                'urls' => $this->facilityNavUrls(),
                'permissions' => $this->facilityNavPermissions($request->user()),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Bed::class);

        $data = $this->validateBed($request);
        Bed::create($data);

        return redirect()
            ->route('beds.index')
            ->with('success', localize('global.bed_created_successfully.'));
    }

    public function edit(Request $request, Bed $bed): Response
    {
        $this->authorize('update', $bed);

        $bed->load('room:id,floor_id,branch_id');

        return Inertia::render('Beds/Edit', [
            'bed' => [
                'id' => $bed->id,
                'number' => $bed->number,
                'branch_id' => $bed->room?->branch_id ? (string) $bed->room->branch_id : '',
                'floor_id' => $bed->room?->floor_id ? (string) $bed->room->floor_id : '',
                'room_id' => (string) $bed->room_id,
                'is_occupied' => (bool) $bed->is_occupied,
            ],
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls($bed),
            'nav' => [
                'active' => 'beds',
                'urls' => $this->facilityNavUrls(),
                'permissions' => $this->facilityNavPermissions($request->user()),
            ],
        ]);
    }

    public function update(Request $request, Bed $bed): RedirectResponse
    {
        $this->authorize('update', $bed);

        $bed->update($this->validateBed($request));

        return redirect()
            ->route('beds.index')
            ->with('success', localize('global.bed_updated_successfully.'));
    }

    public function destroy(Request $request, Bed $bed): RedirectResponse
    {
        $this->authorize('delete', $bed);

        $bed->delete();

        return redirect()
            ->route('beds.index')
            ->with('success', localize('global.bed_deleted_successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormData(): array
    {
        return [
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
            'floors' => Floor::query()->orderBy('name')->get(['id', 'name', 'branch_id']),
            'rooms' => Room::query()->orderBy('name')->get(['id', 'name', 'floor_id', 'branch_id']),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Bed $bed = null): array
    {
        return [
            'index' => route('beds.index'),
            'store' => route('beds.store'),
            'update' => $bed ? route('beds.update', $bed) : '',
            'back' => route('beds.index'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateBed(Request $request): array
    {
        $data = $request->validate([
            'number' => 'required|string|max:191',
            'branch_id' => 'required|exists:branches,id',
            'floor_id' => 'required|exists:floors,id',
            'room_id' => 'required|exists:rooms,id',
            'is_occupied' => 'nullable|boolean',
        ]);

        $room = Room::query()->findOrFail($data['room_id']);

        if ((int) $room->floor_id !== (int) $data['floor_id']
            || (int) $room->branch_id !== (int) $data['branch_id']) {
            abort(422, localize('global.room_hierarchy_mismatch') ?: 'Selected room does not match branch/floor.');
        }

        return [
            'number' => $data['number'],
            'room_id' => (int) $data['room_id'],
            'is_occupied' => $request->boolean('is_occupied'),
        ];
    }
}
