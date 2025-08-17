<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewLabNotification;
use App\Models\Appointment;
use App\Models\Lab;
use App\Models\LabItem;
use App\Models\LabTypeSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Mpdf\Mpdf;
class LabController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $labs = Lab::where('branch_id', auth()->user()->branch_id)->where('status',false)->latest()->paginate(10);
        return view('pages.labs.index',compact('labs'));
    }

    public function completed()
    {
        $labs = Lab::where('branch_id', auth()->user()->branch_id)->where('status',true)->latest()->paginate(10);
        return view('pages.labs.completed',compact('labs'));
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
    // return $request->doctor_id;
    $data = $request->validate([
        'lab_type_id' => 'required|array',
        'appointment_id' => 'required',
        'patient_id' => 'required',
        'doctor_id' => 'required',
        'branch_id' => 'required',
        'status' => 'nullable',
        'hospitalization_id' => 'nullable',
        'under_review_id' => 'nullable',
        'i_c_u_id' => 'nullable',
    ]);

    $labTypeIds = $data['lab_type_id'];
    unset($data['lab_type_id']);
// dd($request->all());
    $lab_item_data = [
        'branch_id' => $request->branch_id,
        'appointment_id' => $request->appointment_id,
        'hospitalization_id' => $request->hospitalization_id,
        'under_review_id' => $request->under_review_id,
        'i_c_u_id' => $request->i_c_u_id,
        'lab_type_id' => $labTypeIds[0],
        'patient_id' => $request->patient_id,
        'doctor_id' => $request->doctor_id,
        'lab_type_section_id' => $request->lab_type_section,
    ];
    $lab = Lab::create($lab_item_data);

    $data['lab_id'] = $lab->id;
    foreach ($labTypeIds as $labTypeId) {
        $labData = array_merge($data, ['lab_type_id' => $labTypeId]);
        $lab_item = LabItem::create($labData);
    }

    SendNewLabNotification::dispatch($lab->created_by, $lab->id);

    return redirect()->back()->with('success', localize('global.lab_test_created_successfully.'));
}

    /**
     * Display the specified resource.
     */
    public function show(Lab $lab)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lab $lab)
    {
        return view('pages.labs.edit',compact('lab'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lab $lab)
    {
        $data = $request->validate([
            'result' => 'required',
            'result_file' => 'nullable|mimes:pdf,jpeg,png,jpg,gif|max:2048',
            'status' => 'required',
        ]);

        // Handle the result file upload
        if ($request->hasFile('result_file')) {
            $resultFile = $request->file('result_file');
            $resultFileName = time().'.'.$resultFile->getClientOriginalExtension();

            // Store the result file in the storage/app/public directory
            $resultFile->storeAs('public', $resultFileName);

            // Update the result_file field
            $data['result_file'] = $resultFileName;
        }

        $lab->update($data);

        return redirect()->route('lab_tests.index')->with('success', localize('global.lab_test_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lab $lab)
    {
        //
    }

    public function printCard($lab)
    {

        $lab = Lab::findOrFail($lab);

        $patient = $lab->patient;

        return view('pages.labs.print_card', compact('patient','lab'));
    }



    public function report()
    {
        $labTypeSections = LabTypeSection::all();
        return view('pages.labs.reports.index', compact('labTypeSections'));
    }
    public function reportSearch(Request $request)
    {
        $query = DB::table('labs as l')
        ->leftJoin('patients as p', 'l.patient_id' , '=', 'p.id')
        ->leftJoin('doctors as d', 'l.doctor_id' , '=', 'd.id')
        ->leftJoin('branches as b', 'l.branch_id' , '=', 'b.id')
        ->leftJoin('lab_type_sections as lts', 'l.lab_type_section_id' , '=', 'lts.id')
        ->select('l.id','p.name as patient_name', 'd.name as doctor_name','b.name as branch_name','lts.section as lab_type_section_name', 'l.status');

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('lab_type_section_id')) {
            $query->where('l.lab_type_section_id', $request->lab_type_section_id);
        }

        if ($request->filled('status')) {
            $query->where('l.status', $request->status);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('l.created_at', [$request->from, $request->to]);
        }

        $items = $query->get();
    return view('pages.labs.reports.report', ['items' => $items]);

    }


    public function exportReport(Request $request)
    {

        $data = json_decode($request->data, true);

        $items = DB::table('labs as l')
        ->leftJoin('patients as p', 'l.patient_id' , '=', 'p.id')
        ->leftJoin('doctors as d', 'l.doctor_id' , '=', 'd.id')
        ->leftJoin('branches as b', 'l.branch_id' , '=', 'b.id')
        ->leftJoin('lab_type_sections as lts', 'l.lab_type_section_id' , '=', 'lts.id')
        ->select('l.id','p.name as patient_name', 'd.name as doctor_name','b.name as branch_name','lts.section as lab_type_section_name', 'l.status')
        ->whereIn('l.id', $data)->get();
        $reader = new Xlsx();
        $spreadsheet = $reader->load("report_templates/lab_report.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        $html = view('pages.labs.reports.pdf_report',  ['items' => $items])->render();
        if ($request->type == 'pdf') {
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('pdf_report.pdf', 'D');
        }else {
            $spreadsheet = $reader->load("report_templates/lab_report.xlsx");
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
                $styleArray = array(
                    'font' => array(
                        'name' => 'B Nazanin',
                        'color' => 15,
                        'bold' => true

                    ),
                );

                $status = '';
                if ($item->status == '0') {
                    $status = 'معاینات تحت کار';
                } else {
                    $status = 'معاینات تکمیل شده';
                }
                    $sheet->setCellValue('A' . $row . '', ++$index);
                    $sheet->setCellValue('B' . $row . '', $item->patient_name);
                    $sheet->setCellValue('C' . $row . '', $status);
                    $sheet->setCellValue('D' . $row . '', $item->doctor_name);
                    $sheet->setCellValue('E' . $row . '', $item->lab_type_section_name);
                    $sheet->setCellValue('F' . $row . '', $item->branch_name);

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
