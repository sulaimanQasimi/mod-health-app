<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewPrescriptionNotification;
use App\Models\Appointment;
use App\Models\Outcome;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Mpdf\Mpdf;
use Hekmatinasser\Verta\Verta;

class PrescriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Prescription::where('branch_id', $user->branch_id)
            ->with(['patient', 'doctor', 'appointment.department']);

        // Filter by user's pharmacy - only show prescriptions for user's pharmacy
        $userPharmacyIds = $user->activePharmacies()->pluck('pharmacies.id')->toArray();
        if (!empty($userPharmacyIds)) {
            $query->whereIn('pharmacy_id', $userPharmacyIds);
        } else {
            // If user has no pharmacy, return empty result
            $query->whereRaw('1 = 0');
        }

        // Search by patient name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('id_card', 'like', '%' . $search . '%');
            });
        }

        // Filter by token ID
        if ($request->filled('token_filter')) {
            $tokenFilter = $request->token_filter;
            $query->whereHas('appointment', function ($q) use ($tokenFilter) {
                $q->whereHas('patient', function ($patientQuery) use ($tokenFilter) {
                    $patientQuery->whereHas('printedNumbers', function ($tokenQuery) use ($tokenFilter) {
                        $tokenQuery->where('number', 'like', '%' . $tokenFilter . '%')
                                  ->whereColumn('printed_numbers.department_id', 'appointments.department_id')
                                  ->whereColumn('printed_numbers.date', 'appointments.date');
                    });
                });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_completed', $request->status);
        } else {
            // Default to show only not completed prescriptions
            $query->where('is_completed', false);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            // Convert Persian date to Gregorian
            $query->whereDate('created_at', '>=',Verta::parse($request->date_from)->datetime());
        }

        if ($request->filled('date_to')) {
            // Convert Persian date to Gregorian
            $query->whereDate('created_at', '<=', Verta::parse($request->date_to)->datetime());
        }

        $prescriptions = $query->latest()->paginate(10)->withQueryString();
        
        // Load token information for each prescription
        $prescriptions->getCollection()->transform(function ($prescription) {
            if ($prescription->appointment) {
                $token = \App\Models\PrintedNumber::where('patient_id', $prescription->patient_id)
                    ->where('department_id', $prescription->appointment->department_id)
                    ->whereDate('date', $prescription->appointment->date)
                    ->first();
                $prescription->token = $token;
            }
            return $prescription;
        });
        
        return view('pages.prescriptions.index', compact('prescriptions'));
    }

    /**
     * Display a listing of the resource.
     */
    public function delivered()
    {
        $user = auth()->user();
        
        $query = Prescription::where('branch_id', $user->branch_id)
            ->where('is_completed', true);

        // Filter by user's pharmacy - only show prescriptions for user's pharmacy
        $userPharmacyIds = $user->activePharmacies()->pluck('pharmacies.id')->toArray();
        if (!empty($userPharmacyIds)) {
            $query->whereIn('pharmacy_id', $userPharmacyIds);
        } else {
            // If user has no pharmacy, return empty result
            $query->whereRaw('1 = 0');
        }

        $prescriptions = $query->latest()->paginate(10);
        return view('pages.prescriptions.delivered', compact('prescriptions'));
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
            'under_review_id' => 'nullable',
            'hospitalization_id' => 'nullable',
            'i_c_u_id' => 'nullable',
            'doctor_id' => 'required',
            'is_completed' => 'nullable',
            'medicine_id' => 'required',
            'usage_type_id' => 'required',
            'dosage' => 'required',
            'frequency' => 'required',
            'amount' => 'required',
        ]);


        $medicineIds = $data['medicine_id'];
        $medicineUsageTypes = $data['usage_type_id'];
        $dosages = $data['dosage'];
        $frequencies = $data['frequency'];
        $amounts = $data['amount'];
        unset($data['medicine_id']);

        $prescription = Prescription::create($data);

        foreach ($medicineIds as $index => $medicineId) {
            $prescription_item_data = [
                'prescription_id' => $prescription->id,
                'medicine_id' => $medicineIds[$index],
                'usage_type_id' => $medicineUsageTypes[$index],
                'dosage' => $dosages[$index],
                'frequency' => $frequencies[$index],
                'amount' => $amounts[$index],
                'is_delivered' => '0',
            ];

            PrescriptionItem::create($prescription_item_data);
        }

        SendNewPrescriptionNotification::dispatch($prescription->created_by, $prescription->id);

        return redirect()->back()->with('success', localize('global.prescription_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Prescription $prescription)
    {
        return view('pages.prescriptions.show', compact('prescription'));
    }

    public function printCard($appointmentId, $prescriptionId)
    {
        $appointment = Appointment::findOrFail($appointmentId);
        $prescription = Prescription::findOrFail($prescriptionId);

        $prescriptionItems = PrescriptionItem::where('prescription_id', $prescriptionId)->get();

        $patient = $appointment->patient;

        return view('pages.prescriptions.print_card', compact('appointment', 'prescription', 'patient', 'prescriptionItems'));
    }

    public function printThermalReceipt(Prescription $prescription)
    {
        // Load prescription with all necessary relationships
        $prescription->load([
            'patient',
            'doctor', 
            'pharmacy',
            'prescriptionItems.medicine',
            'prescriptionItems.medicineType',
            'prescriptionItems.usageType',
            'prescriptionItems.selectedAlternative.medicine'
        ]);

        // Get pharmacy info (from prescription or user's active pharmacy)
        $pharmacy = $prescription->pharmacy ?? auth()->user()->activePharmacies()->first();
        
        // Get user info
        $user = auth()->user();

        return view('pages.prescriptions.thermal_receipt', compact('prescription', 'pharmacy', 'user'));
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

        // If status is being set to delivered (1), create Outcome records
        if ($updatedStatus === "1") {
        }

        // Return a response
        return response()->json(['status' => 'success']);
    }

    /**
     * Create Outcome records for prescription items and alternatives
     */
    private function createOutcomesForPrescription($prescription )
    {
        // Get prescription items
        $prescriptionItems = PrescriptionItem::where('prescription_id', $prescription->id)->get();

        foreach ($prescriptionItems as $prescriptionItem) {


            // Check if this item has a selected alternative
            $selectedAlternative = $prescriptionItem->selectedAlternative;

            if ($selectedAlternative) {

                // Create Outcome for the selected alternative medicine
                Outcome::create([
                    'medicine_id' => $selectedAlternative->medicine_id,
                    'amount' => $selectedAlternative->amount,
                    'prescription_item_id' => $prescriptionItem->id,
                    'patient_id' => $prescription->patient_id,
                    'doctor_id' => $prescription->doctor_id,
                    'pharmacy_id' => $prescription->pharmacy_id,
                    'outcome_type' => 'prescription',
                    'batch_number' => null, // You might want to get this from prescription stock
                    'reason' => 'Prescribed and delivered to patient',
                    'outcome_date' => now(),
                    'notes' => "Alternative medicine delivered for prescription item #{$prescriptionItem->id}"
                ]);
            } else {
                // Create Outcome for the original prescription item
                Outcome::create([
                    'medicine_id' => $prescriptionItem->medicine_id,
                    'amount' => $prescriptionItem->amount,
                    'prescription_item_id' => $prescriptionItem->id,
                    'patient_id' => $prescription->patient_id,
                    'doctor_id' => $prescription->doctor_id,
                    'pharmacy_id' => $prescription->pharmacy_id,
                    'outcome_type' => 'prescription',
                    'batch_number' => null, // You might want to get this from prescription stock
                    'reason' => 'Prescribed and delivered to patient',
                    'outcome_date' => now(),
                    'notes' => "Original medicine delivered for prescription item #{$prescriptionItem->id}"
                ]);
            }
        }
    }

    public function scanQrCode(Request $request)
    {
        // Get the scanned QR code data
        $qrCodeData = $request->input('qrCodeData');

        // Find the patient based on the QR code data
        $prescription = Prescription::where('id', $qrCodeData)->where('branch_id', auth()->user()->branch_id)->first();

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
        // Check if user belongs to a pharmacy
        $userPharmacy = auth()->user()->activePharmacies()->first();

        if (!$userPharmacy) {
            return redirect()->back()->with('error', localize('global.user_not_belong_to_pharmacy'));
        }

        // Validate the input
        $validatedData = $request->validate([
            'is_completed' => 'required',
            'pharmacy_id' => 'required|exists:pharmacies,id',
        ]);

        // Update the prescription
        $prescription->update($validatedData);
        $this->createOutcomesForPrescription($prescription);

        // Redirect to the prescriptions index page with a success message
        return redirect()->route('prescriptions.delivered')->with('success', localize('global.prescription_updated_successfully'));
    }

    public function report()
    {
        return view('pages.prescriptions.reports.index');
    }
    public function reportSearch(Request $request)
    {
        $query = DB::table('prescriptions as a')
            ->leftJoin('patients as p', 'a.patient_id', '=', 'p.id')
            ->leftJoin('doctors as d', 'a.doctor_id', '=', 'd.id')
            ->leftJoin('branches as b', 'a.branch_id', '=', 'b.id')
            ->select('a.id', 'p.name as patient_name', 'd.name as doctor_name', 'b.name as branch_name', 'a.is_completed');

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('is_completed')) {
            $query->where('a.is_completed', $request->is_completed);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $fromDate = Verta::parse($request->from)->datetime();
            $toDate = Verta::parse($request->to)->datetime();
            $query->whereDate('a.created_at', '>=', $fromDate)
                  ->whereDate('a.created_at', '<=', $toDate);
        }

        $items = $query->get();
        return view('pages.prescriptions.reports.report', ['items' => $items]);
    }


    /**
     * Write HTML to mPDF in chunks to avoid pcre.backtrack_limit error
     */
    private function writeHtmlInChunks($mpdf, $items, $departmentCounts = [])
    {
        // Ensure items is a collection
        if (!($items instanceof \Illuminate\Support\Collection)) {
            $items = collect($items);
        }
        
        // Write HTML header and opening tags with department counts
        $header = view('pages.prescriptions.reports.pdf_report_header', [
            'departmentCounts' => $departmentCounts
        ])->render();
        $mpdf->WriteHTML($header);
        
        // Write table rows in chunks (50 rows at a time)
        $chunkSize = 50;
        $chunks = $items->chunk($chunkSize);
        $rowNumber = 0;
        
        foreach ($chunks as $chunk) {
            $rowsHtml = view('pages.prescriptions.reports.pdf_report_rows', [
                'items' => $chunk,
                'startIndex' => $rowNumber
            ])->render();
            $mpdf->WriteHTML($rowsHtml);
            $rowNumber += $chunk->count();
        }
        
        // Write closing tags
        $footer = view('pages.prescriptions.reports.pdf_report_footer')->render();
        $mpdf->WriteHTML($footer);
    }

    public function exportReport(Request $request)
    {

        $data = json_decode($request->data, true);

        $items = DB::table('prescriptions as a')
            ->leftJoin('patients as p', 'a.patient_id', '=', 'p.id')
            ->leftJoin('doctors as d', 'a.doctor_id', '=', 'd.id')
            ->leftJoin('branches as b', 'a.branch_id', '=', 'b.id')
            ->leftJoin('appointments as app', 'a.appointment_id', '=', 'app.id')
            ->leftJoin('departments as dept', 'app.department_id', '=', 'dept.id')
            ->select('a.id', 'p.name as patient_name', 'd.name as doctor_name', 'b.name as branch_name', 'a.is_completed', 'dept.name as department_name', 'dept.id as department_id')
            ->whereIn('a.id', $data)->get();
        
        // Calculate department counts
        $departmentCounts = $items->groupBy(function ($item) {
            return $item->department_id ?? 'unknown';
        })->map(function ($group) {
            return [
                'name' => $group->first()->department_name ?? 'Unknown',
                'count' => $group->count()
            ];
        })->values()->toArray();
        
        $reader = new Xlsx();
        $spreadsheet = $reader->load("report_templates/prescription_report.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        if ($request->type == 'pdf') {
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $this->writeHtmlInChunks($mpdf, $items, $departmentCounts);
            $mpdf->Output('pdf_report.pdf', 'D');
        } else {
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

    /**
     * Export prescriptions with filters and selected items
     */
    public function exportPrescriptions(Request $request)
    {
        try {
            $query = Prescription::where('branch_id', auth()->user()->branch_id)
                ->with(['patient', 'doctor', 'appointment.doctor', 'appointment.department']);

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('patient', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%')
                      ->orWhere('id_card', 'like', '%' . $search . '%');
                });
            }

            if ($request->filled('token_filter')) {
                $tokenFilter = $request->token_filter;
                $query->whereHas('appointment', function ($q) use ($tokenFilter) {
                    $q->whereHas('patient', function ($patientQuery) use ($tokenFilter) {
                        $patientQuery->whereHas('printedNumbers', function ($tokenQuery) use ($tokenFilter) {
                            $tokenQuery->where('number', 'like', '%' . $tokenFilter . '%')
                                      ->whereColumn('printed_numbers.department_id', 'appointments.department_id')
                                      ->whereColumn('printed_numbers.date', 'appointments.date');
                        });
                    });
                });
            }

            if ($request->filled('status')) {
                $query->where('is_completed', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', \Hekmatinasser\Verta\Verta::parse($request->date_from)->datetime());
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', \Hekmatinasser\Verta\Verta::parse($request->date_to)->datetime());
            }

            // Filter by doctor_id: use prescription.doctor_id if set, otherwise appointment.doctor_id
            if ($request->filled('doctor_id')) {
                $query->whereRaw(
                    'COALESCE(prescriptions.doctor_id, (SELECT doctor_id FROM appointments WHERE appointments.id = prescriptions.appointment_id LIMIT 1)) = ?',
                    [$request->doctor_id]
                );
            }

            // If specific items are selected, filter by those IDs
            if ($request->filled('selected') && is_array($request->selected)) {
                $query->whereIn('id', $request->selected);
            }

            // Order by resolved doctor_id (from prescription or appointment)
            $query->orderByRaw(
                'COALESCE(prescriptions.doctor_id, (SELECT doctor_id FROM appointments WHERE appointments.id = prescriptions.appointment_id LIMIT 1)) ASC'
            )->orderBy('prescriptions.created_at', 'desc');

            $prescriptions = $query->get();

            // Transform data for export (doctor: prescription.doctor_id or appointment.doctor_id)
            $items = $prescriptions->map(function ($prescription) {
                return (object) [
                    'id' => $prescription->id,
                    'patient_name' => $prescription->patient->name ?? '-',
                    'patient_id_card' => $prescription->patient->id_card ?? '-',
                    'father_name' => $prescription->patient->father_name ?? '-',
                    'doctor_name' => $prescription->doctor?->name ?? $prescription->appointment?->doctor?->name ?? '-',
                    'is_completed' => $prescription->is_completed ? '1' : '0',
                    'created_at' => $prescription->created_at,
                    'department_name' => $prescription->appointment->department->name ?? 'Unknown',
                    'department_id' => $prescription->appointment->department_id ?? null,
                ];
            });

            // Calculate department counts
            $departmentCounts = $items->groupBy(function ($item) {
                return $item->department_id ?? 'unknown';
            })->map(function ($group) {
                return [
                    'name' => $group->first()->department_name ?? 'Unknown',
                    'count' => $group->count()
                ];
            })->values()->toArray();

            $format = $request->get('format', 'excel');
            
            if ($format === 'pdf') {
                $mpdf = new Mpdf(['format' => 'A4-L']);
                $this->writeHtmlInChunks($mpdf, $items, $departmentCounts);
                $mpdf->Output('prescriptions_report.pdf', 'D');
            } else {
                $reader = new Xlsx();
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

                    $status = $item->is_completed == '1' ? 'نسخه های اجرأ شده' : 'نسخه های نا اجرأ';
                    
                    $sheet->setCellValue('A' . $row . '', ++$index);
                    $sheet->setCellValue('B' . $row . '', $item->patient_name);
                    $sheet->setCellValue('C' . $row . '', $item->doctor_name);
                    $sheet->setCellValue('D' . $row . '', $item->patient_id_card);
                    $sheet->setCellValue('E' . $row . '', $status);

                    $row++;
                }

                return $this->exportResponse($spreadsheet);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_export_prescriptions'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update prescription status
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'prescription_ids' => 'required|array|min:1',
                'prescription_ids.*' => 'exists:prescriptions,id',
                'is_completed' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => localize('global.validation_failed'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $prescriptionIds = $request->prescription_ids;
            $isCompleted = $request->is_completed;

            // Update prescriptions
            Prescription::whereIn('id', $prescriptionIds)
                ->where('branch_id', auth()->user()->branch_id)
                ->update(['is_completed' => $isCompleted]);

            return response()->json([
                'success' => true,
                'message' => localize('global.bulk_status_updated_successfully'),
                'updated_count' => count($prescriptionIds)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_update_bulk_status'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete prescriptions
     */
    public function bulkDelete(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'prescription_ids' => 'required|array|min:1',
                'prescription_ids.*' => 'exists:prescriptions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => localize('global.validation_failed'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $prescriptionIds = $request->prescription_ids;

            // Delete prescriptions
            $deletedCount = Prescription::whereIn('id', $prescriptionIds)
                ->where('branch_id', auth()->user()->branch_id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => localize('global.bulk_delete_successful'),
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_bulk_delete'),
                'error' => $e->getMessage()
            ], 500);
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
