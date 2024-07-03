<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewPrescriptionNotification;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Mpdf\Mpdf;
class PrescriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $prescriptions = Prescription::where('branch_id',auth()->user()->branch_id)->where('is_completed',false)->latest()->paginate(10);
        return view('pages.prescriptions.index',compact('prescriptions'));
    }

    /**
     * Display a listing of the resource.
     */
    public function delivered()
    {
        $prescriptions = Prescription::where('branch_id',auth()->user()->branch_id)->where('is_completed',true)->latest()->paginate(10);
        return view('pages.prescriptions.delivered',compact('prescriptions'));
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

        $data = $request->validate([
            'appointment_id' => 'required',
            'patient_id' => 'required',
            'branch_id' => 'required',
            'doctor_id' => 'required',
            'is_completed' => 'nullable',
            'medicine_type_id' => 'required',
            'medicine_id' => 'required',
            'dosage' => 'nullable',
            'frequency' => 'nullable',
            'amount' => 'nullable',
        ]);

    
        $medicineIds = $data['medicine_id'];
        $medicineTypeIds = $data['medicine_type_id'];
        $dosages = $data['dosage'];
        $frequencies = $data['frequency'];
        $amounts = $data['amount'];
        unset($data['medicine_id']);
    
        $prescription = Prescription::create($data);
    
        foreach ($medicineIds as $index => $medicineId) {
            $prescription_item_data = [
                'prescription_id' => $prescription->id,
                'medicine_id' => $medicineIds[$index],
                'medicine_type_id' => $medicineTypeIds[$index],
                'dosage' => $dosages[$index],
                'frequency' => $frequencies[$index],
                'amount' => $amounts[$index],
                'is_delivered' => '0',
            ];

            PrescriptionItem::create($prescription_item_data);
        }
    
        SendNewPrescriptionNotification::dispatch($prescription->created_by, $prescription->id);
    
        return redirect()->back()->with('success', 'Prescription created successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show(Prescription $prescription)
    {
        return view('pages.prescriptions.show',compact('prescription'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prescription $prescription)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prescription $prescription)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prescription $prescription)
    {
        //
    }

    public function printCard($appointmentId, $prescriptionId)
    {
        $appointment = Appointment::findOrFail($appointmentId);
        $prescription = Prescription::findOrFail($prescriptionId);

        $prescriptionItems = PrescriptionItem::where('prescription_id', $prescriptionId)->get();

        $patient = $appointment->patient;

        return view('pages.prescriptions.print_card', compact('appointment','prescription','patient','prescriptionItems'));
    }

    public function updateStatus($prescriptionId, $key)
    {
        // Find the prescription by ID
        $prescription = Prescription::findOrFail($prescriptionId);

        // Update the status of the specified key
        $statuses = is_array($prescription->is_delivered) ? $prescription->is_delivered : json_decode($prescription->is_delivered, true);
        $updatedStatus = $statuses[$key] === "0" ? "1" : "0";
        $statuses[$key] = $updatedStatus;
        $prescription->is_delivered = json_encode($statuses);
        $prescription->save();

        // Return a response
        return response()->json(['status' => 'success']);
    }

    public function scanQrCode(Request $request)
    {
        // Get the scanned QR code data
        $qrCodeData = $request->input('qrCodeData');

        // Find the patient based on the QR code data
        $prescription = Prescription::where('id', $qrCodeData)->where('branch_id',auth()->user()->branch_id)->first();

        if ($prescription) {
            // Redirect to the patient's show page
            return redirect()->route('prescriptions.show', $prescription->id);
        } else {
            // Handle the case when the patient is not found
            return redirect()->back()->with('error', localize('global.prescription_not_found'));
        }
    }

    public function scanCode()
    {
        return view('pages.prescriptions.scan');
    }

    public function changeStatus(Request $request, Prescription $prescription)
    {
        // Validate the input
        $validatedData = $request->validate([
            'is_completed' => 'required',
        ]);

        // Update the prescription
        $prescription->update($validatedData);

        // Redirect to the prescriptions index page with a success message
        return redirect()->route('prescriptions.delivered')->with('success', 'prescription updated successfully.');
    }

    public function report()
    {
        return view('pages.prescriptions.reports.index');
    }
    public function reportSearch(Request $request)
    {
        $query = DB::table('prescriptions as a')
        ->leftJoin('patients as p', 'a.patient_id' , '=', 'p.id')
        ->leftJoin('doctors as d', 'a.doctor_id' , '=', 'd.id')
        ->leftJoin('branches as b', 'a.branch_id' , '=', 'b.id')
        ->select('a.id','p.name as patient_name', 'd.name as doctor_name','b.name as branch_name','a.is_completed');

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('is_completed')) {
            $query->where('a.is_completed', $request->is_completed);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('a.created_at', [$request->from, $request->to]);
        }

        $items = $query->get();
    return view('pages.prescriptions.reports.report', ['items' => $items]);

    }

    
    public function exportReport(Request $request)
    {

        $data = json_decode($request->data, true);
      
        $items = DB::table('prescriptions as a')
        ->leftJoin('patients as p', 'a.patient_id' , '=', 'p.id')
        ->leftJoin('doctors as d', 'a.doctor_id' , '=', 'd.id')
        ->leftJoin('branches as b', 'a.branch_id' , '=', 'b.id')
        ->select('a.id','p.name as patient_name', 'd.name as doctor_name','b.name as branch_name','a.is_completed')
        ->whereIn('a.id', $data)->get();
        $reader = new Xlsx();
        $spreadsheet = $reader->load("report_templates/prescription_report.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        $html = view('pages.prescriptions.reports.pdf_report',  ['items' => $items])->render();
        if ($request->type == 'pdf') {
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('pdf_report.pdf', 'D');
        }else {
            $spreadsheet = $reader->load("report_templates/prescription_report.xlsx");
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
                if ($item->is_completed == '0') {
                    $status = 'نسخه های نا اجرأ';
                } else {
                    $status = 'نسخه های اجرأ شده';
                }
                    $sheet->setCellValue('A' . $row . '', ++$index);
                    $sheet->setCellValue('B' . $row . '', $item->patient_name);
                    $sheet->setCellValue('C' . $row . '', $item->doctor_name);
                    $sheet->setCellValue('D' . $row . '', $item->branch_name);
                    $sheet->setCellValue('E' . $row . '', $status);
                    
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
