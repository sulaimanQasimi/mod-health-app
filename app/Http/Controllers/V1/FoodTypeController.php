<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\FoodType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FoodTypeController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FoodType::class);

        $query = FoodType::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request);

        return Inertia::render('FoodTypes/Index', [
            'foodTypes' => $this->paginationPayload($paginator, fn (FoodType $foodType) => [
                'id' => $foodType->id,
                'name' => $foodType->name,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-hospitalization-foods',
                'edit-hospitalization-foods',
                'delete-hospitalization-foods',
            ),
            'urls' => [
                'index' => route('food-types.index'),
                'create' => route('food-types.create'),
                'edit' => url('/food-types'),
                'destroy' => url('/food-types'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', FoodType::class);

        return Inertia::render('FoodTypes/Create', [
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', FoodType::class);

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        FoodType::create($data);

        return redirect()
            ->route('food-types.index')
            ->with('success', localize('global.food_type_created_successfully.'));
    }

    public function edit(Request $request, FoodType $foodType): Response
    {
        $this->authorize('update', $foodType);

        return Inertia::render('FoodTypes/Edit', [
            'foodType' => [
                'id' => $foodType->id,
                'name' => $foodType->name,
            ],
            'urls' => $this->formUrls($foodType),
        ]);
    }

    public function update(Request $request, FoodType $foodType): RedirectResponse
    {
        $this->authorize('update', $foodType);

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $foodType->update($data);

        return redirect()
            ->route('food-types.index')
            ->with('success', localize('global.food_type_updated_successfully'));
    }

    public function destroy(Request $request, FoodType $foodType): RedirectResponse
    {
        $this->authorize('delete', $foodType);

        $foodType->delete();

        return redirect()
            ->route('food-types.index')
            ->with('success', localize('global.food_type_deleted_successfully.'));
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?FoodType $foodType = null): array
    {
        return [
            'index' => route('food-types.index'),
            'store' => route('food-types.store'),
            'update' => $foodType ? route('food-types.update', $foodType) : '',
            'back' => route('food-types.index'),
        ];
    }
}
