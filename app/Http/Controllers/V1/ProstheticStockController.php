<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProstheticStockController as LegacyProstheticStockController;
use App\Http\Controllers\V1\Concerns\ManagesProstheticsAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\ProstheticComponentCatalog;
use App\Models\ProstheticStockBalance;
use App\Models\ProstheticStockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProstheticStockController extends Controller
{
    use ManagesProstheticsAccess;
    use PaginatesInertiaIndex;

    public function index(Request $request): Response
    {
        $this->authorizeProstheticsMenu();

        $branchId = $this->userBranchId();

        $query = ProstheticStockBalance::query()
            ->with('catalogItem:id,item_code,name')
            ->when($branchId !== null, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('quantity');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('catalogItem', function ($w) use ($q) {
                $w->where('item_code', 'like', '%'.$q.'%')
                    ->orWhere('name', 'like', '%'.$q.'%');
            });
        }

        $paginator = $this->paginateQuery($query, $request, 40, [20, 40, 80]);
        $balances = $this->paginationPayload($paginator, fn (ProstheticStockBalance $balance) => [
            'id' => $balance->id,
            'quantity' => (float) $balance->quantity,
            'catalog_item' => $balance->catalogItem ? [
                'item_code' => $balance->catalogItem->item_code,
                'name' => $balance->catalogItem->name,
            ] : null,
        ]);

        $movements = ProstheticStockMovement::query()
            ->with(['catalogItem:id,item_code,name'])
            ->when($branchId !== null, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (ProstheticStockMovement $movement) => [
                'id' => $movement->id,
                'movement_type' => $movement->movement_type,
                'quantity_delta' => (float) $movement->quantity_delta,
                'created_at' => $movement->created_at?->format('Y-m-d H:i'),
                'catalog_item' => $movement->catalogItem ? [
                    'item_code' => $movement->catalogItem->item_code,
                    'name' => $movement->catalogItem->name,
                ] : null,
            ]);

        $catalogForReceive = ProstheticComponentCatalog::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'item_code', 'name']);

        return Inertia::render('Prosthetics/Stock/Index', [
            'balances' => $balances,
            'movements' => $movements,
            'catalogForReceive' => $catalogForReceive,
            'filters' => array_merge(['q' => ''], $request->only(['q'])),
            'permissions' => [
                'manage' => $this->canManageStock(),
            ],
            'urls' => [
                'current' => route('prosthetics.stock.index'),
                'receive' => route('prosthetics.stock.receive'),
            ],
        ]);
    }

    public function receive(Request $request): RedirectResponse
    {
        $this->authorizeProstheticsMenu();
        abort_unless($this->canManageStock(), 403);

        return app(LegacyProstheticStockController::class)->receive($request, app(\App\Services\ProstheticsStockService::class));
    }
}
