<?php

namespace App\Http\Controllers;

use App\Models\PharmacyFulfillment;
use App\Models\Medicine;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Mpdf\Mpdf;

class PharmacyFulfillmentController extends Controller
{
    /**
     * Default fulfillments list to the current Jalali month unless dates are set / cleared.
     *
     * @return array{0: ?string, 1: ?string, 2: bool}
     */
    private function resolveIndexDateFilters(Request $request): array
    {
        if ($request->boolean('all_dates')) {
            return [null, null, false];
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            return [
                $request->input('date_from'),
                $request->input('date_to'),
                false,
            ];
        }

        $start = \Hekmatinasser\Verta\Facades\Verta::now()->startMonth();
        $end = \Hekmatinasser\Verta\Facades\Verta::now()->endMonth();

        return [
            $start->format('Y/m/d'),
            $end->format('Y/m/d'),
            true,
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        [$dateFrom, $dateTo, $usingDefaultDates] = $this->resolveIndexDateFilters($request);

        $query = $this->buildIndexQuery($request, $dateFrom, $dateTo);

        $perPage = min(50, max(10, (int) $request->get('per_page', 15)));
        // simplePaginate avoids a second full COUNT(*) query that often 504s on large servers.
        $fulfillments = $query->simplePaginate($perPage)->appends(array_merge(
            $request->query(),
            [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]
        ));

        $user = Auth::user();
        $userPharmacies = $user->activePharmacies;
        $pharmacies = $user->hasRole(['admin', 'super_admin'])
            ? Pharmacy::orderBy('name')->get(['id', 'name'])
            : null;

        return view('pages.pharmacy_fulfillments.index', compact(
            'fulfillments',
            'pharmacies',
            'userPharmacies',
            'dateFrom',
            'dateTo',
            'usingDefaultDates'
        ));
    }

    /**
     * Build the base query for index/export with all filters applied.
     */
    private function buildIndexQuery(Request $request, ?string $dateFrom = null, ?string $dateTo = null)
    {
        if ($dateFrom === null && $dateTo === null && !$request->boolean('all_dates')) {
            [$dateFrom, $dateTo] = $this->resolveIndexDateFilters($request);
        }

        $user = Auth::user();
        $userPharmacies = $user->activePharmacies;
        $isAdmin = $user->hasRole(['admin', 'super_admin']);

        // Select only list columns + light eager loads (avoid loading full related models).
        $query = PharmacyFulfillment::query()
            ->select([
                'pharmacy_fulfillments.id',
                'pharmacy_fulfillments.medicine_id',
                'pharmacy_fulfillments.unit_type',
                'pharmacy_fulfillments.amount',
                'pharmacy_fulfillments.form_no',
                'pharmacy_fulfillments.date',
                'pharmacy_fulfillments.form',
                'pharmacy_fulfillments.pharmacy_id',
                'pharmacy_fulfillments.user_id',
                'pharmacy_fulfillments.created_by',
                'pharmacy_fulfillments.created_at',
            ])
            ->with([
                'medicine:id,name',
                'pharmacy:id,name',
                'user:id,name,last_name',
                'createdBy:id,name,last_name',
            ]);

        // Scope by pharmacy access first (cheap indexed filter).
        if ($isAdmin) {
            if ($request->filled('pharmacy_id')) {
                $query->where('pharmacy_fulfillments.pharmacy_id', (int) $request->pharmacy_id);
            }
        } elseif ($userPharmacies->isNotEmpty()) {
            $query->whereIn('pharmacy_fulfillments.pharmacy_id', $userPharmacies->pluck('id'));
        } else {
            // Non-admin with no pharmacy must not see global data.
            $query->whereRaw('1 = 0');
        }

        // Global search — prefer indexed columns; medicine name via EXISTS (cheaper than whereHas).
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pharmacy_fulfillments.form_no', 'like', "%{$search}%")
                    ->orWhere('pharmacy_fulfillments.unit_type', 'like', "%{$search}%")
                    ->orWhereExists(function ($sub) use ($search) {
                        $sub->select(DB::raw(1))
                            ->from('medicines')
                            ->whereColumn('medicines.id', 'pharmacy_fulfillments.medicine_id')
                            ->where('medicines.name', 'like', "%{$search}%")
                            ->whereNull('medicines.deleted_at');
                    });
            });
        }

        if ($request->filled('medicine_id')) {
            $query->where('pharmacy_fulfillments.medicine_id', (int) $request->medicine_id);
        }

        if ($request->filled('unit_type')) {
            $query->where('pharmacy_fulfillments.unit_type', 'like', '%' . $request->unit_type . '%');
        }

        if ($request->filled('form_no')) {
            $query->where('pharmacy_fulfillments.form_no', 'like', '%' . $request->form_no . '%');
        }

        if ($request->filled('amount_from')) {
            $query->where('pharmacy_fulfillments.amount', '>=', $request->amount_from);
        }
        if ($request->filled('amount_to')) {
            $query->where('pharmacy_fulfillments.amount', '<=', $request->amount_to);
        }

        if ($request->filled('user_name')) {
            $name = $request->user_name;
            $query->whereExists(function ($sub) use ($name) {
                $sub->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'pharmacy_fulfillments.user_id')
                    ->where(function ($uq) use ($name) {
                        $uq->where('users.name', 'like', '%' . $name . '%')
                            ->orWhere('users.last_name', 'like', '%' . $name . '%');
                    });
            });
        }

        if ($request->filled('created_by_name')) {
            $name = $request->created_by_name;
            $query->whereExists(function ($sub) use ($name) {
                $sub->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'pharmacy_fulfillments.created_by')
                    ->where(function ($uq) use ($name) {
                        $uq->where('users.name', 'like', '%' . $name . '%')
                            ->orWhere('users.last_name', 'like', '%' . $name . '%');
                    });
            });
        }

        // Date range (range compare keeps indexes usable; whereDate() does not).
        if (!empty($dateFrom)) {
            $fromDate = \Hekmatinasser\Verta\Facades\Verta::parse($dateFrom)->datetime()->format('Y-m-d');
            $query->where('pharmacy_fulfillments.date', '>=', $fromDate);
        }
        if (!empty($dateTo)) {
            $toDate = \Hekmatinasser\Verta\Facades\Verta::parse($dateTo)->datetime()->format('Y-m-d');
            $query->where('pharmacy_fulfillments.date', '<=', $toDate);
        }

        $sortBy = safe_sort_column(
            $request->get('sort_by'),
            ['id', 'date', 'amount', 'form_no', 'created_at', 'updated_at'],
            'created_at'
        );
        $sortOrder = safe_sort_order($request->get('sort_order'), 'desc');
        $query->orderBy('pharmacy_fulfillments.' . $sortBy, $sortOrder);

        return $query;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $userPharmacy = $user->activePharmacies()->first();

        // Check if user has a pharmacy assigned
        if (!$userPharmacy) {
            return redirect()->route('pharmacy_fulfillments.index')
                ->with('warning', 'You are not assigned to any pharmacy. Please contact your administrator.');
        }

        $medicines = Medicine::orderBy('name')->get();

        return view('pages.pharmacy_fulfillments.create', compact('medicines', 'userPharmacy'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $userPharmacy = $user->activePharmacies()->first();

        // Check if user has a pharmacy assigned
        if (!$userPharmacy) {
            return redirect()->route('pharmacy_fulfillments.index')
                ->with('warning', 'You are not assigned to any pharmacy. Please contact your administrator.');
        }

        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'unit_type' => 'nullable|string|max:255',
            'amount' => 'required|string|max:191',
            'form_no' => 'required|string|max:191',
            'date' => 'required|string',
            'form' => 'nullable|file|mimes:pdf,PDF|max:10240',
        ]);
        $data = $request->all();
        $data['pharmacy_id'] = $userPharmacy->id;
        $data['user_id'] = $user->id;

        // Handle file upload
        if ($request->hasFile('form')) {
            $file = $request->file('form');
            $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('pharmacy_fulfillments', $filename, 'public');
            $data['form'] = $path;
        }
        if ($data['date']) {
            $data['date'] = \Hekmatinasser\Verta\Facades\Verta::parse($data['date'])->datetime();
        }

        PharmacyFulfillment::create($data);

        return redirect()->route('pharmacy_fulfillments.index')
            ->with('success', localize('global.pharmacy_fulfillment_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(PharmacyFulfillment $pharmacyFulfillment)
    {
        // Check if user has access to this fulfillment's pharmacy
        $user = Auth::user();
        $userPharmacies = $user->activePharmacies->pluck('id');

        if (!$user->hasRole('admin') && !$userPharmacies->contains($pharmacyFulfillment->pharmacy_id)) {
            abort(403, 'Unauthorized access');
        }

        $pharmacyFulfillment->load(['medicine', 'pharmacy', 'user', 'createdBy', 'updatedBy']);

        return view('pages.pharmacy_fulfillments.show', compact('pharmacyFulfillment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PharmacyFulfillment $pharmacyFulfillment)
    {
        // Check if user has access to this fulfillment's pharmacy
        $user = Auth::user();
        $userPharmacies = $user->activePharmacies->pluck('id');

        if (!$user->hasRole('admin') && !$userPharmacies->contains($pharmacyFulfillment->pharmacy_id)) {
            abort(403, 'Unauthorized access');
        }

        $medicines = Medicine::orderBy('name')->get();
        $userPharmacy = $pharmacyFulfillment->pharmacy;

        return view('pages.pharmacy_fulfillments.edit', compact('pharmacyFulfillment', 'medicines', 'userPharmacy'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PharmacyFulfillment $pharmacyFulfillment)
    {
        // Check if user has access to this fulfillment's pharmacy
        $user = Auth::user();
        $userPharmacies = $user->activePharmacies->pluck('id');

        if (!$user->hasRole('admin') && !$userPharmacies->contains($pharmacyFulfillment->pharmacy_id)) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'unit_type' => 'nullable|string|max:255',
            'amount' => 'required|string|max:191',
            'form_no' => 'required|string|max:191',
            'date' => 'required|string',
            'form' => 'nullable|file|mimes:pdf,PDF|max:10240',
        ]);

        $data = $request->except(['form']);
        if ($data['date']) {
            $data['date'] = \Hekmatinasser\Verta\Facades\Verta::parse($data['date'])->datetime();
        }
        // Handle file upload
        if ($request->hasFile('form')) {
            // Delete old file if exists
            if ($pharmacyFulfillment->form && Storage::disk('public')->exists($pharmacyFulfillment->form)) {
                Storage::disk('public')->delete($pharmacyFulfillment->form);
            }

            $file = $request->file('form');
            $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('pharmacy_fulfillments', $filename, 'public');
            $data['form'] = $path;
        }

        $pharmacyFulfillment->update($data);

        return redirect()->route('pharmacy_fulfillments.index')
            ->with('success', localize('global.pharmacy_fulfillment_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PharmacyFulfillment $pharmacyFulfillment)
    {
        // Check if user has access to this fulfillment's pharmacy
        $user = Auth::user();
        $userPharmacies = $user->activePharmacies->pluck('id');

        if (!$user->hasRole('admin') && !$userPharmacies->contains($pharmacyFulfillment->pharmacy_id)) {
            abort(403, 'Unauthorized access');
        }

        $pharmacyFulfillment->delete();

        return redirect()->route('pharmacy_fulfillments.index')
            ->with('success', localize('global.pharmacy_fulfillment_deleted_successfully'));
    }

    /**
     * Pharmacy stock dashboard: income (sum of fulfillments) minus outcome (sum of outcomes) per medicine per pharmacy.
     */
    public function stock(Request $request)
    {
        $user = Auth::user();
        $userPharmacies = $user->activePharmacies;
        $isAdmin = $user->hasRole(['admin', 'super_admin']);
        $stockStats = [
            'total_items' => 0,
            'total_stock' => 0,
            'total_income' => 0,
            'total_outcome' => 0,
            'total_low_stock' => 0,
            'total_out_of_stock' => 0,
        ];

        // Always scope to ONE pharmacy on stock — scanning all pharmacies 504s on production.
        $pharmacies = $isAdmin
            ? Pharmacy::orderBy('name')->get(['id', 'name'])
            : $userPharmacies->sortBy('name')->values();

        $selectedPharmacyId = $request->filled('pharmacy_id')
            ? (int) $request->pharmacy_id
            : ($pharmacies->first()->id ?? null);

        if ($selectedPharmacyId && !$isAdmin && !$userPharmacies->pluck('id')->contains($selectedPharmacyId)) {
            abort(403);
        }

        $medicines = Medicine::query()->whereNull('deleted_at')->orderBy('name')->get(['id', 'name']);

        if (!$selectedPharmacyId) {
            $stockItems = new \Illuminate\Pagination\Paginator([], 15);
            return view('pages.pharmacy_fulfillments.stock', compact(
                'stockItems',
                'pharmacies',
                'userPharmacies',
                'stockStats',
                'medicines',
                'selectedPharmacyId'
            ));
        }

        $pharmacyId = (int) $selectedPharmacyId;

        // One CTE pass: income + completed-prescription outcome for a single pharmacy.
        $stockSql = 'COALESCE(ft.total, 0) - COALESCE(ot.total, 0)';
        $cte = "
            WITH ft AS (
                SELECT pharmacy_id, medicine_id, SUM(CAST(amount AS UNSIGNED)) AS total
                FROM pharmacy_fulfillments
                WHERE deleted_at IS NULL AND pharmacy_id = {$pharmacyId}
                GROUP BY pharmacy_id, medicine_id
            ),
            ot AS (
                SELECT p.pharmacy_id,
                       COALESCE(pai.medicine_id, pi.medicine_id) AS medicine_id,
                       SUM(COALESCE(COALESCE(pai.amount, pi.amount), 0)) AS total
                FROM prescription_items pi
                LEFT JOIN prescription_alternative_items pai
                    ON pai.prescription_item_id = pi.id
                    AND pai.prescription_id = pi.prescription_id
                    AND pai.is_selected = 1
                    AND pai.deleted_at IS NULL
                INNER JOIN prescriptions p ON pi.prescription_id = p.id
                WHERE pi.deleted_at IS NULL
                  AND p.deleted_at IS NULL
                  AND p.is_completed = 1
                  AND p.pharmacy_id = {$pharmacyId}
                GROUP BY p.pharmacy_id, COALESCE(pai.medicine_id, pi.medicine_id)
            ),
            pairs AS (
                SELECT pharmacy_id, medicine_id FROM ft
                UNION
                SELECT pharmacy_id, medicine_id FROM ot
            )
            SELECT
                m.id AS medicine_id,
                m.name AS medicine_name,
                ph.id AS pharmacy_id,
                ph.name AS pharmacy_name,
                COALESCE(ft.total, 0) AS income,
                COALESCE(ot.total, 0) AS outcome,
                ({$stockSql}) AS stock
            FROM pairs u
            INNER JOIN medicines m ON m.id = u.medicine_id AND m.deleted_at IS NULL
            INNER JOIN pharmacies ph ON ph.id = u.pharmacy_id AND ph.deleted_at IS NULL
            LEFT JOIN ft ON ft.pharmacy_id = u.pharmacy_id AND ft.medicine_id = u.medicine_id
            LEFT JOIN ot ON ot.pharmacy_id = u.pharmacy_id AND ot.medicine_id = u.medicine_id
        ";

        $baseQuery = DB::table(DB::raw("({$cte}) as stock_rows"));

        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function ($q) use ($search) {
                $q->where('medicine_name', 'like', '%' . $search . '%')
                    ->orWhere('pharmacy_name', 'like', '%' . $search . '%');
            });
        }
        if ($request->filled('medicine_id')) {
            $baseQuery->where('medicine_id', (int) $request->medicine_id);
        }

        $stockStatus = $request->get('stock_status');
        if ($stockStatus === 'out_of_stock') {
            $baseQuery->where('stock', '<=', 0);
        } elseif ($stockStatus === 'low_stock') {
            $baseQuery->where('stock', '>', 0)->where('stock', '<=', 10);
        }

        $stockStatsResult = (clone $baseQuery)->selectRaw("
            COUNT(*) as total_items,
            COALESCE(SUM(income), 0) as total_income,
            COALESCE(SUM(outcome), 0) as total_outcome,
            COALESCE(SUM(stock), 0) as total_stock,
            SUM(CASE WHEN stock > 0 AND stock <= 10 THEN 1 ELSE 0 END) as total_low_stock,
            SUM(CASE WHEN stock <= 0 THEN 1 ELSE 0 END) as total_out_of_stock
        ")->first();

        if ($stockStatsResult) {
            $stockStats['total_items'] = (int) $stockStatsResult->total_items;
            $stockStats['total_income'] = (int) $stockStatsResult->total_income;
            $stockStats['total_outcome'] = (int) $stockStatsResult->total_outcome;
            $stockStats['total_stock'] = (int) $stockStatsResult->total_stock;
            $stockStats['total_low_stock'] = (int) $stockStatsResult->total_low_stock;
            $stockStats['total_out_of_stock'] = (int) $stockStatsResult->total_out_of_stock;
        }

        $sortBy = $request->get('sort_by', 'medicine');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'medicine' => 'medicine_name',
            'pharmacy' => 'pharmacy_name',
            'income' => 'income',
            'outcome' => 'outcome',
            'stock' => 'stock',
        ];
        $sortColumn = $sortableColumns[$sortBy] ?? 'medicine_name';

        $perPage = min(50, max(10, (int) $request->get('per_page', 15)));
        $stockItems = (clone $baseQuery)
            ->orderBy($sortColumn, $sortOrder)
            ->orderBy('medicine_name')
            ->simplePaginate($perPage)
            ->appends(array_merge($request->query(), ['pharmacy_id' => $selectedPharmacyId]));

        return view('pages.pharmacy_fulfillments.stock', compact(
            'stockItems',
            'pharmacies',
            'userPharmacies',
            'stockStats',
            'medicines',
            'selectedPharmacyId'
        ));
    }

    /**
     * Export pharmacy fulfillments list to Excel or PDF using current filters.
     */
    public function exportReport(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveIndexDateFilters($request);
        // Cap export size to avoid gateway timeouts on production.
        $items = $this->buildIndexQuery($request, $dateFrom, $dateTo)->limit(2000)->get();

        if ($request->get('type') === 'pdf') {
            $html = view('pages.pharmacy_fulfillments.report_pdf', compact('items'))->render();

            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('pharmacy_fulfillments_report.pdf', 'D');
            return null;
        }

        // Excel export
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header row
        $headers = [
            localize('global.number'),
            localize('global.medicine'),
            localize('global.unit_type'),
            localize('global.amount'),
            localize('global.form_no'),
            localize('global.date'),
            localize('global.pharmacy'),
            localize('global.user'),
            localize('global.created_by'),
            localize('global.created_at'),
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Data rows
        $row = 2;
        foreach ($items as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->medicine->name ?? '-');
            $sheet->setCellValue('C' . $row, $item->unit_type ?? '-');
            $sheet->setCellValue('D' . $row, $item->amount);
            $sheet->setCellValue('E' . $row, $item->form_no);

            $dateStr = '';
            if ($item->date) {
                $dateStr = \Hekmatinasser\Verta\Facades\Verta::instance($item->date)->format('Y/m/d');
            }
            $sheet->setCellValue('F' . $row, $dateStr);

            $sheet->setCellValue('G' . $row, $item->pharmacy->name ?? '-');
            $sheet->setCellValue('H' . $row, $item->user->name ?? '-');
            $sheet->setCellValue('I' . $row, $item->createdBy->name ?? '-');

            $createdAtStr = '';
            if ($item->created_at) {
                $createdAtStr = \Hekmatinasser\Verta\Facades\Verta::instance($item->created_at)->format('Y/m/d H:i');
            }
            $sheet->setCellValue('J' . $row, $createdAtStr);

            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="pharmacy_fulfillments_report.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
