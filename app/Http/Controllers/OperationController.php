<?php

namespace App\Http\Controllers;

use App\Models\Anesthesia;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\FoodType;
use App\Models\Nurse;
use App\Models\Operation;
use App\Models\Prescription;
use App\Models\Relation;
use App\Models\Room;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Mpdf\Mpdf;
use App\Models\OperationType;

class OperationController extends Controller
{



    /**
     * Apply advanced filters to an operations (anesthesias) query.
     */
    private function applyOperationsFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->whereHas('patient', function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('father_name', 'like', $term)
                    ->orWhere('id_card', 'like', $term);
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('operationType', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('operation_type_id')) {
            $query->where('operation_type_id', $request->operation_type_id);
        }

        if ($request->filled('surgeon_id')) {
            $query->where('operation_surgion_id', $request->surgeon_id);
        }

        // Date filters: Persian (Jalali) from datepicker_dari, convert with Verta
        if ($request->filled('date_from')) {
            try {
                $query->whereDate('date', '>=', Verta::parse($request->date_from)->datetime());
            } catch (\Exception $e) {
                // If Verta parse fails, ignore filter
            }
        }

        if ($request->filled('date_to')) {
            try {
                $query->whereDate('date', '<=', Verta::parse($request->date_to)->datetime());
            } catch (\Exception $e) {
                // If Verta parse fails, ignore filter
            }
        }

        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSort = ['date', 'created_at', 'time'];
        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'date';
        }
        $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');

