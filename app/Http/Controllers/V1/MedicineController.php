<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Medicine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MedicineController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'sort_by', 'sort_order', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Medicine::class);

        $query = Medicine::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $sortBy = $request->input('sort_by', 'id');
        $allowedSortFields = ['id', 'name', 'created_at'];
        if (! in_array($sortBy, $allowedSortFields, true)) {
            $sortBy = 'id';
        }

        $sortOrder = $request->input('sort_order', 'desc');
        if (! in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'desc';
        }

        $paginator = $this->paginateQuery(
            $query->orderBy($sortBy, $sortOrder),
            $request,
        );

        return Inertia::render('Medicines/Index', [
            'medicines' => $this->paginationPayload($paginator, fn (Medicine $medicine) => [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'created_at' => $medicine->created_at?->format('Y-m-d H:i:s'),
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-medicines',
                'edit-medicines',
                'delete-medicines',
            ),
            'urls' => [
                'index' => route('react.medicines.index'),
                'create' => route('react.medicines.create'),
                'edit' => url('/react/medicines'),
                'destroy' => url('/react/medicines'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Medicine::class);

        return Inertia::render('Medicines/Create', [
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Medicine::class);

        $data = $request->validate([
            'name' => 'required|string|max:192',
        ]);

        Medicine::create($data);

        return redirect()
            ->route('react.medicines.index')
            ->with('success', localize('global.medicine_created_successfully.'));
    }

    public function edit(Request $request, Medicine $medicine): Response
    {
        $this->authorize('update', $medicine);

        return Inertia::render('Medicines/Edit', [
            'medicine' => [
                'id' => $medicine->id,
                'name' => $medicine->name,
            ],
            'urls' => $this->formUrls($medicine),
        ]);
    }

    public function update(Request $request, Medicine $medicine): RedirectResponse
    {
        $this->authorize('update', $medicine);

        $data = $request->validate([
            'name' => 'required|string|max:192',
        ]);

        $medicine->update($data);

        return redirect()
            ->route('react.medicines.index')
            ->with('success', localize('global.medicine_updated_successfully.'));
    }

    public function destroy(Request $request, Medicine $medicine): RedirectResponse
    {
        $this->authorize('delete', $medicine);

        $medicine->delete();

        return redirect()
            ->route('react.medicines.index')
            ->with('success', localize('global.medicine_deleted_successfully.'));
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Medicine $medicine = null): array
    {
        return [
            'index' => route('react.medicines.index'),
            'store' => route('react.medicines.store'),
            'update' => $medicine ? route('react.medicines.update', $medicine) : '',
            'back' => route('react.medicines.index'),
        ];
    }
}
