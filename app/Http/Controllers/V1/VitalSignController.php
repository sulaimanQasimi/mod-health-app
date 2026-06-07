<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Hospitalization;
use App\Models\UnderReview;
use App\Models\User;
use App\Models\VitalSign;
use App\Models\VitalSignType;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VitalSignController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = [
        'search',
        'vital_sign_type_id',
        'date_from',
        'date_to',
        'per_page',
    ];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', VitalSign::class);

        $query = VitalSign::query()
            ->with(['vitalSignType:id,name', 'morphable'])
            ->withCount('schedules');

        if ($request->filled('vital_sign_type_id')) {
            $query->where('vital_sign_type_id', $request->vital_sign_type_id);
        }

        if ($request->filled('date_from')) {
            try {
                $query->whereDate('created_at', '>=', Verta::parse($request->date_from)->datetime());
            } catch (\Throwable) {
                // Ignore invalid jalali date filter input.
            }
        }

        if ($request->filled('date_to')) {
            try {
                $query->whereDate('created_at', '<=', Verta::parse($request->date_to)->datetime());
            } catch (\Throwable) {
                // Ignore invalid jalali date filter input.
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('morphable_type', 'like', "%{$search}%")
                    ->orWhere('morphable_id', 'like', "%{$search}%")
                    ->orWhereHasMorph(
                        'morphable',
                        [Hospitalization::class, UnderReview::class],
                        function ($morphQuery) use ($search) {
                            $morphQuery->whereHas('patient', function ($patientQuery) use ($search) {
                                $patientQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%");
                            });
                        },
                    );
            });
        }

        $paginator = $this->paginateQuery(
            $query->orderByDesc('created_at'),
            $request,
        );

        return Inertia::render('VitalSigns/Index', [
            'vitalSigns' => $this->paginationPayload($paginator, fn (VitalSign $vitalSign) => [
                'id' => $vitalSign->id,
                'vital_sign_type_id' => $vitalSign->vital_sign_type_id,
                'vital_sign_type_name' => $vitalSign->vitalSignType?->name,
                'morphable_type' => $vitalSign->morphable_type,
                'morphable_id' => $vitalSign->morphable_id,
                'morphable_label' => $this->formatMorphableLabel($vitalSign),
                'schedules_count' => $vitalSign->schedules_count,
                'created_at' => $vitalSign->created_at
                    ? verta($vitalSign->created_at)->format('Y/m/d H:i')
                    : null,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'vitalSignTypes' => VitalSignType::query()->orderBy('name')->get(['id', 'name']),
            ],
            'permissions' => $this->vitalSignPermissions($request->user()),
            'urls' => [
                'index' => route('react.vital-signs.index'),
                'show' => url('/react/vital-signs'),
            ],
        ]);
    }

    public function show(Request $request, VitalSign $vitalSign): Response
    {
        $this->authorize('view', $vitalSign);

        $vitalSign->load([
            'vitalSignType:id,name',
            'morphable.patient:id,name,last_name',
            'schedules.nurse:id,first_name,last_name',
            'createdBy:id,name,last_name',
            'updatedBy:id,name,last_name',
        ]);

        return Inertia::render('VitalSigns/Show', [
            'vitalSign' => [
                'id' => $vitalSign->id,
                'vital_sign_type' => $vitalSign->vitalSignType ? [
                    'id' => $vitalSign->vitalSignType->id,
                    'name' => $vitalSign->vitalSignType->name,
                ] : null,
                'morphable_type' => $vitalSign->morphable_type,
                'morphable_id' => $vitalSign->morphable_id,
                'morphable_label' => $this->formatMorphableLabel($vitalSign),
                'morphable' => $this->transformMorphable($vitalSign),
                'schedules' => $vitalSign->schedules->map(fn ($schedule) => [
                    'id' => $schedule->id,
                    'nurse_name' => $schedule->nurse?->full_name,
                ])->values()->all(),
                'schedules_count' => $vitalSign->schedules->count(),
                'created_by_name' => $vitalSign->createdBy
                    ? trim($vitalSign->createdBy->name.' '.($vitalSign->createdBy->last_name ?? ''))
                    : null,
                'updated_by_name' => $vitalSign->updatedBy
                    ? trim($vitalSign->updatedBy->name.' '.($vitalSign->updatedBy->last_name ?? ''))
                    : null,
                'created_at' => $vitalSign->created_at
                    ? verta($vitalSign->created_at)->format('Y/m/d H:i')
                    : null,
                'updated_at' => $vitalSign->updated_at
                    ? verta($vitalSign->updated_at)->format('Y/m/d H:i')
                    : null,
            ],
            'permissions' => [
                'view' => $request->user()->can('view', $vitalSign),
            ],
            'urls' => [
                'index' => route('react.vital-signs.index'),
            ],
        ]);
    }

    /**
     * @return array<string, bool>
     */
    private function vitalSignPermissions(User $user): array
    {
        return [
            'view' => $user->can('viewAny', VitalSign::class),
        ];
    }

    private function formatMorphableLabel(VitalSign $vitalSign): ?string
    {
        if (! $vitalSign->morphable_type || ! $vitalSign->morphable_id) {
            return null;
        }

        $typeLabel = class_basename($vitalSign->morphable_type);
        $patientName = $this->morphablePatientName($vitalSign);

        if ($patientName) {
            return "{$typeLabel} #{$vitalSign->morphable_id} — {$patientName}";
        }

        return "{$typeLabel} #{$vitalSign->morphable_id}";
    }

    private function morphablePatientName(VitalSign $vitalSign): ?string
    {
        $morphable = $vitalSign->morphable;

        if (! $morphable || ! $morphable->relationLoaded('patient') || ! $morphable->patient) {
            if ($morphable && method_exists($morphable, 'patient')) {
                $morphable->loadMissing('patient:id,name,last_name');
            }
        }

        if (! $morphable?->patient) {
            return null;
        }

        return trim($morphable->patient->name.' '.($morphable->patient->last_name ?? ''));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function transformMorphable(VitalSign $vitalSign): ?array
    {
        if (! $vitalSign->morphable) {
            return null;
        }

        $patient = $vitalSign->morphable->patient ?? null;

        return [
            'type' => class_basename($vitalSign->morphable_type),
            'id' => $vitalSign->morphable_id,
            'patient' => $patient ? [
                'id' => $patient->id,
                'name' => trim($patient->name.' '.($patient->last_name ?? '')),
            ] : null,
        ];
    }
}
