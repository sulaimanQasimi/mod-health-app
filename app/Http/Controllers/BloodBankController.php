<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewBloodBankNotification;
use App\Models\BloodBank;
use App\Models\BloodBranchTransfer;
use App\Models\BloodCrossmatch;
use App\Models\BloodPatientSample;
use App\Models\BloodStockMovement;
use App\Models\BloodUnit;
use App\Models\Department;
use App\Services\BloodCrossmatchService;
use App\Services\BloodBankStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Excel;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Mpdf\Mpdf;

class BloodBankController extends Controller
{
    public function dashboard()
    {
        $branchId = auth()->user()->branch_id;

        $lowThreshold = config('blood_bank.low_stock_threshold', 5);
        $criticalDays = config('blood_bank.expiry_critical_days', 3);
        $warningDays = config('blood_bank.expiry_warning_days', 7);

        $statusCounts = BloodBank::where('branch_id', $branchId)
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $availableByGroup = BloodUnit::where('branch_id', $branchId)
            ->where('status', 'available')
            ->where('expires_at', '>', now())
            ->selectRaw('blood_group, rh, component_type, count(*) as c')
            ->groupBy('blood_group', 'rh', 'component_type')
            ->get();

        $lowStockRows = $availableByGroup->filter(fn ($row) => (int) $row->c < $lowThreshold)->values();

        $expiringSoon = BloodUnit::where('branch_id', $branchId)
            ->where('status', 'available')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays($warningDays))
            ->orderBy('expires_at')
            ->limit(25)
            ->get();