        return $query;
    }

    /**
     * Get filter data (branches, departments, operation types, surgeons) for operations views.
     */
    private function getOperationsFilterData()
    {
        $branches = Branch::orderBy('name')->get(['id', 'name']);
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $operationTypes = OperationType::orderBy('name')->get(['id', 'name']);
        $surgeons = Doctor::where('active_status', true)->orderBy('name')->get(['id', 'name']);

        return compact('branches', 'departments', 'operationTypes', 'surgeons');
    }

    /**
     * Display a listing of the resource.
     */
    public function new(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : 15;

        $query = Anesthesia::with(['patient', 'operationType'])
            ->where('status', 'approved')
            ->where('is_operation_approved', '0')
            ->where('is_reserved', '0');

        $query = $this->applyOperationsFilters($query, $request);
        $operations = $query->paginate($perPage)->withQueryString();

        $filterData = $this->getOperationsFilterData();
        return view('pages.operations.new', array_merge(compact('operations'), $filterData));
    }

    public function reserved(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : 15;

        $query = Anesthesia::with(['patient', 'operationType'])->reserved();
        $query = $this->applyOperationsFilters($query, $request);
        $reservedOperations = $query->paginate($perPage)->withQueryString();

        $filterData = $this->getOperationsFilterData();
        return view('pages.operations.reserved', array_merge(compact('reservedOperations'), $filterData));
    }

    public function approved(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : 15;

        $query = Anesthesia::with(['patient', 'operationType', 'scrub_nurse', 'circulation_nurse'])
            ->where('status', 'approved')
            ->where('is_operation_approved', '1')
            ->where('is_operation_done', '0')
            ->where('is_reserved', '0');

        $query = $this->applyOperationsFilters($query, $request);
        $operations = $query->paginate($perPage)->withQueryString();

        $filterData = $this->getOperationsFilterData();
        return view('pages.operations.approved', array_merge(compact('operations'), $filterData));
    }

    public function completed(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : 15;

        $query = Anesthesia::with(['patient', 'operationType', 'scrub_nurse', 'circulation_nurse'])->where('is_operation_done', '1');
        $query = $this->applyOperationsFilters($query, $request);
        $operations = $query->paginate($perPage)->withQueryString();

        $filterData = $this->getOperationsFilterData();
        return view('pages.operations.completed', array_merge(compact('operations'), $filterData));
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Anesthesia $operation)
    {
        $operation_doctors = Doctor::where('branch_id', auth()->user()->branch_id)
            ->where('active_status', true)
            ->get();
        $branchId = auth()->user()->branch_id;
        $operation_nurses = Nurse::where('employment_status', 'active')

            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        $rooms = Room::all();
        $beds = Bed::all();
        $foodTypes = FoodType::all();
        $relations = Relation::all();
        $operation_prescription_count = Prescription::where(function ($q) use ($operation) {
            $q->where('appointment_id', $operation->appointment_id);
            if ($operation->hospitalization_id) {
                $q->orWhere('hospitalization_id', $operation->hospitalization_id);
            }
        })->count();
        $operation_for_prescription = $operation->only(['id', 'appointment_id', 'hospitalization_id', 'patient_id', 'branch_id']);
        return view('pages.operations.show', compact('operation', 'operation_doctors', 'operation_nurses', 'rooms', 'beds', 'foodTypes', 'relations', 'operation_prescription_count', 'operation_for_prescription'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Operation $operation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Anesthesia $operation)
    {
        $data = $request->validate([
            'is_operation_done' => 'nullable',
            'is_operation_approved' => 'nullable',
            'operation_remark' => 'nullable',
            'operation_result' => 'nullable',
            'operation_scrub_nurse_id' => 'nullable',
            'operation_circulation_nurse_id' => 'nullable',
            'date' => 'nullable',
            'time' => 'nullable',
            'operation_expense_remarks' => 'nullable',
            'patient_status' => 'nullable',

        ]);

        // Room and bed removed from operation approval form; keep DB null
        $data['room_id'] = $request->input('room_id');
        $data['bed_id'] = $request->input('bed_id');

        if (isset($data['date']) && $data['date'] > $operation->date) {
            $operation->reserve();
            $operation->update($data);
            return redirect()->route('operations.reserved')->with('success', localize('global.operation_reserved_successfully.'));
        } elseif (isset($data['date']) && $data['date'] < $operation->date) {
            $operation->update($data);
            return redirect()->back()->with('success', localize('global.operation_updated_successfully.'));
        } else {
            $operation->update($data);
            return redirect()->back()->with('success', localize('global.operation_updated_successfully.'));
        }
    }

    /**
     * 
     * This complete the operation and return the success message 
     * if the bed is occupied, it releases the bed
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Anesthesia $operation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Request $request, Anesthesia $operation)
    {
        $data = $request->validate([
            'is_operation_done' => 'nullable',
            'operation_remark' => 'nullable',
            'operation_result' => 'nullable',
            'room_id' => 'nullable',
            'bed_id' => 'nullable',

        ]);

        $data['room_id'] = $operation->room->id ?? null;
        $data['bed_id'] = $operation->bed->id ?? null;

        $occupied_bed = Bed::find($data['bed_id']);
        if ($occupied_bed) {
            $occupied_bed->update(['is_occupied' => false]);
            $occupied_bed->save();
        }

        $operation->update($data);

        return redirect()->back()->with('success', localize('global.operation_completed_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Operation $operation)
    {
        //
    }

    public function reserveOperation(Request $request, $operationId)
    {

        $operation = Anesthesia::findOrFail($operationId);
        $operation->reserve();
        $operation->update(['reserve_reason' => $request->reserve_reason]);
        $operation->save();

        // Add any additional logic, such as redirecting or returning a response
        return redirect()->route('operations.reserved')->with('success', localize('global.operation_reserved_successfully.'));
    }

    public function unreserveOperation($operationId)
    {

        $operation = Anesthesia::findOrFail($operationId);
        $operation->unreserve();
        $operation->update(['is_operation_approved' => '0']);
        $operation->save();
        // Add any additional logic, such as redirecting or returning a response
        return redirect()->back()->with('success', localize('global.operation_unreserved_successfully.'));
    }

    public function report()
    {

        $operationTypes = OperationType::all();

        return view('pages.operations.reports.index', compact('operationTypes'));
    }
    public function reportSearch(Request $request)
    {
        $query = DB::table('anesthesias as a')
            ->leftJoin('patients as p', 'a.patient_id', '=', 'p.id')
            ->leftJoin('doctors as d', 'a.doctor_id', '=', 'd.id')
            ->leftJoin('branches as b', 'a.branch_id', '=', 'b.id')
            ->leftJoin('doctors as u', 'a.operation_surgion_id', '=', 'u.id')
            ->leftJoin('operation_types as ot', 'a.operation_type_id', '=', 'ot.id')
            ->select(
                'a.id',
                'p.name as patient_name',
                'd.name as doctor_name',
                'b.name as branch_name',
                'a.status',
                'a.anesthesia_type',
                'a.date',
                'a.time',
                'u.name as operation_surgion_name',
                'ot.name as operation_type_name',
                'a.is_operation_done',
                'a.is_operation_approved',
                'a.is_reserved'
            );

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('operation_surgion_name')) {
            $query->where('u.name', 'like', '%' . $request->operation_surgion_name . '%');
        }

        if ($request->filled('operation_status')) {
            $query->where('a.is_operation_done', $request->operation_status);
        }

        if ($request->filled('operation_approval')) {
            $query->where('a.is_operation_approved', $request->operation_approval);
        }

        if ($request->filled('reserve_status')) {
            $query->where('a.is_reserved', $request->reserve_status);
        }

        if ($request->filled('operation_type_id')) {
            $query->where('a.operation_type_id', $request->operation_type_id);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('a.created_at', [$request->from, $request->to]);
        }

        $items = $query->get();
        return view('pages.operations.reports.report', ['items' => $items]);
    }


    public function exportReport(Request $request)
    {

        $data = json_decode($request->data, true);

        $items = DB::table('anesthesias as a')
            ->leftJoin('patients as p', 'a.patient_id', '=', 'p.id')
            ->leftJoin('doctors as d', 'a.doctor_id', '=', 'd.id')
            ->leftJoin('branches as b', 'a.branch_id', '=', 'b.id')
            ->leftJoin('doctors as u', 'a.operation_surgion_id', '=', 'u.id')
            ->leftJoin('operation_types as ot', 'a.operation_type_id', '=', 'ot.id')
            ->select(
                'a.id',
                'p.name as patient_name',
                'd.name as doctor_name',
                'b.name as branch_name',
                'a.status',
                'a.anesthesia_type',
                'a.date',
                'a.time',
                'u.name as operation_surgion_name',
                'ot.name as operation_type_name',
                'a.is_operation_done',
                'a.is_operation_approved',
                'a.is_reserved'
            )
            ->whereIn('a.id', $data)->get();
        $reader = new Xlsx();
        $spreadsheet = $reader->load("report_templates/operations_report.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        $html = view('pages.operations.reports.pdf_report',  ['items' => $items])->render();
        if ($request->type == 'pdf') {
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('pdf_report.pdf', 'D');
        } else {
            $spreadsheet = $reader->load("report_templates/operations_report.xlsx");
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
                $sheet->getColumnDimension('I')->setWidth(20);
                $styleArray = array(
                    'font' => array(
                        'name' => 'B Nazanin',
                        'color' => 15,
                        'bold' => true

                    ),
                );

                $operation_done = '';
                if ($item->is_operation_done == '0') {
                    $operation_done = 'نااجراء';
                } else {
                    $operation_done = 'تکمیل';
                }

                $operation_approved = '';
                if ($item->is_operation_approved == '0') {
                    $operation_approved = 'تائید ناشده';
                } else {
                    $operation_approved = 'تائید شده';
                }

                $reserved = '';
                if ($item->is_reserved == '0') {
                    $reserved = 'ریزرف ناشده';
                } else {
                    $reserved = 'ریزرف شده';
                }
                $sheet->setCellValue('A' . $row . '', ++$index);
                $sheet->setCellValue('B' . $row . '', $item->patient_name);
                $sheet->setCellValue('C' . $row . '', $item->operation_surgion_name);
                $sheet->setCellValue('D' . $row . '', $operation_done);
                $sheet->setCellValue('E' . $row . '', $operation_approved);
                $sheet->setCellValue('F' . $row . '', $reserved);
                $sheet->setCellValue('G' . $row . '', $item->operation_type_name);
                $sheet->setCellValue('H' . $row . '', $item->date);
                $sheet->setCellValue('I' . $row . '', $item->time);

                $row++;
            }

            return $this->exportResponse($spreadsheet);
        }
    }


    public function exportResponse($spreadsheet)
    {
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
