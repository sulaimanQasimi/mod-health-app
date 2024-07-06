<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewICUNotification;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\Department;
use App\Models\FoodType;
use App\Models\ICU;
use App\Models\ICUProcedureType;
use App\Models\LabType;
use App\Models\LabTypeSection;
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\Relation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Mpdf\Mpdf;
class ICUController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $icus = ICU::where('branch_id',auth()->user()->branch_id)->with('patient')->get();

                if ($icus) {
                    return response()->json([
                        'data' => $icus,
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Internal Server Error',
                        'code' => 500,
                        'data' => [],
                    ]);
                }
        }

        $icus = ICU::where('branch_id',auth()->user()->branch_id)->with(['patient'])->get();
        return view('pages.icus.index', compact('icus'));
    }

    public function new()
    {
        $icus = ICU::where('status', 'new')->latest()->paginate(10);

        return view('pages.icus.new', compact('icus'));
    }

    public function approved()
    {
        $icus = ICU::where('status', 'approved')->latest()->paginate(10);

        return view('pages.icus.approved', compact('icus'));
    }

    public function rejected()
    {
        $icus = ICU::where('status', 'rejected')->latest()->paginate(10);

        return view('pages.icus.rejected', compact('icus'));
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


        // Validate the input
        $validatedData = $request->validate([
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'branch_id' => 'required',
            'appointment_id' => 'nullable',
            'hospitalization_id' => 'nullable',
            'description' => 'required',
            'operation_id' => 'nullable',
            'icu_enterance_note' => 'nullable',
            'icu_reject_reason' => 'nullable',

        ]);

        // Create a new appointment
        $icu = ICU::create($validatedData);

        SendNewICUNotification::dispatch($icu->created_by, $icu->id);
        // Redirect to the appointments index page with a success message
        return redirect()->back()->with('success', localize('global.icu_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ICU $icu)
    {
        $labTypes = LabType::all();
        $labTypeSections = LabTypeSection::all();
        $previousDiagnoses = $icu->patient->diagnoses;
        $previousLabs = $icu->patient->labs;
        $branches = Branch::all();
        $departments = Department::all();
        $doctors = User::all();
        $foodTypes = FoodType::all();
        $medicineTypes = MedicineType::all();
        $medicines = Medicine::all();
        $procedure_types = ICUProcedureType::all();
        $rooms = Room::all();
        $beds = Bed::all();
        $relations = Relation::all();
        return view('pages.icus.show',compact('icu','previousDiagnoses','previousLabs','labTypes','labTypeSections','branches','departments','doctors','foodTypes','medicineTypes','medicines','procedure_types','rooms','beds','relations'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ICU $icu)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ICU $icu)
    {
        $data = $request->validate([
            'icu_enterance_note' => 'nullable',
            'status' => 'nullable',
            'icu_reject_reason' => 'nullable',
            'discharge_status' => 'nullable',
            'discharge_remark' => 'nullable',
            'discharged_at' => 'nullable',
            'cause_of_death' => 'nullable',
            'death_date' => 'nullable',
            'death_time' => 'nullable',
            'move_department_id' => 'nullable',
            'is_discharged' => 'nullable',
            'transfer_date' => 'nullable',
            'brief_history' => 'nullable',

        ]);

        $icu->update($data);

        return redirect()->back()->with('success', localize('global.icu_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ICU $icu)
    {
        //
    }

    public function report()
    {

        return view('pages.icus.reports.index');
    }
    public function reportSearch(Request $request)
    {
        $query = DB::table('i_c_u_s as i')
        ->leftJoin('patients as p', 'i.patient_id' , '=', 'p.id')
        ->leftJoin('doctors as d', 'i.doctor_id' , '=', 'd.id')
        ->leftJoin('branches as b', 'i.branch_id' , '=', 'b.id')
        ->select('i.id','p.name as patient_name', 'd.name as doctor_name','b.name as branch_name', 'i.status');

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('status')) {
            $query->where('i.status', $request->status);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('i.created_at', [$request->from, $request->to]);
        }

        $items = $query->get();
    return view('pages.icus.reports.report', ['items' => $items]);

    }


    public function exportReport(Request $request)
    {

        $data = json_decode($request->data, true);

        $items = DB::table('i_c_u_s as i')
        ->leftJoin('patients as p', 'i.patient_id' , '=', 'p.id')
        ->leftJoin('doctors as d', 'i.doctor_id' , '=', 'd.id')
        ->leftJoin('branches as b', 'i.branch_id' , '=', 'b.id')
        ->select('i.id','p.name as patient_name', 'd.name as doctor_name','b.name as branch_name', 'i.status')
        ->whereIn('i.id', $data)->get();
        $reader = new Xlsx();
        $spreadsheet = $reader->load("report_templates/icus_report.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        $html = view('pages.icus.reports.pdf_report',  ['items' => $items])->render();
        if ($request->type == 'pdf') {
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('pdf_report.pdf', 'D');
        }else {
            $spreadsheet = $reader->load("report_templates/icus_report.xlsx");
            $sheet = $spreadsheet->getActiveSheet();
            $row = 3;

            foreach ($items as $index => $item) {


                $sheet->getStyle('A2:G' . $sheet->getHighestRow())->getAlignment()->setWrapText(true);
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
                $styleArray = array(
                    'font' => array(
                        'name' => 'B Nazanin',
                        'color' => 15,
                        'bold' => true

                    ),
                );

                $status = '';
                if ($item->status == 'new') {
                    $status = 'ICU های جدید';
                } elseif ($item->status == 'approved') {
                    $status = 'ICU های تائید شده';
                }else{
                    $status = 'ICU های مسترد شده';
                }
                    $sheet->setCellValue('A' . $row . '', ++$index);
                    $sheet->setCellValue('B' . $row . '', $item->patient_name);
                    $sheet->setCellValue('C' . $row . '', $status);
                    $sheet->setCellValue('D' . $row . '', $item->doctor_name);
                    $sheet->setCellValue('E' . $row . '', $item->branch_name);

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

    public function printDeathCard(ICU $icu)
    {
        return view('pages.icus.print_death_card', compact('icu'));
    }

    public function printMoveCard(ICU $icu)
    {
        return view('pages.icus.print_move_card', compact('icu'));
    }
}
