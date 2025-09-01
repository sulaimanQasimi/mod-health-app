<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewBloodBankNotification;
use App\Models\BloodBank;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Mpdf\Mpdf;

class BloodBankController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function new()
    {
        $bloodRequests = BloodBank::where('branch_id', auth()->user()->branch_id)->where('status', 'new')->paginate(15);
        return view('pages.blood_banks.new', compact('bloodRequests'));
    }

    public function approved()
    {
        $bloodRequests = BloodBank::where('branch_id', auth()->user()->branch_id)->where('status', 'approved')->paginate(15);
        return view('pages.blood_banks.approved', compact('bloodRequests'));
    }

    public function rejected()
    {
        $bloodRequests = BloodBank::where('branch_id', auth()->user()->branch_id)->where('status', 'rejected')->paginate(15);
        return view('pages.blood_banks.rejected', compact('bloodRequests'));
    }

    public function delivered()
    {
        $bloodRequests = BloodBank::where('branch_id', auth()->user()->branch_id)->where('status', 'delivered')->paginate(15);
        return view('pages.blood_banks.delivered', compact('bloodRequests'));
    }

    public function approve($bloodBank)
    {
        $bloodBank = BloodBank::findOrFail($bloodBank);
        $bloodBank->approve();

        return redirect()->back();
    }

    public function reject(Request $request, $bloodBank)
    {
        $bloodBank = BloodBank::findOrFail($bloodBank);
        $bloodBank->reject();
        $bloodBank->update(['reject_reason' => $request->reject_reason]);
        $bloodBank->save();
        return redirect()->back();
    }

    public function deliver($bloodBank)
    {
        $bloodBank = BloodBank::findOrFail($bloodBank);
        $bloodBank->deliver();
        return redirect()->back();
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
            'group' => 'required|string',
            'rh' => 'required|string',
            'type' => 'required|string',
            'quantity' => 'required|integer',
            'branch_id' => 'required|exists:branches,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'hospitalization_id' => 'nullable|exists:hospitalizations,id',
            'anesthesia_id' => 'nullable|exists:anesthesias,id',
            'patient_id' => 'nullable|exists:patients,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        // Create the blood bank record
        $bloodBank = BloodBank::create([
            'created_by' => auth()->id(),
            'group' => $validatedData['group'],
            'rh' => $validatedData['rh'],
            'type' => $validatedData['type'],
            'quantity' => $validatedData['quantity'],
            'branch_id' => $validatedData['branch_id'],
            'appointment_id' => $validatedData['appointment_id'],
            'hospitalization_id' => $validatedData['hospitalization_id'],
            'anesthesia_id' => $validatedData['anesthesia_id'],
            'patient_id' => $validatedData['patient_id'],
            'department_id' => $validatedData['department_id'],
        ]);
        // Send notification
        SendNewBloodBankNotification::dispatch($bloodBank->created_by, $bloodBank->id);

        return redirect()->back()->with('success', localize('global.blood_request_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(BloodBank $bloodBank)
    {
        return view('pages.blood_banks.show', compact('bloodBank'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BloodBank $bloodBank)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BloodBank $bloodBank)
    {
        $validatedData = $request->validate([
            'group' => 'required|string',
            'rh' => 'required|string',
            'type' => 'required|string',
            'quantity' => 'required|integer',
          
        ]);
        // Update the blood bank record
        $bloodBank->update([
                'group' => $validatedData['group'],
                'rh' => $validatedData['rh'],
                'type' => $validatedData['type'],
                'quantity' => $validatedData['quantity'],
          ]);
        // Send notification
        // SendNewBloodBankNotification::dispatch($bloodBank->created_by, $bloodBank->id);

        return redirect()->back()->with('success', localize('global.blood_request_updated_successfully'));
    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BloodBank $bloodBank)
    {
        $bloodBank->delete();
        return redirect()->back()->with('success', localize('global.blood_request_deleted_successfully'));
    }

    public function report()
    {
        $departments = Department::all();
        return view('pages.blood_banks.reports.index', compact('departments'));
    }
    public function reportSearch(Request $request)
    {
        $query = DB::table('blood_banks as bb')
            ->leftJoin('patients as p', 'bb.patient_id', '=', 'p.id')
            ->leftJoin('departments as d', 'bb.department_id', '=', 'd.id')
            ->leftJoin('branches as b', 'bb.branch_id', '=', 'b.id')
            ->select('bb.id', 'p.name as patient_name', 'd.name as department_name', 'b.name as branch_name', 'bb.status', 'bb.group', 'bb.rh');

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('status')) {
            $query->where('bb.status', $request->status);
        }

        if ($request->filled('group')) {
            $query->where('bb.group', $request->group);
        }

        if ($request->filled('rh')) {
            $query->where('bb.rh', $request->rh);
        }

        if ($request->filled('department_id')) {
            $query->where('bb.department_id', $request->department_id);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('bb.created_at', [$request->from, $request->to]);
        }

        $items = $query->get();
        return view('pages.blood_banks.reports.report', ['items' => $items]);
    }


    public function exportReport(Request $request)
    {

        $data = json_decode($request->data, true);

        $items = DB::table('blood_banks as bb')
            ->leftJoin('patients as p', 'bb.patient_id', '=', 'p.id')
            ->leftJoin('departments as d', 'bb.department_id', '=', 'd.id')
            ->leftJoin('branches as b', 'bb.branch_id', '=', 'b.id')
            ->select('bb.id', 'p.name as patient_name', 'd.name as department_name', 'b.name as branch_name', 'bb.status', 'bb.group', 'bb.rh')
            ->whereIn('bb.id', $data)->get();
        $reader = new Xlsx();
        $spreadsheet = $reader->load("report_templates/blood_bank_report.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        $html = view('pages.blood_banks.reports.pdf_report',  ['items' => $items])->render();
        if ($request->type == 'pdf') {
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('pdf_report.pdf', 'D');
        } else {
            $spreadsheet = $reader->load("report_templates/blood_bank_report.xlsx");
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
                $sheet->setCellValue('A' . $row . '', ++$index);
                $sheet->setCellValue('B' . $row . '', $item->patient_name);
                $sheet->setCellValue('C' . $row . '', $item->status);
                $sheet->setCellValue('D' . $row . '', $item->group);
                $sheet->setCellValue('E' . $row . '', $item->rh);
                $sheet->setCellValue('F' . $row . '', $item->department_name);

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
