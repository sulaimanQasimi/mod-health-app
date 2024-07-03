<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewPACUNotification;
use App\Models\PACU;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Mpdf\Mpdf;
class PACUController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $pacus = PACU::where('branch_id',auth()->user()->branch_id)->with('patient')->where('status', 'new')->get();

                if ($pacus) {
                    return response()->json([
                        'data' => $pacus,
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Internal Server Error',
                        'code' => 500,
                        'data' => [],
                    ]);
                }
        }

        $pacus = PACU::where('branch_id',auth()->user()->branch_id)->with(['patient'])->where('status', 'new')->get();
        return view('pages.pacus.index', compact('pacus'));
    }

    public function completed()
    {
        $pacus = PACU::where('status', 'completed')->latest()->paginate(10);

        return view('pages.pacus.completed', compact('pacus'));
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
            'department_id' => 'required',
            'branch_id' => 'required',
            'appointment_id' => 'nullable',
            'hospitalization_id' => 'nullable',
            'description' => 'required',
            'operation_id' => 'nullable',
        ]);

        // Create a new appointment
        $pacu = PACU::create($validatedData);

        SendNewPACUNotification::dispatch($pacu->created_by, $pacu->id);
        // Redirect to the appointments index page with a success message
        return redirect()->back()->with('success', 'PACU created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PACU $pacu)
    {
        return view('pages.pacus.show',compact('pacu'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PACU $pacu)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PACU $pacu)
    {
        $data = $request->validate([
            'status' => 'nullable',

        ]);

        $pacu->update($data);


        return redirect()->route('pacus.new')->with('success', 'PACU updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PACU $pacu)
    {
        //
    }

    public function complete($pacuId)
    {
        $pacu = PACU::findOrFail($pacuId);
        $pacu->complete();
        return redirect()->route('pacus.index')->with('success', 'PACU Completed successfully.');

    }

    public function report()
    {
      
        return view('pages.pacus.reports.index');
    }
    public function reportSearch(Request $request)
    {
        $query = DB::table('p_a_c_u_s as pa')
        ->leftJoin('patients as p', 'pa.patient_id' , '=', 'p.id')
        ->leftJoin('branches as b', 'pa.branch_id' , '=', 'b.id')
        ->select('pa.id','p.name as patient_name','b.name as branch_name', 'pa.status');

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('status')) {
            $query->where('pa.status', $request->status);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('pa.created_at', [$request->from, $request->to]);
        }

        $items = $query->get();
    return view('pages.pacus.reports.report', ['items' => $items]);

    }

    
    public function exportReport(Request $request)
    {

        $data = json_decode($request->data, true);
      
        $items = DB::table('p_a_c_u_s as pa')
        ->leftJoin('patients as p', 'pa.patient_id' , '=', 'p.id')
        ->leftJoin('branches as b', 'pa.branch_id' , '=', 'b.id')
        ->select('pa.id','p.name as patient_name','b.name as branch_name', 'pa.status')
        ->whereIn('pa.id', $data)->get();
        $reader = new Xlsx();
        $spreadsheet = $reader->load("report_templates/pacus_report.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        $html = view('pages.pacus.reports.pdf_report',  ['items' => $items])->render();
        if ($request->type == 'pdf') {
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('pdf_report.pdf', 'D');
        }else {
            $spreadsheet = $reader->load("report_templates/pacus_report.xlsx");
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
                    $status = 'PACU های جدید';
                } else {
                    $status = 'PACU های تکمیل شده';
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
}
