<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Jobs\SendNewPACUNotification;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Hospitalization;
use App\Models\PACU;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PacuController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        if (! $this->canView($user)) {
            return $this->sectionIndexResponse([], $appointment, ['view' => false, 'create' => false]);
        }

        $items = $appointment->pacus()
            ->with(['patient:id,name'])
            ->latest()
            ->get()
            ->map(fn (PACU $item) => $this->formatListItem($item))
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'view' => true,
            'create' => $this->canMutateAppointment($appointment) && $user->can('refer-to-pacu'),
            'edit' => $this->canMutateAppointment($appointment) && $user->can('show-pacu-menu'),
            'delete' => $this->canMutateAppointment($appointment) && $user->can('show-pacu-menu'),
        ]);
    }

    public function meta(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($this->canMutateAppointment($appointment) && request()->user()->can('refer-to-pacu'), 403);

        $appointment->loadMissing('patient:id,name');
        $branchId = $appointment->branch_id ?? request()->user()->branch_id;

        return response()->json([
            'success' => true,
            'data' => [
                'patient_name' => $appointment->patient?->name,
                'default_department_id' => $appointment->department_id ?? request()->user()->department_id,
                'departments' => $this->departments($branchId),
            ],
        ]);
    }

    public function store(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($this->canMutateAppointment($appointment) && $request->user()->can('refer-to-pacu'), 403);

        $validated = $request->validate([
            'description' => 'required|string|max:2000',
            'department_id' => 'required|exists:departments,id',
        ]);

        $hospitalization = Hospitalization::query()
            ->where('appointment_id', $appointment->id)
            ->where(function ($query) {
                $query->where('is_discharged', 0)->orWhereNull('is_discharged');
            })
            ->latest('id')
            ->first();

        $pacu = PACU::create([
            'description' => $validated['description'],
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->id,
            'hospitalization_id' => $hospitalization?->id,
            'department_id' => $validated['department_id'],
            'branch_id' => $appointment->branch_id ?? $request->user()->branch_id,
            'status' => 'new',
        ]);

        SendNewPACUNotification::dispatch($request->user()->id, $pacu->id);

        return response()->json(['success' => true]);
    }

    public function destroy(Appointment $appointment, PACU $pacu): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('show-pacu-menu'), 403);
        $this->assertAppointmentMutable($appointment);
        abort_unless((int) $pacu->appointment_id === (int) $appointment->id, 404);

        $pacu->delete();

        return response()->json(['success' => true]);
    }

    private function canView($user): bool
    {
        return ($user?->can('refer-to-pacu') ?? false)
            || ($user?->can('show-pacu-menu') ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatListItem(PACU $pacu): array
    {
        return [
            'id' => $pacu->id,
            'patient_name' => $pacu->patient?->name,
            'description' => $pacu->description,
            'status' => $pacu->status,
            'created_at' => $this->formatDate($pacu->created_at),
            'urls' => [
                'show' => route('react.pacus.show', $pacu),
            ],
        ];
    }

    protected function formatDate(\Illuminate\Support\Carbon|string|null $date): ?string
    {
        if (! $date) {
            return null;
        }

        if (! $date instanceof \Illuminate\Support\Carbon) {
            try {
                $date = \Illuminate\Support\Carbon::parse($date);
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return verta($date)->format('Y/n/j');
        } catch (\Throwable) {
            return $date->format('Y-m-d');
        }
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function departments(?int $branchId): array
    {
        return Department::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Department $department) => ['id' => $department->id, 'name' => $department->name])
            ->values()
            ->all();
    }
}
