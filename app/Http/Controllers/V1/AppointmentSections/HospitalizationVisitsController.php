<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\FoodType;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class HospitalizationVisitsController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);

        $foodTypes = FoodType::query()->orderBy('name')->get(['id', 'name'])->keyBy('id');
        $items = collect();
        $appointment->load(['hospitalization.visits.doctor']);

        foreach ($appointment->hospitalization as $hospitalization) {
            foreach ($hospitalization->visits as $visit) {
                $items->push($this->formatVisit($visit, $foodTypes));
            }
        }

        return $this->sectionIndexResponse($items->values()->all(), $appointment, []);
    }

    /**
     * @param  Collection<int, FoodType>  $foodTypes
     * @return array<string, mixed>
     */
    private function formatVisit(Visit $visit, Collection $foodTypes): array
    {
        $foodTypeIds = $this->decodeFoodTypeIds($visit->food_type_id);
        $foodTypeNames = collect($foodTypeIds)
            ->map(fn (int $id) => $foodTypes->get($id)?->name)
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $visit->id,
            'description' => $visit->description,
            'doctor_name' => $visit->doctor?->name,
            'visit_date' => $visit->created_at ? verta($visit->created_at)->format('Y/m/d') : null,
            'visit_time' => $visit->created_at?->format('H:i'),
            'bp' => $visit->bp,
            'pr' => $visit->pr,
            'rr' => $visit->rr,
            't' => $visit->t,
            'spo2' => $visit->spo2,
            'pain' => $visit->pain,
            'antibiotic' => $visit->antibiotic,
            'food_type_names' => $foodTypeNames,
            'intake' => $visit->intake,
            'output' => $visit->output,
        ];
    }

    /**
     * @return list<int>
     */
    private function decodeFoodTypeIds(mixed $foodTypeValue): array
    {
        if ($foodTypeValue === null || $foodTypeValue === '') {
            return [];
        }

        if (is_array($foodTypeValue)) {
            return array_values(array_map('intval', $foodTypeValue));
        }

        if (is_numeric($foodTypeValue)) {
            return [(int) $foodTypeValue];
        }

        if (! is_string($foodTypeValue)) {
            return [];
        }

        $decoded = json_decode($foodTypeValue, true);
        if (is_array($decoded)) {
            return array_values(array_map('intval', $decoded));
        }

        if (ctype_digit($foodTypeValue)) {
            return [(int) $foodTypeValue];
        }

        return collect(explode(',', $foodTypeValue))
            ->map(fn (string $id) => (int) trim($id))
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();
    }
}
