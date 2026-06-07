<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Bed;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BedController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'room_id', 'is_occupied', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Bed::class);

        $query = Bed::query()->with('room:id,name');

        if ($request->filled('search')) {
            $query->where('number', 'like', '%'.$request->search.'%');
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
                'is_occupied' => (bool) $bed->is_occupied,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'rooms' => Room::query()->orderBy('name')->get(['id', 'name']),
            ],
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-beds',
                'edit-beds',
                'delete-beds',
            ),
            'urls' => [
                'index' => route('react.beds.index'),
                'create' => route('react.beds.create'),
                'edit' => url('/react/beds'),
                'destroy' => url('/react/beds'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Bed::class);

        return Inertia::render('Beds/Create', [
            'formData' => [
                'rooms' => Room::query()->orderBy('name')->get(['id', 'name']),
            ],
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Bed::class);

        $data = $request->validate([
            'number' => 'required|string|max:191',
            'room_id' => 'required|exists:rooms,id',
            'is_occupied' => 'nullable|boolean',
        ]);

        $data['is_occupied'] = $request->boolean('is_occupied');

        Bed::create($data);

        return redirect()
            ->route('react.beds.index')
            ->with('success', localize('global.bed_created_successfully.'));
    }

    public function edit(Request $request, Bed $bed): Response
    {
        $this->authorize('update', $bed);

        return Inertia::render('Beds/Edit', [
            'bed' => [
                'id' => $bed->id,
                'number' => $bed->number,
                'room_id' => (string) $bed->room_id,
                'is_occupied' => (bool) $bed->is_occupied,
            ],
            'formData' => [
                'rooms' => Room::query()->orderBy('name')->get(['id', 'name']),
            ],
            'urls' => $this->formUrls($bed),
        ]);
    }

    public function update(Request $request, Bed $bed): RedirectResponse
    {
        $this->authorize('update', $bed);

        $data = $request->validate([
            'number' => 'required|string|max:191',
            'room_id' => 'required|exists:rooms,id',
            'is_occupied' => 'nullable|boolean',
        ]);

        $data['is_occupied'] = $request->boolean('is_occupied');

        $bed->update($data);

        return redirect()
            ->route('react.beds.index')
            ->with('success', localize('global.bed_updated_successfully.'));
    }

    public function destroy(Request $request, Bed $bed): RedirectResponse
    {
        $this->authorize('delete', $bed);

        $bed->delete();

        return redirect()
            ->route('react.beds.index')
            ->with('success', localize('global.bed_deleted_successfully.'));
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Bed $bed = null): array
    {
        return [
            'index' => route('react.beds.index'),
            'store' => route('react.beds.store'),
            'update' => $bed ? route('react.beds.update', $bed) : '',
            'back' => route('react.beds.index'),
        ];
    }
}
