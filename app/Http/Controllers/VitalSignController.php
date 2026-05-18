<?php

namespace App\Http\Controllers;

use App\Models\VitalSign;
use App\Models\VitalSignType;
use App\Models\UnderReview;
use App\Models\Hospitalization;
use App\Http\Requests\StoreVitalSignRequest;
use App\Http\Requests\StoreMultipleVitalSignsRequest;
use App\Models\VitalSignSchedule;
use App\Models\Nurse;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class VitalSignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', VitalSign::class);

        $query = VitalSign::with(['vitalSignType', 'morphable', 'schedules.nurse', 'createdBy']);

        // Apply filters
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
                // ignore invalid Persian date
            }
        }

        if ($request->filled('date_to')) {
            try {
                $query->whereDate('created_at', '<=', Verta::parse($request->date_to)->datetime());
            } catch (\Exception $e) {
                // ignore invalid Persian date
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
                ]
            ]);
        }

        $vitalSignTypes = VitalSignType::orderBy('name')->get();

        return view('pages.vital-signs.index', compact('vitalSigns', 'vitalSignTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', VitalSign::class);

        $vitalSignTypes = VitalSignType::orderBy('name')->get();
        $nurses = Nurse::orderBy('first_name')->get();
        $morphableType = $request->get('morphable_type');
        $morphableId = $request->get('morphable_id');
        $currentUserNurse = auth()->user()->nurse ?? null;
        $morphModel = null;

        if ($morphableType && $morphableId && class_exists($morphableType)) {
            $morphModel = $morphableType::with([
                'vitalSigns.vitalSignType',
                'vitalSigns.schedules' => fn ($q) => $q->orderBy('day'),
            ])->find($morphableId);
        }

        return view('pages.vital-signs.create', compact(
            'vitalSignTypes',
            'nurses',
            'morphableType',
            'morphableId',
            'currentUserNurse',
            'morphModel'
        ));
    }

    /**
     * Store a newly created resource in storage (single or multiple vital signs with schedules).
     */
    public function store(StoreMultipleVitalSignsRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', VitalSign::class);

        $morphableType = $request->input('morphable_type');
        $morphableId = (int) $request->input('morphable_id');
dd($morphableType, $morphableId, $request->all());
        if ($morphableType && $morphableId) {
            \DB::transaction(function () use ($request, $morphableType, $morphableId) {
                if ($request->filled('vital_signs') && is_array($request->vital_signs)) {
                    $this->storeMultipleVitalSignsWithSchedules($request->vital_signs, $morphableType, $morphableId);
                }
                $this->syncMorphableVitalSigns($request, $morphableType, $morphableId);
            });

            $message = 'Vital signs and schedules saved successfully.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 201);
            }

            return redirect()->route('vital-signs.create', [
                'morphable_type' => $morphableType,
                'morphable_id' => $morphableId,
            ])->with('success', $message);
        }

        if ($request->filled('vital_signs') && is_array($request->vital_signs)) {
            $created = $this->storeMultipleVitalSignsWithSchedules($request->vital_signs, $morphableType, $morphableId);
            $message = $created === 1
                ? 'Vital sign and schedules created successfully.'
                : "{$created} vital signs and their schedules created successfully.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 201);
            }

            return redirect()->route('vital-signs.index', ['morphable_type' => $morphableType, 'morphable_id' => $morphableId])
                ->with('success', $message);
        }

        // Single vital sign (legacy)
        $vitalSign = VitalSign::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vital sign created successfully.',
                'data' => $vitalSign->load(['vitalSignType', 'morphable', 'createdBy'])
            ], 201);
        }

        $morphable = $vitalSign->morphable;
        if ($morphable) {
            $routeName = $vitalSign->morphable_type == 'App\\Models\\Hospitalization'
                ? 'hospitalizations.show'
                : 'under_reviews.show';

            return redirect()->route($routeName, $morphable)
                ->with('success', 'Vital sign created successfully.');
        }

        return redirect()->route('vital-signs.index')
            ->with('success', 'Vital sign created successfully.');
    }

    /**
     * Create multiple vital signs with their schedules in a single transaction.
     *
     * @return int Number of vital signs created
     */
    private function storeMultipleVitalSignsWithSchedules(array $vitalSignsData, string $morphableType, int $morphableId): int
    {
        $count = 0;

        foreach ($vitalSignsData as $row) {
                $vitalSignTypeId = (int) ($row['vital_sign_type_id'] ?? 0);
                if ($vitalSignTypeId < 1) {
                    continue;
                }

                $vitalSign = VitalSign::create([
                    'vital_sign_type_id' => $vitalSignTypeId,
                    'morphable_type' => $morphableType,
                    'morphable_id' => $morphableId,
                ]);
                $count++;

                $schedules = $row['schedules'] ?? [];
                if (!is_array($schedules)) {
                    $schedules = [];
                }

                $existingDays = VitalSignSchedule::where('vital_sign_id', $vitalSign->id)
                    ->whereNotNull('day')
                    ->pluck('day')
                    ->toArray();
                $dayNumber = 1;
                while (in_array('Day ' . $dayNumber, $existingDays, true)) {
                    $dayNumber++;
                }

                $authNurse = auth()->user()->nurse;

                foreach ($schedules as $scheduleRow) {
                    $date = $scheduleRow['date'] ?? null;
                    $morningTime = $scheduleRow['morning_time'] ?? null;
                    $eveningTime = $scheduleRow['evening_time'] ?? null;

                    if (!$date && !$morningTime && !$eveningTime) {
                        continue;
                    }

                    // Backend selects nurse: use logged-in user's nurse when they have one
                    // When user is not a nurse, nurse_id will be null (optional)
                    $nurseId = $authNurse ? $authNurse->id : null;

                    VitalSignSchedule::create([
                        'vital_sign_id' => $vitalSign->id,
                        'day' => 'Day ' . $dayNumber,
                        'date' => $date ?: null,
                        'morning_time' => $morningTime ?: null,
                        'evening_time' => $eveningTime ?: null,
                        'nurse_id' => $nurseId,
                    ]);
                    $dayNumber++;
                    $existingDays[] = 'Day ' . ($dayNumber - 1);
                }
            }

        return $count;
    }

    /**
     * Update existing vital signs and schedules, and process deletions for a morphable record.
     */
    private function syncMorphableVitalSigns(StoreMultipleVitalSignsRequest $request, string $morphableType, int $morphableId): void
    {
        $deleteScheduleIds = array_map('intval', $request->input('delete_schedule_ids', []));
            if ($deleteScheduleIds !== []) {
                VitalSignSchedule::query()
                    ->whereIn('id', $deleteScheduleIds)
                    ->whereHas('vitalSign', fn ($q) => $q->where('morphable_type', $morphableType)->where('morphable_id', $morphableId))
                    ->each(function (VitalSignSchedule $schedule) {
                        $this->authorize('delete', $schedule);
                        $schedule->delete();
                    });
            }

            $deleteVitalSignIds = array_map('intval', $request->input('delete_vital_sign_ids', []));
            foreach ($deleteVitalSignIds as $vitalSignId) {
                $vitalSign = VitalSign::where('id', $vitalSignId)
                    ->where('morphable_type', $morphableType)
                    ->where('morphable_id', $morphableId)
                    ->first();
                if (!$vitalSign) {
                    continue;
                }
                $this->authorize('delete', $vitalSign);
                $vitalSign->schedules()->delete();
                $vitalSign->delete();
            }

            $existingRows = $request->input('existing_vital_signs', []);
            if (!is_array($existingRows)) {
                $existingRows = [];
            }

            $authNurse = auth()->user()->nurse;

            foreach ($existingRows as $row) {
                $vitalSignId = (int) ($row['id'] ?? 0);
                if ($vitalSignId < 1) {
                    continue;
                }

                $vitalSign = VitalSign::where('id', $vitalSignId)
                    ->where('morphable_type', $morphableType)
                    ->where('morphable_id', $morphableId)
                    ->first();

                if (!$vitalSign) {
                    continue;
                }

                $this->authorize('update', $vitalSign);
                $vitalSign->update([
                    'vital_sign_type_id' => (int) $row['vital_sign_type_id'],
                ]);

                $schedules = $row['schedules'] ?? [];
                if (!is_array($schedules)) {
                    continue;
                }

                $existingDays = VitalSignSchedule::where('vital_sign_id', $vitalSign->id)
                    ->whereNotNull('day')
                    ->pluck('day')
                    ->toArray();

                foreach ($schedules as $scheduleRow) {
                    $scheduleId = isset($scheduleRow['id']) ? (int) $scheduleRow['id'] : 0;
                    $date = $scheduleRow['date'] ?? null;
                    $morningTime = $scheduleRow['morning_time'] ?? null;
                    $eveningTime = $scheduleRow['evening_time'] ?? null;

                    if ($scheduleId > 0) {
                        $schedule = VitalSignSchedule::where('id', $scheduleId)
                            ->where('vital_sign_id', $vitalSign->id)
                            ->first();
                        if (!$schedule) {
                            continue;
                        }
                        $this->authorize('update', $schedule);
                        $schedule->update([
                            'date' => $date ?: null,
                            'morning_time' => $morningTime ?: null,
                            'evening_time' => $eveningTime ?: null,
                        ]);
                        continue;
                    }

                    if (!$date && !$morningTime && !$eveningTime) {
                        continue;
                    }

                    $dayNumber = 1;
                    while (in_array('Day ' . $dayNumber, $existingDays, true)) {
                        $dayNumber++;
                    }

                    VitalSignSchedule::create([
                        'vital_sign_id' => $vitalSign->id,
                        'day' => 'Day ' . $dayNumber,
                        'date' => $date ?: null,
                        'morning_time' => $morningTime ?: null,
                        'evening_time' => $eveningTime ?: null,
                        'nurse_id' => $authNurse ? $authNurse->id : null,
                    ]);
                    $existingDays[] = 'Day ' . $dayNumber;
                }
            }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, VitalSign $vitalSign): View|JsonResponse
    {
        $this->authorize('view', $vitalSign);

        $vitalSign->load([
            'vitalSignType',
            'morphable',
            'schedules.nurse',
            'createdBy',
            'updatedBy'
        ]);

        // Load nurses for the modal
        $nurses = \App\Models\Nurse::orderBy('first_name')->get();
        $currentUserNurse = auth()->user()->nurse;

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $vitalSign
            ]);
        }

        return view('pages.vital-signs.show', compact('vitalSign', 'nurses', 'currentUserNurse'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VitalSign $vitalSign): View
    {
        $this->authorize('update', $vitalSign);

        $vitalSignTypes = VitalSignType::orderBy('name')->get();

        return view('pages.vital-signs.edit', compact('vitalSign', 'vitalSignTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreVitalSignRequest $request, VitalSign $vitalSign): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $vitalSign);

        $vitalSign->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vital sign updated successfully.',
                'data' => $vitalSign->load(['vitalSignType', 'morphable', 'updatedBy'])
            ]);
        }

        return redirect()->route('vital-signs.index')
            ->with('success', 'Vital sign updated successfully.');
    }

    /**
     * Print the vital sign chart.
     */
    public function print($morphable_type, $morphable_id): View
    {
        // Load vital signs for the specific record
        $vitalSigns = VitalSign::with([
            'vitalSignType',
            'schedules' => function ($query) {
                $query->orderBy('day', 'asc');
            },
            'schedules.nurse',
            'morphable.patient'
        ])
            ->where('morphable_type', $morphable_type)
            ->where('morphable_id', $morphable_id)
            ->get();

        return view('pages.vital-signs.print', compact('vitalSigns'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, VitalSign $vitalSign): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $vitalSign);

        // Check if vital sign has associated schedules
        if ($vitalSign->schedules()->count() > 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cannot delete vital sign with associated schedules.',
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Cannot delete vital sign with associated schedules.');
        }

        $vitalSign->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vital sign deleted successfully.',
            ]);
        }

        return redirect()->route('vital-signs.index')
            ->with('success', 'Vital sign deleted successfully.');
    }
}
