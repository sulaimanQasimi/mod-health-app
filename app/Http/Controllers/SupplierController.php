<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Support\InventoryNav;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    public function index(Request $request): Response
    {
        $suppliers = Supplier::query()
            ->withCount('purchases')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search')->toString();
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => [
                'search' => $request->input('search', ''),
                'is_active' => $request->input('is_active', ''),
            ],
            'inventoryNav' => InventoryNav::urls(),
            'urls' => [
                'index' => route('suppliers.index'),
                'store' => route('suppliers.store'),
                'update' => route('suppliers.update', ['supplier' => '__ID__']),
                'destroy' => route('suppliers.destroy', ['supplier' => '__ID__']),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Supplier::query()->create([
            ...$data,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return back()->with('success', 'Supplier saved.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $supplier->update($data);

        return back()->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return back()->with('success', 'Supplier removed.');
    }
}
