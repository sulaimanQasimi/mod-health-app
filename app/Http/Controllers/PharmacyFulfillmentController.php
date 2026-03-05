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
            'date' => 'required|date',
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
            'date' => 'required|date',
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

        // Admin can filter by pharmacy; otherwise restrict to user's pharmacies
        if ($user->hasRole('admin') && $request->filled('pharmacy_id')) {
            $allowedPharmacyIds = [(int) $request->pharmacy_id];
        }
        if (empty($allowedPharmacyIds)) {
            $stockItems = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $pharmacies = $user->hasRole('admin') ? Pharmacy::orderBy('name')->get() : collect();
            return view('pages.pharmacy_fulfillments.stock', compact('stockItems', 'pharmacies', 'userPharmacies'));
        }

        $unionSql = '((SELECT pharmacy_id, medicine_id FROM pharmacy_fulfillments WHERE deleted_at IS NULL)
            UNION
            (SELECT pharmacy_id, medicine_id FROM outcomes WHERE deleted_at IS NULL AND pharmacy_id IS NOT NULL)) as u';
        $ftSql = '(SELECT pharmacy_id, medicine_id, SUM(CAST(amount AS UNSIGNED)) as total FROM pharmacy_fulfillments WHERE deleted_at IS NULL GROUP BY pharmacy_id, medicine_id)';
        $otSql = '(SELECT pharmacy_id, medicine_id, SUM(amount) as total FROM outcomes WHERE deleted_at IS NULL GROUP BY pharmacy_id, medicine_id)';

        $query = DB::table(DB::raw($unionSql))
            ->join('medicines as m', 'm.id', '=', 'u.medicine_id')
            ->join('pharmacies as p', 'p.id', '=', 'u.pharmacy_id')
            ->leftJoin(DB::raw("{$ftSql} as ft"), function ($j) {
                $j->on('ft.pharmacy_id', '=', 'u.pharmacy_id')->on('ft.medicine_id', '=', 'u.medicine_id');
            })
            ->leftJoin(DB::raw("{$otSql} as ot"), function ($j) {
                $j->on('ot.pharmacy_id', '=', 'u.pharmacy_id')->on('ot.medicine_id', '=', 'u.medicine_id');
            })
            ->select(
                'm.id as medicine_id',
                'm.name as medicine_name',
                'p.id as pharmacy_id',
                'p.name as pharmacy_name',
                DB::raw('COALESCE(ft.total, 0) as income'),
                DB::raw('COALESCE(ot.total, 0) as outcome'),
                DB::raw('COALESCE(ft.total, 0) - COALESCE(ot.total, 0) as stock')
            )
            ->whereIn('u.pharmacy_id', $allowedPharmacyIds)
            ->whereNull('m.deleted_at')
            ->whereNull('p.deleted_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('m.name', 'like', '%' . $search . '%');
        }

        $query->orderBy('m.name')->orderBy('p.name');

        $perPage = (int) $request->get('per_page', 15);
        $stockItems = $query->paginate($perPage > 0 ? $perPage : 15, ['*'], 'page', $request->get('page', 1));

        $pharmacies = $user->hasRole('admin') ? Pharmacy::orderBy('name')->get() : null;

        return view('pages.pharmacy_fulfillments.stock', compact('stockItems', 'pharmacies', 'userPharmacies'));
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
