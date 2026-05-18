<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMultipleVitalSignsRequest;
use App\Http\Requests\StoreVitalSignRequest;
use App\Models\Nurse;
use App\Models\VitalSign;
use App\Models\VitalSignSchedule;
use App\Models\VitalSignType;
use App\Services\VitalSignManageService;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VitalSignController extends Controller
{
    public function __construct(
        private readonly VitalSignManageService $vitalSignManage
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', VitalSign::class);

        $query = VitalSign::with(['vitalSignType', 'morphable', 'schedules.nurse', 'createdBy']);

        if ($request->filled('morphable_type')) {
            $query->where('morphable_type', $request->morphable_type);
        }

        if ($request->filled('morphable_id')) {
            $query->where('morphable_id', $request->morphable_id);
        }

        if ($request->filled('vital_sign_type_id')) {
            $query->where('vital_sign_type_id', $request->vital_sign_type_id);
        }

        if ($request->filled('date_from')) {
            try {
                $query->whereDate('created_at', '>=', Verta::parse($request->date_from)->datetime());
            } catch (\Exception $e) {
            }
        }

        if ($request->filled('date_to')) {
            try {
                $query->whereDate('created_at', '<=', Verta::parse($request->date_to)->datetime());
            } catch (\Exception $e) {
            }
        }

        $vitalSigns = $query->orderBy('created_at', 'desc')->paginate(15);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $vitalSigns->items(),
                'meta' => [
                    'current_page' => $vitalSigns->currentPage(),
                    'last_page' => $vitalSigns->lastPage(),
                    'per_page' => $vitalSigns->perPage(),
                    'total' => $vitalSigns->total(),
                ],
            ]);
        }

        $vitalSignTypes = VitalSignType::orderBy('name')->get();

        return view('pages.vital-signs.index', compact('vitalSigns', 'vitalSignTypes'));
    }

    /**
     * Unified create / edit page for a morphable record (hospitalization, under review).
     */
    public function create(Request $request): View|RedirectResponse
    {
        $vitalSignTypes = VitalSignType::orderBy('name')->get();
        $currentUserNurse = auth()->user()->nurse ?? null;
        $morphableType = $request->get('morphable_type');
        $morphableId = (int) $request->get('morphable_id');
        $morphModel = null;

        if ($morphableType && $morphableId) {
            if (!$this->vitalSignManage->isAllowedMorphableType($morphableType)) {
                abort(404);
            }

            $morphModel = $this->vitalSignManage->resolveMorphable($morphableType, $morphableId);
            if (!$morphModel) {
                abort(404);
            }

            $this->authorizeManagePage($morphableType, $morphableId);

            $morphModel->setRelation(
                'vitalSigns',
                $this->vitalSignManage->loadVitalSignsForMorphable($morphableType, $morphableId)
            );
        } else {
            $this->authorize('create', VitalSign::class);
        }

        return view('pages.vital-signs.create', compact(
            'vitalSignTypes',
            'morphableType',
            'morphableId',
            'currentUserNurse',
            'morphModel'
        ));
    }

    public function store(StoreMultipleVitalSignsRequest $request): RedirectResponse|JsonResponse
    {
        if ($request->isMorphableManageRequest()) {
            return $this->storeMorphableManage($request);
        }

        $this->authorize('create', VitalSign::class);

        if ($request->filled('vital_sign_type_id')) {
            $vitalSign = VitalSign::create($request->only(['vital_sign_type_id', 'morphable_type', 'morphable_id']));

            return $this->respondStored($request, $vitalSign, 'Vital sign created successfully.');
        }

        return redirect()->back()->withInput()->withErrors([
            'vital_signs' => __('global.at_least_one_vital_sign_type_required'),
        ]);
    }

    private function storeMorphableManage(StoreMultipleVitalSignsRequest $request): RedirectResponse|JsonResponse
    {
        $morphableType = $request->input('morphable_type');
        $morphableId = (int) $request->input('morphable_id');

        $this->authorizeManagePage($morphableType, $morphableId);

        foreach ($request->input('vital_signs', []) as $row) {
            if (!empty($row['vital_sign_type_id'])) {
                $this->authorize('create', VitalSign::class);
                break;
            }
        }

        $this->vitalSignManage->syncMorphable(
            $morphableType,
            $morphableId,
            $request->input('vital_signs', []),
            $request->input('existing_vital_signs', []),
            $request->input('delete_vital_sign_ids', []),
            $request->input('delete_schedule_ids', []),
            auth()->user()->nurse,
            fn (VitalSign|VitalSignSchedule $model) => $this->authorize('update', $model),
            fn (VitalSign|VitalSignSchedule $model) => $this->authorize('delete', $model),
        );

        $message = 'Vital signs and schedules saved successfully.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 200);
        }

        return redirect()
            ->route('vital-signs.create', [
                'morphable_type' => $morphableType,
                'morphable_id' => $morphableId,
            ])
            ->with('success', $message);
    }

    public function show(Request $request, VitalSign $vitalSign): View|RedirectResponse|JsonResponse
    {
        $this->authorize('view', $vitalSign);

        if ($redirect = $this->managePageRedirect($vitalSign)) {
            return $redirect;
        }

        $vitalSign->load(['vitalSignType', 'morphable', 'schedules.nurse', 'createdBy', 'updatedBy']);

        if ($request->expectsJson()) {
            return response()->json(['data' => $vitalSign]);
        }

        $nurses = Nurse::orderBy('first_name')->get();
        $currentUserNurse = auth()->user()->nurse;

        return view('pages.vital-signs.show', compact('vitalSign', 'nurses', 'currentUserNurse'));
    }

    public function edit(VitalSign $vitalSign): RedirectResponse
    {
        $this->authorize('update', $vitalSign);

        if ($redirect = $this->managePageRedirect($vitalSign)) {
            return $redirect;
        }

        return redirect()->route('vital-signs.index');
    }

    public function update(StoreVitalSignRequest $request, VitalSign $vitalSign): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $vitalSign);

        if ($vitalSign->morphable_type && $vitalSign->morphable_id) {
            return redirect()->route('vital-signs.create', [
                'morphable_type' => $vitalSign->morphable_type,
                'morphable_id' => $vitalSign->morphable_id,
            ]);
        }

        $vitalSign->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vital sign updated successfully.',
                'data' => $vitalSign->load(['vitalSignType', 'morphable', 'updatedBy']),
            ]);
        }

        return redirect()->route('vital-signs.index')->with('success', 'Vital sign updated successfully.');
    }

    public function print(string $morphable_type, int $morphable_id): View
    {
        $vitalSigns = VitalSign::with([
            'vitalSignType',
            'schedules' => fn ($q) => $q->orderBy('id'),
            'schedules.nurse',
            'morphable.patient',
        ])
            ->where('morphable_type', $morphable_type)
            ->where('morphable_id', $morphable_id)
            ->get();

        return view('pages.vital-signs.print', compact('vitalSigns'));
    }

    public function destroy(Request $request, VitalSign $vitalSign): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $vitalSign);

        $morphableType = $vitalSign->morphable_type;
        $morphableId = $vitalSign->morphable_id;

        $vitalSign->schedules()->delete();
        $vitalSign->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Vital sign deleted successfully.']);
        }

        if ($morphableType && $morphableId) {
            return redirect()
                ->route('vital-signs.create', [
                    'morphable_type' => $morphableType,
                    'morphable_id' => $morphableId,
                ])
                ->with('success', 'Vital sign deleted successfully.');
        }

        return redirect()->route('vital-signs.index')->with('success', 'Vital sign deleted successfully.');
    }

    private function authorizeManagePage(string $morphableType, int $morphableId): void
    {
        if (auth()->user()->can('create', VitalSign::class)) {
            return;
        }

        $canUpdateAny = VitalSign::query()
            ->where('morphable_type', $morphableType)
            ->where('morphable_id', $morphableId)
            ->get()
            ->contains(fn (VitalSign $vs) => auth()->user()->can('update', $vs));

        if (!$canUpdateAny) {
            $this->authorize('create', VitalSign::class);
        }
    }

    private function managePageRedirect(VitalSign $vitalSign): ?RedirectResponse
    {
        if (!$vitalSign->morphable_type || !$vitalSign->morphable_id) {
            return null;
        }

        return redirect()->route('vital-signs.create', [
            'morphable_type' => $vitalSign->morphable_type,
            'morphable_id' => $vitalSign->morphable_id,
        ]);
    }

    private function respondStored(Request $request, VitalSign $vitalSign, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'data' => $vitalSign->load(['vitalSignType', 'morphable', 'createdBy']),
            ], 201);
        }

        if ($redirect = $this->managePageRedirect($vitalSign)) {
            return $redirect->with('success', $message);
        }

        return redirect()->route('vital-signs.index')->with('success', $message);
    }
}
