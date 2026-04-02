<?php

namespace App\Http\Controllers;

use App\Models\ProstheticComponentCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProstheticCatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = ProstheticComponentCatalog::query()->orderBy('category')->orderBy('name');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('item_code', 'like', '%'.$q.'%')
                    ->orWhere('name', 'like', '%'.$q.'%')
                    ->orWhere('category', 'like', '%'.$q.'%');
            });
        }

        $items = $query->paginate(40)->withQueryString();

        return view('pages.prosthetics.catalog.index', compact('items'));
    }

    public function create()
    {
        return view('pages.prosthetics.catalog.create');
    }

    public function store(Request $request)
    {
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

        return redirect()->route('prosthetics.catalog.index')->with('success', __('global.success'));
    }

    public function edit(ProstheticComponentCatalog $item)
    {
        return view('pages.prosthetics.catalog.edit', ['item' => $item]);
    }

    public function update(Request $request, ProstheticComponentCatalog $item)
    {
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

        return redirect()->route('prosthetics.catalog.index')->with('success', __('global.success'));
    }
}
