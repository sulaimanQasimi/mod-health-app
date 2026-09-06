<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\MedicineType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MedicineTypeController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', MedicineType::class);

        $query = MedicineType::query();

        if ($request->filled('search')) {
            $query->where('type', 'like', '%'.$request->search.'%');
        }

        $paginator = $this->paginateQuery($query->orderBy('type'), $request);

        return Inertia::render('MedicineTypes/Index', [
            'medicineTypes' => $this->paginationPayload($paginator, fn (MedicineType $medicineType) => [
                'id' => $medicineType->id,
                'type' => $medicineType->type,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-medicine-types',
                'edit-medicine-types',
                'delete-medicine-types',
            ),
            'urls' => [
                'index' => route('medicine-types.index'),
                'create' => route('medicine-types.create'),
                'edit' => url('/medicine-types'),
                'destroy' => url('/medicine-types'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', MedicineType::class);

        return Inertia::render('MedicineTypes/Create', [
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MedicineType::class);

        $data = $request->validate([
            'type' => 'required|string|max:191',
        ]);

        MedicineType::create($data);

        return redirect()
            ->route('medicine-types.index')
            ->with('success', localize('global.medicine_type_created_successfully.'));
    }

    public function edit(Request $request, MedicineType $medicineType): Response
    {
        $this->authorize('update', $medicineType);

        return Inertia::render('MedicineTypes/Edit', [
            'medicineType' => [
                'id' => $medicineType->id,
                'type' => $medicineType->type,
            ],
            'urls' => $this->formUrls($medicineType),
        ]);
    }

    public function update(Request $request, MedicineType $medicineType): RedirectResponse
    {
        $this->authorize('update', $medicineType);

        $data = $request->validate([
            'type' => 'required|string|max:191',
        ]);

        $medicineType->update($data);

        return redirect()
            ->route('medicine-types.index')
            ->with('success', localize('global.medicine_type_updated_successfully.'));
    }

    public function destroy(Request $request, MedicineType $medicineType): RedirectResponse
    {
        $this->authorize('delete', $medicineType);

        $medicineType->delete();

        return redirect()
            ->route('medicine-types.index')
            ->with('success', localize('global.medicine_type_deleted_successfully.'));
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?MedicineType $medicineType = null): array
    {
        return [
            'index' => route('medicine-types.index'),
            'store' => route('medicine-types.store'),
            'update' => $medicineType ? route('medicine-types.update', $medicineType) : '',
            'back' => route('medicine-types.index'),
        ];
    }
}
