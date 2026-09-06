<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesProstheticsAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\ProstheticComponentCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProstheticCatalogController extends Controller
{
    use ManagesProstheticsAccess;
    use PaginatesInertiaIndex;

    public function index(Request $request): Response
    {
        $this->authorizeProstheticsMenu();

        $query = ProstheticComponentCatalog::query()->orderBy('category')->orderBy('name');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('item_code', 'like', '%'.$q.'%')
                    ->orWhere('name', 'like', '%'.$q.'%')
                    ->orWhere('category', 'like', '%'.$q.'%');
            });
        }

        $paginator = $this->paginateQuery($query, $request, 40, [20, 40, 80]);
        $items = $this->paginationPayload($paginator, fn (ProstheticComponentCatalog $item) => [
            'id' => $item->id,
            'item_code' => $item->item_code,
            'name' => $item->name,
            'category' => $item->category,
            'standard_cost' => $item->standard_cost,
        ]);

        return Inertia::render('Prosthetics/Catalog/Index', [
            'items' => $items,
            'filters' => array_merge(['q' => ''], $request->only(['q'])),
            'permissions' => [
                'manage' => $this->canManageCatalog(),
            ],
            'urls' => [
                'current' => route('prosthetics.catalog.index'),
                'create' => route('prosthetics.catalog.create'),
                'edit' => url('/prosthetics/catalog'),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorizeProstheticsMenu();
        abort_unless($this->canManageCatalog(), 403);

        return Inertia::render('Prosthetics/Catalog/Create', [
            'urls' => [
                'index' => route('prosthetics.catalog.index'),
                'store' => route('prosthetics.catalog.store'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeProstheticsMenu();
        abort_unless($this->canManageCatalog(), 403);

        $data = $request->validate([
            'item_code' => 'required|string|max:64|unique:prosthetic_component_catalog,item_code',
            'name' => 'required|string|max:255',
            'local_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:128',
            'subcategory' => 'nullable|string|max:128',
            'brand' => 'nullable|string|max:128',
            'unit_of_measure' => 'nullable|string|max:32',
            'standard_cost' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'tracks_serial' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $data['tracks_serial'] = $request->boolean('tracks_serial');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        ProstheticComponentCatalog::create($data);

        return redirect()
            ->route('prosthetics.catalog.index')
            ->with('success', __('global.success'));
    }

    public function edit(ProstheticComponentCatalog $item): Response
    {
        $this->authorizeProstheticsMenu();
        abort_unless($this->canManageCatalog(), 403);

        return Inertia::render('Prosthetics/Catalog/Edit', [
            'item' => [
                'id' => $item->id,
                'item_code' => $item->item_code,
                'name' => $item->name,
                'local_name' => $item->local_name,
                'category' => $item->category,
                'subcategory' => $item->subcategory,
                'brand' => $item->brand,
                'unit_of_measure' => $item->unit_of_measure,
                'standard_cost' => $item->standard_cost,
                'minimum_stock' => $item->minimum_stock,
                'tracks_serial' => (bool) $item->tracks_serial,
                'is_active' => (bool) $item->is_active,
            ],
            'urls' => [
                'index' => route('prosthetics.catalog.index'),
                'update' => route('prosthetics.catalog.update', $item),
            ],
        ]);
    }

    public function update(Request $request, ProstheticComponentCatalog $item): RedirectResponse
    {
        $this->authorizeProstheticsMenu();
        abort_unless($this->canManageCatalog(), 403);

        $data = $request->validate([
            'item_code' => 'required|string|max:64|unique:prosthetic_component_catalog,item_code,'.$item->id,
            'name' => 'required|string|max:255',
            'local_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:128',
            'subcategory' => 'nullable|string|max:128',
            'brand' => 'nullable|string|max:128',
            'unit_of_measure' => 'nullable|string|max:32',
            'standard_cost' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'tracks_serial' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $data['tracks_serial'] = $request->boolean('tracks_serial');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['updated_by'] = Auth::id();

        $item->update($data);

        return redirect()
            ->route('prosthetics.catalog.index')
            ->with('success', __('global.success'));
    }
}
