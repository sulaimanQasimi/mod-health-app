<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DentalChartController as LegacyDentalChartController;
use App\Http\Controllers\V1\Concerns\ManagesDentalCharts;
use App\Models\DentalChart;
use App\Models\DentistRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DentalChartController extends Controller
{
    use ManagesDentalCharts;

    public function index(Request $request, DentistRegistration $dentistRegistration): Response
    {
        $this->authorizeDentalChartAccess($request->user());
        $this->assignDentistIfMissing($dentistRegistration);

        $dentistRegistration->load(['appointment.patient:id,name,last_name', 'dentist:id,name']);

        $filters = $request->only(['tooth_number', 'tooth_condition', 'per_page']);
        $query = $this->applyChartIndexFilters(
            $dentistRegistration->dentalCharts()->with('creator:id,name'),
            $filters,
        );

        $perPage = (int) ($filters['per_page'] ?? 32);
        $perPage = in_array($perPage, [16, 32, 48], true) ? $perPage : 32;
        $paginator = $query->paginate($perPage)->withQueryString();

        return Inertia::render('DentalCharts/Index', [
            'registration' => $this->transformRegistrationHeader($dentistRegistration),
            'charts' => [
                'data' => collect($paginator->items())
                    ->map(fn (DentalChart $chart) => $this->transformChart($chart, true))
                    ->values()
                    ->all(),
                'links' => $paginator->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
            'filters' => array_merge(['tooth_number' => '', 'tooth_condition' => '', 'per_page' => '32'], $filters),
            'urls' => $this->chartUrls($dentistRegistration),
        ]);
    }

    public function create(DentistRegistration $dentistRegistration): Response
    {
        $this->authorizeDentalChartAccess(request()->user());
        $this->assignDentistIfMissing($dentistRegistration);
        $dentistRegistration->load(['appointment.patient:id,name,last_name', 'dentist:id,name']);

        return Inertia::render('DentalCharts/Create', [
            'registration' => $this->transformRegistrationHeader($dentistRegistration),
            'urls' => $this->chartUrls($dentistRegistration),
        ]);
    }

    public function store(Request $request, DentistRegistration $dentistRegistration): RedirectResponse
    {
        $this->authorizeDentalChartAccess($request->user());
        $this->assignDentistIfMissing($dentistRegistration);

        $validated = $request->validate($this->chartValidationRules($request, true));
        $payload = $this->chartPayloadFromValidated($validated);
        $payload['chart_date'] = now()->format('Y-m-d');
        $payload['dentist_registration_id'] = $dentistRegistration->id;

        DentalChart::create($payload);

        return redirect()
            ->route('react.dentist-registrations.show', $dentistRegistration)
            ->with('success', localize('global.dental_chart_created_successfully'));
    }

    public function edit(DentalChart $dentalChart): Response
    {
        $this->authorizeDentalChartAccess(request()->user());
        $dentalChart->load(['dentistRegistration.appointment.patient:id,name,last_name', 'dentistRegistration.dentist:id,name']);
        $this->assignDentistIfMissing($dentalChart->dentistRegistration);

        return Inertia::render('DentalCharts/Edit', [
            'registration' => $this->transformRegistrationHeader($dentalChart->dentistRegistration),
            'chart' => $this->transformChart($dentalChart, true),
            'urls' => $this->chartUrls($dentalChart->dentistRegistration, $dentalChart),
        ]);
    }

    public function update(Request $request, DentalChart $dentalChart): RedirectResponse
    {
        $this->authorizeDentalChartAccess($request->user());
        $this->assignDentistIfMissing($dentalChart->dentistRegistration);

        $validated = $request->validate($this->chartValidationRules($request, false));
        $dentalChart->update($this->chartPayloadFromValidated($validated, $dentalChart));

        return redirect()
            ->route('react.dentist-registrations.show', $dentalChart->dentist_registration_id)
            ->with('success', localize('global.dental_chart_updated_successfully'));
    }

    public function destroy(DentalChart $dentalChart): RedirectResponse
    {
        $this->authorizeDentalChartAccess(request()->user());
        $registrationId = $dentalChart->dentist_registration_id;
        $dentalChart->delete();

        return redirect()
            ->route('react.dentist-registrations.show', $registrationId)
            ->with('success', localize('global.dental_chart_deleted_successfully'));
    }

    public function history(Request $request, DentistRegistration $dentistRegistration): Response
    {
        $this->authorizeDentalChartAccess($request->user());
        $this->assignDentistIfMissing($dentistRegistration);
        $dentistRegistration->load(['appointment.patient:id,name,last_name', 'dentist:id,name']);

        $chartDates = $dentistRegistration->dentalCharts()
            ->select('chart_date')
            ->distinct()
            ->orderByDesc('chart_date')
            ->get()
            ->map(fn ($row) => verta($row->chart_date)->format('Y-m-d'))
            ->values()
            ->all();

        $selectedDate = $request->get('date', $chartDates[0] ?? null);

        $charts = collect();
        if ($selectedDate) {
            $charts = $dentistRegistration->dentalCharts()
                ->whereDate('chart_date', \Hekmatinasser\Verta\Facades\Verta::parse($selectedDate)->datetime())
                ->with('creator:id,name')
                ->orderBy('tooth_number')
                ->get()
                ->map(fn (DentalChart $chart) => $this->transformChart($chart, true));
        }

        $timeline = $dentistRegistration->dentalCharts()
            ->selectRaw('chart_date, COUNT(*) as teeth_count')
            ->groupBy('chart_date')
            ->orderByDesc('chart_date')
            ->get()
            ->map(fn ($row) => [
                'date' => verta($row->chart_date)->format('Y-m-d'),
                'teeth_count' => (int) $row->teeth_count,
            ])
            ->values()
            ->all();

        return Inertia::render('DentalCharts/History', [
            'registration' => $this->transformRegistrationHeader($dentistRegistration),
            'chartDates' => $chartDates,
            'selectedDate' => $selectedDate,
            'charts' => $charts->values()->all(),
            'timeline' => $timeline,
            'urls' => $this->chartUrls($dentistRegistration),
        ]);
    }

    public function compare(Request $request, DentistRegistration $dentistRegistration): Response
    {
        $this->authorizeDentalChartAccess($request->user());
        $this->assignDentistIfMissing($dentistRegistration);
        $dentistRegistration->load(['appointment.patient:id,name,last_name', 'dentist:id,name']);

        $chartDates = $dentistRegistration->dentalCharts()
            ->select('chart_date')
            ->distinct()
            ->orderByDesc('chart_date')
            ->get()
            ->map(fn ($row) => verta($row->chart_date)->format('Y-m-d'))
            ->values()
            ->all();

        $date1 = $request->get('date1', $chartDates[0] ?? null);
        $date2 = $request->get('date2', $chartDates[1] ?? $chartDates[0] ?? null);

        $loadForDate = function (?string $date) use ($dentistRegistration) {
            if (! $date) {
                return collect();
            }

            return $dentistRegistration->dentalCharts()
                ->whereDate('chart_date', \Hekmatinasser\Verta\Facades\Verta::parse($date)->datetime())
                ->orderBy('tooth_number')
                ->get()
                ->mapWithKeys(fn (DentalChart $chart) => [$chart->tooth_number => $this->transformChart($chart)]);
        };

        $charts1 = $loadForDate($date1);
        $charts2 = $loadForDate($date2);

        $toothNumbers = collect([11, 12, 13, 14, 15, 16, 17, 18, 21, 22, 23, 24, 25, 26, 27, 28, 31, 32, 33, 34, 35, 36, 37, 38, 41, 42, 43, 44, 45, 46, 47, 48]);

        $comparison = $toothNumbers->map(function (int $tooth) use ($charts1, $charts2) {
            $left = $charts1->get($tooth);
            $right = $charts2->get($tooth);

            return [
                'tooth_number' => $tooth,
                'date1' => $left,
                'date2' => $right,
                'changed' => ($left['tooth_condition'] ?? null) !== ($right['tooth_condition'] ?? null),
            ];
        })->filter(fn ($row) => $row['date1'] || $row['date2'])->values()->all();

        return Inertia::render('DentalCharts/Compare', [
            'registration' => $this->transformRegistrationHeader($dentistRegistration),
            'chartDates' => $chartDates,
            'date1' => $date1,
            'date2' => $date2,
            'comparison' => $comparison,
            'urls' => $this->chartUrls($dentistRegistration),
        ]);
    }

    public function print(DentistRegistration $dentistRegistration): mixed
    {
        $this->authorizeDentalChartAccess(request()->user());

        return app(LegacyDentalChartController::class)->printView($dentistRegistration);
    }

    public function export(DentistRegistration $dentistRegistration): StreamedResponse|\Illuminate\View\View
    {
        $this->authorizeDentalChartAccess(request()->user());

        return app(LegacyDentalChartController::class)->exportPdf($dentistRegistration);
    }

    /**
     * @return array<string, string>
     */
    private function chartUrls(DentistRegistration $dentistRegistration, ?DentalChart $dentalChart = null): array
    {
        $urls = [
            'registrationShow' => route('react.dentist-registrations.show', $dentistRegistration),
            'index' => route('react.dental-charts.index', $dentistRegistration),
            'create' => route('react.dental-charts.create', $dentistRegistration),
            'store' => route('react.dental-charts.store', $dentistRegistration),
            'history' => route('react.dental-charts.history', $dentistRegistration),
            'compare' => route('react.dental-charts.compare', $dentistRegistration),
            'print' => route('react.dental-charts.print', $dentistRegistration),
            'export' => route('react.dental-charts.export', $dentistRegistration),
        ];

        if ($dentalChart) {
            $urls['update'] = route('react.dental-charts.update', $dentalChart);
            $urls['destroy'] = route('react.dental-charts.destroy', $dentalChart);
            $urls['edit'] = route('react.dental-charts.edit', $dentalChart);
        }

        return $urls;
    }
}
