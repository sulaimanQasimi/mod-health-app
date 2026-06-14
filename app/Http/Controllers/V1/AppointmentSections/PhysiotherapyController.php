<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PhysiotherapyProcedure;
use App\Models\PhysiotherapyType;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PhysiotherapyController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function meta(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);

        if (! request()->user()->can('show-physiotherapy-procedures')) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'physiotherapy_types' => PhysiotherapyType::query()
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn (PhysiotherapyType $type) => [
                        'id' => $type->id,
                        'name' => $type->name,
                    ])
                    ->values()
                    ->all(),
                'physiotherapists' => $this->physiotherapistDoctors($appointment)
                    ->map(fn (Doctor $doctor) => [
                        'id' => $doctor->id,
                        'name' => $doctor->name,
                    ])
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        if (! $user->can('show-physiotherapy-procedures')) {
            return $this->sectionIndexResponse([], $appointment, ['view' => false]);
        }

        $items = $appointment->physiotherapyProcedures()
            ->with(['physiotherapyType:id,name', 'doctor:id,name', 'reviews:id,physiotherapy_procedure_id'])
            ->latest()
            ->get()
            ->map(function (PhysiotherapyProcedure $item) {
                $percentage = $item->days_count > 0
                    ? ($item->counter / max(1, $item->days_count)) * 100
                    : 0;

                return [
                    'id' => $item->id,
                    'type_name' => $item->physiotherapyType?->name,
                    'physiotherapist_name' => $item->doctor?->name,
                    'type' => $item->type,
                    'duration' => $item->duration,
                    'days_count' => $item->days_count,
                    'counter' => $item->counter,
                    'progress_counter' => $item->counter,
                    'progress_total' => $item->days_count,
                    'progress_percentage' => round($percentage, 1),
                    'status' => $item->status,
                    'start_date' => $item->start_date ? verta($item->start_date)->format('Y-m-d') : null,
                    'end_date' => $item->end_date ? verta($item->end_date)->format('Y-m-d') : null,
                    'reviews_count' => $item->reviews->count(),
                    'urls' => [
                        'show' => route('react.physiotherapy-procedures.show', $item->id),
                    ],
                ];
            })
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'view' => true,
            'create' => $this->canMutateAppointment($appointment) && $user->can('create-physiotherapy-procedures'),
        ]);
    }

    public function show(Appointment $appointment, PhysiotherapyProcedure $physiotherapyProcedure): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(
            (int) $physiotherapyProcedure->appointment_id === (int) $appointment->id,
            404,
        );

        $this->authorize('view', $physiotherapyProcedure);

        $physiotherapyProcedure->load([
            'physiotherapyType:id,name',
            'doctor:id,name',
            'appointment.patient:id,name,last_name',
            'reviews',
            'createdBy:id,name,last_name',
        ]);

        $percentage = $physiotherapyProcedure->days_count > 0
            ? ($physiotherapyProcedure->counter / max(1, $physiotherapyProcedure->days_count)) * 100
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $physiotherapyProcedure->id,
                'physiotherapy_type_name' => $physiotherapyProcedure->physiotherapyType?->name,
                'physiotherapist_name' => $physiotherapyProcedure->doctor?->name,
                'patient_name' => trim(
                    ($physiotherapyProcedure->appointment?->patient?->name ?? '').' '
                    .($physiotherapyProcedure->appointment?->patient?->last_name ?? ''),
                ) ?: '—',
                'type' => $physiotherapyProcedure->type,
                'duration' => $physiotherapyProcedure->duration,
                'days_count' => $physiotherapyProcedure->days_count,
                'counter' => $physiotherapyProcedure->counter,
                'progress_percentage' => round($percentage, 1),
                'description' => $physiotherapyProcedure->description,
                'notes' => $physiotherapyProcedure->notes,
                'status' => $physiotherapyProcedure->status,
                'start_date' => $physiotherapyProcedure->start_date
                    ? verta($physiotherapyProcedure->start_date)->format('Y-m-d')
                    : null,
                'end_date' => $physiotherapyProcedure->end_date
                    ? verta($physiotherapyProcedure->end_date)->format('Y-m-d')
                    : null,
                'reviews_count' => $physiotherapyProcedure->reviews->count(),
                'created_by_name' => $physiotherapyProcedure->createdBy
                    ? trim("{$physiotherapyProcedure->createdBy->name} {$physiotherapyProcedure->createdBy->last_name}")
                    : null,
                'created_at' => $physiotherapyProcedure->created_at
                    ? verta($physiotherapyProcedure->created_at)->format('Y-m-d H:i')
                    : null,
                'urls' => [
                    'show' => route('react.physiotherapy-procedures.show', $physiotherapyProcedure->id),
                ],
            ],
        ]);
    }

    public function store(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $this->assertAppointmentMutable($appointment);
        $this->authorize('create', PhysiotherapyProcedure::class);

        $physiotherapistIds = $this->physiotherapistDoctors($appointment)->pluck('id')->all();

        $validated = $request->validate([
            'physiotherapy_type_id' => 'required|exists:physiotherapy_types,id',
            'doctor_id' => ['required', Rule::in($physiotherapistIds)],
            'type' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'days_count' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'start_date' => 'required|string',
            'end_date' => 'nullable|string',
        ]);

        $procedure = PhysiotherapyProcedure::create([
            'appointment_id' => $appointment->id,
            'physiotherapy_type_id' => $validated['physiotherapy_type_id'],
            'doctor_id' => $validated['doctor_id'],
            'type' => $validated['type'],
            'duration' => $validated['duration'],
            'counter' => 0,
            'days_count' => $validated['days_count'],
            'description' => $validated['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'start_date' => Verta::parse($validated['start_date'])->datetime(),
            'end_date' => ! empty($validated['end_date'])
                ? Verta::parse($validated['end_date'])->datetime()
                : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => localize('global.physiotherapy_procedure_created_successfully'),
            'data' => ['id' => $procedure->id],
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, Doctor>
     */
    private function physiotherapistDoctors(Appointment $appointment)
    {
        $roleQuery = Doctor::query()
            ->where('active_status', true)
            ->where('branch_id', $appointment->branch_id)
            ->whereHas('user.roles', function ($role) {
                $role->where('name', 'physiotherapist');
            });

        $doctors = $roleQuery->orderBy('name')->get();

        if ($doctors->isNotEmpty()) {
            return $doctors;
        }

        return Doctor::query()
            ->where('active_status', true)
            ->where('branch_id', $appointment->branch_id)
            ->orderBy('name')
            ->get();
    }
}
