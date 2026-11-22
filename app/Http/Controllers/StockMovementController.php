<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Support\InventoryNav;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockMovementController extends Controller
{
    public function index(Request $request): Response
    {
        $movements = StockMovement::query()
            ->with(['medicine:id,name', 'user:id,name'])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search')->toString();
                $q->where(function ($inner) use ($search) {
                    $inner->where('batch_number', 'like', "%{$search}%")
                        ->orWhereHas('medicine', fn ($m) => $m->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $payload = $movements->through(function (StockMovement $row) {
            return [
                'id' => $row->id,
                'type' => $row->type,
                'batch_number' => $row->batch_number,
                'medicine' => $row->medicine?->only(['id', 'name']),
                'from' => $this->locationLabel($row->from_location_type, $row->from_location_id),
                'to' => $this->locationLabel($row->to_location_type, $row->to_location_id),
                'qty_units' => $row->qty_units,
                'user' => $row->user?->name,
                'created_at' => optional($row->created_at)?->format('Y-m-d H:i'),
            ];
        });

        return Inertia::render('StockMovements/Index', [
            'movements' => $payload,
            'filters' => [
                'search' => $request->input('search', ''),
                'type' => $request->input('type', ''),
            ],
            'inventoryNav' => InventoryNav::urls(),
            'urls' => [
                'index' => route('stock-movements.index'),
            ],
        ]);
    }

    private function locationLabel(?string $type, ?int $id): string
    {
        if (! $type || ! $id) {
            return '—';
        }

        return ucfirst($type).' #'.$id;
    }
}
