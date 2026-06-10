<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewOperationNotification;
use App\Services\AnesthesiaReferralService;
use App\Models\Anesthesia;
use App\Models\Doctor;
use App\Models\OperationType;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Excel;
use HanifHefaz\Dcter\Dcter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Mpdf\Mpdf;
class AnesthesiaController extends Controller
{
    public function __construct(
        private readonly AnesthesiaReferralService $anesthesiaReferralService,
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function new(Request $request)
    {
        $query = Anesthesia::with(['patient', 'operationType', 'surgion', 'anesthesia_log', 'anesthesist', 'doctor'])
            ->where('status', 'new');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('patient', function($patientQuery) use ($search) {
                    $patientQuery->where('name', 'like', "%{$search}%")
                                 ->orWhere('id_card', 'like', "%{$search}%")
                                 ->orWhere('father_name', 'like', "%{$search}%");
                })
                ->orWhereHas('operationType', function($opQuery) use ($search) {
                    $opQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('surgion', function($surgionQuery) use ($search) {
                    $surgionQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('plan', 'like', "%{$search}%");
            });
        }

        // Operation Type filter
        if ($request->filled('operation_type_id')) {
            $query->where('operation_type_id', $request->operation_type_id);
        }

        // Department filter (via appointment)
        if ($request->filled('department_id')) {
            $query->whereHas('appointment', fn($q) => $q->where('department_id', (int) $request->input('department_id')));
        }

        // Date from filter
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        // Date to filter
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Anesthesia type filter
        if ($request->filled('anesthesia_type')) {
            $query->where('anesthesia_type', $request->anesthesia_type);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $anesthesias = $query->paginate($request->get('per_page', 15))->withQueryString();

        // Get filter options
        $operationTypes = \App\Models\OperationType::where('branch_id', auth()->user()->branch_id)->get();
        $departments = Department::all();

        return view('pages.anesthesias.new', compact('anesthesias', 'operationTypes', 'departments'));
    }

    public function approved(Request $request)
    {
        $query = Anesthesia::with(['patient', 'operationType', 'surgion', 'anesthesia_log', 'anesthesist', 'doctor'])
            ->where('status', 'approved');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('patient', function($patientQuery) use ($search) {
                    $patientQuery->where('name', 'like', "%{$search}%")
                                 ->orWhere('id_card', 'like', "%{$search}%")
                                 ->orWhere('father_name', 'like', "%{$search}%");
                })
                ->orWhereHas('operationType', function($opQuery) use ($search) {
                    $opQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('surgion', function($surgionQuery) use ($search) {
                    $surgionQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('plan', 'like', "%{$search}%");
            });
        }

        // Operation Type filter
        if ($request->filled('operation_type_id')) {
            $query->where('operation_type_id', $request->operation_type_id);
        }

        // Department filter (via appointment)
        if ($request->filled('department_id')) {
            $query->whereHas('appointment', fn($q) => $q->where('department_id', (int) $request->input('department_id')));
        }

        // Date from filter
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        // Date to filter
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Anesthesia type filter
        if ($request->filled('anesthesia_type')) {
            $query->where('anesthesia_type', $request->anesthesia_type);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $anesthesias = $query->paginate($request->get('per_page', 15))->withQueryString();

        // Get filter options
        $operationTypes = \App\Models\OperationType::where('branch_id', auth()->user()->branch_id)->get();
        $departments = Department::all();

        return view('pages.anesthesias.approved', compact('anesthesias', 'operationTypes', 'departments'));
    }

    public function rejected(Request $request)
    {
        $query = Anesthesia::with(['patient', 'operationType', 'surgion', 'anesthesia_log', 'anesthesist', 'doctor'])
            ->where('status', 'rejected');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('patient', function($patientQuery) use ($search) {
                    $patientQuery->where('name', 'like', "%{$search}%")
                                 ->orWhere('id_card', 'like', "%{$search}%")
                                 ->orWhere('father_name', 'like', "%{$search}%");
                })
                ->orWhereHas('operationType', function($opQuery) use ($search) {
                    $opQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('surgion', function($surgionQuery) use ($search) {
                    $surgionQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('plan', 'like', "%{$search}%");
            });
        }

        // Operation Type filter
        if ($request->filled('operation_type_id')) {
            $query->where('operation_type_id', $request->operation_type_id);
        }

        // Department filter (via appointment)
        if ($request->filled('department_id')) {
            $query->whereHas('appointment', fn($q) => $q->where('department_id', (int) $request->input('department_id')));
        }

        // Date from filter
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        // Date to filter
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Anesthesia type filter
        if ($request->filled('anesthesia_type')) {
            $query->where('anesthesia_type', $request->anesthesia_type);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $anesthesias = $query->paginate($request->get('per_page', 15))->withQueryString();

        // Get filter options
        $operationTypes = \App\Models\OperationType::where('branch_id', auth()->user()->branch_id)->get();
        $departments = Department::all();

        return view('pages.anesthesias.rejected', compact('anesthesias', 'operationTypes', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate($this->anesthesiaReferralService->validationRules());

        $this->anesthesiaReferralService->create($data, $request->user());

        return redirect()->back()->with('success', localize('global.anesthesia_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Anesthesia $anesthesia)
    {
        $anesthesia_prescription_count = \App\Models\Prescription::where(function ($q) use ($anesthesia) {
            $q->where('appointment_id', $anesthesia->appointment_id);
            if ($anesthesia->hospitalization_id) {
                $q->orWhere('hospitalization_id', $anesthesia->hospitalization_id);
            }
        })->count();
        $anesthesia_for_prescription = $anesthesia->only(['id', 'appointment_id', 'hospitalization_id', 'patient_id', 'branch_id']);
        return view('pages.anesthesias.show', compact('anesthesia', 'anesthesia_prescription_count', 'anesthesia_for_prescription'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Anesthesia $anesthesia)
    {
        // Doctors will be loaded via API, no need to pass them here
        $operationTypes = OperationType::where('branch_id', auth()->user()->branch_id)->get();

        return view('pages.anesthesias.edit', compact('anesthesia', 'operationTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Anesthesia $anesthesia)
    {
        $data = $request->validate([
            'anesthesia_log_reply' => 'required',
            'status' => 'nullable',
            'patient_status' => 'nullable',
            'anesthesia_type' => 'nullable',
            'is_operation_done' => 'nullable',
            'operation_remark' => 'nullable',
            'anesthesia_plan' => 'nullable',
            'operation_anesthesia_log_id' => 'nullable',
            'operation_anesthesist_id' => 'nullable',

        ]);
       
        $anesthesia->update($data);

        if (($data['status'] ?? null) === 'approved') {
            return redirect()
                ->route('anesthesias.show', $anesthesia)
                ->with('success', localize('global.anesthesia_updated_successfully.'));
        }

        return redirect()->route('anesthesias.new')->with('success', localize('global.anesthesia_updated_successfully.'));
    }

    public function referToOperation(Request $request, Anesthesia $anesthesia)
    {
        abort_unless($anesthesia->status === 'approved', 422);
        abort_if($anesthesia->is_referred_to_operation, 422);

        $anesthesia->update(['is_referred_to_operation' => true]);

        SendNewOperationNotification::dispatch($anesthesia->created_by, $anesthesia->id);

        return redirect()
            ->route('anesthesias.show', $anesthesia)
            ->with('success', localize('global.anesthesia_referred_to_operation_successfully.'));
    }

    public function updateAnesthesia(Request $request, Anesthesia $anesthesia)
    {
        // Validate the input
        $data = $request->validate([

            'patient_id' => 'required',
            'doctor_id' => 'required',
            'branch_id' => 'required',
            'appointment_id' => 'required',
            'operation_type_id' => 'required',
            'hospitalization_id' => 'nullable',
            'date' => 'required',
            'time' => 'required',
            'plan' => 'required',
            'position_on_bed' => 'required',
            'planned_duration' => 'required',
            'estimated_blood_waste' => 'required',
            'other_problems' => 'required',
            'status' => 'nullable',
            'patient_status' => 'nullable',
            'anesthesia_type' => 'nullable',
            'operation_status' => 'nullable',
            'anesthesia_log_reply' => 'nullable',
            'is_operation_done' => 'nullable',
            'operation_assistants_id' => 'nullable',
            'operation_surgion_id' => 'nullable',
            'operation_anesthesia_log_id' => 'nullable',
            'operation_anesthesist_id' => 'nullable',
            'operation_scrub_nurse_id' => 'nullable',
            'operation_circulation_nurse_id' => 'nullable',
            'anesthesia_plan' => 'nullable',
            'operation_expense_remarks' => 'nullable',
            'room_id' => 'nullable',
            'bed_id' => 'nullable',
            'is_reserved' => 'nullable',
            'reserve_reason' => 'nullable',
        ]);

        $data['operation_assistants_id'] = json_encode($data['operation_assistants_id']);
        $data['date'] = Dcter::JalaliToGregorian(Dcter::Carbonize($data['date']));
        $anesthesia->update($data);

        return redirect()->route('appointments.doctorAppointments')->with('success', localize('global.anesthesia_updated_successfully.'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Anesthesia $anesthesia)
    {
        $anesthesia->delete();
        return redirect()->back()->with('success', localize('global.anesthesia_deleted_successfully.'));
    }

    public function report()
    {
        $doctors = Doctor::orderBy('name')->get();
        $operationTypes = OperationType::where('branch_id', auth()->user()->branch_id)->get();
        $departments = Department::all();

        return view('pages.anesthesias.reports.index', compact('doctors', 'operationTypes', 'departments'));
    }

    public function reportSearch(Request $request)
    {
        $query = DB::table('anesthesias as a')
            ->leftJoin('patients as p', 'a.patient_id', '=', 'p.id')
            ->leftJoin('doctors as d', 'a.doctor_id', '=', 'd.id')
            ->leftJoin('branches as b', 'a.branch_id', '=', 'b.id')
            ->leftJoin('appointments as app', 'a.appointment_id', '=', 'app.id')
            ->select('a.id', 'p.name as patient_name', 'd.name as doctor_name', 'b.name as branch_name', 'a.status', 'a.anesthesia_type', 'a.date', 'a.time');

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('status')) {
            $query->where('a.status', $request->status);
        }

        if ($request->filled('doctor_id')) {
            $query->where('a.doctor_id', $request->doctor_id);
        }

        if ($request->filled('anesthesia_type')) {
            $query->where('a.anesthesia_type', $request->anesthesia_type);
        }

        if ($request->filled('operation_type_id')) {
            $query->where('a.operation_type_id', $request->operation_type_id);
        }

        if ($request->filled('department_id')) {
            $query->where('app.department_id', $request->department_id);
        }

        if ($request->filled('time')) {
            $query->where('a.time', $request->time);
        }

        // Date range: Persian (Jalali) from/to → filter on a.date (surgery date)
        if ($request->filled('from') && $request->filled('to')) {
            try {
                $fromDate = Verta::parse($request->from)->datetime()->format('Y-m-d');
                $toDate = Verta::parse($request->to)->datetime()->format('Y-m-d');
                $query->whereDate('a.date', '>=', $fromDate)->whereDate('a.date', '<=', $toDate);
            } catch (\Exception $e) {
                // Invalid Persian date format, skip date filter
            }
        }

        $items = $query->orderBy('a.date', 'desc')->orderBy('a.time', 'desc')->get();

        return view('pages.anesthesias.reports.report', ['items' => $items]);
    }


    public function exportReport(Request $request)
    {

        $data = json_decode($request->data, true);

        $items = DB::table('anesthesias as a')
        ->leftJoin('patients as p', 'a.patient_id' , '=', 'p.id')
        ->leftJoin('doctors as d', 'a.doctor_id' , '=', 'd.id')
        ->leftJoin('branches as b', 'a.branch_id' , '=', 'b.id')
        ->select('a.id','p.name as patient_name', 'd.name as doctor_name','b.name as branch_name', 'a.status', 'a.anesthesia_type', 'a.date', 'a.time')
        ->whereIn('a.id', $data)->get();
        $reader = new Xlsx();
        $spreadsheet = $reader->load("report_templates/anesthesias_report.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        $html = view('pages.anesthesias.reports.pdf_report',  ['items' => $items])->render();
        if ($request->type == 'pdf') {
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('pdf_report.pdf', 'D');
        }else {
            $spreadsheet = $reader->load("report_templates/anesthesias_report.xlsx");
            $sheet = $spreadsheet->getActiveSheet();
            $row = 3;

            foreach ($items as $index => $item) {


                $sheet->getStyle('A2:G' . $sheet->getHighestRow())->getAlignment()->setWrapText(true);
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(20);
                $sheet->getColumnDimension('H')->setWidth(20);
                $styleArray = array(
                    'font' => array(
                        'name' => 'B Nazanin',
                        'color' => 15,
                        'bold' => true

                    ),
                );

                $status = '';
                if ($item->status == 'new') {
                    $status = 'انستیزی های جدید';
                } elseif($item->status == 'approved') {
                    $status = 'انستیزی های تائید شده';
                } else{
                    $status = 'انستیزی های مسترد شده';
                }
                    $sheet->setCellValue('A' . $row . '', ++$index);
                    $sheet->setCellValue('B' . $row . '', $item->patient_name);
                    $sheet->setCellValue('C' . $row . '', $status);
                    $sheet->setCellValue('D' . $row . '', $item->doctor_name);
                    $sheet->setCellValue('E' . $row . '', $item->anesthesia_type);
                    $sheet->setCellValue('F' . $row . '', $item->branch_name);
                    $sheet->setCellValue('G' . $row . '', $item->date);
                    $sheet->setCellValue('H' . $row . '', $item->time);

                $row++;
            }

return $this->exportResponse($spreadsheet);
}
    }


    public function exportResponse($spreadsheet){
        $writer = new WriterXlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="item_report.xls"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;

    }
}
