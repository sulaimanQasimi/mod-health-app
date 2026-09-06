<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesDepotAccess;
use App\Http\Controllers\V1\Concerns\ProvidesDepotFormData;
use App\Models\DepotRequest;
use App\Models\DepotTransaction;
use App\Services\DepotStockService;
use App\Support\DepotRolePermissions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DepotReportController extends Controller
{
    use ManagesDepotAccess;
    use ProvidesDepotFormData;

    public function __construct(
        private readonly DepotStockService $stockService,
    ) {
    }

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
        $stockItems = collect();

        foreach ($activeDepotIds as $depotId) {
            $stockItems = $stockItems->merge($this->stockService->stockItemsForDepot($depotId));
        }

        $transactions = DepotTransaction::query();
        $requests = DepotRequest::query();

        if (! $this->isDepotSystemAdmin()) {
            $transactions->where(function ($query) use ($allowedDepotIds) {
                $query->whereIn('depot_id', $allowedDepotIds)
                    ->orWhereIn('from_depot_id', $allowedDepotIds)
                    ->orWhereIn('to_depot_id', $allowedDepotIds);
            });
            $requests->where(function ($query) use ($allowedDepotIds) {
                $query->whereIn('requesting_depot_id', $allowedDepotIds)
                    ->orWhereIn('source_depot_id', $allowedDepotIds);
            });
        }

        $requestCounts = (clone $requests)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        $transactionCounts = (clone $transactions)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        return Inertia::render('Depots/Reports/Index', [
            'filterOptions' => [
                'depots' => $options['activeDepots'],
                'pharmacies' => $options['pharmacies'],
                'medicines' => $options['medicines'],
                'tools' => $options['tools'],
                'transactionTypes' => DepotTransaction::types(),
                'transactionStatuses' => DepotTransaction::statuses(),
                'requestStatuses' => DepotRequest::statuses(),
            ],
            'summary' => [
                'stock_items' => $stockItems->count(),
                'stock_quantity' => (int) $stockItems->sum('available'),
                'low_stock' => $stockItems
                    ->filter(fn ($item) => $item['available'] > 0 && $item['available'] <= DepotStockService::LOW_STOCK_THRESHOLD)
                    ->count(),
                'pending_requests' => (int) ($requestCounts[DepotRequest::STATUS_PENDING] ?? 0),
                'fulfilled_requests' => (int) ($requestCounts[DepotRequest::STATUS_FULFILLED] ?? 0),
            ],
            'analytics' => [
                'stock_by_type' => [
                    ['name' => 'medicine', 'count' => $stockItems->where('item_type', DepotTransaction::ITEM_MEDICINE)->count()],
                    ['name' => 'tool', 'count' => $stockItems->where('item_type', DepotTransaction::ITEM_TOOL)->count()],
                ],
                'requests_by_status' => collect(DepotRequest::statuses())
                    ->map(fn ($status) => ['name' => $status, 'count' => (int) ($requestCounts[$status] ?? 0)])
                    ->values()
                    ->all(),
                'transactions_by_type' => collect(DepotTransaction::types())
                    ->map(fn ($type) => ['name' => $type, 'count' => (int) ($transactionCounts[$type] ?? 0)])
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

        if (! $this->isDepotSystemAdmin()) {
            $allowedIds = $this->allowedDepotIdsForAction(DepotRolePermissions::ACTION_REPORT_EXPORT);
            abort_if($allowedIds === [], 403);

            foreach (['depot_id', 'requesting_depot_id', 'source_depot_id'] as $filterKey) {
                if ($request->filled($filterKey) && ! in_array((int) $request->input($filterKey), $allowedIds, true)) {
                    abort(403);
                }
            }

            $request->merge(['_allowed_depot_ids' => $allowedIds]);
        }

        return app(\App\Http\Controllers\DepotReportController::class)->export($request);
    }
}
