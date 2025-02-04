<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewAnesthesiaNotification;
use App\Jobs\SendNewOperationNotification;
use App\Models\Anesthesia;
use App\Models\OperationType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use HanifHefaz\Dcter\Dcter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Mpdf\Mpdf;
class AnesthesiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function new()
    {
        $anesthesias = Anesthesia::where('status', 'new')->latest()->paginate(10);

        return view('pages.anesthesias.new', compact('anesthesias'));
    }

    public function approved()
    {
        $anesthesias = Anesthesia::where('status', 'approved')->latest()->paginate(10);

        return view('pages.anesthesias.approved', compact('anesthesias'));
    }

    public function rejected()
    {
        $anesthesias = Anesthesia::where('status', 'rejected')->latest()->paginate(10);

        return view('pages.anesthesias.rejected', compact('anesthesias'));
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
            'patient_status' => 'nullable',
        ]);

        $data['operation_assistants_id'] = json_encode($data['operation_assistants_id']);
        $data['date'] = Dcter::JalaliToGregorian(Dcter::Carbonize($data['date']));

        // Create a new appointment
        $anesthesia = Anesthesia::create($data);

        SendNewAnesthesiaNotification::dispatch($anesthesia->created_by, $anesthesia->id);


        // Redirect to the appointments index page with a success message
        return redirect()->back()->with('success', localize('global.anesthesia_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Anesthesia $anesthesia)
    {
        $operation_doctors = User::where('branch_id', auth()->user()->branch_id)->get();
        return view('pages.anesthesias.show', compact('anesthesia','operation_doctors'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Anesthesia $anesthesia)
    {
        $operation_doctors = User::where('branch_id', auth()->user()->branch_id)->get();
        $operationTypes = OperationType::where('branch_id', auth()->user()->branch_id)->get();

        return view('pages.anesthesias.edit', compact('anesthesia','operation_doctors','operationTypes'));
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

        if ($data['status'] == 'approved') {
            SendNewOperationNotification::dispatch($anesthesia->created_by, $anesthesia->id);
        }


        return redirect()->route('anesthesias.new')->with('success', localize('global.anesthesia_updated_successfully.'));
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

        return view('pages.anesthesias.reports.index');
    }
    public function reportSearch(Request $request)
    {
        $query = DB::table('anesthesias as a')
        ->leftJoin('patients as p', 'a.patient_id' , '=', 'p.id')
        ->leftJoin('doctors as d', 'a.doctor_id' , '=', 'd.id')
        ->leftJoin('branches as b', 'a.branch_id' , '=', 'b.id')
        ->select('a.id','p.name as patient_name', 'd.name as doctor_name','b.name as branch_name', 'a.status', 'a.anesthesia_type', 'a.date', 'a.time');

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('status')) {
            $query->where('a.status', $request->status);
        }
        if ($request->filled('anesthesia_type')) {
            $query->where('a.anesthesia_type', $request->anesthesia_type);
        }

        if ($request->filled('date')) {
            $query->where('a.date', $request->date);
        }

        if ($request->filled('time')) {
            $query->where('a.time', $request->time);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('a.created_at', [$request->from, $request->to]);
        }

        $items = $query->get();
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
