<?php

namespace App\Http\Controllers;

use App\Models\Anesthesia;
use App\Models\Bed;
use App\Models\FoodType;
use App\Models\Operation;
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
use App\Models\OperationType;
class OperationController extends Controller
{



    /**
     * Display a listing of the resource.
     */
    public function new()
    {

        $operations = Anesthesia::with('patient')->where('status', 'approved')->where('is_operation_approved', '0')->where('is_reserved', '0')->latest()->paginate(15);

        return view('pages.operations.new', compact('operations'));
    }

    public function reserved()
    {

        $reservedOperations = Anesthesia::reserved()->paginate(10);
        return view('pages.operations.reserved', compact('reservedOperations'));
    }

    public function approved()
    {

        $operations = Anesthesia::with('patient')->where('status', 'approved')->where('is_operation_approved', '1')->where('is_operation_done', '0')->where('is_reserved', '0')->latest()->paginate(15);

        return view('pages.operations.approved', compact('operations'));
    }


    public function completed()
    {
        $operations = Anesthesia::where('is_operation_done', '1')->latest()->paginate(15);

        return view('pages.operations.completed', compact('operations'));
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Anesthesia $operation)
    {
        $operation_doctors = User::where('branch_id', auth()->user()->branch_id)->get();
        $rooms = Room::all();
        $beds = Bed::all();
        $foodTypes = FoodType::all();
        $relations = Relation::all();
        return view('pages.operations.show',compact('operation','operation_doctors','rooms','beds','foodTypes','relations'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Operation $operation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Anesthesia $operation)
    {
        $data = $request->validate([
            'is_operation_done' => 'nullable',
            'is_operation_approved' => 'nullable',
            'operation_remark' => 'nullable',
            'operation_result' => 'nullable',
            'operation_scrub_nurse_id' => 'nullable',
            'operation_circulation_nurse_id' => 'nullable',
            'date' => 'nullable',
            'time' => 'nullable',
            'operation_expense_remarks' => 'nullable',
            'room_id' => 'nullable',
            'bed_id' => 'nullable',
            'patient_status' => 'nullable',

        ]);

        if (isset($data['date']) && $data['date'] > $operation->date) {
            $operation->reserve();
            $operation->update($data);
            return redirect()->route('operations.reserved')->with('success', localize('global.operation_reserved_successfully.'));
        }

        elseif (isset($data['date']) && $data['date'] < $operation->date) {
            $operation->update($data);
            return redirect()->back()->with('success', localize('global.operation_updated_successfully.'));
        }

        else {
            $operation->update($data);
            return redirect()->back()->with('success', localize('global.operation_updated_successfully.'));
        }

        $data['room_id'] = $operation->room->id ?? '';
        $data['bed_id'] = $operation->bed->id ?? '';

        $occupied_bed = Bed::findOrFail($data['bed_id']);

        $occupied_bed->update(['is_occupied' => true]);
        $occupied_bed->save();

        $operation->update($data);

        return redirect()->route('operations.new')->with('success', localize('global.operation_updated_successfully.'));
    }

    public function complete(Request $request, Anesthesia $operation)
    {
        $data = $request->validate([
            'is_operation_done' => 'nullable',
            'operation_remark' => 'nullable',
            'operation_result' => 'nullable',
            'room_id' => 'nullable',
            'bed_id' => 'nullable',

        ]);

        $data['room_id'] = $operation->room->id ?? '';
        $data['bed_id'] = $operation->bed->id ?? '';

        $occupied_bed = Bed::findOrFail($data['bed_id']);

        $occupied_bed->update(['is_occupied' => false]);
        $occupied_bed->save();

        $operation->update($data);

        return redirect()->back()->with('success', localize('global.operation_completed_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Operation $operation)
    {
        //
    }

    public function reserveOperation(Request $request, $operationId)
    {

        $operation = Anesthesia::findOrFail($operationId);
        $operation->reserve();
        $operation->update(['reserve_reason' => $request->reserve_reason]);
        $operation->save();

        // Add any additional logic, such as redirecting or returning a response
        return redirect()->route('operations.reserved')->with('success', localize('global.operation_reserved_successfully.'));
    }

    public function unreserveOperation($operationId)
    {

        $operation = Anesthesia::findOrFail($operationId);
        $operation->unreserve();
        $operation->update(['is_operation_approved' => '0']);
        $operation->save();
        // Add any additional logic, such as redirecting or returning a response
        return redirect()->back()->with('success', localize('global.operation_unreserved_successfully.'));
    }

    public function report()
    {

        $operationTypes = OperationType::all();

        return view('pages.operations.reports.index', compact('operationTypes'));
    }
    public function reportSearch(Request $request)
    {
        $query = DB::table('anesthesias as a')
        ->leftJoin('patients as p', 'a.patient_id' , '=', 'p.id')
        ->leftJoin('doctors as d', 'a.doctor_id' , '=', 'd.id')
        ->leftJoin('branches as b', 'a.branch_id' , '=', 'b.id')
        ->leftJoin('users as u', 'a.operation_surgion_id' , '=', 'u.id')
        ->leftJoin('operation_types as ot', 'a.operation_type_id' , '=', 'ot.id')
        ->select('a.id','p.name as patient_name', 'd.name as doctor_name',
        'b.name as branch_name', 'a.status', 'a.anesthesia_type', 'a.date', 'a.time',
        'u.name as operation_surgion_name', 'ot.name as operation_type_name', 'a.is_operation_done', 'a.is_operation_approved', 'a.is_reserved');

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('operation_surgion_name')) {
            $query->where('u.name', 'like', '%' . $request->operation_surgion_name . '%');
        }

        if ($request->filled('operation_status')) {
            $query->where('a.is_operation_done', $request->operation_status);
        }

        if ($request->filled('operation_approval')) {
            $query->where('a.is_operation_approved', $request->operation_approval);
        }

        if ($request->filled('reserve_status')) {
            $query->where('a.is_reserved', $request->reserve_status);
        }

        if ($request->filled('operation_type_id')) {
            $query->where('a.operation_type_id', $request->operation_type_id);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('a.created_at', [$request->from, $request->to]);
        }

        $items = $query->get();
    return view('pages.operations.reports.report', ['items' => $items]);

    }


    public function exportReport(Request $request)
    {

        $data = json_decode($request->data, true);

        $items = DB::table('anesthesias as a')
        ->leftJoin('patients as p', 'a.patient_id' , '=', 'p.id')
        ->leftJoin('doctors as d', 'a.doctor_id' , '=', 'd.id')
        ->leftJoin('branches as b', 'a.branch_id' , '=', 'b.id')
        ->leftJoin('users as u', 'a.operation_surgion_id' , '=', 'u.id')
        ->leftJoin('operation_types as ot', 'a.operation_type_id' , '=', 'ot.id')
        ->select('a.id','p.name as patient_name', 'd.name as doctor_name',
        'b.name as branch_name', 'a.status', 'a.anesthesia_type', 'a.date', 'a.time',
        'u.name as operation_surgion_name', 'ot.name as operation_type_name', 'a.is_operation_done', 'a.is_operation_approved', 'a.is_reserved')
        ->whereIn('a.id', $data)->get();
        $reader = new Xlsx();
        $spreadsheet = $reader->load("report_templates/operations_report.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        $html = view('pages.operations.reports.pdf_report',  ['items' => $items])->render();
        if ($request->type == 'pdf') {
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('pdf_report.pdf', 'D');
        }else {
            $spreadsheet = $reader->load("report_templates/operations_report.xlsx");
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
                $sheet->getColumnDimension('I')->setWidth(20);
                $styleArray = array(
                    'font' => array(
                        'name' => 'B Nazanin',
                        'color' => 15,
                        'bold' => true

                    ),
                );

                $operation_done = '';
                if ($item->is_operation_done == '0') {
                    $operation_done = 'نااجراء';
                }else {
                    $operation_done = 'تکمیل';
                }

                $operation_approved = '';
                if ($item->is_operation_approved == '0') {
                    $operation_approved = 'تائید ناشده';
                }else {
                    $operation_approved = 'تائید شده';
                }

                $reserved = '';
                if ($item->is_reserved == '0') {
                    $reserved = 'ریزرف ناشده';
                }else {
                    $reserved = 'ریزرف شده';
                }
                    $sheet->setCellValue('A' . $row . '', ++$index);
                    $sheet->setCellValue('B' . $row . '', $item->patient_name);
                    $sheet->setCellValue('C' . $row . '', $item->operation_surgion_name);
                    $sheet->setCellValue('D' . $row . '', $operation_done);
                    $sheet->setCellValue('E' . $row . '', $operation_approved);
                    $sheet->setCellValue('F' . $row . '', $reserved);
                    $sheet->setCellValue('G' . $row . '', $item->operation_type_name);
                    $sheet->setCellValue('H' . $row . '', $item->date);
                    $sheet->setCellValue('I' . $row . '', $item->time);

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
