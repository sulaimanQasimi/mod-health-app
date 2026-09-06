<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Branch;
use App\Models\Floor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FloorController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'branch_id', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Floor::class);

        $query = Floor::query()->with('branch:id,name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request, 25);

        return Inertia::render('Floors/Index', [
            'floors' => $this->paginationPayload($paginator, fn (Floor $floor) => [
                'id' => $floor->id,
                'name' => $floor->name,
                'branch_name' => $floor->branch?->name,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
            ],
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-floors',
                'edit-floors',
                'delete-floors',
            ),
            'urls' => [
                'index' => route('floors.index'),
                'create' => route('floors.create'),
                'edit' => url('/floors'),
                'destroy' => url('/floors'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Floor::class);

        return Inertia::render('Floors/Create', [
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Floor::class);

        Floor::create($this->validateFloor($request));

        return redirect()
            ->route('floors.index')
            ->with('success', localize('global.floor_created_successfully.'));
    }

    public function edit(Request $request, Floor $floor): Response
    {
        $this->authorize('update', $floor);

        return Inertia::render('Floors/Edit', [
            'floor' => [
                'id' => $floor->id,
                'name' => $floor->name,
                'branch_id' => (string) $floor->branch_id,
            ],
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls($floor),
        ]);
    }

    public function update(Request $request, Floor $floor): RedirectResponse
    {
        $this->authorize('update', $floor);

        $floor->update($this->validateFloor($request));

        return redirect()
            ->route('floors.index')
            ->with('success', localize('global.floor_updated_successfully.'));
    }

    public function destroy(Request $request, Floor $floor): RedirectResponse
    {
        $this->authorize('delete', $floor);

        $floor->delete();

        return redirect()
            ->route('floors.index')
            ->with('success', localize('global.floor_deleted_successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormData(): array
    {
        return [
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Floor $floor = null): array
    {
        return [
            'index' => route('floors.index'),
            'store' => route('floors.store'),
            'update' => $floor ? route('floors.update', $floor) : '',
            'back' => route('floors.index'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateFloor(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:191',
            'branch_id' => 'required|exists:branches,id',
        ]);
    }
}
