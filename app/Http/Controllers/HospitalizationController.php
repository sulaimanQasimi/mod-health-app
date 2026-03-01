<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewHospitalizationNotification;
use App\Models\Bed;
use App\Models\FoodType;
use App\Models\Hospitalization;
use App\Models\ICU;
use App\Models\LabType;
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\MedicineUsageType;
use App\Models\OperationType;
use App\Models\Relation;
use App\Models\Room;
use App\Models\User;
use App\Models\Doctor;
use App\Models\DiabetesChart;
use App\Models\Nurse;
use App\Models\NurseNote;
use App\Models\MedicationAdministrationRecord;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use HanifHefaz\Dcter\Dcter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Mpdf\Mpdf;

class HospitalizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        
        // Base query with eager loading and select optimization
        $query = Hospitalization::select([
            'hospitalizations.id',
            'hospitalizations.patient_id',
            'hospitalizations.room_id',
            'hospitalizations.bed_id',
            'hospitalizations.doctor_id',
            'hospitalizations.reason',
            'hospitalizations.created_at',
        ])
            ->where('hospitalizations.branch_id', $branchId)
            ->where('hospitalizations.is_discharged', '0')
            ->with([
                'patient:id,name,id_card,father_name',
                'room:id,name',
                'bed:id,number',
                'doctor:id,name'
            ]);

        // Optimized search using joins instead of whereHas for better performance
        if ($request->filled('search')) {
            $search = $request->search;
            $query->leftJoin('patients', 'hospitalizations.patient_id', '=', 'patients.id')
                ->leftJoin('rooms', 'hospitalizations.room_id', '=', 'rooms.id')
                ->leftJoin('beds', 'hospitalizations.bed_id', '=', 'beds.id')
                ->leftJoin('doctors', 'hospitalizations.doctor_id', '=', 'doctors.id')
                ->where(function($q) use ($search) {
                    $q->where('patients.name', 'like', "%{$search}%")
                      ->orWhere('patients.id_card', 'like', "%{$search}%")
                      ->orWhere('patients.father_name', 'like', "%{$search}%")
                      ->orWhere('rooms.name', 'like', "%{$search}%")
                      ->orWhere('beds.number', 'like', "%{$search}%")
                      ->orWhere('doctors.name', 'like', "%{$search}%")
                      ->orWhere('hospitalizations.reason', 'like', "%{$search}%");
                })
                ->groupBy('hospitalizations.id');
        }

        // Room filter
        if ($request->filled('room_id')) {
            $query->where('hospitalizations.room_id', $request->room_id);
        }

        // Date from filter (Jalali/Dari from datepicker_dari, parsed with Verta)
        if ($request->filled('date_from')) {
            $query->whereDate('hospitalizations.created_at', '>=', Verta::parse($request->date_from)->datetime());
        }

        // Date to filter (Jalali/Dari from datepicker_dari, parsed with Verta)
        if ($request->filled('date_to')) {
            $query->whereDate('hospitalizations.created_at', '<=', Verta::parse($request->date_to)->datetime());
        }

        // Use Laravel's default pagination
        $hospitalizations = $query->orderBy('hospitalizations.id', 'desc')
            ->paginate(25)
            ->withQueryString();

        // Transform data with jalali date
        $hospitalizations->getCollection()->transform(function ($hospitalization) {
            $hospitalization->jalali_date = $hospitalization->created_at 
                ? \HanifHefaz\Dcter\Dcter::GregorianToJalali($hospitalization->created_at->format('Y-m-d'))
                : 'Not set';
            return $hospitalization;
        });

        // Get filter options - optimized with select
        $rooms = \App\Models\Room::select('id', 'name')
            // ->where('branch_id', $branchId)
            ->orderBy('name')
            ->get();
        
        return view('pages.hospitalizations.index', compact('hospitalizations', 'rooms'));
    }
    
    public function discharged(Request $request)
    {
        if ($request->ajax()) {
            $hospitalizations = Hospitalization::where('branch_id', auth()->user()->branch_id)
                ->where('is_discharged', '1')
                ->with(['patient', 'room', 'bed', 'doctor'])
                ->get()
                ->map(function ($hospitalization) {

                    if ($hospitalization->created_at) {
                        $hospitalization->jalali_date = Dcter::GregorianToJalali($hospitalization->created_at->format('Y-m-d'));
                    } else {
                        $hospitalization->jalali_date = 'Not set';
                    }
    
                    if ($hospitalization->discharged_at) {
                        $hospitalization->jalali_discharged_at = Dcter::GregorianToJalali(Dcter::Carbonize($hospitalization->discharged_at)->format('Y-m-d'));
                    } else {
                        $hospitalization->jalali_discharged_at = 'Not set';
                    }
    
                    return $hospitalization;
                });
    
            return response()->json([
                'data' => $hospitalizations,
            ]);
        }
    
        // For non-AJAX requests
        $hospitalizations = Hospitalization::where('branch_id', auth()->user()->branch_id)
            ->where('is_discharged', '1')
            ->with(['patient', 'room', 'bed', 'doctor'])
            ->get()
            ->map(function ($hospitalization) {

                if ($hospitalization->created_at) {
                    $hospitalization->jalali_date = Dcter::GregorianToJalali($hospitalization->created_at->format('Y-m-d'));
                } else {
                    $hospitalization->jalali_date = 'Not set';
                }
    
                if ($hospitalization->discharged_at) {
                    $hospitalization->jalali_discharged_at = Dcter::GregorianToJalali(Dcter::Carbonize($hospitalization->discharged_at)->format('Y-m-d'));
                } else {
                    $hospitalization->jalali_discharged_at = 'Not set';
                }
    
                return $hospitalization;
            });
    
        return view('pages.hospitalizations.discharged', compact('hospitalizations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'reason' => 'required',
            'remarks' => 'required',
            'room_id' => 'required',
            'patient_id' => 'required',
            'doctor_id' => 'nullable|exists:doctors,id',
            'bed_id' => 'required',
            'appointment_id' => 'required',
            'is_discharged' => 'nullable',
            'discharge_remark' => 'nullable',
            'branch_id' => 'required',
            'discharge_status' => 'nullable',
            'food_type_id' => 'nullable',
            'patinet_companion' => 'nullable',
            'companion_father_name' => 'nullable',
            'relation_to_patient' => 'nullable',
            'companion_card_type' => 'nullable',
            'discharged_at' => 'nullable',
            'under_review_id' => 'nullable',
            'i_c_u_id' => 'nullable',
        ]);

        // If doctor_id is not provided or doesn't exist, try to get it from appointment
        if (empty($data['doctor_id'])) {
            $appointment = \App\Models\Appointment::find($data['appointment_id']);
            if ($appointment && $appointment->doctor_id) {
                // Verify the doctor exists in doctors table
                if (\App\Models\Doctor::where('id', $appointment->doctor_id)->exists()) {
                    $data['doctor_id'] = $appointment->doctor_id;
                }
            }
        }

        // If still no doctor_id, try to get from authenticated user's doctor relationship
        if (empty($data['doctor_id']) && auth()->user()) {
            $user = auth()->user();
            if ($user->doctor) {
                $data['doctor_id'] = $user->doctor->id;
            }
        }

        // If still no valid doctor_id, set to null (since it's nullable)
        if (!empty($data['doctor_id']) && !\App\Models\Doctor::where('id', $data['doctor_id'])->exists()) {
            $data['doctor_id'] = null;
        }

        $data['food_type_id'] = json_encode($data['food_type_id']);

        $hospitalization = Hospitalization::create($data);

        $occupied_bed = Bed::findOrFail($data['bed_id']);

        $occupied_bed->update(['is_occupied' => true]);
        $occupied_bed->save();

        if (!empty($data['i_c_u_id'])) {
            ICU::where('id', $data['i_c_u_id'])->update(['hospitalization_id' => $hospitalization->id]);
        }

        SendNewHospitalizationNotification::dispatch($hospitalization->created_by, $hospitalization->id);

        return redirect()->back()->with('success', localize('global.hospitalization_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Hospitalization $hospitalization, Request $request)
    {
        // Load only essential data for the main page - heavy data is now loaded via AJAX
        $operationTypes = OperationType::where('branch_id', auth()->user()->branch_id)->get();
        $labTypes = LabType::all();
        // Doctors will be loaded via API, no need to pass them here
        // $operation_doctors = Doctor::where('branch_id', auth()->user()->branch_id)
        //     ->where('active_status', true)
        //     ->get();
        $medicineTypes = MedicineType::all();
        $medicines = Medicine::all();
        $foodTypes = FoodType::all();
        $medicineUsageTypes = MedicineUsageType::all();

        // Load only basic hospitalization data with essential relationships
        $hospitalization->load(['patient', 'doctor', 'room', 'bed', 'appointment']);

        // Load current user's nurse relationship for auto-selection
        $currentUser = auth()->user()->load('nurse');

        return view('pages.hospitalizations.show', compact(
            'hospitalization', 
            'operationTypes', 
            'labTypes', 
            'medicineTypes', 
            'medicines', 
            'foodTypes', 
            'medicineUsageTypes', 
            'currentUser'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hospitalization $hospitalization)
    {
        $rooms = Room::all();
        $beds = Bed::all();
        $foodTypes = FoodType::all();
        $relations = Relation::all();
        return view('pages.hospitalizations.edit',compact('hospitalization','rooms','beds','foodTypes','relations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hospitalization $hospitalization)
    {
        $data = $request->validate([
            'is_discharged' => 'required',
            'discharge_remark' => 'required',
            'discharge_status' => 'required',
            'discharged_at' => 'required',
        ]);

        $hospitalization->update($data);

        $occupied_bed = Bed::findOrFail($hospitalization->bed_id);
        $occupied_bed->update(['is_occupied' => false]);
        $occupied_bed->save();

        return redirect()->route('hospitalizations.show', $hospitalization)->with('success', localize('global.hospitalization_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hospitalization $hospitalization)
    {
        
        $hospitalization->delete();

        return redirect()->back()->with('success', localize('global.hospitalization_deleted_successfully.') );
    }

    public function report()
    {
        $foodTypes = FoodType::all();

        return view('pages.hospitalizations.reports.index', compact('foodTypes'));
    }
    public function reportSearch(Request $request)
    {
        $food_type_ids = DB::table('food_types')->pluck('id')->toArray();
        $query = DB::table('hospitalizations as h')
            ->leftJoin('patients as p', 'h.patient_id', '=', 'p.id')
            ->leftJoin('branches as b', 'h.branch_id', '=', 'b.id')
            ->leftJoin('doctors as d', 'h.doctor_id', '=', 'd.id')
            ->leftJoin('food_types as f', function ($join) use ($food_type_ids) {
                $join->on('h.food_type_id', 'like', DB::raw('concat("%", f.id, "%")'));
            })
            ->select('h.id', 'p.name as patient_name', 'd.name as doctor_name', 'b.name as branch_name', 'h.companion_card_type', 'h.discharge_status', 'f.name as food_type_name');

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('food_type_id')) {
            $foodTypeIds = [$request->food_type_id];
            $query->where(function ($query) use ($foodTypeIds) {
                foreach ($foodTypeIds as $foodTypeId) {
                    $query->orWhere('h.food_type_id', 'like', '%' . $foodTypeId . '%');
                }
            });
        }

        if ($request->filled('companion_card_type')) {
            $query->where('h.companion_card_type', $request->companion_card_type);
        }

        if ($request->filled('discharge_status')) {
            $query->where('h.discharge_status', $request->discharge_status);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('h.created_at', [$request->from, $request->to]);
        }

        $items = $query->get();
        return view('pages.hospitalizations.reports.report', ['items' => $items]);
    }

    public function exportReport(Request $request)
    {
        $data = json_decode($request->data, true);
        $food_type_ids = DB::table('food_types')->pluck('id')->toArray();
        $items = DB::table('hospitalizations as h')
            ->leftJoin('patients as p', 'h.patient_id', '=', 'p.id')
            ->leftJoin('branches as b', 'h.branch_id', '=', 'b.id')
            ->leftJoin('doctors as d', 'h.doctor_id', '=', 'd.id')
            ->leftJoin('food_types as f', function ($join) use ($food_type_ids) {
                $join->on('h.food_type_id', '=', 'f.id')->whereIn('f.id', $food_type_ids);
            })
            ->select('h.id', 'p.name as patient_name', 'd.name as doctor_name', 'b.name as branch_name', 'h.companion_card_type', 'h.discharge_status', 'f.name as food_type_name')
            ->whereIn('h.id', $data)
            ->get();
        $reader = new Xlsx();
        $spreadsheet = $reader->load('report_templates/hospitalizations_report.xlsx');
        $sheet = $spreadsheet->getActiveSheet();
        $html = view('pages.hospitalizations.reports.pdf_report', ['items' => $items])->render();
        if ($request->type == 'pdf') {
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('pdf_report.pdf', 'D');
        } else {
            $spreadsheet = $reader->load('report_templates/hospitalizations_report.xlsx');
            $sheet = $spreadsheet->getActiveSheet();
            $row = 3;

            foreach ($items as $index => $item) {
                $sheet
                    ->getStyle('A2:G' . $sheet->getHighestRow())
                    ->getAlignment()
                    ->setWrapText(true);
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(20);
                $styleArray = [
                    'font' => [
                        'name' => 'B Nazanin',
                        'color' => 15,
                        'bold' => true,
                    ],
                ];
                $sheet->setCellValue('A' . $row . '', ++$index);
                $sheet->setCellValue('B' . $row . '', $item->patient_name);
                $sheet->setCellValue('C' . $row . '', $item->food_type_name);
                $sheet->setCellValue('D' . $row . '', $item->companion_card_type);
                $sheet->setCellValue('E' . $row . '', $item->discharge_status);
                $sheet->setCellValue('F' . $row . '', $item->doctor_name);
                $sheet->setCellValue('G' . $row . '', $item->branch_name);

                $row++;
            }

            return $this->exportResponse($spreadsheet);
        }
    }

    public function exportResponse($spreadsheet)
    {
        $writer = new WriterXlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="item_report.xls"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }

    public function updateHospitalization(Request $request, $id)
{
    // Validate incoming request
    $data = $request->validate([
        'reason' => 'nullable',
        'remarks' => 'nullable',
        'room_id' => 'nullable',
        'patient_id' => 'nullable',
        'doctor_id' => 'nullable|exists:doctors,id',
        'bed_id' => 'nullable',
        'appointment_id' => 'nullable',
        'is_discharged' => 'nullable',
        'discharge_remark' => 'nullable',
        'branch_id' => 'nullable',
        'discharge_status' => 'nullable',
        'food_type_id' => 'nullable',
        'patinet_companion' => 'nullable',
        'companion_father_name' => 'nullable',
        'relation_to_patient' => 'nullable',
        'companion_card_type' => 'nullable',
        'discharged_at' => 'nullable',
        'under_review_id' => 'nullable',
        'i_c_u_id' => 'nullable',
    ]);

    // If doctor_id is not provided or doesn't exist, try to get it from appointment
    if (empty($data['doctor_id'])) {
        $appointment = \App\Models\Appointment::find($data['appointment_id']);
        if ($appointment && $appointment->doctor_id) {
            // Verify the doctor exists in doctors table
            if (\App\Models\Doctor::where('id', $appointment->doctor_id)->exists()) {
                $data['doctor_id'] = $appointment->doctor_id;
            }
        }
    }

    // If still no doctor_id, try to get from authenticated user's doctor relationship
    if (empty($data['doctor_id']) && auth()->user()) {
        $user = auth()->user();
        if ($user->doctor) {
            $data['doctor_id'] = $user->doctor->id;
        }
    }

    // If still no valid doctor_id, set to null (since it's nullable)
    if (!empty($data['doctor_id']) && !\App\Models\Doctor::where('id', $data['doctor_id'])->exists()) {
        $data['doctor_id'] = null;
    }

    // Convert food_type_id to JSON if it exists
    if (isset($data['food_type_id'])) {
        $data['food_type_id'] = json_encode($data['food_type_id']);
    }

    // Find the existing hospitalization record
    $hospitalization = Hospitalization::findOrFail($id);

    // Update the record with validated data
    $hospitalization->update($data);

    // Check and update bed occupancy status
    $occupied_bed = Bed::findOrFail($data['bed_id']);
    $occupied_bed->update(['is_occupied' => true]);

    // Optionally, you can dispatch a notification if needed
    // SendUpdateHospitalizationNotification::dispatch($hospitalization->created_by, $hospitalization->id);

    return redirect()->route('appointments.index')->with('success', localize('global.hospitalization_updated_successfully.'));
}

    /**
     * Get diabetes charts section for AJAX loading
     */
    public function diabetesChartsSection(Request $request)
    {
        $morphableType = $request->morphable_type;
        $morphableId = $request->morphable_id;
        $morphModel = null;
        
        // Load diabetes charts for this hospitalization
        $diabetesChartsQuery = DiabetesChart::where('diabetes_chartable_type', $morphableType)
            ->where('diabetes_chartable_id', $morphableId)
            ->with(['nurse', 'medicine']);

        // Search functionality for diabetes charts
        if ($request->filled('search')) {
            $search = $request->search;
            $diabetesChartsQuery->where(function ($q) use ($search) {
                $q->where('blood_sugar_level', 'like', "%{$search}%")
                  ->orWhere('insulin_dose', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('nurse', function ($nurseQuery) use ($search) {
                      $nurseQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('medicine', function ($medicineQuery) use ($search) {
                      $medicineQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $diabetesChartsQuery->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $diabetesChartsQuery->where('date', '<=', $request->end_date);
        }

        // Filter by nurse
        if ($request->filled('nurse_id')) {
            $diabetesChartsQuery->where('nurse_id', $request->nurse_id);
        }

        $diabetesCharts = $diabetesChartsQuery->orderBy('date', 'desc')
                                             ->orderBy('time', 'desc')
                                             ->get();
        
        $morphModel = $morphableType::find($morphableId);
        
        return view('pages.diabetes-charts.partials.section', compact('diabetesCharts', 'morphableType', 'morphableId', 'morphModel'));
    }

    /**
     * Get medication administration records section for AJAX loading
     */
    public function medicationAdministrationRecordsSection(Request $request)
    {
        $morphableType = $request->morphable_type;
        $morphableId = $request->morphable_id;
        $morphModel = null;
        
        // Load medication administration records for this hospitalization
        $medicationAdministrationRecordsQuery = MedicationAdministrationRecord::where('morphable_type', $morphableType)
            ->where('morphable_id', $morphableId)
            ->with(['medicine', 'nurse', 'administrationTimes', 'createdBy']);

        // Search functionality for MARs
        if ($request->filled('search')) {
            $search = $request->search;
            $medicationAdministrationRecordsQuery->where(function ($q) use ($search) {
                $q->whereHas('medicine', function ($medicineQuery) use ($search) {
                    $medicineQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('nurse', function ($nurseQuery) use ($search) {
                    $nurseQuery->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $medicationAdministrationRecordsQuery->where('order_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $medicationAdministrationRecordsQuery->where('order_date', '<=', $request->end_date);
        }

        // Filter by nurse
        if ($request->filled('nurse_id')) {
            $medicationAdministrationRecordsQuery->where('nurse_id', $request->nurse_id);
        }

        // Filter by medicine
        if ($request->filled('medicine_id')) {
            $medicationAdministrationRecordsQuery->where('medicine_id', $request->medicine_id);
        }

        $medicationAdministrationRecords = $medicationAdministrationRecordsQuery->orderBy('order_date', 'desc')
                                                                               ->orderBy('created_at', 'desc')
                                                                               ->get();
        
        $morphModel = $morphableType::find($morphableId);
        
        return view('pages.medication-administration-records.partials.section', compact('medicationAdministrationRecords', 'morphableType', 'morphableId', 'morphModel'));
    }

    /**
     * Get vital signs section for AJAX loading
     */
    public function vitalSignsSection(Request $request)
    {
        $morphableType = $request->morphable_type;
        $morphableId = $request->morphable_id;
        $morphModel = null;
        
        // Load vital signs for this hospitalization
        $morphModel = $morphableType::find($morphableId);
        $morphModel->load(['vitalSigns.vitalSignType', 'vitalSigns.schedules.nurse']);
        
        return view('pages.vital-signs.partials.section', compact('morphableType', 'morphableId', 'morphModel'));
    }

    /**
     * Get nutrition care section for AJAX loading
     */
    public function nutritionCareSection(Request $request)
    {
        $morphableType = $request->morphable_type;
        $morphableId = $request->morphable_id;
        $morphModel = null;
        
        // Load nutrition cares for this hospitalization
        $morphModel = $morphableType::find($morphableId);
        $morphModel->load(['nutritionCares.createdBy', 'nutritionCares.updatedBy', 'nutritionCares.nurse']);
        
        return view('pages.nutrition-cares.partials.section', compact('morphableType', 'morphableId', 'morphModel'));
    }

    /**
     * Assign a doctor to the hospitalization based on appointment's department
     */
    public function assignDoctor(Request $request, Hospitalization $hospitalization)
    {
        // Validate doctor_id is provided
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id'
        ]);
$hospitalization->appointment->update([
    'doctor_id' => $request->doctor_id
]);
        // Update hospitalization with doctor
        $hospitalization->update([
            'doctor_id' => $request->doctor_id
        ]);

        // Return JSON response for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => localize('global.doctor_assigned_successfully')
            ]);
        }

        return redirect()->back()->with('success', localize('global.doctor_assigned_successfully'));
    }

    /**
     * Show the form for changing room and bed.
     */
    public function changeRoomBed(Hospitalization $hospitalization)
    {
        // Check permission
        if (!auth()->user()->hasPermissionTo('edit-hospitalizations')) {
            abort(403, 'Unauthorized action.');
        }

        // Load essential relationships
        $hospitalization->load(['patient', 'room', 'bed']);
        
        // Get rooms for the current branch
        $rooms = Room::select('id', 'name')
            ->orderBy('name')
            ->get();
        
        return view('pages.hospitalizations.change-room-bed', compact('hospitalization', 'rooms'));
    }

    /**
     * Update room and bed for hospitalization.
     */
    public function updateRoomBed(Request $request, Hospitalization $hospitalization)
    {
        // Check permission
        if (!auth()->user()->hasPermissionTo('edit-hospitalizations')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'bed_id' => 'required|exists:beds,id',
        ]);
        $returnToRoomId = $request->input('return_to_room_id');
        if ($returnToRoomId === null || $returnToRoomId === '' || !Room::where('id', $returnToRoomId)->exists()) {
            $returnToRoomId = null;
        }

        // Get the old bed to free it
        $oldBed = Bed::find($hospitalization->bed_id);

        // Update hospitalization with new room and bed
        $hospitalization->update([
            'room_id' => $data['room_id'],
            'bed_id' => $data['bed_id'],
        ]);

        // Free the old bed
        if ($oldBed) {
            $oldBed->update(['is_occupied' => false]);
        }

        // Occupy the new bed
        $newBed = Bed::findOrFail($data['bed_id']);
        $newBed->update(['is_occupied' => true]);

        if ($returnToRoomId) {
            return redirect()->route('hospitalizations.roomManagement', ['room_id' => $returnToRoomId])
                ->with('success', localize('global.room_and_bed_updated_successfully') ?: 'Room and bed updated successfully.');
        }

        return redirect()->route('hospitalizations.show', $hospitalization)
            ->with('success', localize('global.room_and_bed_updated_successfully') ?: 'Room and bed updated successfully.');
    }

    /**
     * Room management: show rooms and beds with occupancy (patient name per bed, empty beds, unoccupy button).
     */
    public function roomManagement(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $rooms = Room::select('id', 'name')
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->orderBy('name')
            ->get();

        $selectedRoom = null;
        $bedsWithOccupation = collect();

        // Rooms that have at least one occupied bed (active hospitalization) — for swap room dropdown
        $roomIdsWithOccupiedBeds = Hospitalization::where('is_discharged', 0)
            ->whereHas('room', function ($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->pluck('room_id')
            ->unique()
            ->values()
            ->toArray();
        $roomsWithOccupiedBeds = Room::select('id', 'name')
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->whereIn('id', $roomIdsWithOccupiedBeds)
            ->orderBy('name')
            ->get();

        if ($request->filled('room_id')) {
            $roomId = $request->room_id;
            $selectedRoom = Room::find($roomId);
            if ($selectedRoom) {
                $beds = $selectedRoom->allBeds()->orderBy('number')->get();
                $bedIds = $beds->pluck('id');
                $activeHospitalizations = Hospitalization::whereIn('bed_id', $bedIds)
                    ->where('is_discharged', 0)
                    ->with('patient:id,name')
                    ->get()
                    ->keyBy('bed_id');
                foreach ($beds as $bed) {
                    $bed->active_hospitalization = $activeHospitalizations->get($bed->id);
                }
                $bedsWithOccupation = $beds;
            }
        }

        return view('pages.hospitalizations.room-management', compact('rooms', 'selectedRoom', 'bedsWithOccupation', 'roomsWithOccupiedBeds'));
    }

    /**
     * Unoccupy a bed: discharge the hospitalization and free the bed.
     */
    public function unoccupyBed(Request $request, Hospitalization $hospitalization)
    {
        if (!auth()->user()->hasPermissionTo('edit-hospitalizations')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'discharge_remark' => 'required|string|max:2000',
            'discharge_status' => 'required|in:recovered,died,moved',
            'discharged_at_date' => 'required|string',
            'discharged_at_time' => 'nullable|date_format:H:i',
        ]);

        // Convert Persian/Jalali date to datetime using Verta
        $dischargedAt = Verta::parse($data['discharged_at_date'])->datetime();
        $carbon = \Carbon\Carbon::instance($dischargedAt);
        if (!empty($data['discharged_at_time'])) {
            $carbon->setTimeFromTimeString($data['discharged_at_time']);
        }

        $hospitalization->update([
            'is_discharged' => 1,
            'discharge_remark' => $data['discharge_remark'],
            'discharge_status' => $data['discharge_status'],
            'discharged_at' => $carbon,
        ]);

        Bed::where('id', $hospitalization->bed_id)->update(['is_occupied' => false]);

        $roomId = $hospitalization->room_id;
        return redirect()->route('hospitalizations.roomManagement', ['room_id' => $roomId])
            ->with('success', localize('global.bed_freed_successfully') ?: 'Bed freed successfully.');
    }

    /**
     * Swap two patients' beds within the same room.
     */
    public function swapBed(Request $request, Hospitalization $hospitalization)
    {
        if (!auth()->user()->hasPermissionTo('edit-hospitalizations')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'target_bed_id' => 'required|exists:beds,id',
        ]);

        $targetBed = Bed::findOrFail($data['target_bed_id']);
        if ($targetBed->room_id != $hospitalization->room_id) {
            return back()->with('error', localize('global.target_bed_must_be_in_same_room') ?: 'Target bed must be in the same room.');
        }
        if ($hospitalization->bed_id == $targetBed->id) {
            return back()->with('error', localize('global.select_different_bed') ?: 'Select a different bed.');
        }

        $otherHospitalization = Hospitalization::where('bed_id', $targetBed->id)
            ->where('is_discharged', 0)
            ->first();
        if (!$otherHospitalization) {
            return back()->with('error', localize('global.target_bed_must_be_occupied_to_swap') ?: 'Target bed must be occupied to swap.');
        }

        $currentBedId = $hospitalization->bed_id;
        $hospitalization->update(['bed_id' => $targetBed->id]);
        $otherHospitalization->update(['bed_id' => $currentBedId]);

        $roomId = $hospitalization->room_id;
        return redirect()->route('hospitalizations.roomManagement', ['room_id' => $roomId])
            ->with('success', localize('global.beds_swapped_successfully') ?: 'Beds swapped successfully.');
    }

    /**
     * Swap two patients between different rooms (and beds).
     */
    public function swapRoom(Request $request, Hospitalization $hospitalization)
    {
        if (!auth()->user()->hasPermissionTo('edit-hospitalizations')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'target_room_id' => 'required|exists:rooms,id',
            'target_bed_id' => 'required|exists:beds,id',
        ]);

        if ($data['target_room_id'] == $hospitalization->room_id) {
            return back()->with('error', localize('global.select_different_room_to_swap') ?: 'Select a different room to swap.');
        }

        $targetBed = Bed::findOrFail($data['target_bed_id']);
        if ($targetBed->room_id != $data['target_room_id']) {
            return back()->with('error', localize('global.bed_must_belong_to_selected_room') ?: 'Bed must belong to the selected room.');
        }

        $otherHospitalization = Hospitalization::where('bed_id', $targetBed->id)
            ->where('is_discharged', 0)
            ->first();
        if (!$otherHospitalization) {
            return back()->with('error', localize('global.target_bed_must_be_occupied_to_swap') ?: 'Target bed must be occupied to swap.');
        }

        $myRoomId = $hospitalization->room_id;
        $myBedId = $hospitalization->bed_id;
        $otherRoomId = $otherHospitalization->room_id;
        $otherBedId = $otherHospitalization->bed_id;

        $hospitalization->update(['room_id' => $otherRoomId, 'bed_id' => $otherBedId]);
        $otherHospitalization->update(['room_id' => $myRoomId, 'bed_id' => $myBedId]);

        $returnRoomId = $request->input('return_to_room_id', $myRoomId);
        return redirect()->route('hospitalizations.roomManagement', ['room_id' => $returnRoomId])
            ->with('success', localize('global.rooms_swapped_successfully') ?: 'Rooms swapped successfully.');
    }
}
