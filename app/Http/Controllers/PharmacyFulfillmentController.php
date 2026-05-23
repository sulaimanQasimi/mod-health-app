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
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->buildIndexQuery($request);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $fulfillments = $query->paginate($perPage);

        // Get all pharmacies for admin filter
        $user = Auth::user();
        $pharmacies = null;
        if ($user->hasRole('admin')) {
            $pharmacies = Pharmacy::orderBy('name')->get();
        }

        $userPharmacies = $user->activePharmacies;

        return view('pages.pharmacy_fulfillments.index', compact('fulfillments', 'pharmacies', 'userPharmacies'));
    }

    /**
     * Build the base query for index/export with all filters applied.
     */
    private function buildIndexQuery(Request $request)
    {
        $query = PharmacyFulfillment::with(['medicine', 'pharmacy', 'user', 'createdBy']);

        // Get current user's pharmacies
        $user = Auth::user();
        $userPharmacies = $user->activePharmacies;

        // Filter by user's pharmacies if user has any
        if ($userPharmacies->isNotEmpty()) {
            $query->whereIn('pharmacy_id', $userPharmacies->pluck('id'));
        }

        // Global search (medicine name, form no, unit type)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('medicine', function ($medicineQuery) use ($search) {
                    $medicineQuery->where('name', 'like', "%{$search}%");
                })
                    ->orWhere('form_no', 'like', "%{$search}%")
                    ->orWhere('unit_type', 'like', "%{$search}%");
            });
        }

        // Filter by medicine (exact)
        if ($request->filled('medicine_id')) {
            $query->where('medicine_id', $request->medicine_id);
        }

        // Filter by unit type
        if ($request->filled('unit_type')) {
            $query->where('unit_type', 'like', '%' . $request->unit_type . '%');
        }

        // Filter by form number
        if ($request->filled('form_no')) {
            $query->where('form_no', 'like', '%' . $request->form_no . '%');
        }

        // Filter by amount range
        if ($request->filled('amount_from')) {
            $query->where('amount', '>=', $request->amount_from);
        }
        if ($request->filled('amount_to')) {
            $query->where('amount', '<=', $request->amount_to);
        }

        // Filter by user (who fulfilled)
        if ($request->filled('user_name')) {
            $name = $request->user_name;
            $query->whereHas('user', function ($q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%');
            });
        }

        // Filter by created_by user name
        if ($request->filled('created_by_name')) {
            $name = $request->created_by_name;
            $query->whereHas('createdBy', function ($q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%');
            });
        }

        // Filter by pharmacy (for admin users who can see all pharmacies)
        if ($request->filled('pharmacy_id') && $user->hasRole('admin')) {
            $query->where('pharmacy_id', $request->pharmacy_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $fromDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->date_from)->datetime();
            $query->whereDate('date', '>=', $fromDate);
        }
        if ($request->filled('date_to')) {
            $toDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->date_to)->datetime();
            $query->whereDate('date', '<=', $toDate);
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

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
        $allowedPharmacyIds = $userPharmacies->isEmpty() ? [] : $userPharmacies->pluck('id')->toArray();
        $stockStats = [
            'total_items' => 0,
            'total_stock' => 0,
            'total_income' => 0,
            'total_outcome' => 0,
            'total_low_stock' => 0,
            'total_out_of_stock' => 0,
        ];

        // Admin can see all pharmacies by default and can narrow down by pharmacy filter.
        if ($user->hasRole('admin')) {
            if ($request->filled('pharmacy_id')) {
                $allowedPharmacyIds = [(int) $request->pharmacy_id];
            } else {
                $allowedPharmacyIds = Pharmacy::query()->pluck('id')->toArray();
            }
        }
        if (empty($allowedPharmacyIds)) {
            $stockItems = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $pharmacies = $user->hasRole('admin') ? Pharmacy::orderBy('name')->get() : collect();
            $medicines = Medicine::query()->whereNull('deleted_at')->orderBy('name')->get(['id', 'name']);
            return view('pages.pharmacy_fulfillments.stock', compact('stockItems', 'pharmacies', 'userPharmacies', 'stockStats', 'medicines'));
        }

        // Outcome = usage count from prescription_items (same logic as outcomes index), per (pharmacy, medicine)
        $usageSql = "(SELECT p.pharmacy_id, COALESCE(pai.medicine_id, pi.medicine_id) as medicine_id, COUNT(*) as total
            FROM prescription_items pi
            LEFT JOIN prescription_alternative_items pai ON pai.prescription_item_id = pi.id AND pai.prescription_id = pi.prescription_id AND pai.is_selected = 1 AND pai.deleted_at IS NULL
            JOIN prescriptions p ON pi.prescription_id = p.id
            WHERE pi.deleted_at IS NULL AND p.deleted_at IS NULL AND p.pharmacy_id IS NOT NULL
            GROUP BY p.pharmacy_id, COALESCE(pai.medicine_id, pi.medicine_id))";

        $unionSql = "((SELECT pharmacy_id, medicine_id FROM pharmacy_fulfillments WHERE deleted_at IS NULL)
            UNION
            (SELECT pharmacy_id, medicine_id FROM outcomes WHERE deleted_at IS NULL AND pharmacy_id IS NOT NULL)
            UNION
            (SELECT p.pharmacy_id as pharmacy_id, COALESCE(pai.medicine_id, pi.medicine_id) as medicine_id
             FROM prescription_items pi
             LEFT JOIN prescription_alternative_items pai ON pai.prescription_item_id = pi.id AND pai.prescription_id = pi.prescription_id AND pai.is_selected = 1 AND pai.deleted_at IS NULL
             JOIN prescriptions p ON pi.prescription_id = p.id
             WHERE pi.deleted_at IS NULL AND p.deleted_at IS NULL AND p.pharmacy_id IS NOT NULL)) as u";
        $ftSql = '(SELECT pharmacy_id, medicine_id, SUM(CAST(amount AS UNSIGNED)) as total FROM pharmacy_fulfillments WHERE deleted_at IS NULL GROUP BY pharmacy_id, medicine_id)';
        $stockSql = 'COALESCE(ft.total, 0) - COALESCE(ot.total, 0)';

        $baseQuery = DB::table(DB::raw($unionSql))
            ->join('medicines as m', 'm.id', '=', 'u.medicine_id')
            ->join('pharmacies as p', 'p.id', '=', 'u.pharmacy_id')
            ->leftJoin(DB::raw("{$ftSql} as ft"), function ($j) {
                $j->on('ft.pharmacy_id', '=', 'u.pharmacy_id')->on('ft.medicine_id', '=', 'u.medicine_id');
            })
            ->leftJoin(DB::raw("{$usageSql} as ot"), function ($j) {
                $j->on('ot.pharmacy_id', '=', 'u.pharmacy_id')->on('ot.medicine_id', '=', 'u.medicine_id');
            })
            ->whereIn('u.pharmacy_id', $allowedPharmacyIds)
            ->whereNull('m.deleted_at')
            ->whereNull('p.deleted_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function ($q) use ($search) {
                $q->where('m.name', 'like', '%' . $search . '%')
                    ->orWhere('p.name', 'like', '%' . $search . '%');
            });
        }
        if ($request->filled('medicine_id')) {
            $baseQuery->where('m.id', (int) $request->medicine_id);
        }

        $stockStatus = $request->get('stock_status');
        if ($stockStatus === 'out_of_stock') {
            $baseQuery->whereRaw("{$stockSql} <= 0");
        } elseif ($stockStatus === 'low_stock') {
            $baseQuery->whereRaw("{$stockSql} > 0 AND {$stockSql} <= 10");
        }

        // Stat card totals: total_income from fulfillments; total_outcome = usage count (same logic as outcomes index)
        $stockStats['total_income'] = (int) DB::table('pharmacy_fulfillments')
            ->whereNull('deleted_at')
            ->whereIn('pharmacy_id', $allowedPharmacyIds)
            ->sum(DB::raw('CAST(amount AS UNSIGNED)'));
        $stockStats['total_outcome'] = (int) DB::table('prescription_items as pi')
            ->leftJoin('prescription_alternative_items as pai', function ($join) {
                $join->on('pai.prescription_item_id', '=', 'pi.id')
                    ->on('pai.prescription_id', '=', 'pi.prescription_id')
                    ->whereRaw('(pai.is_selected = 1 AND pai.deleted_at IS NULL)');
            })
            ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
            ->whereNull('pi.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereIn('p.pharmacy_id', $allowedPharmacyIds)
            ->count();

        $stockStatsResult = (clone $baseQuery)->selectRaw("
            COUNT(*) as total_items,
            COALESCE(SUM({$stockSql}), 0) as total_stock,
            SUM(CASE WHEN {$stockSql} > 0 AND {$stockSql} <= 10 THEN 1 ELSE 0 END) as total_low_stock,
            SUM(CASE WHEN {$stockSql} <= 0 THEN 1 ELSE 0 END) as total_out_of_stock
        ")->first();

        if ($stockStatsResult) {
            $stockStats['total_items'] = (int) $stockStatsResult->total_items;
            $stockStats['total_stock'] = (int) $stockStatsResult->total_stock;
            $stockStats['total_low_stock'] = (int) $stockStatsResult->total_low_stock;
            $stockStats['total_out_of_stock'] = (int) $stockStatsResult->total_out_of_stock;
        }

        $query = (clone $baseQuery)->select(
            'm.id as medicine_id',
            'm.name as medicine_name',
            'p.id as pharmacy_id',
            'p.name as pharmacy_name',
            DB::raw('COALESCE(ft.total, 0) as income'),
            DB::raw('COALESCE(ot.total, 0) as outcome'),
            DB::raw("{$stockSql} as stock")
        );

        $sortBy = $request->get('sort_by', 'medicine');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'medicine' => 'm.name',
            'pharmacy' => 'p.name',
            'income' => 'income',
            'outcome' => 'outcome',
            'stock' => 'stock',
        ];
        $sortColumn = $sortableColumns[$sortBy] ?? 'm.name';
        $query->orderBy($sortColumn, $sortOrder)->orderBy('m.name')->orderBy('p.name');

        $perPage = (int) $request->get('per_page', 15);
        $stockItems = $query->paginate($perPage > 0 ? $perPage : 15, ['*'], 'page', $request->get('page', 1));

        $pharmacies = $user->hasRole('admin') ? Pharmacy::orderBy('name')->get() : null;
        $medicines = Medicine::query()->whereNull('deleted_at')->orderBy('name')->get(['id', 'name']);

        return view('pages.pharmacy_fulfillments.stock', compact('stockItems', 'pharmacies', 'userPharmacies', 'stockStats', 'medicines'));
    }

    /**
     * Export pharmacy fulfillments list to Excel or PDF using current filters.
     */
    public function exportReport(Request $request)
    {
        $items = $this->buildIndexQuery($request)->get();

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
