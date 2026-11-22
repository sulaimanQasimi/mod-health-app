<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesDepotAccess;
use App\Http\Controllers\Concerns\ProvidesDepotFormData;
use App\Models\DepotTransferRequest;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Support\DepotRolePermissions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DepotReportController extends Controller
{
    use ManagesDepotAccess;
    use ProvidesDepotFormData;

    private const LOW_STOCK_THRESHOLD = 10;

    public function index(): Response
    {
        $this->authorizeDepotPermission('depot.report.export');

        $options = $this->depotFormOptions();
        $allowedDepotIds = $this->allowedDepotIdsForAction(DepotRolePermissions::ACTION_REPORT_EXPORT);
        $activeDepotIds = collect($options['activeDepots'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->when(! $this->isDepotSystemAdmin(), fn ($ids) => $ids->intersect($allowedDepotIds))
            ->values()
            ->all();

        $stocks = Stock::query()
            ->where('location_type', Stock::LOCATION_DEPOT)
            ->when($activeDepotIds !== [], fn ($q) => $q->whereIn('location_id', $activeDepotIds))
            ->where('qty_on_hand', '>', 0)
            ->get();

        $transfers = DepotTransferRequest::query()
            ->when(! $this->isDepotSystemAdmin(), function ($q) use ($allowedDepotIds) {
                $q->whereIn('source_depot_id', $allowedDepotIds);
            });

        $requestCounts = (clone $transfers)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $movementCounts = StockMovement::query()
            ->when(! $this->isDepotSystemAdmin(), function ($q) use ($allowedDepotIds) {
                $q->where(function ($inner) use ($allowedDepotIds) {
                    $inner->where(function ($from) use ($allowedDepotIds) {
                        $from->where('from_location_type', Stock::LOCATION_DEPOT)
                            ->whereIn('from_location_id', $allowedDepotIds);
                    })->orWhere(function ($to) use ($allowedDepotIds) {
                        $to->where('to_location_type', Stock::LOCATION_DEPOT)
                            ->whereIn('to_location_id', $allowedDepotIds);
                    });
                });
            })
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        $statuses = [
            DepotTransferRequest::STATUS_DRAFT,
            DepotTransferRequest::STATUS_PENDING,
            DepotTransferRequest::STATUS_APPROVED,
            DepotTransferRequest::STATUS_FULFILLED,
            DepotTransferRequest::STATUS_REJECTED,
            DepotTransferRequest::STATUS_CANCELLED,
        ];

        $movementTypes = [
            StockMovement::TYPE_PURCHASE_RECEIVE,
            StockMovement::TYPE_DEPOT_TO_PHARMACY,
            StockMovement::TYPE_DEPOT_TO_DEPOT,
            StockMovement::TYPE_SALE,
        ];

        return Inertia::render('Depots/Reports/Index', [
            'filterOptions' => [
                'depots' => $options['activeDepots'],
                'pharmacies' => $options['pharmacies'],
                'medicines' => $options['medicines'],
                'tools' => $options['tools'] ?? [],
                'transactionTypes' => $movementTypes,
                'transactionStatuses' => ['completed'],
                'requestStatuses' => $statuses,
            ],
            'summary' => [
                'stock_items' => $stocks->count(),
                'stock_quantity' => (int) $stocks->sum('qty_on_hand'),
                'low_stock' => $stocks
                    ->filter(fn (Stock $item) => $item->qty_on_hand > 0 && $item->qty_on_hand <= self::LOW_STOCK_THRESHOLD)
                    ->count(),
                'pending_requests' => (int) ($requestCounts[DepotTransferRequest::STATUS_PENDING] ?? 0),
                'fulfilled_requests' => (int) ($requestCounts[DepotTransferRequest::STATUS_FULFILLED] ?? 0),
            ],
            'analytics' => [
                'stock_by_type' => [
                    ['name' => 'medicine', 'count' => $stocks->count()],
                    ['name' => 'tool', 'count' => 0],
                ],
                'requests_by_status' => collect($statuses)
                    ->map(fn ($status) => ['name' => $status, 'count' => (int) ($requestCounts[$status] ?? 0)])
                    ->values()
                    ->all(),
                'transactions_by_type' => collect($movementTypes)
                    ->map(fn ($type) => ['name' => $type, 'count' => (int) ($movementCounts[$type] ?? 0)])
                    ->values()
                    ->all(),
            ],
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
            'urls' => [
                'export' => route('depots.reports.export'),
            ],
        ]);
    }

    public function export(Request $request): ?StreamedResponse
    {
        $this->authorizeDepotPermission('depot.report.export');

        $filename = 'depot-stock-'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'batch_number',
                'medicine_id',
                'location_type',
                'location_id',
                'qty_on_hand',
                'qty_sold',
                'unit_sale_price',
                'pack_price',
                'expiry_date',
            ]);

            $query = Stock::query()->orderBy('batch_number');
            if ($request->filled('depot_id')) {
                $query->forDepot($request->integer('depot_id'));
            }

            $query->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->batch_number,
                        $row->medicine_id,
                        $row->location_type,
                        $row->location_id,
                        $row->qty_on_hand,
                        $row->qty_sold,
                        $row->unit_sale_price,
                        $row->pack_price,
                        optional($row->expiry_date)?->format('Y-m-d'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
