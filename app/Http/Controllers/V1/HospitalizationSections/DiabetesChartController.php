<?php

namespace App\Http\Controllers\V1\HospitalizationSections;

use App\Http\Controllers\Controller;
use App\Models\DiabetesChart;
use App\Models\Hospitalization;
use App\Models\Medicine;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DiabetesChartController extends Controller
{
    private const CHARTABLE_TYPE = Hospitalization::class;

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
                    'permissions' => [
                        'view' => false,
                        'create' => false,
                        'edit' => false,
                        'delete' => false,
                    ],
                    'urls' => ['print' => null],
                ],
            ]);
        }

        $items = DiabetesChart::query()
            ->where('diabetes_chartable_type', self::CHARTABLE_TYPE)
            ->where('diabetes_chartable_id', $hospitalization->id)
            ->with(['nurse:id,first_name,last_name', 'medicine:id,name'])
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->get()
            ->map(fn (DiabetesChart $chart) => $this->formatChart($chart))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'count' => count($items),
                'permissions' => $this->permissions($user, $hospitalization),
                'urls' => [
                    'print' => count($items) > 0
                        ? route('diabetes-charts.print', [
                            'chartable_type' => self::CHARTABLE_TYPE,
                            'chartable_id' => $hospitalization->id,
                        ])
                        : null,
                ],
            ],
        ]);
    }

    public function meta(Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);

        $user = request()->user();
        abort_unless(
            $this->canView($user) && (
                $user->can('create', DiabetesChart::class)
                || $user->hasPermissionTo('edit-diabetes-charts')
                || $user->hasRole(['super_admin', 'admin', 'hr', 'nurse'])
            ),
            403,
        );

        $nurse = request()->user()->nurse;

        return response()->json([
            'success' => true,
            'data' => [
                'medicines' => Medicine::query()->orderBy('name')->get(['id', 'name']),
                'unit_options' => ['mg/dl', 'mmol/l'],
                'default_date' => verta()->format('Y/m/d'),
                'current_nurse' => $nurse ? [
                    'id' => $nurse->id,
                    'name' => $nurse->full_name,
                ] : null,
            ],
        ]);
    }

    public function show(Hospitalization $hospitalization, DiabetesChart $diabetesChart): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        $this->ensureChartBelongsToHospitalization($hospitalization, $diabetesChart);
        abort_unless($this->canView(request()->user()), 403);

        $diabetesChart->load(['nurse:id,first_name,last_name', 'medicine:id,name']);

        return response()->json([
            'success' => true,
            'data' => $this->formatChart($diabetesChart),
        ]);
    }

    public function store(Request $request, Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);
        $this->authorize('create', DiabetesChart::class);

        $nurse = $request->user()->nurse;
        abort_unless($nurse, 403);

        $validated = $request->validate($this->validationRules());

        DiabetesChart::create([
            ...$this->chartPayload($validated),
            'nurse_id' => $nurse->id,
            'diabetes_chartable_type' => self::CHARTABLE_TYPE,
            'diabetes_chartable_id' => $hospitalization->id,
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Hospitalization $hospitalization, DiabetesChart $diabetesChart): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        $this->ensureChartBelongsToHospitalization($hospitalization, $diabetesChart);
        $this->authorize('update', $diabetesChart);

        $validated = $request->validate($this->validationRules());

        $diabetesChart->update($this->chartPayload($validated));

        return response()->json(['success' => true]);
    }

    public function destroy(Hospitalization $hospitalization, DiabetesChart $diabetesChart): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        $this->ensureChartBelongsToHospitalization($hospitalization, $diabetesChart);
        $this->authorize('delete', $diabetesChart);

        $diabetesChart->delete();

        return response()->json(['success' => true]);
    }

    private function ensureAccessible(Hospitalization $hospitalization): void
    {
        abort_unless($hospitalization->userCanView(request()->user()), 404);
    }

    private function ensureChartBelongsToHospitalization(
        Hospitalization $hospitalization,
        DiabetesChart $diabetesChart,
    ): void {
        abort_unless(
            $diabetesChart->diabetes_chartable_type === self::CHARTABLE_TYPE
            && (int) $diabetesChart->diabetes_chartable_id === (int) $hospitalization->id,
            404,
        );
    }

    private function canView($user): bool
    {
        return $user?->can('viewAny', DiabetesChart::class) ?? false;
    }

    /**
     * @return array{view: bool, create: bool, edit: bool, delete: bool}
     */
    private function permissions($user, Hospitalization $hospitalization): array
    {
        return [
            'view' => $this->canView($user),
            'create' => ! (bool) $hospitalization->is_discharged && $user->can('create', DiabetesChart::class),
            'edit' => $user->can('create', DiabetesChart::class)
                || $user->can('edit-diabetes-charts')
                || $user->hasRole(['super_admin', 'admin', 'hr', 'nurse']),
            'delete' => $user->can('delete-diabetes-charts')
                || $user->hasRole(['super_admin', 'admin', 'hr', 'nurse']),
        ];
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    private function validationRules(): array
    {
        return [
            'medicine_id' => 'nullable|exists:medicines,id',
            'insulin_dose' => 'nullable|numeric|min:0|max:999.99',
            'rbs' => 'nullable|numeric|min:0|max:999.99',
            'fbs' => 'nullable|numeric|min:0|max:999.99',
            'unit' => 'nullable|string|max:20',
            'time' => 'nullable|date_format:H:i',
            'date' => 'required|string',
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function chartPayload(array $validated): array
    {
        return [
            'medicine_id' => $validated['medicine_id'] ?? null,
            'insulin_dose' => $validated['insulin_dose'] ?? null,
            'rbs' => $validated['rbs'] ?? null,
            'fbs' => $validated['fbs'] ?? null,
            'unit' => $validated['unit'] ?? null,
            'time' => $validated['time'] ?? null,
            'date' => $this->parseDate($validated['date'])->toDateString(),
        ];
    }

    private function parseDate(string $date): Carbon
    {
        try {
            return Verta::parse($date)->datetime();
        } catch (\Throwable) {
            abort(422, 'Invalid date format.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formatChart(DiabetesChart $chart): array
    {
        return [
            'id' => $chart->id,
            'date' => $chart->date ? verta($chart->date)->format('Y/m/d') : null,
            'time' => $chart->formatted_time,
            'rbs' => $chart->rbs,
            'fbs' => $chart->fbs,
            'insulin_dose' => $chart->insulin_dose,
            'unit' => $chart->unit,
            'nurse_name' => $chart->nurse?->full_name,
            'medicine_name' => $chart->medicine?->name,
            'medicine_id' => $chart->medicine_id,
        ];
    }
}
