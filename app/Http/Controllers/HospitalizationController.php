<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewHospitalizationNotification;
use App\Models\Bed;
use App\Models\FoodType;
use App\Models\Hospitalization;
use App\Models\LabType;
use App\Models\LabTypeSection;
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\MedicineUsageType;
use App\Models\OperationType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
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
        if ($request->ajax()) {
            $hospitalizations = Hospitalization::where('branch_id', auth()->user()->branch_id)
                ->where('is_discharged', '0')
                ->with(['patient', 'room', 'bed'])
                ->get();

            if ($hospitalizations) {
                return response()->json([
                    'data' => $hospitalizations,
                ]);
            } else {
                return response()->json([
                    'message' => 'Internal Server Error',
                    'code' => 500,
                    'data' => [],
                ]);
            }
        }

        $hospitalizations = Hospitalization::where('branch_id', auth()->user()->branch_id)
            ->where('is_discharged', '0')
            ->with(['patient', 'room', 'bed'])
            ->get();
        return view('pages.hospitalizations.index', compact('hospitalizations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function discharged(Request $request)
    {
        if ($request->ajax()) {
            $hospitalizations = Hospitalization::where('branch_id', auth()->user()->branch_id)
                ->where('is_discharged', '1')
                ->with(['patient', 'room', 'bed'])
                ->get();

            if ($hospitalizations) {
                return response()->json([
                    'data' => $hospitalizations,
                ]);
            } else {
                return response()->json([
                    'message' => 'Internal Server Error',
                    'code' => 500,
                    'data' => [],
                ]);
            }
        }

        $hospitalizations = Hospitalization::where('branch_id', auth()->user()->branch_id)
            ->where('is_discharged', '1')
            ->with(['patient', 'room', 'bed'])
            ->get();
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
            'doctor_id' => 'required',
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

        $data['food_type_id'] = json_encode($data['food_type_id']);

        $hospitalization = Hospitalization::create($data);

        $occupied_bed = Bed::findOrFail($data['bed_id']);

        $occupied_bed->update(['is_occupied' => true]);
        $occupied_bed->save();

        SendNewHospitalizationNotification::dispatch($hospitalization->created_by, $hospitalization->id);

        return redirect()->back()->with('success', localize('global.hospitalization_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Hospitalization $hospitalization)
    {
        $labTypeSections = LabTypeSection::all();
        $operationTypes = OperationType::where('branch_id', auth()->user()->branch_id)->get();
        $labTypes = LabType::all();
        $operation_doctors = User::where('branch_id', auth()->user()->branch_id)->get();
        $medicineTypes = MedicineType::all();
        $medicines = Medicine::all();
        $foodTypes = FoodType::all();
        $medicineUsageTypes = MedicineUsageType::all();

        return view('pages.hospitalizations.show', compact('hospitalization', 'labTypeSections', 'operationTypes', 'labTypes', 'operation_doctors', 'medicineTypes', 'medicines', 'foodTypes', 'medicineUsageTypes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hospitalization $hospitalization)
    {
        //
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

        return redirect()->route('hospitalizations.index')->with('success', localize('global.hospitalization_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hospitalization $hospitalization)
    {
        //
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
}
