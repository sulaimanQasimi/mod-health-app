<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\MedicineUsageType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MedicineUsageTypeController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', MedicineUsageType::class);

        $query = MedicineUsageType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request);

        return Inertia::render('MedicineUsageTypes/Index', [
            'medicineUsageTypes' => $this->paginationPayload($paginator, fn (MedicineUsageType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'description' => $type->description,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-medicines-usage-types',
                'edit-medicines-usage-types',
                'delete-medicines-usage-types',
            ),
            'urls' => [
                'index' => route('medicine-usage-types.index'),
                'create' => route('medicine-usage-types.create'),
                'edit' => url('/medicine-usage-types'),
                'destroy' => url('/medicine-usage-types'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', MedicineUsageType::class);

        return Inertia::render('MedicineUsageTypes/Create', [
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MedicineUsageType::class);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        MedicineUsageType::create($data);

        return redirect()
            ->route('medicine-usage-types.index')
            ->with('success', localize('global.medicine_usage_type_created_successfully.'));
    }

    public function edit(Request $request, MedicineUsageType $medicineUsageType): Response
    {
        $this->authorize('update', $medicineUsageType);

        return Inertia::render('MedicineUsageTypes/Edit', [
            'medicineUsageType' => [
                'id' => $medicineUsageType->id,
                'name' => $medicineUsageType->name,
                'description' => $medicineUsageType->description ?? '',
            ],
            'urls' => $this->formUrls($medicineUsageType),
        ]);
    }

    public function update(Request $request, MedicineUsageType $medicineUsageType): RedirectResponse
    {
        $this->authorize('update', $medicineUsageType);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $medicineUsageType->update($data);

        return redirect()
            ->route('medicine-usage-types.index')
            ->with('success', localize('global.medicine_usage_type_updated_successfully.'));
    }

    public function destroy(Request $request, MedicineUsageType $medicineUsageType): RedirectResponse
    {
        $this->authorize('delete', $medicineUsageType);

        $medicineUsageType->delete();

        return redirect()
            ->route('medicine-usage-types.index')
            ->with('success', localize('global.medicine_usage_type_deleted_successfully.'));
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?MedicineUsageType $medicineUsageType = null): array
    {
        return [
            'index' => route('medicine-usage-types.index'),
            'store' => route('medicine-usage-types.store'),
            'update' => $medicineUsageType ? route('medicine-usage-types.update', $medicineUsageType) : '',
            'back' => route('medicine-usage-types.index'),
        ];
    }
}
