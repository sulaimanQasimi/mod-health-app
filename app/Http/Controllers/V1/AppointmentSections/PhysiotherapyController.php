<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;

class PhysiotherapyController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        if (! $user->can('show-physiotherapy-procedures')) {
            return $this->sectionIndexResponse([], $appointment, ['view' => false]);
        }

        $items = $appointment->physiotherapyProcedures()
            ->with(['physiotherapyType:id,name', 'physiotherapist:id,name'])
            ->latest()
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'type_name' => $item->physiotherapyType?->name,
                'physiotherapist_name' => $item->physiotherapist?->name,
                'status' => $item->status,
                'progress' => $item->progress,
                'start_date' => $item->start_date ? verta($item->start_date)->format('Y-m-d') : null,
                'urls' => ['show' => route('physiotherapy-procedures.show', $item->id)],
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'create' => ! $appointment->is_completed && $user->can('create-physiotherapy-procedures'),
        ], [
            'urls' => ['store' => route('physiotherapy-procedures.store')],
        ]);
    }
}
