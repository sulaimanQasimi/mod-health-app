<?php

namespace App\Http\Controllers;

use App\Models\ProstheticComponentCatalog;
use App\Models\ProstheticStockBalance;
use App\Models\ProstheticStockMovement;
use App\Services\ProstheticsStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProstheticStockController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        $query = ProstheticStockBalance::query()
            ->with('catalogItem')
            ->when($branchId !== null, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('quantity');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('catalogItem', function ($w) use ($q) {
                $w->where('item_code', 'like', '%'.$q.'%')
                    ->orWhere('name', 'like', '%'.$q.'%');
            });
        }

        $balances = $query->paginate(40)->withQueryString();

        $movements = ProstheticStockMovement::query()
            ->with(['catalogItem'])
            ->when($branchId !== null, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $catalogForReceive = ProstheticComponentCatalog::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('pages.prosthetics.stock.index', compact('balances', 'movements', 'catalogForReceive'));
    }

    public function receive(Request $request, ProstheticsStockService $stock)
    {
        $data = $request->validate([
            'prosthetic_component_catalog_id' => 'required|exists:prosthetic_component_catalog,id',
            'quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string',
        ]);

        try {
            $stock->receive(
                (int) $data['prosthetic_component_catalog_id'],
                auth()->user()->branch_id,
                (float) $data['quantity'],
                $data['notes'] ?? null
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('global.success'));
    }
}
