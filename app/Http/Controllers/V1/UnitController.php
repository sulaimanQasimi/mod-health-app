<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Unit::class);

        $query = Unit::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('symbol', 'like', "%{$search}%");
            });
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request);

        return Inertia::render('Units/Index', [
            'units' => $this->paginationPayload($paginator, fn (Unit $unit) => [
                'id' => $unit->id,
                'name' => $unit->name,
                'symbol' => $unit->symbol,
                'is_active' => (bool) $unit->is_active,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-units',
                'edit-units',
                'delete-units',
            ),
            'urls' => [
                'index' => route('react.units.index'),
                'create' => route('react.units.create'),
                'edit' => url('/react/units'),
                'destroy' => url('/react/units'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Unit::class);

        return Inertia::render('Units/Create', [
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Unit::class);

        Unit::create($this->validateUnit($request));

        return redirect()
            ->route('react.units.index')
            ->with('success', localize('global.unit_created_successfully'));
    }

    public function edit(Request $request, Unit $unit): Response
    {
        $this->authorize('update', $unit);

        return Inertia::render('Units/Edit', [
            'unit' => [
                'id' => $unit->id,
                'name' => $unit->name,
                'symbol' => $unit->symbol,
                'is_active' => (bool) $unit->is_active,
            ],
            'urls' => $this->formUrls($unit),
        ]);
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $this->authorize('update', $unit);

        $unit->update($this->validateUnit($request, $unit));

        return redirect()
            ->route('react.units.index')
            ->with('success', localize('global.unit_updated_successfully'));
    }

    public function destroy(Request $request, Unit $unit): RedirectResponse
    {
        $this->authorize('delete', $unit);

        $unit->delete();

        return redirect()
            ->route('react.units.index')
            ->with('success', localize('global.unit_deleted_successfully'));
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Unit $unit = null): array
    {
        return [
            'index' => route('react.units.index'),
            'store' => route('react.units.store'),
            'update' => $unit ? route('react.units.update', $unit) : '',
            'back' => route('react.units.index'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateUnit(Request $request, ?Unit $unit = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('units', 'name')->ignore($unit?->id)->whereNull('deleted_at'),
            ],
            'symbol' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($unit) {
            $validated['updated_by'] = $request->user()?->id;
        } else {
            $validated['created_by'] = $request->user()?->id;
        }

        return $validated;
    }
}
