<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesDepotAccess;
use App\Http\Controllers\Concerns\ProvidesDepotFormData;
use App\Models\Depot;
use App\Models\DepotRequest;
use App\Models\DepotTransaction;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\Tool;
use App\Services\DepotStockService;
use App\Support\DepotUi;
use App\Support\DepotRolePermissions;
use Illuminate\Http\Request;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DepotReportController extends Controller
{
    use ManagesDepotAccess;
    use ProvidesDepotFormData;

    public function __construct(
        private readonly DepotStockService $stockService
    ) {
    }

    public function index(Request $request)
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

        $depotsQuery = Depot::query()->where('is_active', true)->orderBy('name');
        if (! $this->isDepotSystemAdmin()) {
            $depotsQuery->whereIn('id', $allowedDepotIds ?: [0]);
        }

        $summary = [
            'stock_items' => $stockItems->count(),
            'stock_quantity' => (int) $stockItems->sum('available'),
            'low_stock' => $stockItems
                ->filter(fn ($item) => $item['available'] > 0 && $item['available'] <= DepotStockService::LOW_STOCK_THRESHOLD)
                ->count(),
            'pending_requests' => (int) ($requestCounts[DepotRequest::STATUS_PENDING] ?? 0),
            'fulfilled_requests' => (int) ($requestCounts[DepotRequest::STATUS_FULFILLED] ?? 0),
        ];

        $summaryCards = [
            ['label' => localize('global.depot.report_stock'), 'value' => $summary['stock_items'], 'icon' => 'bx-box', 'color' => 'primary'],
            ['label' => localize('global.quantity'), 'value' => $summary['stock_quantity'], 'icon' => 'bx-layer', 'color' => 'success'],
            ['label' => localize('global.low_stock'), 'value' => $summary['low_stock'], 'icon' => 'bx-error', 'color' => 'warning'],
            ['label' => localize('global.pending'), 'value' => $summary['pending_requests'], 'icon' => 'bx-time-five', 'color' => 'info'],
            ['label' => localize('global.fulfilled'), 'value' => $summary['fulfilled_requests'], 'icon' => 'bx-check-circle', 'color' => 'secondary'],
        ];

        $analyticsPanels = [
            [
                'title' => localize('global.depot.report_stock'),
                'rows' => [
                    [
                        'label' => localize('global.medicine'),
                        'count' => $stockItems->where('item_type', DepotTransaction::ITEM_MEDICINE)->count(),
                    ],
                    [
                        'label' => localize('global.depot.tool'),
                        'count' => $stockItems->where('item_type', DepotTransaction::ITEM_TOOL)->count(),
                    ],
                ],
            ],
            [
                'title' => localize('global.depot.report_requests'),
                'rows' => collect(DepotRequest::statuses())
                    ->map(fn ($status) => [
                        'label' => DepotUi::requestStatusLabel($status),
                        'count' => (int) ($requestCounts[$status] ?? 0),
                    ])
                    ->values()
                    ->all(),
            ],
            [
                'title' => localize('global.depot.report_transactions'),
                'rows' => collect(DepotTransaction::types())
                    ->map(fn ($type) => [
                        'label' => DepotUi::typeLabel($type),
                        'count' => (int) ($transactionCounts[$type] ?? 0),
                    ])
                    ->values()
                    ->all(),
            ],
        ];

        return view('pages.depots.reports.index', [
            'depots' => $depotsQuery->get(),
            'pharmacies' => Pharmacy::query()->orderBy('name')->get(),
            'medicines' => Medicine::query()->whereNull('deleted_at')->orderBy('name')->get(),
            'tools' => Tool::query()->where('is_active', true)->orderBy('name')->get(),
            'transactionTypes' => DepotTransaction::types(),
            'transactionStatuses' => DepotTransaction::statuses(),
            'requestStatuses' => DepotRequest::statuses(),
            'requestStatusOptions' => depot_label_options(DepotRequest::statuses()),
            'summary' => $summary,
            'summaryCards' => $summaryCards,
            'analyticsPanels' => $analyticsPanels,
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
        ]);
    }

    public function export(Request $request)
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

        $report = $request->get('report', 'transactions');
        $type = $request->get('type', 'excel');

        return match ($report) {
            'stock' => $this->exportStock($request, $type),
            'movements' => $this->exportMovements($request, $type),
            'requests' => $this->exportRequests($request, $type),
            default => $this->exportTransactions($request, $type),
        };
    }

    private function exportTransactions(Request $request, string $type)
    {
        $items = $this->buildTransactionQuery($request)->get();

        if ($type === 'pdf') {
            $html = view('pages.depots.reports.transactions_pdf', compact('items'))->render();
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('depot_transactions_report.pdf', 'D');

            return null;
        }

        return $this->streamExcel('depot_transactions_report.xlsx', [
            'Number', 'Type', 'Status', 'Source', 'Destination', 'Item Type', 'Item', 'Quantity', 'Date', 'Created By',
        ], $items, function ($item, $row, $sheet) {
            $sheet->setCellValue('A' . $row, $item->transaction_number);
            $sheet->setCellValue('B' . $row, $item->type);
            $sheet->setCellValue('C' . $row, $item->status);
            $sheet->setCellValue('D' . $row, $item->fromDepot?->name ?? $item->depot?->name ?? '-');
            $sheet->setCellValue('E' . $row, $item->toDepot?->name ?? $item->pharmacy?->name ?? '-');
            $sheet->setCellValue('F' . $row, $item->itemType() ?? '-');
            $sheet->setCellValue('G' . $row, $item->medicine?->name ?? $item->tool?->name ?? '-');
            $sheet->setCellValue('H' . $row, $item->quantity);
            $sheet->setCellValue('I' . $row, optional($item->transaction_date)->format('Y-m-d'));
            $sheet->setCellValue('J' . $row, $item->createdBy?->name ?? '-');
        });
    }

    private function exportMovements(Request $request, string $type)
    {
        $query = $this->buildTransactionQuery($request)
            ->whereIn('type', [DepotTransaction::TYPE_DEPOT_TO_DEPOT, DepotTransaction::TYPE_DEPOT_TO_PHARMACY]);

        $items = $query->get();

        if ($type === 'pdf') {
            $html = view('pages.depots.reports.movements_pdf', compact('items'))->render();
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('depot_movements_report.pdf', 'D');

            return null;
        }

        return $this->streamExcel('depot_movements_report.xlsx', [
            'Number', 'Type', 'From Depot', 'To Depot/Pharmacy', 'Item', 'Quantity', 'Status', 'Date',
        ], $items, function ($item, $row, $sheet) {
            $sheet->setCellValue('A' . $row, $item->transaction_number);
            $sheet->setCellValue('B' . $row, $item->type);
            $sheet->setCellValue('C' . $row, $item->fromDepot?->name ?? '-');
            $sheet->setCellValue('D' . $row, $item->toDepot?->name ?? $item->pharmacy?->name ?? '-');
            $sheet->setCellValue('E' . $row, $item->medicine?->name ?? $item->tool?->name ?? '-');
            $sheet->setCellValue('F' . $row, $item->quantity);
            $sheet->setCellValue('G' . $row, $item->status);
            $sheet->setCellValue('H' . $row, optional($item->transaction_date)->format('Y-m-d'));
        });
    }

    private function exportStock(Request $request, string $type)
    {
        $items = $this->stockService->stockReport(
            $request->filled('depot_id') ? (int) $request->depot_id : null,
            $request->filled('item_type') ? $request->item_type : null
        );

        if ($request->filled('_allowed_depot_ids')) {
            $allowedIds = collect($request->input('_allowed_depot_ids'))->map(fn ($id) => (int) $id)->all();
            $items = $items->filter(fn ($item) => in_array((int) $item['depot_id'], $allowedIds, true))->values();
        }

        if ($type === 'pdf') {
            $html = view('pages.depots.reports.stock_pdf', compact('items'))->render();
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('depot_stock_report.pdf', 'D');

            return null;
        }

        return $this->streamExcel('depot_stock_report.xlsx', [
            'Depot', 'Item Type', 'Item', 'Available', 'Unit',
        ], $items, function ($item, $row, $sheet) {
            $sheet->setCellValue('A' . $row, $item['depot_name']);
            $sheet->setCellValue('B' . $row, $item['item_type']);
            $sheet->setCellValue('C' . $row, $item['item_name']);
            $sheet->setCellValue('D' . $row, $item['available']);
            $sheet->setCellValue('E' . $row, $item['unit'] ?? '-');
        });
    }

    private function exportRequests(Request $request, string $type)
    {
        $items = $this->buildRequestQuery($request)->get();

        if ($type === 'pdf') {
            $html = view('pages.depots.reports.requests_pdf', compact('items'))->render();
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('depot_requests_report.pdf', 'D');

            return null;
        }

        return $this->streamExcel('depot_requests_report.xlsx', [
            'Number', 'Status', 'Destination', 'Source Depot', 'Item', 'Quantity', 'Requested By', 'Created At',
        ], $items, function ($item, $row, $sheet) {
            $sheet->setCellValue('A' . $row, $item->request_number);
            $sheet->setCellValue('B' . $row, $item->status);
            $sheet->setCellValue('C' . $row, $item->destinationLabel());
            $sheet->setCellValue('D' . $row, $item->sourceDepot?->name ?? '-');
            $sheet->setCellValue('E' . $row, $item->itemName());
            $sheet->setCellValue('F' . $row, $item->quantity);
            $sheet->setCellValue('G' . $row, $item->requestedBy?->name ?? '-');
            $sheet->setCellValue('H' . $row, optional($item->created_at)->format('Y-m-d H:i'));
        });
    }

    private function buildTransactionQuery(Request $request)
    {
        $query = DepotTransaction::with(['depot', 'fromDepot', 'toDepot', 'pharmacy', 'medicine', 'tool', 'createdBy']);

        if ($request->filled('_allowed_depot_ids')) {
            $allowedIds = collect($request->input('_allowed_depot_ids'))->map(fn ($id) => (int) $id)->all();
            $query->where(function ($scoped) use ($allowedIds) {
                $scoped->whereIn('depot_id', $allowedIds)
                    ->orWhereIn('from_depot_id', $allowedIds)
                    ->orWhereIn('to_depot_id', $allowedIds);
            });
        }

        if ($request->filled('depot_id')) {
            $query->forDepot((int) $request->depot_id);
        }
        if ($request->filled('pharmacy_id')) {
            $query->where('pharmacy_id', $request->pharmacy_id);
        }
        if ($request->filled('medicine_id')) {
            $query->where('medicine_id', $request->medicine_id);
        }
        if ($request->filled('tool_id')) {
            $query->where('tool_id', $request->tool_id);
        }
        if ($request->filled('item_type')) {
            if ($request->item_type === 'medicine') {
                $query->whereNotNull('medicine_id');
            } elseif ($request->item_type === 'tool') {
                $query->whereNotNull('tool_id');
            }
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        return $query->latest('transaction_date')->latest('id');
    }

    private function buildRequestQuery(Request $request)
    {
        $query = DepotRequest::with([
            'requestingDepot',
            'pharmacy',
            'sourceDepot',
            'items.medicine',
            'items.tool',
            'requestedBy',
        ]);

        if ($request->filled('_allowed_depot_ids')) {
            $allowedIds = collect($request->input('_allowed_depot_ids'))->map(fn ($id) => (int) $id)->all();
            $query->where(function ($scoped) use ($allowedIds) {
                $scoped->whereIn('requesting_depot_id', $allowedIds)
                    ->orWhereIn('source_depot_id', $allowedIds);
            });
        }

        if ($request->filled('requesting_depot_id')) {
            $query->where('requesting_depot_id', $request->requesting_depot_id);
        }
        if ($request->filled('pharmacy_id')) {
            $query->where('pharmacy_id', $request->pharmacy_id);
        }
        if ($request->filled('source_depot_id')) {
            $query->where('source_depot_id', $request->source_depot_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('medicine_id')) {
            $query->whereHas('items', fn ($q) => $q->where('medicine_id', $request->medicine_id));
        }
        if ($request->filled('tool_id')) {
            $query->whereHas('items', fn ($q) => $q->where('tool_id', $request->tool_id));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->latest('id');
    }

    private function streamExcel(string $filename, array $headers, $items, callable $rowWriter): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $row = 2;
        foreach ($items as $item) {
            $rowWriter($item, $row, $sheet);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
