<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\DentalChart;
use App\Models\DentistRegistration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait ManagesDentalCharts
{
    protected function authorizeDentalChartAccess($user): void
    {
        abort_unless($user->can('access-dentist-registrations'), 403);
    }

    protected function assignDentistIfMissing(DentistRegistration $dentistRegistration): void
    {
        $dentistRegistration->assignToCurrentDentistIfMissing();
    }

    /**
     * @return array<int, null|DentalChart>
     */
    protected function buildAllTeethMap(Collection $chartsByTooth): array
    {
        $allTeeth = [];
        foreach ([11, 12, 13, 14, 15, 16, 17, 18, 21, 22, 23, 24, 25, 26, 27, 28, 31, 32, 33, 34, 35, 36, 37, 38, 41, 42, 43, 44, 45, 46, 47, 48] as $tooth) {
            $allTeeth[$tooth] = null;
        }

        foreach ($chartsByTooth as $toothNumber => $chart) {
            $allTeeth[(int) $toothNumber] = $chart;
        }

        return $allTeeth;
    }

    protected function latestChartsQuery(DentistRegistration $dentistRegistration): Collection
    {
        return $dentistRegistration->dentalCharts()
            ->with(['images', 'periodontalMeasurements'])
            ->orderByDesc('chart_date')
            ->orderByDesc('created_at')
            ->get()
            ->unique('tooth_number')
            ->values();
    }

    protected function chartValidationRules(Request $request, bool $creating): array
    {
        $isImplant = $request->input('tooth_condition') === 'implant';

        $rules = [
            'tooth_condition' => 'required|in:healthy,cavity,filling,crown,bridge,extraction,missing,impacted,root_canal,implant,decay,fractured',
            'gum_health' => 'nullable|in:healthy,gingivitis,periodontitis,recession,bleeding',
            'oral_hygiene_score' => 'nullable|numeric|min:0|max:10',
            'pocket_depth' => 'nullable|numeric|min:0|max:20',
            'bleeding' => 'nullable|boolean',
            'mobility' => 'nullable|in:none,grade1,grade2,grade3',
            'treatment_history' => 'nullable|string',
            'notes' => 'nullable|string',
        ];

        if ($creating) {
            $rules['tooth_number'] = 'required|integer|min:11|max:48';
        }

        if ($isImplant) {
            $rules = array_merge($rules, [
                'implant_system_brand' => 'nullable|string|max:255',
                'implant_diameter' => 'nullable|numeric|min:0',
                'implant_length' => 'nullable|numeric|min:0',
                'implant_status' => 'nullable|in:planned,placed,failed,removed',
                'implant_notes' => 'nullable|string',
            ]);
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function chartPayloadFromValidated(array $validated, ?DentalChart $existing = null): array
    {
        $isImplant = ($validated['tooth_condition'] ?? null) === 'implant';
        $payload = collect($validated)->only([
            'tooth_number',
            'tooth_condition',
            'gum_health',
            'oral_hygiene_score',
            'pocket_depth',
            'bleeding',
            'mobility',
            'treatment_history',
            'notes',
        ])->toArray();

        if (array_key_exists('bleeding', $payload)) {
            $payload['bleeding'] = (bool) $payload['bleeding'];
        }

        if ($isImplant) {
            $implantDate = $existing?->implant_details['implant_date']
                ?? ($existing?->chart_date?->format('Y-m-d') ?? now()->format('Y-m-d'));

            $implantDetails = array_filter([
                'implant_date' => $implantDate,
                'implant_system_brand' => $validated['implant_system_brand'] ?? null,
                'implant_diameter' => $validated['implant_diameter'] ?? null,
                'implant_length' => $validated['implant_length'] ?? null,
                'implant_status' => $validated['implant_status'] ?? null,
                'implant_notes' => $validated['implant_notes'] ?? null,
            ], fn ($value) => ! is_null($value) && $value !== '');

            $payload['measurements'] = array_merge($existing?->measurements ?? [], [
                'implant' => $implantDetails,
            ]);
        } elseif ($existing) {
            $measurements = $existing->measurements ?? [];
            unset($measurements['implant']);
            $payload['measurements'] = $measurements;
        }

        return $payload;
    }

    protected function transformChart(DentalChart $chart, bool $detailed = false): array
    {
        $implant = $chart->implant_details;

        $data = [
            'id' => $chart->id,
            'dentist_registration_id' => $chart->dentist_registration_id,
            'tooth_number' => $chart->tooth_number,
            'tooth_condition' => $chart->tooth_condition,
            'gum_health' => $chart->gum_health,
            'oral_hygiene_score' => $chart->oral_hygiene_score,
            'pocket_depth' => $chart->pocket_depth,
            'bleeding' => (bool) $chart->bleeding,
            'mobility' => $chart->mobility,
            'treatment_history' => $chart->treatment_history,
            'notes' => $chart->notes,
            'chart_date' => $chart->chart_date ? verta($chart->chart_date)->format('Y-m-d') : null,
            'implant_system_brand' => $implant['implant_system_brand'] ?? null,
            'implant_diameter' => $implant['implant_diameter'] ?? null,
            'implant_length' => $implant['implant_length'] ?? null,
            'implant_status' => $implant['implant_status'] ?? null,
            'implant_notes' => $implant['implant_notes'] ?? null,
        ];

        if ($detailed) {
            $data['created_by_name'] = $chart->creator?->name;
            $data['images_count'] = $chart->relationLoaded('images') ? $chart->images->count() : 0;
            $data['periodontal_count'] = $chart->relationLoaded('periodontalMeasurements')
                ? $chart->periodontalMeasurements->count()
                : 0;
        }

        return $data;
    }

    protected function transformRegistrationHeader(DentistRegistration $dentistRegistration): array
    {
        $patient = $dentistRegistration->appointment?->patient;

        return [
            'id' => $dentistRegistration->id,
            'ref_no' => $dentistRegistration->ref_no,
            'patient_name' => trim(($patient?->name ?? '').' '.($patient?->last_name ?? '')) ?: null,
            'dentist_name' => $dentistRegistration->dentist?->name,
        ];
    }

    protected function applyChartIndexFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['tooth_number'])) {
            $query->where('tooth_number', $filters['tooth_number']);
        }

        if (! empty($filters['tooth_condition'])) {
            $query->where('tooth_condition', $filters['tooth_condition']);
        }

        return $query->orderByDesc('chart_date')->orderBy('tooth_number');
    }
}
