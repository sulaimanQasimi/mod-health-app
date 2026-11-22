<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PaginatesInertiaIndex;
use App\Models\Medicine;
use App\Models\MedicineCountry;
use App\Models\MedicineFormula;
use App\Models\MedicineGramMl;
use App\Models\MedicineType;
use App\Models\Unit;
use App\Support\InventoryNav;
use App\Support\MedicineNav;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MedicineController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'sort_by', 'sort_order', 'per_page', 'medicine_type_id', 'medicine_country_id'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Medicine::class);

        $query = Medicine::query()
            ->with([
                'medicineType:id,type',
                'medicineCountry:id,name',
                'medicineFormula:id,name',
                'medicineGramMl:id,name,gram,ml',
            ]);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }
        if ($request->filled('medicine_type_id')) {
            $query->where('medicine_type_id', $request->integer('medicine_type_id'));
        }
        if ($request->filled('medicine_country_id')) {
            $query->where('medicine_country_id', $request->integer('medicine_country_id'));
        }

        $sortBy = $request->input('sort_by', 'id');
        if (! in_array($sortBy, ['id', 'name', 'created_at'], true)) {
            $sortBy = 'id';
        }
        $sortOrder = $request->input('sort_order', 'desc');
        if (! in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'desc';
        }

        $paginator = $this->paginateQuery($query->orderBy($sortBy, $sortOrder), $request);

        return Inertia::render('Medicines/Index', [
            'medicines' => $this->paginationPayload($paginator, fn (Medicine $medicine) => [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'type' => $medicine->medicineType?->type,
                'country' => $medicine->medicineCountry?->name,
                'formula' => $medicine->medicineFormula?->name,
                'gram_ml' => $medicine->medicineGramMl?->name,
                'units_per_pack' => $medicine->unitsPerPack(),
                'created_at' => $medicine->created_at?->format('Y-m-d H:i:s'),
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'types' => MedicineType::query()->orderBy('type')->get(['id', 'type']),
                'countries' => MedicineCountry::query()->orderBy('name')->get(['id', 'name']),
            ],
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-medicines',
                'edit-medicines',
                'delete-medicines',
            ),
            'inventoryNav' => InventoryNav::urls(),
            'medicineNav' => MedicineNav::urls(),
            'urls' => [
                'index' => route('medicines.index'),
                'create' => route('medicines.create'),
                'edit' => url('/medicines'),
                'destroy' => url('/medicines'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Medicine::class);

        return Inertia::render('Medicines/Create', [
            'formData' => $this->formOptions(),
            'inventoryNav' => InventoryNav::urls(),
            'medicineNav' => MedicineNav::urls(),
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Medicine::class);

        Medicine::create($this->validatedMedicine($request));

        return redirect()
            ->route('medicines.index')
            ->with('success', localize('global.medicine_created_successfully.'));
    }

    public function edit(Request $request, Medicine $medicine): Response
    {
        $this->authorize('update', $medicine);

        return Inertia::render('Medicines/Edit', [
            'medicine' => [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'medicine_type_id' => $medicine->medicine_type_id,
                'medicine_country_id' => $medicine->medicine_country_id,
                'medicine_formula_id' => $medicine->medicine_formula_id,
                'medicine_gram_ml_id' => $medicine->medicine_gram_ml_id,
                'unit_id' => $medicine->unit_id,
                'units_per_pack' => $medicine->unitsPerPack(),
            ],
            'formData' => $this->formOptions(),
            'inventoryNav' => InventoryNav::urls(),
            'medicineNav' => MedicineNav::urls(),
            'urls' => $this->formUrls($medicine),
        ]);
    }

    public function update(Request $request, Medicine $medicine): RedirectResponse
    {
        $this->authorize('update', $medicine);

        $medicine->update($this->validatedMedicine($request));

        return redirect()
            ->route('medicines.index')
            ->with('success', localize('global.medicine_updated_successfully.'));
    }

    public function destroy(Request $request, Medicine $medicine): RedirectResponse
    {
        $this->authorize('delete', $medicine);

        $medicine->delete();

        return redirect()
            ->route('medicines.index')
            ->with('success', localize('global.medicine_deleted_successfully.'));
    }

    private function validatedMedicine(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:192',
            'medicine_type_id' => 'nullable|integer|exists:medicine_types,id',
            'medicine_country_id' => 'nullable|integer|exists:medicine_countries,id',
            'medicine_formula_id' => 'nullable|integer|exists:medicine_formulas,id',
            'medicine_gram_ml_id' => 'nullable|integer|exists:medicine_gram_mls,id',
            'unit_id' => 'nullable|integer|exists:units,id',
            'units_per_pack' => 'nullable|integer|min:1',
        ]);

        $data['units_per_pack'] = max(1, (int) ($data['units_per_pack'] ?? 1));

        return $data;
    }

    private function formOptions(): array
    {
        return [
            'types' => MedicineType::query()->orderBy('type')->get(['id', 'type']),
            'countries' => MedicineCountry::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'formulas' => MedicineFormula::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'gramMls' => MedicineGramMl::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'gram', 'ml']),
            'units' => Unit::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'symbol']),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Medicine $medicine = null): array
    {
        return [
            'index' => route('medicines.index'),
            'store' => route('medicines.store'),
            'update' => $medicine ? route('medicines.update', $medicine) : '',
            'back' => route('medicines.index'),
        ];
    }
}