        $criticalExpiryCount = BloodUnit::where('branch_id', $branchId)
            ->where('status', 'available')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays($criticalDays))
            ->count();

        $pendingTransfersCount = BloodBranchTransfer::where('status', 'pending')
            ->where(function ($q) use ($branchId) {
                $q->where('requesting_branch_id', $branchId)
                    ->orWhere('supplying_branch_id', $branchId);
            })
            ->count();

        $quarantineCount = BloodUnit::where('branch_id', $branchId)->where('status', 'quarantine')->count();

        return view('pages.blood_banks.dashboard', compact(
            'statusCounts',
            'availableByGroup',
            'expiringSoon',
            'lowStockRows',
            'lowThreshold',
            'criticalDays',
            'warningDays',
            'criticalExpiryCount',
            'pendingTransfersCount',
            'quarantineCount'
        ));
    }

    /**
     * Branch-scoped stock movement audit log.
     */
    public function stockMovements(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        $query = BloodStockMovement::query()
            ->with(['bloodUnit', 'user'])
            ->whereHas('bloodUnit', fn ($q) => $q->where('branch_id', $branchId));

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('bag_number')) {
            $bn = $request->bag_number;
            $query->whereHas('bloodUnit', fn ($q) => $q->where('bag_number', 'like', '%'.$bn.'%'));
        }

        $movements = $query->orderByDesc('created_at')->paginate(40)->withQueryString();

        return view('pages.blood_banks.movements', compact('movements'));
    }

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

    public function deliver(Request $request, $bloodBank)
    {
        $bloodBank = BloodBank::findOrFail($bloodBank);
        $this->ensureBloodRequestBranch($bloodBank);

        if (! BloodUnit::where('branch_id', $bloodBank->branch_id)->exists()) {
            $bloodBank->deliver();
            return redirect()->back()->with('success', localize('global.blood_request_delivered_successfully'));
        }

        $unitIds = $request->input('unit_ids', []);
        $unitIds = is_array($unitIds) ? array_values(array_filter(array_map('intval', $unitIds))) : [];

        try {
            app(BloodBankStockService::class)->deliverRequest($bloodBank, count($unitIds) > 0 ? $unitIds : null);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', localize('global.blood_request_delivered_successfully'));
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
            'operation_id' => 'nullable|exists:operations,id',
            'i_c_u_id' => 'nullable|exists:i_c_u_s,id',
            'under_review_id' => 'nullable|exists:under_reviews,id',
        ], [
            'appointment_id.exists' => 'Invalid appointment',
        ]);

        // Create the blood bank record
        $bloodBank = BloodBank::create([
            'created_by' => auth()->id(),
            'group' => $validatedData['group'],
            'rh' => $validatedData['rh'],
            'type' => $validatedData['type'],
            'quantity' => $validatedData['quantity'],
            'branch_id' => $validatedData['branch_id'],
            'appointment_id' => $validatedData['appointment_id'] ?? null,
            'hospitalization_id' => $validatedData['hospitalization_id'] ?? null,
            'anesthesia_id' => $validatedData['anesthesia_id'] ?? null,
            'patient_id' => $validatedData['patient_id'] ?? null,
            'department_id' => $validatedData['department_id'] ?? null,
            'operation_id' => $validatedData['operation_id'] ?? null,
            'i_c_u_id' => $validatedData['i_c_u_id'] ?? null,
            'under_review_id' => $validatedData['under_review_id'] ?? null,
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
        $this->ensureBloodRequestBranch($bloodBank);

        $bloodBank->load([
            'patient',
            'department',
            'createdBy',
            'bloodUnits',
            'appointment',
            'patientSamples.collectedBy',
            'crossmatches.bloodUnit',
            'crossmatches.patientSample',
            'crossmatches.testedBy',
            'crossmatches.overriddenBy',
        ]);

        $availableUnits = collect();
        if ($bloodBank->status === 'approved') {
            $availableUnits = BloodUnit::where('branch_id', $bloodBank->branch_id)
                ->where('component_type', $bloodBank->type)
                ->whereIn('status', ['available', 'reserved'])
                ->where('expires_at', '>', now())
                ->whereHas('test', fn ($q) => $q->where('overall_status', 'passed'))
                ->with('test')
                ->orderBy('expires_at')
                ->get();
        }

        $inventoryUrl = route('blood_banks.inventory', [
            'status' => 'available',
            'blood_group' => $bloodBank->group,
            'rh' => $bloodBank->rh,
            'component_type' => $bloodBank->type,
            'source_request_id' => $bloodBank->id,
        ]);

        $inventoryPreviewUnits = BloodUnit::query()
            ->where('branch_id', $bloodBank->branch_id)
            ->where('component_type', $bloodBank->type)
            ->where('status', 'available')
            ->where('expires_at', '>', now())
            ->whereHas('test', fn ($q) => $q->where('overall_status', 'passed'))
            ->with('test')
            ->orderBy('expires_at')
            ->limit(12)
            ->get();

        $crossmatchService = app(BloodCrossmatchService::class);
        $crossmatchesByUnit = $bloodBank->crossmatches->keyBy('blood_unit_id');
        $reservedUnitIds = $bloodBank->bloodUnits
            ->filter(fn ($u) => ! is_null($u->pivot?->reserved_at))
            ->pluck('id')
            ->values();

        $requestedQty = max(0, (int) $bloodBank->quantity);
        $reservedCompatibleQty = $bloodBank->crossmatches
            ->filter(fn ($cx) => in_array($cx->status, ['compatible', 'overridden'], true))
            ->filter(fn ($cx) => $reservedUnitIds->contains($cx->blood_unit_id))
            ->count();
        $issuedQty = $bloodBank->bloodUnits
            ->filter(fn ($u) => ! is_null($u->pivot?->issued_at))
            ->count();
        $remainingQty = max(0, $requestedQty - $issuedQty);

        return view('pages.blood_banks.show', compact(
            'bloodBank',
            'availableUnits',
            'inventoryUrl',
            'inventoryPreviewUnits',
            'crossmatchService',
            'crossmatchesByUnit',
            'reservedUnitIds',
            'requestedQty',
            'reservedCompatibleQty',
            'issuedQty',
            'remainingQty'
        ));
    }

    public function storePatientSample(Request $request, BloodBank $bloodBank)
    {
        $this->ensureBloodRequestBranch($bloodBank);
        $this->ensureCanManageCrossmatch($request);

        $validated = $request->validate([
            'sample_id' => 'nullable|string|max:100',
            'collected_at' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        BloodPatientSample::create([
            'blood_bank_id' => $bloodBank->id,
            'patient_id' => $bloodBank->patient_id,
            'sample_id' => $validated['sample_id'] ?? null,
            'collected_at' => $validated['collected_at'] ?? now(),
            'collected_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', localize('global.crossmatch_sample_saved'));
    }

    public function saveCrossmatch(Request $request, BloodBank $bloodBank, BloodUnit $bloodUnit)
    {
        $this->ensureBloodRequestBranch($bloodBank);
        $this->ensureCanManageCrossmatch($request);

        if ((int) $bloodUnit->branch_id !== (int) $bloodBank->branch_id) {
            abort(404);
        }

        $validated = $request->validate([
            'patient_sample_id' => ['nullable', 'integer', Rule::exists('blood_patient_samples', 'id')],
            'major_result' => ['required', Rule::in(BloodCrossmatch::RESULT_VALUES)],
            'minor_result' => ['required', Rule::in(BloodCrossmatch::RESULT_VALUES)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (! empty($validated['patient_sample_id'])) {
            $sample = BloodPatientSample::where('id', $validated['patient_sample_id'])
                ->where('blood_bank_id', $bloodBank->id)
                ->first();
            if (! $sample) {
                return back()->with('error', localize('global.crossmatch_invalid_sample'));
            }
        }

        $decision = app(BloodCrossmatchService::class)->evaluateCompatibility($bloodBank, $bloodUnit, $validated);

        BloodCrossmatch::updateOrCreate(
            [
                'blood_bank_id' => $bloodBank->id,
                'blood_unit_id' => $bloodUnit->id,
            ],
            [
                'patient_id' => $bloodBank->patient_id,
                'patient_sample_id' => $validated['patient_sample_id'] ?? null,
                'major_result' => $validated['major_result'],
                'minor_result' => $validated['minor_result'],
                'status' => $decision['status'],
                'auto_decision' => $decision['auto_decision'],
                'auto_reason' => $decision['auto_reason'],
                'is_overridden' => false,
                'override_by' => null,
                'override_reason' => null,
                'tested_at' => now(),
                'tested_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return back()->with('success', localize('global.crossmatch_saved'));
    }

    public function overrideCrossmatch(Request $request, BloodBank $bloodBank, BloodCrossmatch $crossmatch)
    {
        $this->ensureBloodRequestBranch($bloodBank);
        if (! $request->user()->can('manage-blood-inventory')) {
            abort(403);
        }

        if ((int) $crossmatch->blood_bank_id !== (int) $bloodBank->id) {
            abort(404);
        }

        $validated = $request->validate([
            'override_reason' => ['required', 'string', 'max:1000'],
        ]);

        app(BloodCrossmatchService::class)->overrideCompatible($crossmatch, (int) auth()->id(), $validated['override_reason']);

        return back()->with('success', localize('global.crossmatch_override_saved'));
    }

    public function reserveCrossmatchUnit(Request $request, BloodBank $bloodBank, BloodCrossmatch $crossmatch)
    {
        $this->ensureBloodRequestBranch($bloodBank);
        $this->ensureCanManageCrossmatch($request);

        if ((int) $crossmatch->blood_bank_id !== (int) $bloodBank->id) {
            abort(404);
        }

        if (! in_array($crossmatch->status, ['compatible', 'overridden'], true)) {
            return back()->with('error', localize('global.crossmatch_cannot_reserve_incompatible'));
        }

        try {
            DB::transaction(function () use ($bloodBank, $crossmatch) {
                $unit = BloodUnit::where('id', $crossmatch->blood_unit_id)
                    ->where('branch_id', $bloodBank->branch_id)
                    ->lockForUpdate()
                    ->first();

                if (! $unit || ! in_array($unit->status, ['available', 'reserved'], true) || $unit->expires_at <= now()) {
                    throw new \RuntimeException(localize('global.crossmatch_unit_not_reservable'));
                }

                $isReservedElsewhere = DB::table('blood_bank_unit')
                    ->where('blood_unit_id', $unit->id)
                    ->whereNotNull('reserved_at')
                    ->where('blood_bank_id', '!=', $bloodBank->id)
                    ->exists();
                if ($isReservedElsewhere) {
                    throw new \RuntimeException(localize('global.crossmatch_unit_already_reserved'));
                }

                $bloodBank->bloodUnits()->syncWithoutDetaching([
                    $unit->id => [
                        'reserved_at' => now(),
                        'reserved_by' => auth()->id(),
                        'crossmatch_id' => $crossmatch->id,
                    ],
                ]);
                $unit->status = 'reserved';
                $unit->save();
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', localize('global.crossmatch_unit_reserved'));
    }

    public function unreserveCrossmatchUnit(Request $request, BloodBank $bloodBank, BloodUnit $bloodUnit)
    {
        $this->ensureBloodRequestBranch($bloodBank);
        $this->ensureCanManageCrossmatch($request);

        try {
            DB::transaction(function () use ($bloodBank, $bloodUnit) {
                $row = DB::table('blood_bank_unit')
                    ->where('blood_bank_id', $bloodBank->id)
                    ->where('blood_unit_id', $bloodUnit->id)
                    ->lockForUpdate()
                    ->first();
                if (! $row) {
                    throw new \RuntimeException(localize('global.crossmatch_unit_not_reserved_for_request'));
                }

                DB::table('blood_bank_unit')
                    ->where('blood_bank_id', $bloodBank->id)
                    ->where('blood_unit_id', $bloodUnit->id)
                    ->update([
                        'reserved_at' => null,
                        'reserved_by' => null,
                        'crossmatch_id' => null,
                        'updated_at' => now(),
                    ]);

                $hasOtherReservations = DB::table('blood_bank_unit')
                    ->where('blood_unit_id', $bloodUnit->id)
                    ->whereNotNull('reserved_at')
                    ->exists();
                if (! $hasOtherReservations && $bloodUnit->status === 'reserved') {
                    $bloodUnit->status = 'available';
                    $bloodUnit->save();
                }
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', localize('global.crossmatch_unit_unreserved'));
    }

    protected function ensureCanManageCrossmatch(Request $request): void
    {
        $user = $request->user();
        if (! $user->can('receive-blood-units') && ! $user->can('manage-blood-inventory')) {
            abort(403);
        }
    }

    protected function ensureBloodRequestBranch(BloodBank $bloodBank): void
    {
        if ((int) $bloodBank->branch_id !== (int) auth()->user()->branch_id) {
            abort(404);
        }
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
            ->leftJoin('appointments as apt', 'bb.appointment_id', '=', 'apt.id')
            ->select(
                'bb.id',
                'p.name as patient_name',
                'd.name as department_name',
                'b.name as branch_name',
                'bb.status',
                'bb.group',
                'bb.rh',
                'apt.id as appointment_id'
            );

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
            ->leftJoin('appointments as apt', 'bb.appointment_id', '=', 'apt.id')
            ->select(
                'bb.id',
                'p.name as patient_name',
                'd.name as department_name',
                'b.name as branch_name',
                'bb.status',
                'bb.group',
                'bb.rh',
                'apt.id as appointment_id'
            )
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


                $sheet->getStyle('A2:H' . $sheet->getHighestRow())->getAlignment()->setWrapText(true);
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(16);
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
                $sheet->setCellValue('G' . $row . '', $item->appointment_id ?? '');

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
