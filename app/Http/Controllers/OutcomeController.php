<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OutcomeController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('prescription_items as pi')
            ->leftJoin('prescription_alternative_items as pai', function($join) {
                $join->on('pai.prescription_item_id', '=', 'pi.id')
                     ->on('pai.prescription_id', '=', 'pi.prescription_id')
                     ->whereRaw('(pai.is_selected = 1 AND pai.deleted_at IS NULL)');
            })
            ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
            ->join('medicines as m', function($join) {
                $join->whereRaw('m.id = COALESCE(pai.medicine_id, pi.medicine_id)');
            })
            ->whereNull('pi.deleted_at')
            ->whereNull('m.deleted_at')
            ->whereNull('p.deleted_at')
            ->select(
                'm.id',
                'm.name',
                DB::raw('COUNT(*) as usage_count')
            )
            ->groupBy('m.id', 'm.name');

        dd($query->toSql());
        // Get current user's pharmacies
        $user = Auth::user();
        $userPharmacies = $user->activePharmacies;

        // Filter by user's pharmacies if user has any
        if ($userPharmacies->isNotEmpty()) {
            $query->whereIn('p.pharmacy_id', $userPharmacies->pluck('id'));
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('m.name', 'like', "%{$search}%");
        }

        // Filter by pharmacy (for admin users who can see all pharmacies)
        if ($request->filled('pharmacy_id') && $user->hasRole('admin')) {
            $query->where('p.pharmacy_id', $request->pharmacy_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $fromDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->date_from)->datetime();
            $query->whereDate('p.created_at', '>=', $fromDate);
        }
        if ($request->filled('date_to')) {
            $toDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->date_to)->datetime();
            $query->whereDate('p.created_at', '<=', $toDate);
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'usage_count');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Map sort_by to valid columns
        $validSortColumns = ['id' => 'm.id', 'name' => 'm.name', 'usage_count' => 'usage_count'];
        $sortColumn = $validSortColumns[$sortBy] ?? 'usage_count';
        $query->orderBy($sortColumn, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $outcomes = $query->paginate($perPage);

        // Get all pharmacies for admin filter
        $pharmacies = null;
        if ($user->hasRole('admin')) {
            $pharmacies = Pharmacy::orderBy('name')->get();
        }

        return view('pages.outcomes.index', compact('outcomes', 'pharmacies', 'userPharmacies'));
    }

    public function reportSearch(Request $request)
    {
        $query = DB::table('outcomes as o')
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
            );

        // Get current user's pharmacy for filtering
        $user = Auth::user();
        $userPharmacy = $user->activePharmacies()->first();

        // Filter by pharmacy if user has one (and is not admin)
        if ($userPharmacy && !$user->hasRole('admin')) {
            $query->where('o.pharmacy_id', $userPharmacy->id);
        }

        // Filter by medicine name
        if ($request->filled('medicine_name')) {
            $query->where('m.name', 'like', '%' . $request->medicine_name . '%');
        }

        // Filter by patient name
        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%' . $request->patient_name . '%');
        }

        // Filter by outcome type
        if ($request->filled('outcome_type')) {
            $query->where('o.outcome_type', $request->outcome_type);
        }

        // Filter by pharmacy (for admin users)
        if ($request->filled('pharmacy_id') && $user->hasRole('admin')) {
            $query->where('o.pharmacy_id', $request->pharmacy_id);
        }

        // Filter by date range - Convert Persian to Gregorian
        if ($request->filled('from') && $request->filled('to')) {
            
            // Convert Persian dates to Gregorian
            $fromDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->from)->datetime();
            $toDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->to)->datetime();

            $query->whereDate('o.outcome_date', '>=', $fromDate)->whereDate('o.outcome_date', '<=', $toDate);
        }

        $items = $query->orderBy('o.outcome_date', 'desc')->get();
        
        return view('pages.outcomes.reports.report', ['items' => $items]);
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