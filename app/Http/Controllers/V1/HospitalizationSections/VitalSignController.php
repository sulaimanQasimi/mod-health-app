<?php

namespace App\Http\Controllers\V1\HospitalizationSections;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMultipleVitalSignsRequest;
use App\Models\Hospitalization;
use App\Models\VitalSign;
use App\Models\VitalSignSchedule;
use App\Models\VitalSignType;
use App\Services\VitalSignManageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VitalSignController extends Controller
{
    public function __construct(
        private readonly VitalSignManageService $vitalSignManage,
    ) {}

    private const MORPHABLE_TYPE = Hospitalization::class;

    public function index(Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);

        $user = request()->user();
        if (! $this->canView($user)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => [],
                    'count' => 0,
                    'permissions' => ['view' => false, 'manage' => false],
                ],
            ]);
        }

        $vitalSigns = VitalSign::query()
            ->with([
                'vitalSignType:id,name',
                'schedules' => fn ($q) => $q->with('nurse:id,first_name,last_name')->orderByDesc('date'),
            ])
            ->where('morphable_type', self::MORPHABLE_TYPE)
            ->where('morphable_id', $hospitalization->id)
            ->orderBy('id')
            ->get();

        $items = [];
        foreach ($vitalSigns as $vitalSign) {
            foreach ($vitalSign->schedules as $schedule) {
                $items[] = [
                    'schedule_id' => $schedule->id,
                    'vital_sign_id' => $vitalSign->id,
                    'type_name' => $vitalSign->vitalSignType?->name,
                    'date' => $schedule->date ? verta($schedule->date)->format('Y/m/d') : null,
                    'morning_time' => $schedule->morning_time,
                    'evening_time' => $schedule->evening_time,
                    'nurse_name' => $schedule->nurse
                        ? trim($schedule->nurse->first_name.' '.$schedule->nurse->last_name)
                        : null,
                ];
            }
        }

        usort($items, fn ($a, $b) => strcmp((string) $b['date'], (string) $a['date']));

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'count' => count($items),
                'permissions' => [
                    'view' => true,
                    'manage' => ! (bool) $hospitalization->is_discharged && $this->canManage($user, $hospitalization),
                ],
                'urls' => [
                    'print' => count($items) > 0
                        ? route('vital-signs.print', [self::MORPHABLE_TYPE, $hospitalization->id])
                        : null,
                ],
            ],
        ]);
    }

    public function meta(Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_unless($this->canManage(request()->user(), $hospitalization), 403);

        return response()->json([
            'success' => true,
            'data' => [
                'vital_sign_types' => VitalSignType::query()->orderBy('name')->get(['id', 'name']),
                'default_schedule_date' => verta()->format('Y/m/d'),
                'schedules_by_date' => $this->vitalSignManage->schedulesGroupedByPersianDate(
                    self::MORPHABLE_TYPE,
                    $hospitalization->id,
                ),
            ],
        ]);
    }

    public function store(StoreMultipleVitalSignsRequest $request, Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);

        $request->merge([
            'morphable_type' => self::MORPHABLE_TYPE,
            'morphable_id' => $hospitalization->id,
        ]);

        $this->authorizeManagePage($hospitalization);

        if ($request->isDailyScheduleRequest()) {
            foreach ($request->input('schedule_rows', []) as $row) {
                if (empty($row['vital_sign_type_id'])) {
                    continue;
                }

                $exists = VitalSign::query()
                    ->where('morphable_type', self::MORPHABLE_TYPE)
                    ->where('morphable_id', $hospitalization->id)
                    ->where('vital_sign_type_id', (int) $row['vital_sign_type_id'])
                    ->exists();

                if (! $exists) {
                    $this->authorize('create', VitalSign::class);
                    break;
                }
            }

            $this->vitalSignManage->syncDailyScheduleRows(
                self::MORPHABLE_TYPE,
                $hospitalization->id,
                $request->input('schedule_date'),
                $request->input('schedule_rows', []),
                $request->user()->nurse,
                fn (VitalSign|VitalSignSchedule $model) => $this->authorize('update', $model),
            );
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid vital sign payload.',
            ], 422);
        }

        return response()->json(['success' => true]);
    }

    private function ensureAccessible(Hospitalization $hospitalization): void
    {
        abort_unless($hospitalization->userCanView(request()->user()), 404);
    }

    private function canView($user): bool
    {
        return $user->can('viewAny', VitalSign::class);
    }

    private function canManage($user, Hospitalization $hospitalization): bool
    {
        if ($user->can('create', VitalSign::class)) {
            return true;
        }

        return VitalSign::query()
            ->where('morphable_type', self::MORPHABLE_TYPE)
            ->where('morphable_id', $hospitalization->id)
            ->get()
            ->contains(fn (VitalSign $vitalSign) => $user->can('update', $vitalSign));
    }

    private function authorizeManagePage(Hospitalization $hospitalization): void
    {
        $user = request()->user();

        if ($user->can('create', VitalSign::class)) {
            return;
        }

        $canUpdateAny = VitalSign::query()
            ->where('morphable_type', self::MORPHABLE_TYPE)
            ->where('morphable_id', $hospitalization->id)
            ->get()
            ->contains(fn (VitalSign $vs) => $user->can('update', $vs));

        if (! $canUpdateAny) {
            $this->authorize('create', VitalSign::class);
        }
    }
}
