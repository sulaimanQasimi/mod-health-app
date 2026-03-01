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
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\MedicineUsageType;
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
            $query = ICU::where('branch_id', auth()->user()->branch_id)->with('patient');

            if ($request->filled('search')) {
                $term = $request->search;
                $query->whereHas('patient', function ($q) use ($term) {
                    $q->where('name', 'like', '%' . $term . '%')
                        ->orWhere('father_name', 'like', '%' . $term . '%')
                        ->orWhere('id_card', 'like', '%' . $term . '%')
                        ->orWhere('last_name', 'like', '%' . $term . '%')
                        ->orWhere('phone', 'like', '%' . $term . '%');
                });
            }
            if ($request->filled('patient_name')) {
                $query->whereHas('patient', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->patient_name . '%')
                        ->orWhere('last_name', 'like', '%' . $request->patient_name . '%');
                });
            }
            if ($request->filled('card_number')) {
                $query->whereHas('patient', function ($q) use ($request) {
                    $q->where('id_card', 'like', '%' . $request->card_number . '%');
                });
            }
            if ($request->filled('father_name')) {
                $query->whereHas('patient', function ($q) use ($request) {
                    $q->where('father_name', 'like', '%' . $request->father_name . '%');
                });
            }

            $icus = $query->get();

            return response()->json(['data' => $icus]);
        }

        return view('pages.icus.index');
    }

    public function new(Request $request)
    {
        $query = ICU::where('status', 'new')
            ->when(auth()->user()->branch_id, fn ($q) => $q->where('branch_id', auth()->user()->branch_id))
            ->with('patient');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->whereHas('patient', function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('father_name', 'like', '%' . $term . '%')
                    ->orWhere('id_card', 'like', '%' . $term . '%')
                    ->orWhere('last_name', 'like', '%' . $term . '%')
                    ->orWhere('phone', 'like', '%' . $term . '%');
            });
        }
        if ($request->filled('patient_name')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->patient_name . '%')
                    ->orWhere('last_name', 'like', '%' . $request->patient_name . '%');
            });
        }
        if ($request->filled('card_number')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('id_card', 'like', '%' . $request->card_number . '%');
            });
        }
        if ($request->filled('father_name')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('father_name', 'like', '%' . $request->father_name . '%');
            });
        }

        $icus = $query->latest()->paginate(10)->appends($request->query());

        return view('pages.icus.new', compact('icus'));
    }

    public function approved(Request $request)
    {
        $query = ICU::where('status', 'approved')
            ->with('patient');

        // Filter by discharge status: all | in_icu | discharged | recovered | died | moved (default: in_icu)
        $dischargeFilter = $request->get('discharge_filter', 'in_icu');
        if ($dischargeFilter === 'in_icu') {
            $query->where(function ($q) {
                $q->where('is_discharged', 0)->orWhereNull('is_discharged');
            });
        } elseif ($dischargeFilter === 'discharged') {
            $query->where('is_discharged', 1);
        } elseif ($dischargeFilter === 'recovered') {
            $query->where('is_discharged', 1)->where('discharge_status', 'recovered');
        } elseif ($dischargeFilter === 'died') {
            $query->where('is_discharged', 1)->where('discharge_status', 'died');
        } elseif ($dischargeFilter === 'moved') {
            $query->where('is_discharged', 1)->where('discharge_status', 'moved');
        }

        // Search by patient name, father name, or card number (id_card)
        if ($request->filled('search')) {
            $term = $request->search;
            $query->whereHas('patient', function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('father_name', 'like', '%' . $term . '%')
                    ->orWhere('id_card', 'like', '%' . $term . '%')
                    ->orWhere('last_name', 'like', '%' . $term . '%')
                    ->orWhere('phone', 'like', '%' . $term . '%');
            });
        }
        if ($request->filled('patient_name')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->patient_name . '%')
                    ->orWhere('last_name', 'like', '%' . $request->patient_name . '%');
            });
        }
        if ($request->filled('card_number')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('id_card', 'like', '%' . $request->card_number . '%');
            });
        }
        if ($request->filled('father_name')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('father_name', 'like', '%' . $request->father_name . '%');
            });
        }

        $icus = $query->latest()->paginate(10)->appends($request->query());

        return view('pages.icus.approved', compact('icus'));
    }

    public function rejected(Request $request)
    {
        $query = ICU::where('status', 'rejected')
            ->when(auth()->user()->branch_id, fn ($q) => $q->where('branch_id', auth()->user()->branch_id))
            ->with('patient');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->whereHas('patient', function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('father_name', 'like', '%' . $term . '%')
                    ->orWhere('id_card', 'like', '%' . $term . '%')
                    ->orWhere('last_name', 'like', '%' . $term . '%')
                    ->orWhere('phone', 'like', '%' . $term . '%');
            });
        }
        if ($request->filled('patient_name')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->patient_name . '%')
                    ->orWhere('last_name', 'like', '%' . $request->patient_name . '%');
            });
        }
        if ($request->filled('card_number')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('id_card', 'like', '%' . $request->card_number . '%');
            });
        }
        if ($request->filled('father_name')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('father_name', 'like', '%' . $request->father_name . '%');
            });
        }

        $icus = $query->latest()->paginate(10)->appends($request->query());

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
        $medicineUsageTypes = MedicineUsageType::all();

        return view('pages.icus.show',compact('icu','previousDiagnoses','previousLabs','labTypes','branches','departments','doctors','foodTypes','medicineTypes','medicines','procedure_types','rooms','beds','relations','medicineUsageTypes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ICU $icu)
    {
        return view('pages.icus.edit',compact('icu'));
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

        // When discharged (recovered, died, moved / رخصت), free the bed
        if ($icu->is_discharged && $icu->hospitalization_id && $icu->hospitalization && $icu->hospitalization->bed_id) {
            $bed = Bed::find($icu->hospitalization->bed_id);
            if ($bed) {
                $bed->update(['is_occupied' => 0]);
            }
        }

        return redirect()->back()->with('success', localize('global.icu_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ICU $icu)
    {
        $icu->delete();
        return redirect()->route('appointments.doctorAppointments')->with('success', localize('global.icu_deleted_successfully.'));

    }

    public function updateICU(Request $request, ICU $icu)
    {
        $data = $request->validate([
            'description' => 'required',
        ]);

        $icu->update($data);

        return redirect()->route('appointments.doctorAppointments')->with('success', localize('global.icu_updated_successfully.'));
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
