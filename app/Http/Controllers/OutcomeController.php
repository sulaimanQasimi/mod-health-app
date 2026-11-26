<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Mpdf\Mpdf;

class OutcomeController extends Controller
{
    /**
     * Resolve date filters for the outcomes index.
     * Defaults to the current Jalali month when no dates are provided,
     * which keeps the heavy prescription aggregation bounded.
     *
     * @return array{0: ?string, 1: ?string, 2: bool} [date_from, date_to, using_defaults]
     */
    private function resolveIndexDateFilters(Request $request): array
    {
        $hasExplicitDates = $request->filled('date_from') || $request->filled('date_to');
        $clearDates = $request->boolean('all_dates');

        if ($clearDates) {
            return [null, null, false];
        }

        if ($hasExplicitDates) {
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
     * Build the index query (usage per medicine/pharmacy from completed prescriptions) for index and export.
     * Intentionally avoids GROUP_CONCAT over users — that was scanning every completed
     * prescription item and made /outcomes extremely slow.
     */
    private function buildIndexQuery(Request $request, ?string $dateFrom = null, ?string $dateTo = null)
    {
        $user = Auth::user();
        $userPharmacies = $user->activePharmacies;

        $usageSubquery = DB::table('prescription_items as pi')
            ->leftJoin('prescription_alternative_items as pai', function ($join) {
                $join->on('pai.prescription_item_id', '=', 'pi.id')
                    ->on('pai.prescription_id', '=', 'pi.prescription_id')
                    ->whereNull('pai.deleted_at')
                    ->where('pai.is_selected', 1);
            })
            ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
            ->leftJoin('pharmacies as ph', 'p.pharmacy_id', '=', 'ph.id')
            ->whereNull('pi.deleted_at')
            ->whereNull('p.deleted_at')
            ->where('p.is_completed', 1)
            ->whereNotNull('p.pharmacy_id');

        // If user has pharmacies, restrict to those; admins / users without pharmacy see all
        if ($userPharmacies->isNotEmpty() && !$user->hasRole(['admin', 'super_admin'])) {
            $usageSubquery->whereIn('p.pharmacy_id', $userPharmacies->pluck('id'));
        }

        if ($request->filled('pharmacy_id')) {
            $pharmacyId = (int) $request->pharmacy_id;
            $canFilterPharmacy = $user->hasRole(['admin', 'super_admin'])
                || $userPharmacies->isEmpty()
                || $userPharmacies->pluck('id')->contains($pharmacyId);

            if ($canFilterPharmacy) {
                $usageSubquery->where('p.pharmacy_id', $pharmacyId);
            }
        }

        if ($request->filled('pharmacy_user_id')) {
            $usageSubquery->where('p.updated_by', (int) $request->pharmacy_user_id);
        }

        if (!empty($dateFrom)) {
            $fromDate = \Hekmatinasser\Verta\Facades\Verta::parse($dateFrom)->datetime();
            $usageSubquery->where('p.created_at', '>=', $fromDate->format('Y-m-d 00:00:00'));
        }
        if (!empty($dateTo)) {
            $toDate = \Hekmatinasser\Verta\Facades\Verta::parse($dateTo)->datetime();
            $usageSubquery->where('p.created_at', '<=', $toDate->format('Y-m-d 23:59:59'));
        }

        $usageSubquery->select(
            DB::raw('COALESCE(pai.medicine_id, pi.medicine_id) as medicine_id'),
            'p.pharmacy_id',
            DB::raw('MAX(ph.name) as pharmacy_name'),
            DB::raw('COUNT(*) as usage_count'),
            DB::raw('MAX(p.updated_at) as prescription_updated_at')
        )->groupBy(DB::raw('COALESCE(pai.medicine_id, pi.medicine_id)'), 'p.pharmacy_id');

        // Join usage (per medicine, pharmacy) to medicines
        $usageSubqueryClone = clone $usageSubquery;
        $query = DB::table(DB::raw('(' . $usageSubqueryClone->toSql() . ') as u'))
            ->mergeBindings($usageSubqueryClone)
            ->join('medicines as m', 'u.medicine_id', '=', 'm.id')
            ->whereNull('m.deleted_at')
            ->select(
                'm.id',
                'm.name',
                'u.pharmacy_id',
                'u.pharmacy_name',
                'u.usage_count',
                'u.prescription_updated_at'
            );

        if ($request->filled('medicine_id')) {
            $query->where('m.id', (int) $request->medicine_id);
        } elseif ($request->filled('search')) {
            // Legacy free-text search fallback
            $query->where('m.name', 'like', '%' . $request->search . '%');
        }

        $sortBy = $request->get('sort_by', 'usage_count');
        $sortOrder = safe_sort_order($request->get('sort_order'), 'desc');
        $validSortColumns = [
            'id' => 'm.id',
            'name' => 'm.name',
            'pharmacy_name' => 'u.pharmacy_name',
            'usage_count' => 'u.usage_count',
            'prescription_updated_at' => 'u.prescription_updated_at',
        ];
        // updated_by_name is hydrated after pagination; fall back to usage_count when sorting by it
        $sortColumn = $validSortColumns[$sortBy] ?? 'u.usage_count';
        $query->orderBy($sortColumn, $sortOrder);

        return $query;
    }

    /**
     * Hydrate updated_by_name only for the current result set (page/export rows),
     * instead of computing it across every completed prescription in SQL.
     *
     * @param  \Illuminate\Support\Collection<int, object>  $rows
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function attachUpdatedByNames($rows)
    {
        $collection = collect($rows);
        if ($collection->isEmpty()) {
            return $collection;
        }

        $pharmacyIds = $collection->pluck('pharmacy_id')->filter()->unique()->values();
        $medicineIds = $collection->pluck('id')->filter()->unique()->values();
        $updatedAts = $collection->pluck('prescription_updated_at')->filter()->unique()->values();

        if ($pharmacyIds->isEmpty() || $medicineIds->isEmpty() || $updatedAts->isEmpty()) {
            return $collection->map(function ($row) {
                $row->updated_by_name = $row->updated_by_name ?? null;
                return $row;
            });
        }

        $latestUpdaters = DB::table('prescription_items as pi')
            ->leftJoin('prescription_alternative_items as pai', function ($join) {
                $join->on('pai.prescription_item_id', '=', 'pi.id')
                    ->on('pai.prescription_id', '=', 'pi.prescription_id')
                    ->whereNull('pai.deleted_at')
                    ->where('pai.is_selected', 1);
            })
            ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
            ->leftJoin('users as updater', 'p.updated_by', '=', 'updater.id')
            ->whereNull('pi.deleted_at')
            ->whereNull('p.deleted_at')
            ->where('p.is_completed', 1)
            ->whereIn('p.pharmacy_id', $pharmacyIds)
            ->where(function ($q) use ($medicineIds) {
                $q->whereIn('pi.medicine_id', $medicineIds)
                    ->orWhereIn('pai.medicine_id', $medicineIds);
            })
            ->whereIn('p.updated_at', $updatedAts)
            ->select(
                DB::raw('COALESCE(pai.medicine_id, pi.medicine_id) as medicine_id'),
                'p.pharmacy_id',
                'p.updated_at',
                DB::raw("TRIM(CONCAT(COALESCE(updater.name, ''), ' ', COALESCE(updater.last_name, ''))) as updated_by_name")
            )
            ->get()
            ->groupBy(fn ($row) => $row->pharmacy_id . ':' . $row->medicine_id . ':' . $row->updated_at);

        return $collection->map(function ($row) use ($latestUpdaters) {
            $key = $row->pharmacy_id . ':' . $row->id . ':' . $row->prescription_updated_at;
            $match = $latestUpdaters->get($key)?->first();
            $row->updated_by_name = $match ? trim((string) $match->updated_by_name) : null;
            return $row;
        });
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $userPharmacies = $user->activePharmacies;

        [$dateFrom, $dateTo, $usingDefaultDates] = $this->resolveIndexDateFilters($request);

        $query = $this->buildIndexQuery($request, $dateFrom, $dateTo);
        $perPage = min(100, max(10, (int) $request->get('per_page', 15)));
        $outcomes = $query->paginate($perPage)->appends(array_merge(
            $request->query(),
            [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]
        ));

        $hydrated = $this->attachUpdatedByNames($outcomes->getCollection());
        $outcomes->setCollection($hydrated);

        $canSeeAllPharmacies = $user->hasRole(['admin', 'super_admin']) || $userPharmacies->isEmpty();
        if ($canSeeAllPharmacies) {
            $pharmacies = Pharmacy::orderBy('name')->get(['id', 'name']);
        } elseif ($userPharmacies->count() > 1) {
            $pharmacies = $userPharmacies->sortBy('name')->values();
        } else {
            $pharmacies = null;
        }

        // Single-pharmacy users: auto-scope user filter to their pharmacy
        $selectedPharmacyId = $request->filled('pharmacy_id')
            ? (int) $request->pharmacy_id
            : ($userPharmacies->count() === 1 ? (int) $userPharmacies->first()->id : null);

        $pharmacyUsers = collect();
        if ($selectedPharmacyId) {
            $pharmacyUsers = $this->getActivePharmacyUsers($selectedPharmacyId);
        }

        // Only load the currently selected medicine (full list is fetched via AJAX Select2)
        $selectedMedicine = null;
        if ($request->filled('medicine_id')) {
            $selectedMedicine = Medicine::query()
                ->whereNull('deleted_at')
                ->where('id', (int) $request->medicine_id)
                ->first(['id', 'name']);
        }

        return view('pages.outcomes.index', compact(
            'outcomes',
            'pharmacies',
            'selectedMedicine',
            'pharmacyUsers',
            'selectedPharmacyId',
            'userPharmacies',
            'dateFrom',
            'dateTo',
            'usingDefaultDates'
        ));
    }

    /**
     * Lightweight medicine search for Select2 (keeps /outcomes page light).
     */
    public function searchMedicines(Request $request)
    {
        $term = trim((string) $request->get('q', $request->get('term', '')));

        $query = Medicine::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->select(['id', 'name']);

        if ($term !== '') {
            $query->where('name', 'like', '%' . $term . '%');
        }

        $medicines = $query->limit(30)->get()->map(fn ($medicine) => [
            'id' => $medicine->id,
            'text' => $medicine->name,
        ]);

        return response()->json(['results' => $medicines]);
    }

    /**
     * JSON list of active users for a pharmacy (used by outcomes filters).
     */
    public function pharmacyUsers(Pharmacy $pharmacy)
    {
        $user = Auth::user();
        $userPharmacies = $user->activePharmacies;

        $allowed = $user->hasRole(['admin', 'super_admin'])
            || $userPharmacies->isEmpty()
            || $userPharmacies->pluck('id')->contains($pharmacy->id);

        if (!$allowed) {
            abort(403);
        }

        return response()->json($this->getActivePharmacyUsers($pharmacy->id));
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{id:int,name:string}>
     */
    private function getActivePharmacyUsers(int $pharmacyId)
    {
        $pharmacy = Pharmacy::find($pharmacyId);
        if (!$pharmacy) {
            return collect();
        }

        return $pharmacy->activeUsers()
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.last_name'])
            ->map(function ($pharmacyUser) {
                return [
                    'id' => $pharmacyUser->id,
                    'name' => trim($pharmacyUser->name . ' ' . ($pharmacyUser->last_name ?? '')),
                ];
            })
            ->values();
    }

    /**
     * Export outcomes index (medicine usage statistics) to PDF or Excel.
     */
    public function exportIndexReport(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveIndexDateFilters($request);
        $query = $this->buildIndexQuery($request, $dateFrom, $dateTo);
        $items = $this->attachUpdatedByNames($query->get());

        if ($request->get('type') === 'pdf') {
            $html = view('pages.outcomes.index_pdf', compact('items'))->render();
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('outcomes_usage_report.pdf', 'D');
            return null;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = [
            localize('global.number'),
            localize('global.medicine'),
            localize('global.pharmacy'),
            localize('global.usage_count'),
            localize('global.updated_by'),
            localize('global.prescription_completed_date'),
        ];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }
        $row = 2;
        foreach ($items as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->name ?? '-');
            $sheet->setCellValue('C' . $row, $item->pharmacy_name ?? '-');
            $sheet->setCellValue('D' . $row, (int) ($item->usage_count ?? 0));
            $sheet->setCellValue('E' . $row, $item->updated_by_name ? trim($item->updated_by_name) : '-');
            $dateStr = '';
            if (!empty($item->prescription_updated_at)) {
                $dateStr = \Hekmatinasser\Verta\Facades\Verta::instance($item->prescription_updated_at)->format('Y/m/d H:i');
            }
            $sheet->setCellValue('F' . $row, $dateStr);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="outcomes_usage_report.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    public function report(Request $request)
    {
        // Dedicated outcome records report UI (not the usage-statistics index)
        return view('pages.outcomes.reports.index');
    }

    public function reportSearch(Request $request)
    {
        $query = DB::table('outcomes as o')
            ->leftJoin('medicines as m', 'o.medicine_id', '=', 'm.id')
            ->leftJoin('patients as p', 'o.patient_id', '=', 'p.id')
            ->leftJoin('users as d', 'o.doctor_id', '=', 'd.id')
            ->leftJoin('users as c', 'o.created_by', '=', 'c.id')
            ->leftJoin('pharmacies as ph', 'o.pharmacy_id', '=', 'ph.id')
            ->whereNull('o.deleted_at')
            ->select(
                'o.id',
                'o.amount',
                'o.outcome_type',
                'o.batch_number',
                'o.reason',
                'o.outcome_date',
                'o.notes',
                'm.name as medicine_name',
                'p.name as patient_name',
                'd.name as doctor_name',
                'c.name as created_by_name',
                'ph.name as pharmacy_name'
            );

        $user = Auth::user();
        $userPharmacies = $user->activePharmacies;

        if ($userPharmacies->isNotEmpty() && !$user->hasRole(['admin', 'super_admin'])) {
            $query->whereIn('o.pharmacy_id', $userPharmacies->pluck('id'));
        }

        if ($request->filled('medicine_name')) {
            $query->where('m.name', 'like', '%' . $request->medicine_name . '%');
        }

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('outcome_type')) {
            $query->where('o.outcome_type', $request->outcome_type);
        }

        if ($request->filled('pharmacy_id')) {
            $pharmacyId = (int) $request->pharmacy_id;
            $canFilterPharmacy = $user->hasRole(['admin', 'super_admin'])
                || $userPharmacies->isEmpty()
                || $userPharmacies->pluck('id')->contains($pharmacyId);
            if ($canFilterPharmacy) {
                $query->where('o.pharmacy_id', $pharmacyId);
            }
        }

        // Bound the search: require/default a date window so we never dump the whole table
        $from = $request->input('from');
        $to = $request->input('to');
        if (!$request->filled('from') || !$request->filled('to')) {
            $start = \Hekmatinasser\Verta\Facades\Verta::now()->startMonth();
            $end = \Hekmatinasser\Verta\Facades\Verta::now()->endMonth();
            $from = $from ?: $start->format('Y/m/d');
            $to = $to ?: $end->format('Y/m/d');
        }

        $fromDate = \Hekmatinasser\Verta\Facades\Verta::parse($from)->datetime();
        $toDate = \Hekmatinasser\Verta\Facades\Verta::parse($to)->datetime();
        $query->where('o.outcome_date', '>=', $fromDate->format('Y-m-d 00:00:00'))
            ->where('o.outcome_date', '<=', $toDate->format('Y-m-d 23:59:59'));

        $limit = 500;
        $items = $query->orderBy('o.outcome_date', 'desc')->limit($limit + 1)->get();
        $truncated = $items->count() > $limit;
        if ($truncated) {
            $items = $items->take($limit);
        }

        return view('pages.outcomes.reports.report', [
            'items' => $items,
            'truncated' => $truncated,
            'limit' => $limit,
        ]);
    }

    /**
     * Export the report to a response
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportReport(Request $request)
    {
        $data = json_decode($request->data, true);

        $items = DB::table('outcomes as o')
            ->leftJoin('medicines as m', 'o.medicine_id', '=', 'm.id')
            ->leftJoin('patients as p', 'o.patient_id', '=', 'p.id')
            ->leftJoin('users as d', 'o.doctor_id', '=', 'd.id')
            ->leftJoin('users as c', 'o.created_by', '=', 'c.id')
            ->leftJoin('pharmacies as ph', 'o.pharmacy_id', '=', 'ph.id')
            ->select(
                'o.id',
                'o.amount',
                'o.outcome_type',
                'o.batch_number',
                'o.reason',
                'o.outcome_date',
                'o.notes',
                'm.name as medicine_name',
                'p.name as patient_name',
                'd.name as doctor_name',
                'c.name as created_by_name',
                'ph.name as pharmacy_name'
            )
            ->whereIn('o.id', $data)
            ->orderBy('o.outcome_date', 'desc')
            ->get();

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $html = view('pages.outcomes.reports.pdf_report', ['items' => $items])->render();
        
        if ($request->type == 'pdf') {
            $mpdf = new \Mpdf\Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('outcome_report.pdf', 'D');
        } else {
            $spreadsheet = $reader->load("report_templates/outcome_report.xlsx");
            $sheet = $spreadsheet->getActiveSheet();
            $row = 3;

            foreach ($items as $index => $item) {
                $sheet->getStyle('A2:G' . $sheet->getHighestRow())->getAlignment()->setWrapText(true);
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(25);
                $sheet->getColumnDimension('H')->setWidth(20);

                $styleArray = array(
                    'font' => array(
                        'name' => 'B Nazanin',
                        'color' => 15,
                        'bold' => true
                    ),
                );

                $outcomeType = '';
                switch ($item->outcome_type) {
                    case 'prescription':
                        $outcomeType = 'نسخه';
                        break;
                    case 'expired':
                        $outcomeType = 'منقضی شده';
                        break;
                    case 'damaged':
                        $outcomeType = 'آسیب دیده';
                        break;
                    case 'lost':
                        $outcomeType = 'گم شده';
                        break;
                    case 'return':
                        $outcomeType = 'بازگشت';
                        break;
                    default:
                        $outcomeType = $item->outcome_type;
                }

                $sheet->setCellValue('A' . $row, ++$index);
                $sheet->setCellValue('B' . $row, $item->medicine_name);
                $sheet->setCellValue('C' . $row, $item->patient_name);
                $sheet->setCellValue('D' . $row, $item->doctor_name);
                $sheet->setCellValue('E' . $row, $item->amount);
                $sheet->setCellValue('F' . $row, $outcomeType);
                // Convert to Persian date for Excel export using Verta
                $outcomeDate = '';
                if ($item->outcome_date) {
                    $outcomeDate = \Hekmatinasser\Verta\Facades\Verta::instance($item->outcome_date)->format('Y/m/d');
                }
                $sheet->setCellValue('G' . $row, $outcomeDate);
                $sheet->setCellValue('H' . $row, $item->reason);

                $row++;
            }

            return $this->exportResponse($spreadsheet);
        }
    }
    /**
     * Export the spreadsheet to a response
     * @param mixed $spreadsheet
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportResponse($spreadsheet)
    {
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="outcome_report.xls"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }
}